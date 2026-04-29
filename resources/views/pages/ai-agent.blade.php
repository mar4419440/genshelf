@extends('layouts.app')

@section('title', 'GenShelf AI | Premium Business Assistant')

@section('content')
<div class="container-fluid py-4 h-100">
    <div class="row g-4 ai-layout">
        <!-- Sidebar: History & Settings -->
        <div class="col-md-3 d-none d-md-flex flex-column h-100 history-sidebar">
            <div class="glass-card shadow-lg flex-fill d-flex flex-column overflow-hidden">
                <div class="p-4">
                    <form action="{{ route('admin.ai.chats.store') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-premium w-100">
                            <i class="bi bi-plus-lg me-2"></i> {{ __('New Chat') }}
                        </button>
                    </form>
                </div>
                
                <div class="flex-fill overflow-y-auto px-2">
                    <div class="sidebar-label px-3">{{ __('Recent Conversations') }}</div>
                    <div class="history-list">
                        @forelse($chats as $chat)
                            <div class="history-item {{ isset($activeChat) && $activeChat->id == $chat->id ? 'active' : '' }}">
                                <a href="{{ route('admin.ai.index', $chat->id) }}" class="history-link">
                                    <div class="history-title">{{ $chat->title ?: 'New Conversation' }}</div>
                                    <div class="history-date">{{ $chat->created_at->diffForHumans() }}</div>
                                </a>
                                <form action="{{ route('admin.ai.chats.destroy', $chat->id) }}" method="POST" onsubmit="return confirm('Delete this chat?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="delete-btn"><i class="bi bi-trash3"></i></button>
                                </form>
                            </div>
                        @empty
                            <div class="empty-history">
                                <i class="bi bi-chat-left-dots"></i>
                                <p>{{ __('No history yet') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="p-3 border-top border-light border-opacity-10">
                    <button class="btn-settings w-100" data-bs-toggle="modal" data-bs-target="#aiSettingsModal">
                        <i class="bi bi-gear-fill me-2"></i> {{ __('AI Configuration') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="col-md-9 d-flex flex-column h-100 chat-main">
            <div class="glass-card shadow-lg flex-fill d-flex flex-column overflow-hidden position-relative">
                <!-- Header -->
                <div class="chat-header p-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="ai-avatar-pulse">
                            <i class="bi bi-robot fs-3"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold text-gradient-primary">GenShelf Assistant</h4>
                            <div class="status-indicator">
                                <span class="dot"></span>
                                <span class="status-text">{{ __('Context-Aware Intelligence') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn-icon" title="Clear Chat History"><i class="bi bi-arrow-counterclockwise"></i></button>
                        <button class="btn-icon" title="Export Logs"><i class="bi bi-download"></i></button>
                    </div>
                </div>

                <!-- Messages -->
                <div id="chatContainer" class="chat-body flex-fill p-4 overflow-y-auto">
                    @if(!$activeChat || empty($activeChat->messages))
                        <div class="welcome-screen">
                            <div class="welcome-icon">
                                <i class="bi bi-stars"></i>
                            </div>
                            <h2>{{ __('Hello, how can I assist your business today?') }}</h2>
                            <p>{{ __('I have live access to your inventory, sales, and financial snapshots.') }}</p>
                            
                            <div class="suggestion-grid mt-5">
                                <button class="suggestion-pill" onclick="fillQuestion('What was my total revenue today?')">
                                    <i class="bi bi-currency-dollar"></i> {{ __('Revenue Today') }}
                                </button>
                                <button class="suggestion-pill" onclick="fillQuestion('Which items are running low on stock?')">
                                    <i class="bi bi-box-seam"></i> {{ __('Low Stock Alert') }}
                                </button>
                                <button class="suggestion-pill" onclick="fillQuestion('Show me a summary of my top debtors.')">
                                    <i class="bi bi-people"></i> {{ __('Debtors Overview') }}
                                </button>
                                <button class="suggestion-pill" onclick="fillQuestion('/update Optimize for holiday seasonal sales.')">
                                    <i class="bi bi-command"></i> {{ __('Update Rule') }}
                                </button>
                            </div>
                        </div>
                    @else
                        @foreach($activeChat->messages as $msg)
                            <div class="message-row {{ $msg['role'] == 'user' ? 'user-row' : 'ai-row' }}">
                                <div class="message-bubble shadow-sm">
                                    <div class="message-avatar">
                                        <i class="bi {{ $msg['role'] == 'user' ? 'bi-person-fill' : 'bi-robot' }}"></i>
                                    </div>
                                    <div class="message-content markdown-content" dir="auto">
                                        {!! $msg['role'] == 'user' ? e($msg['content']) : $msg['content'] !!}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <!-- Input Area -->
                <div class="chat-footer p-4">
                    <form id="chatForm" onsubmit="return handleSubmit(event)" class="input-wrapper">
                        <input type="text" id="userInput" class="chat-input" placeholder="{{ __('Type your message or use /commands...') }}" autocomplete="off">
                        <button type="submit" id="sendBtn" class="send-btn">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </form>
                    <div class="footer-meta d-flex justify-content-between mt-2">
                        <span class="text-muted small"><i class="bi bi-info-circle me-1"></i> {{ __('Uses live data context') }}</span>
                        <div id="statusIndicator" class="typing-indicator d-none">
                            <span></span><span></span><span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AI Settings Modal -->
<div class="modal fade" id="aiSettingsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-gradient-primary">{{ __('AI Configuration') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="settingsForm">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small text-uppercase letter-spacing-1">{{ __('Shelf Access Tokens (API Key)') }}</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 border-light border-opacity-10 text-primary">
                                <i class="bi bi-key-fill"></i>
                            </span>
                            <input type="password" class="form-control bg-transparent border-start-0 border-light border-opacity-10 text-white" 
                                   id="apiTokenInput" placeholder="••••••••••••••••••••••••">
                        </div>
                        <div class="form-text text-muted small mt-2">
                            {{ __('This key is used to connect to the Gemini LLM service.') }}
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small text-uppercase letter-spacing-1">{{ __('Security Password') }}</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 border-light border-opacity-10 text-primary">
                                <i class="bi bi-shield-lock-fill"></i>
                            </span>
                            <input type="password" class="form-control bg-transparent border-start-0 border-light border-opacity-10 text-white" 
                                   id="configPasswordInput" placeholder="••••••••">
                        </div>
                    </div>
                    <button type="button" class="btn-premium w-100" onclick="saveSettings()">{{ __('Save Configuration') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --ai-primary: #6366f1;
        --ai-secondary: #a855f7;
        --ai-bg: #0f172a;
        --ai-card: rgba(30, 41, 59, 0.7);
        --ai-text: #f8fafc;
        --ai-muted: #94a3b8;
    }

    .ai-layout { height: calc(100vh - 120px); }
    .glass-card {
        background: var(--ai-card);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 24px;
    }

    .text-gradient-primary {
        background: linear-gradient(135deg, #818cf8, #c084fc);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .btn-premium {
        background: linear-gradient(135deg, var(--ai-primary), var(--ai-secondary));
        color: white;
        border: none;
        border-radius: 12px;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
    }
    .btn-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
    }

    .sidebar-label {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--ai-muted);
        letter-spacing: 1.5px;
        margin: 20px 0 10px;
    }

    .history-item {
        position: relative;
        margin-bottom: 5px;
        border-radius: 12px;
        transition: all 0.2s ease;
    }
    .history-item:hover, .history-item.active {
        background: rgba(255, 255, 255, 0.05);
    }
    .history-link {
        display: block;
        padding: 12px 15px;
        text-decoration: none;
        color: var(--ai-text);
        padding-right: 40px;
    }
    .history-title { font-size: 14px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .history-date { font-size: 11px; color: var(--ai-muted); }

    .delete-btn {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--ai-muted);
        opacity: 0;
        transition: all 0.2s ease;
    }
    .history-item:hover .delete-btn { opacity: 1; }
    .delete-btn:hover { color: #ef4444; }

    .btn-settings {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: var(--ai-text);
        border-radius: 10px;
        padding: 10px;
        font-size: 13px;
        transition: all 0.2s ease;
    }
    .btn-settings:hover { background: rgba(255, 255, 255, 0.1); }

    /* Chat Styling */
    .chat-header { border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
    .ai-avatar-pulse {
        width: 48px; height: 48px;
        background: linear-gradient(135deg, var(--ai-primary), var(--ai-secondary));
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        color: white;
        box-shadow: 0 0 20px rgba(99, 102, 241, 0.4);
        position: relative;
    }
    .ai-avatar-pulse::after {
        content: ''; position: absolute; inset: -4px;
        border-radius: 18px; border: 2px solid var(--ai-primary);
        animation: pulse 2s infinite; opacity: 0;
    }

    .status-indicator { display: flex; align-items: center; gap: 6px; }
    .status-indicator .dot { width: 8px; height: 8px; background: #10b981; border-radius: 50%; box-shadow: 0 0 10px #10b981; }
    .status-indicator .status-text { font-size: 12px; color: var(--ai-muted); font-weight: 500; }

    .welcome-screen {
        text-align: center; max-width: 600px; margin: 100px auto;
    }
    .welcome-icon {
        font-size: 64px; color: var(--ai-primary); margin-bottom: 20px;
        animation: float 3s ease-in-out infinite;
    }
    .welcome-screen h2 { font-weight: 800; color: white; margin-bottom: 15px; }
    .welcome-screen p { color: var(--ai-muted); font-size: 16px; }

    .suggestion-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .suggestion-pill {
        background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08);
        color: var(--ai-text); padding: 15px; border-radius: 16px; text-align: left;
        font-size: 14px; font-weight: 500; transition: all 0.2s ease;
    }
    .suggestion-pill:hover { background: rgba(255, 255, 255, 0.08); transform: translateY(-3px); border-color: var(--ai-primary); }
    .suggestion-pill i { color: var(--ai-primary); margin-right: 8px; }

    /* Messages */
    .message-row { display: flex; margin-bottom: 24px; animation: slideUp 0.4s ease-out forwards; }
    .user-row { justify-content: flex-end; }
    .ai-row { justify-content: flex-start; }

    .message-bubble {
        max-width: 80%; padding: 18px; border-radius: 20px; position: relative;
        display: flex; gap: 15px;
    }
    .user-row .message-bubble { background: var(--ai-primary); color: white; border-bottom-right-radius: 4px; flex-direction: row-reverse; }
    .ai-row .message-bubble { background: rgba(255, 255, 255, 0.05); color: white; border-bottom-left-radius: 4px; border: 1px solid rgba(255, 255, 255, 0.1); }

    .message-avatar {
        width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center;
        background: rgba(255, 255, 255, 0.1); flex-shrink: 0; font-size: 14px;
    }

    .input-wrapper {
        background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 20px; padding: 10px; display: flex; gap: 10px; transition: all 0.3s ease;
    }
    .input-wrapper:focus-within { border-color: var(--ai-primary); box-shadow: 0 0 20px rgba(99, 102, 241, 0.1); }
    .chat-input {
        background: none; border: none; flex: 1; color: white; padding: 10px 15px; outline: none; font-size: 15px;
    }
    .send-btn {
        background: var(--ai-primary); color: white; border: none; width: 44px; height: 44px;
        border-radius: 14px; display: flex; align-items: center; justify-content: center;
        transition: all 0.2s ease;
    }
    .send-btn:hover { transform: scale(1.05); background: var(--ai-secondary); }

    /* Markdown styling for premium look */
    .markdown-content table { width: 100%; border-radius: 12px; overflow: hidden; border: 1px solid rgba(255, 255, 255, 0.1); margin: 15px 0; }
    .markdown-content th { background: rgba(255, 255, 255, 0.1); padding: 12px; font-weight: 700; font-size: 13px; text-transform: uppercase; }
    .markdown-content td { padding: 12px; border-top: 1px solid rgba(255, 255, 255, 0.05); font-size: 14px; }
    .markdown-content p { margin-bottom: 12px; line-height: 1.6; }
    .markdown-content code { background: rgba(255, 255, 255, 0.1); padding: 2px 6px; border-radius: 4px; font-family: 'JetBrains Mono', monospace; font-size: 13px; }

    .typing-indicator { display: flex; gap: 4px; }
    .typing-indicator span {
        width: 6px; height: 6px; background: var(--ai-primary); border-radius: 50%;
        animation: bounce 1.4s infinite ease-in-out;
    }
    .typing-indicator span:nth-child(1) { animation-delay: -0.32s; }
    .typing-indicator span:nth-child(2) { animation-delay: -0.16s; }

    @keyframes pulse { 0% { transform: scale(1); opacity: 0.5; } 100% { transform: scale(1.5); opacity: 0; } }
    @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
    @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    @keyframes bounce { 0%, 80%, 100% { transform: scale(0); } 40% { transform: scale(1); } }
</style>

<!-- Marked.js for Markdown -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

<script>
    const chatContainer = document.getElementById('chatContainer');
    const chatForm = document.getElementById('chatForm');
    const userInput = document.getElementById('userInput');
    const sendBtn = document.getElementById('sendBtn');
    const statusIndicator = document.getElementById('statusIndicator');
    const activeChatId = "{{ $activeChat ? $activeChat->id : '' }}";
    let currentChatId = activeChatId;

    marked.setOptions({ breaks: true, gfm: true });
    chatContainer.scrollTop = chatContainer.scrollHeight;

    function fillQuestion(q) {
        userInput.value = q;
        userInput.focus();
    }

    function appendMessage(role, content, isHtml = false) {
        const isUser = role === 'user';
        const row = document.createElement('div');
        row.className = `message-row ${isUser ? 'user-row' : 'ai-row'}`;
        
        const inner = `
            <div class="message-bubble shadow-sm">
                <div class="message-avatar">
                    <i class="bi ${isUser ? 'bi-person-fill' : 'bi-robot'}"></i>
                </div>
                <div class="message-content markdown-content" dir="auto">${isHtml ? content : escapeHtml(content)}</div>
            </div>
        `;
        row.innerHTML = inner;
        chatContainer.appendChild(row);
        chatContainer.scrollTop = chatContainer.scrollHeight;
        return row.querySelector('.message-content');
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    async function handleSubmit(e) {
        e.preventDefault();
        const text = userInput.value.trim();
        if (!text) return;

        userInput.value = '';
        userInput.disabled = true;
        sendBtn.disabled = true;
        statusIndicator.classList.remove('d-none');
        
        const welcome = document.querySelector('.welcome-screen');
        if (welcome) welcome.remove();

        appendMessage('user', text);

        try {
            const response = await fetch("{{ route('admin.ai.ask') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ question: text, chat_id: currentChatId })
            });

            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                const data = await response.json();
                appendMessage('assistant', data.text);
                statusIndicator.classList.add('d-none');
                userInput.disabled = false;
                sendBtn.disabled = false;
                return;
            }

            const reader = response.body.getReader();
            const decoder = new TextDecoder();
            let aiMsgContent = '';
            let aiMsgElement = appendMessage('assistant', '', true);
            
            statusIndicator.classList.add('d-none');

            while (true) {
                const { value, done } = await reader.read();
                if (done) break;
                const chunk = decoder.decode(value, { stream: true });
                const lines = chunk.split('\n');
                for (const line of lines) {
                    if (line.startsWith('data: ')) {
                        const dataStr = line.substring(6).trim();
                        if (dataStr === '[DONE]') continue;
                        try {
                            const data = JSON.parse(dataStr);
                            if (data.chat_id && !currentChatId) {
                                currentChatId = data.chat_id;
                                window.history.replaceState(null, null, `{{ url('/ai/assistant') }}/${data.chat_id}`);
                            }
                            if (data.text) {
                                aiMsgContent += data.text;
                                aiMsgElement.innerHTML = marked.parse(aiMsgContent);
                                chatContainer.scrollTop = chatContainer.scrollHeight;
                            }
                        } catch (err) {}
                    }
                }
            }
        } catch (err) {
            appendMessage('assistant', '<span class="text-danger">⚠️ Connection error.</span>', true);
        }

        userInput.disabled = false;
        sendBtn.disabled = false;
        userInput.focus();
    }

    function saveSettings() {
        const token = document.getElementById('apiTokenInput').value;
        const password = document.getElementById('configPasswordInput').value;
        
        if(!token) return alert('Please enter a token');
        if(!password) return alert('Please enter the security password');
        
        fetch("{{ route('admin.ai.settings.save-token') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ token: token, password: password })
        }).then(res => res.json()).then(data => {
            if(data.success) {
                bootstrap.Modal.getInstance(document.getElementById('aiSettingsModal')).hide();
                alert('Token saved successfully!');
                location.reload();
            } else {
                alert(data.message || 'Error saving token');
            }
        });
    }
</script>
@endsection
