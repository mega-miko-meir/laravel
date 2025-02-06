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
                    @endforeach
                </ul>
            </p>
        </div>
        <x-checkbox :employee="$employee" :bricks="$bricks" :selectedBricks="$selectedBricks" />

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
                    <option value="{{ $territory->id }}">{{ $territory->territory_name}} - {{ $territory->manager_id }} - {{ $territory->old_employee_id ?? ''}}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-primary mt-4 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Assign</button>
        </form>
    @endif
</div>
<br>
<div>
    <h3 class="font-semibold text-sm text-gray-700">History:</h3>
    <ul class="text-sm text-gray-500">
        @foreach($territoriesHistory as $history)
            <li>
                <span >{{ $history->territory ? $history->territory->territory_name : '' }}</span>
                <span class="text-sm">{{ \Carbon\Carbon::parse($history->assigned_at)->format('d.m.Y') }} - {{ $history->unassigned_at ? \Carbon\Carbon::parse($history->unassigned_at)->format('d.m.Y') : ''}}</span>
            </li>
        @endforeach
    </ul>
</div>
<br>
