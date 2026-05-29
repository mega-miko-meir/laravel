@extends('layout')
@section('content')

<div style="display:flex; flex-direction:column; height:calc(100vh - 112px); max-width:800px; margin:0 auto;">

    {{-- Шапка --}}
    <div style="display:flex; align-items:center; justify-content:space-between;
                padding:16px 20px; background:#fff; border-radius:12px 12px 0 0;
                border:1px solid #e5e7eb; border-bottom:none;">
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="width:40px; height:40px; border-radius:10px;
                        background:linear-gradient(135deg,#1e3a8a,#2563eb);
                        display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg style="width:20px; height:20px; color:#fff;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2"/>
                </svg>
            </div>
            <div>
                <p style="font-size:15px; font-weight:700; color:#111827; margin:0;">Nobel AI Ассистент</p>
                <p style="font-size:12px; color:#22c55e; margin:0; display:flex; align-items:center; gap:4px;">
                    <span style="width:6px; height:6px; background:#22c55e; border-radius:50%; display:inline-block;"></span>
                    Онлайн
                </p>
            </div>
        </div>

        <button onclick="clearHistory()"
                style="display:flex; align-items:center; gap:6px; padding:7px 12px;
                       background:#fff; color:#6b7280; border:1px solid #e5e7eb;
                       border-radius:8px; font-size:12px; cursor:pointer;"
                onmouseover="this.style.background='#fef2f2'; this.style.color='#dc2626'; this.style.borderColor='#fecaca';"
                onmouseout="this.style.background='#fff'; this.style.color='#6b7280'; this.style.borderColor='#e5e7eb';">
            <svg style="width:13px; height:13px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Очистить
        </button>
    </div>

    {{-- Область сообщений --}}
    <div id="chat-box"
         style="flex:1; overflow-y:auto; padding:20px; background:#f8fafc;
                border-left:1px solid #e5e7eb; border-right:1px solid #e5e7eb;">

        {{-- История из сессии --}}
        @if(empty($history))
            <div id="empty-state" style="display:flex; flex-direction:column; align-items:center;
                        justify-content:center; height:100%; color:#9ca3af; text-align:center;">
                <div style="width:56px; height:56px; border-radius:14px; margin-bottom:16px;
                            background:linear-gradient(135deg,#eff6ff,#dbeafe);
                            display:flex; align-items:center; justify-content:center;">
                    <svg style="width:28px; height:28px; color:#3b82f6;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <p style="font-size:15px; font-weight:600; color:#374151; margin-bottom:6px;">Начните диалог</p>
                <p style="font-size:13px; color:#9ca3af;">Задайте любой вопрос — я готов помочь</p>
            </div>
        @else
            @foreach($history as $msg)
                @if($msg['role'] === 'user')
                    <div style="display:flex; justify-content:flex-end; margin-bottom:12px;">
                        <div style="max-width:70%; background:#2563eb; color:#fff; padding:10px 14px;
                                    border-radius:14px 14px 4px 14px; font-size:14px; line-height:1.5;">
                            {{ $msg['content'] }}
                        </div>
                    </div>
                @else
                    <div style="display:flex; justify-content:flex-start; margin-bottom:12px; gap:10px;">
                        <div style="width:30px; height:30px; border-radius:8px; flex-shrink:0; margin-top:2px;
                                    background:linear-gradient(135deg,#1e3a8a,#2563eb);
                                    display:flex; align-items:center; justify-content:center;">
                            <svg style="width:15px; height:15px; color:#fff;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2"/>
                            </svg>
                        </div>
                        <div style="max-width:70%; background:#fff; color:#111827; padding:10px 14px;
                                    border-radius:14px 14px 14px 4px; font-size:14px; line-height:1.5;
                                    border:1px solid #e5e7eb; white-space:pre-wrap;">{{ $msg['content'] }}</div>
                    </div>
                @endif
            @endforeach
        @endif
    </div>

    {{-- Индикатор печати --}}
    <div id="typing-indicator"
         style="display:none; padding:10px 20px; background:#f8fafc;
                border-left:1px solid #e5e7eb; border-right:1px solid #e5e7eb;">
        <div style="display:flex; align-items:center; gap:10px;">
            <div style="width:30px; height:30px; border-radius:8px; flex-shrink:0;
                        background:linear-gradient(135deg,#1e3a8a,#2563eb);
                        display:flex; align-items:center; justify-content:center;">
                <svg style="width:15px; height:15px; color:#fff;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2"/>
                </svg>
            </div>
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:14px 14px 14px 4px;
                        padding:10px 14px; display:flex; gap:4px; align-items:center;">
                <span style="width:7px; height:7px; background:#9ca3af; border-radius:50%;
                             animation:bounce 1s infinite;"></span>
                <span style="width:7px; height:7px; background:#9ca3af; border-radius:50%;
                             animation:bounce 1s infinite .15s;"></span>
                <span style="width:7px; height:7px; background:#9ca3af; border-radius:50%;
                             animation:bounce 1s infinite .3s;"></span>
            </div>
        </div>
    </div>

    {{-- Ввод --}}
    <div style="padding:16px 20px; background:#fff; border:1px solid #e5e7eb;
                border-top:none; border-radius:0 0 12px 12px;">
        <div style="display:flex; gap:10px; align-items:flex-end;">
            <textarea id="user-input"
                      placeholder="Написать сообщение..."
                      rows="1"
                      style="flex:1; border:1.5px solid #e5e7eb; border-radius:10px; padding:10px 14px;
                             font-size:14px; outline:none; resize:none; font-family:inherit;
                             line-height:1.5; max-height:120px; overflow-y:auto; box-sizing:border-box;"
                      onfocus="this.style.borderColor='#2563eb';"
                      onblur="this.style.borderColor='#e5e7eb';"
                      oninput="autoResize(this)"></textarea>

            <button id="send-button" onclick="sendMessage()"
                    style="width:42px; height:42px; border-radius:10px; border:none; flex-shrink:0;
                           background:linear-gradient(135deg,#1e3a8a,#2563eb); color:#fff;
                           display:flex; align-items:center; justify-content:center; cursor:pointer;"
                    onmouseover="this.style.opacity='0.85';"
                    onmouseout="this.style.opacity='1';">
                <svg style="width:18px; height:18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
            </button>
        </div>
        <p style="font-size:11px; color:#d1d5db; margin-top:8px; text-align:center;">
            Nobel AI · Ответы могут содержать ошибки — проверяйте важную информацию
        </p>
    </div>
</div>

<style>
@keyframes bounce {
    0%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-6px); }
}
</style>

<script>
const chatBox     = document.getElementById('chat-box');
const userInput   = document.getElementById('user-input');
const sendBtn     = document.getElementById('send-button');
const typing      = document.getElementById('typing-indicator');
const csrfToken   = '{{ csrf_token() }}';

// Прокрутка вниз при загрузке если есть история
window.addEventListener('DOMContentLoaded', () => {
    chatBox.scrollTop = chatBox.scrollHeight;
});

// Enter отправляет, Shift+Enter — новая строка
userInput.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}

function sendMessage() {
    const message = userInput.value.trim();
    if (!message) return;

    const emptyState = document.getElementById('empty-state');
    if (emptyState) emptyState.remove();

    appendMessage('user', message);
    userInput.value = '';
    userInput.style.height = 'auto';
    sendBtn.disabled = true;
    typing.style.display = 'block';
    chatBox.scrollTop = chatBox.scrollHeight;

    fetch('/chatbot', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({ message }),
    })
    .then(r => r.json())
    .then(data => {
        typing.style.display = 'none';
        sendBtn.disabled = false;
        appendMessage('bot', data.reply || data.error || 'Нет ответа.');
    })
    .catch(() => {
        typing.style.display = 'none';
        sendBtn.disabled = false;
        appendMessage('bot', 'Ошибка соединения. Попробуйте позже.');
    });
}

function appendMessage(sender, text) {
    const wrap = document.createElement('div');
    wrap.style.cssText = `display:flex; justify-content:${sender === 'user' ? 'flex-end' : 'flex-start'}; margin-bottom:12px; gap:10px;`;

    if (sender === 'bot') {
        const avatar = document.createElement('div');
        avatar.style.cssText = 'width:30px;height:30px;border-radius:8px;flex-shrink:0;margin-top:2px;background:linear-gradient(135deg,#1e3a8a,#2563eb);display:flex;align-items:center;justify-content:center;';
        avatar.innerHTML = `<svg style="width:15px;height:15px;color:#fff;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>`;
        wrap.appendChild(avatar);
    }

    const bubble = document.createElement('div');
    bubble.style.cssText = sender === 'user'
        ? 'max-width:70%;background:#2563eb;color:#fff;padding:10px 14px;border-radius:14px 14px 4px 14px;font-size:14px;line-height:1.5;'
        : 'max-width:70%;background:#fff;color:#111827;padding:10px 14px;border-radius:14px 14px 14px 4px;font-size:14px;line-height:1.5;border:1px solid #e5e7eb;white-space:pre-wrap;';
    bubble.textContent = text;

    wrap.appendChild(bubble);
    chatBox.appendChild(wrap);
    chatBox.scrollTop = chatBox.scrollHeight;
}

function clearHistory() {
    if (!confirm('Очистить историю диалога?')) return;
    fetch('/chatbot/history', {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken },
    }).then(() => location.reload());
}
</script>

@endsection
