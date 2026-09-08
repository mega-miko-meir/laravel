@extends('layout')

@section('content')

<br>

<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
    <div style="display:flex;align-items:center;gap:16px;">
        <a href="javascript:void(0);" onclick="window.history.back();"
           style="display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:500;flex-shrink:0;
                  color:#64748b;background:#fff;padding:5px 12px;border:1px solid #e2e8f0;
                  border-radius:8px;box-shadow:0 1px 2px rgba(0,0,0,.04);text-decoration:none;transition:all .15s;"
           onmouseover="this.style.color='#4f46e5';this.style.borderColor='#c7d2fe';this.style.background='#f5f3ff';"
           onmouseout="this.style.color='#64748b';this.style.borderColor='#e2e8f0';this.style.background='#fff';">
            <svg style="width:13px;height:13px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Назад
        </a>

        <h1 style="font-size:20px;font-weight:700;color:#111827;">
            {{ $title }}
            <span style="font-size:13px;font-weight:500;color:#9ca3af;margin-left:6px;">
                <span id="filtered-count">{{ $employees->count() }}</span>
            </span>
        </h1>
    </div>

    @if(!empty($exportUrl))
        <a href="{{ $exportUrl }}"
           style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;
                  background:#2563eb;color:#fff;border:none;border-radius:8px;
                  font-size:13px;font-weight:600;text-decoration:none;"
           onmouseover="this.style.background='#1d4ed8';"
           onmouseout="this.style.background='#2563eb';">
            <svg style="width:14px;height:14px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Выгрузить в Excel
        </a>
    @endif
</div>

{{-- Поиск --}}
<div style="margin-bottom:16px;">
    <div style="position:relative;max-width:360px;">
        <svg style="position:absolute;left:10px;top:50%;transform:translateY(-50%);
                    width:16px;height:16px;color:#9ca3af;pointer-events:none;"
             fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input id="filtered-search" type="text" placeholder="Поиск по ФИО..." autocomplete="off"
               style="width:100%;padding:8px 12px 8px 34px;border:1px solid #e5e7eb;
                      border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;background:#fff;"
               onfocus="this.style.borderColor='#2563eb';"
               onblur="this.style.borderColor='#e5e7eb';">
    </div>
</div>

<div style="background:#fff;border:1px solid #f0f0f0;border-radius:12px;
            box-shadow:0 1px 3px rgba(0,0,0,.05);overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
        <thead>
            <tr style="background:#f9fafb;border-bottom:1px solid #f0f0f0;">
                <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;
                           text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">ФИО</th>
                <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;
                           text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">Событие</th>
                <th id="filtered-sort-date" style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;
                           text-transform:uppercase;letter-spacing:.05em;color:#6b7280;cursor:pointer;user-select:none;">
                    <span style="display:inline-flex;align-items:center;gap:4px;">
                        Дата
                        <svg id="filtered-sort-icon" style="width:11px;height:11px;transition:transform .15s;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </span>
                </th>
            </tr>
        </thead>
        <tbody id="filtered-tbody">
            @forelse($employees as $employee)
                <tr class="filtered-row" data-name="{{ mb_strtolower($employee->full_name) }}"
                    data-date="{{ \Carbon\Carbon::parse($employee->event_date)->format('Y-m-d') }}"
                    style="border-bottom:1px solid #f9fafb;"
                    onmouseover="this.style.background='#fafafa';"
                    onmouseout="this.style.background='none';">

                    <td style="padding:11px 16px;">
                        <a href="/employee/{{ $employee->id }}"
                           style="color:#2563eb;text-decoration:none;font-weight:500;"
                           onmouseover="this.style.textDecoration='underline';"
                           onmouseout="this.style.textDecoration='none';">
                            {{ $employee->full_name }}
                        </a>
                    </td>

                    <td style="padding:11px 16px;">
                        <x-status-badge :status="$employee->event_type" />
                    </td>

                    <td style="padding:11px 16px;color:#374151;">
                        {{ \Carbon\Carbon::parse($employee->event_date)->format('d.m.Y') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="padding:32px;text-align:center;color:#9ca3af;font-size:13px;">
                        Никого не найдено
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p id="filtered-empty" style="display:none;padding:32px;text-align:center;color:#9ca3af;font-size:13px;">
        По запросу ничего не найдено
    </p>
</div>

<script>
(function () {
    const search  = document.getElementById('filtered-search');
    const tbody   = document.getElementById('filtered-tbody');
    const rows    = Array.from(tbody.querySelectorAll('.filtered-row'));
    const empty   = document.getElementById('filtered-empty');
    const counter = document.getElementById('filtered-count');
    const sortBtn = document.getElementById('filtered-sort-date');
    const sortIcon = document.getElementById('filtered-sort-icon');

    if (!search) return;

    function applyFilter() {
        const q = search.value.trim().toLowerCase();
        let visible = 0;

        rows.forEach(row => {
            const match = !q || row.dataset.name.includes(q);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        counter.textContent = visible;
        const anyRowsAtAll = rows.length > 0;
        empty.style.display = (anyRowsAtAll && visible === 0) ? 'block' : 'none';
        tbody.style.display = (anyRowsAtAll && visible === 0) ? 'none' : '';
    }

    search.addEventListener('input', applyFilter);

    let sortDesc = true;
    sortBtn.addEventListener('click', function () {
        sortDesc = !sortDesc;
        sortIcon.style.transform = sortDesc ? 'rotate(0deg)' : 'rotate(180deg)';

        rows.sort((a, b) => {
            const cmp = a.dataset.date.localeCompare(b.dataset.date);
            return sortDesc ? -cmp : cmp;
        });

        rows.forEach(row => tbody.appendChild(row));
    });
})();
</script>

@endsection
