<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite('resources/css/app.css')

    <title>Laravel project</title>
</head>
@auth
    @if (!isset($showHeader))
        @include('partials.__header')
    @endif
    <body style="padding-top: {{$printPadding ?? 5}}rem;">
        @yield('content')
    </body>
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
</html>
