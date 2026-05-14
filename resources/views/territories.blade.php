@extends('layout')

@section('content')
@auth

<br>
{{-- Тулбар --}}
<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px;">

    <h1 style="font-size:20px;font-weight:700;color:#111827;">
        Территории
        <span style="font-size:13px;font-weight:500;color:#9ca3af;margin-left:6px;">{{ $territories->count() }}</span>
    </h1>

    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">

        {{-- Сотрудники без территории --}}
        <div x-data="{ open: false }" style="position:relative;">
            <button @click="open = !open"
                    style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;
                           background:#fff;color:#374151;border:1px solid #e5e7eb;border-radius:8px;
                           font-size:13px;font-weight:500;cursor:pointer;"
                    onmouseover="this.style.background='#f9fafb';"
                    onmouseout="this.style.background='#fff';">
                <span style="display:inline-flex;align-items:center;justify-content:center;
                             width:20px;height:20px;background:#fef3c7;color:#b45309;
                             border-radius:50%;font-size:11px;font-weight:700;">
                    {{ $availableEmployees->count() }}
                </span>
                Без территории
                <svg style="width:14px;height:14px;color:#9ca3af;" :class="{ 'rotate-180': open }"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open" @click.away="open = false" x-cloak
                 style="position:absolute;right:0;top:calc(100% + 6px);width:280px;
                        background:#fff;border:1px solid #e5e7eb;border-radius:10px;
                        box-shadow:0 4px 16px rgba(0,0,0,.08);z-index:50;
                        max-height:260px;overflow-y:auto;">
                <ul style="margin:0;padding:4px 0;list-style:none;">
                    @forelse($availableEmployees as $employee)
                        <li>
                            <a href="{{ route('employees.show', $employee->id) }}"
                               style="display:block;padding:9px 14px;font-size:13px;color:#374151;text-decoration:none;"
                               onmouseover="this.style.background='#f9fafb';"
                               onmouseout="this.style.background='none';">
                                {{ $employee->full_name }}
                            </a>
                        </li>
                    @empty
                        <li style="padding:12px 14px;font-size:13px;color:#9ca3af;">Все сотрудники с территориями</li>
                    @endforelse
                </ul>
            </div>
        </div>

        @can('editor')
            <a href="/create-territory"
               style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;
                      background:#2563eb;color:#fff;border:none;border-radius:8px;
                      font-size:13px;font-weight:600;text-decoration:none;cursor:pointer;"
               onmouseover="this.style.background='#1d4ed8';"
               onmouseout="this.style.background='#2563eb';">
                <svg style="width:15px;height:15px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Добавить
            </a>
        @endcan
    </div>
</div>

{{-- Поиск --}}
<form action="{{ route('territories.search') }}" method="GET"
      style="margin-bottom:16px;">
    <div style="display:flex;gap:8px;max-width:520px;">
        <div style="position:relative;flex:1;">
            <svg style="position:absolute;left:10px;top:50%;transform:translateY(-50%);
                        width:16px;height:16px;color:#9ca3af;pointer-events:none;"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Поиск по территории, городу, группе..."
                   style="width:100%;padding:8px 12px 8px 34px;border:1px solid #e5e7eb;
                          border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;background:#fff;"
                   onfocus="this.style.borderColor='#2563eb';"
                   onblur="this.style.borderColor='#e5e7eb';">
        </div>
        <button type="submit"
                style="padding:8px 18px;background:#2563eb;color:#fff;border:none;
                       border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap;"
                onmouseover="this.style.background='#1d4ed8';"
                onmouseout="this.style.background='#2563eb';">
            Поиск
        </button>
    </div>
</form>

{{-- Таблица --}}
<div style="background:#fff;border:1px solid #f0f0f0;border-radius:12px;
            box-shadow:0 1px 3px rgba(0,0,0,.05);overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
        <thead>
            <tr style="background:#f9fafb;border-bottom:1px solid #f0f0f0;">
                <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;
                           text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">
                    <a href="{{ route('territories.search', ['sort' => 'territory_name', 'order' => request('order') === 'asc' ? 'desc' : 'asc', 'search' => request('search')]) }}"
                       style="color:inherit;text-decoration:none;display:inline-flex;align-items:center;gap:4px;"
                       onmouseover="this.style.color='#374151';" onmouseout="this.style.color='#6b7280';">
                        Территория
                        @if($sort === 'territory_name')
                            <span>{{ $order === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </a>
                </th>
                @foreach(['Роль','Группа','Департамент','Сотрудник','Город','Менеджер'] as $col)
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;
                               text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">
                        {{ $col }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($territories as $territory)
                @php
                    $emp = \App\Models\EmployeeTerritory::where('territory_id', $territory->id)
                        ->whereNull('unassigned_at')->latest('assigned_at')->first()?->employee;

                    $roleColors = [
                        'Rep'     => ['bg'=>'#dcfce7','color'=>'#15803d'],
                        'RM'      => ['bg'=>'#dbeafe','color'=>'#1d4ed8'],
                        'FFM'     => ['bg'=>'#ede9fe','color'=>'#6d28d9'],
                        'Product' => ['bg'=>'#fce7f3','color'=>'#9d174d'],
                        'Marketing'=> ['bg'=>'#fef3c7','color'=>'#b45309'],
                    ];
                    $rc = $roleColors[$territory->role] ?? ['bg'=>'#f3f4f6','color'=>'#374151'];
                @endphp
                <tr style="border-bottom:1px solid #f9fafb;"
                    onmouseover="this.style.background='#fafafa';"
                    onmouseout="this.style.background='none';">

                    <td style="padding:11px 16px;">
                        <a href="{{ route('territories.show', $territory->id) }}"
                           style="color:#2563eb;text-decoration:none;font-weight:500;"
                           onmouseover="this.style.textDecoration='underline';"
                           onmouseout="this.style.textDecoration='none';">
                            {{ $territory->territory_name }}
                        </a>
                    </td>

                    <td style="padding:11px 16px;">
                        @if($territory->role)
                            <span style="display:inline-block;padding:2px 8px;border-radius:9999px;
                                         font-size:11px;font-weight:600;
                                         background:{{ $rc['bg'] }};color:{{ $rc['color'] }};">
                                {{ $territory->role }}
                            </span>
                        @else
                            <span style="color:#d1d5db;">—</span>
                        @endif
                    </td>

                    <td style="padding:11px 16px;color:#374151;">{{ $territory->team ?? '—' }}</td>
                    <td style="padding:11px 16px;color:#374151;">{{ $territory->department ?? '—' }}</td>

                    <td style="padding:11px 16px;">
                        @if($emp)
                            <a href="{{ route('employees.show', $emp->id) }}"
                               style="color:#2563eb;text-decoration:none;"
                               onmouseover="this.style.textDecoration='underline';"
                               onmouseout="this.style.textDecoration='none';">
                                {{ $emp->sh_name }}
                            </a>
                        @else
                            <span style="color:#d1d5db;font-style:italic;">Не назначен</span>
                        @endif
                    </td>

                    <td style="padding:11px 16px;color:#374151;">{{ $territory->city ?? '—' }}</td>

                    <td style="padding:11px 16px;color:#374151;">
                        {{ $territory->parent->employee->full_name ?? '—' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="padding:32px;text-align:center;color:#9ca3af;font-size:13px;">
                        Территории не найдены
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endauth
@endsection
