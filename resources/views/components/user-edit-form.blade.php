@extends('layout')

@section('content')

<div id="auth-content" class="w-full max-w-md bg-white shadow-md rounded-lg p-6">
    <div>
        <div class="p-6 bg-gray-100 rounded-lg shadow-md mb-6">
            <h2 class="text-xl font-semibold mb-4">Edit</h2>
            <form action="{{route("users.update", $user->id)}}" method="POST" class="space-y-4">
                @method('PUT')
                @csrf
                <input name="full_name" type="text" placeholder="Full name" value="{{$user->full_name}}" class="w-full p-2 border rounded">
                <input name="first_name" type="text" placeholder="First name" value="{{$user->first_name}}" class="w-full p-2 border rounded">
                <input name="last_name" type="text" placeholder="Last name" value="{{$user->last_name}}" class="w-full p-2 border rounded">
                <input name="position" type="text" placeholder="Position" value="{{$user->position}}" class="w-full p-2 border rounded">
                <input name="email" type="email" placeholder="Email" value="{{$user->email}}" class="w-full p-2 border rounded">


                <select name="role_id" id="role_id" required
                        class="w-full p-2 border rounded focus:ring-blue-500 focus:border-blue-500">

                    <option value="" disabled {{ old('role_id', $user->role_id ?? null) ? '' : 'selected' }}>
                        Выберите роль...
                    </option>

                    @foreach(App\Models\Role::all() as $role)
                            <option value="{{ $role->id }}"
                                @selected(old('role_id', $user->role_id ?? null) == $role->id)>
                                {{ ucfirst($role->name) }}
                            </option>
                        @endforeach
                </select>

                @error('role_id')
                    <span class="text-red-500 text-xs">{{ $message }}</span>
                @enderror


                <input name="password" type="password" placeholder="Password" class="w-full p-2 border rounded">
                <input name="password_confirmation" type="password" placeholder="Password confirmation" class="w-full p-2 border rounded">
                <button class="btn-primary bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Submit</button>
            </form>
        </div>
    </div>
</div>

@endsection
