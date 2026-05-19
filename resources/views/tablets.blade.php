@extends('layout')

@section('content')
@auth

@php
    $totalAllCount = \App\Models\Tablet::where('status', 'active')->count();
    $freeCount     = \App\Models\Tablet::free()->count();
    $newCount      = \App\Models\Tablet::where('status', 'new')->count();
    $damagedCount  = \App\Models\Tablet::whereIn('status', ['damaged', 'lost'])->count();
    $adminCount    = \App\Models\Tablet::whereIn('status', ['admin'])->count();
    $totalCount    = $tablets->count();

    $stats = [
        ['label'=>'Всего исправных', 'value'=>$totalAllCount, 'color'=>'#374151', 'search'=>'active'],
        ['label'=>'Свободных',       'value'=>$freeCount,     'color'=>'#16a34a', 'search'=>'free'],
        // ['label'=>'Без сотрудника',  'value'=>$count,         'color'=>'#b45309', 'search'=>''],
        ['label'=>'Новых',           'value'=>$newCount,      'color'=>'#7c3aed', 'search'=>'new'],
        ['label'=>'Повреждён/Утерян','value'=>$damagedCount,  'color'=>'#dc2626', 'search'=>'damaged'],
        ['label'=>'Админ',           'value'=>$adminCount,    'color'=>'#2563eb', 'search'=>'admin'],
    ];
@endphp


{{-- Тулбар --}}
<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
    <h1 style="font-size:20px;font-weight:700;color:#111827;">
        Планшеты
        <span style="font-size:13px;font-weight:500;color:#9ca3af;margin-left:6px;">{{ $totalCount }}</span>
    </h1>
    @can('editor')
        <a href="/create-tablet"
           style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;
                  background:#2563eb;color:#fff;border:none;border-radius:8px;
                  font-size:13px;font-weight:600;text-decoration:none;"
           onmouseover="this.style.background='#1d4ed8';"
           onmouseout="this.style.background='#2563eb';">
            <svg style="width:15px;height:15px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Добавить
        </a>
    @endcan
</div>

{{-- Статистика --}}
<div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:16px;">
    @foreach($stats as $s)
        <a href="{{ $s['search'] ? route('tablets.search', ['search'=>$s['search']]) : '#' }}"
           style="display:flex;flex-direction:column;flex:1;min-width:100px;
                  background:#fff;border:1px solid #f0f0f0;border-radius:10px;
                  padding:12px 16px;text-decoration:none;
                  box-shadow:0 1px 2px rgba(0,0,0,.04);transition:box-shadow .15s;"
           onmouseover="this.style.boxShadow='0 3px 10px rgba(0,0,0,.08)';"
           onmouseout="this.style.boxShadow='0 1px 2px rgba(0,0,0,.04)';">
            <p style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;
                      color:#9ca3af;margin-bottom:4px;">{{ $s['label'] }}</p>
            <p style="font-size:24px;font-weight:700;color:{{ $s['color'] }};line-height:1;">{{ $s['value'] }}</p>
        </a>
    @endforeach
</div>

