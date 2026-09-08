@props(['title', 'description', 'count'])

<div x-data="{ open: {{ $count > 0 ? 'true' : 'false' }} }"
     style="background:#fff;border:1px solid #f0f0f0;border-radius:12px;margin-bottom:12px;
            overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.05);">

    <button @click="open = !open"
            style="width:100%;display:flex;align-items:center;justify-content:space-between;
                   padding:14px 18px;background:none;border:none;cursor:pointer;text-align:left;">
        <div>
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="font-size:14px;font-weight:600;color:#1f2937;">{{ $title }}</span>
                <span style="display:inline-flex;align-items:center;padding:2px 9px;border-radius:9999px;
                             font-size:12px;font-weight:700;
                             {{ $count > 0 ? 'background:#fee2e2;color:#b91c1c;' : 'background:#dcfce7;color:#15803d;' }}">
                    {{ $count }}
                </span>
            </div>
            @if($description)
                <p style="font-size:12px;color:#9ca3af;margin-top:3px;">{{ $description }}</p>
            @endif
        </div>
        <svg :class="{'rotate-180':open}" style="width:14px;height:14px;color:#9ca3af;transition:transform .2s;flex-shrink:0;"
             fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    @if($count > 0)
        <div x-show="open" x-cloak style="border-top:1px solid #f5f5f5;">
            {{ $slot }}
        </div>
    @endif
</div>
