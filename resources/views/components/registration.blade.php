@extends('layout')

@section('content')

<div id="auth-content" class="w-full max-w-md bg-white shadow-md rounded-lg p-6">
    <div>
        <div class="p-6 bg-gray-100 rounded-lg shadow-md mb-6">
            <h2 class="text-xl font-semibold mb-4">Register</h2>
            <form action="/register" method="POST" class="space-y-4">
                @csrf
                <input name="full_name" type="text" placeholder="Full name" value="{{old('full_name')}}" class="w-full p-2 border rounded">
                <input name="first_name" type="text" placeholder="First name" value="{{old('first_name')}}" class="w-full p-2 border rounded">
                <input name="last_name" type="text" placeholder="Last name" value="{{old('last_name')}}" class="w-full p-2 border rounded">
                <input name="position" type="text" placeholder="Position" value="{{old('position')}}" class="w-full p-2 border rounded">
                <input name="email" type="email" placeholder="Email" value="{{old('email')}}" class="w-full p-2 border rounded">
                <div class="mt-4">
                    {{-- <label for="role_id" class="block text-sm font-medium text-gray-700">Выберите роль</label> --}}
                    <select name="role_id" id="role_id" required
                            class="w-full p-2 border rounded focus:ring-blue-500 focus:border-blue-500">
                        <option value="" disabled {{ old('role_id') ? '' : 'selected' }}>Выберите роль...</option>
                        @foreach(App\Models\Role::all() as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>
                <input name="password" type="password" placeholder="Password" required class="w-full p-2 border rounded">
                <input name="password_confirmation" type="password" placeholder="Password confirmation" required class="w-full p-2 border rounded">
                <button class="btn-primary bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Submit</button>
            </form>
        </div>
    </div>
</div>

@endsection
