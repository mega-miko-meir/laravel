@props([
    'action',
    'method' => 'POST',
    'territory' => null,
    'parentTerritories' => null,
    'role' => collect(config('constants.roles'))->sort()->reverse()->toArray(),
    'department' => collect(config('constants.departments'))->sort()->toArray(),
    'cities' => collect(config('constants.cities'))->sort()->toArray(),
    'teams' => collect(config('constants.teams'))->sort()->toArray()
])

{{-- @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif --}}

<x-flash-message />

<form action="{{ $action }}" method="POST" class="space-y-4">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div>
        <label for="territory" class="block text-sm font-medium text-gray-600">Territory</label>
        <input name="territory" type="text" placeholder="Territory" value="{{ old('territory', $territory->territory ?? '') }}"
               class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div>
        <label for="territory_name" class="block text-sm font-medium text-gray-600">Territory name</label>
        <input name="territory_name" id="territory_name" type="text" placeholder="Territory Name" value="{{ old('territory_name', $territory->territory_name ?? '') }}"
               class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div>
        <label for="department" class="block text-sm font-medium text-gray-600">Department</label>
        <select name="department" id="department" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            @foreach ($department as $dep)
                <option value="{{ $dep }}" {{ old('department', $territory->department ?? '') == $dep ? 'selected' : '' }}>
                    {{ $dep }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="team" class="block text-sm font-medium text-gray-600">Team</label>
        <select name="team" id="team" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            @foreach ($teams as $team)
                <option value="{{ $team }}" {{ old('team', $territory->team ?? '') == $team ? 'selected' : '' }}>
                    {{ $team }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="role" class="block text-sm font-medium text-gray-600">Role</label>
        <select name="role" id="role"
                class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            @foreach($role as $role)
                <option value="{{ $role }}" {{ old('role', $employee->role ?? '') === $role ? 'selected' : '' }}>
                    {{ $role }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="city" class="block text-sm font-medium text-gray-600">City</label>
        <select name="city" id="city" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            @foreach ($cities as $city)
                <option value="{{ $city }}" {{ old('city', $territory->city ?? '') == $city ? 'selected' : '' }}>
                    {{ $city }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="parent_territory_id" class="block text-sm font-medium text-gray-600">Parent Territory</label>
        <select name="parent_territory_id" id="parent_territory_id" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">No Parent Territory</option>
            @foreach ($parentTerritories as $parentTerritory)
                <option value="{{ $parentTerritory->id }}" {{ old('parent_territory_id', $territory->parent_territory_id ?? '') == $parentTerritory->id ? 'selected' : '' }}>
                    {{ $parentTerritory->territory_name }} - {{ $parentTerritory->employee ? $parentTerritory->employee->first_name . ' ' . $parentTerritory->employee->last_name : 'No employee'}}
                </option>
            @endforeach
        </select>
    </div>

    <div class="flex justify-end">
        <button type="submit"
                class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-lg transition duration-300">
            {{ $territory ? 'Edit' : 'Create' }}
        </button>
    </div>
</form>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const firstNameInput = document.getElementById("first_name");
        const lastNameInput = document.getElementById("last_name");
        const emailInput = document.getElementById("email");

        function generateEmail() {
            if (emailInput.dataset.autoGenerated === "false") return; // Если email изменен вручную, не перезаписываем

            let firstName = firstNameInput.value.trim().toLowerCase();
            let lastName = lastNameInput.value.trim().toLowerCase();

            if (firstName && lastName) {
                emailInput.value = `${firstName}.${lastName}@nobel.kz`;
            }
        }

        firstNameInput.addEventListener("input", generateEmail);
        lastNameInput.addEventListener("input", generateEmail);

        emailInput.addEventListener("focus", () => {
            emailInput.dataset.autoGenerated = "false"; // Если пользователь нажал на поле email, автогенерация отключается
        });
    });
</script>
