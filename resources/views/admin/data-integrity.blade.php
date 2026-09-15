@extends('layout')

@section('content')

<style>
    [x-cloak] { display:none !important; }
    .di-tab { border:1px solid #d1d5db; border-radius:8px; padding:9px 20px; font-size:14px; font-weight:600; cursor:pointer; background:#fff; color:#374151; display:inline-flex; align-items:center; gap:8px; }
    .di-tab-active { background:#1e3a8a; color:#fff; border-color:#1e3a8a; }
    .di-tab-badge { display:inline-flex; align-items:center; padding:1px 8px; border-radius:9999px; font-size:12px; font-weight:700; background:rgba(0,0,0,.08); }
    .di-tab-active .di-tab-badge { background:rgba(255,255,255,.25); }

    .crm-tab, .kmp-tab { border:1px solid #d1d5db; border-radius:6px; padding:6px 14px; font-size:13px; font-weight:500; cursor:pointer; background:#fff; color:#374151; }
    .crm-tab-blue, .kmp-tab-blue   { background:#1d4ed8; color:#fff; border-color:#1d4ed8; }
    .crm-tab-red, .kmp-tab-red     { background:#dc2626; color:#fff; border-color:#dc2626; }
    .crm-tab-green, .kmp-tab-green { background:#16a34a; color:#fff; border-color:#16a34a; }
</style>

@php
    $qualityIssues = $duplicateEvents->count() + $positionMismatches->count()
        + $activeWithoutCrm->count() + $activeWithoutKmp->count()
        + $crmAccountsWithoutEmployee->count() + $kmpAccountsWithoutEmployee->count()
        + $territoriesHeldByDismissed->count() + $tabletsHeldByDismissed->count()
        + $tabletsResponsibleDismissed->count();
@endphp

<div x-data="{ tab: '{{ in_array($tab, ['crm','kmp','quality']) ? $tab : 'crm' }}' }">

    <div style="margin-bottom:20px;">
        <h1 style="font-size:22px;font-weight:700;color:#1e3a8a;margin:0;">Привязки и проверка данных</h1>
        <p style="font-size:13px;color:#64748b;margin:4px 0 0;">
            Привязка сотрудников к внешним системам (CRM/KMP) и несостыковки между таблицами — в одном месте.
        </p>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div style="background:#dcfce7;border:1px solid #86efac;border-radius:8px;padding:12px 16px;margin-bottom:20px;color:#166534;font-size:13px;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:12px 16px;margin-bottom:20px;color:#991b1b;font-size:13px;">
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:12px 16px;margin-bottom:20px;color:#991b1b;font-size:13px;">
            @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        </div>
    @endif

    {{-- Верхние вкладки --}}
    <div style="display:flex;gap:10px;margin-bottom:24px;flex-wrap:wrap;">
        <button class="di-tab" :class="tab==='crm' ? 'di-tab-active' : ''"
                @click="tab='crm'; history.replaceState({}, '', '?tab=crm')">
            Привязка CRM
            <span class="di-tab-badge">{{ $crmTotal - $crmMapped }}</span>
        </button>
        <button class="di-tab" :class="tab==='kmp' ? 'di-tab-active' : ''"
                @click="tab='kmp'; history.replaceState({}, '', '?tab=kmp')">
            Привязка KMP
            <span class="di-tab-badge">{{ $kmpTotal - $kmpMapped }}</span>
        </button>
        <button class="di-tab" :class="tab==='quality' ? 'di-tab-active' : ''"
                @click="tab='quality'; history.replaceState({}, '', '?tab=quality')">
            Проверка данных
            <span class="di-tab-badge">{{ $qualityIssues }}</span>
        </button>
    </div>

    {{-- ============================== CRM ============================== --}}
    <div x-show="tab==='crm'" x-cloak>
        <div style="display:flex;align-items:center;justify-content:flex-end;margin-bottom:16px;">
            <form method="POST" action="{{ route('admin.crm-mapping.auto') }}">
                @csrf
                <button type="submit"
                    style="background:#1d4ed8;color:#fff;border:none;border-radius:8px;padding:9px 18px;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:8px;">
                    <svg style="width:15px;height:15px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Автоматически привязать
                </button>
            </form>
        </div>

        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
            <div style="background:#fff;border-radius:12px;padding:20px;border:1px solid #e2e8f0;text-align:center;">
                <div style="font-size:28px;font-weight:700;color:#1e3a8a;">{{ $crmTotal }}</div>
                <div style="font-size:12px;color:#64748b;margin-top:4px;">Сотрудников в CRM</div>
            </div>
            <div style="background:#fff;border-radius:12px;padding:20px;border:1px solid #e2e8f0;text-align:center;">
                <div style="font-size:28px;font-weight:700;color:#16a34a;">{{ $crmMapped }}</div>
                <div style="font-size:12px;color:#64748b;margin-top:4px;">Привязано</div>
            </div>
            <div style="background:#fff;border-radius:12px;padding:20px;border:1px solid #e2e8f0;text-align:center;">
                <div style="font-size:28px;font-weight:700;color:#dc2626;">{{ $crmTotal - $crmMapped }}</div>
                <div style="font-size:12px;color:#64748b;margin-top:4px;">Не привязано</div>
            </div>
        </div>

        <div x-data="{ subtab: 'unmapped', search: '' }">
            <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;align-items:center;">
                <button @click="subtab='all'" class="crm-tab" :class="subtab==='all' ? 'crm-tab-blue' : ''">
                    Все ({{ $crmTotal }})
                </button>
                <button @click="subtab='unmapped'" class="crm-tab crm-tab-red" :class="subtab==='unmapped' ? 'crm-tab-red' : ''">
                    Не привязано ({{ $crmTotal - $crmMapped }})
                </button>
                <button @click="subtab='mapped'" class="crm-tab" :class="subtab==='mapped' ? 'crm-tab-green' : ''">
                    Привязано ({{ $crmMapped }})
                </button>
                <input x-model="search" type="text" placeholder="Поиск по имени CRM..."
                    style="margin-left:auto;border:1px solid #d1d5db;border-radius:6px;padding:6px 12px;font-size:13px;outline:none;width:220px;">
            </div>

            <div style="background:#fff;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden;">
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                            <th style="padding:10px 16px;text-align:left;font-weight:600;color:#374151;width:36px;"></th>
                            <th style="padding:10px 16px;text-align:left;font-weight:600;color:#374151;">Сотрудник CRM</th>
                            <th style="padding:10px 16px;text-align:left;font-weight:600;color:#374151;">Должность CRM</th>
                            <th style="padding:10px 16px;text-align:left;font-weight:600;color:#374151;">Привязан к сотруднику системы</th>
                            <th style="padding:10px 16px;text-align:left;font-weight:600;color:#374151;width:90px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($crmEmployees as $crm)
                        @php
                            $linked = $crmLinks->get($crm->employee_id)?->employee;
                            $isMapped = !is_null($linked);
                            $crmNameLower = mb_strtolower(trim($crm->employee));
                        @endphp
                        <tr
                            x-show="
                                (subtab==='all'
                                 || (subtab==='mapped' && {{ $isMapped ? 'true' : 'false' }})
                                 || (subtab==='unmapped' && {{ !$isMapped ? 'true' : 'false' }}))
                                && (search==='' || '{{ addslashes($crmNameLower) }}'.includes(search.toLowerCase()))
                            "
                            style="border-bottom:1px solid #f1f5f9;"
                            onmouseover="this.style.background='#f8fafc'"
                            onmouseout="this.style.background=''"
                        >
                            <td style="padding:10px 16px;">
                                @if($isMapped)
                                    <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#16a34a;" title="Привязан"></span>
                                @else
                                    <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#dc2626;" title="Не привязан"></span>
                                @endif
                            </td>
                            <td style="padding:10px 16px;">
                                <div style="font-weight:500;color:#1e293b;">{{ trim($crm->employee) }}</div>
                                <div style="color:#94a3b8;font-size:11px;">ID: {{ $crm->employee_id }}</div>
                            </td>
                            <td style="padding:10px 16px;color:#64748b;font-size:12px;">{{ $crm->employee_position ?? '—' }}</td>
                            <td style="padding:10px 16px;">
                                @php $initLabel = $linked ? ($linked->full_name . ($linked->position ? ' ('.$linked->position.')' : '')) : '' @endphp
                                <form method="POST" action="{{ route('admin.crm-mapping.link') }}"
                                      style="display:flex;gap:6px;align-items:center;">
                                    @csrf
                                    <input type="hidden" name="crm_employee_id" value="{{ $crm->employee_id }}">

                                    <div x-data="empPicker({{ $linked ? $linked->id : 'null' }}, @js($initLabel))"
                                         @click.outside="open=false"
                                         style="position:relative;flex:1;max-width:280px;">
                                        <div style="position:relative;">
                                            <input type="text"
                                                   x-model="query"
                                                   @focus="openPicker($event)"
                                                   @input="open=true"
                                                   @keydown.escape="open=false"
                                                   autocomplete="off"
                                                   placeholder="Поиск сотрудника..."
                                                   style="width:100%;box-sizing:border-box;border:1px solid #d1d5db;border-radius:6px;padding:5px 22px 5px 8px;font-size:12px;color:#374151;outline:none;">
                                            <span x-show="query" @click="clear()"
                                                  style="position:absolute;right:6px;top:50%;transform:translateY(-50%);cursor:pointer;color:#94a3b8;font-size:15px;line-height:1;user-select:none;">×</span>
                                        </div>
                                        <input type="hidden" name="employee_id" :value="selected ?? ''">

                                        <div x-show="open" x-cloak
                                             :style="`position:fixed;top:${pos.top}px;left:${pos.left}px;width:${pos.width}px;z-index:9999;background:#fff;border:1px solid #d1d5db;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,.1);max-height:220px;overflow-y:auto;`">
                                            <div @click="clear()"
                                                 style="padding:6px 10px;font-size:12px;color:#94a3b8;cursor:pointer;border-bottom:1px solid #f1f5f9;"
                                                 onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                                                — не привязан —
                                            </div>
                                            <template x-for="emp in filtered" :key="emp.id">
                                                <div @click="choose(emp)"
                                                     :style="emp.id===selected ? 'background:#eff6ff;font-weight:500;' : ''"
                                                     style="padding:6px 10px;font-size:12px;color:#1e293b;cursor:pointer;"
                                                     onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=this._x_isSelected?'#eff6ff':''">
                                                    <span x-text="emp.label"></span>
                                                </div>
                                            </template>
                                            <div x-show="filtered.length===0"
                                                 style="padding:8px 10px;font-size:12px;color:#94a3b8;">Не найдено</div>
                                        </div>
                                    </div>

                                    <button type="submit"
                                        style="white-space:nowrap;background:#e0e7ff;color:#3730a3;border:none;border-radius:6px;padding:5px 10px;font-size:11px;font-weight:600;cursor:pointer;flex-shrink:0;">
                                        Сохранить
                                    </button>
                                </form>
                            </td>
                            <td style="padding:10px 16px;">
                                @if($isMapped)
                                    <span style="font-size:11px;color:#16a34a;font-weight:500;">{{ $linked->position ?? '' }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ============================== KMP ============================== --}}
    <div x-show="tab==='kmp'" x-cloak>
        <div style="display:flex;align-items:center;justify-content:flex-end;margin-bottom:16px;">
            <form method="POST" action="{{ route('admin.kmp-mapping.auto') }}">
                @csrf
                <button type="submit"
                    style="background:#1d4ed8;color:#fff;border:none;border-radius:8px;padding:9px 18px;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:8px;">
                    <svg style="width:15px;height:15px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Автоматически привязать
                </button>
            </form>
        </div>

        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
            <div style="background:#fff;border-radius:12px;padding:20px;border:1px solid #e2e8f0;text-align:center;">
                <div style="font-size:28px;font-weight:700;color:#1e3a8a;">{{ $kmpTotal }}</div>
                <div style="font-size:12px;color:#64748b;margin-top:4px;">МП в KMP</div>
            </div>
            <div style="background:#fff;border-radius:12px;padding:20px;border:1px solid #e2e8f0;text-align:center;">
                <div style="font-size:28px;font-weight:700;color:#16a34a;">{{ $kmpMapped }}</div>
                <div style="font-size:12px;color:#64748b;margin-top:4px;">Привязано</div>
            </div>
            <div style="background:#fff;border-radius:12px;padding:20px;border:1px solid #e2e8f0;text-align:center;">
                <div style="font-size:28px;font-weight:700;color:#dc2626;">{{ $kmpTotal - $kmpMapped }}</div>
                <div style="font-size:12px;color:#64748b;margin-top:4px;">Не привязано</div>
            </div>
        </div>

        <div x-data="{ subtab: 'unmapped', search: '' }">
            <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;align-items:center;">
                <button @click="subtab='all'" class="kmp-tab" :class="subtab==='all' ? 'kmp-tab-blue' : ''">
                    Все ({{ $kmpTotal }})
                </button>
                <button @click="subtab='unmapped'" class="kmp-tab kmp-tab-red" :class="subtab==='unmapped' ? 'kmp-tab-red' : ''">
                    Не привязано ({{ $kmpTotal - $kmpMapped }})
                </button>
                <button @click="subtab='mapped'" class="kmp-tab" :class="subtab==='mapped' ? 'kmp-tab-green' : ''">
                    Привязано ({{ $kmpMapped }})
                </button>
                <input x-model="search" type="text" placeholder="Поиск по имени..."
                    style="margin-left:auto;border:1px solid #d1d5db;border-radius:6px;padding:6px 12px;font-size:13px;outline:none;width:220px;">
            </div>

            <div style="background:#fff;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden;">
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                            <th style="padding:10px 16px;text-align:left;font-weight:600;color:#374151;width:36px;"></th>
                            <th style="padding:10px 16px;text-align:left;font-weight:600;color:#374151;">МП в KMP</th>
                            <th style="padding:10px 16px;text-align:left;font-weight:600;color:#374151;">Привязан к сотруднику системы</th>
                            <th style="padding:10px 16px;text-align:left;font-weight:600;color:#374151;width:90px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($kmpEmployees as $kmp)
                        @php
                            $linked   = $kmpLinks->get($kmp->name)?->employee;
                            $isMapped = !is_null($linked);
                            $nameLower = mb_strtolower(trim($kmp->name));
                        @endphp
                        <tr
                            x-show="
                                (subtab==='all'
                                 || (subtab==='mapped'   && {{ $isMapped  ? 'true' : 'false' }})
                                 || (subtab==='unmapped' && {{ !$isMapped ? 'true' : 'false' }}))
                                && (search==='' || '{{ addslashes($nameLower) }}'.includes(search.toLowerCase()))
                            "
                            style="border-bottom:1px solid #f1f5f9;"
                            onmouseover="this.style.background='#f8fafc'"
                            onmouseout="this.style.background=''"
                        >
                            <td style="padding:10px 16px;">
                                @if($isMapped)
                                    <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#16a34a;"></span>
                                @else
                                    <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#dc2626;"></span>
                                @endif
                            </td>
                            <td style="padding:10px 16px;">
                                <div style="font-weight:500;color:#1e293b;">{{ $kmp->name }}</div>
                            </td>
                            <td style="padding:10px 16px;">
                                @php $initLabel = $linked ? ($linked->full_name . ($linked->position ? ' ('.$linked->position.')' : '')) : '' @endphp
                                <form method="POST" action="{{ route('admin.kmp-mapping.link') }}"
                                      style="display:flex;gap:6px;align-items:center;">
                                    @csrf
                                    <input type="hidden" name="kmp_name" value="{{ $kmp->name }}">

                                    <div x-data="kmpEmpPicker({{ $linked ? $linked->id : 'null' }}, @js($initLabel))"
                                         @click.outside="open=false"
                                         style="position:relative;flex:1;max-width:280px;">
                                        <div style="position:relative;">
                                            <input type="text"
                                                   x-model="query"
                                                   @focus="openPicker($event)"
                                                   @input="open=true"
                                                   @keydown.escape="open=false"
                                                   autocomplete="off"
                                                   placeholder="Поиск сотрудника..."
                                                   style="width:100%;box-sizing:border-box;border:1px solid #d1d5db;border-radius:6px;padding:5px 22px 5px 8px;font-size:12px;color:#374151;outline:none;">
                                            <span x-show="query" @click="clear()"
                                                  style="position:absolute;right:6px;top:50%;transform:translateY(-50%);cursor:pointer;color:#94a3b8;font-size:15px;line-height:1;user-select:none;">×</span>
                                        </div>
                                        <input type="hidden" name="employee_id" x-ref="hiddenEmpId"
                                               x-effect="$refs.hiddenEmpId.value = selected ?? ''">

                                        <div x-show="open" x-cloak
                                             :style="`position:fixed;top:${pos.top}px;left:${pos.left}px;width:${pos.width}px;z-index:9999;background:#fff;border:1px solid #d1d5db;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,.1);max-height:220px;overflow-y:auto;`">
                                            <div @click="clear()"
                                                 style="padding:6px 10px;font-size:12px;color:#94a3b8;cursor:pointer;border-bottom:1px solid #f1f5f9;"
                                                 onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                                                — не привязан —
                                            </div>
                                            <template x-for="emp in filtered" :key="emp.id">
                                                <div @click="choose(emp)"
                                                     :style="emp.id===selected ? 'background:#eff6ff;font-weight:500;' : ''"
                                                     style="padding:6px 10px;font-size:12px;color:#1e293b;cursor:pointer;"
                                                     onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                                                    <span x-text="emp.label"></span>
                                                </div>
                                            </template>
                                            <div x-show="filtered.length===0"
                                                 style="padding:8px 10px;font-size:12px;color:#94a3b8;">Не найдено</div>
                                        </div>
                                    </div>

                                    <button type="submit"
                                        style="white-space:nowrap;background:#e0e7ff;color:#3730a3;border:none;border-radius:6px;padding:5px 10px;font-size:11px;font-weight:600;cursor:pointer;flex-shrink:0;">
                                        Сохранить
                                    </button>
                                </form>
                            </td>
                            <td style="padding:10px 16px;">
                                @if($isMapped)
                                    <span style="font-size:11px;color:#16a34a;font-weight:500;">{{ $linked->position ?? '' }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ========================= Проверка данных ========================= --}}
    <div x-show="tab==='quality'" x-cloak>
        <p style="font-size:13px;color:#9ca3af;margin-bottom:20px;">
            Несостыковки между таблицами, которые стоит проверить руками. Раздел с 0 — сворачивается, всё остальное открыто по умолчанию.
        </p>

        <div style="max-width:900px;">

            <x-dq-section title="Дублирующиеся события" count="{{ $duplicateEvents->count() }}"
                          description="Один и тот же тип события с одной и той же датой записан больше одного раза.">
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr style="background:#f9fafb;border-bottom:1px solid #f0f0f0;">
                            <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">ФИО</th>
                            <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Событие</th>
                            <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Дата</th>
                            <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Записей</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($duplicateEvents as $row)
                            <tr style="border-bottom:1px solid #f9fafb;">
                                <td style="padding:9px 18px;">
                                    <a href="/employee/{{ $row->employee_id }}" style="color:#2563eb;text-decoration:none;">{{ $row->full_name }}</a>
                                </td>
                                <td style="padding:9px 18px;"><x-status-badge :status="$row->event_type" /></td>
                                <td style="padding:9px 18px;color:#374151;">{{ \Carbon\Carbon::parse($row->event_date)->format('d.m.Y') }}</td>
                                <td style="padding:9px 18px;color:#b91c1c;font-weight:600;">{{ $row->cnt }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-dq-section>

            <x-dq-section title="Должность не совпадает с территорией" count="{{ $positionMismatches->count() }}"
                          description="У активного сотрудника employees.position отличается от роли на последней назначенной территории.">
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr style="background:#f9fafb;border-bottom:1px solid #f0f0f0;">
                            <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">ФИО</th>
                            <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">employees.position</th>
                            <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Роль по территории</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($positionMismatches as $row)
                            <tr style="border-bottom:1px solid #f9fafb;">
                                <td style="padding:9px 18px;">
                                    <a href="/employee/{{ $row->id }}" style="color:#2563eb;text-decoration:none;">{{ $row->full_name }}</a>
                                </td>
                                <td style="padding:9px 18px;color:#374151;">{{ $row->position }}</td>
                                <td style="padding:9px 18px;color:#b91c1c;font-weight:600;">{{ $row->territory_role }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-dq-section>

            <x-dq-section title="Активные без привязки CRM" count="{{ $activeWithoutCrm->count() }}"
                          description="У сотрудника нет ни одного привязанного CRM-аккаунта — на карточке не будет блока визитов.">
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr style="background:#f9fafb;border-bottom:1px solid #f0f0f0;">
                            <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">ФИО</th>
                            <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Должность</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activeWithoutCrm as $row)
                            <tr style="border-bottom:1px solid #f9fafb;">
                                <td style="padding:9px 18px;">
                                    <a href="/employee/{{ $row->id }}" style="color:#2563eb;text-decoration:none;">{{ $row->full_name }}</a>
                                </td>
                                <td style="padding:9px 18px;color:#374151;">{{ $row->position }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-dq-section>

            <x-dq-section title="Активные без привязки KMP" count="{{ $activeWithoutKmp->count() }}"
                          description="У сотрудника нет ни одного привязанного имени КМП — на карточке не будет блока продаж.">
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr style="background:#f9fafb;border-bottom:1px solid #f0f0f0;">
                            <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">ФИО</th>
                            <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Должность</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activeWithoutKmp as $row)
                            <tr style="border-bottom:1px solid #f9fafb;">
                                <td style="padding:9px 18px;">
                                    <a href="/employee/{{ $row->id }}" style="color:#2563eb;text-decoration:none;">{{ $row->full_name }}</a>
                                </td>
                                <td style="padding:9px 18px;color:#374151;">{{ $row->position }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-dq-section>

            <x-dq-section title="CRM-аккаунты без сотрудника в системе" count="{{ $crmAccountsWithoutEmployee->count() }}"
                          description="Визиты есть в Nobel CRM (Rep/RM), но ни одной похожей по имени записи в employees нет — не привязка забыта, а сотрудник вообще не заведён в системе.">
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr style="background:#f9fafb;border-bottom:1px solid #f0f0f0;">
                            <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Сотрудник CRM</th>
                            <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Должность CRM</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($crmAccountsWithoutEmployee as $row)
                            <tr style="border-bottom:1px solid #f9fafb;">
                                <td style="padding:9px 18px;">
                                    <a href="#" @click.prevent="tab='crm'" style="color:#2563eb;text-decoration:none;">{{ $row->employee }}</a>
                                </td>
                                <td style="padding:9px 18px;color:#374151;">{{ $row->position }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-dq-section>

            <x-dq-section title="KMP-аккаунты без сотрудника в системе" count="{{ $kmpAccountsWithoutEmployee->count() }}"
                          description="Продажи есть в KMP (Медпредставитель), но ни одной похожей по имени записи в employees нет.">
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr style="background:#f9fafb;border-bottom:1px solid #f0f0f0;">
                            <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Медпредставитель KMP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kmpAccountsWithoutEmployee as $row)
                            <tr style="border-bottom:1px solid #f9fafb;">
                                <td style="padding:9px 18px;">
                                    <a href="#" @click.prevent="tab='kmp'" style="color:#2563eb;text-decoration:none;">{{ $row->employee }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-dq-section>

            <x-dq-section title="Территории на уволенных" count="{{ $territoriesHeldByDismissed->count() }}"
                          description="Территория числится активно назначенной (не снята) сотруднику, чей статус — уволен.">
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr style="background:#f9fafb;border-bottom:1px solid #f0f0f0;">
                            <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Уволенный сотрудник</th>
                            <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Территория</th>
                            <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Назначена</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($territoriesHeldByDismissed as $row)
                            <tr style="border-bottom:1px solid #f9fafb;">
                                <td style="padding:9px 18px;">
                                    <a href="/employee/{{ $row->employee_id }}" style="color:#2563eb;text-decoration:none;">{{ $row->full_name }}</a>
                                </td>
                                <td style="padding:9px 18px;">
                                    <a href="{{ route('territories.show', $row->territory_id) }}" style="color:#2563eb;text-decoration:none;">{{ $row->territory_name }}</a>
                                </td>
                                <td style="padding:9px 18px;color:#374151;">{{ \Carbon\Carbon::parse($row->assigned_at)->format('d.m.Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-dq-section>

            <x-dq-section title="Планшеты на уволенных" count="{{ $tabletsHeldByDismissed->count() }}"
                          description="Планшет числится выданным (не возвращён) сотруднику, чей статус — уволен.">
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr style="background:#f9fafb;border-bottom:1px solid #f0f0f0;">
                            <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Уволенный сотрудник</th>
                            <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Планшет</th>
                            <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Выдан</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tabletsHeldByDismissed as $row)
                            <tr style="border-bottom:1px solid #f9fafb;">
                                <td style="padding:9px 18px;">
                                    <a href="/employee/{{ $row->employee_id }}" style="color:#2563eb;text-decoration:none;">{{ $row->full_name }}</a>
                                </td>
                                <td style="padding:9px 18px;">
                                    <a href="{{ route('tablets.show', $row->tablet_id) }}" style="color:#2563eb;text-decoration:none;">{{ $row->invent_number }}</a>
                                </td>
                                <td style="padding:9px 18px;color:#374151;">{{ \Carbon\Carbon::parse($row->assigned_at)->format('d.m.Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-dq-section>

            <x-dq-section title="Ответственный за планшет уволен" count="{{ $tabletsResponsibleDismissed->count() }}"
                          description="tablets.responsible_id указывает на сотрудника, который уже уволен.">
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr style="background:#f9fafb;border-bottom:1px solid #f0f0f0;">
                            <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Уволенный сотрудник</th>
                            <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Планшет</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tabletsResponsibleDismissed as $row)
                            <tr style="border-bottom:1px solid #f9fafb;">
                                <td style="padding:9px 18px;">
                                    <a href="/employee/{{ $row->employee_id }}" style="color:#2563eb;text-decoration:none;">{{ $row->full_name }}</a>
                                </td>
                                <td style="padding:9px 18px;">
                                    <a href="{{ route('tablets.show', $row->tablet_id) }}" style="color:#2563eb;text-decoration:none;">{{ $row->invent_number }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-dq-section>

        </div>
    </div>

</div>

{{-- Данные сотрудников системы — один раз для всех строк обеих таблиц --}}
<script>
const SYS_EMPLOYEES = @json($sysEmployees->map(fn($e) => [
    'id'    => $e->id,
    'label' => $e->full_name . ($e->position ? ' (' . $e->position . ')' : ''),
])->values());
const KMP_SYS_EMPLOYEES = SYS_EMPLOYEES;

function empPicker(initId, initLabel) {
    return {
        selected: initId,
        query:    initLabel,
        open:     false,
        pos:      { top: 0, left: 0, width: 280 },
        get filtered() {
            const q = this.query.trim().toLowerCase();
            if (!q) return SYS_EMPLOYEES.slice(0, 80);
            return SYS_EMPLOYEES.filter(e => e.label.toLowerCase().includes(q)).slice(0, 80);
        },
        openPicker(event) {
            const rect = event.currentTarget.getBoundingClientRect();
            this.pos = { top: rect.bottom + 2, left: rect.left, width: rect.width };
            this.open = true;
            window.addEventListener('scroll', () => { this.open = false; }, { once: true });
        },
        choose(emp) {
            this.selected = emp.id;
            this.query    = emp.label;
            this.open     = false;
        },
        clear() {
            this.selected = null;
            this.query    = '';
        },
    };
}

function kmpEmpPicker(initId, initLabel) {
    return {
        selected: initId,
        query:    initLabel,
        open:     false,
        pos:      { top: 0, left: 0, width: 280 },
        get filtered() {
            const q = this.query.trim().toLowerCase();
            if (!q) return KMP_SYS_EMPLOYEES.slice(0, 80);
            return KMP_SYS_EMPLOYEES.filter(e => e.label.toLowerCase().includes(q)).slice(0, 80);
        },
        openPicker(event) {
            const rect = event.currentTarget.getBoundingClientRect();
            this.pos = { top: rect.bottom + 2, left: rect.left, width: rect.width };
            this.open = true;
            window.addEventListener('scroll', () => { this.open = false; }, { once: true });
        },
        choose(emp) {
            this.selected = emp.id;
            this.query    = emp.label;
            this.open     = false;
        },
        clear() {
            this.selected = null;
            this.query    = '';
        },
    };
}
</script>

@endsection
