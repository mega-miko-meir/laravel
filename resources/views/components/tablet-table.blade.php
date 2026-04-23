{{-- resources/views/components/tablet-table.blade.php --}}
@props(['tablets'])

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
                        'admin'   => 'bg-blue-100 text-blue-700',
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
                           class="text-blue-500 hover:underline">
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
