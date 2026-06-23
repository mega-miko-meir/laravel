@extends('layout')

@section('content')

<style>
:root {
    --kmp-bg:     #f1f5f9;
    --kmp-card:   #ffffff;
    --kmp-border: #e2e8f0;
    --kmp-t1:     #0f172a;
    --kmp-t2:     #475569;
    --kmp-t3:     #94a3b8;
    --kmp-accent: #0ea5e9;
}
.kmp-card { background:var(--kmp-card);border-radius:12px;border:1px solid var(--kmp-border);padding:20px; }
.kmp-label { font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--kmp-t3); }
.kmp-val   { font-size:26px;font-weight:700;color:var(--kmp-t1);line-height:1.1;margin-top:4px; }
.kmp-sub   { font-size:12px;color:var(--kmp-t3);margin-top:2px; }
.kmp-sel   { border:1px solid var(--kmp-border);border-radius:6px;padding:6px 10px;font-size:12px;color:var(--kmp-t1);background:#fff;outline:none; }
.kmp-th    { padding:10px 14px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--kmp-t3);white-space:nowrap; }
.kmp-td    { padding:10px 14px;font-size:13px;color:var(--kmp-t1);border-bottom:1px solid var(--kmp-border); }
.kmp-sort  { text-decoration:none;color:inherit; }
.kmp-sort:hover { text-decoration:underline; }

