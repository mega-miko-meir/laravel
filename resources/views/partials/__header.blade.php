{{-- <body class="font-arial m-0"> --}}

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



{{-- </body> --}}
