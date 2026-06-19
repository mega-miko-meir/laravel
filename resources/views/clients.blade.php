@extends('layout')
@section('content')

{{-- Тулбар --}}
<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px;margin-top:24px;">
    <h1 style="font-size:20px;font-weight:700;color:#111827;">
        База OneKey
        <span style="font-size:13px;font-weight:500;color:#9ca3af;margin-left:6px;">{{ $clients->total() }}</span>
    </h1>

    {{-- Выгрузить --}}
    <div x-data="{ open: false }" style="position:relative;">
        <button @click="open = !open"
                style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;
                       background:#fff;color:#374151;border:1px solid #e5e7eb;border-radius:8px;
                       font-size:13px;font-weight:500;cursor:pointer;"
                onmouseover="this.style.background='#f9fafb';"
                onmouseout="this.style.background='#fff';">
            <svg style="width:14px;height:14px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Выгрузить
            <svg style="width:13px;height:13px;color:#9ca3af;" :class="{'rotate-180':open}"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open" @click.away="open=false" x-cloak
             style="position:absolute;right:0;top:calc(100% + 6px);width:260px;
                    background:#fff;border:1px solid #e5e7eb;border-radius:10px;
                    box-shadow:0 4px 20px rgba(0,0,0,.1);z-index:50;padding:16px;">
            <form action="{{ route('export.onekey') }}" method="POST">
                @csrf
                <input type="hidden" name="organization_type" value="{{ request('organization_type') }}">
                @foreach(request('specialty', []) as $item)
                    <input type="hidden" name="specialty[]" value="{{ $item }}">
                @endforeach
                @foreach(request('city', []) as $item)
                    <input type="hidden" name="city[]" value="{{ $item }}">
                @endforeach
                @foreach(request('brick_label', []) as $item)
                    <input type="hidden" name="brick_label[]" value="{{ $item }}">
                @endforeach
                <input type="hidden" name="full_name" value="{{ request('full_name') }}">

                <p style="font-size:13px;font-weight:600;color:#374151;margin-bottom:10px;">Выберите колонки:</p>
                <div style="display:flex;flex-direction:column;gap:6px;font-size:12px;color:#374151;">
                    @if(!$isPharmacy)
                        @foreach([
                            ['customer',             'ФИО',           true],
                            ['customer_spesiality',  'Специальность', true],
                            ['organization',         'Место работы',  true],
                            ['organization_address', 'Адрес',         false],
                            ['town',                 'Город',         true],
                            ['province',             'Регион',        true],
                        ] as [$val, $lbl, $chk])
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                                <input type="checkbox" name="columns[]" value="{{ $val }}" {{ $chk ? 'checked' : '' }}
                                       style="width:14px;height:14px;accent-color:#2563eb;">
                                {{ $lbl }}
                            </label>
                        @endforeach
                    @else
                        @foreach([
                            ['organization',         'Название',  true],
                            ['organization_address', 'Адрес',     true],
                            ['town',                 'Город',     true],
                            ['province',             'Регион',    true],
                        ] as [$val, $lbl, $chk])
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                                <input type="checkbox" name="columns[]" value="{{ $val }}" {{ $chk ? 'checked' : '' }}
                                       style="width:14px;height:14px;accent-color:#2563eb;">
                                {{ $lbl }}
                            </label>
                        @endforeach
                    @endif
                </div>
                <div style="display:flex;justify-content:flex-end;margin-top:12px;">
                    <button type="submit"
                            style="padding:6px 16px;background:#2563eb;color:#fff;border:none;
                                   border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;"
                            onmouseover="this.style.background='#1d4ed8';"
                            onmouseout="this.style.background='#2563eb';">
                        Скачать
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Фильтры --}}
<form method="GET" class="w-full">

    <div style="display:flex;flex-wrap:nowrap;align-items:flex-end;gap:12px;padding-bottom:4px;">

        {{-- Тип клиента --}}
        <div x-data="{ selected: '{{ request('organization_type', 'Специалист') }}' }" class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Тип клиента</label>
            <div class="inline-flex rounded-lg bg-gray-100 p-1">
                @foreach(['Специалист', 'Аптека'] as $type)
                    <button type="button"
                        @click="selected = '{{ $type }}'; $nextTick(() => $el.closest('form').submit())"
                        :class="selected === '{{ $type }}' ? 'bg-white shadow text-blue-600' : 'text-gray-500 hover:text-gray-700'"
                        class="px-3 py-1.5 text-sm font-medium rounded-md transition">
                        {{ $type }}
                    </button>
                @endforeach
            </div>
            <input type="hidden" name="organization_type" :value="selected">
        </div>

        {{-- Регион --}}
        <div x-data="filterComponent('brick_label', {{ json_encode($regions) }}, {{ json_encode(request('brick_label', [])) }})"
            @click.outside="open = false"
            class="w-64 relative font-sans">
            <label class="text-sm font-medium text-gray-700 block">Регион</label>
            <div class="rounded-lg min-h-[32px] flex flex-col gap-1 cursor-text focus-within:ring-2 focus-within:ring-blue-400"
                @click="open = true">
                <template x-for="item in selected" :key="item">
                    <div class="flex items-center justify-between border border-purple-300 px-2 py-1 rounded text-sm w-full">
                        <span class="truncate" x-text="item"></span>
                        <button type="button" @click.stop="remove(item)"
                                class="text-purple-600 hover:text-white rounded-full w-4 h-4 flex items-center justify-center text-xs">✕</button>
                    </div>
                </template>
                <input type="text" x-model="search" @input="filter()" placeholder="Поиск..."
                    class="outline-none text-sm px-2 py-2 mt-1 border border-gray-200 rounded w-full">
            </div>
            <div x-show="open" class="absolute bg-white border border-gray-300 mt-1 w-full overflow-y-auto z-10 rounded shadow-lg" style="max-height:360px;">
                <template x-for="item in filtered" :key="item">
                    <label class="flex items-center gap-2 p-2 hover:bg-gray-100 cursor-pointer text-xs">
                        <input type="checkbox" :value="item" @change="toggle(item)" :checked="selected.includes(item)" class="rounded border-gray-300">
                        <span x-text="item" class="truncate text-xs"></span>
                    </label>
                </template>
            </div>
            <template x-for="item in selected">
                <input type="hidden" name="brick_label[]" :value="item">
            </template>
        </div>

        {{-- Город --}}
        <div x-data="filterComponent('city', {{ json_encode($cities) }}, {{ json_encode(request('city', [])) }})"
            @click.outside="open = false"
            class="w-64 relative font-sans">
            <label class="text-sm font-medium text-gray-700 block">Город</label>
            <div class="rounded-lg min-h-[32px] flex flex-col gap-1 cursor-text focus-within:ring-2 focus-within:ring-blue-400"
                @click="open = true">
                <template x-for="item in selected" :key="item">
                    <div class="flex items-center justify-between bg-green-100 text-green-800 border border-green-300 px-2 py-1 rounded text-sm w-full">
                        <span class="truncate" x-text="item"></span>
                        <button type="button" @click.stop="remove(item)"
                                class="text-green-600 hover:text-white rounded-full w-4 h-4 flex items-center justify-center text-xs">✕</button>
                    </div>
                </template>
                <input type="text" x-model="search" @input="filter()" placeholder="Поиск..."
                    class="outline-none text-sm px-2 py-2 mt-1 border border-gray-200 rounded w-full">
            </div>
            <div x-show="open" class="absolute bg-white border border-gray-300 mt-1 w-full overflow-y-auto z-10 rounded shadow-lg" style="max-height:360px;">
                <template x-for="item in filtered" :key="item">
                    <label class="flex items-center gap-2 p-2 hover:bg-gray-100 cursor-pointer text-xs">
                        <input type="checkbox" :value="item" @change="toggle(item)" :checked="selected.includes(item)" class="rounded border-gray-300">
                        <span x-text="item" class="truncate text-xs"></span>
                    </label>
                </template>
            </div>
            <template x-for="item in selected">
                <input type="hidden" name="city[]" :value="item">
            </template>
        </div>

        {{-- Специальность (только для врачей) --}}
        @if(!$isPharmacy)
        <div x-data="filterComponent('specialty', {{ json_encode($specialties) }}, {{ json_encode(request('specialty', [])) }})"
             @click.outside="open = false"
             class="w-64 relative font-sans">
            <label class="text-sm font-medium text-gray-700 block">Специальность</label>
            <div class="rounded-lg min-h-[32px] flex flex-col gap-1 cursor-text focus-within:ring-2 focus-within:ring-blue-400"
                 @click="open = true">
                <template x-for="item in selected" :key="item">
                    <div class="flex items-center justify-between bg-blue-100 text-blue-800 border border-blue-300 px-2 py-1 rounded text-sm w-full">
                        <span class="truncate" x-text="item"></span>
                        <button type="button" @click.stop="remove(item)"
                                class="text-blue-600 hover:text-white rounded-full w-4 h-4 flex items-center justify-center text-xs">✕</button>
                    </div>
                </template>
                <input type="text" x-model="search" @input="filter()" placeholder="Поиск..."
                       class="outline-none text-sm px-2 py-2 mt-1 border border-gray-200 rounded">
            </div>
            <div x-show="open" class="absolute bg-white border border-gray-300 mt-1 w-full overflow-y-auto z-10 rounded shadow-lg" style="max-height:360px;">
                <template x-for="item in filtered" :key="item">
                    <label class="flex items-center gap-2 p-2 hover:bg-gray-100 cursor-pointer">
                        <input type="checkbox" :value="item" @change="toggle(item)" :checked="selected.includes(item)" class="rounded border-gray-300">
                        <span x-text="item" class="text-sm"></span>
                    </label>
                </template>
            </div>
            <template x-for="item in selected">
                <input type="hidden" name="specialty[]" :value="item">
            </template>
        </div>
        @endif

        {{-- Поиск по имени --}}
        <div class="flex flex-col gap-1" style="flex-shrink:0;width:200px;">
            <label class="text-sm font-medium text-gray-700">{{ $isPharmacy ? 'Название' : 'ФИО' }}</label>
            <input type="text" name="full_name" value="{{ request('full_name') }}"
                   placeholder="Поиск..."
                   class="outline-none text-sm px-2 py-2 mt-1 border border-gray-200 rounded w-full">
        </div>

        <div style="flex-shrink:0;padding-bottom:1px;">
            <button type="submit"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-1.5 text-sm font-medium rounded-md shadow-sm transition-all duration-200"
                    style="white-space:nowrap;">
                Найти
            </button>
        </div>

    </div>
