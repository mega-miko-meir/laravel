@props(['employees', 'sort', 'order'])

<div class="overflow-x-auto bg-white shadow rounded-lg mt-6 p-4">
    <table class="w-full border-collapse text-sm text-gray-700">
        <thead>
            <tr class="bg-gray-100 text-gray-600 uppercase text-xs">
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
                    <a href="{{ route('employees.search', ['sort' => 'event_date', 'order' => request('order') === 'asc' ? 'desc' : 'asc']) }}">
                        Event
                        {{-- @if($sort === 'event_date')
                            {!! $order === 'asc' ? '↑' : '↓' !!}
                        @endif --}}
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
                        <x-status-badge :status="$employee->events()->latest('event_date')->first()?->event_type" />
                    </td>

                    <td class="px-4 py-3 text-gray-700">
                        {{ \Carbon\Carbon::parse($employee->events()->latest('event_date')->first()?->event_date)->format('d.m.Y') ?? '-'}}
                    </td>

                    {{-- <td class="px-4 py-3 text-gray-700">
                        @php
                            $lastEvent = optional($employee->events->whereIn('event_type', ['maternity_leave', 'dismissed', 'changed_position'])->last());
                        @endphp
                        {{ $lastEvent->event_date ? \Carbon\Carbon::parse($lastEvent->event_date)->format('d.m.Y') : '-' }}
                    </td> --}}

                    <td class="px-4 py-3 text-gray-700">
                        {{ $employee->employee_territory()->latest('assigned_at')->first()->team ?? '-' }}
                    </td>
                    <td class="px-4 py-3 text-gray-700">
                        {{ $employee->employee_territory()->latest('assigned_at')->first()->city ?? '-' }}
                    </td>
                    <td class="px-4 py-3 flex items-center gap-3">
                        <x-edit-employee-button :employee="$employee"/>
                        <form action="/delete-employee/{{ $employee->id }}" method="POST"
                              onsubmit="return confirm('Are you sure?');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="text-red-500 hover:text-red-700 transition text-sm">
                                ✕
                            </button>
                        </form>

                        {{-- <form action="{{route('users.destroy', $user->id)}}" method="POST"
                            onsubmit="return confirm('Удалить пользователя?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                            class="text-red-500 hover:text-red-700 transition text-sm">
                            ✕
                            </button>
                        </form> --}}


                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

