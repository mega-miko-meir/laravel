@extends('layout')
@section('content')

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
    <h1 style="font-size:20px;font-weight:700;color:#111827;margin:0;">Дашборд</h1>

    @can('admin')
        <form action="{{ route('admin.reports.weekly-dismissed') }}" method="POST"
              onsubmit="return confirm('Отправить еженедельный отчёт об увольнениях и декретах (прошлая пн-вс) прямо сейчас, не дожидаясь понедельника?');">
            @csrf
            <button type="submit"
                   style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;
                          background:#fff;color:#374151;border:1px solid #e2e8f0;border-radius:8px;
                          font-size:13px;font-weight:600;cursor:pointer;"
                   onmouseover="this.style.background='#f9fafb';this.style.borderColor='#c7d2fe';"
                   onmouseout="this.style.background='#fff';this.style.borderColor='#e2e8f0';">
                <svg style="width:14px;height:14px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Отправить отчёт: уволенные и декрет
            </button>
        </form>
    @endcan
</div>

@php
    $cards = [
        ['route' => 'hired_total',        'label' => 'Всего сотрудников',       'value' => $hired_total,        'color' => '#2563eb', 'bg' => '#eff6ff', 'period_type' => null],
        ['route' => 'on_maternity_leave', 'label' => 'В декрете',               'value' => $on_maternity_leave, 'color' => '#9333ea', 'bg' => '#faf5ff', 'period_type' => null],
        ['route' => 'hired_this_month',   'label' => 'Нанятые в этом месяце',   'value' => $hired_this_month,   'color' => '#16a34a', 'bg' => '#f0fdf4', 'period_type' => 'hired'],
        ['route' => 'fired_this_month',   'label' => 'Уволенные в этом месяце', 'value' => $fired_this_month,   'color' => '#dc2626', 'bg' => '#fef2f2', 'period_type' => 'dismissed'],
        ['route' => 'hired_this_year',    'label' => 'Нанятые в этом году',     'value' => $hired_this_year,    'color' => '#16a34a', 'bg' => '#f0fdf4', 'period_type' => 'hired'],
        ['route' => 'fired_this_year',    'label' => 'Уволенные в этом году',   'value' => $fired_this_year,    'color' => '#dc2626', 'bg' => '#fef2f2', 'period_type' => 'dismissed'],
    ];
@endphp

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;max-width:1000px;">
    <div style="background:#fff;border:1px solid #f0f0f0;border-radius:10px;padding:12px 14px;box-shadow:0 1px 3px rgba(0,0,0,.05);">
        <p style="font-size:11px;color:#6b7280;font-weight:500;margin-bottom:6px;">Средний стаж</p>
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <p id="avg-tenure-value" style="font-size:22px;font-weight:700;color:#0891b2;line-height:1;">
                {{ $avgTenureYears }}<span style="font-size:12px;font-weight:500;margin-left:1px;">л</span>
                {{ $avgTenureMonths }}<span style="font-size:12px;font-weight:500;margin-left:1px;">мес</span>
            </p>
            <div style="width:30px;height:30px;border-radius:8px;background:#ecfeff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg style="width:15px;height:15px;color:#0891b2;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <div style="background:#fff;border:1px solid #f0f0f0;border-radius:10px;padding:12px 14px;box-shadow:0 1px 3px rgba(0,0,0,.05);">
        <p style="font-size:11px;color:#6b7280;font-weight:500;margin-bottom:6px;">Текучесть {{ now()->year }}</p>
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <p id="turnover-value" style="font-size:26px;font-weight:700;line-height:1;
                      color:{{ $turnoverPct > 20 ? '#dc2626' : ($turnoverPct > 10 ? '#d97706' : '#16a34a') }};">
                {{ $turnoverPct }}<span style="font-size:14px;font-weight:500;">%</span>
            </p>
            <div id="turnover-icon-wrap" style="width:30px;height:30px;border-radius:8px;flex-shrink:0;
                        background:{{ $turnoverPct > 20 ? '#fef2f2' : ($turnoverPct > 10 ? '#fffbeb' : '#f0fdf4') }};
                        display:flex;align-items:center;justify-content:center;">
                <svg id="turnover-icon" style="width:15px;height:15px;color:{{ $turnoverPct > 20 ? '#dc2626' : ($turnoverPct > 10 ? '#d97706' : '#16a34a') }};"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
        </div>
    </div>
    @foreach($cards as $card)
        <a id="card-{{ $card['route'] }}"
           href="{{ route('employees.filtered', $card['route']) }}"
           data-normal-href="{{ route('employees.filtered', $card['route']) }}"
           @if($card['period_type']) data-period-type="{{ $card['period_type'] }}" @endif
           style="display:block;background:#fff;border:1px solid #f0f0f0;border-radius:10px;
                  padding:12px 14px;text-decoration:none;box-shadow:0 1px 3px rgba(0,0,0,.05);"
           onmouseover="this.style.boxShadow='0 4px 14px rgba(0,0,0,.08)';this.style.transform='translateY(-1px)';"
           onmouseout="this.style.boxShadow='0 1px 3px rgba(0,0,0,.05)';this.style.transform='translateY(0)';">
            <p id="card-{{ $card['route'] }}-label" style="font-size:11px;color:#6b7280;font-weight:500;margin-bottom:6px;line-height:1.3;">{{ $card['label'] }}</p>
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <p id="card-{{ $card['route'] }}-value" style="font-size:26px;font-weight:700;color:{{ $card['color'] }};line-height:1;">{{ $card['value'] }}</p>
                <div style="width:30px;height:30px;border-radius:8px;background:{{ $card['bg'] }};
                            display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg style="width:15px;height:15px;color:{{ $card['color'] }};" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        @if(str_contains($card['route'], 'hired') || $card['route'] === 'hired_total')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        @elseif($card['route'] === 'on_maternity_leave')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                        @endif
                    </svg>
                </div>
            </div>
        </a>
    @endforeach
