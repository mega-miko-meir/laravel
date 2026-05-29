@extends('layout')
@section('content')

<div style="max-width:600px;margin:32px auto 0;">

    {{-- Заголовок --}}
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
        <a href="/users"
           style="width:32px;height:32px;display:inline-flex;align-items:center;justify-content:center;
                  border-radius:8px;color:#6b7280;text-decoration:none;border:1px solid #e5e7eb;"
           onmouseover="this.style.background='#f9fafb';"
           onmouseout="this.style.background='#fff';">
            <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 style="font-size:20px;font-weight:700;color:#111827;margin:0;">Редактировать пользователя</h1>
    </div>

    @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:10px 14px;margin-bottom:16px;">
            @foreach($errors->all() as $error)
                <p style="color:#dc2626;font-size:13px;margin:0;">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;
                    box-shadow:0 1px 3px rgba(0,0,0,.05);">

            {{-- Личные данные --}}
            <div style="padding:20px 24px;border-bottom:1px solid #f0f0f0;">
                <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;
                           color:#9ca3af;margin:0 0 16px;">Личные данные</p>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">

                    <div style="grid-column:span 2;">
                        <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">
                            Полное ФИО
                        </label>
                        <input name="full_name" type="text" value="{{ old('full_name', $user->full_name) }}"
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
                        <input name="first_name" type="text" value="{{ old('first_name', $user->first_name) }}"
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
                        <input name="last_name" type="text" value="{{ old('last_name', $user->last_name) }}"
                               placeholder="Иванов"
                               style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;
                                      font-size:13px;outline:none;box-sizing:border-box;"
                               onfocus="this.style.borderColor='#2563eb';"
                               onblur="this.style.borderColor='#e5e7eb';">
                    </div>

                    <div>
                        <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">
                            Должность
                        </label>
                        <input name="position" type="text" value="{{ old('position', $user->position) }}"
                               placeholder="Менеджер"
                               style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;
                                      font-size:13px;outline:none;box-sizing:border-box;"
                               onfocus="this.style.borderColor='#2563eb';"
                               onblur="this.style.borderColor='#e5e7eb';">
                    </div>

                    <div>
                        <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">
                            Роль <span style="color:#dc2626;">*</span>
                        </label>
                        <select name="role_id" required
                                style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;
                                       font-size:13px;outline:none;background:#fff;box-sizing:border-box;"
                                onfocus="this.style.borderColor='#2563eb';"
                                onblur="this.style.borderColor='#e5e7eb';">
                            <option value="">— выберите —</option>
                            @foreach(App\Models\Role::all() as $role)
                                <option value="{{ $role->id }}"
                                    {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div style="grid-column:span 2;">
                        <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">
                            Email <span style="color:#dc2626;">*</span>
                        </label>
                        <input name="email" type="email" value="{{ old('email', $user->email) }}"
                               placeholder="email@nobel.kz"
                               style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;
                                      font-size:13px;outline:none;box-sizing:border-box;"
                               onfocus="this.style.borderColor='#2563eb';"
                               onblur="this.style.borderColor='#e5e7eb';">
                    </div>
                </div>
            </div>

            {{-- Пароль --}}
            <div style="padding:20px 24px;">
                <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;
                           color:#9ca3af;margin:0 0 4px;">Пароль</p>
                <p style="font-size:12px;color:#9ca3af;margin:0 0 16px;">Оставьте пустым, чтобы не менять пароль</p>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">
                            Новый пароль
                        </label>
                        <input name="password" type="password"
                               placeholder="Минимум 6 символов"
                               style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;
                                      font-size:13px;outline:none;box-sizing:border-box;"
                               onfocus="this.style.borderColor='#2563eb';"
                               onblur="this.style.borderColor='#e5e7eb';">
                    </div>
                    <div>
                        <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">
                            Подтверждение
                        </label>
                        <input name="password_confirmation" type="password"
                               placeholder="Повторите пароль"
                               style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;
                                      font-size:13px;outline:none;box-sizing:border-box;"
                               onfocus="this.style.borderColor='#2563eb';"
                               onblur="this.style.borderColor='#e5e7eb';">
                    </div>
                </div>
            </div>
        </div>

        {{-- Кнопки --}}
        <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px;">
            <a href="/users"
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
                Сохранить изменения
            </button>
        </div>
    </form>
</div>

@endsection
