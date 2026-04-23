{{-- resources/views/components/tablet-toolbar.blade.php --}}
@props(['availableEmployees', 'count'])

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
            Сотрудн. без планшета
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

    {{-- Кнопка выгрузки --}}
    <x-tablet-export-dropdown />

</div>