{{-- Поиск + сотрудники без планшета --}}
<div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;flex-wrap:wrap;">

    <form action="{{ route('tablets.search') }}" method="GET" style="flex:1;max-width:520px;">
        <div style="display:flex;gap:8px;">
            <div style="position:relative;flex:1;">
                <svg style="position:absolute;left:10px;top:50%;transform:translateY(-50%);
                            width:16px;height:16px;color:#9ca3af;pointer-events:none;"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Серийный номер, сотрудник, статус..."
                       style="width:100%;padding:8px 12px 8px 34px;border:1px solid #e5e7eb;
                              border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;background:#fff;"
                       onfocus="this.style.borderColor='#2563eb';"
                       onblur="this.style.borderColor='#e5e7eb';">
            </div>
            <button type="submit"
                    style="padding:8px 18px;background:#2563eb;color:#fff;border:none;
                           border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap;"
                    onmouseover="this.style.background='#1d4ed8';"
                    onmouseout="this.style.background='#2563eb';">Поиск</button>
        </div>
    </form>

    {{-- Без планшета --}}
    <div x-data="{ open: false }" style="position:relative;">
        <button @click="open = !open"
                style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;
                       background:#fff;color:#374151;border:1px solid #e5e7eb;border-radius:8px;
                       font-size:13px;font-weight:500;cursor:pointer;white-space:nowrap;"
                onmouseover="this.style.background='#f9fafb';"
                onmouseout="this.style.background='#fff';">
            <span style="display:inline-flex;align-items:center;justify-content:center;
                         width:20px;height:20px;background:#fef3c7;color:#b45309;
                         border-radius:50%;font-size:11px;font-weight:700;">{{ $count }}</span>
            Без планшета
        </button>
        <div x-show="open" @click.away="open=false" x-cloak
             style="position:absolute;left:0;top:calc(100% + 6px);width:260px;
                    background:#fff;border:1px solid #e5e7eb;border-radius:10px;
                    box-shadow:0 4px 16px rgba(0,0,0,.08);z-index:50;max-height:260px;overflow-y:auto;">
            <ul style="margin:0;padding:4px 0;list-style:none;">
                @forelse($availableEmployees as $emp)
                    <li>
                        <a href="{{ route('employees.show', $emp->id) }}"
                           style="display:block;padding:9px 14px;font-size:13px;color:#374151;text-decoration:none;"
                           onmouseover="this.style.background='#f9fafb';"
                           onmouseout="this.style.background='none';">
                            {{ $emp->full_name }}
                        </a>
                    </li>
                @empty
                    <li style="padding:12px 14px;font-size:13px;color:#9ca3af;">Все сотрудники с планшетами</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

{{-- Свободные планшеты (сворачиваемые) --}}
<div x-data="{ open: false }" style="margin-bottom:16px;">
    <button @click="open = !open"
            style="display:inline-flex;align-items:center;gap:8px;padding:0;background:none;
                   border:none;cursor:pointer;margin-bottom:10px;">
        <span style="font-size:13px;font-weight:600;color:#374151;">Свободные планшеты</span>
        <span style="display:inline-flex;align-items:center;padding:2px 8px;background:#dcfce7;
                     color:#15803d;border-radius:9999px;font-size:11px;font-weight:600;">
            {{ $freeCount }}
        </span>
        <svg :class="{'rotate-180': open}"
             style="width:14px;height:14px;color:#9ca3af;transition:transform .2s;"
             fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-show="open" x-cloak
         style="background:#fff;border:1px solid #f0f0f0;border-radius:12px;overflow:hidden;
                box-shadow:0 1px 3px rgba(0,0,0,.05);">
        <table style="width:100%;border-collapse:collapse;font-size:12px;">
            <thead>
                <tr style="background:#f9fafb;border-bottom:1px solid #f0f0f0;">
                    @foreach(['Номер','Серийный','Последний сотрудник','Выдача','Возврат','Ответственный','Город'] as $col)
                        <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:600;
                                   text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">{{ $col }}</th>
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
                        <td style="padding:9px 14px;color:#6b7280;">{{ $tablet->serial_number }}</td>
                        <td style="padding:9px 14px;color:#374151;">
                            {{ $tablet->latestAssignment?->employee?->sh_name ?? '—' }}
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
                            {{ $tablet->responsible?->employee_territory()->latest('assigned_at')->first()?->city ?? '—' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Все планшеты --}}
<div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
    <span style="font-size:13px;font-weight:600;color:#374151;">Все планшеты</span>
    <span style="display:inline-flex;align-items:center;padding:2px 8px;background:#f3f4f6;
                 color:#374151;border-radius:9999px;font-size:11px;font-weight:600;">
        {{ $totalCount }}
    </span>
</div>

<div style="background:#fff;border:1px solid #f0f0f0;border-radius:12px;overflow:hidden;
            box-shadow:0 1px 3px rgba(0,0,0,.05);">
    <table style="width:100%;border-collapse:collapse;font-size:12px;">
        <thead>
            <tr style="background:#f9fafb;border-bottom:1px solid #f0f0f0;">
                @foreach(['Номер','Серийный','Сотрудник','Дата привязки','Модель','Статус','Выдача','Возврат'] as $col)
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
                    <td style="padding:9px 14px;color:#6b7280;">{{ $tablet->serial_number }}</td>
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

@else
    <x-auth-container />
@endauth

<script src="{{ asset('js/search.js') }}"></script>
@endsection
