@extends('layout')

@section('content')

<h1 class="text-2xl font-bold mt-10 mb-6">
    Команды
</h1>

@foreach($ffms->sortByDesc(fn($f) => $f->lastTerritory?->department)->groupBy(fn($f) => $f->lastTerritory->department ?? 'Без департамента') as $deptName => $groupedFfms)

    @php
        $rmTotal = 0;
        $rmUsed  = 0;

        $repTotal = 0;
        $repUsed  = 0;

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
                }
            }
        }
    @endphp



    <div class="flex items-center gap-2 font-bold text-gray-500 uppercase text-xs mb-2 mt-6">
        <span>Департамент: {{ $deptName }}</span>

        <span class="text-blue-600">
            RM {{ $rmUsed }}/{{ $rmTotal }}
        </span>

        <span class="text-green-600">
            Rep {{ $repUsed }}/{{ $repTotal }}
        </span>
    </div>


    @foreach($groupedFfms as $ffm)
        @php
            $lastTerritory = $ffm->lastTerritory;

            // RM
            $rmTotal = 0;
            $rmUsed  = 0;

            // Rep
            $repTotal = 0;
            $repUsed  = 0;

            if ($lastTerritory) {
                foreach ($lastTerritory->children as $rmTerritory) {
                    // считаем RM
                    $rmTotal++;

                    if ($rmTerritory->employee) {
                        $rmUsed++;
                    }

                    // считаем Rep внутри RM
                    foreach ($rmTerritory->children as $repTerritory) {
                        $repTotal++;

                        if ($repTerritory->employee) {
                            $repUsed++;
                        }
                    }
                }
            }
        @endphp

        <h2 class="mb-3 mt-4 flex items-center gap-3">
            {{-- Имя FFM --}}
            <a href="{{ route('employees.show', $ffm->id) }}"
            class="font-semibold text-gray-800 hover:text-blue-600 transition underline-offset-4 hover:underline">
                {{ $ffm->full_name ?? 'FFM' }}
            </a>

            {{-- RM --}}
            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                        bg-blue-50 text-blue-700">
                RM {{ $rmUsed }}/{{ $rmTotal }}
            </span>

            {{-- Rep --}}
            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                        bg-green-50 text-green-700">
                Rep {{ $repUsed }}/{{ $repTotal }}
            </span>
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
                                                    @if($memberTerritory->employee)
                                                        <a href="{{ route('employees.show', $memberTerritory->employee->id) }}"
                                                        class="text-blue-600 hover:underline">
                                                            {{ $memberTerritory->employee->sh_name }}
                                                        </a>
                                                    @else
                                                        <em class="text-gray-500">({{$memberTerritory->employeeTerritories()->latest('assigned_at')->first()?->employee->sh_name}})</em>
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

@endforeach

@endsection


