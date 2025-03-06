@props(['employees', 'sort', 'order'])

<div class="overflow-x-auto bg-white shadow-md rounded-lg">
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-gray-100 text-gray-700 text-sm">
                <th class="px-4 py-3 text-left">
                    <a href="{{ route('employees.search', ['sort' => 'full_name', 'order' => request('order') === 'asc' ? 'desc' : 'asc']) }}">
                        Full Name
                        @if($sort === 'full_name')
                            {!! $order === 'asc' ? '↑' : '↓' !!}
                        @endif
                    </a>
                </th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-left">
                    <a href="{{ route('employees.search', ['sort' => 'hiring_date', 'order' => request('order') === 'asc' ? 'desc' : 'asc']) }}">
                        Hiring Date
                        @if($sort === 'hiring_date')
                            {!! $order === 'asc' ? '↑' : '↓' !!}
                        @endif
                    </a>
                </th>
                <th class="px-4 py-3 text-left">
                    <a href="{{ route('employees.search', ['sort' => 'firing_date', 'order' => request('order') === 'asc' ? 'desc' : 'asc']) }}">
                        Dismissal Date
                        @if($sort === 'firing_date')
                            {!! $order === 'asc' ? '↑' : '↓' !!}
                        @endif
                    </a>
                </th>
                <th class="px-4 py-3 text-left">Team</th>
                <th class="px-4 py-3 text-left">City</th>
                <th class="px-4 py-3 text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($employees as $employee)
                <tr class="border-b hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-gray-900">
                        <a href="/employee/{{ $employee->id }}" class="text-blue-500 hover:underline">
                            {{ $employee->full_name }}
                        </a>
                    </td>
                    <td class="px-4 py-3 text-gray-700">
                        <x-status-badge :status="$employee->status" />
                    </td>
                    <td class="px-4 py-3 text-gray-700">
                        {{ $employee->hiring_date ? \Carbon\Carbon::parse($employee->hiring_date)->format('d.m.Y') : '-'}}
                    </td>
                    {{-- <td class="px-4 py-3 text-gray-700">
                        {{ $employee->firing_date ? \Carbon\Carbon::parse($employee->firing_date)->format('d.m.Y') : '-'}}
                    </td> --}}
                    <td class="px-4 py-3 text-gray-700">
                        @php
                            $lastEvent = optional($employee->events->whereIn('event_type', ['maternity_leave', 'dismissed', 'changed_position'])->last());
                        @endphp
                        {{ $lastEvent->event_date ? \Carbon\Carbon::parse($lastEvent->event_date)->format('d.m.Y') : '-' }}
                    </td>

                    <td class="px-4 py-3 text-gray-700">
                        {{ $employee->territories->first()->team ?? '-' }}
                    </td>
                    <td class="px-4 py-3 text-gray-700">
                        {{ $employee->territories->first()->city ?? '-' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <x-edit-employee-button :employee="$employee"/>
                        <form action="/delete-employee/{{ $employee->id }}" method="POST"
                              onsubmit="return confirm('Are you sure?');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="bg-red-500 hover:bg-red-600 text-white text-xs font-medium py-1 px-3 rounded transition">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

