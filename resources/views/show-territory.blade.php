@extends('layout')

@section('content')

<x-back-button />
<x-flash-message />

@php
    $roleColors = [
        'Rep'      => ['bg'=>'#dcfce7','color'=>'#15803d'],
        'RM'       => ['bg'=>'#dbeafe','color'=>'#1d4ed8'],
        'FFM'      => ['bg'=>'#ede9fe','color'=>'#6d28d9'],
        'Product'  => ['bg'=>'#fce7f3','color'=>'#9d174d'],
        'Marketing'=> ['bg'=>'#fef3c7','color'=>'#b45309'],
    ];
    $rc = $roleColors[$territory->role] ?? ['bg'=>'#f3f4f6','color'=>'#374151'];
@endphp

<div style="display:flex;flex-wrap:wrap;gap:20px;padding:8px 0;">

    {{-- ═══ Левая колонка: инфо ═══ --}}
    <div style="flex:0 0 320px;min-width:260px;display:flex;flex-direction:column;gap:16px;">

        {{-- Карточка основной информации --}}
        <div style="background:#fff;border-radius:12px;border:1px solid #f0f0f0;
                    box-shadow:0 1px 3px rgba(0,0,0,.05);overflow:hidden;">

            {{-- Шапка --}}
            <div style="padding:18px 20px;border-bottom:1px solid #f5f5f5;">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;">
                    <div>
                        <h1 style="font-size:16px;font-weight:700;color:#111827;line-height:1.3;word-break:break-word;">
                            {{ $territory->territory_name }}
                        </h1>
                        @if($territory->role)
                            <span style="display:inline-block;margin-top:6px;padding:2px 10px;border-radius:9999px;
                                         font-size:11px;font-weight:600;
                                         background:{{ $rc['bg'] }};color:{{ $rc['color'] }};">
                                {{ $territory->role }}
                            </span>
                        @endif
                    </div>
                    @can('editor')
                        <a href="/edit-territory/{{ $territory->id }}"
                           style="flex-shrink:0;display:inline-flex;align-items:center;gap:4px;
                                  padding:5px 12px;background:#fff;color:#374151;border:1px solid #e5e7eb;
                                  border-radius:8px;font-size:12px;font-weight:500;text-decoration:none;"
                           onmouseover="this.style.background='#f9fafb';"
                           onmouseout="this.style.background='#fff';">
                            <svg style="width:13px;height:13px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Изменить
                        </a>
                    @endcan
                </div>
            </div>

            {{-- Поля --}}
            <div style="padding:16px 20px;display:flex;flex-direction:column;gap:12px;">
                @php
                    $fields = [
                        ['icon'=>'M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z',
                         'label'=>'Город', 'value'=>$territory->city ?? '—'],
                        ['icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                         'label'=>'Группа', 'value'=>$territory->team ?? '—'],
                        ['icon'=>'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                         'label'=>'Департамент', 'value'=>$territory->department ?? '—'],
                    ];
                @endphp

                @foreach($fields as $f)
                    <div style="display:flex;align-items:flex-start;gap:8px;">
                        <svg style="width:14px;height:14px;flex-shrink:0;margin-top:1px;color:#d1d5db;"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $f['icon'] }}"/>
                        </svg>
                        <div>
                            <p style="font-size:10px;font-weight:600;text-transform:uppercase;
                                      letter-spacing:.05em;color:#9ca3af;">{{ $f['label'] }}</p>
                            <p style="font-size:13px;color:#1f2937;margin-top:1px;">{{ $f['value'] }}</p>
                        </div>
                    </div>
                @endforeach

                {{-- Менеджер --}}
                <div style="display:flex;align-items:flex-start;gap:8px;">
                    <svg style="width:14px;height:14px;flex-shrink:0;margin-top:1px;color:#d1d5db;"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <div>
                        <p style="font-size:10px;font-weight:600;text-transform:uppercase;
                                  letter-spacing:.05em;color:#9ca3af;">Менеджер</p>
                        @if($territory->parent?->employee)
                            <a href="{{ route('employees.show', $territory->parent->employee->id) }}"
                               style="font-size:13px;color:#2563eb;text-decoration:none;margin-top:1px;display:block;"
                               onmouseover="this.style.textDecoration='underline';"
                               onmouseout="this.style.textDecoration='none';">
                                {{ $territory->parent->employee->full_name }}
                            </a>
                        @else
                            <p style="font-size:13px;color:#9ca3af;margin-top:1px;">—</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Брики / дочерние территории --}}
        <div style="background:#fff;border-radius:12px;border:1px solid #f0f0f0;
                    box-shadow:0 1px 3px rgba(0,0,0,.05);padding:16px 20px;">
            @if($territory->role === 'Rep')
                <x-checkbox :bricks="$bricks" :selectedBricks="$selectedBricks" :territory="$territory" />
            @else
                <x-child-territories :territory="$territory" />
            @endif
        </div>

    </div>

    {{-- ═══ Правая колонка: сотрудник + история ═══ --}}
    <div style="flex:1;min-width:300px;display:flex;flex-direction:column;gap:16px;">

        {{-- Текущий сотрудник / форма назначения --}}
        <div style="background:#fff;border-radius:12px;border:1px solid #f0f0f0;
                    box-shadow:0 1px 3px rgba(0,0,0,.05);overflow:hidden;">

            <div style="padding:16px 20px;border-bottom:1px solid #f5f5f5;display:flex;align-items:center;gap:8px;">
                <svg style="width:15px;height:15px;color:#6b7280;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span style="font-size:14px;font-weight:600;color:#1f2937;">Сотрудник</span>
            </div>

            <div style="padding:16px 20px;" x-data="{ showForm: false }">
                @if($employee)
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                        <div>
                            <a href="{{ route('employees.show', $employee->id) }}"
                               style="font-size:15px;font-weight:600;color:#2563eb;text-decoration:none;"
                               onmouseover="this.style.textDecoration='underline';"
                               onmouseout="this.style.textDecoration='none';">
                                {{ $employee->full_name }}
                            </a>
                            <p style="font-size:12px;color:#9ca3af;margin-top:2px;">
                                {{ $employee->position ?? '' }}
                            </p>
                        </div>
                        @can('editor')
                            <button @click="showForm = !showForm"
                                    style="display:inline-flex;align-items:center;gap:5px;padding:6px 12px;
                                           background:#fff;color:#374151;border:1px solid #e5e7eb;border-radius:8px;
                                           font-size:12px;font-weight:500;cursor:pointer;"
                                    onmouseover="this.style.background='#f9fafb';"
                                    onmouseout="this.style.background='#fff';">
                                <svg style="width:13px;height:13px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                                Переназначить
                            </button>
                        @endcan
                    </div>
                @else
                    <p style="font-size:13px;color:#9ca3af;margin-bottom:12px;">Не назначен</p>
                @endunless

                {{-- Форма назначения --}}
                @can('editor')
                    <div x-show="showForm || {{ $employee ? 'false' : 'true' }}" x-cloak
                         style="margin-top:14px;">
                        <form action="{{ route('assign.employee', $territory->id) }}" method="POST"
                              style="display:flex;flex-direction:column;gap:8px;">
                            @csrf
                            <div>
                                <label style="display:block;font-size:11px;font-weight:600;
                                              text-transform:uppercase;letter-spacing:.05em;
                                              color:#9ca3af;margin-bottom:4px;">Сотрудник</label>
                                <select name="employee_id"
                                        style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;
                                               border-radius:8px;font-size:13px;outline:none;
                                               background:#fff;color:#374151;">
                                    <option value="">— выберите сотрудника —</option>
                                    @foreach($availableEmployees as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label style="display:block;font-size:11px;font-weight:600;
                                              text-transform:uppercase;letter-spacing:.05em;
                                              color:#9ca3af;margin-bottom:4px;">Дата назначения</label>
                                <input type="date" name="assigned_at" value="{{ now()->format('Y-m-d') }}"
                                       style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;
                                              border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;">
                            </div>
                            <button type="submit"
                                    style="padding:8px 18px;background:#2563eb;color:#fff;border:none;
                                           border-radius:8px;font-size:13px;font-weight:600;
                                           cursor:pointer;align-self:flex-start;"
                                    onmouseover="this.style.background='#1d4ed8';"
                                    onmouseout="this.style.background='#2563eb';">
                                Назначить
                            </button>
                        </form>
                    </div>
                @endcan
            </div>
        </div>

        {{-- История сотрудников --}}
        <div style="background:#fff;border-radius:12px;border:1px solid #f0f0f0;
                    box-shadow:0 1px 3px rgba(0,0,0,.05);overflow:hidden;">

            <div style="padding:16px 20px;border-bottom:1px solid #f5f5f5;display:flex;align-items:center;gap:8px;">
                <svg style="width:15px;height:15px;color:#6b7280;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span style="font-size:14px;font-weight:600;color:#1f2937;">История назначений</span>
            </div>

            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr style="background:#f9fafb;">
                            <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:600;
                                       text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">ФИО</th>
                            <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:600;
                                       text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">Дата назначения</th>
                            <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:600;
                                       text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">Дата снятия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($previousUsers as $record)
                            <tr style="border-top:1px solid #f5f5f5;"
                                onmouseover="this.style.background='#fafafa';"
                                onmouseout="this.style.background='none';">
                                <td style="padding:10px 16px;">
                                    <a href="{{ route('employees.show', $record->id) }}"
                                       style="color:#2563eb;text-decoration:none;font-weight:500;"
                                       onmouseover="this.style.textDecoration='underline';"
                                       onmouseout="this.style.textDecoration='none';">
                                        {{ $record->full_name }}
                                    </a>
                                </td>
                                <td style="padding:10px 16px;color:#374151;">
                                    <div style="display:flex;align-items:center;gap:6px;">
                                        <span>{{ $record->pivot->assigned_at ? \Carbon\Carbon::parse($record->pivot->assigned_at)->format('d.m.Y') : '—' }}</span>
                                        @can('editor')
                                            <button onclick="openEditModal('{{ $record->pivot->id }}', 'assigned_at', '{{ $record->pivot->assigned_at }}', 'territory')"
                                                    style="display:inline-flex;align-items:center;justify-content:center;
                                                           width:20px;height:20px;background:none;border:none;cursor:pointer;
                                                           color:#9ca3af;border-radius:4px;"
                                                    onmouseover="this.style.color='#2563eb';this.style.background='#eff6ff';"
                                                    onmouseout="this.style.color='#9ca3af';this.style.background='none';">
                                                <svg style="width:12px;height:12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                                <td style="padding:10px 16px;color:#374151;">
                                    <div style="display:flex;align-items:center;gap:6px;">
                                        @if($record->pivot->unassigned_at)
                                            <span>{{ \Carbon\Carbon::parse($record->pivot->unassigned_at)->format('d.m.Y') }}</span>
                                        @else
                                            <span style="color:#16a34a;font-weight:500;font-size:12px;">сейчас</span>
                                        @endif
                                        @can('editor')
                                            <button onclick="openEditModal('{{ $record->pivot->id }}', 'unassigned_at', '{{ $record->pivot->unassigned_at }}', 'territory')"
                                                    style="display:inline-flex;align-items:center;justify-content:center;
                                                           width:20px;height:20px;background:none;border:none;cursor:pointer;
                                                           color:#9ca3af;border-radius:4px;"
                                                    onmouseover="this.style.color='#2563eb';this.style.background='#eff6ff';"
                                                    onmouseout="this.style.color='#9ca3af';this.style.background='none';">
                                                <svg style="width:12px;height:12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="padding:24px;text-align:center;font-size:13px;color:#9ca3af;">
                                    Нет истории назначений
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

{{-- Модальное окно редактирования даты --}}
<x-data-edit-modal />

@endsection
