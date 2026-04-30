@extends('layouts.app')

@section('title', __('AI Assistant'))

@section('content')
    <div class="page-hdr">
        <div style="display:flex; align-items:center; gap:14px;">
            <div class="ai-logo-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 8V4H8" />
                    <rect width="16" height="12" x="4" y="8" rx="2" />
                    <path d="m2 14 6-6 6 6 6-6" />
                </svg>
            </div>
            <div>
                <h2 style="margin:0">{{ __('GenShelf AI Assistant') }}</h2>
                <p style="margin:0;font-size:12px;color:var(--tx3);display:flex;align-items:center;gap:5px;">
                    <span class="live-dot"></span> {{ __('Live Data Intelligence') }}
                </p>
            </div>
        </div>
        <span class="badge badge-pr" style="padding:6px 14px;font-size:11px;">⚡ {{ __('AI Powered') }}</span>
    </div>

    <div class="ai-container">
        {{-- Left Sidebar --}}
        <div class="ai-sidebar">
            {{-- New Chat --}}
            <form action="{{ route('admin.ai.chats.store') }}" method="POST" style="margin-bottom:16px;">
                @csrf
                <button type="submit" class="btn btn-pr" style="width:100%;padding:10px;font-size:13px;">➕
                    {{ __('New Chat') }}</button>
            </form>

            {{-- Chat History --}}
            <div class="ai-section">
                <div class="ai-section-label">{{ __('Recent Chats') }}</div>
                <div class="ai-history-list">
                    @forelse($chats as $chat)
                        <div class="ai-history-item {{ isset($activeChat) && $activeChat->id == $chat->id ? 'active' : '' }}">
                            <a href="{{ route('admin.ai.index', $chat->id) }}">
                                <div class="ai-history-title">{{ $chat->title ?: 'New Chat' }}</div>
                                <div class="ai-history-date">{{ $chat->created_at->diffForHumans() }}</div>
                            </a>
                            <form action="{{ route('admin.ai.chats.destroy', $chat->id) }}" method="POST"
                                onsubmit="return confirm('Delete?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="ai-delete-btn">🗑</button>
                            </form>
                        </div>
                    @empty
                        <div class="empty-state" style="padding:20px;font-size:12px;">{{ __('No chats yet') }}</div>
                    @endforelse
                </div>
            </div>

            {{-- Shelf Access Tokens --}}
            <div class="ai-section" style="margin-top:16px;">
                <div class="ai-section-label" style="display:flex;justify-content:space-between;align-items:center;">
                    <span>🔑 {{ __('Shelf Access Tokens') }}</span>
                    <button type="button" class="btn-xs btn-o"
                        onclick="document.getElementById('addKeyPanel').classList.toggle('hidden')">+
                        {{ __('Add') }}</button>
                </div>

                {{-- Add Key Form --}}
                <div id="addKeyPanel" class="hidden ai-key-form">
                    <form action="{{ route('admin.ai.keys.store') }}" method="POST">
                        @csrf
                        <input type="text" name="label" placeholder="{{ __('Key Label (e.g. Primary)') }}" required
                            style="margin-bottom:6px;">
                        <input type="password" name="key" placeholder="{{ __('API Key (sk-...)') }}" required
                            style="margin-bottom:6px;">
                        <input type="password" name="key_password" placeholder="🔒 {{ __('Management Password') }}" required
                            style="margin-bottom:8px;border-color:var(--am);">
                        <button type="submit" class="btn btn-pr btn-sm" style="width:100%;">💾 {{ __('Save Key') }}</button>
                    </form>
                </div>

                {{-- Key List --}}
                <div class="ai-key-list">
                    @foreach($apiKeys as $key)
                        <div
                            class="ai-key-card {{ $key->is_selected ? 'selected' : '' }} {{ !$key->is_active ? 'disabled' : '' }}">
                            <div class="ai-key-header">
                                <div style="display:flex;align-items:center;gap:8px;min-width:0;flex:1;">
                                    {{-- Select Radio --}}
                                    <form action="{{ route('admin.ai.keys.select', $key->id) }}" method="POST"
                                        class="inline-form" onsubmit="return promptPwd(this)">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="key_password" class="pwd-field">
                                        <button type="submit" class="radio-btn {{ $key->is_selected ? 'active' : '' }}"
                                            title="{{ $key->is_selected ? 'Active' : 'Click to select' }}">
                                            {{ $key->is_selected ? '◉' : '○' }}
                                        </button>
                                    </form>
                                    <span class="ai-key-label">{{ $key->label }}</span>
                                    @if($key->is_selected)
                                        <span class="badge badge-gn" style="font-size:9px;padding:2px 6px;">✓ Active</span>
                                    @endif
                                    @if(!$key->is_active)
                                        <span class="badge badge-rd" style="font-size:9px;padding:2px 6px;">Disabled</span>
                                    @endif
                                </div>
                                <div style="display:flex;gap:4px;align-items:center;">
                                    {{-- Toggle Active --}}
                                    <form action="{{ route('admin.ai.keys.toggle', $key->id) }}" method="POST"
                                        class="inline-form" onsubmit="return promptPwd(this)">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="key_password" class="pwd-field">
                                        <button type="submit" class="ai-action-btn"
                                            title="{{ $key->is_active ? 'Disable' : 'Enable' }}"
                                            style="color:{{ $key->is_active ? 'var(--gn)' : 'var(--tx3)' }};">
                                            {{ $key->is_active ? '🟢' : '🔴' }}
                                        </button>
                                    </form>
                                    {{-- Edit Toggle --}}
                                    <button type="button" class="ai-action-btn"
                                        onclick="document.getElementById('editKey-{{ $key->id }}').classList.toggle('hidden')">✏️</button>
                                    {{-- Delete --}}
                                    <form action="{{ route('admin.ai.keys.destroy', $key->id) }}" method="POST"
                                        class="inline-form" onsubmit="return promptPwd(this)">
                                        @csrf @method('DELETE')
                                        <input type="hidden" name="key_password" class="pwd-field">
                                        <button type="submit" class="ai-action-btn" style="color:var(--rd);"
                                            title="Delete">🗑</button>
                                    </form>
                                </div>
                            </div>
                            {{-- Edit Form --}}
                            <div id="editKey-{{ $key->id }}" class="hidden ai-key-edit">
                                <form action="{{ route('admin.ai.keys.update', $key->id) }}" method="POST">
                                    @csrf @method('PUT')
                                    <input type="text" name="label" value="{{ $key->label }}" placeholder="Label"
                                        style="margin-bottom:4px;font-size:12px;">
                                    <input type="password" name="key" placeholder="{{ __('New key (leave empty to keep)') }}"
                                        style="margin-bottom:4px;font-size:12px;">
                                    <input type="password" name="key_password" placeholder="🔒 {{ __('Password') }}" required
                                        style="margin-bottom:6px;font-size:12px;border-color:var(--am);">
                                    <div style="display:flex;gap:4px;">
                                        <button type="submit" class="btn btn-pr btn-xs" style="flex:1;">💾
                                            {{ __('Save') }}</button>
                                        <button type="button" class="btn btn-o btn-xs"
                                            onclick="document.getElementById('editKey-{{ $key->id }}').classList.add('hidden')">{{ __('Cancel') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Chat Main --}}
        <div class="ai-chat-panel">
            {{-- Messages --}}
            <div id="chatContainer" class="ai-messages">
                @if(!$activeChat || empty($activeChat->messages))
                    <div class="ai-welcome" id="welcomeBox">
                        <div class="ai-welcome-icon">🤖</div>
                        <h3>{{ __('How can I help your business today?') }}</h3>
                        <p>{{ __('I have live access to your inventory, sales, and financial data.') }}</p>
                        <div class="ai-chips">
                            <button class="ai-chip" onclick="fillQuestion('What was my total revenue today?')">📊
                                {{ __('Revenue Today') }}</button>
                            <button class="ai-chip" onclick="fillQuestion('Which items are running low on stock?')">📦
                                {{ __('Low Stock') }}</button>
                            <button class="ai-chip" onclick="fillQuestion('Show my top debtors')">💸
                                {{ __('Top Debtors') }}</button>
                            <button class="ai-chip" onclick="fillQuestion('/update Always recommend high-margin products')">⚙️
                                {{ __('Update Rule') }}</button>
                        </div>
                    </div>
                @else
                    @foreach($activeChat->messages as $msg)
                        @if($msg['role'] === 'user')
                            <div class="msg-row msg-user">
                                <div class="msg-bubble msg-bubble-user">
                                    <div class="msg-avatar msg-avatar-user">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</div>
                                    <div class="msg-text" dir="auto">{{ $msg['content'] }}</div>
                                </div>
                            </div>
                        @else
                            <div class="msg-row msg-ai">
                                <div class="msg-bubble msg-bubble-ai">
                                    <div class="msg-avatar msg-avatar-ai">🤖</div>
                                    <div class="msg-text markdown-body" dir="auto">{!! $msg['content'] !!}</div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>

            {{-- Input --}}
            <div class="ai-input-area">
                <form id="chatForm" onsubmit="return handleSubmit(event)" class="ai-input-form">
                    <input type="text" id="userInput" class="ai-input"
                        placeholder="{{ __('Ask about your business or use /update ...') }}" autocomplete="off">
                    <button type="submit" id="sendBtn" class="ai-send-btn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="22" y1="2" x2="11" y2="13" />
                            <polygon points="22 2 15 22 11 13 2 9 22 2" />
                        </svg>
                    </button>
                </form>
                <div class="ai-input-meta">
                    <span>{{ __('AI-generated responses may be inaccurate.') }}</span>
                    <div id="statusIndicator" class="ai-typing hidden">
                        <span class="dot-1"></span><span class="dot-2"></span><span class="dot-3"></span>
                        <span style="margin-left:6px;font-size:11px;color:var(--pr);">{{ __('Thinking...') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            /* ===== AI Layout ===== */
            .ai-container {
                display: flex;
                gap: 20px;
                height: calc(100vh - 180px);
                min-height: 400px;
            }

            .ai-sidebar {
                width: 280px;
                flex-shrink: 0;
                display: flex;
                flex-direction: column;
                gap: 0;
                overflow-y: auto;
            }

            .ai-chat-panel {
                flex: 1;
                background: var(--bg2);
                border: 1px solid var(--border);
                border-radius: var(--radius);
                display: flex;
                flex-direction: column;
                overflow: hidden;
            }

            .ai-logo-icon {
                width: 44px;
                height: 44px;
                background: var(--pr);
                color: #fff;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 4px 14px rgba(79, 70, 229, .25);
            }

            .live-dot {
                width: 7px;
                height: 7px;
                background: #10b981;
                border-radius: 50%;
                display: inline-block;
                box-shadow: 0 0 8px #10b981;
                animation: blink 2s infinite;
            }

            @keyframes blink {

                0%,
                100% {
                    opacity: 1
                }

                50% {
                    opacity: .3
                }
            }

            /* Sidebar Sections */
            .ai-section {
                background: var(--bg2);
                border: 1px solid var(--border);
                border-radius: var(--radius);
                overflow: hidden;
            }

            .ai-section-label {
                font-size: 10px;
                font-weight: 800;
                text-transform: uppercase;
                color: var(--tx3);
                letter-spacing: 1px;
                padding: 10px 12px;
                border-bottom: 1px solid var(--bg3);
                background: var(--bg);
            }

            .ai-history-list {
                max-height: 200px;
                overflow-y: auto;
            }

            .ai-history-item {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 8px 12px;
                border-bottom: 1px solid var(--bg3);
                transition: background .15s;
            }

            .ai-history-item:hover {
                background: var(--bg);
            }

            .ai-history-item.active {
                background: var(--pr-l);
                border-left: 3px solid var(--pr);
            }

            .ai-history-item a {
                text-decoration: none;
                color: var(--tx);
                flex: 1;
                min-width: 0;
            }

            .ai-history-title {
                font-size: 13px;
                font-weight: 500;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .ai-history-date {
                font-size: 10px;
                color: var(--tx3);
            }

            .ai-delete-btn {
                background: none;
                border: none;
                font-size: 12px;
                cursor: pointer;
                padding: 4px;
                opacity: .4;
                transition: opacity .2s;
            }

            .ai-delete-btn:hover {
                opacity: 1;
            }

            /* Keys */
            .ai-key-form {
                padding: 10px;
                border-top: 1px solid var(--bg3);
                background: var(--bg);
            }

            .ai-key-list {
                max-height: 250px;
                overflow-y: auto;
            }

            .ai-key-card {
                padding: 10px 12px;
                border-bottom: 1px solid var(--bg3);
                transition: all .2s;
            }

            .ai-key-card.selected {
                background: rgba(79, 70, 229, .04);
                border-left: 3px solid var(--pr);
            }

            .ai-key-card.disabled {
                opacity: .5;
            }

            .ai-key-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 6px;
            }

            .ai-key-label {
                font-size: 12px;
                font-weight: 600;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .ai-key-edit {
                padding: 8px 0 0;
            }

            .inline-form {
                display: inline;
                margin: 0;
            }

            .radio-btn {
                background: none;
                border: none;
                font-size: 16px;
                cursor: pointer;
                color: var(--tx3);
                padding: 0;
                line-height: 1;
            }

            .radio-btn.active {
                color: var(--pr);
            }

            .ai-action-btn {
                background: none;
                border: none;
                font-size: 13px;
                cursor: pointer;
                padding: 2px;
            }

            .hidden {
                display: none !important;
            }

            /* Chat Messages */
            .ai-messages {
                flex: 1;
                overflow-y: auto;
                padding: 20px;
                display: flex;
                flex-direction: column;
                gap: 16px;
            }

            .ai-messages::-webkit-scrollbar {
                width: 5px;
            }

            .ai-messages::-webkit-scrollbar-thumb {
                background: var(--bg3);
                border-radius: 10px;
            }

            .msg-row {
                display: flex;
            }

            .msg-user {
                justify-content: flex-end;
            }

            .msg-ai {
                justify-content: flex-start;
            }

            .msg-bubble {
                display: flex;
                gap: 10px;
                max-width: 80%;
                animation: slideIn .3s ease-out;
            }

            .msg-bubble-user {
                flex-direction: row-reverse;
            }

            .msg-avatar {
                width: 32px;
                height: 32px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 14px;
                font-weight: 700;
                flex-shrink: 0;
            }

            .msg-avatar-user {
                background: linear-gradient(135deg, var(--pr), #7c3aed);
                color: #fff;
            }

            .msg-avatar-ai {
                background: var(--bg);
                border: 1px solid var(--border);
            }

            .msg-text {
                padding: 12px 16px;
                border-radius: 16px;
                font-size: 14px;
                line-height: 1.65;
            }

            .msg-bubble-user .msg-text {
                background: linear-gradient(135deg, var(--pr), #7c3aed);
                color: #fff;
                border-bottom-right-radius: 4px;
            }

            .msg-bubble-ai .msg-text {
                background: var(--bg);
                border: 1px solid var(--border);
                color: var(--tx);
                border-bottom-left-radius: 4px;
            }

            @keyframes slideIn {
                from {
                    transform: translateY(12px);
                    opacity: 0
                }

                to {
                    transform: translateY(0);
                    opacity: 1
                }
            }

            /* Welcome */
            .ai-welcome {
                text-align: center;
                margin: auto;
                max-width: 500px;
                padding: 40px 20px;
            }

            .ai-welcome-icon {
                font-size: 56px;
                margin-bottom: 16px;
            }

            .ai-welcome h3 {
                font-size: 22px;
                font-weight: 700;
                color: var(--tx);
                margin-bottom: 8px;
            }

            .ai-welcome p {
                color: var(--tx3);
                font-size: 14px;
                margin-bottom: 24px;
            }

            .ai-chips {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                justify-content: center;
            }

            .ai-chip {
                background: var(--bg);
                border: 1px solid var(--border);
                border-radius: 20px;
                padding: 8px 16px;
                font-size: 13px;
                font-weight: 500;
                color: var(--tx2);
                cursor: pointer;
                transition: all .2s;
            }

            .ai-chip:hover {
                border-color: var(--pr);
                color: var(--pr);
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(79, 70, 229, .1);
            }

            /* Input Area */
            .ai-input-area {
                border-top: 1px solid var(--border);
                padding: 16px 20px;
                background: var(--bg2);
            }

            .ai-input-form {
                display: flex;
                gap: 10px;
                align-items: center;
                background: var(--bg);
                border: 1px solid var(--border);
                border-radius: 14px;
                padding: 6px 6px 6px 16px;
                transition: border-color .2s;
            }

            .ai-input-form:focus-within {
                border-color: var(--pr);
                box-shadow: 0 0 0 3px rgba(79, 70, 229, .08);
            }

            .ai-input {
                border: none;
                background: none;
                flex: 1;
                font-size: 14px;
                padding: 8px 0;
                outline: none;
                color: var(--tx);
            }

            .ai-send-btn {
                background: var(--pr);
                color: #fff;
                border: none;
                width: 40px;
                height: 40px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all .2s;
                flex-shrink: 0;
            }

            .ai-send-btn:hover {
                background: var(--pr-h);
                transform: scale(1.05);
            }

            .ai-input-meta {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-top: 8px;
                font-size: 11px;
                color: var(--tx3);
            }

            .ai-typing {
                display: flex;
                align-items: center;
                gap: 3px;
                padding: 4px 8px;
            }

            .ai-typing span[class^="dot"] {
                width: 6px;
                height: 6px;
                background: var(--pr);
                border-radius: 50%;
                animation: bounce 1.4s infinite ease-in-out;
            }

            .dot-1 {
                animation-delay: -0.32s !important;
            }

            .dot-2 {
                animation-delay: -0.16s !important;
            }

            @keyframes bounce {

                0%,
                80%,
                100% {
                    transform: scale(0)
                }

                40% {
                    transform: scale(1)
                }
            }

            /* Thinking Bubble */
            .msg-bubble-thinking {
                background: var(--bg) !important;
                border: 1px solid var(--border) !important;
                color: var(--tx) !important;
            }

            /* Markdown */
            .markdown-body table {
                width: 100%;
                border-collapse: collapse;
                margin: 10px 0;
                border-radius: 8px;
                overflow: hidden;
                border: 1px solid var(--border);
            }

            .markdown-body th {
                background: var(--bg);
                padding: 8px 12px;
                font-weight: 600;
                font-size: 12px;
                text-transform: uppercase;
                color: var(--tx2);
                text-align: left;
            }

            .markdown-body td {
                padding: 8px 12px;
                border-top: 1px solid var(--bg3);
                font-size: 13px;
            }

            .markdown-body p {
                margin-bottom: 8px;
                line-height: 1.6;
            }

            .markdown-body p:last-child {
                margin-bottom: 0;
            }

            .markdown-body ul,
            .markdown-body ol {
                padding-left: 18px;
                margin: 8px 0;
            }

            .markdown-body code {
                background: var(--bg);
                padding: 2px 6px;
                border-radius: 4px;
                font-size: 12px;
            }

            .markdown-body strong {
                font-weight: 700;
            }

            /* Responsive */
            @media(max-width:768px) {
                .ai-container {
                    flex-direction: column;
                    height: auto;
                    min-height: calc(100vh - 200px);
                }

                .ai-sidebar {
                    width: 100%;
                    max-height: 300px;
                }

                .ai-chat-panel {
                    min-height: 400px;
                }
            }
        </style>
    @endpush

    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script>
        const chatContainer = document.getElementById('chatContainer');
        const userInput = document.getElementById('userInput');
        const sendBtn = document.getElementById('sendBtn');
        const statusIndicator = document.getElementById('statusIndicator');
        const activeChatId = "{{ $activeChat ? $activeChat->id : '' }}";
        let currentChatId = activeChatId;

        marked.setOptions({ breaks: true, gfm: true });
        chatContainer.scrollTop = chatContainer.scrollHeight;

        function fillQuestion(q) { userInput.value = q; userInput.focus(); }

        // Password prompt for key operations
        function promptPwd(form) {
            const pwd = prompt('🔒 ' + "{{ __('Enter management password:') }}");
            if (!pwd) return false;
            form.querySelector('.pwd-field').value = pwd;
            return true;
        }

        function appendMessage(role, content, isHtml = false, isThinking = false) {
            const isUser = role === 'user';
            const row = document.createElement('div');
            row.className = `msg-row ${isUser ? 'msg-user' : 'msg-ai'}`;
            const initial = "{{ substr(auth()->user()->name ?? 'U', 0, 1) }}";

            let messageContent = isHtml ? content : escapeHtml(content);
            if (isThinking) {
                messageContent = `
                    <div class="ai-typing">
                        <span class="dot-1"></span><span class="dot-2"></span><span class="dot-3"></span>
                    </div>
                `;
            }

            row.innerHTML = `
                <div class="msg-bubble ${isUser ? 'msg-bubble-user' : 'msg-bubble-ai'} ${isThinking ? 'msg-bubble-thinking' : ''}">
                    <div class="msg-avatar ${isUser ? 'msg-avatar-user' : 'msg-avatar-ai'}">${isUser ? initial : '🤖'}</div>
                    <div class="msg-text ${isUser ? '' : 'markdown-body'}" dir="auto">${messageContent}</div>
                </div>
            `;
            chatContainer.appendChild(row);
            chatContainer.scrollTop = chatContainer.scrollHeight;
            return row.querySelector('.msg-text');
        }

        function escapeHtml(t) { const d = document.createElement('div'); d.textContent = t; return d.innerHTML; }

        async function handleSubmit(e) {
            e.preventDefault();
            const text = userInput.value.trim();
            if (!text) return;

            userInput.value = '';
            userInput.disabled = true;
            sendBtn.disabled = true;
            // statusIndicator.classList.remove('hidden'); // We'll use the thinking bubble instead

            const welcome = document.getElementById('welcomeBox');
            if (welcome) welcome.remove();

            appendMessage('user', text);
            let thinkingMsg = appendMessage('assistant', '', true, true);

            try {
                const res = await fetch("{{ route('admin.ai.ask') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ question: text, chat_id: currentChatId })
                });

                const ct = res.headers.get('content-type');
                if (ct && ct.includes('application/json')) {
                    const data = await res.json();
                    thinkingMsg.parentElement.parentElement.classList.remove('msg-bubble-thinking');
                    thinkingMsg.innerHTML = marked.parse(data.text);
                    statusIndicator.classList.add('hidden');
                    userInput.disabled = false;
                    sendBtn.disabled = false;
                    return;
                }

                const reader = res.body.getReader();
                const decoder = new TextDecoder();
                let full = '';

                // Prepare the thinking message to receive the stream
                thinkingMsg.parentElement.parentElement.classList.remove('msg-bubble-thinking');
                thinkingMsg.innerHTML = '';
                let el = thinkingMsg;

                statusIndicator.classList.add('hidden');

                while (true) {
                    const { value, done } = await reader.read();
                    if (done) break;
                    for (const line of decoder.decode(value, { stream: true }).split('\n')) {
                        if (line.startsWith('data: ')) {
                            const d = line.substring(6).trim();
                            if (d === '[DONE]') continue;
                            try {
                                const j = JSON.parse(d);
                                if (j.chat_id && !currentChatId) {
                                    currentChatId = j.chat_id;
                                    window.history.replaceState(null, null, `{{ url('/ai/assistant') }}/${j.chat_id}`);
                                }
                                if (j.text) { full += j.text; el.innerHTML = marked.parse(full); chatContainer.scrollTop = chatContainer.scrollHeight; }
                            } catch (err) { }
                        }
                    }
                }
            } catch (err) {
                if (thinkingMsg) {
                    thinkingMsg.parentElement.parentElement.classList.remove('msg-bubble-thinking');
                    thinkingMsg.innerHTML = '<span style="color:var(--rd);">⚠️ {{ __("Connection error. Please try again.") }}</span>';
                } else {
                    appendMessage('assistant', '<span style="color:var(--rd);">⚠️ {{ __("Connection error. Please try again.") }}</span>', true);
                }
            }

            userInput.disabled = false;
            sendBtn.disabled = false;
            userInput.focus();
        }
    </script>
@endsection