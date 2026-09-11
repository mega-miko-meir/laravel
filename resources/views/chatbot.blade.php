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
                        <div class="bot-bubble" data-raw="{{ $msg['content'] }}"
                             style="max-width:80%; background:#fff; color:#111827; padding:10px 14px;
                                    border-radius:14px 14px 14px 4px; font-size:14px; line-height:1.5;
                                    border:1px solid #e5e7eb;"></div>
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
    document.querySelectorAll('.bot-bubble[data-raw]').forEach(el => {
        renderMessageContent(el, el.dataset.raw);
    });
    chatBox.scrollTop = chatBox.scrollHeight;
});

// Копирование в буфер с фолбэком для не-HTTPS окружений (navigator.clipboard требует secure context)
function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
        return navigator.clipboard.writeText(text);
    }
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.left = '-9999px';
    document.body.appendChild(ta);
    ta.focus();
    ta.select();
    try { document.execCommand('copy'); } finally { document.body.removeChild(ta); }
    return Promise.resolve();
}

// Парсинг Markdown-таблицы: возвращает {header, rows} или null, если это не таблица
function parseMarkdownTable(block) {
    const lines = block.trim().split('\n').filter(l => l.trim() !== '');
    if (lines.length < 2) return null;
    const isRow = l => /^\s*\|.*\|\s*$/.test(l);
    if (!lines.every(isRow)) return null;
    if (!/^\s*\|?[\s:|-]+\|?\s*$/.test(lines[1])) return null;

    const parseRow = line => line.trim().replace(/^\|/, '').replace(/\|$/, '').split('|').map(c => c.trim());
    return { header: parseRow(lines[0]), rows: lines.slice(2).map(parseRow) };
}

function buildTableElement(table) {
    const wrap = document.createElement('div');
    wrap.style.cssText = 'margin:6px 0;overflow-x:auto;';

    const btn = document.createElement('button');
    btn.textContent = 'Скопировать таблицу';
    btn.style.cssText = 'font-size:11px;padding:4px 9px;margin-bottom:6px;border:1px solid #e5e7eb;' +
        'border-radius:6px;background:#f9fafb;color:#374151;cursor:pointer;';
    btn.onmouseover = () => btn.style.background = '#f0f4ff';
    btn.onmouseout  = () => btn.style.background = '#f9fafb';
    btn.onclick = () => {
        const tsv = [table.header, ...table.rows].map(r => r.join('\t')).join('\n');
        copyToClipboard(tsv).then(() => {
            const old = btn.textContent;
            btn.textContent = 'Скопировано! Вставьте в Excel (Ctrl+V)';
            setTimeout(() => { btn.textContent = old; }, 2000);
        });
    };
    wrap.appendChild(btn);

    const tableEl = document.createElement('table');
    tableEl.style.cssText = 'border-collapse:collapse;width:100%;font-size:13px;';

    const thead = document.createElement('thead');
    const trh = document.createElement('tr');
    table.header.forEach(h => {
        const th = document.createElement('th');
        th.textContent = h;
        th.style.cssText = 'border:1px solid #e5e7eb;padding:5px 9px;background:#f9fafb;text-align:left;white-space:nowrap;';
        trh.appendChild(th);
    });
    thead.appendChild(trh);
    tableEl.appendChild(thead);

    const tbody = document.createElement('tbody');
    table.rows.forEach(row => {
        const tr = document.createElement('tr');
        row.forEach(cell => {
            const td = document.createElement('td');
            td.textContent = cell;
            td.style.cssText = 'border:1px solid #e5e7eb;padding:5px 9px;';
            tr.appendChild(td);
        });
        tbody.appendChild(tr);
    });
    tableEl.appendChild(tbody);
    wrap.appendChild(tableEl);

    return wrap;
}

// Рендерит текст ответа бота в контейнер: markdown-таблицы -> <table>, остальное -> обычный текст
function renderMessageContent(container, text) {
    container.innerHTML = '';
    container.style.whiteSpace = 'normal';
    const lines = String(text).split('\n');
    const isTableLine = l => /^\s*\|.*\|\s*$/.test(l);
    let i = 0;

    while (i < lines.length) {
        if (isTableLine(lines[i])) {
            let j = i;
            while (j < lines.length && isTableLine(lines[j])) j++;
            const table = parseMarkdownTable(lines.slice(i, j).join('\n'));
            if (table) {
                container.appendChild(buildTableElement(table));
                i = j;
                continue;
            }
        }
        let j = i;
        while (j < lines.length && !isTableLine(lines[j])) j++;
        const textBlock = lines.slice(i, j).join('\n');
        if (textBlock.trim() !== '') {
            const p = document.createElement('div');
            p.style.whiteSpace = 'pre-wrap';
            p.textContent = textBlock;
            container.appendChild(p);
        }
        i = j;
    }
}

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
    if (sender === 'user') {
        bubble.style.cssText = 'max-width:70%;background:#2563eb;color:#fff;padding:10px 14px;border-radius:14px 14px 4px 14px;font-size:14px;line-height:1.5;white-space:pre-wrap;';
        bubble.textContent = text;
    } else {
        bubble.style.cssText = 'max-width:80%;background:#fff;color:#111827;padding:10px 14px;border-radius:14px 14px 14px 4px;font-size:14px;line-height:1.5;border:1px solid #e5e7eb;';
        renderMessageContent(bubble, text);
    }

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
