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
.btn[disabled] { opacity:.6; cursor:default; }

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

.table-pagination {
    display:flex; align-items:center; justify-content:space-between;
    padding:12px 16px; border-top:1px solid var(--border);
    font-size:12px; color:var(--text2); flex-wrap:wrap; gap:8px;
}
.pager-btn {
    padding:5px 10px; border-radius:6px; border:1px solid var(--border); background:var(--card);
    color:var(--text2); font-size:12px; cursor:pointer;
}
.pager-btn:hover:not(:disabled) { background:var(--bg); color:var(--text1); }
.pager-btn:disabled { opacity:.4; cursor:default; }

.dash[data-loading="1"] { opacity:.6; pointer-events:none; transition:opacity .15s; }

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

<div class="dash" x-data='callsDash(@json($initialData), "{{ $month }}")' :data-loading="loading ? '1' : null" id="calls-dash"
     @ms-change.window="filters[$event.detail.name] = $event.detail.selected; applyFilters();">

{{-- ─── HEADER ─── --}}
<div class="dash-header">
    <div class="dash-title">
        Визиты
        <span x-text="fmt(kpi.total)"></span>
    </div>
    <div class="dash-actions">
        <button class="btn btn-icon" @click="filtersOpen = !filtersOpen" :title="filtersOpen ? 'Скрыть фильтры' : 'Показать фильтры'">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 010 2H4a1 1 0 01-1-1zm3 4h12M7 16h10M9 12h6"/>
            </svg>
        </button>
        <form action="{{ route('calls.export') }}" method="POST" id="calls-export-form" @submit="beforeExport($event)">
            @csrf
            <button type="submit" class="btn" style="background:#16a34a;color:#fff;border-color:#16a34a;gap:7px;"
                    onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'"
                    title="Выгрузить текущую выборку в CSV">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:14px;height:14px;flex-shrink:0;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Выгрузить
                <span x-show="activeFiltersCount()" style="background:rgba(255,255,255,.25);border-radius:10px;padding:1px 7px;font-size:11px;font-weight:700;" x-text="activeFiltersCount()"></span>
            </button>
        </form>
    </div>
</div>

