{{-- Таблица «Все планшеты» + сортировка + пагинация — отдельный partial,
     чтобы клик по заголовку колонки мог перерисовывать только его через
     AJAX, без полной перезагрузки страницы. --}}
<div style="background:#fff;border:1px solid #f0f0f0;border-radius:12px;overflow:hidden;
            box-shadow:0 1px 3px rgba(0,0,0,.05);">
    @php
        // Первый клик по колонке — по возрастанию, кроме "Дата привязки" (там
        // естественнее сначала новые); повторный клик по той же колонке — тумблер.
        $thUrl = function (string $col, string $firstDir = 'asc') use ($sort, $dir) {
            $newDir = ($sort === $col) ? ($dir === 'asc' ? 'desc' : 'asc') : $firstDir;
            return route('tablets.search', array_merge(
                request()->except('sort', 'dir', 'page'),
                ['sort' => $col, 'dir' => $newDir]
            ));
        };
        $sortIco = fn (string $col) => $sort === $col ? ($dir === 'asc' ? '↑' : '↓') : '↕';
    @endphp
    <table style="width:100%;border-collapse:collapse;font-size:12px;">
        <thead>
            <tr style="background:#f9fafb;border-bottom:1px solid #f0f0f0;">
                @foreach([
                    ['invent_number', 'Номер', 'asc'],
                    ['serial_number', 'Серийный', 'asc'],
                    ['employee', 'Сотрудник', 'asc'],
                    ['assigned_at', 'Дата привязки', 'desc'],
                ] as [$col, $label, $firstDir])
                    <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:600;
                               text-transform:uppercase;letter-spacing:.05em;
                               color:{{ $sort === $col ? '#2563eb' : '#6b7280' }};">
                        <a href="{{ $thUrl($col, $firstDir) }}" onclick="return tabletsAjaxSort(event, this.href)"
                           style="color:inherit;text-decoration:none;display:inline-flex;align-items:center;gap:3px;">
                            {{ $label }}<span style="opacity:{{ $sort === $col ? 1 : .5 }};">{{ $sortIco($col) }}</span>
                        </a>
                    </th>
                @endforeach
                @foreach(['Модель','Статус','Выдача','Возврат'] as $col)
                    <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:600;
                               text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">{{ $col }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($tablets as $tablet)
                @php
                    $sc = match($tablet->status) {
                        'new'     => ['bg'=>'#ede9fe','color'=>'#6d28d9'],
                        'damaged','lost' => ['bg'=>'#fee2e2','color'=>'#dc2626'],
                        'active'  => ['bg'=>'#dbeafe','color'=>'#1d4ed8'],
                        'free'    => ['bg'=>'#dcfce7','color'=>'#15803d'],
                        'admin'   => ['bg'=>'#e0f2fe','color'=>'#0369a1'],
                        default   => ['bg'=>'#f3f4f6','color'=>'#374151'],
                    };
                @endphp
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
                    <td style="padding:9px 14px;">
                        @if($tablet->current_employee)
                            <a href="{{ route('employees.show', $tablet->current_employee->id) }}"
                               style="color:#2563eb;text-decoration:none;"
                               onmouseover="this.style.textDecoration='underline';"
                               onmouseout="this.style.textDecoration='none';">
                                {{ $tablet->current_employee->sh_name }}
                            </a>
                        @else
                            <span style="color:#d1d5db;">—</span>
                        @endif
                    </td>
                    <td style="padding:9px 14px;color:#6b7280;">
                        {{ $tablet->latestAssignment?->assigned_at?->format('d.m.Y') ?? '—' }}
                    </td>
                    <td style="padding:9px 14px;color:#374151;">{{ $tablet->model ?? '—' }}</td>
                    <td style="padding:9px 14px;">
                        <span style="display:inline-block;padding:2px 8px;border-radius:9999px;
                                     font-size:10px;font-weight:600;
                                     background:{{ $sc['bg'] }};color:{{ $sc['color'] }};">
                            {{ $tablet->status }}
                        </span>
                    </td>
                    <td style="padding:9px 14px;">
                        @if($tablet->currentAssignment?->pdf_path)
                            <a href="{{ asset('storage/'.$tablet->currentAssignment->pdf_path) }}" target="_blank"
                               style="color:#2563eb;font-size:11px;text-decoration:none;"
                               onmouseover="this.style.textDecoration='underline';"
                               onmouseout="this.style.textDecoration='none';">PDF</a>
                        @else
                            <span style="color:#d1d5db;">—</span>
                        @endif
                    </td>
                    <td style="padding:9px 14px;">
                        @if($tablet->currentAssignment?->unassign_pdf)
                            <a href="{{ asset('storage/'.$tablet->currentAssignment->unassign_pdf) }}" target="_blank"
                               style="color:#2563eb;font-size:11px;text-decoration:none;"
                               onmouseover="this.style.textDecoration='underline';"
                               onmouseout="this.style.textDecoration='none';">PDF</a>
                        @else
                            <span style="color:#d1d5db;">—</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if($tablets->hasPages())
    <div style="margin-top:12px;">
        {{ $tablets->onEachSide(1)->links() }}
    </div>
@endif
