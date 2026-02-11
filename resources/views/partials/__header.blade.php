<div class="topnav fixed top-0 left-0 w-full bg-gradient-to-r from-blue-700 to-blue-500 z-10 shadow-md flex items-center justify-between px-8 py-4 h-16">
    <!-- Логотип (Левая часть) -->
    <div class="flex-shrink-0">
        <a href="/" class="flex items-center group transition-transform hover:scale-105">
            <img src="/images/nobel-logo.png" alt="Nobel Logo" class="h-10 md:h-12 w-auto">
        </a>
    </div>
    <!-- Правая часть (Цитата + Погода + Пользователь) -->
    <div class="flex items-center space-x-6 text-white">
        <!-- Блок нотификации -->
        @php
            $unreadCount = auth()->user()->unreadNotifications()->count();
        @endphp

        @can('admin')
            <div class="relative inline-flex items-center">
                <a href="{{ route('admin.notifications') }}" class="relative inline-block text-xl">
                    🔔
                    @if($unreadCount > 0)
                        <span class="absolute top-0 right-0 transform translate-x-1/2 -translate-y-1/2
                                    bg-red-600 text-white text-[10px] font-bold
                                    w-5 h-5 flex items-center justify-center rounded-full
                                    border-2 shadow-sm" style="background-color: red">
                            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                        </span>
                    @endif
                </a>
            </div>

        @endcan

        {{-- <button
            @click="feedbackOpen = true"
            class="relative text-gray-600 text-2xl hover:text-blue-600 mr-16"
            title="Обратная связь">
            💬<span class="text-sm">Обратная связь</span>
        </button> --}}

        <!-- Блок погоды -->
        <div class="flex items-center gap-6">
            @foreach($weatherData as $city => $weather)
                @if($weather)
                    <div class="flex items-center gap-2 text-white">
                        <img src="https://openweathermap.org/img/wn/{{ $weather['icon'] }}@2x.png"
                            alt="{{ $city }} weather"
                            class="w-10 h-10">
                        <div class="flex flex-col text-xs leading-tight">
                            <span class="font-semibold opacity-80">{{ $city }}</span>
                            <span class="font-bold text-sm">{{ round($weather['temp']) }}°C</span>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <!-- Разделитель между погодой и пользователем -->
        <div class="h-8 w-px bg-white/20 hidden sm:block"></div>

        <!-- Блок пользователя -->
        @if (Auth::user())
            <div class="flex flex-col text-right hidden sm:flex">
                <span class="text-xs text-blue-200 uppercase tracking-wider font-medium">Welcome back,</span>
                <span class="text-sm md:text-base font-bold">
                    {{ Auth::user()->first_name . ' ' . Auth::user()->last_name }}
                </span>
            </div>

            <div class="h-8 w-px bg-white/20 hidden sm:block"></div> <!-- Еще один разделитель перед кнопкой -->
            <x-logout-button class="transition-all duration-300" />
        @else
            <h1 class="text-sm md:text-lg font-medium leading-tight tracking-tight text-right">
                Welcome to the <span class="text-yellow-300 font-bold">Nobel</span>
                <span class="opacity-80">Management System</span>
            </h1>
        @endif
    </div>
</div>