{{-- ─── FILTERS ─── --}}
<div x-show="filtersOpen" x-transition class="filter-panel" x-cloak>
    <div class="filter-grid">

        <div class="filter-field">
            <label class="filter-label">Месяц</label>
            <div style="display:flex;gap:6px;">
                <input type="month" x-model="month" class="filter-input" style="width:136px;">
                <button type="button" @click="loadMonth()" :disabled="loading" class="btn btn-primary" style="height:30px;padding:0 12px;font-size:12px;">
                    <span x-text="loading ? '…' : 'Показать'"></span>
                </button>
            </div>
        </div>

        <div class="filter-field"
             x-data="multiSelect('province', sortedDict('province'))"
             @click.outside="open=false" @reset-filters.window="selected=[]; q=''; if ($event.detail && $event.detail[name]) { options=$event.detail[name]; } filter();">
            <label class="filter-label">Регион</label>
            <div class="ms-wrap">
                <div class="ms-display" @click="open=!open" tabindex="0">
                    <span class="ms-val" :class="selected.length ? 'active' : ''" x-text="selected.length ? selected.length + ' выбр.' : 'Все'"></span>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:11px;height:11px;flex-shrink:0;color:var(--text3);" :style="open ? 'transform:rotate(180deg)' : ''">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
                <div class="ms-dropdown" x-show="open" x-cloak>
                    <div class="ms-search"><input type="text" x-model="q" @input="filter()" placeholder="Поиск..."></div>
                    <template x-for="item in filtered" :key="item">
                        <label class="ms-option">
                            <input type="checkbox" :value="item" @change="toggle(item)" :checked="selected.includes(item)">
                            <span x-text="item"></span>
                        </label>
                    </template>
                </div>
            </div>
        </div>

        <div class="filter-field"
             x-data="multiSelect('town', sortedDict('town'))"
             @click.outside="open=false" @reset-filters.window="selected=[]; q=''; if ($event.detail && $event.detail[name]) { options=$event.detail[name]; } filter();">
            <label class="filter-label">Город</label>
            <div class="ms-wrap">
                <div class="ms-display" @click="open=!open" tabindex="0">
                    <span class="ms-val" :class="selected.length ? 'active' : ''" x-text="selected.length ? selected.length + ' выбр.' : 'Все'"></span>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:11px;height:11px;flex-shrink:0;color:var(--text3);" :style="open ? 'transform:rotate(180deg)' : ''">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
                <div class="ms-dropdown" x-show="open" x-cloak>
                    <div class="ms-search"><input type="text" x-model="q" @input="filter()" placeholder="Поиск..."></div>
                    <template x-for="item in filtered" :key="item">
                        <label class="ms-option">
                            <input type="checkbox" :value="item" @change="toggle(item)" :checked="selected.includes(item)">
                            <span x-text="item"></span>
                        </label>
                    </template>
                </div>
            </div>
        </div>

        <div class="filter-field"
             x-data="multiSelect('specialty', sortedDict('specialty'))"
             @click.outside="open=false" @reset-filters.window="selected=[]; q=''; if ($event.detail && $event.detail[name]) { options=$event.detail[name]; } filter();">
            <label class="filter-label">Специальность</label>
            <div class="ms-wrap">
                <div class="ms-display" @click="open=!open" tabindex="0">
                    <span class="ms-val" :class="selected.length ? 'active' : ''" x-text="selected.length ? selected.length + ' выбр.' : 'Все'"></span>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:11px;height:11px;flex-shrink:0;color:var(--text3);" :style="open ? 'transform:rotate(180deg)' : ''">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
                <div class="ms-dropdown" x-show="open" x-cloak>
                    <div class="ms-search"><input type="text" x-model="q" @input="filter()" placeholder="Поиск..."></div>
                    <template x-for="item in filtered" :key="item">
                        <label class="ms-option">
                            <input type="checkbox" :value="item" @change="toggle(item)" :checked="selected.includes(item)">
                            <span x-text="item"></span>
                        </label>
                    </template>
                </div>
            </div>
        </div>

        <div class="filter-field"
             x-data="multiSelect('department', sortedNormalizedDepts())"
             @click.outside="open=false" @reset-filters.window="selected=[]; q=''; if ($event.detail && $event.detail[name]) { options=$event.detail[name]; } filter();">
            <label class="filter-label">Группа</label>
            <div class="ms-wrap">
                <div class="ms-display" @click="open=!open" tabindex="0">
                    <span class="ms-val" :class="selected.length ? 'active' : ''" x-text="selected.length ? selected.length + ' выбр.' : 'Все'"></span>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:11px;height:11px;flex-shrink:0;color:var(--text3);" :style="open ? 'transform:rotate(180deg)' : ''">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
                <div class="ms-dropdown" x-show="open" x-cloak>
                    <template x-for="item in filtered" :key="item">
                        <label class="ms-option">
                            <input type="checkbox" :value="item" @change="toggle(item)" :checked="selected.includes(item)">
                            <span x-text="item"></span>
                        </label>
                    </template>
                </div>
            </div>
        </div>

        <div class="filter-field">
            <label class="filter-label">Сотрудник</label>
            <div x-data="callsEmpPicker(data.empList)"
                 @reset-filters.window="selected=null; query=''; open=false;"
                 style="position:relative;width:180px;">
                <div style="position:relative;">
                    <input type="text" x-model="query"
                           @focus="open=true" @input="open=true" @keydown.escape="open=false"
                           @click.outside="open=false"
                           autocomplete="off" placeholder="Поиск..."
                           class="filter-input" style="width:100%;box-sizing:border-box;padding-right:22px;">
                    <span x-show="selected" @click="clear()"
                          style="position:absolute;right:6px;top:50%;transform:translateY(-50%);cursor:pointer;color:#94a3b8;font-size:16px;line-height:1;user-select:none;">×</span>
                </div>
                <div x-show="open && filtered.length" x-cloak
                     style="position:absolute;top:calc(100% + 2px);left:0;width:100%;z-index:999;background:#fff;border:1px solid #d1d5db;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,.1);max-height:200px;overflow-y:auto;">
                    <template x-for="emp in filtered" :key="emp.value">
                        <div @click="choose(emp)"
                             style="padding:7px 10px;font-size:12px;color:#1e293b;cursor:pointer;"
                             onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background=''">
                            <span x-text="emp.label"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div class="filter-field" style="flex-direction:row;gap:6px;align-items:flex-end;">
            <button type="button" @click="resetFilters()" class="btn" style="height:30px;padding:0 12px;font-size:12px;">
                Сбросить
            </button>
        </div>

    </div>
</div>

