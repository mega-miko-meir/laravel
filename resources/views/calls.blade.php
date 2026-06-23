@extends('layout')
@section('content')

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
:root {
    --bg:       #f1f5f9;
    --card:     #ffffff;
    --border:   #e2e8f0;
    --text1:    #1e293b;
    --text2:    #64748b;
    --text3:    #94a3b8;
    --blue:     #3b82f6;
    --green:    #10b981;
    --amber:    #f59e0b;
    --purple:   #8b5cf6;
    --red:      #ef4444;
    --shadow:   0 1px 3px rgba(0,0,0,.07), 0 1px 2px rgba(0,0,0,.05);
    --shadow-md:0 4px 6px rgba(0,0,0,.07), 0 2px 4px rgba(0,0,0,.05);
    --radius:   12px;
    --transition: .2s ease;
}
.dash-dark {
    --bg:     #0f172a;
    --card:   #1e293b;
    --border: #334155;
    --text1:  #f1f5f9;
    --text2:  #94a3b8;
    --text3:  #64748b;
    --shadow: 0 1px 3px rgba(0,0,0,.3);
    --shadow-md: 0 4px 6px rgba(0,0,0,.3);
}

.dash { background:var(--bg); min-height:100%; padding:0 0 40px; transition:background var(--transition); }

/* Header */
.dash-header {
    display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap;
    gap:12px; margin-bottom:20px; padding-top:8px;
}
.dash-title { font-size:22px; font-weight:700; color:var(--text1); display:flex; align-items:center; gap:10px; }
.dash-title span { font-size:13px; font-weight:500; color:var(--text3); background:var(--card); border:1px solid var(--border); border-radius:20px; padding:2px 10px; }
.dash-actions { display:flex; align-items:center; gap:8px; }

