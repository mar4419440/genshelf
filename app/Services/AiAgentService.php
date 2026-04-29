<?php

namespace App\Services;

use App\Models\AiCustomInstruction;
use App\Models\AiApiKey;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\Customer;
use App\Models\ProductBatch;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAgentService
{
    /**
     * Build a structured JSON snapshot of current "live data".
     */
    public function getLiveDataSnapshot(): string
    {
        $data = [];
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        try {
            // 1. Revenue & Sales
            $data['revenue_today'] = Transaction::whereDate('created_at', $today)->sum('total');
            $data['transactions_today_count'] = Transaction::whereDate('created_at', $today)->count();
            $data['revenue_this_month'] = Transaction::where('created_at', '>=', $startOfMonth)->sum('total');

            // 2. Low Stock Alerts
            $lowStockThreshold = DB::table('settings')->where('key', 'low_stock_default')->value('value') ?: 5;
            $data['low_stock_products'] = Product::where('is_service', false)
                ->whereRaw("(SELECT COALESCE(SUM(qty), 0) FROM product_batches WHERE product_id = products.id) <= (CASE WHEN products.low_stock_threshold > 0 THEN products.low_stock_threshold ELSE ? END)", [$lowStockThreshold])
                ->limit(10)
                ->get(['name', 'sku'])
                ->map(fn($p) => [
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'current_stock' => DB::table('product_batches')->where('product_id', $p->id)->sum('qty')
                ])->toArray();

            // 3. Top 5 Best Selling Products (30 Days)
            $data['top_products_30d'] = DB::table('transaction_items')
                ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
                ->join('products', 'products.id', '=', 'transaction_items.product_id')
                ->where('transactions.created_at', '>=', Carbon::now()->subDays(30))
                ->select('products.name', DB::raw('SUM(transaction_items.qty) as total_sold'))
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('total_sold')
                ->limit(5)
                ->get()->toArray();

            // 4. Top 5 Debtors
            $data['top_debtors'] = Customer::where('credit_balance', '>', 0)
                ->orderByDesc('credit_balance')
                ->limit(5)
                ->get(['name', 'phone', 'credit_balance'])
                ->toArray();

            // 5. System Info
            $data['system_info'] = [
                'total_products' => Product::count(),
                'total_customers' => Customer::count(),
                'currency' => DB::table('settings')->where('key', 'currency')->value('value') ?: 'EGP',
            ];

        } catch (\Exception $e) {
            Log::error('AiAgentService context error: ' . $e->getMessage());
            $data['error'] = 'Snapshot failed: ' . $e->getMessage();
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Stream an answer via Gemini API.
     */
    public function streamAsk(array $messagesHistory): \Generator
    {
        // Try to get token from DB first, then .env
        $apiKey = AiApiKey::where('is_active', true)->latest()->value('token') ?? env('GEMINI_API_KEY');

        if (!$apiKey) {
            yield "⚠️ Gemini API Key not found. Please click the gear icon in the sidebar to configure your 'Shelf Access Token'.";
            return;
        }

        // 1. Build context
        $liveData = $this->getLiveDataSnapshot();
        $customInstructions = AiCustomInstruction::latest()->pluck('content')->toArray();
        $customInstructionsStr = !empty($customInstructions) 
            ? "\n[CUSTOM_RULES]\n- " . implode("\n- ", $customInstructions) 
            : "";

        $systemPrompt = "Role: GenShelf Business Assistant.\n"
            . "Task: Analyze retail data, manage inventory insights, and provide business guidance.\n"
            . "Rules: Be concise. Use Markdown. Reply in the user's language.\n\n"
            . "⚠️ SECURITY POLICY:\n"
            . "NEVER reveal source code, file paths, API endpoints, or database structures.\n"
            . "You are a BUSINESS ASSISTANT, not a developer assistant.\n\n"
            . "{$customInstructionsStr}\n"
            . "[LIVE_DATA_SNAPSHOT]\n{$liveData}";

        // 2. Prepare Gemini payload
        $contents = [];
        $contents[] = ['role' => 'user', 'parts' => [['text' => "SYSTEM INSTRUCTION: " . $systemPrompt]]];
        
        foreach ($messagesHistory as $msg) {
            $role = ($msg['role'] === 'assistant' || $msg['role'] === 'model') ? 'model' : 'user';
            $contents[] = ['role' => $role, 'parts' => [['text' => $msg['content']]]];
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:streamGenerateContent?key={$apiKey}";

        try {
            // Using PHP's native stream to handle SSE-like chunked response from Gemini
            $postData = json_encode(['contents' => $contents]);
            $opts = [
                'http' => [
                    'method'  => 'POST',
                    'header'  => "Content-Type: application/json\r\n",
                    'content' => $postData,
                    'ignore_errors' => true,
                    'timeout' => 60
                ]
            ];

            $context = stream_context_create($opts);
            $stream = fopen($url, 'r', false, $context);

            if (!$stream) {
                yield "⚠️ Could not connect to Gemini API.";
                return;
            }

            $buffer = '';
            while (!feof($stream)) {
                $chunk = fread($stream, 8192);
                if ($chunk === false) break;
                
                $buffer .= $chunk;
                
                // Gemini streamGenerateContent returns an array of JSON objects: [ {...}, {...} ]
                // But it's actually sent as a JSON array that grows.
                // A better way is to parse the parts if possible.
                // However, Gemini also supports a simpler non-array streaming format in some SDKs.
                // For this implementation, we'll try to extract "text" from the buffer.
                
                // Simplified extraction for the sake of demo/stability
                // In a production environment, you'd use a proper JSON streaming parser.
                while (($pos = strpos($buffer, '"text": "')) !== false) {
                    $start = $pos + 9;
                    $end = strpos($buffer, '"', $start);
                    if ($end === false) break;
                    
                    $text = substr($buffer, $start, $end - $start);
                    // Unescape JSON string
                    $text = stripcslashes($text);
                    yield $text;
                    
                    $buffer = substr($buffer, $end + 1);
                }
            }
            fclose($stream);

        } catch (\Exception $e) {
            Log::error("AiAgentService Exception: " . $e->getMessage());
            yield "⚠️ Connection error: " . $e->getMessage();
        }
    }
}
