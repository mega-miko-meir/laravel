@props([
    'action',
    'method'      => 'POST',
    'tablet'      => null,
    'responsibles',
])

@php
    $statuses = [
        'new'        => 'Новый',
        'active'     => 'Активен',
        'lost'       => 'Потерян',
        'damaged'    => 'Повреждён',
        'written-off'=> 'Списан',
        'admin'      => 'Админ',
    ];
    $currentStatus = old('status', $tablet->status ?? 'new');
@endphp

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

                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">
                        Статус <span style="color:#dc2626;">*</span>
                    </label>
                    <select name="status"
                            style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;
                                   font-size:13px;outline:none;background:#fff;box-sizing:border-box;color:#374151;"
                            onfocus="this.style.borderColor='#2563eb';"
                            onblur="this.style.borderColor='#e5e7eb';">
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ $currentStatus === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">
                        Модель
                    </label>
                    <input name="model" type="text"
                           value="{{ old('model', $tablet->model ?? '') }}"
                           placeholder="Например: iPad 10"
                           style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;
                                  font-size:13px;outline:none;box-sizing:border-box;"
                           onfocus="this.style.borderColor='#2563eb';"
                           onblur="this.style.borderColor='#e5e7eb';">
                </div>

            </div>
        </div>

        {{-- Идентификаторы --}}
        <div style="padding:20px 24px;border-bottom:1px solid #f0f0f0;">
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;
                       color:#9ca3af;margin:0 0 16px;">Идентификаторы</p>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">

                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">
                        Инвентарный номер
                    </label>
                    <input name="invent_number" type="text"
                           value="{{ old('invent_number', $tablet->invent_number ?? '') }}"
                           placeholder="INV-0001"
                           style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;
                                  font-size:13px;outline:none;box-sizing:border-box;"
                           onfocus="this.style.borderColor='#2563eb';"
                           onblur="this.style.borderColor='#e5e7eb';">
                </div>

                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">
                        Серийный номер
                    </label>
                    <input name="serial_number" type="text"
                           value="{{ old('serial_number', $tablet->serial_number ?? '') }}"
                           placeholder="SN-XXXXXXXXXXXX"
                           style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;
                                  font-size:13px;outline:none;box-sizing:border-box;"
                           onfocus="this.style.borderColor='#2563eb';"
                           onblur="this.style.borderColor='#e5e7eb';">
                </div>

                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">
                        IMEI
                    </label>
                    <input name="imei" type="text"
                           value="{{ old('imei', $tablet->imei ?? '') }}"
                           placeholder="000000000000000"
                           style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;
                                  font-size:13px;outline:none;box-sizing:border-box;"
                           onfocus="this.style.borderColor='#2563eb';"
                           onblur="this.style.borderColor='#e5e7eb';">
                </div>

                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">
                        Номер Beeline
                    </label>
                    <input name="beeline_number" type="text"
                           value="{{ old('beeline_number', $tablet->beeline_number ?? '') }}"
                           placeholder="+7 XXX XXX XX XX"
                           style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;
                                  font-size:13px;outline:none;box-sizing:border-box;"
                           onfocus="this.style.borderColor='#2563eb';"
                           onblur="this.style.borderColor='#e5e7eb';">
                </div>

            </div>
        </div>

        {{-- Ответственный --}}
        <div style="padding:20px 24px;">
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;
                       color:#9ca3af;margin:0 0 16px;">Ответственный</p>

            <div>
                <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">
                    Ответственное лицо
                </label>
                <select name="responsible_id"
                        style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;
                               font-size:13px;outline:none;background:#fff;box-sizing:border-box;color:#374151;"
                        onfocus="this.style.borderColor='#2563eb';"
                        onblur="this.style.borderColor='#e5e7eb';">
                    <option value="">— не выбрано —</option>
                    @foreach($responsibles as $employee)
                        <option value="{{ $employee->id }}"
                            {{ old('responsible_id', $tablet->responsible_id ?? '') == $employee->id ? 'selected' : '' }}>
                            {{ $employee->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

    </div>

    {{-- Кнопки --}}
    <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px;">
        <a href="/tablets"
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
            {{ $tablet ? 'Сохранить изменения' : 'Добавить планшет' }}
        </button>
    </div>

</form>
