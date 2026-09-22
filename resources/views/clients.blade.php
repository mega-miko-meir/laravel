@extends('layout')
@section('content')

{{-- Без JSON_UNESCAPED_UNICODE кириллица в @json раздувается ~2.3x (\uXXXX); HEX_* флаги (15) оставляем — атрибут в одинарных кавычках --}}
<div x-data='oneKey({!! json_encode($initialData, 15 | JSON_UNESCAPED_UNICODE) !!}, "{{ $type }}")'>

{{-- Тулбар --}}
<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px;margin-top:24px;">
    <h1 style="font-size:20px;font-weight:700;color:#111827;">
        База OneKey
        <span style="font-size:13px;font-weight:500;color:#9ca3af;margin-left:6px;" x-text="fmt(total)"></span>
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
                <input type="hidden" name="organization_type" :value="type">
                <template x-for="item in filters.specialties"><input type="hidden" name="specialty[]" :value="item"></template>
                <template x-for="item in filters.cities"><input type="hidden" name="city[]" :value="item"></template>
                <template x-for="item in filters.regions"><input type="hidden" name="brick_label[]" :value="item"></template>
                <input type="hidden" name="full_name" :value="name">

                <p style="font-size:13px;font-weight:600;color:#374151;margin-bottom:10px;">Выберите колонки:</p>
                <div style="display:flex;flex-direction:column;gap:6px;font-size:12px;color:#374151;">
                    <div x-show="!isPharmacy" style="display:flex;flex-direction:column;gap:6px;">
                        @foreach([
                            ['customer_id',          'OneKey ID',     true],
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
                    </div>
                    <div x-show="isPharmacy" x-cloak style="display:flex;flex-direction:column;gap:6px;">
                        @foreach([
                            ['organization_id',      'OneKey ID', true],
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
                    </div>
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
<div class="w-full" :class="{ 'onekey-loading': loading }">

    <div style="display:flex;flex-wrap:nowrap;align-items:flex-end;gap:12px;padding-bottom:4px;">

        {{-- Тип клиента --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Тип клиента</label>
            <div class="inline-flex rounded-lg bg-gray-100 p-1">
                @foreach(['Специалист', 'Аптека'] as $t)
                    <button type="button" @click="setType('{{ $t }}')"
                        :class="type === '{{ $t }}' ? 'bg-white shadow text-blue-600' : 'text-gray-500 hover:text-gray-700'"
                        class="px-3 py-1.5 text-sm font-medium rounded-md transition">
                        {{ $t }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Регион / Город / Специальность — мультиселекты, состояние в общем компоненте --}}
        @foreach([
            ['regions',     'Регион',        'border border-purple-300',                            'text-purple-600', false],
            ['cities',      'Город',         'bg-green-100 text-green-800 border border-green-300', 'text-green-600',  false],
            ['specialties', 'Специальность', 'bg-blue-100 text-blue-800 border border-blue-300',    'text-blue-600',   true],
        ] as [$key, $label, $chipClass, $xClass, $doctorsOnly])
        <div @if($doctorsOnly) x-show="!isPharmacy" @endif
             @click.outside="ms.{{ $key }}.open = false"
             class="w-64 relative font-sans">
            <label class="text-sm font-medium text-gray-700 block">{{ $label }}</label>
            <div class="rounded-lg min-h-[32px] flex flex-col gap-1 cursor-text focus-within:ring-2 focus-within:ring-blue-400"
                 @click="ms.{{ $key }}.open = true">
                <template x-for="item in filters.{{ $key }}" :key="item">
                    <div class="flex items-center justify-between {{ $chipClass }} px-2 py-1 rounded text-sm w-full">
                        <span class="truncate" x-text="item"></span>
                        <button type="button" @click.stop="toggle('{{ $key }}', item)"
                                class="{{ $xClass }} hover:text-white rounded-full w-4 h-4 flex items-center justify-center text-xs">✕</button>
                    </div>
                </template>
                <input type="text" x-model="ms.{{ $key }}.search" placeholder="Поиск..."
                       class="outline-none text-sm px-2 py-2 mt-1 border border-gray-200 rounded w-full">
            </div>
            <div x-show="ms.{{ $key }}.open" x-cloak class="absolute bg-white border border-gray-300 mt-1 w-full overflow-y-auto z-10 rounded shadow-lg" style="max-height:360px;">
                <template x-for="item in msFiltered('{{ $key }}')" :key="item">
                    <label class="flex items-center gap-2 p-2 hover:bg-gray-100 cursor-pointer text-xs">
                        <input type="checkbox" @change="toggle('{{ $key }}', item)" :checked="filters.{{ $key }}.includes(item)" class="rounded border-gray-300">
                        <span x-text="item" class="truncate text-xs"></span>
                    </label>
                </template>
            </div>
        </div>
        @endforeach

        {{-- Поиск по имени --}}
        <div class="flex flex-col gap-1" style="flex-shrink:0;width:200px;">
            <label class="text-sm font-medium text-gray-700" x-text="isPharmacy ? 'Название' : 'ФИО'"></label>
            <input type="text" x-model="name" @input.debounce.200ms="refilter()"
                   placeholder="Поиск..."
                   class="outline-none text-sm px-2 py-2 mt-1 border border-gray-200 rounded w-full">
        </div>

        <div style="flex-shrink:0;padding-bottom:1px;" x-show="hasFilters" x-cloak>
            <button type="button" @click="resetFilters()"
                    class="bg-white hover:bg-gray-50 text-gray-700 px-4 py-1.5 text-sm font-medium rounded-md border border-gray-300 transition-all duration-200"
                    style="white-space:nowrap;">
                Сбросить
            </button>
        </div>

    </div>
</div>

<div x-show="error" x-cloak x-text="error"
     style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:12px 16px;margin-top:12px;color:#991b1b;font-size:13px;"></div>

{{-- Найдено --}}
<div style="font-size:12px;color:#6b7280;margin-top:12px;margin-bottom:12px;">
    Найдено: <span style="font-weight:700;color:#2563eb;" x-text="fmt(total)"></span>
</div>

{{-- Таблица --}}
<div :class="{ 'onekey-loading': loading }"
     style="background:#fff;border:1px solid #f0f0f0;border-radius:12px;overflow:hidden;
            box-shadow:0 1px 3px rgba(0,0,0,.05);">
    <table style="width:100%;border-collapse:collapse;font-size:12px;">
        <thead>
            <tr style="background:#f9fafb;border-bottom:1px solid #f0f0f0;" x-show="!isPharmacy">
                @foreach(['OneKey ID', 'ФИО', 'Специальность', 'Место работы', 'Регион', 'Город'] as $col)
                    <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:600;
                               text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">{{ $col }}</th>
                @endforeach
            </tr>
            <tr style="background:#f9fafb;border-bottom:1px solid #f0f0f0;" x-show="isPharmacy" x-cloak>
                @foreach(['OneKey ID', 'Название', 'Адрес', 'Регион', 'Город'] as $col)
                    <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:600;
                               text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">{{ $col }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            <template x-if="pageRows.length === 0">
                <tr>
                    <td :colspan="isPharmacy ? 5 : 6"
                        style="text-align:center;padding:32px 14px;color:#9ca3af;font-size:13px;">
                        Нет данных
                    </td>
                </tr>
            </template>
            <template x-for="r in (isPharmacy ? [] : pageRows)" :key="r.i">
                <tr style="border-top:1px solid #f5f5f5;"
                    onmouseover="this.style.background='#fafafa';"
                    onmouseout="this.style.background='none';">
                    <td style="padding:9px 14px;color:#9ca3af;" x-text="r.id"></td>
                    <td style="padding:9px 14px;color:#111827;font-weight:500;" x-text="r.name"></td>
                    <td style="padding:9px 14px;color:#6b7280;" x-text="r.specialty"></td>
                    <td style="padding:9px 14px;color:#374151;" x-text="r.organization"></td>
                    <td style="padding:9px 14px;color:#6b7280;" x-text="r.province"></td>
                    <td style="padding:9px 14px;color:#374151;" x-text="r.town"></td>
                </tr>
            </template>
            <template x-for="r in (isPharmacy ? pageRows : [])" :key="r.i">
                <tr style="border-top:1px solid #f5f5f5;"
                    onmouseover="this.style.background='#fafafa';"
                    onmouseout="this.style.background='none';">
                    <td style="padding:9px 14px;color:#9ca3af;" x-text="r.id"></td>
                    <td style="padding:9px 14px;color:#111827;font-weight:500;" x-text="r.name"></td>
                    <td style="padding:9px 14px;color:#6b7280;" x-text="r.address"></td>
                    <td style="padding:9px 14px;color:#6b7280;" x-text="r.province"></td>
                    <td style="padding:9px 14px;color:#374151;" x-text="r.town"></td>
                </tr>
            </template>
        </tbody>
    </table>
</div>

{{-- Пагинация --}}
<div x-show="totalPages > 1" x-cloak style="margin-top:16px;display:flex;justify-content:center;align-items:center;gap:6px;">
    <button type="button" class="onekey-page-btn" @click="goTo(1)" :disabled="page === 1">«</button>
    <button type="button" class="onekey-page-btn" @click="goTo(page - 1)" :disabled="page === 1">‹</button>
    <span style="font-size:12px;color:#6b7280;padding:0 8px;">
        <span x-text="fmt(page)"></span> / <span x-text="fmt(totalPages)"></span>
    </span>
    <button type="button" class="onekey-page-btn" @click="goTo(page + 1)" :disabled="page === totalPages">›</button>
    <button type="button" class="onekey-page-btn" @click="goTo(totalPages)" :disabled="page === totalPages">»</button>
</div>

</div>

<style>
    .onekey-loading { opacity:.6; pointer-events:none; transition:opacity .15s; }
    .onekey-page-btn { border:1px solid #e5e7eb; background:#fff; color:#374151; border-radius:6px; padding:5px 11px; font-size:12px; cursor:pointer; }
    .onekey-page-btn[disabled] { opacity:.4; pointer-events:none; }
</style>

<script>
function oneKey(initialData, initialType) {
    // Десятки тысяч строк держим ВНЕ реактивного состояния Alpine (иначе каждая строка
    // оборачивается в Proxy) — реактивны только счётчики и текущая страница (<=50 строк).
    let allRows = [];
    let filteredRows = [];

    return {
        type: initialType,
        loading: false,
        error: initialData.error,
        total: 0,
        page: 1,
        perPage: 50,
        totalPages: 1,
        pageRows: [],
        name: '',
        filters: { regions: [], cities: [], specialties: [] },
        opts: { regions: [], cities: [], specialties: [] },
        ms: { regions: { open: false, search: '' }, cities: { open: false, search: '' }, specialties: { open: false, search: '' } },

        init() {
            this.load(initialData);
        },

        get isPharmacy() { return this.type === 'Аптека'; },
        get hasFilters() {
            return this.name !== '' || this.filters.regions.length > 0 || this.filters.cities.length > 0 || this.filters.specialties.length > 0;
        },

        load(payload) {
            const dict = payload.dictionaries || {};
            const cols = payload.cols;
            allRows = (payload.data || []).map((row, i) => {
                const o = { i };
                cols.forEach((c, ci) => { o[c] = dict[c] ? dict[c][row[ci]] : row[ci]; });
                o.q = (o.name || '').toLowerCase();
                return o;
            });
            const sorted = (arr) => (arr || []).filter(v => v !== '').sort((a, b) => a.localeCompare(b, 'ru'));
            this.opts = { regions: sorted(dict.province), cities: sorted(dict.town), specialties: sorted(dict.specialty) };
            this.error = payload.error || null;
            this.refilter();
        },

        refilter() {
            const q = this.name.trim().toLowerCase();
            const { regions, cities, specialties } = this.filters;
            filteredRows = allRows.filter(r =>
                (!q || r.q.includes(q))
                && (!regions.length || regions.includes(r.province))
                && (!cities.length || cities.includes(r.town))
                && (!specialties.length || specialties.includes(r.specialty))
            );
            this.total = filteredRows.length;
            this.totalPages = Math.max(1, Math.ceil(this.total / this.perPage));
            this.goTo(1);
        },

        goTo(p) {
            this.page = Math.min(Math.max(1, p), this.totalPages);
            const start = (this.page - 1) * this.perPage;
            this.pageRows = filteredRows.slice(start, start + this.perPage);
        },

        msFiltered(key) {
            const s = this.ms[key].search.trim().toLowerCase();
            const list = this.opts[key];
            return (s ? list.filter(i => i.toLowerCase().includes(s)) : list).slice(0, 300);
        },
        toggle(key, item) {
            const cur = this.filters[key];
            this.filters[key] = cur.includes(item) ? cur.filter(i => i !== item) : [...cur, item];
            this.refilter();
        },
        resetFilters() {
            this.name = '';
            this.filters = { regions: [], cities: [], specialties: [] };
            this.refilter();
        },

        fmt(n) { return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, ' '); },

        async setType(t) {
            if (t === this.type || this.loading) return;
            this.loading = true;
            try {
                const res = await fetch('{{ route('clients.data') }}?' + new URLSearchParams({ organization_type: t }), {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });
                if (!res.ok) throw new Error('http_' + res.status);
                const payload = await res.json();
                this.type = t;
                this.filters.specialties = [];
                this.load(payload);
                // регион/город переносим, только если такие значения есть в новом наборе
                this.filters.regions = this.filters.regions.filter(v => this.opts.regions.includes(v));
                this.filters.cities = this.filters.cities.filter(v => this.opts.cities.includes(v));
                this.refilter();

                const url = new URL(window.location.href);
                url.searchParams.set('organization_type', t);
                window.history.replaceState({}, '', url);
            } catch (e) {
                this.error = 'Не удалось получить данные из Nobel CRM. Попробуйте позже.';
            } finally {
                this.loading = false;
            }
        },
    };
}
</script>

@endsection
