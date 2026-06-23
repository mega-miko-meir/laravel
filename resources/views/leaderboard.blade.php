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
    --purple:  #8b5cf6;
    --shadow:  0 1px 3px rgba(0,0,0,.07);
    --shadow-md: 0 4px 6px rgba(0,0,0,.07);
    --radius:  12px;
}

.lb-wrap  { max-width:1300px; margin:0 auto; }
.lb-header{ display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;padding-top:4px; }
.lb-title { font-size:22px;font-weight:700;color:var(--text1);display:flex;align-items:center;gap:10px; }
.lb-title span { font-size:13px;font-weight:500;color:var(--text3);background:var(--card);border:1px solid var(--border);border-radius:20px;padding:2px 10px; }

.btn {
    display:inline-flex;align-items:center;gap:6px;
    padding:7px 14px;border-radius:8px;font-size:13px;font-weight:500;
    cursor:pointer;border:1px solid var(--border);background:var(--card);
    color:var(--text2);text-decoration:none;transition:all .15s;
}
.btn:hover { background:var(--bg);color:var(--text1); }
.btn-green { background:#16a34a;color:#fff;border-color:#16a34a; }
.btn-green:hover { opacity:.9;background:#16a34a;color:#fff; }

.filter-panel { background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:10px 16px;margin-bottom:20px;box-shadow:var(--shadow); }
.filter-grid  { display:flex;flex-wrap:wrap;gap:8px;align-items:flex-end; }
.filter-field { display:flex;flex-direction:column;gap:3px; }
.filter-label { font-size:10px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:.06em; }
.filter-sel   { background:var(--bg);border:1px solid var(--border);border-radius:7px;padding:0 8px;height:30px;font-size:12px;color:var(--text1);outline:none; }
.filter-sel:focus { border-color:var(--blue); }
.filter-input { background:var(--bg);border:1px solid var(--border);border-radius:7px;padding:0 10px;height:30px;font-size:12px;color:var(--text1);outline:none; }
.filter-input:focus { border-color:var(--blue); }

.lb-card {
    background:var(--card);border:1px solid var(--border);border-radius:var(--radius);
    box-shadow:var(--shadow);overflow:hidden;
}
.lb-toolbar {
    display:flex;align-items:center;justify-content:space-between;gap:12px;
    padding:14px 16px;border-bottom:1px solid var(--border);flex-wrap:wrap;
}
.lb-info { font-size:13px;color:var(--text2); }
.lb-info strong { color:var(--text1); }

.lb-table { width:100%;border-collapse:collapse;font-size:13px; }
.lb-table th {
    padding:10px 14px;text-align:left;font-size:10px;font-weight:600;
    text-transform:uppercase;letter-spacing:.06em;color:var(--text2);
    background:var(--bg);white-space:nowrap;cursor:pointer;user-select:none;
    border-bottom:1px solid var(--border);
}
.lb-table th:hover { color:var(--blue); }
.lb-table th.sort-active { color:var(--blue); }
.sort-ico { margin-left:3px;opacity:.4;font-size:10px; }
.sort-active .sort-ico { opacity:1; }
.lb-table td {
    padding:11px 14px;border-bottom:1px solid var(--border);
    color:var(--text1);vertical-align:middle;
}
.lb-table tr:last-child td { border-bottom:none; }
.lb-table tbody tr:hover td { background:#f8fafc; }

.rank-cell { width:44px;text-align:center;font-weight:700;font-size:15px; }
.rank-1 { color:#f59e0b; }
.rank-2 { color:#94a3b8; }
.rank-3 { color:#b45309; }
.rank-n { color:#cbd5e1;font-size:12px; }

.emp-name { font-weight:600;color:var(--text1); }
.emp-pos  { font-size:11px;color:var(--text3);margin-top:1px; }

.num-cell { text-align:right;font-variant-numeric:tabular-nums; }
.num-big  { font-weight:700; }
.num-sub  { font-size:11px;color:var(--text3); }

.bar-wrap { margin-top:4px;height:3px;background:var(--border);border-radius:2px;min-width:60px; }
.bar-fill { height:100%;border-radius:2px;transition:width .5s; }

.badge-visits  { background:#eff6ff;color:#2563eb;border-radius:6px;padding:2px 7px;font-size:11px;font-weight:600; }
.badge-amount  { background:#f0fdf4;color:#16a34a;border-radius:6px;padding:2px 7px;font-size:11px;font-weight:600; }
</style>

<div class="lb-wrap" style="background:var(--bg);min-height:100%;padding-bottom:40px;">

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
                <label class="filter-label">Год (KMP)</label>
                <select name="year" class="filter-sel" style="width:90px;">
                    <option value="">Все</option>
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

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
                <a href="{{ route('leaderboard.index', ['year' => $year, 'sort' => $sort, 'dir' => $dir]) }}"
                   class="btn" style="height:30px;padding:0 12px;font-size:12px;">Сбросить</a>
                @endif
            </div>

        </div>
    </form>
</div>

{{-- Table --}}
@php
    $maxVisits = $rows->max('total_visits') ?: 1;
    $maxAmount = $rows->max('total_amount') ?: 1;
    $thUrl = fn(string $col) => route('leaderboard.index', array_merge(
        request()->except('sort','dir'),
        ['sort' => $col, 'dir' => ($sort === $col && $dir === 'desc') ? 'asc' : 'desc']
    ));
    $sortIco = fn(string $col) => $sort === $col ? ($dir === 'desc' ? '↓' : '↑') : '↕';
@endphp

<div class="lb-card">
    <div class="lb-toolbar">
        <div class="lb-info">
            Показано <strong>{{ $rows->count() }}</strong> сотрудников &nbsp;·&nbsp;
            @if($dateFrom || $dateTo)
                <span>{{ $dateFrom ?? '...' }} — {{ $dateTo ?? '...' }}</span>
            @else
                <span>все даты</span>
            @endif
            @if($year)
                &nbsp;·&nbsp; KMP год: <strong>{{ $year }}</strong>
            @endif
        </div>
        <div style="font-size:12px;color:var(--text3);">Кликните на заголовок для сортировки</div>
    </div>

    @if($rows->isEmpty())
    <div style="padding:48px;text-align:center;color:var(--text3);font-size:14px;">
        Нет данных для выбранного периода
    </div>
    @else
    <div style="overflow-x:auto;">
    <table class="lb-table">
        <thead>
            <tr>
                <th class="rank-cell" style="cursor:default;">#</th>
                <th style="cursor:default;">Сотрудник</th>
                <th class="{{ $sort==='total_visits' ? 'sort-active' : '' }}" style="text-align:right;">
                    <a href="{{ $thUrl('total_visits') }}" style="color:inherit;text-decoration:none;display:flex;align-items:center;justify-content:flex-end;gap:2px;">
                        Визиты<span class="sort-ico">{!! $sortIco('total_visits') !!}</span>
                    </a>
                </th>
                <th class="{{ $sort==='doctor_visits' ? 'sort-active' : '' }}" style="text-align:right;">
                    <a href="{{ $thUrl('doctor_visits') }}" style="color:inherit;text-decoration:none;display:flex;align-items:center;justify-content:flex-end;gap:2px;">
                        Врачи<span class="sort-ico">{!! $sortIco('doctor_visits') !!}</span>
                    </a>
                </th>
                <th class="{{ $sort==='pharmacy_visits' ? 'sort-active' : '' }}" style="text-align:right;">
                    <a href="{{ $thUrl('pharmacy_visits') }}" style="color:inherit;text-decoration:none;display:flex;align-items:center;justify-content:flex-end;gap:2px;">
                        Аптеки<span class="sort-ico">{!! $sortIco('pharmacy_visits') !!}</span>
                    </a>
                </th>
                <th class="{{ $sort==='avg_duration' ? 'sort-active' : '' }}" style="text-align:right;">
                    <a href="{{ $thUrl('avg_duration') }}" style="color:inherit;text-decoration:none;display:flex;align-items:center;justify-content:flex-end;gap:2px;">
                        Ср. длит.<span class="sort-ico">{!! $sortIco('avg_duration') !!}</span>
                    </a>
                </th>
                <th class="{{ $sort==='total_amount' ? 'sort-active' : '' }}" style="text-align:right;">
                    <a href="{{ $thUrl('total_amount') }}" style="color:inherit;text-decoration:none;display:flex;align-items:center;justify-content:flex-end;gap:2px;">
                        Сумма KZT<span class="sort-ico">{!! $sortIco('total_amount') !!}</span>
                    </a>
                </th>
                <th class="{{ $sort==='total_qty' ? 'sort-active' : '' }}" style="text-align:right;">
                    <a href="{{ $thUrl('total_qty') }}" style="color:inherit;text-decoration:none;display:flex;align-items:center;justify-content:flex-end;gap:2px;">
                        Уп.<span class="sort-ico">{!! $sortIco('total_qty') !!}</span>
                    </a>
                </th>
            </tr>
        </thead>
        <tbody>
        @foreach($rows as $i => $row)
        @php
            $rank = $i + 1;
            $rankClass = match($rank) { 1 => 'rank-1', 2 => 'rank-2', 3 => 'rank-3', default => 'rank-n' };
            $rankLabel = match($rank) { 1 => '1', 2 => '2', 3 => '3', default => (string)$rank };
            $visitPct  = $maxVisits > 0 ? round($row['total_visits']  / $maxVisits * 100) : 0;
            $amountPct = $maxAmount > 0 ? round($row['total_amount']  / $maxAmount * 100) : 0;
        @endphp
        <tr>
            <td class="rank-cell"><span class="{{ $rankClass }}">{{ $rankLabel }}</span></td>
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

            {{-- Всего визитов --}}
            <td class="num-cell">
                @if($row['total_visits'] > 0)
                <div class="num-big">{{ number_format($row['total_visits'], 0, '.', ' ') }}</div>
                <div class="bar-wrap">
                    <div class="bar-fill" style="background:#3b82f6;width:{{ $visitPct }}%;"></div>
                </div>
                @else
                <span style="color:var(--text3);">—</span>
                @endif
            </td>

            {{-- К врачам --}}
            <td class="num-cell" style="color:#6366f1;">
                @if($row['doctor_visits'] > 0)
                    {{ number_format($row['doctor_visits'], 0, '.', ' ') }}
                @else
                    <span style="color:var(--text3);">—</span>
                @endif
            </td>

            {{-- В аптеки --}}
            <td class="num-cell" style="color:#0ea5e9;">
                @if($row['pharmacy_visits'] > 0)
                    {{ number_format($row['pharmacy_visits'], 0, '.', ' ') }}
                @else
                    <span style="color:var(--text3);">—</span>
                @endif
            </td>

            {{-- Ср. длительность --}}
            <td class="num-cell">
                @if($row['avg_duration'] > 0)
                <span style="color:var(--text2);">{{ $row['avg_duration'] }}<span style="font-size:11px;"> мин</span></span>
                @else
                <span style="color:var(--text3);">—</span>
                @endif
            </td>

            {{-- Сумма KZT --}}
            <td class="num-cell">
                @if($row['total_amount'] > 0)
                <div class="num-big" style="color:#16a34a;">{{ number_format($row['total_amount'], 0, '.', ' ') }}</div>
                <div class="bar-wrap">
                    <div class="bar-fill" style="background:#16a34a;width:{{ $amountPct }}%;"></div>
                </div>
                @else
                <span style="color:var(--text3);">—</span>
                @endif
            </td>

            {{-- Уп. --}}
            <td class="num-cell">
                @if($row['total_qty'] > 0)
                    <span style="color:var(--text2);">{{ number_format($row['total_qty'], 0, '.', ' ') }}</span>
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
