@extends('layout')

@section('content')


{{-- <div class="container mx-auto"> --}}
    @auth
    <div class="flex">
        <!-- Слева боковое меню -->
        @include('partials.__side-menu')

        <!-- Основной контент -->
        <div class="flex-1 ml-64 p-8">
            <!-- Включение шапки -->
            <div class="mb-6">
                @include('partials.__header')
            </div>

            <!-- Сообщение об успехе -->
            <div class="mb-6">
                <x-flash-message />
            </div>

            <!-- Кнопка для создания сотрудника -->
            <div class="mb-6">
                <x-create-employee-button />
            </div>

            <!-- Компонент поиска -->
            <div class="mb-6">
                @include('partials.__search')
            </div>

            <!-- Заголовок с количеством сотрудников -->
            <h2 class="text-2xl font-bold mb-4 mt-6">
                Список всех сотрудников ({{ $employees->count() }})
            </h2>

            <!-- Список сотрудников -->
            <div class="space-y-4">
                @foreach ($employees as $employee)
                    <x-employees-component :employee="$employee" />
                @endforeach
            </div>
        </div>

    </div>

    @else
    <div id="auth-container" class="flex flex-col items-center justify-center min-h-screen bg-gray-100 p-4">
        <!-- Заголовок -->
        <h1 id="auth-title" class="text-2xl font-semibold mb-6">Login</h1>

        <!-- Компоненты -->
        <div id="auth-content" class="w-full max-w-md bg-white shadow-md rounded-lg p-6">
            <x-login />
        </div>

        <!-- Кнопка переключения -->
        <button
            id="auth-toggle"
            onclick="toggleAuth()"
            class="mt-4 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">
            Registration
        </button>
    </div>

    <script>
        const toggleAuth = () => {
            const title = document.getElementById('auth-title');
            const content = document.getElementById('auth-content');
            const button = document.getElementById('auth-toggle');

            if (title.innerText === 'Login') {
                title.innerText = 'Registration';
                content.innerHTML = `<x-registration />`;
                button.innerText = 'Login';
            } else {
                title.innerText = 'Login';
                content.innerHTML = `<x-login />`;
                button.innerText = 'Registration';
            }
        };
    </script>


    @endauth
{{-- </div> --}}

<script src="{{ asset('js/search.js') }}"></script>





@endsection
