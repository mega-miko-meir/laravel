{{-- @extends('layout')

@section('content')
<div class="bg-gray-200 min-h-screen flex items-center justify-center">
    <div class="p-8 bg-white rounded-lg shadow-lg w-full max-w-3xl">
        <x-back-button />
        <h1 class="text-2xl font-bold mb-6 text-gray-700">Edit Employee</h1>
        <x-employee-form :employee="$employee" action="/edit-employee/{{$employee->id}}" method="PUT" />
    </div>
</div>
@endsection --}}


@extends('layout')

@section('content')
    <div class="bg-gray-200 min-h-screen flex items-center justify-center">
        <div class="p-8 bg-white rounded-lg shadow-lg w-full max-w-3xl">
            <x-back-button />
            <h1 class="text-2xl font-bold mb-6 text-gray-700">
                {{ isset($employee) ? 'Edit Employee' : 'Create Employee' }}
            </h1>
            <x-employee-form
                :employee="$employee ?? null"
                action="{{ isset($employee) ? '/edit-employee/' . $employee->id : '/create-employee' }}"
                method="{{ isset($employee) ? 'PUT' : 'POST' }}"
            />
        </div>
    </div>
@endsection
