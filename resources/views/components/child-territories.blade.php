@props(['territory'])

@if ($territory->children->isEmpty())
    <p>Нет дочерних территорий</p>
@else
    <p>Дочерние территории</p>
    <ul class="text-sm">
        @foreach ($territory->children->sortBy(['territory_name', 'asc'])->sortBy(['team', 'asc']) as $child)
            <li>
                <span class="font-semibold text-gray-700">{{ $child->team }}</span> -
                <a href="{{route('territories.show', $child->id)}}" class="text-blue-600 hover:underline">
                    {{ $child->territory_name }}
                </a> -
                @php
                    $lastEmployee = $child->employeeTerritories()
                        ->whereNull('unassigned_at')
                        ->latest('assigned_at')
                        ->first()
                        ?->employee;

                    $lastDismissedEmployee = $child->employeeTerritories()
                        ->latest('assigned_at')
                        ->first()
                        ?->employee;
                @endphp
                @if ($lastEmployee)
                    <a href="{{ route('employees.show', $lastEmployee->id) }}" class="text-blue-600 hover:underline">
                        {{ $lastEmployee->shName }}
                    </a>
                @else
                    <span class="text-gray-500 italic">Нет сотрудника ({{$lastDismissedEmployee?->shName ?? ''}})</span>
                @endif
            </li>
        @endforeach
    </ul>
@endif
