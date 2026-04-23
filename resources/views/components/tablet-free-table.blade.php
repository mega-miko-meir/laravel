{{-- resources/views/components/tablet-free-table.blade.php --}}
@props(['freeTablets', 'freeCount'])

<div x-data="{ open: false }" class="mb-5">

    <button @click="open = !open"
            class="flex items-center gap-2 text-sm font-semibold text-gray-600 uppercase tracking-wide mb-2 hover:text-gray-900 transition">
        <span>Свободные планшеты</span>
        <span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ $freeCount }}</span>
        <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                            <td class="px-3 py-2">
                                <a href="{{ route('tablets.show', $tablet->id) }}"
                                   class="text-blue-500 hover:underline">
                                    {{ $tablet->serial_number }}
                                </a>
                            </td>
                            <td class="px-3 py-2 text-gray-600">
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
