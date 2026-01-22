<div {{ $attributes->merge(['class' => 'inline-block']) }}>
    <form action="/logout" method="POST" class="m-0">
        @csrf
        <button type="submit" class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition-all duration-300 rounded-lg bg-white/10 hover:bg-red-500/20 hover:text-red-200 border border-white/20 hover:border-red-500/50 shadow-sm">
            <!-- Иконка выхода (опционально, но в 2026 это стандарт) -->
            <svg xmlns="http://www.w3.org" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            <span>Log out</span>
        </button>
    </form>
</div>