{{-- ─── KPI CARDS ─── --}}
<div class="kpi-grid">

    <div class="kpi-card">
        <div class="kpi-accent" style="background:var(--blue);"></div>
        <div class="kpi-icon" style="background:rgba(59,130,246,.1);">
            <svg fill="none" viewBox="0 0 24 24" stroke="#3b82f6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <div class="kpi-label">Всего визитов</div>
        <div class="kpi-value" x-text="fmt(kpi.total)"></div>
        <div class="kpi-sub">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:12px;height:12px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span x-text="kpi.visitsPerEmployee"></span> визита на сотрудника
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-accent" style="background:var(--purple);"></div>
        <div class="kpi-icon" style="background:rgba(139,92,246,.1);">
            <svg fill="none" viewBox="0 0 24 24" stroke="#8b5cf6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </div>
        <div class="kpi-label">Сотрудников</div>
        <div class="kpi-value" x-text="fmt(kpi.employeesCount)"></div>
        <div class="kpi-sub">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:12px;height:12px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            уникальных медпредставителей
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-accent" style="background:var(--amber);"></div>
        <div class="kpi-icon" style="background:rgba(245,158,11,.1);">
            <svg fill="none" viewBox="0 0 24 24" stroke="#f59e0b"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="kpi-label">Ср. длительность</div>
        <div class="kpi-value"><span x-text="kpi.avgDuration"></span><span style="font-size:16px;font-weight:500;color:var(--text2);margin-left:4px;">мин</span></div>
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
            <div style="width:44px;height:44px;border-radius:10px;background:rgba(99,102,241,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg style="width:22px;height:22px;" fill="none" viewBox="0 0 24 24" stroke="#6366f1"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:11px;font-weight:600;color:var(--text2);text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Визит к врачу</div>
                <div style="font-size:26px;font-weight:700;color:var(--text1);line-height:1;" x-text="fmt(kpi.doctorVisits)"></div>
                <template x-if="kpi.total > 0">
                    <div style="font-size:11px;color:var(--text3);margin-top:3px;"><span x-text="pct(kpi.doctorVisits, kpi.total)"></span>% от всех визитов</div>
                </template>
            </div>
        </div>
        <div style="margin-top:12px;height:4px;background:var(--border);border-radius:2px;">
            <div style="height:100%;border-radius:2px;background:#6366f1;transition:width .6s;" :style="'width:' + pct(kpi.doctorVisits, kpi.total) + '%'"></div>
        </div>
    </div>

    <div class="kpi-card" style="padding:16px 20px;">
        <div class="kpi-accent" style="background:#0ea5e9;"></div>
        <div style="display:flex;align-items:center;gap:14px;">
            <div style="width:44px;height:44px;border-radius:10px;background:rgba(14,165,233,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg style="width:22px;height:22px;" fill="none" viewBox="0 0 24 24" stroke="#0ea5e9"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:11px;font-weight:600;color:var(--text2);text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Визит в аптеку</div>
                <div style="font-size:26px;font-weight:700;color:var(--text1);line-height:1;" x-text="fmt(kpi.pharmacyVisits)"></div>
                <template x-if="kpi.total > 0">
                    <div style="font-size:11px;color:var(--text3);margin-top:3px;"><span x-text="pct(kpi.pharmacyVisits, kpi.total)"></span>% от всех визитов</div>
                </template>
            </div>
        </div>
        <div style="margin-top:12px;height:4px;background:var(--border);border-radius:2px;">
            <div style="height:100%;border-radius:2px;background:#0ea5e9;transition:width .6s;" :style="'width:' + pct(kpi.pharmacyVisits, kpi.total) + '%'"></div>
        </div>
    </div>

</div>

