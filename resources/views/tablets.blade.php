@extends('layout')

@section('content')
@auth
<x-container class="container mx-auto py-4 px-4">

    {{-- ================================================================ --}}
    {{-- HEADER ROW                                                       --}}
    {{-- ================================================================ --}}
    <div class="flex items-center justify-between mb-4">
        <x-header />
        <x-create-tablet-button />
    </div>

    <x-flash-message />

    {{-- ================================================================ --}}
    {{-- MINI DASHBOARD                                                   --}}
    {{-- ================================================================ --}}
    @php
        $totalCount   = $tablets->count();
        // $freeCount    = $freeTablets->count();
        // $damagedCount = $tablets->whereIn('status', ['damaged', 'lost'])->count();
        // $activeCount  = $totalCount - $freeCount;
        // Указываем полный путь App\Models\Tablet
        $totalAllCount   = \App\Models\Tablet::where('status', 'active')->count();
        $freeCount    = \App\Models\Tablet::free()->get()->count();
        $newCount     = \App\Models\Tablet::where('status', 'new')->count();
        $damagedCount = \App\Models\Tablet::whereIn('status', ['damaged', 'lost'])->count();
        $adminCount = \App\Models\Tablet::whereIn('status', ['admin'])->count();
        $activeCount  = $totalCount - $freeCount;
    @endphp

    <div class="flex flex-wrap sm:flex-nowrap gap-3 mb-5">
        {{-- Всего --}}
        <a href="{{ route('tablets.search', ['search' => 'active']) }}" class="flex-1 min-w-[120px] bg-white rounded-lg border border-gray-200 px-4 py-2 hover:border-gray-400 transition">
            <div class="text-xs text-gray-400 uppercase tracking-wide mb-1">Всего исправных</div>
            <div class="text-2xl font-bold text-gray-800">{{ $totalAllCount }}</div>
        </a>

        {{-- Свободных --}}
        <a href="{{ route('tablets.search', ['search' => 'free']) }}"
        class="flex-1 min-w-[120px] bg-white rounded-lg border border-gray-200 px-4 py-2 hover:border-green-400 transition">
            <div class="text-xs text-gray-400 uppercase tracking-wide mb-1">Свободных</div>
            <div class="text-2xl font-bold text-green-600">{{ $freeCount }}</div>
        </a>

        {{-- Админ --}}
        <a href="{{ route('tablets.search', ['search' => 'admin']) }}" class="flex-1 min-w-[120px] bg-white rounded-lg border border-gray-200 px-4 py-2 hover:border-blue-400 transition">
            <div class="text-xs text-gray-400 uppercase tracking-wide mb-1">Админ</div>
            <div class="text-2xl font-bold text-blue-600">{{ $adminCount }}</div>
        </a>

        {{-- Новых --}}
        <a href="{{ route('tablets.search', ['search' => 'new']) }}" class="flex-1 min-w-[120px] bg-white rounded-lg border border-gray-200 px-4 py-2 hover:border-purple-400 transition">
            <div class="text-xs text-gray-400 uppercase tracking-wide mb-1">Новых</div>
            <div class="text-2xl font-bold text-purple-600">{{ $newCount }}</div>
        </a>

        {{-- Повреждён/Утерян --}}
        <a href="{{ route('tablets.search', ['search' => 'damaged']) }}" class="flex-1 min-w-[120px] bg-white rounded-lg border border-gray-200 px-4 py-2 hover:border-red-400 transition">
            <div class="text-xs text-gray-400 uppercase tracking-wide mb-1">Повреждён/Утерян</div>
            <div class="text-2xl font-bold text-red-500">{{ $damagedCount }}</div>
        </a>
    </div>

    {{-- ================================================================ --}}
    {{-- TOOLBAR: поиск + сотрудники без планшета                        --}}
    {{-- ================================================================ --}}
    <div class="flex flex-wrap items-center gap-3 mb-5">

        {{-- Поиск --}}
        <div class="flex-1 min-w-[180px] max-w-md">
            <x-search :action="route('tablets.search')" />
        </div>

        {{-- Сотрудники без планшета --}}
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open"
                    class="flex items-center gap-2 bg-white border border-gray-200 text-gray-700 text-sm px-3 py-2 rounded-lg hover:bg-gray-50 transition whitespace-nowrap">
                <span class="w-5 h-5 flex items-center justify-center bg-amber-100 text-amber-700 rounded-full text-xs font-bold">
                    {{ $count }}
                </span>
                Без планшета
            </button>

            <div x-show="open"
                 @click.away="open = false"
                 x-cloak
                 class="absolute left-0 mt-1 w-72 bg-white shadow-lg rounded-lg border border-gray-200 z-50 max-h-60 overflow-y-auto">
                <ul>
                    @forelse($availableEmployees as $employee)
                        <li class="px-3 py-2 hover:bg-gray-50 border-b border-gray-100 last:border-0">
                            <a href="{{ route('employees.show', $employee->id) }}"
                               class="text-sm text-blue-600 hover:underline">
                                {{ $employee->full_name }}
                            </a>
                        </li>
                    @empty
                        <li class="px-3 py-2 text-sm text-gray-400">Все сотрудники с планшетами</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- СВОБОДНЫЕ ПЛАНШЕТЫ (сворачиваемая секция)                       --}}
    {{-- ================================================================ --}}
    <div x-data="{ open: false }" class="mb-5">

        <button @click="open = !open"
                class="flex items-center gap-2 text-sm font-semibold text-gray-600 uppercase tracking-wide mb-2 hover:text-gray-900 transition">
            <span>Свободные планшеты</span>
            <span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ $freeCount }}</span>
            <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open" x-cloak>
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                <table class="w-full text-xs text-gray-700">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 uppercase tracking-wide">
                            <th class="px-3 py-2 text-left font-medium">Номер</th>
                            <th class="px-3 py-2 text-left font-medium">Серийный</th>
                            <th class="px-3 py-2 text-left font-medium">Последний сотрудник</th>
                            <th class="px-3 py-2 text-left font-medium">Выдача PDF</th>
                            <th class="px-3 py-2 text-left font-medium">Возврат PDF</th>
                            <th class="px-3 py-2 text-left font-medium">Ответственный</th>
                            <th class="px-3 py-2 text-left font-medium">Город</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($freeTablets as $tablet)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-3 py-2">
                                    <a href="{{ route('tablets.show', $tablet->id) }}"
                                       class="text-blue-500 hover:underline font-medium">
                                        {{ $tablet->invent_number }}
                                    </a>
                                </td>
                                <td class="px-3 py-2 text-gray-500">
                                    <a href="{{ route('tablets.show', $tablet->id) }}"
                                       class="text-blue-500 hover:underline font-medium">
                                        {{ $tablet->serial_number }}
                                    </a>
                                </td>
                                <td class="px-3 py-2">
                                    {{ $tablet->latestAssignment?->employee?->sh_name ?? '—' }}
                                </td>
                                <td class="px-3 py-2">
                                    @if($tablet->currentAssignment?->pdf_path)
                                        <a href="{{ asset('storage/' . $tablet->currentAssignment->pdf_path) }}"
                                           class="text-blue-500 hover:underline" target="_blank">📄</a>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    @if($tablet->currentAssignment?->unassign_pdf)
                                        <a href="{{ asset('storage/' . $tablet->currentAssignment->unassign_pdf) }}"
                                           class="text-blue-500 hover:underline" target="_blank">📄</a>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    @if($tablet->responsible)
                                        <a href="{{ route('employees.show', $tablet->responsible->id) }}"
                                           class="text-blue-500 hover:underline">
                                            {{ $tablet->responsible->sh_name }}
                                        </a>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-gray-500">
                                    {{ $tablet->responsible?->employee_territory()->latest('assigned_at')->first()?->city ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- ВСЕ ПЛАНШЕТЫ                                                    --}}
    {{-- ================================================================ --}}
    <div class="flex items-center gap-2 mb-2">
        <span class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Все планшеты</span>
        <span class="bg-gray-100 text-gray-600 text-xs font-bold px-2 py-0.5 rounded-full">{{ $totalCount }}</span>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <table class="w-full text-xs text-gray-700">
            <thead>
                <tr class="bg-gray-50 text-gray-500 uppercase tracking-wide">
                    <th class="px-3 py-2 text-left font-medium">Номер</th>
                    <th class="px-3 py-2 text-left font-medium">Серийный</th>
                    <th class="px-3 py-2 text-left font-medium">Сотрудник</th>
                    <th class="px-3 py-2 text-left font-medium">Дата привязки</th>
                    <th class="px-3 py-2 text-left font-medium">Модель</th>
                    <th class="px-3 py-2 text-left font-medium">Статус</th>
                    <th class="px-3 py-2 text-left font-medium">Выдача PDF</th>
                    <th class="px-3 py-2 text-left font-medium">Возврат PDF</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($tablets as $tablet)
                    @php
                        $statusColor = match($tablet->status) {
                            'new'     => 'bg-purple-100 text-purple-700',
                            'damaged' => 'bg-red-100 text-red-600',
                            'lost'    => 'bg-red-100 text-red-600',
                            'active'  => 'bg-blue-100 text-blue-700',
                            'free'    => 'bg-green-100 text-green-700',
                            default   => 'bg-gray-100 text-gray-500',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-3 py-2">
                            <a href="{{ route('tablets.show', $tablet->id) }}"
                               class="text-blue-500 hover:underline font-medium">
                                {{ $tablet->invent_number }}
                            </a>
                        </td>
                        <td class="px-3 py-2">
                            <a href="{{ route('tablets.show', $tablet->id) }}"
                               class="text-blue-500 hover:underline font-medium">
                                {{ $tablet->serial_number }}
                            </a>
                        </td>
                        <td class="px-3 py-2">
                            @if($tablet->current_employee)
                                <a href="{{ route('employees.show', $tablet->current_employee->id) }}"
                                   class="text-blue-500 hover:underline">
                                    {{ $tablet->current_employee->sh_name }}
                                </a>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-gray-500">
                            {{ $tablet->latestAssignment?->assigned_at?->format('d.m.Y') ?? '—' }}
                        </td>
                        <td class="px-3 py-2 text-gray-600">{{ $tablet->model }}</td>
                        <td class="px-3 py-2">
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-medium {{ $statusColor }}">
                                {{ $tablet->status }}
                            </span>
                        </td>
                        <td class="px-3 py-2">
                            @if($tablet->currentAssignment?->pdf_path)
                                <a href="{{ asset('storage/' . $tablet->currentAssignment->pdf_path) }}"
                                   class="text-blue-500 hover:underline" target="_blank">📄</a>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-2">
                            @if($tablet->currentAssignment?->unassign_pdf)
                                <a href="{{ asset('storage/' . $tablet->currentAssignment->unassign_pdf) }}"
                                   class="text-blue-500 hover:underline" target="_blank">📄</a>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</x-container>
@else
    <x-auth-container />
@endauth

<script src="{{ asset('js/search.js') }}"></script>
@endsection
