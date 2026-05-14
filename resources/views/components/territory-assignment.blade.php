@props(['employee', 'bricks', 'selectedBricks', 'availableTablets', 'availableTerritories', 'territoriesHistory', 'lastTerritory'])

<div style="background:#fff;border-radius:12px;border:1px solid #f0f0f0;
            box-shadow:0 1px 3px rgba(0,0,0,.06);overflow:hidden;">

    {{-- Заголовок --}}
    <div style="padding:16px 20px;border-bottom:1px solid #f5f5f5;display:flex;align-items:center;gap:8px;">
        <svg style="width:16px;height:16px;color:#6b7280;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
        </svg>
        <span style="font-size:14px;font-weight:600;color:#1f2937;">Территория</span>
    </div>

    <div style="padding:16px 20px;">

        @if($lastTerritory && is_null(optional($lastTerritory->pivot)->unassigned_at))

            {{-- ── Текущая территория ── --}}
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;">

                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                    <div>
                        <p style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;
                                  color:#9ca3af;margin-bottom:4px;">Текущая территория</p>
                        <a href="{{ route('territories.show', $lastTerritory->id) }}"
                           style="font-size:14px;font-weight:600;color:#2563eb;text-decoration:none;"
                           onmouseover="this.style.textDecoration='underline';"
                           onmouseout="this.style.textDecoration='none';">
                            {{ $lastTerritory->territory_name }}
                        </a>
                        @if($lastTerritory->role)
                            <span style="display:inline-block;margin-left:8px;padding:2px 8px;background:#ede9fe;
                                         color:#6d28d9;border-radius:9999px;font-size:11px;font-weight:600;">
                                {{ $lastTerritory->role }}
                            </span>
                        @endif
                    </div>

                    @if($lastTerritory->pivot->confirmed)
                        <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;
                                     background:#dcfce7;color:#15803d;border-radius:9999px;font-size:11px;font-weight:600;">
                            <svg style="width:11px;height:11px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                            Подтверждено
                        </span>
                    @endif
                </div>

                {{-- Кнопки действий --}}
                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-top:12px;
                            padding-top:12px;border-top:1px solid #e5e7eb;">

                    {{-- Снятие с территории --}}
                    <form action="/unassign-territory/{{ $employee->id }}/{{ $lastTerritory->id }}" method="POST"
                          style="display:flex;align-items:center;gap:6px;"
                          x-data x-on:submit.prevent="if(confirm('Снять с территории?')) $el.submit()">
                        @csrf
                        <input type="date" name="unassigned_at" value="{{ now()->format('Y-m-d') }}"
                               style="font-size:11px;border:1px solid #e5e7eb;border-radius:7px;
                                      padding:4px 8px;outline:none;color:#374151;">
                        <button type="submit"
                                style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;
                                       background:#fff;color:#ef4444;border:1px solid #fca5a5;border-radius:7px;
                                       font-size:11px;font-weight:600;cursor:pointer;"
                                onmouseover="this.style.background='#fef2f2';"
                                onmouseout="this.style.background='#fff';">
                            <svg style="width:11px;height:11px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Снять
                        </button>
                    </form>

                    {{-- OCE шаблон --}}
                    <form action="/form-template/{{ $employee->id }}" method="POST"
                          x-data x-on:submit.prevent="if(confirm('Создать OCE шаблон?')) $el.submit()">
                        @csrf
                        <button type="submit"
                                style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;
                                       background:#fff;color:#374151;border:1px solid #e5e7eb;border-radius:7px;
                                       font-size:11px;font-weight:600;cursor:pointer;"
                                onmouseover="this.style.background='#f9fafb';"
                                onmouseout="this.style.background='#fff';">
                            <svg style="width:11px;height:11px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            OCE
                        </button>
                    </form>

                    {{-- Подтверждение --}}
                    @if(!$lastTerritory->pivot->confirmed)
                        <form action="{{ route('confirm.territory', [$employee->id, $lastTerritory->id]) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;
                                           background:#fff;color:#16a34a;border:1px solid #86efac;border-radius:7px;
                                           font-size:11px;font-weight:600;cursor:pointer;"
                                    onmouseover="this.style.background='#f0fdf4';"
                                    onmouseout="this.style.background='#fff';">
                                <svg style="width:11px;height:11px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                Подтвердить
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Брики / дочерние территории --}}
            <div style="margin-top:14px;">
                @if($lastTerritory->role === 'Rep')
                    <x-checkbox :employee="$employee" :bricks="$bricks"
                                :selectedBricks="$selectedBricks" :territory="$lastTerritory"/>
                @else
                    <x-child-territories :territory="$lastTerritory" />
                @endif
            </div>

        @else

            {{-- ── Форма назначения ── --}}
            <p style="font-size:13px;color:#9ca3af;margin-bottom:12px;">Нет назначенной территории</p>

            <form action="/assign-territory/{{ $employee->id }}" method="POST"
                  style="display:flex;flex-direction:column;gap:8px;">
                @csrf
                <div>
                    <label style="display:block;font-size:11px;font-weight:600;text-transform:uppercase;
                                  letter-spacing:.05em;color:#9ca3af;margin-bottom:4px;">Территория</label>
                    <select name="territory_id"
                            style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:8px;
                                   font-size:13px;outline:none;background:#fff;color:#374151;">
                        <option value="">— выберите территорию —</option>
                        @foreach($availableTerritories as $territory)
                            @php
                                $lastEmp = $territory->employeeTerritories
                                    ->sortByDesc('assigned_at')->first()?->employee;
                            @endphp
                            <option value="{{ $territory->id }}">
                                {{ $territory->territory_name }}
                                {{ $territory->parent?->employee
                                    ? '— ' . $territory->parent->employee->sh_name
                                    : '' }}
                                {{ $lastEmp ? '(был: ' . $lastEmp->sh_name . ')' : '' }}
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

        @endif

        {{-- История территорий --}}
        <div x-data="{ open: false }" style="margin-top:16px;">
            <button x-on:click="open = !open"
                    style="width:100%;display:flex;align-items:center;justify-content:space-between;
                           padding:10px 0;background:none;border:none;border-top:1px solid #f5f5f5;
                           cursor:pointer;">
                <span style="font-size:13px;font-weight:600;color:#374151;">История территорий</span>
                <svg :class="{ 'rotate-180': open }"
                     style="width:16px;height:16px;color:#9ca3af;transition:transform .2s;flex-shrink:0;"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <ul x-show="open" x-cloak style="margin:0;padding:0;list-style:none;">
                @forelse($territoriesHistory as $history)
                    <li style="display:flex;align-items:center;justify-content:space-between;
                               padding:10px 0;border-bottom:1px solid #f9fafb;flex-wrap:wrap;gap:8px;">
                        <div>
                            <a href="{{ route('territories.show', $history->id) }}"
                               style="font-size:13px;color:#2563eb;text-decoration:none;font-weight:500;"
                               onmouseover="this.style.textDecoration='underline';"
                               onmouseout="this.style.textDecoration='none';">
                                {{ $history->territory_name ?? 'Неизвестная территория' }}
                            </a>
                            <p style="font-size:11px;color:#9ca3af;margin-top:2px;">
                                {{ \Carbon\Carbon::parse($history->pivot->assigned_at)->format('d.m.Y') }}
                                —
                                {{ $history->pivot->unassigned_at
                                    ? \Carbon\Carbon::parse($history->pivot->unassigned_at)->format('d.m.Y')
                                    : 'сейчас' }}
                            </p>
                        </div>
                    </li>
                @empty
                    <li style="padding:12px 0;font-size:13px;color:#9ca3af;text-align:center;">Нет истории</li>
                @endforelse
            </ul>
        </div>

    </div>
</div>
