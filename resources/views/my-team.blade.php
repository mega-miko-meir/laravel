@extends('layout')

@section('content')

{{-- <div x-data="{ open2: false }" class="text-2xl font-bold mt-10 mb-6">
    <button @click="open2 = !open2">Click</button>
    <h1 >Команда 1</h1>
    <div>
        <h1 x-show="open2">Команда 2</h1>
        <h1 x-show="open2">Команда 3</h1>
    </div>
</div> --}}

<h1 class="text-2xl font-bold mt-10 mb-6">
    Команда
</h1>

@foreach($ffms->sortByDesc(fn($f) => $f->lastTerritory?->department)->groupBy(fn($f) => $f->lastTerritory->department ?? 'Без департамента') as $deptName => $groupedFfms)
<div x-data="{ open2: false }">

    @php
        $rmTotal = 0;
        $rmUsed  = 0;

        $repTotal = 0;
        $repUsed  = 0;

        $teamsStats = []; // 🔹 team => ['used' => 0, 'total' => 0]

        foreach ($groupedFfms as $ffm) {
            if (!$ffm->lastTerritory) continue;

            // 🔹 RM — дочерние территории FFM
            foreach ($ffm->lastTerritory->children as $rmTerritory) {
                $rmTotal++;

                if ($rmTerritory->employee) {
                    $rmUsed++;
                }

                // 🔹 Rep — дочерние территории RM
                foreach ($rmTerritory->children as $repTerritory) {
                    $repTotal++;

                    if ($repTerritory->employee) {
                        $repUsed++;
                    }

                    $team = $repTerritory->team ?? 'Без группы';

                    if (!isset($teamsStats[$team])) {
                        $teamsStats[$team] = ['used' => 0, 'total' => 0];
                    }

                    $teamsStats[$team]['total']++;

                    if ($repTerritory->employee) {
                        $teamsStats[$team]['used']++;
                    }
                }
            }
        }

        // 🔤 сортировка team по алфавиту
        ksort($teamsStats, SORT_NATURAL | SORT_FLAG_CASE);
    @endphp

    <div @click="open2 = !open2" class="cursor-pointer flex flex-wrap items-center gap-2 font-bold uppercase text-xs mb-2 mt-6">
        {{-- Название департамента --}}
        <span class="text-gray-500">
            Департамент: {{ $deptName }}
        </span>

        {{-- RM --}}
        <span class="text-blue-600">
            RM {{ $rmUsed }}/{{ $rmTotal }}
        </span>

        {{-- Rep --}}
        <span class="text-green-600">
            Rep {{ $repUsed }}/{{ $repTotal }}
        </span>

        {{-- Teams --}}
        @foreach($teamsStats as $teamName => $stat)
            <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-800">
                {{ $teamName }} {{ $stat['used'] }}/{{ $stat['total'] }}
            </span>
        @endforeach

    </div>

    <div  x-show="open2">
    @foreach($groupedFfms as $ffm)
        @php
            $lastTerritory = $ffm->lastTerritory;

            // RM
            $rmTotal = 0;
            $rmUsed  = 0;

            // Rep (общие)
            $repTotal = 0;
            $repUsed  = 0;

            // Rep по team
            $teamsStats = [];

            if ($lastTerritory) {
                foreach ($lastTerritory->children as $rmTerritory) {

                    // 🔹 RM
                    $rmTotal++;
                    if ($rmTerritory->employee) {
                        $rmUsed++;
                    }

                    // 🔹 Rep внутри RM
                    foreach ($rmTerritory->children as $repTerritory) {

                        $repTotal++;
                        $team = $repTerritory->team ?? 'Без группы';

                        if (!isset($teamsStats[$team])) {
                            $teamsStats[$team] = [
                                'total' => 0,
                                'used'  => 0,
                            ];
                        }

                        $teamsStats[$team]['total']++;

                        if ($repTerritory->employee) {
                            $repUsed++;
                            $teamsStats[$team]['used']++;
                        }
                    }
                }
            }

            // сортировка team по алфавиту
            ksort($teamsStats);
        @endphp

        <h2 class="mb-3 mt-4 flex flex-wrap items-center gap-2">

            {{-- Имя FFM --}}
            <a href="{{ route('employees.show', $ffm->id) }}"
            class="font-semibold text-gray-800 hover:text-blue-600 transition underline-offset-4 hover:underline">
                {{ $ffm->full_name ?? 'FFM' }}
            </a>

            {{-- RM --}}
            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                        bg-blue-100 text-blue-700">
                RM {{ $rmUsed }}/{{ $rmTotal }}
            </span>

            {{-- Rep --}}
            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                        bg-green-100 text-green-700">
                Rep {{ $repUsed }}/{{ $repTotal }}
            </span>

            {{-- Rep по team --}}
            @foreach($teamsStats as $teamName => $stat)
                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                            bg-gray-100 text-gray-800">
                    {{ $teamName }} {{ $stat['used'] }}/{{ $stat['total'] }}
                </span>
            @endforeach

        </h2>



        @if($lastTerritory && $lastTerritory->children->isNotEmpty())
            <div x-data="{ open: false }" class="flex flex-wrap gap-4">

                @foreach($lastTerritory->children->sortBy('city') as $child)

                    <div class="w-64 bg-white rounded-xl shadow p-2">
                        @php
                            $allPlaces = 0;
                            $occupiedPlaces = 0;


                            foreach ($child->children as $memberTerritory) {
                                $allPlaces++;
                                if ($memberTerritory->employee) {
                                    $occupiedPlaces++;
                                }
                            }

                            $freePlaces = $allPlaces - $occupiedPlaces;
                        @endphp

                        <div @click="open = !open"
                            class="cursor-pointer relative flex justify-between items-center p-3">

                            {{-- Левая часть --}}
                            <div>
                                <div class="font-bold text-gray-800 text-sm">
                                    @php
                                        $employee = optional(
                                            $child->employeeTerritories()
                                                ->whereNull('unassigned_at')
                                                ->latest('assigned_at')
                                                ->first()
                                        )->employee;

                                        $employeeDismissed = optional(
                                            $child->employeeTerritories()
                                                ->whereNotNull('unassigned_at')
                                                ->latest('assigned_at')
                                                ->first()
                                        )->employee;
                                    @endphp

                                    @if ($employee)
                                        {{ $employee->sh_name_sh }}
                                    @else
                                        <em class="text-gray-500">
                                            @if ($employeeDismissed)
                                                ({{ $employeeDismissed->sh_name_sh }})
                                            @endif
                                        </em>
                                    @endif
                                </div>

                                <div class="text-sm text-gray-500">
                                    {{ $child->city }}
                                </div>
                            </div>

                            {{-- Красный бейдж в самом углу --}}
                            @if($freePlaces > 0)
                                <div class="absolute top-0 right-0
                                            text-white text-[5px] font-semibold
                                            rounded-full w-4 h-4
                                            flex items-center justify-center
                                            -translate-y-1 translate-x-1 z-10"
                                            style="background-color: #dc2626;">
                                    {{ $freePlaces }}
                                </div>
                            @endif

                            {{-- Стрелка --}}
                            <div class="text-gray-400 ml-4">▼</div>
                        </div>


                        <div x-show="open" x-cloak class="mt-3 space-y-4">

                            {{-- 🔹 группировка по team --}}
                            @foreach($child->children->sortBy('team')->groupBy('team') as $teamName => $groupTerritories)

                                <div class="ml-2 border-l-2 border-gray-200 pl-3">
                                    <div class="text-sm font-semibold text-gray-700">
                                        {{ $teamName ?? 'Без группы' }}
                                    </div>

                                    <div class="mt-2 space-y-1">
                                        @foreach($groupTerritories as $memberTerritory)

                                            <div class="ml-2 mb-2 border-l pl-3">
                                                <div class="text-sm">
                                                    @php
                                                        $activeAssignment = $memberTerritory->employeeTerritories()
                                                            ->whereNull('unassigned_at')
                                                            ->latest('assigned_at')
                                                            ->first();

                                                        $lastAssignment = $memberTerritory->employeeTerritories()
                                                            ->latest('assigned_at')
                                                            ->first();

                                                        $employee = $activeAssignment?->employee;
                                                        $lastEmployee = $lastAssignment?->employee;
                                                    @endphp

                                                    @if($employee)
                                                        <a href="{{ route('employees.show', $employee->id) }}"
                                                        class="text-blue-600 hover:underline">
                                                            {{ $employee->sh_name }}
                                                        </a>
                                                    @elseif($lastEmployee)
                                                        <a href="{{ route('employees.show', $lastEmployee->id) }}"
                                                        class="text-gray-500 hover:underline italic">
                                                            ({{ $lastEmployee->sh_name }})
                                                        </a>
                                                    @else
                                                        <span class="text-gray-400 italic">Нет сотрудника</span>
                                                    @endif

                                                    {{-- @if($memberTerritory->employee)
                                                        <a href="{{ route('employees.show', $memberTerritory->employee->id) }}"
                                                        class="text-blue-600 hover:underline">
                                                            {{ $memberTerritory->employee->sh_name }}
                                                        </a>
                                                    @else
                                                        <a href="{{ route('employees.show', $memberTerritory->employeeTerritories()->latest('assigned_at')->first()?->employee->id) }}">
                                                        <em class="text-gray-500 hover:underline">({{$memberTerritory->employeeTerritories()->latest('assigned_at')->first()?->employee->sh_name}})</em>
                                                        </a>
                                                    @endif --}}
                                                </div>
                                            </div>

                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-400">Нет территорий</p>
        @endif

    @endforeach
    </div>
</div>
@endforeach
@endsection


