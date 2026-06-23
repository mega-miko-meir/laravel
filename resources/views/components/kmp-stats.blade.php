@props(['stats'])

@php
    $totalAmount  = $stats['totalAmount'];
    $thisMonth    = $stats['thisMonth'];
    $lastMonth    = $stats['lastMonth'];
    $monthly      = $stats['monthly'];
    $topBrands    = $stats['topBrands'];
    $kmpName      = $stats['kmpName'];

    $monthDiff = $lastMonth > 0 ? round(($thisMonth - $lastMonth) / $lastMonth * 100) : null;
    $maxVal    = $monthly->max('amount') ?: 1;

    $monthNames = ['01'=>'Янв','02'=>'Фев','03'=>'Мар','04'=>'Апр','05'=>'Май','06'=>'Июн',
                   '07'=>'Июл','08'=>'Авг','09'=>'Сен','10'=>'Окт','11'=>'Ноя','12'=>'Дек'];
@endphp

<div style="margin-top:16px;background:#fff;border-radius:12px;border:1px solid #f0f0f0;
            box-shadow:0 1px 3px rgba(0,0,0,.06);overflow:hidden;">

    {{-- Заголовок --}}
    <div style="display:flex;align-items:center;justify-content:space-between;
                padding:14px 18px;border-bottom:1px solid #f5f5f5;">
        <div style="display:flex;align-items:center;gap:8px;">
            <svg style="width:15px;height:15px;color:#0ea5e9;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <span style="font-size:14px;font-weight:600;color:#1f2937;">KMP Продажи</span>
        </div>
        <a href="{{ route('kmp.index', ['kmp_employee_name' => $kmpName]) }}"
           style="font-size:11px;color:#0ea5e9;text-decoration:none;"
           onmouseover="this.style.textDecoration='underline';"
           onmouseout="this.style.textDecoration='none';">
            Подробнее →
        </a>
    </div>

    <div style="padding:16px 18px;display:flex;flex-direction:column;gap:14px;">

        {{-- Общая сумма --}}
        <div style="background:#f0f9ff;border-radius:8px;padding:10px 12px;">
            <div style="font-size:22px;font-weight:700;color:#0284c7;line-height:1;">
                {{ number_format($totalAmount, 0, '.', ' ') }}
                <span style="font-size:12px;font-weight:400;margin-left:2px;">KZT</span>
            </div>
            <div style="font-size:10px;color:#9ca3af;margin-top:3px;">Всего продаж (после скидок)</div>
        </div>

        {{-- Этот месяц vs прошлый --}}
        <div style="display:flex;align-items:center;justify-content:space-between;
                    background:#f8fafc;border-radius:8px;padding:10px 14px;">
            <div>
                <div style="font-size:10px;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;">Этот месяц</div>
                <div style="font-size:18px;font-weight:700;color:#1f2937;line-height:1.2;">
                    {{ number_format($thisMonth, 0, '.', ' ') }}
                </div>
            </div>

            @if($monthDiff !== null)
                <div style="padding:3px 9px;border-radius:20px;font-size:12px;font-weight:600;display:flex;align-items:center;gap:3px;
                            {{ $monthDiff >= 0 ? 'background:#dcfce7;color:#15803d;' : 'background:#fee2e2;color:#b91c1c;' }}">
                    @if($monthDiff >= 0)
                        <svg style="width:10px;height:10px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"/>
                        </svg>+{{ $monthDiff }}%
                    @else
                        <svg style="width:10px;height:10px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
                        </svg>{{ $monthDiff }}%
                    @endif
                </div>
            @endif

            <div style="text-align:right;">
                <div style="font-size:10px;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;">Пред. месяц</div>
                <div style="font-size:18px;font-weight:700;color:#94a3b8;line-height:1.2;">
                    {{ number_format($lastMonth, 0, '.', ' ') }}
                </div>
            </div>
        </div>

        {{-- Мини-график --}}
        @if($monthly->count() > 0)
        <div>
            <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#9ca3af;margin-bottom:8px;">
                Последние 6 месяцев (KZT)
            </div>
            <div style="display:flex;align-items:flex-end;gap:5px;height:52px;">
                @foreach($monthly as $m)
                    @php
                        [, $mn] = explode('-', $m->month);
                        $label = $monthNames[$mn] ?? $mn;
                        $heightPct = max(4, round($m->amount / $maxVal * 100));
                    @endphp
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:3px;"
                         title="{{ $label }}: {{ number_format($m->amount) }} KZT">
                        <div style="width:100%;height:40px;display:flex;align-items:flex-end;">
                            <div style="width:100%;height:{{ $heightPct }}%;
                                        background:linear-gradient(180deg,#38bdf8,#0ea5e9);
                                        border-radius:3px 3px 0 0;min-height:3px;"></div>
                        </div>
                        <span style="font-size:9px;color:#9ca3af;">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Топ бренды --}}
        @if($topBrands->count() > 0)
        <div>
            <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#9ca3af;margin-bottom:8px;">
                Топ бренды
            </div>
            @php $brandMax = $topBrands->first()->amount ?: 1; @endphp
            @foreach($topBrands as $b)
            <div style="margin-bottom:7px;">
                <div style="display:flex;justify-content:space-between;margin-bottom:3px;">
                    <span style="font-size:11px;color:#374151;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:72%;">
                        {{ $b->brand }}
                    </span>
                    <span style="font-size:11px;font-weight:600;color:#0ea5e9;flex-shrink:0;margin-left:6px;">
                        {{ number_format($b->amount, 0, '.', ' ') }}
                    </span>
                </div>
                <div style="height:4px;background:#f1f5f9;border-radius:2px;overflow:hidden;">
                    <div style="height:100%;width:{{ round($b->amount / $brandMax * 100) }}%;
                                background:linear-gradient(90deg,#38bdf8,#0ea5e9);border-radius:2px;"></div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

    </div>
</div>
