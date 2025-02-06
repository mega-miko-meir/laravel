{{-- <body class="font-arial m-0">

    <div class="topnav fixed top-0 left-0 w-full bg-[#2471a3] z-10 shadow-lg flex items-center justify-between px-6 py-3 h-16">
        <!-- Логотип -->
        <a href="/" class="flex items-center space-x-3">
            <img src="/images/nobel-logo.png" alt="Logo" class="h-12 w-auto">
        </a>

        <!-- Информация о пользователе -->
        <div class="user-info flex items-center space-x-6">
            @if (Auth::user())
                <span class="username text-white text-lg font-semibold" id="username">
                    {{ Auth::user()->full_name ?? "Guest" }}
                </span>
                <x-logout-button />
            @else
                <h1 class="text-xl md:text-2xl font-bold text-white leading-snug tracking-wide text-center">
                    Welcome to the <span class="text-yellow-400">Nobel</span> Employee Management System
                </h1>
            @endif
        </div>
    </div>
 --}}


 <div class="topnav fixed top-0 left-0 w-full bg-gradient-to-r from-blue-700 to-blue-500 z-10 shadow-md flex items-center justify-between px-8 py-4 h-16">
    <!-- Логотип -->
    <a href="/" class="flex items-center space-x-3">
        <img src="/images/nobel-logo.png" alt="Nobel Logo" class="h-10 md:h-12 w-auto">
    </a>

    <!-- Информация о пользователе -->
    <div class="user-info flex items-center space-x-6 text-white">
        @if (Auth::user())
            <span class="text-lg font-semibold">
                {{ Auth::user()->full_name ?? "Guest" }}
            </span>
            <x-logout-button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition duration-300"/>
        @else
            <h1 class="text-lg md:text-xl font-bold leading-snug tracking-wide text-center">
                Welcome to the <span class="text-yellow-300">Nobel</span> Employee Management System
            </h1>
        @endif
    </div>
</div>
