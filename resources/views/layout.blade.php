<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite('resources/css/app.css')

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="{{ asset('js/activeFilter.js') }}"></script>

    <title>Laravel project</title>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>

<body
    class="bg-gray-100 h-screen flex flex-col"
    x-data="{ feedbackOpen: false }"
>

@auth
    <div class="flex flex-1 h-full">

        <!-- Боковое меню -->
        @if (!isset($showHeader))
            <aside class="w-64 text-white h-full flex-shrink-0" style="background:#1e3a8a;">
                <x-side-menu class="col-span-2" />
            </aside>
        @endif

        <!-- Основной контент -->
        <main class="flex-1 p-8 overflow-auto">
            <br>

            @if (!isset($showHeader))
                <x-header class="mb-6" />
            @endif

            @yield('content')

        </main>
    </div>
    <x-feedback-form />
    <x-flash-message />

@else

    <div style="min-height:100vh; display:flex; align-items:center; justify-content:center;
                background:linear-gradient(135deg, #0f2460 0%, #1e3a8a 45%, #1d4ed8 100%);
                padding:16px;">

        <div style="width:100%; max-width:380px;">

            {{-- Карточка --}}
            <div style="background:#fff; border-radius:20px; overflow:hidden;
                        box-shadow:0 25px 60px rgba(0,0,0,0.35);">

                {{-- Шапка --}}
                <div style="background:linear-gradient(135deg, #1e3a8a, #2563eb);
                            padding:36px 32px 28px; text-align:center;">
                    <img src="/images/nobel-logo.png" alt="Nobel"
                         style="height:38px; width:auto; margin:0 auto 20px; display:block;">
                    <p style="color:rgba(255,255,255,0.75); font-size:13px; margin:0; letter-spacing:0.02em;">
                        Система управления персоналом
                    </p>
                </div>

                {{-- Форма --}}
                <div style="padding:32px;">
                    <x-login />
                </div>
            </div>

            {{-- Подпись --}}
            <p style="text-align:center; color:rgba(255,255,255,0.35); font-size:11px; margin-top:20px;">
                © {{ date('Y') }} Nobel · Все права защищены
            </p>
        </div>
    </div>

@endauth

</body>
</html>
