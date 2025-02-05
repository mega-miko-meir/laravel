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
                            <div class="flex items-center space-x-4">
                                <form action="/unassign-territory/{{$employee->id}}/{{$territory->id}}" method="POST" onsubmit="return confirm('Are you sure you want to assign the territory?');">
                                    @csrf
                                    <button class="btn-primary bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">
                                        Unassign territory
                                    </button>
                                </form>
                                <form action="/form-template/{{$employee->id}}" method="POST" onsubmit="return confirm('Are you sure you want to from OCE template?');">
                                    @csrf
                                    <button class="btn-primary bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-6 rounded">
                                        Form OCE template
                                    </button>
                                </form>
                                @if ($territory->assignmentToRemove)
                                    {{-- <h1>{{$employee->employee_territory->confirmed ?? "Not confirmed"}}</h1> --}}
                                    <form action="{{ route('confirm.territory', [$employee->id, $territory->id]) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit" class="btn-success bg-green-500 hover:bg-green-600 text-white font-bold py-1 px-3 rounded">
                                            Confirm
                                        </button>
                                    </form>
                                @else
                                    <span class="text-green-500">(Confirmed)</span>
                                @endif





                                {{-- @foreach ($assignments as $assignment)
                                    <div class="assignment-item flex justify-between items-center mb-4">
                                        <span class="text-lg">{{ $assignment->territory->territory_name }}</span>

                                        <!-- Метка подтверждения -->
                                        @if ($assignment->confirmed)
                                            <span class="text-sm text-green-500">Подтверждено</span>
                                        @else
                                            <span class="text-sm text-red-500">Не подтверждено</span>
                                            <button class="btn btn-primary confirm-button" data-assignment-id="{{ $assignment->id }}">
                                                Подтвердить
                                            </button>
                                        @endif
                                    </div>
                                @endforeach --}}


                                {{-- <form action="/export-excel/{{$employee->id}}/{{$employee->territory->id}}" method="GET" onsubmit="return confirm('Are you sure you want to form OCE template?');">
                                    @csrf
                                    <button class="btn-primary bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded">Form Template</button>
                                </form> --}}
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
