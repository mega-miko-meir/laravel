@extends('layout')
@section('content')

{{-- Модалка сброса пароля --}}
<div id="reset-modal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:100;
            align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;padding:24px;width:360px;
                box-shadow:0 8px 32px rgba(0,0,0,.15);position:relative;">
        <h3 style="font-size:15px;font-weight:700;color:#111827;margin:0 0 6px;">Пароль сброшен</h3>
        <p style="font-size:12px;color:#6b7280;margin:0 0 16px;">Скопируйте данные и передайте пользователю</p>

        <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:12px 14px;margin-bottom:14px;">
            <p style="font-size:12px;color:#6b7280;margin:0 0 4px;font-weight:600;">Логин</p>
            <p id="modal-login" style="font-size:13px;color:#111827;margin:0;word-break:break-all;"></p>
        </div>

        <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:12px 14px;margin-bottom:20px;">
            <p style="font-size:12px;color:#6b7280;margin:0 0 4px;font-weight:600;">Пароль</p>
            <p id="modal-password" style="font-size:13px;color:#111827;margin:0;font-family:monospace;"></p>
        </div>

        <div style="display:flex;gap:8px;">
            <button onclick="copyCredentials()"
                    id="copy-btn"
                    style="flex:1;padding:9px;background:#2563eb;color:#fff;border:none;
                           border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;"
                    onmouseover="this.style.background='#1d4ed8';"
                    onmouseout="if(!this.dataset.copied)this.style.background='#2563eb';">
                Скопировать
            </button>
            <button onclick="closeResetModal()"
                    style="flex:1;padding:9px;background:#fff;color:#374151;border:1px solid #e5e7eb;
                           border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;"
                    onmouseover="this.style.background='#f9fafb';"
                    onmouseout="this.style.background='#fff';">
                Закрыть
            </button>
        </div>
    </div>
</div>

<script>
function openResetModal(userId, userName) {
    if (!confirm('Сбросить пароль пользователя ' + userName + '?')) return;

    fetch('/users/' + userId + '/reset-password', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('modal-login').textContent = data.login;
        document.getElementById('modal-password').textContent = data.password;
        const btn = document.getElementById('copy-btn');
        btn.textContent = 'Скопировать';
        btn.style.background = '#2563eb';
        delete btn.dataset.copied;
        const modal = document.getElementById('reset-modal');
        modal.style.display = 'flex';
    })
    .catch(() => alert('Ошибка при сбросе пароля'));
}

function copyCredentials() {
    const login = document.getElementById('modal-login').textContent;
    const password = document.getElementById('modal-password').textContent;
    navigator.clipboard.writeText('Логин: ' + login + '\nПароль: ' + password).then(() => {
        const btn = document.getElementById('copy-btn');
        btn.textContent = 'Скопировано!';
        btn.style.background = '#16a34a';
        btn.dataset.copied = '1';
    });
}

function closeResetModal() {
    document.getElementById('reset-modal').style.display = 'none';
}
</script>

{{-- Тулбар --}}
<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:20px;margin-top:24px;">

    <h1 style="font-size:20px;font-weight:700;color:#111827;margin:0;">
        Пользователи
        <span style="font-size:13px;font-weight:500;color:#9ca3af;margin-left:6px;">{{ count($users) }}</span>
    </h1>

    <a href="/register"
       style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;
              background:#2563eb;color:#fff;border:none;border-radius:8px;
              font-size:13px;font-weight:600;text-decoration:none;"
       onmouseover="this.style.background='#1d4ed8';"
       onmouseout="this.style.background='#2563eb';">
        <svg style="width:15px;height:15px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Добавить
    </a>
</div>

@if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:10px 14px;
                margin-bottom:16px;font-size:13px;color:#16a34a;">
        {{ session('success') }}
    </div>
@endif

