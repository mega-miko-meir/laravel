@extends('layout')

@section('content')
    @auth
        <x-container class="container mx-auto py-6">
            <!-- Боковое меню -->
            {{-- <x-side-menu class="col-span-2" /> --}}

            <!-- Основной контент -->
            <div class="col-span-10 p-8 bg-white relative">
                <!-- Включение шапки -->
                <x-header class="mb-6" />

                <!-- Сообщение об успехе -->
                <x-flash-message />

                <!-- Кнопка для создания сотрудника -->
                <div class="absolute top-0 right-0 mt-4 mr-4">
                    <x-create-employee-button />
                </div>

                <!-- Компонент поиска -->
                <x-search class="mb-6" />

                <!-- Заголовок с количеством сотрудников -->
                <h2 class="text-2xl font-bold mb-4 mt-6">
                    Список всех сотрудников ({{ $employees->count() }})
                </h2>

                <!-- Список сотрудников -->
                <x-employee-card :employees="$employees" />
            </div>
        </x-container>
    @else
        <x-auth-container />
    @endauth

    <script src="{{ asset('js/search.js') }}"></script>
@endsection
