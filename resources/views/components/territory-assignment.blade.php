@props(['employee', 'bricks','selectedBricks', 'availableTablets', 'availableTerritories', 'territoriesHistory', 'lastTerritory'])

<div class="space-y-6">
    <!-- Territory Assignment Section FOR TESTING-->
    <div class="bg-white p-6 rounded-lg shadow-md">
        {{-- <h2 class="text-xl font-semibold text-gray-800">Territory Assignment</h2> --}}
        {{-- @if ($employee->territories->isNotEmpty()) --}}

        @if ($lastTerritory && is_null(optional($lastTerritory->pivot)->unassigned_at))
            <div class="mt-4">
                <span class="font-medium text-gray-800">Территория:</span>

                    <!-- Изменено: flex-col и items-start (чтобы прижать к левому краю) -->
                    <li class="flex flex-col items-start text-gray-600 py-2 border-bottom">

                        <!-- Ссылка (будет сверху) -->
                        <a href="{{ route('territories.show', $lastTerritory->id) }}" class="text-blue-600 hover:underline mb-2">
                            {{ $lastTerritory->territory_name }}
                        </a>

                        <!-- Контейнер с формами (будет снизу) -->
                        <!-- flex-wrap добавлен на случай, если кнопки не влезут в одну строку на мобильных -->
                        <div class="flex flex-wrap items-center gap-2 text-sm">

                            <div class="flex flex-wrap items-center gap-2">
                                <!-- Кнопка удаления (с полем даты) -->
                                <form action="/unassign-territory/{{$employee->id}}/{{$lastTerritory->id}}" method="POST"
                                    onsubmit="return confirm('Are you sure you want to unassign the territory?');"
                                    class="flex items-center gap-1">
                                    @csrf
                                    <!-- Поле даты: уменьшен шрифт и высота -->
                                    <input type="date" name="unassigned_at" id="unassigned_at"
                                        value="{{ now()->format('Y-m-d') }}"
                                        class="border border-gray-300 rounded text-[11px] px-1 py-0.5 focus:ring-1 focus:ring-blue-300 outline-none h-7">

                                    <button class="bg-red-400 hover:bg-red-500 text-white text-[8px] py-1 px-2 rounded shadow-sm transition-all h-7 flex items-center">
                                        ❌
                                    </button>
                                </form>

                                <!-- Кнопка OCE template -->
                                <form action="/form-template/{{$employee->id}}" method="POST"
                                    onsubmit="return confirm('Are you sure you want to use OCE template?');">
                                    @csrf
                                    <button class="bg-blue-400 hover:bg-blue-500 text-white text-[11px] py-1 px-2 rounded shadow-sm transition-all h-7 flex items-center">
                                        📝 OCE
                                    </button>
                                </form>
                            </div>


                            <!-- Кнопка подтверждения -->
                            @if (!$lastTerritory->pivot->confirmed)
                                <form action="{{ route('confirm.territory', [$employee->id, $lastTerritory->id]) }}" method="POST">
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


                    @if ($lastTerritory->role === 'Rep')
                        <x-checkbox :employee="$employee" :bricks="$bricks" :selectedBricks="$selectedBricks" :territory="$lastTerritory"/>
                    @else
                        <x-child-territories :territory="$lastTerritory" />
                    @endif
            </div>
        @else
            <p class="text-lg text-gray-600">Нет назначенных территории</p>
            <!-- Assign Territory Form -->
                <form action="/assign-territory/{{$employee->id}}" method="POST" class="mt-3 space-y-2">
                    @csrf
                    <label for="territory" class="block text-sm font-medium text-gray-600">Назначение территории</label>
                    <select id="territory" name="territory_id" class="w-full p-2 border rounded-lg text-sm">
                        <option value="">No Territory</option>
                        @foreach ($availableTerritories as $territory)
                        <option value="{{ $territory->id }}">
                            {{ $territory->territory_name }} -
                            {{ $territory->parent && $territory->parent->employee ? $territory->parent->employee->first_name . ' ' . $territory->parent->employee->last_name : 'Нет руководителя' }} -
                            {{ $territory->old_employee_id ?? '' }}
                        </option>

                        @endforeach
                    </select>
                    <input type="date" name="assigned_at" id="assigned_at" value="{{ now()->format('Y-m-d')}}" class="w-full p-2 border rounded-lg text-sm">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-1.5 px-4 rounded text-sm">Назначить</button>
                </form>
        @endif

        <div x-data="{open:true}" class="bg-white mt-6">
            <button
            {{-- onclick="toggleTerritoryHistory()" --}}s
            x-on:click="open = !open"
            class="w-full text-left font-semibold text-lg text-gray-700 border-b pb-2 mb-3 flex justify-between items-center">
                История
                <svg :class="{'rotate-180': open}" id="territoryArrowIcon" class="w-5 h-5 transition-transform transform rotate-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <ul x-show="open" id="territoryHistoryList" class="text-sm text-gray-600 space-y-2" style="display:none">
                @foreach($territoriesHistory as $history)
                    <li class="flex justify-between items-center border-b py-2">
                        <div>
                            {{-- <span>{{$history->pivot->id}}</span> --}}
                            <span class="font-medium text-gray-800">
                                <a href="{{route('territories.show', $history->id)}}" class="text-blue-500 hover:underline" >{{ $history->territory_name ?? 'Неизвестная территория' }}</a>
                            </span>
                            <span class="text-sm text-gray-500 ml-2">
                                {{ \Carbon\Carbon::parse($history->pivot->assigned_at)->format('d.m.Y') }} -
                                {{ $history->pivot->unassigned_at ? \Carbon\Carbon::parse($history->pivot->unassigned_at)->format('d.m.Y') : 'Текущий пользователь' }}
                            </span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>


</div>

{{-- <script>
    function toggleTerritoryHistory() {
        let list = document.getElementById("territoryHistoryList");
        let arrow = document.getElementById("territoryArrowIcon");

        list.classList.toggle("hidden");
        arrow.classList.toggle("rotate-180");
    }
</script> --}}


