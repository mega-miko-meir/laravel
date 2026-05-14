@props(['employee'])

<div x-data="{ open: false, formOpen: false }">

    {{-- Заголовок секции (кликабельный) --}}
    <button x-on:click="open = !open"
            style="width:100%;display:flex;align-items:center;justify-content:space-between;
                   padding:16px 20px;background:none;border:none;cursor:pointer;text-align:left;"
            onmouseover="this.style.background='#f9fafb';"
            onmouseout="this.style.background='none';">
        <div style="display:flex;align-items:center;gap:8px;">
            <svg style="width:15px;height:15px;color:#6b7280;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
            <span style="font-size:14px;font-weight:600;color:#1f2937;">Учётные данные</span>
        </div>
        <svg :class="{ 'rotate-180': open }"
             style="width:16px;height:16px;color:#9ca3af;transition:transform .2s;flex-shrink:0;"
             fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-show="open" x-cloak style="padding:0 20px 16px;">

        {{-- Список учётных данных --}}
        @if($employee->credentials->isNotEmpty())
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;margin-bottom:14px;">
                @foreach($employee->credentials as $credential)
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px;position:relative;">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                            <span style="font-size:10px;font-weight:700;text-transform:uppercase;
                                         letter-spacing:.08em;color:#4f46e5;background:#eef2ff;
                                         padding:2px 8px;border-radius:9999px;">
                                {{ $credential->system }}
                            </span>
                            <button onclick="deleteCredential({{ $credential->id }})"
                                    style="width:22px;height:22px;display:flex;align-items:center;justify-content:center;
                                           background:none;border:none;cursor:pointer;color:#d1d5db;border-radius:5px;"
                                    onmouseover="this.style.color='#ef4444';this.style.background='#fef2f2';"
                                    onmouseout="this.style.color='#d1d5db';this.style.background='none';">
                                <svg style="width:13px;height:13px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                        @if($credential->user_name)
                            <p style="font-size:11px;color:#9ca3af;margin-bottom:2px;">Имя</p>
                            <p style="font-size:12px;color:#374151;font-weight:500;margin-bottom:6px;">{{ $credential->user_name }}</p>
                        @endif
                        @if($credential->login)
                            <p style="font-size:11px;color:#9ca3af;margin-bottom:2px;">Логин</p>
                            <p style="font-size:12px;font-family:monospace;color:#374151;margin-bottom:6px;">{{ $credential->login }}</p>
                        @endif
                        @if($credential->password)
                            <p style="font-size:11px;color:#9ca3af;margin-bottom:2px;">Пароль</p>
                            <p style="font-size:12px;font-family:monospace;color:#dc2626;font-weight:600;">{{ $credential->password }}</p>
                        @endif
                        @if($credential->add_password)
                            <p style="font-size:11px;color:#9ca3af;margin-top:4px;margin-bottom:2px;">Доп. пароль</p>
                            <p style="font-size:12px;font-family:monospace;color:#dc2626;">{{ $credential->add_password }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <p style="font-size:13px;color:#9ca3af;margin-bottom:12px;">Нет учётных данных</p>
        @endif

        {{-- Кнопка добавить --}}
        <button @click="formOpen = !formOpen"
                style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;
                       background:#fff;color:#374151;border:1px solid #e5e7eb;border-radius:8px;
                       font-size:12px;font-weight:600;cursor:pointer;"
                onmouseover="this.style.background='#f9fafb';"
                onmouseout="this.style.background='#fff';">
            <svg style="width:13px;height:13px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span x-text="formOpen ? 'Свернуть' : 'Добавить / изменить'"></span>
        </button>

        {{-- Форма --}}
        <div x-show="formOpen" x-cloak
             style="margin-top:12px;background:#f8fafc;border:1px solid #e2e8f0;
                    border-radius:10px;padding:16px;">
            <form action="{{ route('employees.updateCredentials', $employee->id) }}" method="POST">
                @csrf
                @method('PUT')

                @php
                    $inputStyle = 'width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;background:#fff;box-sizing:border-box;';
                    $labelStyle = 'display:block;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#9ca3af;margin-bottom:4px;';
                @endphp

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">

                    <div style="grid-column:span 2;">
                        <label style="{{ $labelStyle }}">Система</label>
                        <select name="system" id="cred_system_{{ $employee->id }}"
                                style="{{ $inputStyle }}"
                                onchange="credAutoFill_{{ $employee->id }}()">
                            <option value="">— выберите —</option>
                            <option value="crm">CRM</option>
                            <option value="tablet">Планшет</option>
                            <option value="kmp">КМП</option>
                        </select>
                    </div>

                    <div>
                        <label style="{{ $labelStyle }}">Имя пользователя</label>
                        <input type="text" name="user_name" id="cred_username_{{ $employee->id }}" style="{{ $inputStyle }}">
                    </div>

                    <div>
                        <label style="{{ $labelStyle }}">Логин</label>
                        <input type="text" name="login" id="cred_login_{{ $employee->id }}" style="{{ $inputStyle }}">
                    </div>

                    <div>
                        <label style="{{ $labelStyle }}">Пароль</label>
                        <input type="text" name="password" style="{{ $inputStyle }}">
                    </div>

                    <div>
                        <label style="{{ $labelStyle }}">Доп. пароль</label>
                        <input type="text" name="add_password" style="{{ $inputStyle }}">
                    </div>
                </div>

                <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:14px;">
                    <button type="button" @click="formOpen = false"
                            style="padding:7px 16px;font-size:13px;color:#374151;background:#fff;
                                   border:1px solid #e5e7eb;border-radius:8px;cursor:pointer;">
                        Отмена
                    </button>
                    <button type="submit"
                            style="padding:7px 16px;font-size:13px;font-weight:600;color:#fff;
                                   background:#2563eb;border:none;border-radius:8px;cursor:pointer;"
                            onmouseover="this.style.background='#1d4ed8';"
                            onmouseout="this.style.background='#2563eb';">
                        Сохранить
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function credAutoFill_{{ $employee->id }}() {
    const system    = document.getElementById('cred_system_{{ $employee->id }}').value;
    const username  = document.getElementById('cred_username_{{ $employee->id }}');
    const login     = document.getElementById('cred_login_{{ $employee->id }}');
    const firstName = @json($employee->first_name);
    const lastName  = @json($employee->last_name);
    const email     = @json($employee->email);
    const fullName  = @json($employee->full_name);
    const parts     = fullName.split(' ');
    const kmpName   = parts.length > 2 ? parts.slice(0, 2).join(' ') : fullName;

    if (system === 'crm')       { username.value = firstName + ' ' + lastName; login.value = email; }
    else if (system === 'kmp')  { username.value = kmpName; login.value = ''; }
    else                        { username.value = ''; login.value = ''; }
}

function deleteCredential(id) {
    if (!confirm('Удалить учётные данные?')) return;
    fetch('/employees/credentials/' + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    }).then(() => location.reload());
}
</script>
