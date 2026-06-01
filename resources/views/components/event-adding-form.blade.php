@props(['employee'])

@php
    $lastEventType = optional($employee->events()->latest('event_date')->first())->event_type;
    $events = [
        'hired'             => 'Принят',
        'dismissed'         => 'Уволен',
        'return_from_leave' => 'Вернулся из отпуска',
        'maternity_leave'   => 'Декретный отпуск',
        'change_position'   => 'Смена должности',
        'long_vacation'     => 'Длительный отпуск',
    ];
@endphp

<div id="editForm"
     style="display:none;margin-top:10px;padding:14px;border-radius:10px;
            background:#f9fafb;border:1px solid #e5e7eb;">

    <form action="{{ route('employees.updateStatusAndEvent', $employee->id) }}" method="POST"
          onsubmit="return confirm('Добавить событие и обновить статус?');">
        @csrf
        @method('PUT')

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">

            <div>
                <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;
                               text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">
                    Событие
                </label>
                <select name="event_type"
                        style="width:100%;padding:7px 10px;border:1.5px solid #e5e7eb;border-radius:7px;
                               font-size:12px;outline:none;background:#fff;color:#374151;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#2563eb';"
                        onblur="this.style.borderColor='#e5e7eb';">
                    @foreach($events as $val => $lbl)
                        <option value="{{ $val }}" {{ $lastEventType === $val ? 'selected' : '' }}>
                            {{ $lbl }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;
                               text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">
                    Дата
                </label>
                <input type="date" name="event_date"
                       value="{{ now()->format('Y-m-d') }}"
                       style="width:100%;padding:7px 10px;border:1.5px solid #e5e7eb;border-radius:7px;
                              font-size:12px;outline:none;color:#374151;box-sizing:border-box;"
                       onfocus="this.style.borderColor='#2563eb';"
                       onblur="this.style.borderColor='#e5e7eb';">
            </div>

        </div>

        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:10px;">
            <button type="button" onclick="toggleEditForm()"
                    style="padding:6px 14px;font-size:12px;font-weight:500;background:#fff;
                           color:#374151;border:1px solid #e5e7eb;border-radius:7px;cursor:pointer;"
                    onmouseover="this.style.background='#f3f4f6';"
                    onmouseout="this.style.background='#fff';">
                Отмена
            </button>
            <button type="submit"
                    style="padding:6px 14px;font-size:12px;font-weight:600;background:#2563eb;
                           color:#fff;border:none;border-radius:7px;cursor:pointer;"
                    onmouseover="this.style.background='#1d4ed8';"
                    onmouseout="this.style.background='#2563eb';">
                Сохранить
            </button>
        </div>

    </form>
</div>
