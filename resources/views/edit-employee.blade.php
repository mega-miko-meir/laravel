@extends('layout')

@section('content')
<div class="bg-gray-200 min-h-screen flex items-center justify-center">
    <div class="p-8 bg-white rounded-lg shadow-lg w-full max-w-3xl">
        <x-back-button />
        <h1 class="text-2xl font-bold mb-6 text-gray-700">Edit Employee</h1>
        <form action="/edit-employee/{{$employee->id}}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="full_name" class="block text-sm font-medium text-gray-600">Full name</label>
                <input name="full_name" type="text" placeholder="Full Name" value="{{$employee->full_name}}" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="first_name" class="block text-sm font-medium text-gray-600">First Name</label>
                <input name="first_name" type="text" placeholder="First Name" value="{{$employee->first_name}}" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="last_name" class="block text-sm font-medium text-gray-600">Last Name</label>
                <input name="last_name" type="text" placeholder="Last Name" value="{{$employee->last_name}}" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="birth_date" class="block text-sm font-medium text-gray-600">Birth date</label>
                <input name="birth_date" type="date" placeholder="Birth date" value="{{$employee->birth_date}}" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-600">Email</label>
                <input name="email" type="email" placeholder="Email" value="{{$employee->email}}" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="hiring_date" class="block text-sm font-medium text-gray-600">Hiring Date</label>
                <input name="hiring_date" type="date" value="{{$employee->hiring_date}}" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="position" class="block text-sm font-medium text-gray-600">Position</label>
                <input name="position" type="text" placeholder="Position" value="{{$employee->position}}" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-lg transition duration-300">Edit</button>
            </div>
        </form>
    </div>
</div>
@endsection