.btn {
    display:inline-flex; align-items:center; gap:6px;
    padding:7px 14px; border-radius:8px; font-size:13px; font-weight:500;
    cursor:pointer; border:1px solid var(--border); background:var(--card);
    color:var(--text2); transition:all var(--transition); text-decoration:none;
}
.btn:hover { background:var(--bg); color:var(--text1); }
.btn-primary { background:var(--blue); color:#fff; border-color:var(--blue); }
.btn-primary:hover { opacity:.9; background:var(--blue); color:#fff; }
.btn-icon { width:36px; height:36px; padding:0; justify-content:center; border-radius:8px; }

/* Filter panel */
.filter-panel {
    background:var(--card); border:1px solid var(--border); border-radius:var(--radius);
    padding:10px 16px; margin-bottom:20px; box-shadow:var(--shadow);
    transition:background var(--transition), border-color var(--transition);
}
.filter-grid { display:flex; flex-wrap:wrap; gap:8px; align-items:flex-end; }
.filter-field { display:flex; flex-direction:column; gap:3px; }
.filter-label { font-size:10px; font-weight:600; color:var(--text3); text-transform:uppercase; letter-spacing:.06em; }
.filter-input {
    background:var(--bg); border:1px solid var(--border); border-radius:7px;
    padding:0 10px; height:30px; font-size:12px; color:var(--text1); outline:none;
    transition:border-color var(--transition), background var(--transition);
}
.filter-input:focus { border-color:var(--blue); box-shadow:0 0 0 2px rgba(59,130,246,.15); }

/* KPI Grid */
.kpi-grid {
    display:grid;
    grid-template-columns:repeat(4, 1fr);
    gap:16px;
    margin-bottom:20px;
}
.kpi-card {
    background:var(--card); border:1px solid var(--border); border-radius:var(--radius);
    padding:20px; box-shadow:var(--shadow); transition:all var(--transition);
    position:relative; overflow:hidden;
}
.kpi-card:hover { transform:translateY(-2px); box-shadow:var(--shadow-md); }
.kpi-accent { position:absolute; top:0; left:0; right:0; height:3px; border-radius:var(--radius) var(--radius) 0 0; }
.kpi-icon {
    width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center;
    margin-bottom:12px;
}
.kpi-icon svg { width:20px; height:20px; }
.kpi-label { font-size:11px; font-weight:600; color:var(--text2); text-transform:uppercase; letter-spacing:.06em; margin-bottom:6px; }
.kpi-value { font-size:28px; font-weight:700; color:var(--text1); line-height:1; margin-bottom:8px; }
.kpi-sub { font-size:12px; color:var(--text2); display:flex; align-items:center; gap:4px; }
.kpi-badge {
    display:inline-flex; align-items:center; gap:3px; padding:2px 8px;
    border-radius:9999px; font-size:11px; font-weight:600;
}

/* Progress bar */
.progress-bar { height:4px; background:var(--border); border-radius:2px; margin-top:8px; }
.progress-fill { height:100%; border-radius:2px; transition:width .6s ease; }

/* Charts Grid */
.charts-grid {
    display:grid;
    grid-template-columns:2fr 1fr;
    gap:16px;
    margin-bottom:20px;
}
.chart-card {
    background:var(--card); border:1px solid var(--border); border-radius:var(--radius);
    padding:20px; box-shadow:var(--shadow); transition:all var(--transition);
}
.chart-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; }
.chart-title { font-size:14px; font-weight:600; color:var(--text1); }
.chart-subtitle { font-size:12px; color:var(--text3); margin-top:2px; }
.chart-wrap { position:relative; }

/* Table Card */
.table-card {
    background:var(--card); border:1px solid var(--border); border-radius:var(--radius);
    box-shadow:var(--shadow); overflow:hidden; transition:all var(--transition);
}
.table-toolbar {
    display:flex; align-items:center; justify-content:space-between; gap:12px;
    padding:14px 16px; border-bottom:1px solid var(--border); flex-wrap:wrap;
}
.table-info { font-size:13px; color:var(--text2); }
.table-info strong { color:var(--text1); }
.search-wrap { position:relative; }
.search-wrap svg { position:absolute; left:10px; top:50%; transform:translateY(-50%); width:14px; height:14px; color:var(--text3); }
.search-input {
    background:var(--bg); border:1px solid var(--border); border-radius:8px;
    padding:7px 10px 7px 32px; font-size:13px; color:var(--text1); outline:none;
    width:220px; transition:all var(--transition);
}
.search-input:focus { border-color:var(--blue); width:260px; box-shadow:0 0 0 3px rgba(59,130,246,.15); }
.search-input::placeholder { color:var(--text3); }

.data-table { width:100%; border-collapse:collapse; font-size:12px; }
.data-table th {
    padding:10px 12px; text-align:left; font-size:10px; font-weight:600;
    text-transform:uppercase; letter-spacing:.06em; color:var(--text2);
    background:var(--bg); white-space:nowrap; cursor:pointer; user-select:none;
    border-bottom:1px solid var(--border); transition:color var(--transition);
}
.data-table th:hover { color:var(--blue); }
.data-table th .sort-icon { margin-left:4px; opacity:.4; }
.data-table th.active-sort .sort-icon { opacity:1; color:var(--blue); }
.data-table td {
    padding:9px 12px; border-bottom:1px solid var(--border);
    color:var(--text1); transition:background var(--transition);
}
.data-table tr:last-child td { border-bottom:none; }
.data-table tr:hover td { background:var(--bg); }

.status-badge {
    display:inline-flex; align-items:center; padding:2px 9px;
    border-radius:9999px; font-size:11px; font-weight:500;
}

.table-pagination {
    display:flex; align-items:center; justify-content:space-between;
    padding:12px 16px; border-top:1px solid var(--border);
    font-size:12px; color:var(--text2); flex-wrap:wrap; gap:8px;
}

/* Responsive */
@media (max-width:1280px) {
    .kpi-grid { grid-template-columns:repeat(2,1fr); }
    .charts-grid { grid-template-columns:1fr; }
}
@media (max-width:768px) {
    .kpi-grid { grid-template-columns:1fr 1fr; }
    .charts-grid { grid-template-columns:1fr; }
    .search-input { width:100%; }
}
@media (max-width:480px) {
    .kpi-grid { grid-template-columns:1fr; }
}

/* Compact Multi-select */
.ms-wrap { position:relative; }
.ms-display {
    background:var(--bg); border:1px solid var(--border); border-radius:7px;
    padding:0 8px 0 10px; font-size:12px; color:var(--text1); cursor:pointer;
    display:flex; align-items:center; justify-content:space-between; gap:6px;
    height:30px; white-space:nowrap; min-width:110px;
}
.ms-display:focus { outline:none; border-color:var(--blue); }
.ms-val { color:var(--text2); overflow:hidden; text-overflow:ellipsis; }
.ms-val.active { color:var(--blue); font-weight:500; }
.ms-dropdown {
    position:absolute; top:calc(100% + 4px); left:0; z-index:200;
    background:var(--card); border:1px solid var(--border); border-radius:8px;
    box-shadow:var(--shadow-md); max-height:240px; overflow-y:auto; min-width:200px;
}
.ms-search { padding:6px 8px; border-bottom:1px solid var(--border); }
.ms-search input {
    width:100%; background:var(--bg); border:1px solid var(--border); border-radius:6px;
    padding:4px 8px; font-size:12px; color:var(--text1); outline:none;
}
.ms-option { display:flex; align-items:center; gap:8px; padding:6px 10px; cursor:pointer; font-size:12px; color:var(--text1); }
.ms-option:hover { background:var(--bg); }
.ms-option input[type=checkbox] { accent-color:var(--blue); width:13px; height:13px; }
</style>

<div class="dash" x-data="callsDash()" :class="{ 'dash-dark': dark }" id="calls-dash">

{{-- ─── HEADER ─── --}}
<div class="dash-header">
    <div class="dash-title">
        Визиты
        <span>{{ number_format($totalVisits) }}</span>
    </div>
    <div class="dash-actions">
        <button class="btn btn-icon" @click="filtersOpen = !filtersOpen" :title="filtersOpen ? 'Скрыть фильтры' : 'Показать фильтры'">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 010 2H4a1 1 0 01-1-1zm3 4h12M7 16h10M9 12h6"/>
            </svg>
        </button>
        <button class="btn btn-icon" @click="toggleDark()" :title="dark ? 'Светлая тема' : 'Тёмная тема'">
            <svg x-show="!dark" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
            <svg x-show="dark" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
        </button>
        @php
            $activeFilters = count(array_filter(
                request()->except('_token','sort','dir','page'),
                fn($v) => $v !== '' && $v !== null && $v !== []
            ));
        @endphp
        <form action="{{ route('calls.export') }}" method="POST" style="display:inline;">
            @csrf
            @foreach(request()->except('_token','sort','dir') as $k => $v)
                @if(is_array($v)) @foreach($v as $item) <input type="hidden" name="{{ $k }}[]" value="{{ $item }}"> @endforeach
                @else <input type="hidden" name="{{ $k }}" value="{{ $v }}"> @endif
            @endforeach
            <button type="submit" class="btn" style="background:#16a34a;color:#fff;border-color:#16a34a;gap:7px;"
                    onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'"
                    title="Выгрузить текущую выборку в CSV">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:14px;height:14px;flex-shrink:0;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Выгрузить
                @if($activeFilters)
                    <span style="background:rgba(255,255,255,.25);border-radius:10px;padding:1px 7px;font-size:11px;font-weight:700;">
                        {{ $activeFilters }}
                    </span>
                @endif
            </button>
        </form>
    </div>
</div>

{{-- ─── FILTERS ─── --}}
<div x-show="filtersOpen" x-transition class="filter-panel" x-cloak>
    <form method="GET" class="filter-grid">

        <div class="filter-field">
            <label class="filter-label">Дата от</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="filter-input" style="width:136px;">
        </div>

        <div class="filter-field">
            <label class="filter-label">Дата до</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="filter-input" style="width:136px;">
        </div>

        @php
            $msFilters = [
                ['employee_department','Группа',        $departments, false],
                ['province',           'Регион',        $provinces,   true],
                ['town',               'Город',         $towns,       true],
                ['customer_spesiality','Специальность', $specialties, true],
            ];
        @endphp

        @foreach($msFilters as [$fname, $flabel, $fopts, $fhasSearch])
            <div class="filter-field"
                 x-data="multiSelect('{{ $fname }}', {{ json_encode($fopts) }}, {{ json_encode(request($fname, [])) }})"
                 @click.outside="open=false">
                <label class="filter-label">{{ $flabel }}</label>
                <div class="ms-wrap">
                    <div class="ms-display" @click="open=!open" tabindex="0">
                        <span class="ms-val" :class="selected.length ? 'active' : ''"
                              x-text="selected.length ? selected.length + ' выбр.' : 'Все'"></span>
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             style="width:11px;height:11px;flex-shrink:0;color:var(--text3);"
                             :style="open ? 'transform:rotate(180deg)' : ''">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                    <div class="ms-dropdown" x-show="open" x-cloak>
                        @if($fhasSearch)
                        <div class="ms-search">
                            <input type="text" x-model="q" @input="filter()" placeholder="Поиск...">
                        </div>
                        @endif
                        <template x-for="item in filtered" :key="item">
                            <label class="ms-option">
                                <input type="checkbox" :value="item" @change="toggle(item)" :checked="selected.includes(item)">
                                <span x-text="item"></span>
                            </label>
                        </template>
                    </div>
                    <template x-for="s in selected">
                        <input type="hidden" name="{{ $fname }}[]" :value="s">
                    </template>
                </div>
            </div>
        @endforeach

        <div class="filter-field">
            <label class="filter-label">Сотрудник</label>
            @php
                $selEmp = collect($empList)->firstWhere('value', request('crm_employee_id'));
                $initEmpLabel = $selEmp ? $selEmp['label'] : '';
            @endphp
            <div x-data="callsEmpPicker(@js($empList), @js(request('crm_employee_id')), @js($initEmpLabel))"
                 style="position:relative;width:180px;">
                <div style="position:relative;">
                    <input type="text" x-model="query"
                           @focus="open=true" @input="open=true" @keydown.escape="open=false"
                           @click.outside="open=false"
                           autocomplete="off" placeholder="Поиск..."
                           class="filter-input" style="width:100%;box-sizing:border-box;padding-right:22px;">
                    <span x-show="selected" @click="clear($el.closest('form'))"
                          style="position:absolute;right:6px;top:50%;transform:translateY(-50%);cursor:pointer;color:#94a3b8;font-size:16px;line-height:1;user-select:none;">×</span>
                </div>
                <input type="hidden" name="crm_employee_id" x-ref="hiddenVal"
                       x-effect="$refs.hiddenVal.value = selected ?? ''">
                <div x-show="open && filtered.length" x-cloak
                     style="position:absolute;top:calc(100% + 2px);left:0;width:100%;z-index:999;background:#fff;border:1px solid #d1d5db;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,.1);max-height:200px;overflow-y:auto;">
                    <template x-for="emp in filtered" :key="emp.value">
                        <div @click="choose(emp, $el.closest('form'))"
                             style="padding:7px 10px;font-size:12px;color:#1e293b;cursor:pointer;"
                             onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background=''">
                            <span x-text="emp.label"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div class="filter-field" style="flex-direction:row;gap:6px;align-items:flex-end;">
            <button type="submit" class="btn btn-primary" style="height:30px;padding:0 14px;font-size:12px;">
                Применить
            </button>
            @if(request()->anyFilled(['date_from','date_to','province','town','employee','customer_spesiality','employee_department']))
                <a href="{{ route('calls.index') }}" class="btn" style="height:30px;padding:0 12px;font-size:12px;">
                    Сбросить
                </a>
            @endif
        </div>

    </form>
</div>

{{-- ─── KPI CARDS ─── --}}
<div class="kpi-grid">

    {{-- Всего визитов --}}
    <div class="kpi-card">
        <div class="kpi-accent" style="background:var(--blue);"></div>
        <div class="kpi-icon" style="background:rgba(59,130,246,.1);">
            <svg fill="none" viewBox="0 0 24 24" stroke="#3b82f6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <div class="kpi-label">Всего визитов</div>
        <div class="kpi-value">{{ number_format($totalVisits, 0, '.', ' ') }}</div>
        <div class="kpi-sub">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:12px;height:12px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            {{ number_format($visitsPerEmployee, 1) }} визита на сотрудника
        </div>
    </div>

    {{-- Сотрудников --}}
    <div class="kpi-card">
        <div class="kpi-accent" style="background:var(--purple);"></div>
        <div class="kpi-icon" style="background:rgba(139,92,246,.1);">
            <svg fill="none" viewBox="0 0 24 24" stroke="#8b5cf6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </div>
        <div class="kpi-label">Сотрудников</div>
        <div class="kpi-value">{{ number_format($employeesCount, 0, '.', ' ') }}</div>
        <div class="kpi-sub">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:12px;height:12px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            уникальных медпредставителей
        </div>
    </div>

    {{-- Ср. длительность --}}
    <div class="kpi-card">
        <div class="kpi-accent" style="background:var(--amber);"></div>
        <div class="kpi-icon" style="background:rgba(245,158,11,.1);">
            <svg fill="none" viewBox="0 0 24 24" stroke="#f59e0b"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="kpi-label">Ср. длительность</div>
        <div class="kpi-value">{{ $avgDuration }}<span style="font-size:16px;font-weight:500;color:var(--text2);margin-left:4px;">мин</span></div>
        <div class="kpi-sub">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:12px;height:12px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            среди выполненных визитов
        </div>
    </div>

</div>

{{-- ─── TYPE BREAKDOWN ─── --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">

    <div class="kpi-card" style="padding:16px 20px;">
        <div class="kpi-accent" style="background:#6366f1;"></div>
        <div style="display:flex;align-items:center;gap:14px;">
            <div style="width:44px;height:44px;border-radius:10px;background:rgba(99,102,241,.1);
                        display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg style="width:22px;height:22px;" fill="none" viewBox="0 0 24 24" stroke="#6366f1">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:11px;font-weight:600;color:var(--text2);text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Визит к врачу</div>
                <div style="font-size:26px;font-weight:700;color:var(--text1);line-height:1;">{{ number_format($doctorVisits, 0, '.', ' ') }}</div>
                @if($totalVisits > 0)
                <div style="font-size:11px;color:var(--text3);margin-top:3px;">{{ round($doctorVisits / $totalVisits * 100) }}% от всех визитов</div>
                @endif
            </div>
        </div>
        <div style="margin-top:12px;height:4px;background:var(--border);border-radius:2px;">
            <div style="height:100%;border-radius:2px;background:#6366f1;width:{{ $totalVisits > 0 ? round($doctorVisits / $totalVisits * 100) : 0 }}%;transition:width .6s;"></div>
        </div>
    </div>

    <div class="kpi-card" style="padding:16px 20px;">
        <div class="kpi-accent" style="background:#0ea5e9;"></div>
        <div style="display:flex;align-items:center;gap:14px;">
            <div style="width:44px;height:44px;border-radius:10px;background:rgba(14,165,233,.1);
                        display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg style="width:22px;height:22px;" fill="none" viewBox="0 0 24 24" stroke="#0ea5e9">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:11px;font-weight:600;color:var(--text2);text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Визит в аптеку</div>
                <div style="font-size:26px;font-weight:700;color:var(--text1);line-height:1;">{{ number_format($pharmacyVisits, 0, '.', ' ') }}</div>
                @if($totalVisits > 0)
                <div style="font-size:11px;color:var(--text3);margin-top:3px;">{{ round($pharmacyVisits / $totalVisits * 100) }}% от всех визитов</div>
                @endif
            </div>
        </div>
        <div style="margin-top:12px;height:4px;background:var(--border);border-radius:2px;">
            <div style="height:100%;border-radius:2px;background:#0ea5e9;width:{{ $totalVisits > 0 ? round($pharmacyVisits / $totalVisits * 100) : 0 }}%;transition:width .6s;"></div>
        </div>
    </div>

</div>

{{-- ─── OneKey COVERAGE ─── --}}
@if($onekeyTotal > 0 || $pharmOnekeyTotal > 0)
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">

    {{-- Врачи --}}
    @if($onekeyTotal > 0)
    <div class="kpi-card" style="padding:16px 20px;">
        <div class="kpi-accent" style="background:#6366f1;"></div>
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(99,102,241,.1);
                        display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg style="width:20px;height:20px;" fill="none" viewBox="0 0 24 24" stroke="#6366f1">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <div style="font-size:11px;font-weight:600;color:var(--text2);text-transform:uppercase;letter-spacing:.06em;">Охват врачей OneKey</div>
                <div style="font-size:11px;color:var(--text3);margin-top:1px;">по названию организации</div>
            </div>
        </div>
        <div style="display:flex;align-items:baseline;gap:8px;margin-bottom:10px;">
            <span style="font-size:32px;font-weight:700;color:#6366f1;line-height:1;">{{ $onekeyPercent }}%</span>
            <span style="font-size:13px;color:var(--text2);">
                {{ number_format($onekeyVisited, 0, '.', ' ') }}
                <span style="color:var(--text3);">/ {{ number_format($onekeyTotal, 0, '.', ' ') }}</span>
            </span>
        </div>
        <div style="height:6px;background:var(--border);border-radius:3px;overflow:hidden;">
            <div style="height:100%;border-radius:3px;background:#6366f1;width:{{ $onekeyPercent }}%;transition:width .6s;"></div>
        </div>
    </div>
    @endif

    {{-- Аптеки --}}
    @if($pharmOnekeyTotal > 0)
    <div class="kpi-card" style="padding:16px 20px;">
        <div class="kpi-accent" style="background:#0ea5e9;"></div>
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(14,165,233,.1);
                        display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg style="width:20px;height:20px;" fill="none" viewBox="0 0 24 24" stroke="#0ea5e9">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div>
                <div style="font-size:11px;font-weight:600;color:var(--text2);text-transform:uppercase;letter-spacing:.06em;">Охват аптек OneKey</div>
                <div style="font-size:11px;color:var(--text3);margin-top:1px;">по названию организации</div>
            </div>
        </div>
        <div style="display:flex;align-items:baseline;gap:8px;margin-bottom:10px;">
            <span style="font-size:32px;font-weight:700;color:#0ea5e9;line-height:1;">{{ $pharmOnekeyPercent }}%</span>
            <span style="font-size:13px;color:var(--text2);">
                {{ number_format($pharmOnekeyVisited, 0, '.', ' ') }}
                <span style="color:var(--text3);">/ {{ number_format($pharmOnekeyTotal, 0, '.', ' ') }}</span>
            </span>
        </div>
        <div style="height:6px;background:var(--border);border-radius:3px;overflow:hidden;">
            <div style="height:100%;border-radius:3px;background:#0ea5e9;width:{{ $pharmOnekeyPercent }}%;transition:width .6s;"></div>
        </div>
    </div>
    @endif

</div>
@endif

{{-- ─── CHARTS ─── --}}
<div class="charts-grid">

    {{-- Trend Line Chart --}}
    <div class="chart-card">
        <div class="chart-header">
            <div>
                <div class="chart-title">Динамика визитов</div>
                <div class="chart-subtitle">Визиты по месяцам</div>
            </div>
            <div style="display:flex;gap:12px;font-size:11px;color:var(--text2);">
                <span style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:3px;background:#3b82f6;display:inline-block;border-radius:2px;"></span>Всего</span>
                <span style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:3px;background:#10b981;display:inline-block;border-radius:2px;"></span>Выполнено</span>
            </div>
        </div>
        <div class="chart-wrap" style="height:220px;">
            <canvas id="trendChart"></canvas>
        </div>
    </div>

    {{-- Top Regions Bar Chart --}}
    <div class="chart-card">
        <div class="chart-header">
            <div>
                <div class="chart-title">Топ регионов</div>
                <div class="chart-subtitle">По числу визитов</div>
            </div>
        </div>
        <div class="chart-wrap" style="height:220px;">
            <canvas id="regionsChart"></canvas>
        </div>
    </div>

</div>

{{-- ─── SPECIALTIES CHART ─── --}}
<div class="chart-card" style="margin-bottom:20px;">
    <div class="chart-header">
        <div>
            <div class="chart-title">Визиты по специальностям</div>
            <div class="chart-subtitle">Топ-12 специальностей врачей</div>
        </div>
        <div style="display:flex;gap:6px;">
            <button onclick="setSpecChartType('bar')" id="btn-bar"
                    style="padding:4px 10px;font-size:11px;font-weight:500;border-radius:6px;border:1px solid var(--border);background:var(--blue);color:#fff;cursor:pointer;">
                Столбцы
            </button>
            <button onclick="setSpecChartType('doughnut')" id="btn-doughnut"
                    style="padding:4px 10px;font-size:11px;font-weight:500;border-radius:6px;border:1px solid var(--border);background:var(--bg);color:var(--text2);cursor:pointer;">
                Диаграмма
            </button>
        </div>
    </div>
    <div class="chart-wrap" style="height:320px;">
        <canvas id="specChart"></canvas>
    </div>
</div>

{{-- ─── TABLE ─── --}}
<div class="table-card">
    <div class="table-toolbar">
        <div class="table-info">
            Показано <strong>{{ $calls->firstItem() }}–{{ $calls->lastItem() }}</strong> из <strong>{{ number_format($calls->total(), 0, '.', ' ') }}</strong>
        </div>
        <div class="search-wrap">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" class="search-input" x-model="tableSearch" placeholder="Поиск на странице...">
        </div>
    </div>

    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    @php
                        $cols = [
                            'appointment_Date'    => 'Дата',
                            'employee'            => 'Сотрудник',
                            'organization'        => 'Организация',
                            'customer_spesiality' => 'Специальность',
                            'town'                => 'Город',
                            'province'            => 'Регион',
                            'appointment_type'    => 'Тип',
                            'appointment_duration'=> 'Мин.',
                        ];
                    @endphp
                    @foreach($cols as $col => $label)
                        @php $canSort = in_array($col, ['appointment_Date','employee','organization','province','town','appointment_duration']); @endphp
                        <th class="{{ $sortCol === $col ? 'active-sort' : '' }}">
                            @if($canSort)
                                <a href="{{ request()->fullUrlWithQuery(['sort' => $col, 'dir' => ($sortCol === $col && $sortDir === 'asc') ? 'desc' : 'asc']) }}"
                                   style="text-decoration:none;color:inherit;display:flex;align-items:center;gap:2px;">
                                    {{ $label }}
                                    <span class="sort-icon">
                                        @if($sortCol === $col)
                                            {{ $sortDir === 'asc' ? '↑' : '↓' }}
                                        @else
                                            ↕
                                        @endif
                                    </span>
                                </a>
                            @else
                                {{ $label }}
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($calls as $call)
                    @php
                        $row = collect([
                            $call->appointment_Date?->format('d.m.Y') ?? '—',
                            $call->employee ?? '—',
                            $call->organization ?? '—',
                            $call->customer_spesiality ?? '—',
                            $call->town ?? '—',
                            $call->province ?? '—',
                            $call->appointment_type ?? '—',
                            $call->appointment_duration ?? '—',
                        ])->implode('|__|');
                    @endphp
                    <tr x-show="rowVisible('{{ addslashes($row) }}')"
                        style="transition:opacity .15s;">
                        <td style="white-space:nowrap;color:var(--text2);">{{ $call->appointment_Date?->format('d.m.Y') ?? '—' }}</td>
                        <td style="font-weight:500;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $call->employee ?? '—' }}</td>
                        <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $call->organization }}">{{ $call->organization ?? '—' }}</td>
                        <td style="color:var(--text2);max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $call->customer_spesiality ?? '—' }}</td>
                        <td style="color:var(--text2);">{{ $call->town ?? '—' }}</td>
                        <td style="color:var(--text2);">{{ $call->province ?? '—' }}</td>
                        <td style="color:var(--text2);white-space:nowrap;">{{ $call->appointment_type ?? '—' }}</td>
                        <td style="text-align:center;color:var(--text2);">{{ $call->appointment_duration ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center;padding:40px;color:var(--text3);">Нет данных</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-pagination">
        <div style="color:var(--text2);font-size:12px;">Страница {{ $calls->currentPage() }} из {{ $calls->lastPage() }}</div>
        <div>{{ $calls->appends(request()->query())->links() }}</div>
    </div>
</div>

</div>{{-- end .dash --}}

<script>
const TREND_DATA      = @json($monthlyTrend);
const REGIONS_DATA    = @json($topRegions);
const SPEC_DATA       = @json($topSpecialties);
const PALETTE = ['#3b82f6','#8b5cf6','#06b6d4','#10b981','#f59e0b','#ef4444','#ec4899','#6366f1','#14b8a6','#f97316','#84cc16','#a855f7'];

let specChartType = 'bar';

function setSpecChartType(type) {
    specChartType = type;
    document.getElementById('btn-bar').style.background      = type === 'bar'      ? 'var(--blue)' : 'var(--bg)';
    document.getElementById('btn-bar').style.color           = type === 'bar'      ? '#fff'        : 'var(--text2)';
    document.getElementById('btn-doughnut').style.background = type === 'doughnut' ? 'var(--blue)' : 'var(--bg)';
    document.getElementById('btn-doughnut').style.color      = type === 'doughnut' ? '#fff'        : 'var(--text2)';
    Alpine.store && Alpine.store('dash') ? null : null;
    // find the dash component and rebuild
    const el = document.getElementById('calls-dash');
    if (el && el._x_dataStack) {
        el._x_dataStack[0].buildCharts();
    }
}

function callsDash() {
    return {
        dark: localStorage.getItem('callsTheme') === 'dark',
        filtersOpen: {{ request()->anyFilled(['date_from','date_to','province','town','employee','organization_type','customer_spesiality','employee_department']) ? 'true' : 'false' }},
        tableSearch: '',

        init() {
            this.$nextTick(() => this.buildCharts());
        },

        toggleDark() {
            this.dark = !this.dark;
            localStorage.setItem('callsTheme', this.dark ? 'dark' : 'light');
            this.$nextTick(() => this.buildCharts());
        },

        rowVisible(rowData) {
            if (!this.tableSearch.trim()) return true;
            return rowData.toLowerCase().includes(this.tableSearch.toLowerCase());
        },

        buildCharts() {
            const dark     = this.dark;
            const gridColor = dark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.06)';
            const textColor = dark ? '#94a3b8' : '#64748b';

            Chart.defaults.color = textColor;

            // ── Trend chart ──
            const tCtx = document.getElementById('trendChart');
            if (tCtx) {
                if (tCtx._chart) tCtx._chart.destroy();
                tCtx._chart = new Chart(tCtx, {
                    type: 'line',
                    data: {
                        labels: TREND_DATA.map(d => d.month),
                        datasets: [
                            {
                                label: 'Всего',
                                data: TREND_DATA.map(d => d.total),
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59,130,246,.08)',
                                borderWidth: 2,
                                pointRadius: 3,
                                pointHoverRadius: 5,
                                fill: true,
                                tension: .35,
                            },
                            {
                                label: 'Выполнено',
                                data: TREND_DATA.map(d => d.completed),
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16,185,129,.06)',
                                borderWidth: 2,
                                pointRadius: 3,
                                pointHoverRadius: 5,
                                fill: true,
                                tension: .35,
                            }
                        ]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
                        scales: {
                            x: { grid: { color: gridColor }, ticks: { color: textColor, maxTicksLimit: 8 } },
                            y: { grid: { color: gridColor }, ticks: { color: textColor }, beginAtZero: true }
                        }
                    }
                });
            }

            // ── Regions bar chart ──
            const rCtx = document.getElementById('regionsChart');
            if (rCtx) {
                if (rCtx._chart) rCtx._chart.destroy();
                rCtx._chart = new Chart(rCtx, {
                    type: 'bar',
                    data: {
                        labels: REGIONS_DATA.map(d => d.province ? d.province.substring(0, 18) : '—'),
                        datasets: [{
                            data: REGIONS_DATA.map(d => d.total),
                            backgroundColor: PALETTE.map(c => c + 'cc'),
                            borderColor: PALETTE,
                            borderWidth: 1,
                            borderRadius: 4,
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ' ' + ctx.parsed.x.toLocaleString() } } },
                        scales: {
                            x: { grid: { color: gridColor }, ticks: { color: textColor } },
                            y: { grid: { display: false }, ticks: { color: textColor, font: { size: 11 } } }
                        }
                    }
                });
            }

            // ── Specialties chart ──
            const sCtx = document.getElementById('specChart');
            if (sCtx) {
                if (sCtx._chart) sCtx._chart.destroy();
                const labels = SPEC_DATA.map(d => d.customer_spesiality || '—');
                const values = SPEC_DATA.map(d => d.total);
                const bgColors = PALETTE.map(c => c + 'cc');

                if (specChartType === 'doughnut') {
                    sCtx._chart = new Chart(sCtx, {
                        type: 'doughnut',
                        data: { labels, datasets: [{ data: values, backgroundColor: bgColors, borderColor: dark ? '#1e293b' : '#fff', borderWidth: 2 }] },
                        options: {
                            responsive: true, maintainAspectRatio: false,
                            cutout: '60%',
                            plugins: {
                                legend: {
                                    position: 'right',
                                    labels: { color: textColor, font: { size: 11 }, boxWidth: 12, padding: 10,
                                              generateLabels: chart => chart.data.labels.map((l, i) => ({
                                                  text: l.length > 22 ? l.substring(0,22)+'…' : l,
                                                  fillStyle: bgColors[i], strokeStyle: bgColors[i], index: i
                                              }))
                                    }
                                },
                                tooltip: { callbacks: {
                                    label: ctx => ' ' + ctx.label + ': ' + ctx.parsed.toLocaleString()
                                }}
                            }
                        }
                    });
                } else {
                    const total = values.reduce((a, b) => a + b, 0);
                    sCtx._chart = new Chart(sCtx, {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [{
                                data: values,
                                backgroundColor: bgColors,
                                borderColor: PALETTE,
                                borderWidth: 1,
                                borderRadius: 4,
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true, maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: ctx => {
                                            const val = ctx.parsed.x;
                                            const pct = total > 0 ? (val / total * 100).toFixed(1) : 0;
                                            return `  ${val.toLocaleString()} визитов (${pct}%)`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: { grid: { color: gridColor }, ticks: { color: textColor }, beginAtZero: true },
                                y: { grid: { display: false }, ticks: { color: textColor, font: { size: 11 } } }
                            }
                        }
                    });
                }
            }
        }
    }
}

function multiSelect(name, options, init) {
    return {
        open: false, q: '', options, filtered: options, selected: init,
        filter() { this.filtered = this.options.filter(i => i.toLowerCase().includes(this.q.toLowerCase())); },
        toggle(item) { this.selected.includes(item) ? this.selected = this.selected.filter(i => i !== item) : this.selected.push(item); },
        remove(item) { this.selected = this.selected.filter(i => i !== item); },
    };
}

function callsEmpPicker(list, initValue, initLabel) {
    return {
        list,
        selected: initValue || null,
        query:    initLabel || '',
        open:     false,
        get filtered() {
            const q = this.query.trim().toLowerCase();
            if (!q) return this.list.slice(0, 80);
            return this.list.filter(e => e.label.toLowerCase().includes(q)).slice(0, 80);
        },
        choose(emp, form) {
            this.selected = emp.value;
            this.query    = emp.label;
            this.open     = false;
            this.$nextTick(() => form && form.submit());
        },
        clear(form) {
            this.selected = null;
            this.query    = '';
            this.open     = false;
            this.$nextTick(() => form && form.submit());
        },
    };
}
</script>

@endsection
