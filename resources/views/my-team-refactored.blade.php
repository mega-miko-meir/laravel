@extends('layout')

@section('content')

<h1 class="text-2xl font-bold mt-10 mb-6">Команда</h1>

@foreach($data as $deptName => $deptData)
    @php
        $stats = $deptData['stats'];
        $ffms = $deptData['ffms'];
    @endphp

    <div x-data="{ open2: false }">
        {{-- Department Header --}}
        <div @click="open2 = !open2" class="cursor-pointer flex flex-wrap items-center gap-2 font-bold uppercase text-xs mb-2 mt-6">
            <span class="text-gray-500">Департамент: {{ $deptName }}</span>
            <span class="text-blue-600">RM {{ $stats['rmUsed'] }}/{{ $stats['rmTotal'] }}</span>
            <span class="text-green-600">Rep {{ $stats['repUsed'] }}/{{ $stats['repTotal'] }}</span>

            @foreach($stats['teamsStats'] as $teamName => $stat)
                <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-800">
                    {{ $teamName }} {{ $stat['used'] }}/{{ $stat['total'] }}
                </span>
            @endforeach
        </div>

        <div x-data="{ viewMode: 'team' }">
            {{-- View mode toggle --}}
            <div x-show="open2" class="mb-4">
                <div class="inline-flex rounded-lg bg-gray-100 p-1">
                    <button @click="viewMode = 'team'"
                            :class="viewMode === 'team' ? 'bg-white shadow text-blue-600' : 'text-gray-500 hover:text-gray-700'"
                            class="px-4 py-1.5 text-sm font-medium rounded-md transition">
                        По группам
                    </button>
                    <button @click="viewMode = 'ffm'"
                            :class="viewMode === 'ffm' ? 'bg-white shadow text-blue-600' : 'text-gray-500 hover:text-gray-700'"
                            class="px-4 py-1.5 text-sm font-medium rounded-md transition">
                        По FFM и RM
                    </button>
                </div>
            </div>

            {{-- FFM View --}}
            <div x-show="viewMode === 'ffm'" x-cloak>
                <div x-show="open2">
                    @forelse($ffms as $ffm)
                        @include('my-team-partials.ffm-view', ['ffm' => $ffm])
                    @empty
                        <p class="text-gray-400">Нет данных</p>
                    @endforelse
                </div>
            </div>

            {{-- Team View --}}
            <div x-show="viewMode === 'team'" x-cloak>
                <div x-show="open2" class="flex flex-wrap gap-4">
                    @include('my-team-partials.team-view', ['ffms' => $ffms, 'productEmployees' => $deptData['productEmployees']])
                </div>
            </div>
        </div>
    </div>
@endforeach

@endsection