{{-- ─── OneKey COVERAGE ─── --}}
<div x-show="data.onekeyTotal > 0 || data.pharmOnekeyTotal > 0" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">

    <div class="kpi-card" x-show="data.onekeyTotal > 0" style="padding:16px 20px;">
        <div class="kpi-accent" style="background:#6366f1;"></div>
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(99,102,241,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg style="width:20px;height:20px;" fill="none" viewBox="0 0 24 24" stroke="#6366f1"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div>
                <div style="font-size:11px;font-weight:600;color:var(--text2);text-transform:uppercase;letter-spacing:.06em;">Охват врачей OneKey</div>
                <div style="font-size:11px;color:var(--text3);margin-top:1px;">таргет / вся база OneKey</div>
            </div>
        </div>
        <div style="display:flex;align-items:baseline;gap:8px;margin-bottom:10px;">
            <span style="font-size:32px;font-weight:700;color:#6366f1;line-height:1;" x-text="coverage.doctorPercent + '%'"></span>
            <span style="font-size:13px;color:var(--text2);">
                <span x-text="fmt(coverage.doctorTarget)"></span>
                <span style="color:var(--text3);">/ <span x-text="fmt(data.onekeyTotal)"></span></span>
            </span>
        </div>
        <div style="height:6px;background:var(--border);border-radius:3px;overflow:hidden;">
            <div style="height:100%;border-radius:3px;background:#6366f1;transition:width .6s;" :style="'width:' + coverage.doctorPercent + '%'"></div>
        </div>
    </div>

    <div class="kpi-card" x-show="data.pharmOnekeyTotal > 0" style="padding:16px 20px;">
        <div class="kpi-accent" style="background:#0ea5e9;"></div>
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(14,165,233,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg style="width:20px;height:20px;" fill="none" viewBox="0 0 24 24" stroke="#0ea5e9"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div>
                <div style="font-size:11px;font-weight:600;color:var(--text2);text-transform:uppercase;letter-spacing:.06em;">Охват аптек OneKey</div>
                <div style="font-size:11px;color:var(--text3);margin-top:1px;">таргет / вся база OneKey</div>
            </div>
        </div>
        <div style="display:flex;align-items:baseline;gap:8px;margin-bottom:10px;">
            <span style="font-size:32px;font-weight:700;color:#0ea5e9;line-height:1;" x-text="coverage.pharmacyPercent + '%'"></span>
            <span style="font-size:13px;color:var(--text2);">
                <span x-text="fmt(coverage.pharmacyTarget)"></span>
                <span style="color:var(--text3);">/ <span x-text="fmt(data.pharmOnekeyTotal)"></span></span>
            </span>
        </div>
        <div style="height:6px;background:var(--border);border-radius:3px;overflow:hidden;">
            <div style="height:100%;border-radius:3px;background:#0ea5e9;transition:width .6s;" :style="'width:' + coverage.pharmacyPercent + '%'"></div>
        </div>
    </div>

</div>

{{-- ─── CHARTS ─── --}}
<div class="charts-grid">

    <div class="chart-card">
        <div class="chart-header">
            <div>
                <div class="chart-title">Динамика визитов</div>
                <div class="chart-subtitle">Визиты по месяцам (последние 12, без учёта фильтров ниже)</div>
            </div>
        </div>
        <div class="chart-wrap" style="height:220px;">
            <canvas id="trendChart"></canvas>
        </div>
    </div>

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
            Показано <strong><span x-text="pageInfo().from"></span>–<span x-text="pageInfo().to"></span></strong> из <strong x-text="fmt(kpi.total)"></strong>
        </div>
        <div class="search-wrap">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" class="search-input" x-model="tableSearch" @input="applyFilters()" placeholder="Поиск по таблице...">
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
                        <th :class="{ 'active-sort': sortCol === '{{ $col }}' }">
                            @if($canSort)
                                <span @click="sortBy('{{ $col }}')" style="display:flex;align-items:center;gap:2px;">
                                    {{ $label }}
                                    <span class="sort-icon" x-text="sortCol === '{{ $col }}' ? (sortDir === 'asc' ? '↑' : '↓') : '↕'"></span>
                                </span>
                            @else
                                {{ $label }}
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <template x-if="pageRows().length === 0">
                    <tr>
                        <td colspan="8" style="text-align:center;padding:40px;color:var(--text3);">Нет данных</td>
                    </tr>
                </template>
                <template x-for="(row, idx) in pageRows()" :key="idx">
                    <tr>
                        <td style="white-space:nowrap;color:var(--text2);" x-text="decode(row, 'date')"></td>
                        <td style="font-weight:500;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" x-text="decode(row, 'employee')"></td>
                        <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" :title="decode(row, 'organization')" x-text="decode(row, 'organization')"></td>
                        <td style="color:var(--text2);max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" x-text="decode(row, 'specialty')"></td>
                        <td style="color:var(--text2);" x-text="decode(row, 'town')"></td>
                        <td style="color:var(--text2);" x-text="decode(row, 'province')"></td>
                        <td style="color:var(--text2);white-space:nowrap;" x-text="decode(row, 'type')"></td>
                        <td style="text-align:center;color:var(--text2);" x-text="row[9] ?? '—'"></td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <div class="table-pagination">
        <div style="color:var(--text2);font-size:12px;">Страница <span x-text="page"></span> из <span x-text="pageInfo().lastPage"></span></div>
        <div style="display:flex;gap:6px;">
            <button class="pager-btn" @click="page = 1" :disabled="page <= 1">« Первая</button>
            <button class="pager-btn" @click="page--" :disabled="page <= 1">‹ Назад</button>
            <button class="pager-btn" @click="page++" :disabled="page >= pageInfo().lastPage">Вперёд ›</button>
            <button class="pager-btn" @click="page = pageInfo().lastPage" :disabled="page >= pageInfo().lastPage">Последняя »</button>
        </div>
    </div>
</div>

</div>{{-- end .dash --}}

<script>
const PALETTE = ['#3b82f6','#8b5cf6','#06b6d4','#10b981','#f59e0b','#ef4444','#ec4899','#6366f1','#14b8a6','#f97316','#84cc16','#a855f7'];
const GRID_COLOR = 'rgba(0,0,0,.06)';
const TEXT_COLOR = '#64748b';
const PER_PAGE = 25;
// Позиция полей в строке $rows — см. CallController::ROW_FIELDS (PHP) и 'rowFields' в payload.
const COL = { date:0, employee:1, employeeId:2, organization:3, specialty:4, town:5, province:6, department:7, type:8, duration:9 };
// Позиция полей в doctorTargets/pharmacyTargets (см. CallController::loadMonth) —
// [customer_id/organization_id, employee_id, province, town, department, specialty?].
const TCOL = { id:0, employeeId:1, province:2, town:3, department:4, specialty:5 };

// employee_department в Nobel CRM разбит на пары-варианты одной группы (М/К-суффикс)
// и вдобавок пишется то латиницей "OTC", то кириллицей "ОТС". Единственный источник
// этой карты — TargetClientsService::DEPARTMENT_GROUP_MAP (PHP); сюда она передана
// из контроллера через Blade-директиву ниже, а не продублирована в JS — иначе
// расходится, как уже случалось. Фильтр "Группа" работает с нормализованными
// названиями (OTC 1/2/3).
const DEPARTMENT_GROUP_MAP = @json($departmentGroupMap);
function normalizeDept(raw) { return DEPARTMENT_GROUP_MAP[raw] || raw; }

let specChartType = 'bar';

function setSpecChartType(type) {
    specChartType = type;
    document.getElementById('btn-bar').style.background      = type === 'bar'      ? 'var(--blue)' : 'var(--bg)';
    document.getElementById('btn-bar').style.color           = type === 'bar'      ? '#fff'        : 'var(--text2)';
    document.getElementById('btn-doughnut').style.background = type === 'doughnut' ? 'var(--blue)' : 'var(--bg)';
    document.getElementById('btn-doughnut').style.color      = type === 'doughnut' ? '#fff'        : 'var(--text2)';
    const el = document.getElementById('calls-dash');
    if (el && el._x_dataStack) {
        el._x_dataStack[0].buildTrendAndSpecChart();
    }
}

function callsDash(initialData, initialMonth) {
    return {
        month: initialMonth,
        filtersOpen: false,
        tableSearch: '',
        loading: false,
        data: initialData,
        filters: { province: [], town: [], specialty: [], department: [], employeeId: null },
        sortCol: 'appointment_Date',
        sortDir: 'desc',
        page: 1,
        filtered: [],
        sorted: [],
        kpi: { total: 0, employeesCount: 0, avgDuration: 0, doctorVisits: 0, pharmacyVisits: 0, visitsPerEmployee: 0 },
        topRegions: [],
        topSpecialties: [],
        coverage: { doctorTarget: 0, pharmacyTarget: 0, doctorPercent: 0, pharmacyPercent: 0 },

        init() {
            this.applyFilters();
            this.$nextTick(() => this.buildTrendAndSpecChart());
        },

        fmt(n) { return String(n ?? 0).replace(/\B(?=(\d{3})+(?!\d))/g, ' '); },
        pct(part, total) { return total > 0 ? Math.round(part / total * 100) : 0; },
        decode(row, col) { return this.data.dictionaries[col][row[COL[col]]]; },
        sortedDict(col) { return [...this.data.dictionaries[col]].sort((a, b) => a.localeCompare(b)); },
        // Уникальные НОРМАЛИЗОВАННЫЕ названия групп для фильтра (OTC 1 М + ОТС 1 К => OTC 1) —
        // см. DEPARTMENT_GROUP_MAP выше.
        sortedNormalizedDepts() {
            return [...new Set(this.data.dictionaries.department.map(normalizeDept))].sort((a, b) => a.localeCompare(b));
        },
        // По списку выбранных нормализованных групп — набор индексов СЫРЫХ значений
        // словаря department, которые под них попадают (обратный маппинг).
        deptIndexSet(names) {
            if (!names.length) return null;
            const set = new Set();
            this.data.dictionaries.department.forEach((raw, idx) => {
                if (names.includes(normalizeDept(raw))) set.add(idx);
            });
            return set;
        },

        activeFiltersCount() {
            return this.filters.province.length + this.filters.town.length + this.filters.specialty.length
                + this.filters.department.length + (this.filters.employeeId ? 1 : 0) + (this.tableSearch.trim() ? 1 : 0);
        },

        // Все фильтры (регион/город/специальность/группа/сотрудник/поиск) применяются
        // мгновенно, целиком на уже загруженных в браузер данных месяца — без запроса
        // к серверу. Единственное, что реально идёт на сервер — смена месяца (loadMonth).
        // Общие для $rows и таргет-листов наборы допустимых индексов словаря по
        // текущим фильтрам — считаются один раз и переиспользуются, чтобы не
        // делать indexOf() дважды на разных наборах данных.
        buildFilterSets() {
            const d = this.data.dictionaries;
            const emp = this.filters.employeeId ? this.data.empList.find(e => e.value === this.filters.employeeId) : null;
            return {
                provSet: this.filters.province.length ? new Set(this.filters.province.map(v => d.province.indexOf(v))) : null,
                townSet: this.filters.town.length ? new Set(this.filters.town.map(v => d.town.indexOf(v))) : null,
                specSet: this.filters.specialty.length ? new Set(this.filters.specialty.map(v => d.specialty.indexOf(v))) : null,
                deptSet: this.deptIndexSet(this.filters.department),
                crmSet: emp ? new Set(emp.crmIds || []) : null,
            };
        },

        applyFilters() {
            const d = this.data.dictionaries;
            const { provSet, townSet, specSet, deptSet, crmSet } = this.buildFilterSets();
            const q = this.tableSearch.trim().toLowerCase();

            this.filtered = this.data.rows.filter(r => {
                if (provSet && !provSet.has(r[COL.province])) return false;
                if (townSet && !townSet.has(r[COL.town])) return false;
                if (specSet && !specSet.has(r[COL.specialty])) return false;
                if (deptSet && !deptSet.has(r[COL.department])) return false;
                if (crmSet && !crmSet.has(r[COL.employeeId])) return false;
                if (q) {
                    const text = [d.date[r[COL.date]], d.employee[r[COL.employee]], d.organization[r[COL.organization]],
                                  d.specialty[r[COL.specialty]], d.town[r[COL.town]], d.province[r[COL.province]],
                                  d.type[r[COL.type]]].join(' ').toLowerCase();
                    if (!text.includes(q)) return false;
                }
                return true;
            });

            this.recomputeKpi();
            this.recomputeSort();
            this.recomputeCoverage({ provSet, townSet, specSet, deptSet, crmSet });
            this.page = 1;
            // Баг: раньше topRegions/topSpecialties пересчитывались в recomputeKpi(),
            // но графики Chart.js (canvas) не перерисовывались — смена региона/
            // специальности/группы/сотрудника обновляла KPI-карточки и таблицу,
            // но "Топ регионов"/"Топ специальностей" оставались от предыдущего
            // состояния. Теперь перерисовываем оба графика при каждом изменении.
            this.buildRegionsAndSpecCharts();
        },

        // Охват = Таргет/OneKey. Таргет-лист (doctorTargets/pharmacyTargets) —
        // ОТДЕЛЬНЫЙ от $rows набор (уникальные клиенты, все статусы визита, а
        // не только "Выполнено" — таргет это план, а не факт), но фильтруется
        // теми же region/town/department/employee критериями, что и таблица.
        // OneKey-знаменатель — весь справочник, от фильтров не зависит.
        recomputeCoverage({ provSet, townSet, specSet, deptSet, crmSet }) {
            const countTargets = (list, hasSpecialty) => {
                const ids = new Set();
                for (const r of list) {
                    if (provSet && !provSet.has(r[TCOL.province])) continue;
                    if (townSet && !townSet.has(r[TCOL.town])) continue;
                    if (deptSet && !deptSet.has(r[TCOL.department])) continue;
                    if (crmSet && !crmSet.has(r[TCOL.employeeId])) continue;
                    // У аптек нет специальности — если фильтр по специальности активен,
                    // ни одна аптека под него не подходит (корректно даёт 0, не все).
                    if (specSet && (!hasSpecialty || !specSet.has(r[TCOL.specialty]))) continue;
                    ids.add(r[TCOL.id]);
                }
                return ids.size;
            };

            const doctorTarget = countTargets(this.data.doctorTargets, true);
            const pharmacyTarget = countTargets(this.data.pharmacyTargets, false);
            this.coverage = {
                doctorTarget, pharmacyTarget,
                doctorPercent: this.pct(doctorTarget, this.data.onekeyTotal),
                pharmacyPercent: this.pct(pharmacyTarget, this.data.pharmOnekeyTotal),
            };
        },

        recomputeKpi() {
            const d = this.data.dictionaries;
            const rows = this.filtered;
            const doctorIdx = d.type.indexOf('Визит к врачу');
            const pharmacyIdx = d.type.indexOf('Визит в аптеку');
            const employees = new Set();
            let durSum = 0, durCount = 0, doctorVisits = 0, pharmacyVisits = 0;
            for (const r of rows) {
                employees.add(r[COL.employee]);
                if (r[COL.duration] > 0) { durSum += r[COL.duration]; durCount++; }
                if (r[COL.type] === doctorIdx) doctorVisits++;
                else if (r[COL.type] === pharmacyIdx) pharmacyVisits++;
            }
            const total = rows.length;
            const employeesCount = employees.size;
            this.kpi = {
                total, employeesCount,
                avgDuration: durCount > 0 ? Math.round(durSum / durCount) : 0,
                doctorVisits, pharmacyVisits,
                visitsPerEmployee: employeesCount > 0 ? Math.round(total / employeesCount * 10) / 10 : 0,
            };

            this.topRegions = this.topBy(rows, COL.province, d.province, 10);
            this.topSpecialties = this.topBy(rows, COL.specialty, d.specialty, 12);
        },

        topBy(rows, colIdx, dict, limit) {
            const counts = new Map();
            for (const r of rows) counts.set(r[colIdx], (counts.get(r[colIdx]) || 0) + 1);
            return Array.from(counts.entries())
                .sort((a, b) => b[1] - a[1])
                .slice(0, limit)
                .map(([idx, total]) => ({ name: dict[idx], total }));
        },

        sortBy(col) {
            const sortable = ['appointment_Date', 'employee', 'organization', 'province', 'town', 'appointment_duration'];
            if (!sortable.includes(col)) return;
            this.sortDir = (this.sortCol === col && this.sortDir === 'asc') ? 'desc' : 'asc';
            this.sortCol = col;
            this.recomputeSort();
            this.page = 1;
        },

        recomputeSort() {
            const d = this.data.dictionaries;
            const dir = this.sortDir === 'asc' ? 1 : -1;
            const arr = this.filtered.slice();

            if (this.sortCol === 'appointment_duration') {
                arr.sort((a, b) => dir * ((a[COL.duration] || 0) - (b[COL.duration] || 0)));
            } else if (this.sortCol === 'appointment_Date') {
                const key = r => { const [dd, mm, yyyy] = d.date[r[COL.date]].split('.'); return yyyy + mm + dd; };
                arr.sort((a, b) => dir * key(a).localeCompare(key(b)));
            } else {
                const colMap = { employee: 'employee', organization: 'organization', province: 'province', town: 'town' };
                const dictName = colMap[this.sortCol];
                const idxKey = COL[this.sortCol];
                arr.sort((a, b) => dir * d[dictName][a[idxKey]].localeCompare(d[dictName][b[idxKey]]));
            }
            this.sorted = arr;
        },

        pageRows() {
            const start = (this.page - 1) * PER_PAGE;
            return this.sorted.slice(start, start + PER_PAGE);
        },

        pageInfo() {
            const total = this.sorted.length;
            const lastPage = Math.max(1, Math.ceil(total / PER_PAGE));
            const from = total === 0 ? 0 : (this.page - 1) * PER_PAGE + 1;
            const to = Math.min(this.page * PER_PAGE, total);
            return { total, lastPage, from, to };
        },

        resetFilters() {
            window.dispatchEvent(new CustomEvent('reset-filters'));
            this.filters = { province: [], town: [], specialty: [], department: [], employeeId: null };
            this.tableSearch = '';
            this.sortCol = 'appointment_Date';
            this.sortDir = 'desc';
            this.applyFilters();
        },

        async loadMonth() {
            this.loading = true;
            try {
                const params = new URLSearchParams({ month: this.month });
                const res = await fetch('{{ route('calls.data') }}?' + params.toString(), {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });
                if (!res.ok) throw new Error('http_' + res.status);
                const json = await res.json();
                if (json.error) { this.data = { ...this.data, error: json.error }; return; }

                this.data = json;
                // detail с новыми списками опций — у нового месяца другой набор
                // встретившихся регионов/городов/специальностей/групп, выпадающие
                // списки фильтров должны обновиться, а не остаться от старого месяца.
                window.dispatchEvent(new CustomEvent('reset-filters', { detail: {
                    province: this.sortedDict('province'), town: this.sortedDict('town'),
                    specialty: this.sortedDict('specialty'), department: this.sortedNormalizedDepts(),
                } }));
                this.filters = { province: [], town: [], specialty: [], department: [], employeeId: null };
                this.tableSearch = '';
                this.$nextTick(() => {
                    this.applyFilters();
                    this.buildTrendAndSpecChart();
                    const url = new URL(window.location.href);
                    url.searchParams.set('month', this.month);
                    window.history.replaceState({}, '', url);
                });
            } catch (e) {
                this.data = { ...this.data, error: 'Не удалось получить данные из Nobel CRM. Попробуйте позже.' };
            } finally {
                this.loading = false;
            }
        },

        beforeExport(e) {
            const exportForm = e.target;
            exportForm.querySelectorAll('input[data-dyn]').forEach(el => el.remove());
            const add = (name, value) => {
                const input = document.createElement('input');
                input.type = 'hidden'; input.name = name; input.value = value; input.dataset.dyn = '1';
                exportForm.appendChild(input);
            };
            add('month', this.month);
            this.filters.province.forEach(v => add('province[]', v));
            this.filters.town.forEach(v => add('town[]', v));
            this.filters.specialty.forEach(v => add('customer_spesiality[]', v));
            this.filters.department.forEach(v => add('employee_department[]', v));
            if (this.filters.employeeId) add('employee_id', this.filters.employeeId);
        },

        buildTrendAndSpecChart() {
            Chart.defaults.color = TEXT_COLOR;

            const trend = this.data.trend || [];
            const tCtx = document.getElementById('trendChart');
            if (tCtx) {
                if (tCtx._chart) tCtx._chart.destroy();
                tCtx._chart = new Chart(tCtx, {
                    type: 'line',
                    data: {
                        labels: trend.map(d => d.month),
                        datasets: [{
                            label: 'Визитов',
                            data: trend.map(d => d.total),
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59,130,246,.08)',
                            borderWidth: 2, pointRadius: 3, pointHoverRadius: 5, fill: true, tension: .35,
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
                        scales: {
                            x: { grid: { color: GRID_COLOR }, ticks: { color: TEXT_COLOR, maxTicksLimit: 8 } },
                            y: { grid: { color: GRID_COLOR }, ticks: { color: TEXT_COLOR }, beginAtZero: true }
                        }
                    }
                });
            }
            this.buildRegionsAndSpecCharts();
        },

        buildRegionsAndSpecCharts() {
            const rCtx = document.getElementById('regionsChart');
            if (rCtx) {
                if (rCtx._chart) rCtx._chart.destroy();
                rCtx._chart = new Chart(rCtx, {
                    type: 'bar',
                    data: {
                        labels: this.topRegions.map(d => d.name ? d.name.substring(0, 18) : '—'),
                        datasets: [{
                            data: this.topRegions.map(d => d.total),
                            backgroundColor: PALETTE.map(c => c + 'cc'), borderColor: PALETTE, borderWidth: 1, borderRadius: 4,
                        }]
                    },
                    options: {
                        indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ' ' + ctx.parsed.x.toLocaleString() } } },
                        scales: {
                            x: { grid: { color: GRID_COLOR }, ticks: { color: TEXT_COLOR } },
                            y: { grid: { display: false }, ticks: { color: TEXT_COLOR, font: { size: 11 } } }
                        }
                    }
                });
            }

            const sCtx = document.getElementById('specChart');
            if (sCtx) {
                if (sCtx._chart) sCtx._chart.destroy();
                const labels = this.topSpecialties.map(d => d.name || '—');
                const values = this.topSpecialties.map(d => d.total);
                const bgColors = PALETTE.map(c => c + 'cc');

                if (specChartType === 'doughnut') {
                    sCtx._chart = new Chart(sCtx, {
                        type: 'doughnut',
                        data: { labels, datasets: [{ data: values, backgroundColor: bgColors, borderColor: '#fff', borderWidth: 2 }] },
                        options: {
                            responsive: true, maintainAspectRatio: false, cutout: '60%',
                            plugins: {
                                legend: { position: 'right', labels: { color: TEXT_COLOR, font: { size: 11 }, boxWidth: 12, padding: 10,
                                    generateLabels: chart => chart.data.labels.map((l, i) => ({ text: l.length > 22 ? l.substring(0,22)+'…' : l, fillStyle: bgColors[i], strokeStyle: bgColors[i], index: i })) } },
                                tooltip: { callbacks: { label: ctx => ' ' + ctx.label + ': ' + ctx.parsed.toLocaleString() } }
                            }
                        }
                    });
                } else {
                    const total = values.reduce((a, b) => a + b, 0);
                    sCtx._chart = new Chart(sCtx, {
                        type: 'bar',
                        data: { labels, datasets: [{ data: values, backgroundColor: bgColors, borderColor: PALETTE, borderWidth: 1, borderRadius: 4 }] },
                        options: {
                            indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: { callbacks: { label: ctx => { const val = ctx.parsed.x; const p = total > 0 ? (val / total * 100).toFixed(1) : 0; return `  ${val.toLocaleString()} визитов (${p}%)`; } } }
                            },
                            scales: {
                                x: { grid: { color: GRID_COLOR }, ticks: { color: TEXT_COLOR }, beginAtZero: true },
                                y: { grid: { display: false }, ticks: { color: TEXT_COLOR, font: { size: 11 } } }
                            }
                        }
                    });
                }
            }
        }
    }
}

