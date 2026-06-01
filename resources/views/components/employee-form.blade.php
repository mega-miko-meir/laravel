@props(['action', 'method' => 'POST', 'employee' => null, 'role' => collect(config('constants.roles'))->sort()->reverse()->toArray()])

@if($errors->any())
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:10px 14px;margin-bottom:16px;">
        @foreach($errors->all() as $error)
            <p style="color:#dc2626;font-size:13px;margin:0 0 2px;">{{ $error }}</p>
        @endforeach
    </div>
@endif

<form action="{{ $action }}" method="POST">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;
                box-shadow:0 1px 3px rgba(0,0,0,.05);">

        {{-- Основная информация --}}
        <div style="padding:20px 24px;border-bottom:1px solid #f0f0f0;">
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;
                       color:#9ca3af;margin:0 0 16px;">Основная информация</p>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">

                <div style="grid-column:span 2;">
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">
                        Полное ФИО <span style="color:#dc2626;">*</span>
                    </label>
                    <input name="full_name" type="text"
                           value="{{ old('full_name', $employee->full_name ?? '') }}"
                           placeholder="Иванов Иван Иванович"
                           style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;
                                  font-size:13px;outline:none;box-sizing:border-box;"
                           onfocus="this.style.borderColor='#2563eb';"
                           onblur="this.style.borderColor='#e5e7eb';">
                </div>

                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">
                        Имя
                    </label>
                    <input name="first_name" id="first_name" type="text"
                           value="{{ old('first_name', $employee->first_name ?? '') }}"
                           placeholder="Иван"
                           style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;
                                  font-size:13px;outline:none;box-sizing:border-box;"
                           onfocus="this.style.borderColor='#2563eb';"
                           onblur="this.style.borderColor='#e5e7eb';">
                </div>

                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">
                        Фамилия
                    </label>
                    <input name="last_name" id="last_name" type="text"
                           value="{{ old('last_name', $employee->last_name ?? '') }}"
                           placeholder="Иванов"
                           style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;
                                  font-size:13px;outline:none;box-sizing:border-box;"
                           onfocus="this.style.borderColor='#2563eb';"
                           onblur="this.style.borderColor='#e5e7eb';">
                </div>

            </div>
        </div>

        {{-- Контакты и даты --}}
        <div style="padding:20px 24px;border-bottom:1px solid #f0f0f0;">
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;
                       color:#9ca3af;margin:0 0 16px;">Контакты и даты</p>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">

                <div style="grid-column:span 2;">
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">
                        Email <span style="color:#dc2626;">*</span>
                    </label>
                    <input name="email" id="email" type="email"
                           value="{{ old('email', $employee->email ?? '') }}"
                           placeholder="ivan.ivanov@nobel.kz"
                           style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;
                                  font-size:13px;outline:none;box-sizing:border-box;"
                           onfocus="this.style.borderColor='#2563eb';"
                           onblur="this.style.borderColor='#e5e7eb';">
                </div>

                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">
                        Дата рождения
                    </label>
                    <input name="birth_date" type="date"
                           value="{{ old('birth_date', $employee->birth_date ?? '') }}"
                           style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;
                                  font-size:13px;outline:none;box-sizing:border-box;color:#374151;"
                           onfocus="this.style.borderColor='#2563eb';"
                           onblur="this.style.borderColor='#e5e7eb';">
                </div>

                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">
                        Дата найма
                    </label>
                    <input name="hiring_date" type="date"
                           value="{{ old('hiring_date', $employee->hiring_date ?? now()->format('Y-m-d')) }}"
                           style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;
                                  font-size:13px;outline:none;box-sizing:border-box;color:#374151;"
                           onfocus="this.style.borderColor='#2563eb';"
                           onblur="this.style.borderColor='#e5e7eb';">
                </div>

            </div>
        </div>

        {{-- Должность --}}
        <div style="padding:20px 24px;">
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;
                       color:#9ca3af;margin:0 0 16px;">Должность</p>

            <div style="max-width:280px;">
                <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">
                    Позиция
                </label>
                <select name="position"
                        style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;
                               font-size:13px;outline:none;background:#fff;box-sizing:border-box;color:#374151;"
                        onfocus="this.style.borderColor='#2563eb';"
                        onblur="this.style.borderColor='#e5e7eb';">
                    <option value="">— выберите —</option>
                    @foreach($role as $position)
                        <option value="{{ $position }}"
                            {{ old('position', $employee->position ?? '') === $position ? 'selected' : '' }}>
                            {{ $position }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

    </div>

    {{-- Кнопки --}}
    <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px;">
        <a href="/"
           style="padding:9px 20px;background:#fff;color:#374151;border:1px solid #e5e7eb;
                  border-radius:8px;font-size:13px;font-weight:500;text-decoration:none;"
           onmouseover="this.style.background='#f9fafb';"
           onmouseout="this.style.background='#fff';">
            Отмена
        </a>
        <button type="submit"
                style="padding:9px 20px;background:#2563eb;color:#fff;border:none;
                       border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;"
                onmouseover="this.style.background='#1d4ed8';"
                onmouseout="this.style.background='#2563eb';">
            {{ $employee ? 'Сохранить изменения' : 'Добавить сотрудника' }}
        </button>
    </div>

</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const firstInput = document.getElementById('first_name');
    const lastInput  = document.getElementById('last_name');
    const emailInput = document.getElementById('email');

    if (!firstInput || !lastInput || !emailInput) return;

    // Если при загрузке email уже заполнен (режим редактирования) — не перезаписывать
    let autoFill = emailInput.value.trim() === '';

    // Пользователь сам начал редактировать email — отключаем автозаполнение
    emailInput.addEventListener('input', () => { autoFill = false; });

    function generateEmail() {
        if (!autoFill) return;
        const first = firstInput.value.trim().toLowerCase();
        const last  = lastInput.value.trim().toLowerCase();
        if (first && last) emailInput.value = `${first}.${last}@nobel.kz`;
    }

    firstInput.addEventListener('input', generateEmail);
    lastInput.addEventListener('input', generateEmail);
});
</script>
