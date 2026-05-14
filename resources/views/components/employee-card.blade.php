@props(['employees', 'sort', 'order'])

<div style="background:#fff;border:1px solid #f0f0f0;border-radius:12px;
            box-shadow:0 1px 3px rgba(0,0,0,.05);overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
        <thead>
            <tr style="background:#f9fafb;border-bottom:1px solid #f0f0f0;">
                <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;
                           text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">
                    <a href="{{ route('employees.search', ['search'=>request('search'),'sort'=>'full_name','order'=>request('order')==='asc'?'desc':'asc','active_only'=>request('active_only',1)]) }}"
                       style="color:inherit;text-decoration:none;display:inline-flex;align-items:center;gap:4px;"
                       onmouseover="this.style.color='#374151';" onmouseout="this.style.color='#6b7280';">
                        ФИО @if($sort==='full_name')<span>{{ $order==='asc'?'↑':'↓' }}</span>@endif
                    </a>
                </th>
                @foreach(['Позиция','Статус'] as $col)
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;
                               text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">{{ $col }}</th>
                @endforeach
                <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;
                           text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">
                    <a href="{{ route('employees.search', ['search'=>request('search'),'sort'=>'latest_event_date','order'=>request('order')==='asc'?'desc':'asc','active_only'=>request('active_only',1)]) }}"
                       style="color:inherit;text-decoration:none;display:inline-flex;align-items:center;gap:4px;"
                       onmouseover="this.style.color='#374151';" onmouseout="this.style.color='#6b7280';">
                        Дата события @if($sort==='latest_event_date')<span>{{ $order==='asc'?'↑':'↓' }}</span>@endif
                    </a>
                </th>
                @foreach(['Группа','Город','Действия'] as $col)
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:600;
                               text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">{{ $col }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $employee)
                <tr style="border-bottom:1px solid #f9fafb;"
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

                    <td style="padding:11px 16px;color:#374151;">
                        {{ $employee->employee_territory()->latest('assigned_at')->first()->role ?? '—' }}
                    </td>

                    <td style="padding:11px 16px;">
                        <x-status-badge :status="$employee->latestEvent?->event_type" />
                    </td>

                    <td style="padding:11px 16px;color:#6b7280;font-size:12px;">
                        {{ $employee->latestEvent?->event_date
                            ? \Carbon\Carbon::parse($employee->latestEvent->event_date)->format('d.m.Y')
                            : '—' }}
                    </td>

                    <td style="padding:11px 16px;color:#374151;">
                        {{ $employee->employee_territory()->latest('assigned_at')->first()->team ?? '—' }}
                    </td>

                    <td style="padding:11px 16px;color:#374151;">
                        {{ $employee->employee_territory()->latest('assigned_at')->first()->city ?? '—' }}
                    </td>

                    <td style="padding:11px 16px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <a href="/edit-employee/{{ $employee->id }}"
                               style="font-size:12px;color:#2563eb;text-decoration:none;font-weight:500;"
                               onmouseover="this.style.textDecoration='underline';"
                               onmouseout="this.style.textDecoration='none';">
                                Изменить
                            </a>
                            @can('editor')
                                <form action="/delete-employee/{{ $employee->id }}" method="POST"
                                      x-data x-on:submit.prevent="if(confirm('Удалить сотрудника?')) $el.submit()">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            style="display:inline-flex;align-items:center;justify-content:center;
                                                   width:22px;height:22px;background:none;border:none;cursor:pointer;
                                                   color:#d1d5db;border-radius:5px;"
                                            onmouseover="this.style.color='#ef4444';this.style.background='#fef2f2';"
                                            onmouseout="this.style.color='#d1d5db';this.style.background='none';">
                                        <svg style="width:13px;height:13px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="padding:32px;text-align:center;color:#9ca3af;font-size:13px;">
                        Сотрудники не найдены
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
