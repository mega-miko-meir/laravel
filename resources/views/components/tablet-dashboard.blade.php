{{-- resources/views/components/tablet-dashboard.blade.php --}}
@php
    $totalAllCount = \App\Models\Tablet::where('status', 'active')->count();
    $freeCount     = \App\Models\Tablet::free()->count();
    $newCount      = \App\Models\Tablet::where('status', 'new')->count();
    $damagedCount  = \App\Models\Tablet::whereIn('status', ['damaged', 'lost'])->count();
    $adminCount    = \App\Models\Tablet::where('status', 'admin')->count();
@endphp

<div class="flex flex-wrap sm:flex-nowrap gap-3 mb-5">

    <a href="{{ route('tablets.search', ['search' => 'active']) }}"
       class="flex-1 min-w-[110px] bg-white rounded-lg border border-gray-200 px-4 py-2 hover:border-gray-400 transition">
        <div class="text-xs text-gray-400 uppercase tracking-wide mb-1">Исправных</div>
        <div class="text-2xl font-bold text-gray-800">{{ $totalAllCount }}</div>
    </a>

    {{-- <a href="{{ route('tablets.search', ['search' => 'free']) }}"
       class="flex-1 min-w-[110px] bg-white rounded-lg border border-gray-200 px-4 py-2 hover:border-green-400 transition">
        <div class="text-xs text-gray-400 uppercase tracking-wide mb-1">Свободных</div>
        <div class="text-2xl font-bold text-green-600">{{ $freeCount }}</div>
    </a> --}}

    <a href="{{ route('tablets.search', ['search' => 'admin']) }}"
       class="flex-1 min-w-[110px] bg-white rounded-lg border border-gray-200 px-4 py-2 hover:border-blue-400 transition">
        <div class="text-xs text-gray-400 uppercase tracking-wide mb-1">Админ</div>
        <div class="text-2xl font-bold text-blue-600">{{ $adminCount }}</div>
    </a>

    <a href="{{ route('tablets.search', ['search' => 'new']) }}"
       class="flex-1 min-w-[110px] bg-white rounded-lg border border-gray-200 px-4 py-2 hover:border-purple-400 transition">
        <div class="text-xs text-gray-400 uppercase tracking-wide mb-1">Новых</div>
        <div class="text-2xl font-bold text-green-600">{{ $newCount }}</div>
    </a>

    <a href="{{ route('tablets.search', ['search' => 'damaged']) }}"
       class="flex-1 min-w-[110px] bg-white rounded-lg border border-gray-200 px-4 py-2 hover:border-red-400 transition">
        <div class="text-xs text-gray-400 uppercase tracking-wide mb-1">Повреждён/Утерян</div>
        <div class="text-2xl font-bold text-red-500">{{ $damagedCount }}</div>
    </a>

</div>
