@extends('layout')

@section('content')

<x-back-button />
<x-flash-message />

@php
    $currentUser = $previousUsers->first();
    $isAssigned  = $currentUser && is_null($currentUser->pivot->returned_at);

    $statusColors = [
        'active'  => ['bg' => '#dbeafe', 'color' => '#1d4ed8', 'label' => 'Активен'],
        'free'    => ['bg' => '#dcfce7', 'color' => '#15803d', 'label' => 'Свободен'],
        'new'     => ['bg' => '#ede9fe', 'color' => '#6d28d9', 'label' => 'Новый'],
        'damaged' => ['bg' => '#fee2e2', 'color' => '#dc2626', 'label' => 'Повреждён'],
        'lost'    => ['bg' => '#fee2e2', 'color' => '#dc2626', 'label' => 'Утерян'],
        'admin'   => ['bg' => '#e0f2fe', 'color' => '#0369a1', 'label' => 'Админ'],
    ];
    $sc = $statusColors[$tablet->status] ?? ['bg' => '#f3f4f6', 'color' => '#374151', 'label' => $tablet->status];
@endphp
<br>
<div style="display:flex;flex-wrap:wrap;gap:20px;padding:8px 0;">

    {{-- ═══ Левая колонка: информация о планшете ═══ --}}
    <div style="flex:0 0 300px;min-width:260px;display:flex;flex-direction:column;gap:16px;">

        {{-- Карточка планшета --}}
        <div style="background:#fff;border-radius:12px;border:1px solid #f0f0f0;
                    box-shadow:0 1px 3px rgba(0,0,0,.05);overflow:hidden;">

            {{-- Шапка --}}
            <div style="padding:18px 20px;border-bottom:1px solid #f5f5f5;">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;">
                    <div>
                        <h1 style="font-size:16px;font-weight:700;color:#111827;line-height:1.3;">
                            {{ $tablet->invent_number }}
                        </h1>
                        <p style="font-size:12px;color:#9ca3af;margin-top:2px;">{{ $tablet->model ?? '—' }}</p>
                        <span style="display:inline-block;margin-top:8px;padding:2px 10px;border-radius:9999px;
                                     font-size:11px;font-weight:600;
                                     background:{{ $sc['bg'] }};color:{{ $sc['color'] }};">
                            {{ $sc['label'] }}
                        </span>
                    </div>
                    @can('editor')
                        <a href="/edit-tablet/{{ $tablet->id }}"
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
                    $infoFields = [
                        ['label' => 'Серийный номер',   'value' => $tablet->serial_number  ?? '—'],
                        ['label' => 'Инвент. номер',    'value' => $tablet->invent_number  ?? '—'],
                        ['label' => 'IMEI',             'value' => $tablet->imei           ?? '—'],
                        ['label' => 'Билайн номер',     'value' => $tablet->beeline_number ?? '—'],
                    ];
                @endphp

                @foreach($infoFields as $f)
                    <div>
                        <p style="font-size:10px;font-weight:600;text-transform:uppercase;
                                  letter-spacing:.05em;color:#9ca3af;margin-bottom:2px;">{{ $f['label'] }}</p>
                        <p style="font-size:13px;color:#1f2937;font-family:monospace;">{{ $f['value'] }}</p>
                    </div>
                @endforeach

                {{-- Ответственный --}}
                <div>
                    <p style="font-size:10px;font-weight:600;text-transform:uppercase;
                              letter-spacing:.05em;color:#9ca3af;margin-bottom:2px;">Ответственный</p>
                    @if($tablet->responsible)
                        <a href="{{ route('employees.show', $tablet->responsible->id) }}"
                           style="font-size:13px;color:#2563eb;text-decoration:none;"
                           onmouseover="this.style.textDecoration='underline';"
                           onmouseout="this.style.textDecoration='none';">
                            {{ $tablet->responsible->full_name }}
                        </a>
                    @else
                        <p style="font-size:13px;color:#9ca3af;">Не указан</p>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- ═══ Правая колонка: сотрудник + история ═══ --}}
    <div style="flex:1;min-width:320px;display:flex;flex-direction:column;gap:16px;">

        {{-- Текущий сотрудник --}}
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
                @if($isAssigned)
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                        <div>
                            <a href="{{ route('employees.show', $currentUser->id) }}"
                               style="font-size:15px;font-weight:600;color:#2563eb;text-decoration:none;"
                               onmouseover="this.style.textDecoration='underline';"
                               onmouseout="this.style.textDecoration='none';">
                                {{ $currentUser->full_name }}
                            </a>
                            @if($currentUser->pivot->assigned_at)
                                <p style="font-size:11px;color:#9ca3af;margin-top:2px;">
                                    с {{ \Carbon\Carbon::parse($currentUser->pivot->assigned_at)->format('d.m.Y') }}
                                </p>
                            @endif
                        </div>
                        @can('editor')
                            <button @click="showForm = !showForm"
                                    style="display:inline-flex;align-items:center;gap:5px;padding:6px 12px;
                                           background:#fff;color:#374151;border:1px solid #e5e7eb;border-radius:8px;
                                           font-size:12px;font-weight:500;cursor:pointer;"
                                    onmouseover="this.style.background='#f9fafb';"
                                    onmouseout="this.style.background='#fff';">
                                <svg style="width:13px;height:13px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                                Переназначить
                            </button>
                        @endcan
                    </div>
                @else
                    <p style="font-size:13px;color:#9ca3af;margin-bottom:12px;">Не назначен</p>
                @endif

                {{-- Форма назначения --}}
                @can('editor')
                <div x-show="showForm || {{ $isAssigned ? 'false' : 'true' }}" x-cloak style="margin-top:14px;">
                    <div x-data="{
                        showModal: false,
                        employeeCity: '',
                        responsibleCity: '',
                        async checkAndSubmit(e) {
                            e.preventDefault();
                            const employeeId = document.getElementById('employee_id_tablet').value;
                            const tabletId   = {{ $tablet->id }};
                            if (!employeeId) { e.target.submit(); return; }
                            const res  = await fetch(`/api/city-check?employee_id=${employeeId}&tablet_id=${tabletId}`);
                            const data = await res.json();
                            if (!data.match && data.responsible_city) {
                                this.employeeCity    = data.employee_city ?? '—';
                                this.responsibleCity = data.responsible_city ?? '—';
                                this.showModal = true;
                            } else { e.target.submit(); }
                        }
                    }">
                        <form id="assign-form-tablet"
                              action="{{ route('assign.employee2', $tablet->id) }}" method="POST"
                              @submit="checkAndSubmit($event)"
                              style="display:flex;flex-direction:column;gap:8px;">
                            @csrf
                            <div>
                                <label style="display:block;font-size:11px;font-weight:600;text-transform:uppercase;
                                              letter-spacing:.05em;color:#9ca3af;margin-bottom:4px;">Сотрудник</label>
                                <select name="employee_id" id="employee_id_tablet"
                                        style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:8px;
                                               font-size:13px;outline:none;background:#fff;color:#374151;">
                                    <option value="">— выберите сотрудника —</option>
                                    @foreach($availableEmployees as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label style="display:block;font-size:11px;font-weight:600;text-transform:uppercase;
                                              letter-spacing:.05em;color:#9ca3af;margin-bottom:4px;">Дата назначения</label>
                                <input type="date" name="assigned_at"
                                       value="{{ now()->format('Y-m-d') }}"
                                       style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:8px;
                                              font-size:13px;outline:none;box-sizing:border-box;">
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

                        {{-- Модал города --}}
                        <div x-show="showModal" x-cloak
                             style="position:fixed;inset:0;z-index:60;display:flex;align-items:center;
                                    justify-content:center;background:rgba(0,0,0,.45);">
                            <div @click.outside="showModal=false"
                                 style="background:#fff;border-radius:14px;padding:24px;max-width:340px;
                                        width:100%;margin:0 16px;box-shadow:0 20px 60px rgba(0,0,0,.2);">
                                <p style="font-size:14px;font-weight:700;color:#111827;margin-bottom:8px;">
                                    Города не совпадают
                                </p>
                                <p style="font-size:13px;color:#6b7280;line-height:1.6;margin-bottom:20px;">
                                    Сотрудник: <strong x-text="employeeCity" style="color:#374151;"></strong><br>
                                    Ответственный: <strong x-text="responsibleCity" style="color:#374151;"></strong><br>
                                    Привязать всё равно?
                                </p>
                                <div style="display:flex;gap:10px;justify-content:flex-end;">
                                    <button @click="showModal=false"
                                            style="padding:8px 16px;font-size:13px;color:#374151;background:#fff;
                                                   border:1px solid #e5e7eb;border-radius:8px;cursor:pointer;">
                                        Отмена
                                    </button>
                                    <button @click="showModal=false;$nextTick(()=>document.getElementById('assign-form-tablet').submit())"
                                            style="padding:8px 16px;font-size:13px;font-weight:600;color:#fff;
                                                   background:#2563eb;border:none;border-radius:8px;cursor:pointer;">
                                        Привязать
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endcan
            </div>
        </div>

        {{-- История --}}
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
                            @foreach(['ФИО','Дата выдачи','Дата возврата','Акт выдачи','Акт возврата'] as $col)
                                <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;
                                           text-transform:uppercase;letter-spacing:.05em;color:#6b7280;
                                           white-space:nowrap;">{{ $col }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($previousUsers as $record)
                            <tr style="border-top:1px solid #f5f5f5;"
                                onmouseover="this.style.background='#fafafa';"
                                onmouseout="this.style.background='none';">

                                {{-- ФИО --}}
                                <td style="padding:10px 16px;">
                                    <a href="{{ route('employees.show', $record->id) }}"
                                       style="color:#2563eb;text-decoration:none;font-weight:500;"
                                       onmouseover="this.style.textDecoration='underline';"
                                       onmouseout="this.style.textDecoration='none';">
                                        {{ $record->full_name }}
                                    </a>
                                </td>

                                {{-- Дата выдачи --}}
                                <td style="padding:10px 16px;">
                                    <div style="display:flex;align-items:center;gap:6px;">
                                        <span style="font-size:12px;color:#374151;">
                                            {{ $record->pivot->assigned_at
                                                ? \Carbon\Carbon::parse($record->pivot->assigned_at)->format('d.m.Y')
                                                : '—' }}
                                        </span>
                                        @can('editor')
                                            <button onclick="openEditModal('{{ $record->pivot->id }}', 'assigned_at', '{{ $record->pivot->assigned_at }}', 'tablet')"
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

                                {{-- Дата возврата --}}
                                <td style="padding:10px 16px;">
                                    <div style="display:flex;align-items:center;gap:6px;">
                                        @if($record->pivot->returned_at)
                                            <span style="font-size:12px;color:#374151;">
                                                {{ \Carbon\Carbon::parse($record->pivot->returned_at)->format('d.m.Y') }}
                                            </span>
                                        @else
                                            <span style="font-size:11px;font-weight:500;color:#16a34a;">сейчас</span>
                                        @endif
                                        @can('editor')
                                            <button onclick="openEditModal('{{ $record->pivot->id }}', 'returned_at', '{{ $record->pivot->returned_at }}', 'tablet')"
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

                                {{-- Акт выдачи --}}
                                <td style="padding:10px 16px;">
                                    <div style="display:flex;align-items:center;gap:6px;">
                                        @if($record->pivot->pdf_path)
                                            <a href="{{ asset('storage/'.$record->pivot->pdf_path) }}" target="_blank"
                                               style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;
                                                      background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;
                                                      border-radius:6px;font-size:11px;font-weight:600;text-decoration:none;"
                                               onmouseover="this.style.background='#dbeafe';"
                                               onmouseout="this.style.background='#eff6ff';">
                                                <svg style="width:11px;height:11px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                PDF
                                            </a>
                                        @else
                                            <span style="color:#d1d5db;font-size:12px;">—</span>
                                        @endif
                                        @can('editor')
                                            <button onclick="openPdfModal('{{ $record->pivot->id }}', 'pdf_path')"
                                                    style="display:inline-flex;align-items:center;justify-content:center;
                                                           width:22px;height:22px;background:none;border:none;cursor:pointer;
                                                           color:#9ca3af;border-radius:5px;"
                                                    title="{{ $record->pivot->pdf_path ? 'Заменить PDF' : 'Добавить PDF' }}"
                                                    onmouseover="this.style.color='#2563eb';this.style.background='#eff6ff';"
                                                    onmouseout="this.style.color='#9ca3af';this.style.background='none';">
                                                <svg style="width:13px;height:13px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                                </svg>
                                            </button>
                                        @endcan
                                    </div>
                                </td>

                                {{-- Акт возврата --}}
                                <td style="padding:10px 16px;">
                                    <div style="display:flex;align-items:center;gap:6px;">
                                        @if($record->pivot->unassign_pdf)
                                            <a href="{{ asset('storage/'.$record->pivot->unassign_pdf) }}" target="_blank"
                                               style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;
                                                      background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;
                                                      border-radius:6px;font-size:11px;font-weight:600;text-decoration:none;"
                                               onmouseover="this.style.background='#dbeafe';"
                                               onmouseout="this.style.background='#eff6ff';">
                                                <svg style="width:11px;height:11px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                PDF
                                            </a>
                                        @else
                                            <span style="color:#d1d5db;font-size:12px;">—</span>
                                        @endif
                                        @can('editor')
                                            <button onclick="openPdfModal('{{ $record->pivot->id }}', 'unassign_pdf')"
                                                    style="display:inline-flex;align-items:center;justify-content:center;
                                                           width:22px;height:22px;background:none;border:none;cursor:pointer;
                                                           color:#9ca3af;border-radius:5px;"
                                                    title="{{ $record->pivot->unassign_pdf ? 'Заменить PDF' : 'Добавить PDF' }}"
                                                    onmouseover="this.style.color='#2563eb';this.style.background='#eff6ff';"
                                                    onmouseout="this.style.color='#9ca3af';this.style.background='none';">
                                                <svg style="width:13px;height:13px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                                </svg>
                                            </button>
                                        @endcan
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="padding:24px;text-align:center;font-size:13px;color:#9ca3af;">
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

<x-data-edit-modal />
<x-pdf-edit-modal />

@endsection
