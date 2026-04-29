<?php

namespace App\Http\Controllers;

use App\Models\AiChat;
use App\Models\AiCustomInstruction;
use App\Models\AiMessageLog;
use App\Models\AiApiKey;
use App\Services\AiAgentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class AiAgentController extends Controller
{
    protected $aiService;
    private const KEY_PASSWORD = 'P@ssw0rd@gencode';

    public function __construct(AiAgentService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index($chatId = null)
    {
        $chats = AiChat::where('user_id', Auth::id())->latest()->get();
        $activeChat = $chatId ? AiChat::findOrFail($chatId) : null;
        $apiKeys = AiApiKey::latest()->get();

        return view('pages.ai-agent', compact('chats', 'activeChat', 'apiKeys'));
    }

    public function storeChat()
    {
        $chat = AiChat::create([
            'user_id' => Auth::id(),
            'title' => 'New Conversation',
            'messages' => []
        ]);

        return redirect()->route('admin.ai.index', $chat->id);
    }

    public function destroyChat($id)
    {
        AiChat::findOrFail($id)->delete();
        return redirect()->route('admin.ai.index');
    }

    // ===== API Key CRUD =====

    public function storeKey(Request $request)
    {
        if ($request->input('key_password') !== self::KEY_PASSWORD) {
            return redirect()->back()->with('error', 'Invalid management password.');
        }

        AiApiKey::create([
            'label' => $request->input('label', 'Shelf Access Token'),
            'token' => $request->input('key'),
            'is_active' => true,
            'is_selected' => AiApiKey::count() === 0, // Auto-select first key
        ]);

        return redirect()->back()->with('success', 'API Key added successfully.');
    }

    public function updateKey(Request $request, $id)
    {
        if ($request->input('key_password') !== self::KEY_PASSWORD) {
            return redirect()->back()->with('error', 'Invalid management password.');
        }

        $key = AiApiKey::findOrFail($id);
        $data = ['label' => $request->input('label')];
        if ($request->filled('key')) {
            $data['token'] = $request->input('key');
        }
        $key->update($data);

        return redirect()->back()->with('success', 'API Key updated.');
    }

    public function destroyKey(Request $request, $id)
    {
        if ($request->input('key_password') !== self::KEY_PASSWORD) {
            return redirect()->back()->with('error', 'Invalid management password.');
        }

        $key = AiApiKey::findOrFail($id);
        $wasSelected = $key->is_selected;
        $key->delete();

        // If we deleted the selected key, auto-select the next one
        if ($wasSelected) {
            AiApiKey::where('is_active', true)->latest()->first()?->update(['is_selected' => true]);
        }

        return redirect()->back()->with('success', 'API Key deleted.');
    }

    public function selectKey(Request $request, $id)
    {
        if ($request->input('key_password') !== self::KEY_PASSWORD) {
            return redirect()->back()->with('error', 'Invalid management password.');
        }

        AiApiKey::query()->update(['is_selected' => false]);
        AiApiKey::findOrFail($id)->update(['is_selected' => true]);

        return redirect()->back()->with('success', 'Key selected as active.');
    }

    public function toggleKey(Request $request, $id)
    {
        if ($request->input('key_password') !== self::KEY_PASSWORD) {
            return redirect()->back()->with('error', 'Invalid management password.');
        }

        $key = AiApiKey::findOrFail($id);
        $key->update(['is_active' => !$key->is_active]);

        return redirect()->back()->with('success', 'Key toggled.');
    }

    // ===== Chat / AI =====

    public function ask(Request $request)
    {
        $question = $request->input('question');
        $chatId = $request->input('chat_id');

        // 1. Handle Commands
        if (str_starts_with($question, '/update')) {
            $content = trim(substr($question, 7));
            if (!empty($content)) {
                AiCustomInstruction::create(['content' => $content]);
                return response()->json(['text' => '✅ Knowledge Updated: "' . $content . '"', 'command' => true]);
            }
        }

        // 2. Load or Create Chat
        $chat = $chatId ? AiChat::find($chatId) : AiChat::create([
            'user_id' => Auth::id(),
            'title' => substr($question, 0, 30) . '...',
            'messages' => []
        ]);

        // 3. Log User Message
        AiMessageLog::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => $question
        ]);

        // 4. Update History
        $history = $chat->messages ?? [];
        $history[] = ['role' => 'user', 'content' => $question];

        // 5. SSE Response
        return Response::stream(function () use ($chat, $history) {
            $fullResponse = '';

            echo "data: " . json_encode(['chat_id' => $chat->id]) . "\n\n";
            ob_flush();
            flush();

            foreach ($this->aiService->streamAsk($history) as $chunk) {
                $fullResponse .= $chunk;
                echo "data: " . json_encode(['text' => $chunk]) . "\n\n";
                ob_flush();
                flush();
            }

            $history[] = ['role' => 'assistant', 'content' => $fullResponse];
            $chat->update(['messages' => $history]);

            AiMessageLog::create([
                'chat_id' => $chat->id,
                'role' => 'assistant',
                'content' => $fullResponse
            ]);

            echo "data: [DONE]\n\n";
            ob_flush();
            flush();
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
