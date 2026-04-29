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

    public function __construct(AiAgentService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index($chatId = null)
    {
        $chats = AiChat::where('user_id', Auth::id())->latest()->get();
        $activeChat = $chatId ? AiChat::findOrFail($chatId) : null;

        return view('pages.ai-agent', compact('chats', 'activeChat'));
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
        $chat = AiChat::findOrFail($id);
        $chat->delete();

        return redirect()->route('admin.ai.index');
    }

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
        return Response::stream(function () use ($chat, $history, $question) {
            $fullResponse = '';
            
            // Output initial chat_id if it's new
            echo "data: " . json_encode(['chat_id' => $chat->id]) . "\n\n";
            ob_flush();
            flush();

            foreach ($this->aiService->streamAsk($history) as $chunk) {
                $fullResponse .= $chunk;
                echo "data: " . json_encode(['text' => $chunk]) . "\n\n";
                ob_flush();
                flush();
            }

            // Save Final Response to History
            $history[] = ['role' => 'assistant', 'content' => $fullResponse];
            $chat->update(['messages' => $history]);

            // Log Assistant Message
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
            'X-Accel-Buffering' => 'no', // For Nginx
        ]);
    }

    public function saveToken(Request $request)
    {
        $token = $request->input('token');
        $password = $request->input('password');

        if ($password !== 'P@ssw0rd@gencode') {
            return response()->json(['success' => false, 'message' => 'Unauthorized: Invalid configuration password.']);
        }

        if (empty($token)) {
            return response()->json(['success' => false, 'message' => 'Token cannot be empty']);
        }

        // Deactivate old tokens
        AiApiKey::query()->update(['is_active' => false]);

        // Create new token
        AiApiKey::create([
            'label' => 'Shelf Access Token',
            'token' => $token,
            'is_active' => true
        ]);

        return response()->json(['success' => true]);
    }
}
