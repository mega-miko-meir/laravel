@extends('layout')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <x-back-button />
        <h1>This is employee create form</h1>
        <div class="p-6 bg-gray-100 rounded-lg shadow-md mb-8">
            <h2 class="text-xl font-semibold mb-4">Create an employee</h2>
            <form action="/create-employee" method="POST" class="space-y-4">
                @csrf
                <input name="full_name" type="text" placeholder="Full Name" class="w-full p-2 border rounded">
                <input name="first_name" type="text" placeholder="First Name" class="w-full p-2 border rounded">
                <input name="last_name" type="text" placeholder="Last Name" class="w-full p-2 border rounded">
                <input name="birth_date" type="date" placeholder="Birth date" class="w-full p-2 border rounded">
                <input name="email" type="email" placeholder="Email" class="w-full p-2 border rounded">
                <input name="hiring_date" type="date" placeholder="Hiring Date" class="w-full p-2 border rounded">
                <input name="position" type="text" placeholder="Position" class="w-full p-2 border rounded">
                <button class="btn-primary bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Submit</button>
            </form>
        </div>
    </div>
@endsection