</form>

<script>
function filterComponent(name, options, selectedInit) {
    return {
        open: false, search: '', options: options, filtered: options, selected: selectedInit,
        filter() { this.filtered = this.options.filter(i => i.toLowerCase().includes(this.search.toLowerCase())); },
        toggle(item) { this.selected.includes(item) ? this.selected = this.selected.filter(i => i !== item) : this.selected.push(item); },
        remove(item) { this.selected = this.selected.filter(i => i !== item); },
    }
}
</script>

{{-- Найдено --}}
<div style="font-size:12px;color:#6b7280;margin-top:12px;margin-bottom:12px;">
    Найдено: <span style="font-weight:700;color:#2563eb;">{{ $clients->total() }}</span>
</div>

{{-- Таблица --}}
<div style="background:#fff;border:1px solid #f0f0f0;border-radius:12px;overflow:hidden;
            box-shadow:0 1px 3px rgba(0,0,0,.05);">
    <table style="width:100%;border-collapse:collapse;font-size:12px;">
        <thead>
            <tr style="background:#f9fafb;border-bottom:1px solid #f0f0f0;">
                @if(!$isPharmacy)
                    @foreach(['ФИО', 'Специальность', 'Место работы', 'Регион', 'Город'] as $col)
                        <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:600;
                                   text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">{{ $col }}</th>
                    @endforeach
                @else
                    @foreach(['Название', 'Адрес', 'Регион', 'Город'] as $col)
                        <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:600;
                                   text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">{{ $col }}</th>
                    @endforeach
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($clients as $client)
                <tr style="border-top:1px solid #f5f5f5;"
                    onmouseover="this.style.background='#fafafa';"
                    onmouseout="this.style.background='none';">
                    @if(!$isPharmacy)
                        <td style="padding:9px 14px;color:#111827;font-weight:500;">{{ $client->customer }}</td>
                        <td style="padding:9px 14px;color:#6b7280;">{{ $client->customer_spesiality }}</td>
                        <td style="padding:9px 14px;color:#374151;">{{ $client->organization }}</td>
                        <td style="padding:9px 14px;color:#6b7280;">{{ $client->province }}</td>
                        <td style="padding:9px 14px;color:#374151;">{{ $client->town }}</td>
                    @else
                        <td style="padding:9px 14px;color:#111827;font-weight:500;">{{ $client->organization }}</td>
                        <td style="padding:9px 14px;color:#6b7280;">{{ $client->organization_address }}</td>
                        <td style="padding:9px 14px;color:#6b7280;">{{ $client->province }}</td>
                        <td style="padding:9px 14px;color:#374151;">{{ $client->town }}</td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $isPharmacy ? 4 : 5 }}"
                        style="text-align:center;padding:32px 14px;color:#9ca3af;font-size:13px;">
                        Нет данных
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:16px;">
    {{ $clients->appends(request()->query())->links() }}
</div>

@endsection
