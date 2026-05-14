@props(['territory'])

@if($territory->children->isEmpty())
    <p style="font-size:12px;color:#9ca3af;font-style:italic;">Нет дочерних территорий</p>
@else
    <div style="margin-top:4px;">
        <p style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;
                  color:#9ca3af;margin-bottom:6px;">
            Дочерние территории ({{ $territory->children->count() }})
        </p>

        @php
            $grouped = $territory->children
                ->sortBy('territory_name')
                ->sortBy('team')
                ->groupBy('team');
        @endphp

        <div style="display:flex;flex-direction:column;gap:10px;">
            @foreach($grouped as $teamName => $children)
                <div>
                    <p style="font-size:11px;font-weight:700;color:#6d28d9;
                               text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">
                        {{ $teamName ?: 'Без группы' }}
                    </p>
                    <div style="display:flex;flex-direction:column;gap:2px;padding-left:8px;
                                border-left:2px solid #e0e7ff;">
                        @foreach($children as $child)
                            @php
                                $activeEmployee = $child->employeeTerritories()
                                    ->whereNull('unassigned_at')
                                    ->latest('assigned_at')
                                    ->first()?->employee;

                                $lastEmployee = $child->employeeTerritories()
                                    ->latest('assigned_at')
                                    ->first()?->employee;
                            @endphp

                            <div style="display:flex;align-items:center;justify-content:space-between;
                                        padding:6px 8px;border-radius:6px;gap:8px;"
                                 onmouseover="this.style.background='#f8fafc';"
                                 onmouseout="this.style.background='none';">
                                <a href="{{ route('territories.show', $child->id) }}"
                                   style="font-size:12px;color:#374151;text-decoration:none;font-weight:500;"
                                   onmouseover="this.style.color='#2563eb';"
                                   onmouseout="this.style.color='#374151';">
                                    {{ $child->territory_name }}
                                </a>

                                @if($activeEmployee)
                                    <a href="{{ route('employees.show', $activeEmployee->id) }}"
                                       style="font-size:11px;color:#2563eb;text-decoration:none;white-space:nowrap;"
                                       onmouseover="this.style.textDecoration='underline';"
                                       onmouseout="this.style.textDecoration='none';">
                                        {{ $activeEmployee->sh_name }}
                                    </a>
                                @elseif($lastEmployee)
                                    <span style="font-size:11px;color:#9ca3af;font-style:italic;white-space:nowrap;">
                                        ({{ $lastEmployee->sh_name }})
                                    </span>
                                @else
                                    <span style="font-size:11px;color:#d1d5db;font-style:italic;">—</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