.kms-wrap { position:relative; }
.kms-display {
    background:#fff;border:1px solid var(--kmp-border);border-radius:6px;
    padding:0 8px 0 10px;font-size:12px;color:var(--kmp-t1);cursor:pointer;
    display:flex;align-items:center;justify-content:space-between;gap:6px;
    height:32px;white-space:nowrap;min-width:130px;
}
.kms-display:focus { outline:none;border-color:#0ea5e9; }
.kms-val { color:var(--kmp-t2);overflow:hidden;text-overflow:ellipsis; }
.kms-val.active { color:#0ea5e9;font-weight:600; }
.kms-dropdown {
    position:absolute;top:calc(100% + 4px);left:0;z-index:300;
    background:#fff;border:1px solid var(--kmp-border);border-radius:8px;
    box-shadow:0 4px 16px rgba(0,0,0,.1);max-height:220px;overflow-y:auto;min-width:200px;
}
.kms-option { display:flex;align-items:center;gap:8px;padding:7px 10px;cursor:pointer;font-size:12px;color:var(--kmp-t1); }
.kms-option:hover { background:#f8fafc; }
.kms-option input[type=checkbox] { accent-color:#0ea5e9;width:13px;height:13px; }
</style>

<div style="max-width:1300px;margin:0 auto;">

    {{-- Header --}}
    <div x-data="{ exportOpen: false }" style="margin-bottom:24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <h1 style="font-size:22px;font-weight:700;color:#1e3a8a;margin:0;">KMP — Продажи</h1>
                <p style="font-size:13px;color:#64748b;margin:4px 0 0;">Данные из Nobel KMP (аптечные продажи МП)</p>
            </div>
            <button @click="exportOpen = !exportOpen"
                style="display:flex;align-items:center;gap:8px;background:#16a34a;color:#fff;border:none;border-radius:8px;padding:9px 18px;font-size:13px;font-weight:600;cursor:pointer;">
                <svg style="width:15px;height:15px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Выгрузить отчёт
            </button>
        </div>

        {{-- Export panel --}}
        <div x-show="exportOpen" x-cloak x-transition
             style="margin-top:12px;background:#f0fdf4;border:1px solid #86efac;border-radius:12px;padding:16px;">
            <form method="POST" action="{{ route('kmp.export') }}" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
                @csrf
                <div style="display:flex;flex-direction:column;gap:4px;">
                    <label style="font-size:11px;font-weight:600;color:#15803d;">Дата от</label>
                    <input type="date" name="date_from"
                           value="{{ request('date_from') }}"
                           class="kmp-sel" style="border-color:#86efac;">
                </div>
                <div style="display:flex;flex-direction:column;gap:4px;">
                    <label style="font-size:11px;font-weight:600;color:#15803d;">Дата до</label>
                    <input type="date" name="date_to"
                           value="{{ request('date_to') }}"
                           class="kmp-sel" style="border-color:#86efac;">
                </div>
                <button type="submit"
                    style="background:#16a34a;color:#fff;border:none;border-radius:6px;padding:7px 18px;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;">
                    <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Скачать
                </button>
                <p style="font-size:11px;color:#64748b;margin:0;align-self:center;">
                    CSV, все колонки, только «Доставлено»
                </p>
            </form>
        </div>
    </div>

    {{-- Errors --}}
    @if($errors->has('nobel_db'))
        <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:12px 16px;margin-bottom:20px;color:#991b1b;font-size:13px;">
            {{ $errors->first('nobel_db') }}
        </div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ route('kmp.index') }}" style="margin-bottom:20px;">
        <div style="background:#fff;border-radius:12px;border:1px solid var(--kmp-border);padding:16px;display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;">

            <div style="display:flex;flex-direction:column;gap:4px;">
                <label style="font-size:11px;font-weight:600;color:#64748b;">Год</label>
                <select name="year" class="kmp-sel">
                    <option value="">Все годы</option>
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ request('year', '2026') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display:flex;flex-direction:column;gap:4px;">
                <label style="font-size:11px;font-weight:600;color:#64748b;">Дата от</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="kmp-sel">
            </div>

            <div style="display:flex;flex-direction:column;gap:4px;">
                <label style="font-size:11px;font-weight:600;color:#64748b;">Дата до</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="kmp-sel">
            </div>

            <div style="display:flex;flex-direction:column;gap:4px;">
                <label style="font-size:11px;font-weight:600;color:#64748b;">МП</label>
                @php
                    $selEmp = collect($empList)->firstWhere('value', request('kmp_employee_name'));
                    $initEmpLabel = $selEmp ? $selEmp['label'] : '';
                @endphp
                <div x-data="filterPicker(@js($empList), @js(request('kmp_employee_name')), @js($initEmpLabel))"
                     style="position:relative;width:190px;">
                    <div style="position:relative;">
                        <input type="text" x-model="query"
                               @focus="open=true" @input="open=true" @keydown.escape="open=false"
                               @click.outside="open=false"
                               autocomplete="off" placeholder="Поиск МП..."
                               class="kmp-sel" style="width:100%;box-sizing:border-box;padding-right:22px;">
                        <span x-show="selected" @click="clear($el.closest('form'))"
                              style="position:absolute;right:6px;top:50%;transform:translateY(-50%);cursor:pointer;color:#94a3b8;font-size:16px;line-height:1;user-select:none;">×</span>
                    </div>
                    <input type="hidden" name="kmp_employee_name" x-ref="hiddenVal"
                           x-effect="$refs.hiddenVal.value = selected ?? ''">
                    <div x-show="open && filtered.length" x-cloak
                         style="position:absolute;top:calc(100% + 2px);left:0;width:100%;z-index:999;background:#fff;border:1px solid #d1d5db;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,.1);max-height:200px;overflow-y:auto;">
                        <template x-for="emp in filtered" :key="emp.value">
                            <div @click="choose(emp, $el.closest('form'))"
                                 style="padding:7px 10px;font-size:12px;color:#1e293b;cursor:pointer;"
                                 onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background=''">
                                <span x-text="emp.label"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:4px;">
                <label style="font-size:11px;font-weight:600;color:#64748b;">Город</label>
                <select name="city" class="kmp-sel">
                    <option value="">Все города</option>
                    @foreach($cities as $c)
                        <option value="{{ $c }}" {{ request('city') == $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display:flex;flex-direction:column;gap:4px;">
                <label style="font-size:11px;font-weight:600;color:#64748b;">Бренд</label>
                <select name="brand" class="kmp-sel">
                    <option value="">Все бренды</option>
                    @foreach($brands as $b)
                        <option value="{{ $b }}" {{ request('brand') == $b ? 'selected' : '' }}>{{ $b }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display:flex;flex-direction:column;gap:4px;"
                 x-data="kmpMultiSelect(@js($depts->values()), @js(request('dept', [])))"
                 @click.outside="open=false">
                <label style="font-size:11px;font-weight:600;color:#64748b;">Подразделение</label>
                <div class="kms-wrap">
                    <div class="kms-display" @click="open=!open" tabindex="0">
                        <span class="kms-val" :class="selected.length ? 'active' : ''"
                              x-text="selected.length ? selected.length + ' выбр.' : 'Все'"></span>
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             style="width:11px;height:11px;flex-shrink:0;color:#94a3b8;"
                             :style="open ? 'transform:rotate(180deg)' : ''">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                    <div class="kms-dropdown" x-show="open" x-cloak>
                        <template x-for="item in options" :key="item">
                            <label class="kms-option">
                                <input type="checkbox" :value="item" @change="toggle(item)" :checked="selected.includes(item)">
                                <span x-text="item"></span>
                            </label>
                        </template>
                    </div>
                    <template x-for="s in selected">
                        <input type="hidden" name="dept[]" :value="s">
                    </template>
                </div>
            </div>

            <div style="display:flex;gap:8px;align-items:flex-end;">
                <button type="submit"
                    style="background:#1d4ed8;color:#fff;border:none;border-radius:6px;padding:7px 16px;font-size:13px;font-weight:600;cursor:pointer;">
                    Применить
                </button>
                <a href="{{ route('kmp.index') }}"
                   style="border:1px solid #d1d5db;background:#fff;color:#374151;border-radius:6px;padding:7px 14px;font-size:13px;text-decoration:none;">
                    Сбросить
                </a>
            </div>
        </div>
    </form>

    {{-- KPI cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:16px;margin-bottom:24px;">

        <div class="kmp-card">
            <div class="kmp-label">Сумма (KZT)</div>
            <div class="kmp-val" style="color:#0ea5e9;">{{ number_format($kpi->total_amount ?? 0) }}</div>
            <div class="kmp-sub">после скидок</div>
        </div>

        <div class="kmp-card">
            <div class="kmp-label">Заказы</div>
            <div class="kmp-val">{{ number_format($kpi->total_orders ?? 0) }}</div>
            <div class="kmp-sub">строк</div>
        </div>

        <div class="kmp-card">
            <div class="kmp-label">Упаковки</div>
            <div class="kmp-val" style="color:#16a34a;">{{ number_format($kpi->total_qty ?? 0) }}</div>
            <div class="kmp-sub">доставлено</div>
        </div>

        <div class="kmp-card">
            <div class="kmp-label">МП</div>
            <div class="kmp-val" style="color:#6366f1;">{{ $kpi->emp_count ?? 0 }}</div>
            <div class="kmp-sub">медпредставителей</div>
        </div>

        <div class="kmp-card">
            <div class="kmp-label">Аптеки</div>
            <div class="kmp-val" style="color:#f59e0b;">{{ $kpi->pharmacy_count ?? 0 }}</div>
            <div class="kmp-sub">уникальных</div>
        </div>

        <div class="kmp-card">
            <div class="kmp-label">Бренды</div>
            <div class="kmp-val" style="color:#ec4899;">{{ $kpi->brand_count ?? 0 }}</div>
            <div class="kmp-sub">уникальных</div>
        </div>

    </div>

    {{-- Charts row --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">

        {{-- Monthly trend --}}
        <div class="kmp-card">
            <div class="kmp-label" style="margin-bottom:16px;">Продажи по месяцам (KZT)</div>
            @if($monthlyTrend->count() > 0)
                @php
                    $maxAmt = $monthlyTrend->max('amount') ?: 1;
                    $mnMap = ['01'=>'Янв','02'=>'Фев','03'=>'Мар','04'=>'Апр','05'=>'Май','06'=>'Июн',
                              '07'=>'Июл','08'=>'Авг','09'=>'Сен','10'=>'Окт','11'=>'Ноя','12'=>'Дек'];
                @endphp
                <div style="display:flex;align-items:flex-end;gap:4px;height:100px;">
                    @foreach($monthlyTrend->take(-12) as $m)
                        @php
                            [, $mn] = explode('-', $m->month);
                            $lbl = ($mnMap[$mn] ?? $mn) . ' ' . substr($m->month, 0, 4);
                            $pct = max(4, round($m->amount / $maxAmt * 100));
                        @endphp
                        <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:2px;"
                             title="{{ $lbl }}: {{ number_format($m->amount) }} KZT / {{ number_format($m->qty) }} уп.">
                            <div style="width:100%;height:80px;display:flex;align-items:flex-end;">
                                <div style="width:100%;height:{{ $pct }}%;background:linear-gradient(180deg,#38bdf8,#0ea5e9);border-radius:3px 3px 0 0;min-height:3px;"></div>
                            </div>
                            <span style="font-size:8px;color:#94a3b8;writing-mode:vertical-rl;transform:rotate(180deg);height:32px;">{{ substr($lbl,0,7) }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="color:#94a3b8;font-size:13px;">Нет данных</div>
            @endif
        </div>

        {{-- Top brands --}}
        <div class="kmp-card" x-data="{
            showAmt: true,
            showQty: true,
            byAmt: @js($topBrands),
            byQty: @js($topBrandsByQty),
            get list() { return (!this.showAmt && this.showQty) ? this.byQty : this.byAmt; },
            get maxAmt() { return Math.max(...this.byAmt.map(b => b.amount), 1); },
            get maxQty()  { return Math.max(...this.byQty.map(b => b.qty),  1); },
            fmt(n) { return Math.round(n || 0).toLocaleString('ru-RU'); },
            toggle(m) {
                if (m === 'amt') { if (this.showAmt && !this.showQty) return; this.showAmt = !this.showAmt; }
                else             { if (this.showQty && !this.showAmt) return; this.showQty = !this.showQty; }
            },
        }">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
                <div class="kmp-label">Топ брендов</div>
                <div style="display:flex;gap:6px;">
                    <button @click="toggle('amt')"
                            :style="showAmt ? 'background:#0ea5e9;color:#fff;border-color:#0ea5e9;' : 'background:#fff;color:#94a3b8;border-color:#e2e8f0;'"
                            style="border:1px solid;border-radius:20px;padding:3px 10px;font-size:11px;font-weight:600;cursor:pointer;transition:all .15s;">
                        Сумма
                    </button>
                    <button @click="toggle('qty')"
                            :style="showQty ? 'background:#10b981;color:#fff;border-color:#10b981;' : 'background:#fff;color:#94a3b8;border-color:#e2e8f0;'"
                            style="border:1px solid;border-radius:20px;padding:3px 10px;font-size:11px;font-weight:600;cursor:pointer;transition:all .15s;">
                        Уп.
                    </button>
                </div>
            </div>
            @if($topBrands->count() > 0)
            <div style="display:flex;flex-direction:column;gap:8px;max-height:200px;overflow-y:auto;">
                <template x-for="b in list" :key="b.brand">
                    <div>
                        <div style="display:flex;justify-content:space-between;margin-bottom:3px;gap:6px;">
                            <span x-text="b.brand" style="font-size:12px;color:#374151;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0;flex:1;"></span>
                            <span x-show="showQty" x-text="fmt(b.qty) + ' уп.'" style="font-size:11px;color:#10b981;font-weight:600;flex-shrink:0;white-space:nowrap;"></span>
                            <span x-show="showAmt" x-text="fmt(b.amount)" style="font-size:11px;font-weight:600;color:#0ea5e9;flex-shrink:0;white-space:nowrap;"></span>
                        </div>
                        <div style="height:4px;background:#f1f5f9;border-radius:2px;">
                            <div x-show="showAmt"
                                 :style="`height:100%;width:${Math.round(b.amount/maxAmt*100)}%;background:linear-gradient(90deg,#38bdf8,#0ea5e9);border-radius:2px;`"></div>
                            <div x-show="!showAmt && showQty"
                                 :style="`height:100%;width:${Math.round(b.qty/maxQty*100)}%;background:linear-gradient(90deg,#34d399,#10b981);border-radius:2px;`"></div>
                        </div>
                    </div>
                </template>
            </div>
            @else
                <div style="color:#94a3b8;font-size:13px;">Нет данных</div>
            @endif
        </div>

    </div>

    {{-- Top pharmacies --}}
    @if($topPharmacies->count() > 0)
    <div class="kmp-card" style="margin-bottom:24px;">
        <div class="kmp-label" style="margin-bottom:14px;">Топ аптек по сумме</div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:1px solid var(--kmp-border);">
                        <th class="kmp-th" style="text-align:left;">#</th>
                        <th class="kmp-th" style="text-align:left;">Аптека</th>
                        <th class="kmp-th" style="text-align:left;">Город</th>
                        <th class="kmp-th" style="text-align:right;">Упак.</th>
                        <th class="kmp-th" style="text-align:right;">Сумма (KZT)</th>
                        <th class="kmp-th" style="text-align:right;">Заказов</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($topPharmacies as $i => $ph)
                    <tr onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                        <td class="kmp-td" style="color:#94a3b8;width:32px;">{{ $i + 1 }}</td>
                        <td class="kmp-td" style="font-weight:500;">{{ $ph->name }}</td>
                        <td class="kmp-td" style="color:#64748b;">{{ $ph->city }}</td>
                        <td class="kmp-td" style="text-align:right;color:#374151;font-weight:500;">{{ number_format($ph->qty) }}</td>
                        <td class="kmp-td" style="text-align:right;font-weight:600;color:#0ea5e9;">{{ number_format($ph->amount) }}</td>
                        <td class="kmp-td" style="text-align:right;color:#64748b;">{{ $ph->orders }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Детальная таблица --}}
    <div class="kmp-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <div class="kmp-label">Детальные строки</div>
            <span style="font-size:12px;color:#64748b;">{{ $rows->total() }} записей</span>
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:1px solid var(--kmp-border);">
                        @php
                            $sorts = [
                                'Дата'           => 'Дата',
                                'Медпредставитель' => 'МП',
                                'Название аптеки'  => 'Аптека',
                                'Брэнд'            => 'Бренд',
                                'Amount_disc'      => 'Сумма (KZT)',
                                'Дост_колво'       => 'Упак.',
                            ];
                        @endphp
                        @foreach($sorts as $col => $label)
                            <th class="kmp-th" style="text-align:{{ in_array($col, ['Amount_disc','Дост_колво']) ? 'right' : 'left' }};">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => $col, 'dir' => ($sortCol === $col && $sortDir === 'asc') ? 'desc' : 'asc']) }}"
                                   class="kmp-sort">
                                    {{ $label }}
                                    @if($sortCol === $col)
                                        {{ $sortDir === 'asc' ? '↑' : '↓' }}
                                    @endif
                                </a>
                            </th>
                        @endforeach
                        <th class="kmp-th">Статус</th>
                        <th class="kmp-th">Город</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($rows as $row)
                    <tr onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                        <td class="kmp-td" style="color:#64748b;white-space:nowrap;">{{ \Carbon\Carbon::parse($row->{'Дата'})->format('d.m.Y') }}</td>
                        <td class="kmp-td" style="white-space:nowrap;max-width:160px;overflow:hidden;text-overflow:ellipsis;">{{ $row->{'Медпредставитель'} }}</td>
                        <td class="kmp-td" style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $row->{'Название аптеки'} }}</td>
                        <td class="kmp-td">{{ $row->{'Брэнд'} }}</td>
                        <td class="kmp-td" style="text-align:right;font-weight:600;color:#0ea5e9;white-space:nowrap;">{{ number_format($row->{'Amount_disc'}, 0, '.', ' ') }}</td>
                        <td class="kmp-td" style="text-align:right;">{{ (int) $row->{'Дост_колво'} }}</td>
                        <td class="kmp-td">
                            @php $st = $row->{'Статус заказа'}; @endphp
                            <span style="padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600;
                                {{ $st === 'Доставлено' ? 'background:#dcfce7;color:#15803d;' : 'background:#fef9c3;color:#854d0e;' }}">
                                {{ $st }}
                            </span>
                        </td>
                        <td class="kmp-td" style="color:#64748b;">{{ $row->{'Город'} }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="padding:24px;text-align:center;color:#94a3b8;font-size:13px;">Нет данных</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($rows->hasPages())
        <div style="padding:16px 0 0;display:flex;justify-content:center;">
            {{ $rows->withQueryString()->links() }}
        </div>
        @endif
    </div>

</div>

<script>
function kmpMultiSelect(options, init) {
    return {
        open: false,
        options,
        selected: Array.isArray(init) ? init : (init ? [init] : []),
        toggle(item) {
            this.selected.includes(item)
                ? this.selected = this.selected.filter(i => i !== item)
                : this.selected.push(item);
        },
    };
}

function filterPicker(list, initValue, initLabel) {
    return {
        list,
        selected: initValue || null,
        query:    initLabel || '',
        open:     false,
        get filtered() {
            const q = this.query.trim().toLowerCase();
            if (!q) return this.list.slice(0, 80);
            return this.list.filter(e => e.label.toLowerCase().includes(q)).slice(0, 80);
        },
        choose(emp, form) {
            this.selected = emp.value;
            this.query    = emp.label;
            this.open     = false;
            this.$nextTick(() => form && form.submit());
        },
        clear(form) {
            this.selected = null;
            this.query    = '';
            this.open     = false;
            this.$nextTick(() => form && form.submit());
        },
    };
}
</script>

@endsection
