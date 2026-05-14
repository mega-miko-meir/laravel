@props(['employee', 'availableTablets', 'tabletHistories', 'lastTablet'])

@php $tablet = $tabletHistories->first(); @endphp

<div style="background:#fff;border-radius:12px;border:1px solid #f0f0f0;
            box-shadow:0 1px 3px rgba(0,0,0,.06);overflow:hidden;">

    {{-- Заголовок секции --}}
    <div style="padding:16px 20px;border-bottom:1px solid #f5f5f5;display:flex;align-items:center;gap:8px;">
        <svg style="width:16px;height:16px;color:#6b7280;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
        </svg>
        <span style="font-size:14px;font-weight:600;color:#1f2937;">Планшет</span>
    </div>

    <div style="padding:16px 20px;">

        @if($tablet && is_null($tablet->returned_at))
            {{-- ── Текущий планшет ── --}}
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;">

                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                    <div>
                        <p style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;
                                  color:#9ca3af;margin-bottom:4px;">Текущий планшет</p>
                        <a href="{{ route('tablets.show', $lastTablet->id) }}"
                           style="font-size:14px;font-weight:600;color:#2563eb;text-decoration:none;"
                           onmouseover="this.style.textDecoration='underline';"
                           onmouseout="this.style.textDecoration='none';">
                            {{ $lastTablet->invent_number }} — {{ $lastTablet->serial_number }}
                        </a>
                        <p style="font-size:11px;color:#9ca3af;margin-top:2px;">
                            с {{ \Carbon\Carbon::parse($lastTablet->latestAssignment->assigned_at)->format('d.m.Y') }}
                        </p>
                    </div>

                    {{-- Статус подтверждения --}}
                    @if($tablet->confirmed)
                        <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;
                                     background:#dcfce7;color:#15803d;border-radius:9999px;font-size:11px;font-weight:600;">
                            <svg style="width:11px;height:11px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                            Подтверждён
                        </span>
                    @endif
                </div>

                {{-- Кнопки действий --}}
                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-top:12px;
                            padding-top:12px;border-top:1px solid #e5e7eb;">

                    {{-- Печать --}}
                    <form action="/print-act/{{ $employee->id }}/{{ $tablet->tablet_id }}" method="POST">
                        @csrf
                        <button type="submit"
                                style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;
                                       background:#fff;color:#374151;border:1px solid #e5e7eb;border-radius:7px;
                                       font-size:11px;font-weight:600;cursor:pointer;"
                                onmouseover="this.style.background='#f9fafb';"
                                onmouseout="this.style.background='#fff';">
                            <svg style="width:12px;height:12px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Акт 1
                        </button>
                    </form>

                    <form action="/print-act2/{{ $employee->id }}/{{ $tablet->tablet_id }}" method="POST">
                        @csrf
                        <button type="submit"
                                style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;
                                       background:#fff;color:#374151;border:1px solid #e5e7eb;border-radius:7px;
                                       font-size:11px;font-weight:600;cursor:pointer;"
                                onmouseover="this.style.background='#f9fafb';"
                                onmouseout="this.style.background='#fff';">
                            <svg style="width:12px;height:12px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Акт 2
                        </button>
                    </form>

                    <x-pdf-upload-form :employee="$employee" :tablet="$tablet->tablet" :record="$tablet"/>

                    <x-unassign-tablet-button :employee="$employee" :tablet="$tablet->tablet"/>

                    @if(!$tablet->confirmed)
                        <form action="{{ route('confirm.tablet', [$employee->id, $tablet->tablet_id]) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;
                                           background:#fff;color:#16a34a;border:1px solid #86efac;border-radius:7px;
                                           font-size:11px;font-weight:600;cursor:pointer;"
                                    onmouseover="this.style.background='#f0fdf4';"
                                    onmouseout="this.style.background='#fff';">
                                <svg style="width:12px;height:12px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                Подтвердить
                            </button>
                        </form>
                    @endif
                </div>
            </div>

        @else
            {{-- ── Форма назначения ── --}}
            <p style="font-size:13px;color:#9ca3af;margin-bottom:12px;">Нет назначенного планшета</p>

            <div x-data="{
                showModal: false,
                employeeCity: '',
                responsibleCity: '',
                async checkAndSubmit(e) {
                    e.preventDefault();
                    const tabletId = document.getElementById('tablet_select_{{ $employee->id }}').value;
                    if (!tabletId) { e.target.submit(); return; }
                    const res  = await fetch('/api/city-check?employee_id={{ $employee->id }}&tablet_id=' + tabletId);
                    const data = await res.json();
                    if (!data.match && data.responsible_city) {
                        this.employeeCity    = data.employee_city ?? '—';
                        this.responsibleCity = data.responsible_city ?? '—';
                        this.showModal = true;
                    } else {
                        e.target.submit();
                    }
                }
            }">
                <form action="/assign-tablet/{{ $employee->id }}" method="POST"
                      style="display:flex;flex-direction:column;gap:8px;"
                      @submit="checkAndSubmit($event)">
                    @csrf

                    <div>
                        <label style="display:block;font-size:11px;font-weight:600;text-transform:uppercase;
                                      letter-spacing:.05em;color:#9ca3af;margin-bottom:4px;">Планшет</label>
                        <select id="tablet_select_{{ $employee->id }}" name="tablet_id"
                                style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:8px;
                                       font-size:13px;outline:none;background:#fff;color:#374151;">
                            <option value="">— выберите планшет —</option>
                            @foreach($availableTablets as $t)
                                <option value="{{ $t->id }}">
                                    {{ $t->invent_number }} — {{ $t->serial_number }}
                                    ({{ $t->latestAssignment?->employee?->sh_name ?? 'новый' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display:block;font-size:11px;font-weight:600;text-transform:uppercase;
                                      letter-spacing:.05em;color:#9ca3af;margin-bottom:4px;">Дата назначения</label>
                        <input type="date" name="assigned_at" value="{{ now()->format('Y-m-d') }}"
                               style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:8px;
                                      font-size:13px;outline:none;box-sizing:border-box;">
                    </div>

                    <button type="submit"
                            style="padding:8px 18px;background:#2563eb;color:#fff;border:none;
                                   border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;align-self:flex-start;"
                            onmouseover="this.style.background='#1d4ed8';"
                            onmouseout="this.style.background='#2563eb';">
                        Назначить
                    </button>
                </form>

                {{-- Модал города --}}
                <div x-show="showModal" x-cloak
                     style="position:fixed;inset:0;z-index:60;display:flex;align-items:center;
                            justify-content:center;background:rgba(0,0,0,.45);">
                    <div @click.outside="showModal=false"
                         style="background:#fff;border-radius:14px;padding:24px;max-width:340px;
                                width:100%;margin:0 16px;box-shadow:0 20px 60px rgba(0,0,0,.2);">
                        <p style="font-size:14px;font-weight:700;color:#111827;margin-bottom:8px;">
                            Города не совпадают
                        </p>
                        <p style="font-size:13px;color:#6b7280;line-height:1.6;margin-bottom:20px;">
                            Сотрудник: <strong x-text="employeeCity" style="color:#374151;"></strong><br>
                            Ответственный: <strong x-text="responsibleCity" style="color:#374151;"></strong><br>
                            Привязать всё равно?
                        </p>
                        <div style="display:flex;gap:10px;justify-content:flex-end;">
                            <button @click="showModal=false"
                                    style="padding:8px 16px;font-size:13px;color:#374151;background:#fff;
                                           border:1px solid #e5e7eb;border-radius:8px;cursor:pointer;">
                                Отмена
                            </button>
                            <button @click="showModal=false;$nextTick(()=>document.querySelector('form[action*=assign-tablet]').submit())"
                                    style="padding:8px 16px;font-size:13px;font-weight:600;color:#fff;
                                           background:#2563eb;border:none;border-radius:8px;cursor:pointer;">
                                Привязать
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- История планшетов --}}
        <div x-data="{ open: false }" style="margin-top:16px;">
            <button x-on:click="open = !open"
                    style="width:100%;display:flex;align-items:center;justify-content:space-between;
                           padding:10px 0;background:none;border:none;border-top:1px solid #f5f5f5;
                           cursor:pointer;">
                <span style="font-size:13px;font-weight:600;color:#374151;">История планшетов</span>
                <svg :class="{ 'rotate-180': open }"
                     style="width:16px;height:16px;color:#9ca3af;transition:transform .2s;"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <ul x-show="open" x-cloak style="margin:0;padding:0;list-style:none;">
                @forelse($tabletHistories as $history)
                    <li style="display:flex;align-items:center;justify-content:space-between;
                               padding:10px 0;border-bottom:1px solid #f9fafb;flex-wrap:wrap;gap:8px;">
                        <div>
                            <a href="{{ route('tablets.show', $history->tablet->id) }}"
                               style="font-size:13px;color:#2563eb;text-decoration:none;font-weight:500;"
                               onmouseover="this.style.textDecoration='underline';"
                               onmouseout="this.style.textDecoration='none';">
                                {{ $history->tablet?->serial_number ?? 'Неизвестный планшет' }}
                            </a>
                            <p style="font-size:11px;color:#9ca3af;margin-top:2px;">
                                {{ \Carbon\Carbon::parse($history->assigned_at)->format('d.m.Y') }}
                                —
                                {{ $history->returned_at
                                    ? \Carbon\Carbon::parse($history->returned_at)->format('d.m.Y')
                                    : 'сейчас' }}
                            </p>
                        </div>
                        <div style="display:flex;gap:8px;">
                            @if($history->pdf_path)
                                <a href="{{ asset('storage/'.$history->pdf_path) }}" target="_blank"
                                   style="font-size:11px;color:#2563eb;text-decoration:none;font-weight:600;"
                                   onmouseover="this.style.textDecoration='underline';"
                                   onmouseout="this.style.textDecoration='none';">
                                    Акт выдачи
                                </a>
                            @endif
                            @if($history->unassign_pdf)
                                <a href="{{ asset('storage/'.$history->unassign_pdf) }}" target="_blank"
                                   style="font-size:11px;color:#2563eb;text-decoration:none;font-weight:600;"
                                   onmouseover="this.style.textDecoration='underline';"
                                   onmouseout="this.style.textDecoration='none';">
                                    Акт возврата
                                </a>
                            @endif
                        </div>
                    </li>
                @empty
                    <li style="padding:12px 0;font-size:13px;color:#9ca3af;text-align:center;">
                        Нет истории
                    </li>
                @endforelse
            </ul>
        </div>

    </div>
</div>
