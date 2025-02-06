@props(['action', 'method' => 'POST', 'employee' => null])

<form action="{{ $action }}" method="POST" class="space-y-4">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div>
        <label for="full_name" class="block text-sm font-medium text-gray-600">Full Name</label>
        <input name="full_name" type="text" placeholder="Full Name" value="{{ old('full_name', $employee->full_name ?? '') }}"
               class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div>
        <label for="first_name" class="block text-sm font-medium text-gray-600">First Name</label>
        <input name="first_name" type="text" placeholder="First Name" value="{{ old('first_name', $employee->first_name ?? '') }}"
               class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div>
        <label for="last_name" class="block text-sm font-medium text-gray-600">Last Name</label>
        <input name="last_name" type="text" placeholder="Last Name" value="{{ old('last_name', $employee->last_name ?? '') }}"
               class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div>
        <label for="birth_date" class="block text-sm font-medium text-gray-600">Birth Date</label>
        <input name="birth_date" type="date" value="{{ old('birth_date', $employee->birth_date ?? '') }}"
               class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div>
        <label for="email" class="block text-sm font-medium text-gray-600">Email</label>
        <input name="email" type="email" placeholder="Email" value="{{ old('email', $employee->email ?? '') }}"
               class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div>
        <label for="hiring_date" class="block text-sm font-medium text-gray-600">Hiring Date</label>
        <input name="hiring_date" type="date" value="{{ old('hiring_date', $employee->hiring_date ?? '') }}"
               class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div>
        <label for="position" class="block text-sm font-medium text-gray-600">Position</label>
        <input name="position" type="text" placeholder="Position" value="{{ old('position', $employee->position ?? '') }}"
               class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div class="flex justify-end">
        <button type="submit"
                class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-lg transition duration-300">
            {{ $employee ? 'Edit' : 'Create' }}
        </button>
    </div>
</form>
