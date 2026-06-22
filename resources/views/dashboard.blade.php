@extends('layout')
@section('content')

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<h1 style="font-size:20px;font-weight:700;color:#111827;margin-bottom:16px;">Дашборд</h1>

@php
    $cards = [
        ['route' => 'hired_total',        'label' => 'Всего сотрудников',       'value' => $hired_total,        'color' => '#2563eb', 'bg' => '#eff6ff'],
        ['route' => 'on_maternity_leave', 'label' => 'В декрете',               'value' => $on_maternity_leave, 'color' => '#9333ea', 'bg' => '#faf5ff'],
        ['route' => 'hired_this_month',   'label' => 'Нанятые в этом месяце',   'value' => $hired_this_month,   'color' => '#16a34a', 'bg' => '#f0fdf4'],
        ['route' => 'fired_this_month',   'label' => 'Уволенные в этом месяце', 'value' => $fired_this_month,   'color' => '#dc2626', 'bg' => '#fef2f2'],
        ['route' => 'hired_this_year',    'label' => 'Нанятые в этом году',     'value' => $hired_this_year,    'color' => '#16a34a', 'bg' => '#f0fdf4'],
        ['route' => 'fired_this_year',    'label' => 'Уволенные в этом году',   'value' => $fired_this_year,    'color' => '#dc2626', 'bg' => '#fef2f2'],
    ];
@endphp

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;max-width:1000px;">
    <div style="background:#fff;border:1px solid #f0f0f0;border-radius:10px;padding:12px 14px;box-shadow:0 1px 3px rgba(0,0,0,.05);">
        <p style="font-size:11px;color:#6b7280;font-weight:500;margin-bottom:6px;">Средний стаж</p>
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <p style="font-size:22px;font-weight:700;color:#0891b2;line-height:1;">
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
            <p style="font-size:26px;font-weight:700;line-height:1;
                      color:{{ $turnoverPct > 20 ? '#dc2626' : ($turnoverPct > 10 ? '#d97706' : '#16a34a') }};">
                {{ $turnoverPct }}<span style="font-size:14px;font-weight:500;">%</span>
            </p>
            <div style="width:30px;height:30px;border-radius:8px;flex-shrink:0;
                        background:{{ $turnoverPct > 20 ? '#fef2f2' : ($turnoverPct > 10 ? '#fffbeb' : '#f0fdf4') }};
                        display:flex;align-items:center;justify-content:center;">
                <svg style="width:15px;height:15px;color:{{ $turnoverPct > 20 ? '#dc2626' : ($turnoverPct > 10 ? '#d97706' : '#16a34a') }};"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
        </div>
    </div>
    @foreach($cards as $card)
        <a href="{{ route('employees.filtered', $card['route']) }}"
           style="display:block;background:#fff;border:1px solid #f0f0f0;border-radius:10px;
                  padding:12px 14px;text-decoration:none;box-shadow:0 1px 3px rgba(0,0,0,.05);"
           onmouseover="this.style.boxShadow='0 4px 14px rgba(0,0,0,.08)';this.style.transform='translateY(-1px)';"
           onmouseout="this.style.boxShadow='0 1px 3px rgba(0,0,0,.05)';this.style.transform='translateY(0)';">
            <p style="font-size:11px;color:#6b7280;font-weight:500;margin-bottom:6px;line-height:1.3;">{{ $card['label'] }}</p>
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <p style="font-size:26px;font-weight:700;color:{{ $card['color'] }};line-height:1;">{{ $card['value'] }}</p>
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
            <canvas id="cityChart" style="max-height:220px;"></canvas>
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

    let selectedRoles = new Set(allRoles);

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
        return Object.entries(totals).sort(([,a],[,b]) => b - a).slice(0, 12);
    }
    function cityColors(n) {
        return Array.from({ length: n }, (_, i) => `rgba(37,99,235,${Math.max(0.18, 1 - i * 0.07).toFixed(2)})`);
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
    const cityChartInst = new Chart(document.getElementById('cityChart'), {
        plugins: [cityValuePlugin], type: 'bar',
        data: { labels: initCity.map(([c]) => c), datasets: [{ label: 'Сотрудников', data: initCity.map(([,n]) => n), backgroundColor: cityColors(initCity.length), borderRadius: 4 }] },
        options: { indexAxis: 'y', responsive: true, maintainAspectRatio: true, layout: { padding: { right: 30 } }, plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ' ' + ctx.parsed.x + ' чел.' } } }, scales: { x: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 }, color: '#9ca3af' }, grid: { color: '#f3f4f6' } }, y: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#374151' } } } },
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
        const sorted = getCitySorted(); cityChartInst.data.labels = sorted.map(([c]) => c); cityChartInst.data.datasets[0].data = sorted.map(([,n]) => n); cityChartInst.data.datasets[0].backgroundColor = cityColors(sorted.length); cityChartInst.update();
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

    document.getElementById('btn-select-all')?.addEventListener('click', () => { selectedRoles = new Set(allRoles); syncButtons(); refreshAll(); });
    document.getElementById('btn-clear-all')?.addEventListener('click', () => { selectedRoles = new Set(); syncButtons(); refreshAll(); });
    document.querySelectorAll('.role-btn[data-role]').forEach(btn => { btn.addEventListener('click', () => { const r = btn.dataset.role; selectedRoles.has(r) ? selectedRoles.delete(r) : selectedRoles.add(r); syncButtons(); refreshAll(); }); });
})();
</script>

@endcan

@endsection
