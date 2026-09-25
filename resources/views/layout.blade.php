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
    class="bg-gray-100"
    @auth style="height:100vh;display:flex;flex-direction:column;overflow:hidden;" @endauth
    x-data="{ feedbackOpen: false }"
>

@auth
    {{-- Шапка — обычный flex-элемент на всю ширину (не fixed): область под ней
         (меню + контент) занимает остаток высоты экрана, и прокрутка контента
         начинается ровно под шапкой, а не уходит под неё. --}}
    @if (!isset($showHeader))
        <x-header />
    @endif

    <div style="flex:1 1 0%;min-height:0;display:flex;">

        <!-- Боковое меню (прокручивается само, если пункты не влезают) -->
        @if (!isset($showHeader))
            <aside class="text-white" style="width:16rem;flex:0 0 16rem;display:flex;flex-direction:column;background:#1e3a8a;">
                <x-side-menu />
            </aside>
        @endif

        <!-- Основной контент — единственная зона прокрутки страницы -->
        <main class="p-8" style="flex:1 1 0%;min-width:0;overflow:auto;">
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