// Каждый multiSelect/callsEmpPicker живёт в своей x-data области и не имеет
// прямого доступа к filters/applyFilters() родителя — вместо этого рассылает
// событие 'ms-change', которое ловит родитель (callsDash) на внешнем div и
// атомарно обновляет filters + пересчитывает всё разом. Так надёжнее, чем
// x-effect + $nextTick: нет риска гонки между копированием selected и вызовом
// applyFilters() — оба шага происходят в одном обработчике родителя.
function multiSelect(name, options) {
    return {
        name, open: false, q: '', options, filtered: options, selected: [],
        filter() { this.filtered = this.options.filter(i => i.toLowerCase().includes(this.q.toLowerCase())); },
        toggle(item) {
            this.selected = this.selected.includes(item) ? this.selected.filter(i => i !== item) : [...this.selected, item];
            this.$dispatch('ms-change', { name: this.name, selected: this.selected });
        },
    };
}

function callsEmpPicker(list) {
    return {
        list, selected: null, query: '', open: false,
        get filtered() {
            const q = this.query.trim().toLowerCase();
            if (!q) return this.list.slice(0, 80);
            return this.list.filter(e => e.label.toLowerCase().includes(q)).slice(0, 80);
        },
        choose(emp) {
            this.selected = emp.value;
            this.query = emp.label;
            this.open = false;
            this.$dispatch('ms-change', { name: 'employeeId', selected: this.selected });
        },
        clear() {
            this.selected = null;
            this.query = '';
            this.open = false;
            this.$dispatch('ms-change', { name: 'employeeId', selected: this.selected });
        },
    };
}
</script>

@endsection
