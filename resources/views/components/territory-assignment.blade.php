@props(['employee', 'bricks','selectedBricks', 'availableTablets', 'availableTerritories', 'territoriesHistory'])

<!-- Territory Assignment Section FOR TESTING-->
<div class="mt-8 bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-xl font-semibold text-gray-800">Territory Assignment</h2>

    @if($employee->territories->isNotEmpty())
        <div class="mt-4">
            <p class="text-lg text-gray-600">
                <span class="font-medium text-gray-800">Territory:</span>
                <ul class="space-y-4">
                    @foreach($employee->territories as $territory)
                        <li class="flex items-center justify-between text text-gray-600 py-2">
                            <span>{{ $territory->territory_name }}</span>
                            <div class="flex items-center space-x-2 text-sm">
                                <!-- Кнопка удаления территории -->
                                <form action="/unassign-territory/{{$employee->id}}/{{$territory->id}}" method="POST"
                                      onsubmit="return confirm('Are you sure you want to unassign the territory?');">
                                    @csrf
                                    <button class="bg-red-400 hover:bg-red-500 text-white font-medium py-1 px-3 rounded-md shadow-sm transition-all">
                                        ❌ Unassign
                                    </button>
                                </form>

                                <!-- Кнопка OCE template -->
                                <form action="/form-template/{{$employee->id}}" method="POST"
                                      onsubmit="return confirm('Are you sure you want to use OCE template?');">
                                    @csrf
                                    <button class="bg-blue-400 hover:bg-blue-500 text-white font-medium py-1 px-3 rounded-md shadow-sm transition-all">
                                        📝 OCE Template
                                    </button>
                                </form>

                                <!-- Кнопка подтверждения -->
                                @if ($territory->assignmentToRemove)
                                    <form action="{{ route('confirm.territory', [$employee->id, $territory->id]) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="bg-green-400 hover:bg-green-500 text-white font-medium py-1 px-3 rounded-md shadow-sm transition-all">
                                            ✅ Confirm
                                        </button>
                                    </form>
                                @else
                                    <span class="text-green-600 font-medium">✔️ Confirmed</span>
                                @endif
                            </div>
                        </li>
                        @if ($territory->role === 'Rep')
                            <x-checkbox :employee="$employee" :bricks="$bricks" :selectedBricks="$selectedBricks" />
                        @else
                            @if ($territory->children->isEmpty())
                                <p>Нет дочерних территорий</p>
                            @else
                            <p>Дочерние территории</p>
                            <ul>
                                @foreach ($territory->children->sortBy(['team', 'asc'])->sortBy(['territory_name', 'asc']) as $child)
                                <li>
                                    <span class="font-semibold text-gray-700">{{ $child->team }}</span> -
                                    <a href="{{route('territories.show', $child->id)}}" class="text-blue-600 hover:underline">
                                        {{ $child->territory_name }}
                                    </a> -
                                    @if ($child->employee)
                                        <a href="{{ route('employees.show', $child->employee->id) }}" class="text-blue-600 hover:underline">
                                            {{ $child->employee->full_name }}
                                        </a>
                                    @else
                                        <span class="text-gray-500 italic">Нет сотрудника</span>
                                    @endif

                                </li>
                                @endforeach
                            </ul>
                            @endif
                        @endif
                    @endforeach
                </ul>
            </p>
        </div>


        {{-- Brick information section --}}

    @else
        <p class="text-lg text-gray-600">No territory assigned</p>
        <!-- Assign Territory Form -->
        <form action="/assign-territory/{{$employee->id}}" method="POST" class="mt-4">
            @csrf
            <label for="territory" class="block text-sm font-medium text-gray-600">Assign Territory</label>
            <select id="territory" name="territory_id" class="w-full p-3 border rounded-lg mt-2">
                <option value="">No Territory</option>
                @foreach ($availableTerritories as $territory)
                <option value="{{ $territory->id }}">
                    {{ $territory->territory_name }} -
                    {{ $territory->parent && $territory->parent->employee ? $territory->parent->employee->first_name . ' ' . $territory->parent->employee->last_name : 'Нет руководителя' }} -
                    {{ $territory->old_employee_id ?? '' }}
                </option>

                @endforeach
            </select>
            <input type="date" name="assigned_at" id="assigned_at" value="{{ now()->format('Y-m-d')}}">
            <button type="submit" class="btn-primary mt-4 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Assign</button>
        </form>
    @endif
</div>
<div class="bg-white shadow-md rounded-lg p-4 mt-6">
    <button onclick="toggleTerritoryHistory()" class="w-full text-left font-semibold text-lg text-gray-700 border-b pb-2 mb-3 flex justify-between items-center">
        История территорий
        <svg id="territoryArrowIcon" class="w-5 h-5 transition-transform transform rotate-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <ul id="territoryHistoryList" class="text-sm text-gray-600 space-y-2 hidden">
        @foreach($territoriesHistory as $history)
            <li class="flex justify-between items-center border-b py-2">
                <div>
                    <span class="font-medium text-gray-800">
                        {{ $history->territory ? $history->territory->territory_name : 'Неизвестная территория' }}
                    </span>
                    <span class="text-sm text-gray-500 ml-2">
                        {{ \Carbon\Carbon::parse($history->assigned_at)->format('d.m.Y') }} -
                        {{ $history->unassigned_at ? \Carbon\Carbon::parse($history->unassigned_at)->format('d.m.Y') : 'Текущий' }}
                    </span>
                </div>
            </li>
        @endforeach
    </ul>
</div>

<script>
    function toggleTerritoryHistory() {
        let list = document.getElementById("territoryHistoryList");
        let arrow = document.getElementById("territoryArrowIcon");

        list.classList.toggle("hidden");
        arrow.classList.toggle("rotate-180");
    }
</script>