</div>

{{-- Период: принято / уволено / в декрете за произвольный диапазон дат.
     Пока период выбран, карточки «в этом месяце/году» выше тоже показывают
     этот период (см. refreshDashboardCards ниже) — без перезагрузки страницы. --}}
<div style="background:#fff;border:1px solid #f0f0f0;border-radius:10px;padding:14px;
            margin-top:14px;max-width:1000px;">
    <p style="font-size:13px;font-weight:600;color:#374151;margin-bottom:10px;">За период</p>

    <form id="period-form" method="GET" style="display:flex;align-items:flex-end;gap:10px;flex-wrap:wrap;margin-bottom:14px;">
        <div>
            <label style="display:block;font-size:11px;color:#9ca3af;margin-bottom:4px;">С</label>
            <input type="date" id="period-date-from" name="date_from" value="{{ $periodFrom }}"
                   style="padding:7px 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:13px;outline:none;">
        </div>
        <div>
            <label style="display:block;font-size:11px;color:#9ca3af;margin-bottom:4px;">По</label>
            <input type="date" id="period-date-to" name="date_to" value="{{ $periodTo }}"
                   style="padding:7px 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:13px;outline:none;">
        </div>
        <button type="submit"
                style="padding:7px 18px;background:#2563eb;color:#fff;border:none;
                       border-radius:7px;font-size:13px;font-weight:600;cursor:pointer;"
                onmouseover="this.style.background='#1d4ed8';"
                onmouseout="this.style.background='#2563eb';">
            Показать
        </button>
    </form>

    <div id="period-results" style="display:{{ $hasPeriod ? 'block' : 'none' }};">
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;">
            @foreach([
                ['type' => 'hired',           'label' => 'Принято',   'color' => '#16a34a', 'bg' => '#f0fdf4'],
                ['type' => 'dismissed',       'label' => 'Уволено',   'color' => '#dc2626', 'bg' => '#fef2f2'],
                ['type' => 'maternity_leave', 'label' => 'В декрете', 'color' => '#9333ea', 'bg' => '#faf5ff'],
            ] as $pc)
                <a id="period-link-{{ $pc['type'] }}"
                   href="{{ route('employees.periodList', ['type' => $pc['type'], 'date_from' => $periodFrom, 'date_to' => $periodTo]) }}"
                   style="display:block;background:{{ $pc['bg'] }};border-radius:8px;padding:12px 14px;text-decoration:none;"
                   onmouseover="this.style.opacity='0.85';" onmouseout="this.style.opacity='1';">
                    <p style="font-size:11px;color:#6b7280;font-weight:500;margin-bottom:4px;">{{ $pc['label'] }}</p>
                    <p id="period-{{ $pc['type'] }}-value" style="font-size:24px;font-weight:700;color:{{ $pc['color'] }};line-height:1;">{{ $periodStats[$pc['type']] ?? 0 }}</p>
                </a>
            @endforeach
        </div>

        <a id="period-link-all"
           href="{{ route('employees.periodList', ['type' => 'all', 'date_from' => $periodFrom, 'date_to' => $periodTo]) }}"
           style="display:inline-flex;align-items:center;gap:6px;margin-top:10px;font-size:12px;font-weight:600;
                  color:#2563eb;text-decoration:none;"
           onmouseover="this.style.textDecoration='underline';"
           onmouseout="this.style.textDecoration='none';">
            Показать всех одним списком (<span id="period-all-count">{{ ($periodStats['hired'] ?? 0) + ($periodStats['dismissed'] ?? 0) + ($periodStats['maternity_leave'] ?? 0) }}</span>)
            <svg style="width:12px;height:12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
            </svg>
        </a>
    </div>
    <p id="period-empty" style="font-size:12px;color:#9ca3af;display:{{ $hasPeriod ? 'none' : 'block' }};">Укажите даты «С» и «По», чтобы увидеть принятых/уволенных/ушедших в декрет за этот период.</p>
