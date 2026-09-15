@extends('layout')

@section('content')
<h2 class="text-xl font-bold mb-6">
    Полевая команда
</h2>


@foreach($grouped as $deptName => $dept)
<div x-data="{ open2: false }">

    <div @click="open2 = !open2" class="cursor-pointer flex flex-wrap items-center gap-2 font-bold uppercase text-xs mb-2 mt-6">
        {{-- Название департамента --}}
        <span class="text-gray-500">
            Департамент: {{ $deptName }}
        </span>

        {{-- RM --}}
        <span class="text-blue-600">
            RM {{ $dept['stats']['rmUsed'] }}/{{ $dept['stats']['rmTotal'] }}
        </span>

        {{-- Rep --}}
        <span class="text-green-600">
            Rep {{ $dept['stats']['repUsed'] }}/{{ $dept['stats']['repTotal'] }}
        </span>

        {{-- Teams --}}
        @foreach($dept['stats']['teams'] as $teamName => $stat)
            <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-800">
                {{ $teamName }} {{ $stat['used'] ?? 0 }}/{{ $stat['total'] }}
            </span>
        @endforeach

    </div>

    <div x-data="{ viewMode: 'team' }">

        <div x-show="open2" class="mb-4">
            <div class="inline-flex rounded-lg bg-gray-100 p-1">
                <button
                    @click="viewMode = 'team'"
                    :class="viewMode === 'team'
                        ? 'bg-white shadow text-blue-600'
                        : 'text-gray-500 hover:text-gray-700'"
                    class="px-4 py-1.5 text-sm font-medium rounded-md transition"
                >
                    По группам
                </button>
                <button
                    @click="viewMode = 'ffm'"
                    :class="viewMode === 'ffm'
                        ? 'bg-white shadow text-blue-600'
                        : 'text-gray-500 hover:text-gray-700'"
                    class="px-4 py-1.5 text-sm font-medium rounded-md transition"
                >
                    По FFM и RM
                </button>
            </div>
        </div>


        <div x-show="viewMode === 'ffm'" x-cloak>
            <div  x-show="open2">
                @foreach($dept['ffms'] as $ffm)
                    @continue(!$ffm->lastTerritory)

                    <h2 class="mb-3 mt-4 flex flex-wrap items-center gap-2">

                        {{-- Имя FFM --}}
                        <a href="{{ route('employees.show', $ffm->id) }}"
                        class="font-semibold text-gray-800 hover:text-blue-600 transition underline-offset-4 hover:underline">
                            {{ $ffm->full_name ?? 'FFM' }}
                        </a>

                        {{-- RM --}}
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    bg-blue-100 text-blue-700">
                            RM {{ $ffm->ffmStats['rmUsed'] }}/{{ $ffm->ffmStats['rmTotal'] }}
                        </span>

                        {{-- Rep --}}
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    bg-green-100 text-green-700">
                            Rep {{ $ffm->ffmStats['repUsed'] }}/{{ $ffm->ffmStats['repTotal'] }}
                        </span>

                        {{-- Rep по team --}}
                        @foreach($ffm->ffmStats['teams'] as $teamName => $stat)
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                        bg-gray-100 text-gray-800">
                                {{ $teamName }} {{ $stat['used'] ?? 0 }}/{{ $stat['total'] }}
                            </span>
                        @endforeach

                    </h2>

                    @if($ffm->preparedRms->isNotEmpty())
                        <div x-data="{ open: false }" style="display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:0.75rem">

                            @foreach($ffm->preparedRms->sortBy('city') as $rm)

                                <div class="w-full bg-white rounded-xl shadow p-2">

                                    <div @click="open = !open"
                                        class="cursor-pointer relative flex justify-between items-center p-3">

                                        {{-- Левая часть --}}
                                        <div>
                                            <div class="font-bold text-gray-800 text-sm">
                                                @if ($rm->activeEmployee)
                                                    {{ $rm->activeEmployee->sh_name_sh }}
                                                @elseif ($rm->dismissedEmployee)
                                                    <em class="text-gray-500">
                                                        ({{ $rm->dismissedEmployee->sh_name_sh }})
                                                    </em>
                                                @else
                                                    <em class="text-gray-400">—</em>
                                                @endif
                                            </div>

                                            <div class="text-sm text-gray-500">
                                                {{ $rm->city }}
                                            </div>
                                        </div>

                                        {{-- Красный бейдж в самом углу --}}
                                        @if($rm->freePlaces > 0)
                                            <div class="absolute top-0 right-0
                                                        text-white text-[5px] font-semibold
                                                        rounded-full w-4 h-4
                                                        flex items-center justify-center
                                                        -translate-y-1 translate-x-1 z-10"
                                                        style="background-color: #dc2626;">
                                                {{ $rm->freePlaces }}
                                            </div>
                                        @endif

                                        {{-- Стрелка --}}
                                        <div class="text-gray-400 ml-4">▼</div>
                                    </div>


                                    <div x-show="open" x-cloak class="mt-3 space-y-4">

                                        {{-- 🔹 группировка по team --}}
                                        @foreach($rm->preparedReps as $teamName => $repsInTeam)

                                            <div class="ml-2 border-l-2 border-gray-200 pl-3">
                                                <div class="text-sm font-semibold text-gray-700">
                                                    {{ $teamName ?? 'Без группы' }}
                                                </div>

                                                <div class="mt-2 space-y-1">
                                                    @foreach($repsInTeam as $repRow)

                                                        <div class="ml-2 mb-2 border-l pl-3">
                                                            <div class="text-sm">
                                                                @if($repRow->employee)
                                                                    <a href="{{ route('employees.show', $repRow->employee->id) }}"
                                                                    class="text-blue-600 hover:underline flex items-center gap-1">

                                                                        {{ $repRow->employee->sh_name }}

                                                                        @if($repRow->isNew)
                                                                        <span class="ml-1 inline-flex items-center justify-center
                                                                                        min-w-[22px] px-2 py-0.5 text-[10px] font-bold
                                                                                        text-white rounded-lg"
                                                                                        style="background-color: #50C878">
                                                                                new
                                                                            </span>

                                                                        @endif
                                                                    </a>

                                                                @elseif($repRow->fallback)
                                                                    <a href="{{ route('employees.show', $repRow->fallback->id) }}"
                                                                    class="text-gray-500 hover:underline italic">
                                                                        ({{ $repRow->fallback->sh_name }})
                                                                    </a>
                                                                @else
                                                                    <span class="text-gray-400 italic">Нет сотрудника</span>
                                                                @endif
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
            <div x-show="open2">
                <div style="display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:0.75rem">
                    @foreach($dept['teamView'] as $teamData)

                        <div class="w-full bg-white rounded-xl shadow p-2">

                            <div class="relative flex justify-between items-center p-3">
                                <div>
                                    <div class="font-bold text-gray-800 text-sm uppercase">
                                        {{ $teamData->name ?? 'Без команды' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Rep {{ $teamData->usedRep }}/{{ $teamData->total }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 space-y-2">

                                @foreach($teamData->reps as $repRow)

                                    <div class="ml-2 border-l pl-3 text-sm">

                                        @if($repRow->employee)
                                            <div class="flex items-center gap-1">
                                                <a href="{{ route('employees.show', $repRow->employee->id) }}"
                                                class="text-blue-600 hover:underline">
                                                    {{ $repRow->employee->sh_name }}
                                                </a>

                                                <span class="text-gray-500 text-xs">
                                                    &nbsp;({{ $repRow->city }})
                                                </span>

                                                @if($repRow->isNew)
                                                    <span class="ml-1 px-2 py-0.5 text-[10px] font-bold text-white rounded-lg"
                                                        style="background-color:#50C878">
                                                        new
                                                    </span>
                                                @endif
                                            </div>

                                        @elseif($repRow->fallback)
                                            <a href="{{ route('employees.show', $repRow->fallback->id) }}"
                                            class="text-gray-500 italic hover:underline">
                                                ({{ $repRow->fallback->sh_name }})
                                            </a>
                                            <span class="text-gray-500 text-xs">({{ $repRow->city }})</span>

                                        @else
                                            <a href="{{ route('territories.show', $repRow->territoryId) }}"
                                            class="text-gray-500 italic hover:underline">
                                                Нет сотрудника
                                                <span class="text-xs">({{ $repRow->city }})</span>
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

<br>

{{-- ================================================================== --}}
{{-- PRODUCT                                                            --}}
{{-- ================================================================== --}}

@if($productTerritories->isNotEmpty())

    <h2 class="text-xl font-bold mt-12 mb-2">Продакт менеджеры</h2>

    @foreach($productTerritories as $deptName => $deptTerritories)
        <div x-data="{ open: false }">

            @php
                $productTotal  = $deptTerritories->count();
                $productUsed   = 0;

                foreach ($deptTerritories as $t) {
                    $a = $t->employeeTerritories
                        ->whereNull('unassigned_at')
                        ->sortByDesc('assigned_at')
                        ->first();
                    if ($a) $productUsed++;
                }

                $groupedByTeam = $deptTerritories->sortBy('team')->groupBy('team');
            @endphp

            <div @click="open = !open"
                 class="cursor-pointer flex flex-wrap items-center gap-2 font-bold uppercase text-xs mb-2 mt-6">
                <span class="text-gray-500">Департамент: {{ $deptName }}</span>
                <span class="text-purple-600">PM {{ $productUsed }}
                    {{-- /{{ $productTotal }} --}}
                </span>
            </div>

            <div x-show="open">
            <div style="display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:0.75rem">

                @foreach($groupedByTeam as $teamName => $teamTerritories)

                    @php
                        $teamTerritories = $teamTerritories->sortBy('city', SORT_NATURAL | SORT_FLAG_CASE);
                        $teamTotal = $teamTerritories->count();
                        $teamUsed  = 0;

                        foreach ($teamTerritories as $t) {
                            $a = $t->employeeTerritories
                                ->whereNull('unassigned_at')
                                ->sortByDesc('assigned_at')
                                ->first();
                            if ($a) $teamUsed++;
                        }
                    @endphp

                    <div class="w-full bg-white rounded-xl shadow p-2">

                        <div class="relative flex justify-between items-center p-3">
                            <div>
                                <div class="font-bold text-gray-800 text-sm uppercase">
                                    {{ $teamName ?? 'Без группы' }}
                                </div>
                                {{-- <div class="text-xs text-gray-500">
                                    Product {{ $teamUsed }}/{{ $teamTotal }}
                                </div> --}}
                            </div>
                        </div>

                        <div class="mt-3 space-y-2">
                            @foreach($teamTerritories as $territory)

                                @php
                                    $active   = $territory->employeeTerritories
                                        ->whereNull('unassigned_at')
                                        ->sortByDesc('assigned_at')
                                        ->first();

                                    $last     = $territory->employeeTerritories
                                        ->sortByDesc('assigned_at')
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
                                            {{-- <span class="text-gray-500 text-xs">({{ $territory->city }})</span> --}}

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

                                    {{-- @elseif($fallback)
                                        <a href="{{ route('employees.show', $fallback->id) }}"
                                           class="text-gray-500 italic hover:underline">
                                            ({{ $fallback->sh_name }})
                                        </a>
                                        <span class="text-gray-500 text-xs">({{ $territory->city }})</span>

                                    @else
                                        <a href="{{ route('territories.show', $territory->id) }}"
                                           class="text-gray-500 italic hover:underline">
                                            Нет сотрудника
                                            <span class="text-xs">({{ $territory->city }})</span>
                                        </a> --}}
                                    @endif

                                </div>

                            @endforeach
                        </div>

                    </div>

                @endforeach

            </div>
            </div>

        </div>
    @endforeach

@endif

@endsection
