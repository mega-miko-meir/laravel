<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite('resources/css/app.css')
    {{-- <script src="//unpkg.com/alpinejs" defer></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <script src="{{ asset('js/activeFilter.js') }}"></script>

    <title>Laravel project</title>
</head>
<body class="bg-gray-100 h-screen flex flex-col">

    @auth
        <div class="flex flex-1 h-full">
            <!-- Боковое меню (фиксированное) -->
            @if (!isset($showHeader))
                <aside class="w-64 bg-blue-800 text-white h-full flex-shrink-0">
                    <x-side-menu class="col-span-2" />
                </aside>
            @endif

            <!-- Основной контент -->
            <main class="flex-1 p-8 overflow-auto">
                @if (!isset($showHeader))
                    <x-header class="mb-6" />
                @endif

                @yield('content')
            </main>
        </div>

    @else
        <div id="auth-container" class="flex flex-col items-center justify-center min-h-screen bg-gray-100 p-4">
            <h1 id="auth-title" class="text-2xl font-semibold mb-6">Login</h1>
            <div id="auth-content" class="w-full max-w-md bg-white shadow-md rounded-lg p-6">
                <x-login />
            </div>
            <button id="auth-toggle" onclick="toggleAuth()" class="mt-4 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">
                Registration
            </button>
        </div>

        {{-- <script>
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
        </script> --}}
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>
    @endauth

</body>
</html>
