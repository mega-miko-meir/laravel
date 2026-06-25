@extends('layout')
@section('content')

<style>
:root {
    --bg:      #f1f5f9;
    --card:    #ffffff;
    --border:  #e2e8f0;
    --text1:   #1e293b;
    --text2:   #64748b;
    --text3:   #94a3b8;
    --blue:    #3b82f6;
    --green:   #10b981;
    --amber:   #f59e0b;
    --red:     #ef4444;
    --purple:  #6366f1;
    --sky:     #0ea5e9;
    --shadow:  0 1px 3px rgba(0,0,0,.07);
    --radius:  12px;
}

.lb-wrap   { max-width:1500px; margin:0 auto; padding-bottom:40px; }
.lb-header { display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;padding-top:4px; }
.lb-title  { font-size:22px;font-weight:700;color:var(--text1);display:flex;align-items:center;gap:10px; }
.lb-title span { font-size:13px;font-weight:500;color:var(--text3);background:var(--card);border:1px solid var(--border);border-radius:20px;padding:2px 10px; }

.btn { display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;border:1px solid var(--border);background:var(--card);color:var(--text2);text-decoration:none;transition:all .15s; }
.btn:hover { background:var(--bg);color:var(--text1); }
.btn-green { background:#16a34a;color:#fff;border-color:#16a34a; }
.btn-green:hover { opacity:.9;background:#16a34a;color:#fff; }

.filter-panel { background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:10px 16px;margin-bottom:20px;box-shadow:var(--shadow); }
.filter-grid  { display:flex;flex-wrap:wrap;gap:8px;align-items:flex-end; }
.filter-field { display:flex;flex-direction:column;gap:3px; }
.filter-label { font-size:10px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:.06em; }
.filter-input { background:var(--bg);border:1px solid var(--border);border-radius:7px;padding:0 10px;height:30px;font-size:12px;color:var(--text1);outline:none; }
.filter-input:focus { border-color:var(--blue); }

.lb-card    { background:var(--card);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden; }
.lb-toolbar { display:flex;align-items:center;justify-content:space-between;gap:16px;padding:12px 16px;border-bottom:1px solid var(--border);flex-wrap:wrap; }
.lb-info    { font-size:13px;color:var(--text2); }
.lb-info strong { color:var(--text1); }

.lb-meta { display:flex;gap:20px;flex-wrap:wrap; }
.meta-chip {
    display:flex;align-items:center;gap:6px;
    background:var(--bg);border:1px solid var(--border);border-radius:8px;
    padding:5px 12px;font-size:12px;color:var(--text2);
}
.meta-chip strong { color:var(--text1);font-size:13px; }

.lb-table { width:100%;border-collapse:collapse;font-size:13px; }
.lb-table th {
    padding:9px 13px;text-align:left;font-size:10px;font-weight:600;
    text-transform:uppercase;letter-spacing:.06em;color:var(--text2);
    background:var(--bg);white-space:nowrap;cursor:pointer;user-select:none;
    border-bottom:1px solid var(--border);
}
.lb-table th a { color:inherit;text-decoration:none;display:flex;align-items:center;justify-content:flex-end;gap:2px; }
.lb-table th:hover { color:var(--blue); }
.lb-table th.sort-active { color:var(--blue); }
.sort-ico { margin-left:3px;opacity:.4;font-size:10px; }
.sort-active .sort-ico { opacity:1; }
.lb-table td { padding:10px 13px;border-bottom:1px solid var(--border);color:var(--text1);vertical-align:middle; }
.lb-table tr:last-child td { border-bottom:none; }
.lb-table tbody tr:hover td { background:#f8fafc; }

.lb-table th.group-sep,
.lb-table td.group-sep { border-left:2px solid var(--border); }

.th-group {
    padding:5px 13px 4px;font-size:9px;font-weight:700;text-transform:uppercase;
    letter-spacing:.08em;color:var(--text3);background:var(--bg);
    border-bottom:1px solid var(--border) !important;text-align:center;
    cursor:default;
}

.rank-cell { width:40px;text-align:center;font-weight:700;font-size:15px; }
.rank-1 { color:#f59e0b; }
.rank-2 { color:#94a3b8; }
.rank-3 { color:#b45309; }
.rank-n { color:#cbd5e1;font-size:12px; }

.emp-name { font-weight:600;color:var(--text1); }
.emp-pos  { font-size:11px;color:var(--text3);margin-top:1px; }

.num-cell { text-align:right;font-variant-numeric:tabular-nums; }
.num-big  { font-weight:700; }

.bar-wrap { margin-top:4px;height:3px;background:var(--border);border-radius:2px;min-width:50px; }
.bar-fill { height:100%;border-radius:2px;transition:width .5s; }

.pct-cell { text-align:right; }
.pct-val  { font-size:13px;font-weight:700;margin-bottom:2px; }
.pct-sub  { font-size:11px;color:var(--text3); }
.pct-bar  { height:4px;background:var(--border);border-radius:2px;min-width:70px;margin-top:3px; }
.pct-bar-fill { height:100%;border-radius:2px;max-width:100%; }
</style>

<div class="lb-wrap" style="background:var(--bg);min-height:100%;">

{{-- Header --}}
<div class="lb-header">
    <div class="lb-title">
        Рейтинг МП
        <span>{{ $rows->count() }} сотрудников</span>
    </div>
    <form method="POST" action="{{ route('leaderboard.export') }}">
        @csrf
        @foreach(request()->except('_token','sort','dir') as $k => $v)
            <input type="hidden" name="{{ $k }}" value="{{ $v }}">
        @endforeach
        <button type="submit" class="btn btn-green">
            <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Скачать CSV
        </button>
    </form>
</div>

{{-- Filters --}}
<div class="filter-panel">
    <form method="GET" action="{{ route('leaderboard.index') }}">
        <div class="filter-grid">
            <div class="filter-field">
                <label class="filter-label">Дата от</label>
                <input type="date" name="date_from" class="filter-input" value="{{ $dateFrom }}" style="width:130px;">
            </div>
            <div class="filter-field">
                <label class="filter-label">Дата до</label>
                <input type="date" name="date_to" class="filter-input" value="{{ $dateTo }}" style="width:130px;">
            </div>
            <div class="filter-field" style="flex-direction:row;gap:6px;align-items:flex-end;">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="dir" value="{{ $dir }}">
                <button type="submit" class="btn" style="background:#1d4ed8;color:#fff;border-color:#1d4ed8;height:30px;padding:0 14px;font-size:12px;">
                    Применить
                </button>
                @if($dateFrom || $dateTo)
                <a href="{{ route('leaderboard.index', ['sort' => $sort, 'dir' => $dir]) }}"
                   class="btn" style="height:30px;padding:0 12px;font-size:12px;">Сбросить</a>
                @endif
            </div>
        </div>
    </form>
</div>

{{-- Table --}}
@php
    $maxVisits = $rows->max('total_visits') ?: 1;
    $thUrl = fn(string $col) => route('leaderboard.index', array_merge(
        request()->except('sort','dir'),
        ['sort' => $col, 'dir' => ($sort === $col && $dir === 'desc') ? 'asc' : 'desc']
    ));
    $sortIco  = fn(string $col) => $sort === $col ? ($dir === 'desc' ? '↓' : '↑') : '↕';
    $pctColor = fn(int $pct) => $pct >= 80 ? '#16a34a' : ($pct >= 50 ? '#f59e0b' : '#ef4444');
@endphp

<div class="lb-card">
    <div class="lb-toolbar">
        <div class="lb-info">
            Показано <strong>{{ $rows->count() }}</strong> сотрудников
            @if($dateFrom || $dateTo)
                &nbsp;·&nbsp; {{ $dateFrom ?? '...' }} — {{ $dateTo ?? '...' }}
            @else
                &nbsp;·&nbsp; текущий месяц
            @endif
        </div>
        <div class="lb-meta">
            <div class="meta-chip">
                Рабочих дней: <strong>{{ $workingDays }}</strong>
            </div>
            <div class="meta-chip">
                Таргет визитов: <strong>{{ $workingDays }} × {{ \App\Http\Controllers\LeaderboardController::DAILY_TARGET }} = {{ $callTarget }}</strong>
            </div>
            <div class="meta-chip">
                Частота: <strong>{{ \App\Http\Controllers\LeaderboardController::FREQUENCY }}×</strong>
            </div>
        </div>
    </div>

    @if($rows->isEmpty())
    <div style="padding:48px;text-align:center;color:var(--text3);font-size:14px;">
        Нет данных для выбранного периода
    </div>
    @else
    <div style="overflow-x:auto;">
    <table class="lb-table">
        <thead>
            {{-- Строка 1: группы --}}
            <tr>
                <th class="rank-cell" rowspan="2" style="cursor:default;">#</th>
                <th rowspan="2" style="cursor:default;min-width:160px;">Сотрудник</th>

                {{-- Реализация --}}
                <th colspan="2" class="th-group group-sep" style="color:#1d4ed8;">
                    Реализация визитов
                </th>

                {{-- Врачи --}}
                <th colspan="3" class="th-group group-sep" style="color:var(--purple);">
                    Врачи
                </th>

                {{-- Аптеки --}}
                <th colspan="3" class="th-group group-sep" style="color:var(--sky);">
                    Аптеки
                </th>

                {{-- Доп --}}
                <th rowspan="2" class="group-sep" style="text-align:right;cursor:default;white-space:nowrap;color:var(--text2);">
                    Ср. длит.
                </th>
            </tr>

            {{-- Строка 2: подзаголовки --}}
            <tr>
                {{-- Реализация --}}
                <th class="{{ $sort==='total_visits' ? 'sort-active' : '' }} group-sep" style="text-align:right;">
                    <a href="{{ $thUrl('total_visits') }}">
                        Факт<span class="sort-ico">{!! $sortIco('total_visits') !!}</span>
                    </a>
                </th>
                <th class="{{ $sort==='call_pct' ? 'sort-active' : '' }}" style="text-align:right;">
                    <a href="{{ $thUrl('call_pct') }}">
                        % выполн.<span class="sort-ico">{!! $sortIco('call_pct') !!}</span>
                    </a>
                </th>

                {{-- Врачи --}}
                <th class="{{ $sort==='base_doctors' ? 'sort-active' : '' }} group-sep" style="text-align:right;">
                    <a href="{{ $thUrl('base_doctors') }}">
                        База<span class="sort-ico">{!! $sortIco('base_doctors') !!}</span>
                    </a>
                </th>
                <th class="{{ $sort==='doctor_visits' ? 'sort-active' : '' }}" style="text-align:right;">
                    <a href="{{ $thUrl('doctor_visits') }}">
                        Визиты<span class="sort-ico">{!! $sortIco('doctor_visits') !!}</span>
                    </a>
                </th>
                <th class="{{ $sort==='freq_pct_doc' ? 'sort-active' : '' }}" style="text-align:right;">
                    <a href="{{ $thUrl('freq_pct_doc') }}">
                        Частота<span class="sort-ico">{!! $sortIco('freq_pct_doc') !!}</span>
                    </a>
                </th>

                {{-- Аптеки --}}
                <th class="{{ $sort==='base_pharmacies' ? 'sort-active' : '' }} group-sep" style="text-align:right;">
                    <a href="{{ $thUrl('base_pharmacies') }}">
                        База<span class="sort-ico">{!! $sortIco('base_pharmacies') !!}</span>
                    </a>
                </th>
                <th class="{{ $sort==='pharmacy_visits' ? 'sort-active' : '' }}" style="text-align:right;">
                    <a href="{{ $thUrl('pharmacy_visits') }}">
                        Визиты<span class="sort-ico">{!! $sortIco('pharmacy_visits') !!}</span>
                    </a>
                </th>
                <th class="{{ $sort==='freq_pct_phar' ? 'sort-active' : '' }}" style="text-align:right;">
                    <a href="{{ $thUrl('freq_pct_phar') }}">
                        Частота<span class="sort-ico">{!! $sortIco('freq_pct_phar') !!}</span>
                    </a>
                </th>
            </tr>
        </thead>
        <tbody>
        @foreach($rows as $i => $row)
        @php
            $rank      = $i + 1;
            $rankClass = match($rank) { 1 => 'rank-1', 2 => 'rank-2', 3 => 'rank-3', default => 'rank-n' };
            $visitPct  = $maxVisits > 0 ? round($row['total_visits'] / $maxVisits * 100) : 0;
            $cCall     = $pctColor($row['call_pct']);
            $cDoc      = $pctColor($row['freq_pct_doc']);
            $cPhar     = $pctColor($row['freq_pct_phar']);
        @endphp
        <tr>
            <td class="rank-cell"><span class="{{ $rankClass }}">{{ $rank }}</span></td>

            <td>
                <div class="emp-name">
                    <a href="{{ route('employees.show', $row['id']) }}"
                       style="color:inherit;text-decoration:none;"
                       onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='inherit'">
                        {{ $row['name'] }}
                    </a>
                </div>
                @if($row['position'])
                <div class="emp-pos">{{ $row['position'] }}</div>
                @endif
            </td>

            {{-- Реализация: факт --}}
            <td class="num-cell group-sep">
                <div class="num-big">{{ number_format($row['total_visits'], 0, '.', ' ') }}</div>
                <div class="pct-sub">/ {{ $callTarget }}</div>
                <div class="bar-wrap"><div class="bar-fill" style="background:#3b82f6;width:{{ $visitPct }}%;"></div></div>
            </td>

            {{-- Реализация: % --}}
            <td class="pct-cell">
                @if($callTarget > 0)
                    <div class="pct-val" style="color:{{ $cCall }};">{{ $row['call_pct'] }}%</div>
                    <div class="pct-bar">
                        <div class="pct-bar-fill" style="background:{{ $cCall }};width:{{ min($row['call_pct'],100) }}%;"></div>
                    </div>
                @else
                    <span style="color:var(--text3);">—</span>
                @endif
            </td>

            {{-- Врачи: база --}}
            <td class="num-cell group-sep" style="color:var(--purple);">
                @if($row['base_doctors'] > 0)
                    <div class="num-big">{{ $row['base_doctors'] }}</div>
                @else
                    <span style="color:var(--text3);">—</span>
                @endif
            </td>

            {{-- Врачи: визиты --}}
            <td class="num-cell" style="color:var(--purple);">
                @if($row['doctor_visits'] > 0)
                    {{ number_format($row['doctor_visits'], 0, '.', ' ') }}
                @else
                    <span style="color:var(--text3);">—</span>
                @endif
            </td>

            {{-- Врачи: частота --}}
            <td class="pct-cell">
                @if($row['freq_target_doc'] > 0)
                    <div class="pct-val" style="color:{{ $cDoc }};">{{ $row['freq_pct_doc'] }}%</div>
                    <div style="font-size:11px;color:var(--text3);">{{ $row['doctor_visits'] }} / {{ $row['freq_target_doc'] }}</div>
                    <div class="pct-bar">
                        <div class="pct-bar-fill" style="background:{{ $cDoc }};width:{{ min($row['freq_pct_doc'],100) }}%;"></div>
                    </div>
                @else
                    <span style="color:var(--text3);">—</span>
                @endif
            </td>

            {{-- Аптеки: база --}}
            <td class="num-cell group-sep" style="color:var(--sky);">
                @if($row['base_pharmacies'] > 0)
                    <div class="num-big">{{ $row['base_pharmacies'] }}</div>
                @else
                    <span style="color:var(--text3);">—</span>
                @endif
            </td>

            {{-- Аптеки: визиты --}}
            <td class="num-cell" style="color:var(--sky);">
                @if($row['pharmacy_visits'] > 0)
                    {{ number_format($row['pharmacy_visits'], 0, '.', ' ') }}
                @else
                    <span style="color:var(--text3);">—</span>
                @endif
            </td>

            {{-- Аптеки: частота --}}
            <td class="pct-cell">
                @if($row['freq_target_phar'] > 0)
                    <div class="pct-val" style="color:{{ $cPhar }};">{{ $row['freq_pct_phar'] }}%</div>
                    <div style="font-size:11px;color:var(--text3);">{{ $row['pharmacy_visits'] }} / {{ $row['freq_target_phar'] }}</div>
                    <div class="pct-bar">
                        <div class="pct-bar-fill" style="background:{{ $cPhar }};width:{{ min($row['freq_pct_phar'],100) }}%;"></div>
                    </div>
                @else
                    <span style="color:var(--text3);">—</span>
                @endif
            </td>

            {{-- Ср. длительность --}}
            <td class="num-cell group-sep">
                @if($row['avg_duration'] > 0)
                    <span style="color:var(--text2);">{{ $row['avg_duration'] }}<span style="font-size:11px;"> мин</span></span>
                @else
                    <span style="color:var(--text3);">—</span>
                @endif
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>

</div>
@endsection
