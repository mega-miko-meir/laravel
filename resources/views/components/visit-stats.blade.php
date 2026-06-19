@props(['stats'])

@php
    $total          = $stats['total'];
    $avgDur         = $stats['avgDur'];
    $lastDate       = $stats['lastDate'] ? \Carbon\Carbon::parse($stats['lastDate'])->format('d.m.Y') : '—';
    $thisMonth      = $stats['thisMonth'];
    $lastMonth      = $stats['lastMonth'];
    $monthly        = $stats['monthly'];
    $topSpec        = $stats['topSpec'];
    $doctorVisits   = $stats['doctorVisits'];
    $pharmacyVisits = $stats['pharmacyVisits'];
    $crmId          = $stats['crmId'];

    $monthDiff = $lastMonth > 0 ? round(($thisMonth - $lastMonth) / $lastMonth * 100) : null;
    $maxVal    = $monthly->max('total') ?: 1;

    $monthNames = ['01'=>'Янв','02'=>'Фев','03'=>'Мар','04'=>'Апр','05'=>'Май','06'=>'Июн',
                   '07'=>'Июл','08'=>'Авг','09'=>'Сен','10'=>'Окт','11'=>'Ноя','12'=>'Дек'];
@endphp

<div style="margin-top:16px;background:#fff;border-radius:12px;border:1px solid #f0f0f0;
            box-shadow:0 1px 3px rgba(0,0,0,.06);overflow:hidden;">

    {{-- Заголовок --}}
    <div style="display:flex;align-items:center;justify-content:space-between;
                padding:14px 18px;border-bottom:1px solid #f5f5f5;">
        <div style="display:flex;align-items:center;gap:8px;">
            <svg style="width:15px;height:15px;color:#6366f1;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            <span style="font-size:14px;font-weight:600;color:#1f2937;">Визиты</span>
        </div>
        <a href="{{ route('calls.index', ['crm_employee_id' => $crmId]) }}"
           style="font-size:11px;color:#6366f1;text-decoration:none;"
           onmouseover="this.style.textDecoration='underline';"
           onmouseout="this.style.textDecoration='none';">
            Подробнее →
        </a>
    </div>

    <div style="padding:16px 18px;display:flex;flex-direction:column;gap:14px;">

        {{-- KPI --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">

            <div style="background:#f8f7ff;border-radius:8px;padding:10px 12px;">
                <div style="font-size:22px;font-weight:700;color:#4f46e5;line-height:1;">{{ number_format($total) }}</div>
                <div style="font-size:10px;color:#9ca3af;margin-top:3px;">Всего визитов</div>
            </div>

            <div style="background:#fffbeb;border-radius:8px;padding:10px 12px;">
                <div style="font-size:22px;font-weight:700;color:#d97706;line-height:1;">
                    {{ $avgDur }}<span style="font-size:12px;font-weight:400;margin-left:2px;">мин</span>
                </div>
                <div style="font-size:10px;color:#9ca3af;margin-top:3px;">Ср. длительность</div>
            </div>

        </div>

        {{-- Тип визитов --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">

            <div style="border:1px solid #e0e7ff;border-radius:8px;padding:10px 12px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:0;left:0;right:0;height:3px;background:#6366f1;border-radius:8px 8px 0 0;"></div>
                <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">
                    <svg style="width:13px;height:13px;color:#6366f1;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span style="font-size:10px;font-weight:600;color:#6366f1;text-transform:uppercase;letter-spacing:.04em;">К врачу</span>
                </div>
                <div style="font-size:20px;font-weight:700;color:#1f2937;line-height:1;">{{ number_format($doctorVisits) }}</div>
                @if($total > 0)
                <div style="margin-top:6px;height:3px;background:#e0e7ff;border-radius:2px;">
                    <div style="height:100%;width:{{ round($doctorVisits / $total * 100) }}%;background:#6366f1;border-radius:2px;"></div>
                </div>
                @endif
            </div>

            <div style="border:1px solid #bae6fd;border-radius:8px;padding:10px 12px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:0;left:0;right:0;height:3px;background:#0ea5e9;border-radius:8px 8px 0 0;"></div>
                <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">
                    <svg style="width:13px;height:13px;color:#0ea5e9;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span style="font-size:10px;font-weight:600;color:#0ea5e9;text-transform:uppercase;letter-spacing:.04em;">В аптеку</span>
                </div>
                <div style="font-size:20px;font-weight:700;color:#1f2937;line-height:1;">{{ number_format($pharmacyVisits) }}</div>
                @if($total > 0)
                <div style="margin-top:6px;height:3px;background:#bae6fd;border-radius:2px;">
                    <div style="height:100%;width:{{ round($pharmacyVisits / $total * 100) }}%;background:#0ea5e9;border-radius:2px;"></div>
                </div>
                @endif
            </div>

        </div>

        {{-- Этот месяц vs прошлый --}}
        <div style="display:flex;align-items:center;justify-content:space-between;
                    background:#f8fafc;border-radius:8px;padding:10px 14px;">
            <div>
                <div style="font-size:10px;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;">Этот месяц</div>
                <div style="font-size:20px;font-weight:700;color:#1f2937;line-height:1.2;">{{ $thisMonth }}</div>
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
                <div style="font-size:20px;font-weight:700;color:#94a3b8;line-height:1.2;">{{ $lastMonth }}</div>
            </div>
        </div>

        {{-- Мини-график --}}
        @if($monthly->count() > 0)
        <div>
            <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#9ca3af;margin-bottom:8px;">
                Последние 6 месяцев
            </div>
            <div style="display:flex;align-items:flex-end;gap:5px;height:52px;">
                @foreach($monthly as $m)
                    @php
                        [, $mn] = explode('-', $m->month);
                        $label = $monthNames[$mn] ?? $mn;
                        $heightPct = max(4, round($m->total / $maxVal * 100));
                    @endphp
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:3px;"
                         title="{{ $label }}: {{ $m->total }} визитов">
                        <div style="width:100%;height:40px;display:flex;align-items:flex-end;">
                            <div style="width:100%;height:{{ $heightPct }}%;
                                        background:linear-gradient(180deg,#818cf8,#6366f1);
                                        border-radius:3px 3px 0 0;min-height:3px;"></div>
                        </div>
                        <span style="font-size:9px;color:#9ca3af;">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Топ специальности --}}
        @if($topSpec->count() > 0)
        <div>
            <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#9ca3af;margin-bottom:8px;">
                Топ специальности
            </div>
            @php $specMax = $topSpec->first()->cnt; @endphp
            @foreach($topSpec as $s)
            <div style="margin-bottom:7px;">
                <div style="display:flex;justify-content:space-between;margin-bottom:3px;">
                    <span style="font-size:11px;color:#374151;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:82%;">
                        {{ $s->customer_spesiality }}
                    </span>
                    <span style="font-size:11px;font-weight:600;color:#6366f1;flex-shrink:0;margin-left:6px;">{{ $s->cnt }}</span>
                </div>
                <div style="height:4px;background:#f1f5f9;border-radius:2px;overflow:hidden;">
                    <div style="height:100%;width:{{ round($s->cnt / $specMax * 100) }}%;
                                background:linear-gradient(90deg,#818cf8,#6366f1);border-radius:2px;"></div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Последний визит --}}
        <div style="display:flex;align-items:center;justify-content:space-between;
                    padding-top:10px;border-top:1px solid #f5f5f5;">
            <span style="font-size:11px;color:#9ca3af;">Последний визит</span>
            <span style="font-size:12px;font-weight:500;color:#374151;">{{ $lastDate }}</span>
        </div>

    </div>
</div>
