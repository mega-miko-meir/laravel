@extends('layout')

@section('content')
    <div class="bg-gray-200 min-h-screen flex items-center justify-center">
        <div class="p-8 bg-white rounded-lg shadow-lg w-full max-w-3xl">
            <x-back-button />

            {{-- @if(isset($employee) && $employee->territories->isNotEmpty()) --}}
                {{-- @foreach ($employee->territories as $territory) --}}
                    <h1 class="text-2xl font-bold mb-6 text-gray-700">
                        Добавить планшет
                    </h1>
                    <x-tablet-form
                        :tablet="$tablet ?? null"
                        action="{{ isset($tablet) ? route('tablet.edit', $tablet->id) : route('tablet.create') }}"
                        method="{{ isset($tablet) ? 'PUT' : 'POST' }}"
                    />
                    {{-- <x-tablet-form
                        action="{{ route('tablet.edit') }}"
                        method="POST"
                    /> --}}
                {{-- @endforeach --}}
            {{-- @else
                <p class="text-gray-600">Нет территорий для отображения.</p>
            @endif --}}
        </div>
    </div>
@endsection