</div>

<script>
(function () {
    const allRolesForCards = @json($allRoles);
    // Общее состояние выбранных ролей — расшарено с графиками ниже (которые
    // рендерятся только для админов): один Set, обе части страницы читают и
    // мутируют его, чтобы клик по роли обновлял и графики, и карточки разом.
    window.dashboardState = window.dashboardState || { selectedRoles: new Set(allRolesForCards) };

    function periodListUrl(type, from, to) {
        return '{{ route('employees.periodList', ['type' => '__TYPE__']) }}'.replace('__TYPE__', type)
            + '?date_from=' + encodeURIComponent(from) + '&date_to=' + encodeURIComponent(to);
    }

    function applyCardsData(d, from, to) {
        const labels = {
            hired_this_month:  d.has_period ? 'Нанятые за период'   : 'Нанятые в этом месяце',
            fired_this_month:  d.has_period ? 'Уволенные за период' : 'Уволенные в этом месяце',
            hired_this_year:   d.has_period ? 'Нанятые за период'   : 'Нанятые в этом году',
            fired_this_year:   d.has_period ? 'Уволенные за период' : 'Уволенные в этом году',
        };
        ['hired_total', 'on_maternity_leave', 'hired_this_month', 'fired_this_month', 'hired_this_year', 'fired_this_year'].forEach(key => {
            const valueEl = document.getElementById('card-' + key + '-value');
            if (valueEl) valueEl.textContent = d[key];
            if (labels[key]) {
                const labelEl = document.getElementById('card-' + key + '-label');
                if (labelEl) labelEl.textContent = labels[key];
            }
            const linkEl = document.getElementById('card-' + key);
            if (linkEl && linkEl.dataset.periodType) {
                linkEl.href = d.has_period
                    ? periodListUrl(linkEl.dataset.periodType, from, to)
                    : linkEl.dataset.normalHref;
            }
        });

        const avgTenureEl = document.getElementById('avg-tenure-value');
        if (avgTenureEl) {
            avgTenureEl.innerHTML =
                d.avg_tenure_years + '<span style="font-size:12px;font-weight:500;margin-left:1px;">л</span> '
                + d.avg_tenure_months + '<span style="font-size:12px;font-weight:500;margin-left:1px;">мес</span>';
        }

        const turnoverColor = d.turnover_pct > 20 ? '#dc2626' : (d.turnover_pct > 10 ? '#d97706' : '#16a34a');
        const turnoverBg    = d.turnover_pct > 20 ? '#fef2f2' : (d.turnover_pct > 10 ? '#fffbeb' : '#f0fdf4');
        const turnoverValueEl = document.getElementById('turnover-value');
        if (turnoverValueEl) {
            turnoverValueEl.style.color = turnoverColor;
            turnoverValueEl.innerHTML = d.turnover_pct + '<span style="font-size:14px;font-weight:500;">%</span>';
        }
        const turnoverIconWrap = document.getElementById('turnover-icon-wrap');
        if (turnoverIconWrap) turnoverIconWrap.style.background = turnoverBg;
        const turnoverIcon = document.getElementById('turnover-icon');
        if (turnoverIcon) turnoverIcon.style.color = turnoverColor;

        const periodResults = document.getElementById('period-results');
        const periodEmpty   = document.getElementById('period-empty');
        if (d.has_period) {
            if (periodResults) periodResults.style.display = 'block';
            if (periodEmpty) periodEmpty.style.display = 'none';
            ['hired', 'dismissed', 'maternity_leave'].forEach(type => {
                const valEl = document.getElementById('period-' + type + '-value');
                if (valEl) valEl.textContent = d.period_stats[type];
                const linkEl = document.getElementById('period-link-' + type);
                if (linkEl) linkEl.href = periodListUrl(type, from, to);
            });
            const countEl = document.getElementById('period-all-count');
            if (countEl) countEl.textContent = d.period_stats.hired + d.period_stats.dismissed + d.period_stats.maternity_leave;
            const allLinkEl = document.getElementById('period-link-all');
            if (allLinkEl) allLinkEl.href = periodListUrl('all', from, to);
        } else {
            if (periodResults) periodResults.style.display = 'none';
            if (periodEmpty) periodEmpty.style.display = 'block';
        }
    }

    window.refreshDashboardCards = function () {
        const from = document.getElementById('period-date-from').value;
        const to   = document.getElementById('period-date-to').value;
        const roles = Array.from(window.dashboardState.selectedRoles);

        const params = new URLSearchParams();
        if (roles.length === 0) {
            // Все роли сброшены («Сбросить») — это осознанный выбор «ничего»,
            // а не «фильтр не задан». Пустой roles[] сервер интерпретирует
            // как «без фильтра» (см. applyRoles() в EmployeeEventStatsService),
            // поэтому шлём заведомо несуществующую роль — 0 совпадений.
            params.append('roles[]', '__none__');
        } else if (roles.length < allRolesForCards.length) {
            roles.forEach(r => params.append('roles[]', r));
        }
        if (from && to) {
            params.set('date_from', from);
            params.set('date_to', to);
        }

        fetch('{{ route('dashboard.cardsData') }}?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then(r => r.json())
            .then(d => applyCardsData(d, from, to))
            .catch(err => console.error('Не удалось обновить карточки дашборда:', err));

        const url = new URL(window.location.href);
        url.searchParams.delete('date_from');
        url.searchParams.delete('date_to');
        if (from && to) {
            url.searchParams.set('date_from', from);
            url.searchParams.set('date_to', to);
        }
        window.history.replaceState({}, '', url);
    };

    document.getElementById('period-form').addEventListener('submit', function (e) {
        e.preventDefault();
        window.refreshDashboardCards();
    });
})();
</script>

@can('admin')

{{-- Фильтр по должности (глобальный) --}}
@if(count($allRoles) > 0)
<div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;
            background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;
            padding:8px 14px;margin-top:14px;max-width:1000px;">
    <span style="font-size:11px;font-weight:600;color:#6b7280;white-space:nowrap;margin-right:2px;">Должность:</span>
    <button id="btn-select-all" style="padding:3px 10px;font-size:11px;font-weight:600;border-radius:20px;cursor:pointer;border:1.5px solid #2563eb;background:#2563eb;color:#fff;">Все</button>
    <button id="btn-clear-all" style="padding:3px 10px;font-size:11px;font-weight:600;border-radius:20px;cursor:pointer;border:1.5px solid #e5e7eb;background:#fff;color:#6b7280;">Сбросить</button>
    <div style="width:1px;height:16px;background:#e5e7eb;margin:0 2px;"></div>
    @foreach($allRoles as $role)
    <button class="role-btn" data-role="{{ $role }}" style="padding:3px 10px;font-size:11px;font-weight:600;border-radius:20px;cursor:pointer;border:1.5px solid #2563eb;background:#2563eb;color:#fff;">{{ strtoupper($role) }}</button>
    @endforeach
</div>
@endif

{{-- Линейный чарт + Donut --}}
<div style="display:grid;grid-template-columns:1fr 300px;gap:16px;margin-top:24px;max-width:1000px;">
    <div style="background:#fff;border:1px solid #f0f0f0;border-radius:12px;padding:20px 24px;box-shadow:0 1px 3px rgba(0,0,0,.05);">
        <p style="font-size:13px;font-weight:700;color:#111827;margin:0 0 2px;">Динамика найма и увольнений</p>
        <p style="font-size:12px;color:#9ca3af;margin:0 0 20px;">Последние 12 месяцев</p>
        <canvas id="barChart" style="max-height:240px;"></canvas>
    </div>
    <div style="background:#fff;border:1px solid #f0f0f0;border-radius:12px;padding:20px 24px;box-shadow:0 1px 3px rgba(0,0,0,.05);">
        <p style="font-size:13px;font-weight:700;color:#111827;margin:0 0 2px;">Активные по ролям</p>
        <p style="font-size:12px;color:#9ca3af;margin:0 0 16px;">Текущих сотрудников</p>
        <canvas id="donutChart" style="max-height:160px;"></canvas>
        <div id="donut-legend" style="margin-top:14px;display:flex;flex-direction:column;gap:7px;"></div>
    </div>
</div>

{{-- Накопительный + Города --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px;max-width:1000px;">
    <div style="background:#fff;border:1px solid #f0f0f0;border-radius:12px;padding:20px 24px;box-shadow:0 1px 3px rgba(0,0,0,.05);">
        <p style="font-size:13px;font-weight:700;color:#111827;margin:0 0 2px;">Численность персонала</p>
        <p style="font-size:12px;color:#9ca3af;margin:0 0 20px;">Активных на конец месяца</p>
        <canvas id="cumulativeChart" style="max-height:220px;"></canvas>
    </div>
    <div style="background:#fff;border:1px solid #f0f0f0;border-radius:12px;padding:20px 24px;box-shadow:0 1px 3px rgba(0,0,0,.05);">
        <p style="font-size:13px;font-weight:700;color:#111827;margin:0 0 2px;">По городам</p>
        <p style="font-size:12px;color:#9ca3af;margin:0 0 20px;">Активные сотрудники</p>
        @if($hasCityData)
            <div id="cityChartWrap" style="position:relative;">
                <canvas id="cityChart"></canvas>
            </div>
        @else
            <p style="font-size:13px;color:#9ca3af;text-align:center;padding:40px 0;">Нет данных по городам</p>
        @endif
    </div>
</div>

<script>
(function () {
    const chartLabels      = @json($chartLabels);
    const chartKeys        = @json($chartKeys);
    const barRoleData      = @json($barRoleData);
    const donutRoleData    = @json($donutRoleData);
    const cumulativeByRole = @json($cumulativeByRole);
    const cityRoleData     = @json($cityRoleData);
    const allRoles         = @json($allRoles);

    // Общий Set с карточками (см. скрипт в блоке «За период» выше) — клик
    // по роли должен пересчитывать графики и карточки одним и тем же состоянием.
    window.dashboardState = window.dashboardState || { selectedRoles: new Set(allRoles) };
    let selectedRoles = window.dashboardState.selectedRoles;

    const palette = ['#2563eb','#16a34a','#d97706','#9333ea','#0891b2','#ea580c','#65a30d','#e11d48','#0f766e'];
    const roleColor = {};
    allRoles.forEach((r, i) => roleColor[r] = palette[i % palette.length]);

    function getBarSeries() {
        const hired = chartKeys.map(k => { let t = 0; selectedRoles.forEach(r => t += barRoleData[r]?.hired?.[k] ?? 0); return t; });
        const dismissed = chartKeys.map(k => { let t = 0; selectedRoles.forEach(r => t += barRoleData[r]?.dismissed?.[k] ?? 0); return t; });
        return { hired, dismissed };
    }
    function getDonutSeries() {
        const roles = allRoles.filter(r => selectedRoles.has(r));
        const data  = roles.map(r => donutRoleData[r] ?? 0);
        const colors = roles.map(r => roleColor[r]);
        return { roles, data, colors };
    }
    function getCumulativeSeries() {
        return chartLabels.map((_, i) => { let t = 0; selectedRoles.forEach(r => t += cumulativeByRole[r]?.[i] ?? 0); return t; });
    }
    function getCitySorted() {
        const totals = {};
        for (const [city, roleMap] of Object.entries(cityRoleData)) {
            let t = 0; selectedRoles.forEach(r => t += roleMap[r] ?? 0);
            if (t > 0) totals[city] = t;
        }
        // Раньше .slice(0, 12) тихо обрезал список — при 15+ городах несколько
        // самых маленьких вообще не попадали на график. 40 — не реальный лимит
        // отображения, а просто защита от аномально длинного списка (грязные
        // данные и т.п.), которого сейчас в базе нет.
        return Object.entries(totals).sort(([,a],[,b]) => b - a).slice(0, 40);
    }
    function cityColors(n) {
        return Array.from({ length: n }, (_, i) => `rgba(37,99,235,${Math.max(0.18, 1 - i * 0.07).toFixed(2)})`);
    }
    // Фиксированная высота canvas + много городов = Chart.js прячет подписи
    // через одну, чтобы они не накладывались друг на друга. Вместо этого
    // подгоняем высоту под реальное число строк — по ~26px на бар.
    function setCityChartHeight(n) {
        const wrap = document.getElementById('cityChartWrap');
        if (wrap) wrap.style.height = Math.max(180, n * 26 + 24) + 'px';
    }

    const { hired: initHired, dismissed: initDismissed } = getBarSeries();
    const barChartInst = new Chart(document.getElementById('barChart'), {
        type: 'line',
        data: { labels: chartLabels, datasets: [
            { label: 'Нанятые',   data: initHired,     borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,0.08)', borderWidth: 2.5, fill: true, tension: 0.4, pointRadius: 3, pointBackgroundColor: '#2563eb', pointBorderColor: '#fff', pointBorderWidth: 2 },
            { label: 'Уволенные', data: initDismissed, borderColor: '#dc2626', backgroundColor: 'rgba(220,38,38,0.05)', borderWidth: 2.5, fill: true, tension: 0.4, pointRadius: 3, pointBackgroundColor: '#dc2626', pointBorderColor: '#fff', pointBorderWidth: 2 },
        ]},
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'top', labels: { font: { size: 11 }, boxWidth: 12, padding: 14 } }, tooltip: { mode: 'index', intersect: false } }, scales: { x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#9ca3af' } }, y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 }, color: '#9ca3af' }, grid: { color: '#f3f4f6' } } } },
    });

    const { roles: initRoles, data: initData, colors: initColors } = getDonutSeries();
    const donutChartInst = new Chart(document.getElementById('donutChart'), {
        type: 'doughnut',
        data: { labels: initRoles, datasets: [{ data: initData, backgroundColor: initColors, borderWidth: 2, borderColor: '#fff', hoverOffset: 6 }] },
        options: { responsive: true, cutout: '65%', plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ' ' + ctx.label + ': ' + ctx.parsed } } } },
    });
    renderDonutLegend(initRoles, initData, initColors);

    const cumulativeChartInst = new Chart(document.getElementById('cumulativeChart'), {
        type: 'line',
        data: { labels: chartLabels, datasets: [{ label: 'Активных', data: getCumulativeSeries(), borderColor: '#7c3aed', backgroundColor: 'rgba(124,58,237,0.07)', borderWidth: 2.5, fill: true, tension: 0.4, pointRadius: 3, pointBackgroundColor: '#7c3aed', pointBorderColor: '#fff', pointBorderWidth: 2 }] },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { display: false }, tooltip: { mode: 'index', callbacks: { label: ctx => ' ' + ctx.parsed.y + ' чел.' } } }, scales: { x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#9ca3af' } }, y: { ticks: { stepSize: 1, font: { size: 11 }, color: '#9ca3af' }, grid: { color: '#f3f4f6' } } } },
    });

    @if($hasCityData)
    const cityValuePlugin = { id: 'cityValue', afterDatasetsDraw(chart) { const { ctx } = chart; chart.getDatasetMeta(0).data.forEach((bar, i) => { const value = chart.data.datasets[0].data[i]; ctx.save(); ctx.font = '600 11px sans-serif'; ctx.fillStyle = '#374151'; ctx.textBaseline = 'middle'; ctx.fillText(value, bar.x + 6, bar.y); ctx.restore(); }); } };
    const initCity = getCitySorted();
    setCityChartHeight(initCity.length);
    const cityChartInst = new Chart(document.getElementById('cityChart'), {
        plugins: [cityValuePlugin], type: 'bar',
        data: { labels: initCity.map(([c]) => c), datasets: [{ label: 'Сотрудников', data: initCity.map(([,n]) => n), backgroundColor: cityColors(initCity.length), borderRadius: 4 }] },
        options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, layout: { padding: { right: 30 } }, plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ' ' + ctx.parsed.x + ' чел.' } } }, scales: { x: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 }, color: '#9ca3af' }, grid: { color: '#f3f4f6' } }, y: { grid: { display: false }, ticks: { autoSkip: false, font: { size: 11 }, color: '#374151' } } } },
    });
    @endif

    function refreshAll() {
        const { hired, dismissed } = getBarSeries();
        barChartInst.data.datasets[0].data = hired;
        barChartInst.data.datasets[1].data = dismissed;
        barChartInst.update();
        const { roles, data, colors } = getDonutSeries();
        donutChartInst.data.labels = roles; donutChartInst.data.datasets[0].data = data; donutChartInst.data.datasets[0].backgroundColor = colors; donutChartInst.update();
        renderDonutLegend(roles, data, colors);
        cumulativeChartInst.data.datasets[0].data = getCumulativeSeries(); cumulativeChartInst.update();
        @if($hasCityData)
        const sorted = getCitySorted(); setCityChartHeight(sorted.length); cityChartInst.data.labels = sorted.map(([c]) => c); cityChartInst.data.datasets[0].data = sorted.map(([,n]) => n); cityChartInst.data.datasets[0].backgroundColor = cityColors(sorted.length); cityChartInst.update();
        @endif
    }

    function renderDonutLegend(roles, data, colors) {
        document.getElementById('donut-legend').innerHTML = roles.map((r, i) => `<div style="display:flex;align-items:center;justify-content:space-between;"><div style="display:flex;align-items:center;gap:7px;"><span style="width:9px;height:9px;border-radius:50%;background:${colors[i]};flex-shrink:0;"></span><span style="font-size:12px;color:#374151;">${r}</span></div><span style="font-size:12px;font-weight:700;color:#111827;">${data[i]}</span></div>`).join('');
    }

    function syncButtons() {
        const allSel = selectedRoles.size === allRoles.length;
        const btnAll = document.getElementById('btn-select-all');
        btnAll.style.background  = allSel ? '#2563eb' : '#fff'; btnAll.style.color = allSel ? '#fff' : '#6b7280'; btnAll.style.borderColor = allSel ? '#2563eb' : '#e5e7eb';
        document.querySelectorAll('.role-btn[data-role]').forEach(btn => { const on = selectedRoles.has(btn.dataset.role); btn.style.background = on ? '#2563eb' : '#fff'; btn.style.color = on ? '#fff' : '#374151'; btn.style.borderColor = on ? '#2563eb' : '#e5e7eb'; });
    }

    document.getElementById('btn-select-all')?.addEventListener('click', () => { selectedRoles = new Set(allRoles); window.dashboardState.selectedRoles = selectedRoles; syncButtons(); refreshAll(); window.refreshDashboardCards?.(); });
    document.getElementById('btn-clear-all')?.addEventListener('click', () => { selectedRoles = new Set(); window.dashboardState.selectedRoles = selectedRoles; syncButtons(); refreshAll(); window.refreshDashboardCards?.(); });
    document.querySelectorAll('.role-btn[data-role]').forEach(btn => { btn.addEventListener('click', () => { const r = btn.dataset.role; selectedRoles.has(r) ? selectedRoles.delete(r) : selectedRoles.add(r); syncButtons(); refreshAll(); window.refreshDashboardCards?.(); }); });
})();
</script>