{{-- Таблица --}}
<div style="background:#fff;border:1px solid #f0f0f0;border-radius:12px;overflow:hidden;
            box-shadow:0 1px 3px rgba(0,0,0,.05);">
    <table style="width:100%;border-collapse:collapse;font-size:12px;">
        <thead>
            <tr style="background:#f9fafb;border-bottom:1px solid #f0f0f0;">
                <th style="padding:10px 16px;text-align:center;font-size:10px;font-weight:600;
                           text-transform:uppercase;letter-spacing:.05em;color:#6b7280;width:44px;">№</th>
                <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;
                           text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">ФИО</th>
                <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;
                           text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">Email</th>
                <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;
                           text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">Позиция</th>
                <th style="padding:10px 16px;text-align:center;font-size:10px;font-weight:600;
                           text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">Роль</th>
                <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;
                           text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">Создан</th>
                <th style="padding:10px 16px;text-align:center;font-size:10px;font-weight:600;
                           text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">Действия</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $index => $user)
                <tr style="border-top:1px solid #f5f5f5;"
                    onmouseover="this.style.background='#fafafa';"
                    onmouseout="this.style.background='none';">

                    <td style="padding:9px 16px;text-align:center;color:#9ca3af;font-size:11px;">
                        {{ $index + 1 }}
                    </td>

                    <td style="padding:9px 16px;">
                        <a href="{{ route('users.show', $user->id) }}"
                           style="color:#111827;font-weight:500;text-decoration:none;"
                           onmouseover="this.style.color='#2563eb';"
                           onmouseout="this.style.color='#111827';">
                            {{ $user->full_name }}
                        </a>
                    </td>

                    <td style="padding:9px 16px;color:#6b7280;">
                        {{ $user->email }}
                    </td>

                    <td style="padding:9px 16px;color:#374151;">
                        {{ $user->position ?? '—' }}
                    </td>

                    <td style="padding:9px 16px;text-align:center;">
                        @php
                            $roleColors = [
                                'admin'  => 'background:#fef3c7;color:#92400e;',
                                'editor' => 'background:#eff6ff;color:#1d4ed8;',
                                'viewer' => 'background:#f0fdf4;color:#166534;',
                            ];
                            $roleName  = $user->role->name ?? '';
                            $roleStyle = $roleColors[$roleName] ?? 'background:#f3f4f6;color:#374151;';
                        @endphp
                        <span style="padding:2px 10px;border-radius:9999px;font-size:10px;
                                     font-weight:700;letter-spacing:.04em;{{ $roleStyle }}">
                            {{ $roleName }}
                        </span>
                    </td>

                    <td style="padding:9px 16px;color:#9ca3af;white-space:nowrap;">
                        {{ $user->created_at->format('d.m.Y') }}
                    </td>

                    <td style="padding:9px 16px;text-align:center;">
                        <div style="display:inline-flex;align-items:center;gap:4px;">

                            {{-- Редактировать --}}
                            <a href="{{ route('users.edit', $user->id) }}"
                               style="width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;
                                      border-radius:6px;color:#6b7280;text-decoration:none;"
                               onmouseover="this.style.background='#eff6ff';this.style.color='#2563eb';"
                               onmouseout="this.style.background='none';this.style.color='#6b7280';"
                               title="Редактировать">
                                <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>

                            {{-- Сброс пароля --}}
                            <button type="button"
                                    onclick="openResetModal({{ $user->id }}, '{{ addslashes($user->full_name) }}')"
                                    style="width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;
                                           border-radius:6px;color:#6b7280;background:none;border:none;cursor:pointer;"
                                    onmouseover="this.style.background='#fffbeb';this.style.color='#d97706';"
                                    onmouseout="this.style.background='none';this.style.color='#6b7280';"
                                    title="Сбросить пароль">
                                <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                </svg>
                            </button>

                            {{-- Удалить --}}
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                  onsubmit="return confirm('Удалить пользователя {{ $user->full_name }}?')"
                                  style="margin:0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        style="width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;
                                               border-radius:6px;color:#6b7280;background:none;border:none;cursor:pointer;"
                                        onmouseover="this.style.background='#fef2f2';this.style.color='#dc2626';"
                                        onmouseout="this.style.background='none';this.style.color='#6b7280';"
                                        title="Удалить">
                                    <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:40px 16px;color:#9ca3af;font-size:13px;">
                        Нет пользователей
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
