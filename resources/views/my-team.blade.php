@extends('layout')

@section('content')

<h1 class="text-2xl font-bold mt-10 mb-6">
    Команды
</h1>

@foreach($ffms->sortByDesc(fn($f) => $f->lastTerritory?->department)->groupBy(fn($f) => $f->lastTerritory->department ?? 'Без департамента') as $deptName => $groupedFfms)

    <div class="font-bold text-gray-500 uppercase text-xs mb-2 mt-6">
        Департамент: {{ $deptName }}
    </div>

    @foreach($groupedFfms as $ffm)

        @php
            $lastTerritory = $ffm->lastTerritory;
        @endphp

        @php
            $allPlaces = 0;
            $occupiedPlaces = 0;

            // Проверяем, существует ли территория вообще
            if ($lastTerritory) {
                foreach ($lastTerritory->children as $child) {
                    // Также на всякий случай проверяем существование дочерних элементов
                    if ($child->children) {
                        foreach ($child->children as $memberTerritory) {
                            $allPlaces++;

                            if ($memberTerritory->employee) {
                                $occupiedPlaces++;
                            }
                        }
                    }
                }
            }
        @endphp


        <h2 class="font-semibold mb-3 bt-3">
            {{ $ffm->full_name ?? 'FFM' }}
            — {{ $occupiedPlaces }}/{{ $allPlaces }}
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
                                    {{ $child->employeeTerritories()
                                        ->latest('assigned_at')
                                        ->first()
                                        ?->employee->sh_name ?? 'Нет сотрудника' }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $child->city }}
                                </div>
                            </div>

                            {{-- Красный бейдж в самом углу --}}
                            @if($freePlaces > 0)
                                <div class="absolute top-0 right-0
                                            text-white text-[10px] font-bold
                                            rounded-full min-w-[20px] h-5
                                            flex items-center justify-center z-10 p-2"
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

                                            <div class="ml-3 border-l pl-3">
                                                <div class="text-sm">
                                                    @if($memberTerritory->employee)
                                                        <a href="{{ route('employees.show', $memberTerritory->employee->id) }}"
                                                        class="text-blue-600 hover:underline">
                                                            {{ $memberTerritory->employee->sh_name }}
                                                        </a>
                                                    @else
                                                        <em class="text-gray-500">Нет сотрудника ({{$memberTerritory->employeeTerritories()->latest('assigned_at')->first()?->employee->sh_name}})</em>
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


