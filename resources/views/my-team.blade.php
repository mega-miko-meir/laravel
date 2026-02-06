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

        // team => ['used' => 0, 'total' => 0]
        $teamsStats = [];

        foreach ($groupedFfms as $ffm) {
            if (!$ffm->lastTerritory) continue;

            // 🔹 RM — дочерние территории FFM
            foreach ($ffm->lastTerritory->children as $rmTerritory) {
                $rmTotal++;

                $rmActive = $rmTerritory->employeeTerritories()
                    ->whereNull('unassigned_at')
                    ->latest('assigned_at')
                    ->first();

                if ($rmActive) {
                    $rmUsed++;
                }

                // 🔹 Rep — дочерние территории RM
                foreach ($rmTerritory->children as $repTerritory) {
                    $repTotal++;

                    $repActive = $repTerritory->employeeTerritories()
                        ->whereNull('unassigned_at')
                        ->latest('assigned_at')
                        ->first();

                    if ($repActive) {
                        $repUsed++;
                    }

                    $team = $repTerritory->team ?? 'Без группы';

                    if (!isset($teamsStats[$team])) {
                        $teamsStats[$team] = [
                            'used'  => 0,
                            'total' => 0,
                        ];
                    }

                    $teamsStats[$team]['total']++;

                    if ($repActive) {
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

    <div x-data="{ viewMode: 'ffm' }">

        <div x-show="open2" class="mb-4">
            <div class="inline-flex rounded-lg bg-gray-100 p-1">
                <button
                    @click="viewMode = 'ffm'"
                    :class="viewMode === 'ffm'
                        ? 'bg-white shadow text-blue-600'
                        : 'text-gray-500 hover:text-gray-700'"
                    class="px-4 py-1.5 text-sm font-medium rounded-md transition"
                >
                    По FFM и RM
                </button>

                <button
                    @click="viewMode = 'team'"
                    :class="viewMode === 'team'
                        ? 'bg-white shadow text-blue-600'
                        : 'text-gray-500 hover:text-gray-700'"
                    class="px-4 py-1.5 text-sm font-medium rounded-md transition"
                >
                    По группам
                </button>
            </div>
        </div>


        <div x-show="viewMode === 'ffm'" x-cloak>
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
                        // team => ['total' => 0, 'used' => 0]
                        $teamsStats = [];

                        if ($lastTerritory) {
                            foreach ($lastTerritory->children as $rmTerritory) {

                                // 🔹 RM
                                $rmTotal++;

                                $rmActive = $rmTerritory->employeeTerritories()
                                    ->whereNull('unassigned_at')
                                    ->latest('assigned_at')
                                    ->first();

                                if ($rmActive) {
                                    $rmUsed++;
                                }

                                // 🔹 Rep внутри RM
                                foreach ($rmTerritory->children as $repTerritory) {

                                    $repTotal++;

                                    $repActive = $repTerritory->employeeTerritories()
                                        ->whereNull('unassigned_at')
                                        ->latest('assigned_at')
                                        ->first();

                                    if ($repActive) {
                                        $repUsed++;
                                    }

                                    $team = $repTerritory->team ?? 'Без группы';

                                    if (!isset($teamsStats[$team])) {
                                        $teamsStats[$team] = [
                                            'total' => 0,
                                            'used'  => 0,
                                        ];
                                    }

                                    $teamsStats[$team]['total']++;

                                    if ($repActive) {
                                        $teamsStats[$team]['used']++;
                                    }
                                }
                            }
                        }

                        // 🔤 сортировка team по алфавиту
                        ksort($teamsStats, SORT_NATURAL | SORT_FLAG_CASE);
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

                                            $activeAssignment = $memberTerritory->employeeTerritories()
                                                ->whereNull('unassigned_at')
                                                ->latest('assigned_at')
                                                ->first();

                                            if ($activeAssignment) {
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
                                                    $activeAssignment = $child->employeeTerritories()
                                                        ->whereNull('unassigned_at')
                                                        ->latest('assigned_at')
                                                        ->first();

                                                    $dismissedAssignment = $child->employeeTerritories()
                                                        ->whereNotNull('unassigned_at')
                                                        ->latest('assigned_at')
                                                        ->first();

                                                    $employee = $activeAssignment?->employee;
                                                    $employeeDismissed = $dismissedAssignment?->employee;
                                                @endphp


                                                @if ($employee)
                                                    {{ $employee->sh_name_sh }}
                                                @elseif ($employeeDismissed)
                                                    <em class="text-gray-500">
                                                        ({{ $employeeDismissed->sh_name_sh }})
                                                    </em>
                                                @else
                                                    <em class="text-gray-400">—</em>
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
                                                                    class="text-blue-600 hover:underline flex items-center gap-1">

                                                                        {{ $employee->sh_name }}

                                                                        @if($employee->latestEvent->event_date && \Carbon\Carbon::parse($employee->latestEvent->event_date)->greaterThanOrEqualTo(now()->subDays(30)))
                                                                        <span class="ml-1 inline-flex items-center justify-center
                                                                                        min-w-[22px] px-2 py-0.5 text-[10px] font-bold
                                                                                        text-white rounded-lg"
                                                                                        style="background-color: #50C878">
                                                                                new
                                                                            </span>

                                                                        @endif
                                                                    </a>

                                                                @elseif($lastEmployee)
                                                                    <a href="{{ route('employees.show', $lastEmployee->id) }}"
                                                                    class="text-gray-500 hover:underline italic">
                                                                        ({{ $lastEmployee->sh_name }})
                                                                    </a>
                                                                @else
                                                                    <span class="text-gray-400 italic">Нет сотрудника</span>
                                                                @endif


                                                                {{-- @if($employee)
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
                                                                @endif --}}

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
        <div x-show="viewMode === 'team'" x-cloak>
            <div x-show="open2" class="flex flex-wrap gap-4">
                @php
                    // Собираем все rep территории по департаменту
                    $repTerritories = collect();

                    foreach ($groupedFfms as $ffm) {
                        if (!$ffm->lastTerritory) continue;

                        // Берём все дочерние rep территори из всех RM этого FFM
                        foreach ($ffm->lastTerritory->children as $rm) {
                            foreach ($rm->children as $rep) {
                                $repTerritories->push($rep);
                            }
                        }
                    }

                    // Группировка по team
                    $groupedByTeam = $repTerritories
                        ->sortBy('team')
                        ->groupBy('team');
                @endphp

                <div class="flex flex-wrap gap-4">
                    @foreach($groupedByTeam as $teamName => $teamTerritories)
                        @php
                            // сортировка Rep по городу
                            $teamTerritories = $teamTerritories
                                ->sortBy('city', SORT_NATURAL | SORT_FLAG_CASE);

                            $totalRep = $teamTerritories->count();
                            $usedRep  = 0;

                            foreach ($teamTerritories as $repTerritory) {
                                $active = $repTerritory->employeeTerritories()
                                    ->whereNull('unassigned_at')
                                    ->latest('assigned_at')
                                    ->first();

                                if ($active) {
                                    $usedRep++;
                                }
                            }
                        @endphp

                        <div class="w-64 bg-white rounded-xl shadow p-2">

                            {{-- TEAM (как RM в режиме FFM) --}}
                            <div class="relative flex justify-between items-center p-3">
                                <div>
                                    <div class="font-bold text-gray-800 text-sm uppercase">
                                        {{ $teamName ?? 'Без команды' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Rep {{ $usedRep }}/{{ $totalRep }}
                                    </div>
                                </div>
                            </div>

                            {{-- REP --}}
                            <div class="mt-3 space-y-2">

                                @foreach($teamTerritories as $repTerritory)

                                    @php
                                        $active = $repTerritory->employeeTerritories()
                                            ->whereNull('unassigned_at')
                                            ->latest('assigned_at')
                                            ->first();

                                        $last   = $repTerritory->employeeTerritories()
                                            ->latest('assigned_at')
                                            ->first();

                                        $employee = $active?->employee;
                                        $fallback = $last?->employee;
                                    @endphp

                                    <div class="ml-2 border-l pl-3 text-sm">

                                        @if($employee)
                                            <div class="flex items-center gap-1">
                                                <a href="{{ route('employees.show', $employee->id) }}"
                                                class="text-blue-600 hover:underline">
                                                    {{ $employee->sh_name }}
                                                </a>

                                                <span class="text-gray-500 text-xs">
                                                    &nbsp;({{ $repTerritory->city }})
                                                </span>

                                                @if(
                                                    $employee->latestEvent?->event_date &&
                                                    \Carbon\Carbon::parse($employee->latestEvent->event_date)
                                                        ->greaterThanOrEqualTo(now()->subDays(30))
                                                )
                                                    <span class="ml-1 px-2 py-0.5 text-[10px] font-bold text-white rounded-lg"
                                                        style="background-color:#50C878">
                                                        new
                                                    </span>
                                                @endif
                                            </div>


                                        @elseif($fallback)
                                            <a href="{{ route('employees.show', $fallback->id) }}"
                                            class="text-gray-500 italic hover:underline">
                                                ({{ $fallback->sh_name }})
                                            </a>
                                            <span class="text-gray-500 text-xs">({{ $repTerritory->city }})</span>

                                        @else
                                            <a href="{{ route('territories.show', $repTerritory->id) }}"
                                            class="text-gray-500 italic hover:underline">
                                                Нет сотрудника
                                                <span class="text-xs">({{ $repTerritory->city }})</span>
                                            </a>
                                        @endif

                                    </div>


                                @endforeach

                            </div>
                        </div>

                    @endforeach
                </div>




            </div>
        </div>


    </div>
</div>
@endforeach
@endsection


