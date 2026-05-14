@php
    $messages = [];
    if (session('success')) $messages[] = ['type' => 'success', 'text' => session('success')];
    if (session('error'))   $messages[] = ['type' => 'error',   'text' => session('error')];
@endphp

@foreach($messages as $msg)
    @php
        $isSuccess = $msg['type'] === 'success';
        $bg     = $isSuccess ? '#f0fdf4' : '#fef2f2';
        $border = $isSuccess ? '#86efac' : '#fca5a5';
        $icon   = $isSuccess ? '#16a34a' : '#dc2626';
        $text   = $isSuccess ? '#15803d' : '#b91c1c';
        $iconPath = $isSuccess
            ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
            : 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z';
    @endphp

    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 5000)"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-[-8px]"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-cloak
        style="position:fixed;top:72px;right:20px;z-index:9999;
               display:flex;align-items:flex-start;gap:10px;
               padding:12px 14px;min-width:260px;max-width:380px;
               background:{{ $bg }};border:1px solid {{ $border }};
               border-radius:10px;box-shadow:0 4px 16px rgba(0,0,0,.08);"
    >
        {{-- Иконка --}}
        <svg style="width:18px;height:18px;flex-shrink:0;margin-top:1px;color:{{ $icon }};"
             fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"/>
        </svg>

        {{-- Текст --}}
        <p style="font-size:13px;font-weight:500;color:{{ $text }};line-height:1.4;flex:1;">
            {{ $msg['text'] }}
        </p>

        {{-- Закрыть --}}
        <button @click="show = false"
                style="flex-shrink:0;width:18px;height:18px;display:flex;align-items:center;
                       justify-content:center;background:none;border:none;cursor:pointer;
                       color:{{ $icon }};opacity:.6;margin-top:1px;"
                onmouseover="this.style.opacity='1';"
                onmouseout="this.style.opacity='.6';">
            <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
@endforeach
