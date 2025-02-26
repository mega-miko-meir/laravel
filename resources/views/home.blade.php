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

                <div class="absolute top-4 right-4 flex gap-2">
                    <!-- Кнопка для создания сотрудника -->
                    <a href="/create-employee" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-1.5 px-4 rounded-md shadow-sm transition duration-200 flex items-center text-sm">
                        + Create
                    </a>

                    <!-- Кнопка для экспорта в Excel -->
                    <a href="/export-excel" class="bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-1.5 px-4 rounded-md shadow-sm transition duration-200 flex items-center text-sm">
                        Export
                    </a>
                </div>

                <x-active-employee-checkbox />

                <!-- Компонент поиска -->
                <x-search class="mb-6" />

                <!-- Заголовок с количеством сотрудников -->
                <h2 class="text-2xl font-bold mb-4 mt-6">
                    Список всех сотрудников ({{ $employees->count() }})
                </h2>

                <!-- Список сотрудников -->
                <x-employee-card :employees="$employees" :sort="$sort" :order="$order"/>
            </div>
        </x-container>
    @else
        <x-auth-container />
    @endauth

    <script src="{{ asset('js/search.js') }}"></script>
@endsection
