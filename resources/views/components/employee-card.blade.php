@props(['employees'])

<div class="overflow-x-auto bg-white shadow-md rounded-lg">
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-gray-100 text-gray-700 text-sm">
                <th class="px-4 py-3 text-left">Full Name</th>
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
                        {{ $employee->territories->first()->team ?? '-' }}
                    </td>
                    <td class="px-4 py-3 text-gray-700">
                        {{ $employee->territories->first()->city ?? '-' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <x-edit-employee-button :employee="$employee"/>
                        {{-- <form action="/delete-employee/{{ $employee->id }}" method="POST"
                              onsubmit="return confirm('Are you sure?');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="bg-red-500 hover:bg-red-600 text-white text-xs font-medium py-1 px-3 rounded transition">
                                Delete
                            </button>
                        </form> --}}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

