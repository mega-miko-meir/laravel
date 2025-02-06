@props(['employee'])

<div class="mt-8 bg-white p-6 rounded-lg shadow-md">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Employee Information</h1>

    <div class="bg-white">
        <h2 class="text-2xl font-semibold text-gray-700 mb-4">{{ $employee->first_name }} {{ $employee->last_name }}</h2>

        <p class="text-lg text-gray-600 mb-2">
            <span class="font-medium text-gray-800">Email:</span> {{ $employee->email }}
        </p>

        <p class="text-lg text-gray-600 mb-2">
            <span class="font-medium text-gray-800">Position:</span> {{ $employee->position }}
        </p>
        @if($employee->territories->isNotEmpty())
            <p class="text-lg text-gray-600 mb-2">
                <span class="font-medium text-gray-800">Team:</span>
                {{ $employee->territories->first()->team }}
            </p>
            <p class="text-lg text-gray-600 mb-2">
                <span class="font-medium text-gray-800">City:</span>
                {{ $employee->territories->first()->city}}
            </p>
            <p class="text-lg text-gray-600">
                <span class="font-medium text-gray-800">Role:</span>
                {{ $employee->territories->first()->role }}
            </p>
            <p class="text-lg text-gray-600">
                <span class="font-medium text-gray-800">Manager:</span>
                {{ $employee->territories->first()->manager_id }}
            </p>
            @endif
            <p class="text-lg text-gray-600">
                <span class="font-medium text-gray-800">Hiring date:</span> {{ $employee->hiring_date }}
            </p>

    </div>
</div>
