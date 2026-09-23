{{-- Таблица «Свободные планшеты» с сортировкой — отдельный partial, чтобы
     клик по заголовку колонки перерисовывал через AJAX только его, не
     перезагружая страницу (и не сворачивая эту секцию обратно). --}}
@php
    $freeThUrl = function (string $col, string $firstDir = 'asc') use ($freeSort, $freeDir) {
        $newDir = ($freeSort === $col) ? ($freeDir === 'asc' ? 'desc' : 'asc') : $firstDir;
        return route('tablets.search', array_merge(
            request()->except('free_sort', 'free_dir', 'partial'),
            ['free_sort' => $col, 'free_dir' => $newDir]
        ));
    };
    $freeSortIco = fn (string $col) => $freeSort === $col ? ($freeDir === 'asc' ? '↑' : '↓') : '↕';
@endphp
<table style="width:100%;border-collapse:collapse;font-size:12px;">
    <thead>
        <tr style="background:#f9fafb;border-bottom:1px solid #f0f0f0;">
            @foreach([
                ['invent_number', 'Номер', 'asc'],
                ['serial_number', 'Серийный', 'asc'],
                ['employee', 'Последний сотрудник', 'asc'],
            ] as [$col, $label, $firstDir])
                <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:600;
                           text-transform:uppercase;letter-spacing:.05em;
                           color:{{ $freeSort === $col ? '#2563eb' : '#6b7280' }};">
                    <a href="{{ $freeThUrl($col, $firstDir) }}" onclick="return tabletsAjaxSort(event, this.href, 'free-tablets-table', 'free')"
                       style="color:inherit;text-decoration:none;display:inline-flex;align-items:center;gap:3px;">
                        {{ $label }}<span style="opacity:{{ $freeSort === $col ? 1 : .5 }};">{{ $freeSortIco($col) }}</span>
                    </a>
                </th>
            @endforeach
            @foreach([
                ['responsible', 'Ответственный', 'asc'],
                ['city', 'Город', 'asc'],
                ['returned_at', 'Дата возврата', 'desc'],
            ] as [$col, $label, $firstDir])
                <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:600;
                           text-transform:uppercase;letter-spacing:.05em;
                           color:{{ $freeSort === $col ? '#2563eb' : '#6b7280' }};">
                    <a href="{{ $freeThUrl($col, $firstDir) }}" onclick="return tabletsAjaxSort(event, this.href, 'free-tablets-table', 'free')"
                       style="color:inherit;text-decoration:none;display:inline-flex;align-items:center;gap:3px;">
                        {{ $label }}<span style="opacity:{{ $freeSort === $col ? 1 : .5 }};">{{ $freeSortIco($col) }}</span>
                    </a>
                </th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($freeTablets as $tablet)
            <tr style="border-top:1px solid #f5f5f5;"
                onmouseover="this.style.background='#fafafa';"
                onmouseout="this.style.background='none';">
                <td style="padding:9px 14px;">
                    <a href="{{ route('tablets.show', $tablet->id) }}"
                       style="color:#2563eb;font-weight:500;text-decoration:none;"
                       onmouseover="this.style.textDecoration='underline';"
                       onmouseout="this.style.textDecoration='none';">
                        {{ $tablet->invent_number }}
                    </a>
                </td>
                <td style="padding:9px 14px;">
                    <a href="{{ route('tablets.show', $tablet->id) }}"
                       style="color:#6b7280;text-decoration:none;"
                       onmouseover="this.style.textDecoration='underline';this.style.color='#2563eb';"
                       onmouseout="this.style.textDecoration='none';this.style.color='#6b7280';">
                        {{ $tablet->serial_number }}
                    </a>
                </td>
                <td style="padding:9px 14px;color:#374151;">
                    {{ $tablet->latestAssignment?->employee?->sh_name ?? '—' }}
                </td>
                <td style="padding:9px 14px;">
                    @if($tablet->responsible)
                        <a href="{{ route('employees.show', $tablet->responsible->id) }}"
                           style="color:#2563eb;font-size:12px;text-decoration:none;"
                           onmouseover="this.style.textDecoration='underline';"
                           onmouseout="this.style.textDecoration='none';">
                            {{ $tablet->responsible->sh_name }}
                        </a>
                    @else
                        <span style="color:#d1d5db;">—</span>
                    @endif
                </td>
                <td style="padding:9px 14px;color:#6b7280;">
                    {{ $tablet->responsible?->employee_territory->first()?->city ?? '—' }}
                </td>
                <td style="padding:9px 14px;color:#6b7280;">
                    {{ $tablet->latestAssignment?->returned_at?->format('d.m.Y') ?? '—' }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
