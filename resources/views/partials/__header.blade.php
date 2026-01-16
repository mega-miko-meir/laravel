<div class="topnav fixed top-0 left-0 w-full bg-gradient-to-r from-blue-700 to-blue-500 z-10 shadow-md flex items-center justify-between px-8 py-4 h-16">
<!-- Логотип (Левая часть) -->
    <div class="flex-shrink-0">
        <a href="/" class="flex items-center group transition-transform hover:scale-105">
            <img src="/images/nobel-logo.png" alt="Nobel Logo" class="h-10 md:h-12 w-auto">
        </a>
    </div>

    <!-- Центральная/Правая часть (Информация о пользователе) -->
    <div class="flex items-center space-x-6 text-white">
        @if (Auth::user())
            <div class="flex flex-col text-right hidden sm:flex">
                <span class="text-xs text-blue-200 uppercase tracking-wider font-medium">Welcome back,</span>
                <span class="text-sm md:text-base font-bold">
                    {{ Auth::user()->first_name . ' ' . Auth::user()->last_name }}
                </span>
            </div>

            <div class="h-8 w-px bg-white/20 hidden sm:block"></div> <!-- Разделитель -->

            <x-logout-button class="bg-white/10 hover:bg-red-500 text-white text-sm font-semibold px-5 py-2.5 rounded-xl border border-white/20 transition-all duration-300 shadow-lg hover:shadow-red-500/30"/>
        @else
            <h1 class="text-sm md:text-lg font-medium leading-tight tracking-tight text-right">
                Welcome to the <span class="text-yellow-300 font-bold">Nobel</span>
                <span class="opacity-80">Management System</span>
            </h1>
        @endif
    </div>
</div>

{{-- <div class="h-20"></div> --}}


{{-- <div class="fixed top-0 left-0 w-full z-50 shadow-sm border-b border-white/20 backdrop-blur-md bg-blue-700/90 h-20">
    <div class="max-w-7xl mx-auto h-full flex items-center justify-between px-6 lg:px-8">

        <!-- Логотип (Левая часть) -->
        <div class="flex-shrink-0">
            <a href="/" class="flex items-center group transition-transform hover:scale-105">
                <img src="/images/nobel-logo.png" alt="Nobel Logo" class="h-10 md:h-12 w-auto">
            </a>
        </div>

        <!-- Центральная/Правая часть (Информация о пользователе) -->
        <div class="flex items-center space-x-6 text-white">
            @if (Auth::user())
                <div class="flex flex-col text-right hidden sm:flex">
                    <span class="text-xs text-blue-200 uppercase tracking-wider font-medium">Welcome back,</span>
                    <span class="text-sm md:text-base font-bold">
                        {{ Auth::user()->first_name . ' ' . Auth::user()->last_name }}
                    </span>
                </div>

                <div class="h-8 w-px bg-white/20 hidden sm:block"></div> <!-- Разделитель -->

                <x-logout-button class="bg-white/10 hover:bg-red-500 text-white text-sm font-semibold px-5 py-2.5 rounded-xl border border-white/20 transition-all duration-300 shadow-lg hover:shadow-red-500/30"/>
            @else
                <h1 class="text-sm md:text-lg font-medium leading-tight tracking-tight text-right">
                    Welcome to the <span class="text-yellow-300 font-bold">Nobel</span>
                    <span class="opacity-80">Management System</span>
                </h1>
            @endif
        </div>

    </div>
</div>

<!-- Отступ под хедером, чтобы контент не заезжал под него -->
<div class="h-20"></div> --}}
