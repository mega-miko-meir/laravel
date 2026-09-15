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
.kmp-th    { padding:10px 14px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--kmp-t3);white-space:nowrap;cursor:pointer;user-select:none; }
.kmp-th:hover { color:var(--kmp-accent); }
.kmp-td    { padding:10px 14px;font-size:13px;color:var(--kmp-t1);border-bottom:1px solid var(--kmp-border); }
.kmp-sort  { text-decoration:none;color:inherit; }
.kmp-sort:hover { text-decoration:underline; }
.kmp-loading { opacity:.6;pointer-events:none;transition:opacity .15s; }
.kmp-page-btn { border:1px solid var(--kmp-border);background:#fff;color:var(--kmp-t2);border-radius:6px;padding:5px 11px;font-size:12px;cursor:pointer; }
.kmp-page-btn[disabled] { opacity:.4;pointer-events:none; }
.kmp-page-btn.active { background:#1d4ed8;color:#fff;border-color:#1d4ed8; }

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

<div style="max-width:1300px;margin:0 auto;"
     x-data='kmp(@json($initialData), "{{ $year }}", @json($empList), @json($brands), @json($cities), @json($depts), @json($years))'>

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
                <span x-show="activeFilterCount > 0"
                      style="background:rgba(255,255,255,.25);border-radius:10px;padding:1px 7px;font-size:11px;font-weight:700;"
                      x-text="activeFilterCount"></span>
            </button>
        </div>

        {{-- Export panel --}}
        <div x-show="exportOpen" x-cloak x-transition
             style="margin-top:12px;background:#f0fdf4;border:1px solid #86efac;border-radius:12px;padding:16px;">
            <form method="POST" action="{{ route('kmp.export') }}" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
                @csrf
                <input type="hidden" name="year" :value="year">
                <input type="hidden" name="employee_id" :value="filters.employeeId ?? ''">
                <input type="hidden" name="city" :value="filters.city">
                <input type="hidden" name="brand" :value="filters.brand">
                <template x-for="d in filters.depts">
                    <input type="hidden" name="dept[]" :value="d">
                </template>
                <div style="display:flex;flex-direction:column;gap:4px;">
                    <label style="font-size:11px;font-weight:600;color:#15803d;">Дата от</label>
                    <input type="date" name="date_from" class="kmp-sel" style="border-color:#86efac;">
                </div>
                <div style="display:flex;flex-direction:column;gap:4px;">
                    <label style="font-size:11px;font-weight:600;color:#15803d;">Дата до</label>
                    <input type="date" name="date_to" class="kmp-sel" style="border-color:#86efac;">
                </div>
                <button type="submit"
                    style="background:#16a34a;color:#fff;border:none;border-radius:6px;padding:7px 18px;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;">
                    <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Скачать
                </button>
                <p style="font-size:11px;color:#15803d;margin:0;align-self:center;font-weight:500;" x-show="activeFilterCount > 0">
                    Фильтров: <span x-text="activeFilterCount"></span> — в файл попадут только отфильтрованные строки
                </p>
                <p style="font-size:11px;color:#64748b;margin:0;align-self:center;" x-show="activeFilterCount === 0">
                    CSV, все колонки за <span x-text="year"></span> год (даты выше — чтобы выгрузить другой диапазон)
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
    <div x-show="data.error" x-cloak style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:12px 16px;margin-bottom:20px;color:#991b1b;font-size:13px;" x-text="data.error"></div>

    {{-- Filters --}}
    <div style="background:#fff;border-radius:12px;border:1px solid var(--kmp-border);padding:16px;margin-bottom:20px;display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;">

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label style="font-size:11px;font-weight:600;color:#64748b;">Год</label>
            <select x-model="year" class="kmp-sel" style="width:100px;">
                <template x-for="y in years" :key="y">
                    <option :value="String(y)" x-text="y"></option>
                </template>
            </select>
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label style="font-size:11px;font-weight:600;color:#64748b;">МП</label>
            <div style="position:relative;width:190px;">
                <div style="position:relative;">
                    <input type="text" x-model="empQuery"
                           @focus="empOpen=true" @input="empOpen=true" @keydown.escape="empOpen=false"
                           @click.outside="empOpen=false"
                           autocomplete="off" placeholder="Поиск МП..."
                           class="kmp-sel" style="width:100%;box-sizing:border-box;padding-right:22px;">
                    <span x-show="filters.employeeId" @click="clearEmp()"
                          style="position:absolute;right:6px;top:50%;transform:translateY(-50%);cursor:pointer;color:#94a3b8;font-size:16px;line-height:1;user-select:none;">×</span>
                </div>
                <div x-show="empOpen && empFiltered.length" x-cloak
                     style="position:absolute;top:calc(100% + 2px);left:0;width:100%;z-index:999;background:#fff;border:1px solid #d1d5db;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,.1);max-height:200px;overflow-y:auto;">
                    <template x-for="emp in empFiltered" :key="emp.value">
                        <div @click="chooseEmp(emp)"
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
            <select x-model="filters.city" class="kmp-sel">
                <option value="">Все города</option>
                <template x-for="c in cities" :key="c">
                    <option :value="c" x-text="c"></option>
                </template>
            </select>
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label style="font-size:11px;font-weight:600;color:#64748b;">Бренд</label>
            <select x-model="filters.brand" class="kmp-sel">
                <option value="">Все бренды</option>
                <template x-for="b in brands" :key="b">
                    <option :value="b" x-text="b"></option>
                </template>
            </select>
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;" x-data="{ open: false }" @click.outside="open=false">
            <label style="font-size:11px;font-weight:600;color:#64748b;">Подразделение</label>
            <div class="kms-wrap">
                <div class="kms-display" @click="open=!open" tabindex="0">
                    <span class="kms-val" :class="filters.depts.length ? 'active' : ''"
                          x-text="filters.depts.length ? filters.depts.length + ' выбр.' : 'Все'"></span>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         style="width:11px;height:11px;flex-shrink:0;color:#94a3b8;"
                         :style="open ? 'transform:rotate(180deg)' : ''">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
                <div class="kms-dropdown" x-show="open" x-cloak>
                    <template x-for="item in depts" :key="item">
                        <label class="kms-option">
                            <input type="checkbox" :value="item"
                                   @change="filters.depts.includes(item) ? filters.depts = filters.depts.filter(i => i !== item) : filters.depts.push(item)"
                                   :checked="filters.depts.includes(item)">
                            <span x-text="item"></span>
                        </label>
                    </template>
                </div>
            </div>
        </div>

        <div style="display:flex;gap:8px;align-items:flex-end;">
            <button type="button" @click="loadYear()" :disabled="loading" class="kmp-page-btn"
                    style="background:#1d4ed8;color:#fff;border-color:#1d4ed8;height:32px;padding:0 16px;font-weight:600;">
                <span x-text="loading ? 'Загрузка…' : 'Обновить год'"></span>
            </button>
            <a href="#" @click.prevent="resetFilters()" x-show="activeFilterCount > 0"
               class="kmp-page-btn" style="height:32px;padding:0 14px;display:inline-flex;align-items:center;">Сбросить фильтры</a>
        </div>
    </div>

    <div :class="{ 'kmp-loading': loading }">

    {{-- KPI cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:16px;margin-bottom:24px;">

        <div class="kmp-card">
            <div class="kmp-label">Сумма (KZT)</div>
            <div class="kmp-val" style="color:#0ea5e9;" x-text="fmt(kpi.total_amount)"></div>
            <div class="kmp-sub">после скидок</div>
        </div>

        <div class="kmp-card">
            <div class="kmp-label">Заказы</div>
            <div class="kmp-val" x-text="fmt(kpi.total_orders)"></div>
            <div class="kmp-sub">строк</div>
        </div>

        <div class="kmp-card">
            <div class="kmp-label">Упаковки</div>
            <div class="kmp-val" style="color:#16a34a;" x-text="fmt(kpi.total_qty)"></div>
            <div class="kmp-sub">доставлено</div>
        </div>

        <div class="kmp-card">
            <div class="kmp-label">МП</div>
            <div class="kmp-val" style="color:#6366f1;" x-text="kpi.emp_count"></div>
            <div class="kmp-sub">медпредставителей</div>
        </div>

        <div class="kmp-card">
            <div class="kmp-label">Аптеки</div>
            <div class="kmp-val" style="color:#f59e0b;" x-text="kpi.pharmacy_count"></div>
            <div class="kmp-sub">уникальных</div>
        </div>

        <div class="kmp-card">
            <div class="kmp-label">Бренды</div>
            <div class="kmp-val" style="color:#ec4899;" x-text="kpi.brand_count"></div>
            <div class="kmp-sub">уникальных</div>
        </div>

    </div>

    {{-- Charts row --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">

        {{-- Тренд по месяцам выбранного года — не реагирует на вторичные фильтры --}}
        <div class="kmp-card">
            <div class="kmp-label" style="margin-bottom:16px;">Продажи по месяцам (KZT)</div>
            <template x-if="data.trend.length > 0">
                <div style="display:flex;align-items:flex-end;gap:4px;height:100px;">
                    <template x-for="m in data.trend" :key="m.month">
                        <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:2px;"
                             :title="trendLabel(m.month) + ': ' + fmt(m.amount) + ' KZT / ' + fmt(m.qty) + ' уп.'">
                            <div style="width:100%;height:80px;display:flex;align-items:flex-end;">
                                <div :style="'width:100%;height:' + trendPct(m.amount) + '%;background:linear-gradient(180deg,#38bdf8,#0ea5e9);border-radius:3px 3px 0 0;min-height:3px;'"></div>
                            </div>
                            <span style="font-size:8px;color:#94a3b8;writing-mode:vertical-rl;transform:rotate(180deg);height:32px;" x-text="trendLabel(m.month)"></span>
                        </div>
                    </template>
                </div>
            </template>
            <template x-if="data.trend.length === 0">
                <div style="color:#94a3b8;font-size:13px;">Нет данных</div>
            </template>
        </div>

        {{-- Top brands --}}
        <div class="kmp-card" x-data="{ showAmt: true, showQty: true,
            toggle(m) {
                if (m === 'amt') { if (this.showAmt && !this.showQty) return; this.showAmt = !this.showAmt; }
                else             { if (this.showQty && !this.showAmt) return; this.showQty = !this.showQty; }
            } }">
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
            <template x-if="topBrands.length > 0">
                <div style="display:flex;flex-direction:column;gap:8px;max-height:200px;overflow-y:auto;">
                    <template x-for="b in ((!showAmt && showQty) ? topBrandsByQty : topBrands)" :key="b.brand">
                        <div>
                            <div style="display:flex;justify-content:space-between;margin-bottom:3px;gap:6px;">
                                <span x-text="b.brand" style="font-size:12px;color:#374151;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0;flex:1;"></span>
                                <span x-show="showQty" x-text="fmt(b.qty) + ' уп.'" style="font-size:11px;color:#10b981;font-weight:600;flex-shrink:0;white-space:nowrap;"></span>
                                <span x-show="showAmt" x-text="fmt(b.amount)" style="font-size:11px;font-weight:600;color:#0ea5e9;flex-shrink:0;white-space:nowrap;"></span>
                            </div>
                            <div style="height:4px;background:#f1f5f9;border-radius:2px;">
                                <div x-show="showAmt"
                                     :style="'height:100%;width:' + Math.round(b.amount / (maxBrandAmt || 1) * 100) + '%;background:linear-gradient(90deg,#38bdf8,#0ea5e9);border-radius:2px;'"></div>
                                <div x-show="!showAmt && showQty"
                                     :style="'height:100%;width:' + Math.round(b.qty / (maxBrandQty || 1) * 100) + '%;background:linear-gradient(90deg,#34d399,#10b981);border-radius:2px;'"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
            <template x-if="topBrands.length === 0">
                <div style="color:#94a3b8;font-size:13px;">Нет данных</div>
            </template>
        </div>

    </div>

    {{-- Top pharmacies --}}
    <template x-if="topPharmacies.length > 0">
    <div class="kmp-card" style="margin-bottom:24px;">
        <div class="kmp-label" style="margin-bottom:14px;">Топ аптек по сумме</div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:1px solid var(--kmp-border);">
                        <th class="kmp-th" style="text-align:left;cursor:default;">#</th>
                        <th class="kmp-th" style="text-align:left;cursor:default;">Аптека</th>
                        <th class="kmp-th" style="text-align:left;cursor:default;">Город</th>
                        <th class="kmp-th" style="text-align:right;cursor:default;">Упак.</th>
                        <th class="kmp-th" style="text-align:right;cursor:default;">Сумма (KZT)</th>
                        <th class="kmp-th" style="text-align:right;cursor:default;">Заказов</th>
                    </tr>
                </thead>
                <tbody>
                <template x-for="(ph, i) in topPharmacies" :key="ph.name + '|' + ph.city">
                    <tr onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                        <td class="kmp-td" style="color:#94a3b8;width:32px;" x-text="i + 1"></td>
                        <td class="kmp-td" style="font-weight:500;" x-text="ph.name"></td>
                        <td class="kmp-td" style="color:#64748b;" x-text="ph.city"></td>
                        <td class="kmp-td" style="text-align:right;color:#374151;font-weight:500;" x-text="fmt(ph.qty)"></td>
                        <td class="kmp-td" style="text-align:right;font-weight:600;color:#0ea5e9;" x-text="fmt(ph.amount)"></td>
                        <td class="kmp-td" style="text-align:right;color:#64748b;" x-text="ph.orders"></td>
                    </tr>
                </template>
                </tbody>
            </table>
        </div>
    </div>
    </template>

    {{-- Детальная таблица --}}
    <div class="kmp-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <div class="kmp-label">Детальные строки</div>
            <span style="font-size:12px;color:#64748b;"><span x-text="fmt(filteredRows.length)"></span> записей</span>
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:1px solid var(--kmp-border);">
                        <th class="kmp-th" @click="sortBy('date')">Дата <span x-text="sortIco('date')"></span></th>
                        <th class="kmp-th" @click="sortBy('employee')">МП <span x-text="sortIco('employee')"></span></th>
                        <th class="kmp-th" @click="sortBy('pharmacy')">Аптека <span x-text="sortIco('pharmacy')"></span></th>
                        <th class="kmp-th" @click="sortBy('brand')">Бренд <span x-text="sortIco('brand')"></span></th>
                        <th class="kmp-th" style="text-align:right;" @click="sortBy('amount')">Сумма (KZT) <span x-text="sortIco('amount')"></span></th>
                        <th class="kmp-th" style="text-align:right;" @click="sortBy('qty')">Упак. <span x-text="sortIco('qty')"></span></th>
                        <th class="kmp-th" style="cursor:default;">Статус</th>
                        <th class="kmp-th" style="cursor:default;">Город</th>
                    </tr>
                </thead>
                <tbody>
                <template x-if="pagedRows.length === 0">
                    <tr><td colspan="8" style="padding:24px;text-align:center;color:#94a3b8;font-size:13px;">Нет данных</td></tr>
                </template>
                <template x-for="row in pagedRows" :key="row.i">
                    <tr onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                        <td class="kmp-td" style="color:#64748b;white-space:nowrap;" x-text="fmtDate(row.date)"></td>
                        <td class="kmp-td" style="white-space:nowrap;max-width:160px;overflow:hidden;text-overflow:ellipsis;" x-text="row.employee"></td>
                        <td class="kmp-td" style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" x-text="row.pharmacy"></td>
                        <td class="kmp-td" x-text="row.brand"></td>
                        <td class="kmp-td" style="text-align:right;font-weight:600;color:#0ea5e9;white-space:nowrap;" x-text="fmt(row.amount)"></td>
                        <td class="kmp-td" style="text-align:right;" x-text="fmt(row.qty)"></td>
                        <td class="kmp-td">
                            <span style="padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600;background:#dcfce7;color:#15803d;">Доставлено</span>
                        </td>
                        <td class="kmp-td" style="color:#64748b;" x-text="row.city"></td>
                    </tr>
                </template>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div x-show="totalPages > 1" style="padding:16px 0 0;display:flex;justify-content:center;gap:6px;">
            <button class="kmp-page-btn" @click="page = 1" :disabled="page === 1">«</button>
            <button class="kmp-page-btn" @click="page--" :disabled="page === 1">‹</button>
            <span style="font-size:12px;color:#64748b;padding:0 8px;display:flex;align-items:center;">
                <span x-text="page"></span> / <span x-text="totalPages"></span>
            </span>
            <button class="kmp-page-btn" @click="page++" :disabled="page === totalPages">›</button>
            <button class="kmp-page-btn" @click="page = totalPages" :disabled="page === totalPages">»</button>
        </div>
    </div>

    </div>

</div>

<script>
function kmp(initialData, initialYear, empList, brands, cities, depts, years) {
    return {
        year: initialYear,
        loading: false,
        data: initialData,
        rows: [],
        empList, brands, cities, depts, years,

        filters: { employeeId: null, city: '', brand: '', depts: [] },
        empQuery: '',
        empOpen: false,
        sort: { col: 'date', dir: 'desc' },
        page: 1,
        perPage: 25,

        init() {
            this.decodeRows();
        },

        get empFiltered() {
            const q = this.empQuery.trim().toLowerCase();
            if (!q) return this.empList.slice(0, 80);
            return this.empList.filter(e => e.label.toLowerCase().includes(q)).slice(0, 80);
        },
        chooseEmp(emp) {
            this.filters.employeeId = emp.value;
            this.empQuery = emp.label;
            this.empOpen = false;
        },
        clearEmp() {
            this.filters.employeeId = null;
            this.empQuery = '';
            this.empOpen = false;
        },

        decodeRows() {
            const r = this.data.rows;
            if (!r || !r.data) { this.rows = []; return; }
            const dict = r.dictionaries;
            this.rows = r.data.map((row, i) => ({
                i,
                date: dict.date[row[0]],
                employee: dict.employee[row[1]],
                city: dict.city[row[2]],
                pharmacy: dict.pharmacy[row[3]],
                pharmacyCity: dict.pharmacyCity[row[4]],
                brand: dict.brand[row[5]],
                dept: dict.dept[row[6]],
                amount: row[7],
                qty: row[8],
                pharmacyId: row[9],
            }));
        },

        get activeFilterCount() {
            let n = 0;
            if (this.filters.employeeId) n++;
            if (this.filters.city) n++;
            if (this.filters.brand) n++;
            if (this.filters.depts.length) n++;
            return n;
        },

        get filteredRows() {
            let rows = this.rows;
            if (this.filters.employeeId) {
                const emp = this.empList.find(e => e.value === this.filters.employeeId);
                const names = emp ? emp.names : [];
                rows = rows.filter(r => names.includes(r.employee));
            }
            if (this.filters.city)  rows = rows.filter(r => r.city === this.filters.city);
            if (this.filters.brand) rows = rows.filter(r => r.brand === this.filters.brand);
            if (this.filters.depts.length) rows = rows.filter(r => this.filters.depts.includes(r.dept));
            return rows;
        },

        get kpi() {
            const rows = this.filteredRows;
            const emps = new Set(), pharms = new Set(), brands = new Set();
            let amount = 0, qty = 0;
            for (const r of rows) {
                amount += r.amount; qty += r.qty;
                emps.add(r.employee); pharms.add(r.pharmacyId); brands.add(r.brand);
            }
            return {
                total_amount: Math.round(amount), total_qty: Math.round(qty), total_orders: rows.length,
                emp_count: emps.size, pharmacy_count: pharms.size, brand_count: brands.size,
            };
        },

        get topBrands() {
            const agg = {};
            for (const r of this.filteredRows) {
                agg[r.brand] ??= { brand: r.brand, amount: 0, qty: 0 };
                agg[r.brand].amount += r.amount;
                agg[r.brand].qty += r.qty;
            }
            return Object.values(agg).sort((a, b) => b.amount - a.amount).slice(0, 15);
        },
        get topBrandsByQty() {
            return [...this.topBrands].sort((a, b) => b.qty - a.qty);
        },
        get maxBrandAmt() { return Math.max(...this.topBrands.map(b => b.amount), 1); },
        get maxBrandQty() { return Math.max(...this.topBrands.map(b => b.qty), 1); },

        get topPharmacies() {
            const agg = {};
            for (const r of this.filteredRows) {
                const key = r.pharmacy + '|' + r.pharmacyCity;
                agg[key] ??= { name: r.pharmacy, city: r.pharmacyCity, amount: 0, qty: 0, orders: 0 };
                agg[key].amount += r.amount;
                agg[key].qty += r.qty;
                agg[key].orders++;
            }
            return Object.values(agg).sort((a, b) => b.amount - a.amount).slice(0, 10);
        },

        get sortedRows() {
            const { col, dir } = this.sort;
            const mult = dir === 'asc' ? 1 : -1;
            return [...this.filteredRows].sort((a, b) => {
                const av = a[col], bv = b[col];
                if (typeof av === 'string') return av.localeCompare(bv) * mult;
                return (av - bv) * mult;
            });
        },
        get totalPages() { return Math.max(1, Math.ceil(this.filteredRows.length / this.perPage)); },
        get pagedRows() {
            if (this.page > this.totalPages) this.page = this.totalPages;
            const start = (this.page - 1) * this.perPage;
            return this.sortedRows.slice(start, start + this.perPage);
        },

        sortBy(col) {
            if (this.sort.col === col) {
                this.sort.dir = this.sort.dir === 'asc' ? 'desc' : 'asc';
            } else {
                this.sort = { col, dir: col === 'amount' || col === 'qty' || col === 'date' ? 'desc' : 'asc' };
            }
            this.page = 1;
        },
        sortIco(col) {
            if (this.sort.col !== col) return '↕';
            return this.sort.dir === 'asc' ? '↑' : '↓';
        },

        resetFilters() {
            this.filters = { employeeId: null, city: '', brand: '', depts: [] };
            this.empQuery = '';
            this.page = 1;
        },

        fmt(n) {
            return String(Math.round(n || 0)).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        },
        fmtDate(d) {
            if (!d) return '';
            const [y, m, day] = d.split('-');
            return `${day}.${m}.${y}`;
        },
        trendLabel(month) {
            const map = {'01':'Янв','02':'Фев','03':'Мар','04':'Апр','05':'Май','06':'Июн','07':'Июл','08':'Авг','09':'Сен','10':'Окт','11':'Ноя','12':'Дек'};
            const [y, m] = month.split('-');
            return (map[m] || m) + ' ' + y;
        },
        trendPct(amount) {
            const max = Math.max(...this.data.trend.map(t => t.amount), 1);
            return Math.max(4, Math.round(amount / max * 100));
        },

        async loadYear() {
            this.loading = true;
            try {
                const params = new URLSearchParams({ year: this.year });
                const res = await fetch('{{ route('kmp.data') }}?' + params.toString(), {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });
                if (!res.ok) throw new Error('http_' + res.status);
                this.data = await res.json();
                this.decodeRows();
                this.page = 1;

                const url = new URL(window.location.href);
                url.searchParams.set('year', this.year);
                window.history.replaceState({}, '', url);
            } catch (e) {
                this.data = { ...this.data, error: 'Не удалось получить данные из Nobel CRM. Попробуйте позже.' };
            } finally {
                this.loading = false;
            }
        },
    };
}
</script>

@endsection