<script>
(function () {
    // Фоновая предзагрузка «Аналитики» и «Привязок»: сразу после логина/захода
    // на дашборд тихо прогреваем серверный кэш (Cache::remember по месяцу — для
    // Аналитики, и часовой кэш CRM/KMP-выгрузок — для страницы привязок) для
    // основных страниц, чтобы при первом реальном переходе туда данные уже
    // лежали в кэше и страница открывалась мгновенно, а не ждала живой запрос
    // в Nobel CRM. Каждый эндпоинт сам решает дефолтный период — параметры
    // передавать не нужно.
    const prefetchUrls = [
        '{{ route('calls.data') }}',
        '{{ route('admin.target-clients.data') }}',
        '{{ route('admin.double-visit-plan.data') }}',
        '{{ route('leaderboard.data') }}',
        '{{ route('kmp.data') }}',
        '{{ route('admin.data-integrity') }}',
        '{{ route('clients.data') }}',
    ];

    function prefetchAnalytics() {
        prefetchUrls.forEach(url => {
            fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                .catch(() => {});
        });
    }

    if ('requestIdleCallback' in window) {
        requestIdleCallback(prefetchAnalytics, { timeout: 5000 });
    } else {
        setTimeout(prefetchAnalytics, 1500);
    }
})();
</script>

@endcan

@endsection
