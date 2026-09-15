@extends('layout')
@section('content')

<style>
:root {
    --bg:      #f1f5f9;
    --card:    #ffffff;
    --border:  #e2e8f0;
    --text1:   #1e293b;
    --text2:   #64748b;
    --text3:   #94a3b8;
    --blue:    #3b82f6;
    --green:   #10b981;
    --amber:   #f59e0b;
    --red:     #ef4444;
    --purple:  #6366f1;
    --sky:     #0ea5e9;
    --shadow:  0 1px 3px rgba(0,0,0,.07);
    --radius:  12px;
}

.lb-wrap   { max-width:1500px; margin:0 auto; padding-bottom:40px; }
.lb-header { display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;padding-top:4px; }
.lb-title  { font-size:22px;font-weight:700;color:var(--text1);display:flex;align-items:center;gap:10px; }
.lb-title span { font-size:13px;font-weight:500;color:var(--text3);background:var(--card);border:1px solid var(--border);border-radius:20px;padding:2px 10px; }

.btn { display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;border:1px solid var(--border);background:var(--card);color:var(--text2);text-decoration:none;transition:all .15s; }
.btn:hover { background:var(--bg);color:var(--text1); }
.btn-green { background:#16a34a;color:#fff;border-color:#16a34a; }
.btn-green:hover { opacity:.9;background:#16a34a;color:#fff; }
.btn[disabled] { opacity:.6;pointer-events:none; }

.filter-panel { background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:10px 16px;margin-bottom:20px;box-shadow:var(--shadow); }
.filter-grid  { display:flex;flex-wrap:wrap;gap:8px;align-items:flex-end; }
.filter-field { display:flex;flex-direction:column;gap:3px; }
.filter-label { font-size:10px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:.06em; }
.filter-input { background:var(--bg);border:1px solid var(--border);border-radius:7px;padding:0 10px;height:30px;font-size:12px;color:var(--text1);outline:none; }
.filter-input:focus { border-color:var(--blue); }

.lb-card    { background:var(--card);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;transition:opacity .15s; }
.lb-loading { opacity:.6;pointer-events:none; }
.lb-toolbar { display:flex;align-items:center;justify-content:space-between;gap:16px;padding:12px 16px;border-bottom:1px solid var(--border);flex-wrap:wrap; }
.lb-info    { font-size:13px;color:var(--text2); }
.lb-info strong { color:var(--text1); }

.lb-meta { display:flex;gap:20px;flex-wrap:wrap; }
.meta-chip {
    display:flex;align-items:center;gap:6px;
    background:var(--bg);border:1px solid var(--border);border-radius:8px;
    padding:5px 12px;font-size:12px;color:var(--text2);
}
.meta-chip strong { color:var(--text1);font-size:13px; }

.lb-table { width:100%;border-collapse:collapse;font-size:13px; }
.lb-table th {
    padding:9px 13px;text-align:left;font-size:10px;font-weight:600;
    text-transform:uppercase;letter-spacing:.06em;color:var(--text2);
    background:var(--bg);white-space:nowrap;cursor:pointer;user-select:none;
    border-bottom:1px solid var(--border);
}
.lb-table th a { color:inherit;text-decoration:none;display:flex;align-items:center;justify-content:flex-end;gap:2px; }
.lb-table th:hover { color:var(--blue); }
.lb-table th.sort-active { color:var(--blue); }
.sort-ico { margin-left:3px;opacity:.4;font-size:10px; }
.sort-active .sort-ico { opacity:1; }
.lb-table td { padding:10px 13px;border-bottom:1px solid var(--border);color:var(--text1);vertical-align:middle; }
.lb-table tr:last-child td { border-bottom:none; }
.lb-table tbody tr:hover td { background:#f8fafc; }

.lb-table th.group-sep,
.lb-table td.group-sep { border-left:2px solid var(--border); }

.th-group {
    padding:5px 13px 4px;font-size:9px;font-weight:700;text-transform:uppercase;
    letter-spacing:.08em;color:var(--text3);background:var(--bg);
    border-bottom:1px solid var(--border) !important;text-align:center;
    cursor:default;
}

.rank-cell { width:40px;text-align:center;font-weight:700;font-size:15px; }
.rank-1 { color:#f59e0b; }
.rank-2 { color:#94a3b8; }
.rank-3 { color:#b45309; }
.rank-n { color:#cbd5e1;font-size:12px; }

.emp-name { font-weight:600;color:var(--text1); }
.emp-pos  { font-size:11px;color:var(--text3);margin-top:1px; }

.num-cell { text-align:right;font-variant-numeric:tabular-nums; }
.num-big  { font-weight:700; }

.bar-wrap { margin-top:4px;height:3px;background:var(--border);border-radius:2px;min-width:50px; }
.bar-fill { height:100%;border-radius:2px;transition:width .5s; }

.pct-cell { text-align:right; }
.pct-val  { font-size:13px;font-weight:700;margin-bottom:2px; }
.pct-sub  { font-size:11px;color:var(--text3); }
.pct-bar  { height:4px;background:var(--border);border-radius:2px;min-width:70px;margin-top:3px; }
.pct-bar-fill { height:100%;border-radius:2px;max-width:100%; }
</style>

<div class="lb-wrap" style="background:var(--bg);min-height:100%;"
     x-data='leaderboard(@json($initialData), "{{ $month }}")'>

{{-- Header --}}
<div class="lb-header">
    <div class="lb-title">
        Рейтинг МП
        <span x-text="data.rows.length + ' сотрудников'"></span>
    </div>
    <a :href="exportUrl()" class="btn btn-green">
        <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Скачать CSV
    </a>
</div>

{{-- Filters --}}
<div class="filter-panel">
    <div class="filter-grid">
        <div class="filter-field">
            <label class="filter-label">Месяц</label>
            <input type="month" x-model="month" class="filter-input" style="width:150px;">
        </div>
        <div class="filter-field" style="flex-direction:row;gap:6px;align-items:flex-end;">
            <button type="button" @click="loadMonth()" :disabled="loading" class="btn"
                    style="background:#1d4ed8;color:#fff;border-color:#1d4ed8;height:30px;padding:0 14px;font-size:12px;">
                <span x-text="loading ? 'Загрузка…' : 'Показать'"></span>
            </button>
        </div>
    </div>
</div>

<div x-show="data.error" x-cloak style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:14px 18px;color:#b91c1c;font-size:13px;margin-bottom:20px;" x-text="data.error"></div>

{{-- Table --}}
<div class="lb-card" :class="{ 'lb-loading': loading }" x-show="!data.error">
    <div class="lb-toolbar">
        <div class="lb-info">
            Показано <strong x-text="data.rows.length"></strong> сотрудников
        </div>
        <div class="lb-meta">
            <div class="meta-chip">
                Рабочих дней: <strong x-text="data.workingDays"></strong>
            </div>
            <div class="meta-chip">
                Таргет визитов: <strong x-text="data.workingDays + ' × {{ \App\Http\Controllers\LeaderboardController::DAILY_TARGET }} = ' + data.callTarget"></strong>
            </div>
            <div class="meta-chip">
                Частота: <strong>{{ \App\Http\Controllers\LeaderboardController::FREQUENCY }}×</strong>
            </div>
        </div>
    </div>

    <template x-if="data.rows.length === 0">
        <div style="padding:48px;text-align:center;color:var(--text3);font-size:14px;">
            Нет данных для выбранного периода
        </div>
    </template>

    <div style="overflow-x:auto;" x-show="data.rows.length > 0">
    <table class="lb-table">
        <thead>
            {{-- Строка 1: группы --}}
            <tr>
                <th class="rank-cell" rowspan="2" style="cursor:default;">#</th>
                <th rowspan="2" style="cursor:default;min-width:160px;">Сотрудник</th>

                <th colspan="2" class="th-group group-sep" style="color:#1d4ed8;">
                    Реализация визитов
                </th>

                <th colspan="3" class="th-group group-sep" style="color:var(--purple);">
                    Врачи
                </th>

                <th colspan="3" class="th-group group-sep" style="color:var(--sky);">
                    Аптеки
                </th>

                <th rowspan="2" class="group-sep" style="text-align:right;cursor:default;white-space:nowrap;color:var(--text2);">
                    Ср. длит.
                </th>
            </tr>

            {{-- Строка 2: подзаголовки, сортировка кликом --}}
            <tr>
                <th :class="{ 'sort-active': sort === 'total_visits' }" class="group-sep" style="text-align:right;" @click="sortBy('total_visits')">
                    <span style="display:flex;align-items:center;justify-content:flex-end;gap:2px;">
                        Факт<span class="sort-ico" x-text="sortIco('total_visits')"></span>
                    </span>
                </th>
                <th :class="{ 'sort-active': sort === 'call_pct' }" style="text-align:right;" @click="sortBy('call_pct')">
                    <span style="display:flex;align-items:center;justify-content:flex-end;gap:2px;">
                        % выполн.<span class="sort-ico" x-text="sortIco('call_pct')"></span>
                    </span>
                </th>

                <th :class="{ 'sort-active': sort === 'base_doctors' }" class="group-sep" style="text-align:right;" @click="sortBy('base_doctors')">
                    <span style="display:flex;align-items:center;justify-content:flex-end;gap:2px;">
                        База<span class="sort-ico" x-text="sortIco('base_doctors')"></span>
                    </span>
                </th>
                <th :class="{ 'sort-active': sort === 'doctor_visits' }" style="text-align:right;" @click="sortBy('doctor_visits')">
                    <span style="display:flex;align-items:center;justify-content:flex-end;gap:2px;">
                        Визиты<span class="sort-ico" x-text="sortIco('doctor_visits')"></span>
                    </span>
                </th>
                <th :class="{ 'sort-active': sort === 'freq_pct_doc' }" style="text-align:right;" @click="sortBy('freq_pct_doc')">
                    <span style="display:flex;align-items:center;justify-content:flex-end;gap:2px;">
                        Частота<span class="sort-ico" x-text="sortIco('freq_pct_doc')"></span>
                    </span>
                </th>

                <th :class="{ 'sort-active': sort === 'base_pharmacies' }" class="group-sep" style="text-align:right;" @click="sortBy('base_pharmacies')">
                    <span style="display:flex;align-items:center;justify-content:flex-end;gap:2px;">
                        База<span class="sort-ico" x-text="sortIco('base_pharmacies')"></span>
                    </span>
                </th>
                <th :class="{ 'sort-active': sort === 'pharmacy_visits' }" style="text-align:right;" @click="sortBy('pharmacy_visits')">
                    <span style="display:flex;align-items:center;justify-content:flex-end;gap:2px;">
                        Визиты<span class="sort-ico" x-text="sortIco('pharmacy_visits')"></span>
                    </span>
                </th>
                <th :class="{ 'sort-active': sort === 'freq_pct_phar' }" style="text-align:right;" @click="sortBy('freq_pct_phar')">
                    <span style="display:flex;align-items:center;justify-content:flex-end;gap:2px;">
                        Частота<span class="sort-ico" x-text="sortIco('freq_pct_phar')"></span>
                    </span>
                </th>
            </tr>
        </thead>
        <tbody>
        <template x-for="(row, i) in sortedRows" :key="row.id">
        <tr>
            <td class="rank-cell"><span :class="rankClass(i + 1)" x-text="i + 1"></span></td>

            <td>
                <div class="emp-name">
                    <a :href="'/employee/' + row.id"
                       style="color:inherit;text-decoration:none;"
                       onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='inherit'"
                       x-text="row.name"></a>
                </div>
                <div class="emp-pos" x-show="row.position" x-text="row.position"></div>
            </td>

            {{-- Реализация: факт --}}
            <td class="num-cell group-sep">
                <div class="num-big" x-text="fmt(row.total_visits)"></div>
                <div class="pct-sub" x-text="'/ ' + data.callTarget"></div>
                <div class="bar-wrap"><div class="bar-fill" style="background:#3b82f6;" :style="'width:' + visitPct(row) + '%'"></div></div>
            </td>

            {{-- Реализация: % --}}
            <td class="pct-cell">
                <template x-if="data.callTarget > 0">
                    <div>
                        <div class="pct-val" :style="'color:' + pctColor(row.call_pct)" x-text="row.call_pct + '%'"></div>
                        <div class="pct-bar">
                            <div class="pct-bar-fill" :style="'background:' + pctColor(row.call_pct) + ';width:' + Math.min(row.call_pct, 100) + '%'"></div>
                        </div>
                    </div>
                </template>
                <template x-if="data.callTarget <= 0">
                    <span style="color:var(--text3);">—</span>
                </template>
            </td>

            {{-- Врачи: база --}}
            <td class="num-cell group-sep" style="color:var(--purple);">
                <span x-show="row.base_doctors > 0" class="num-big" x-text="row.base_doctors"></span>
                <span x-show="row.base_doctors <= 0" style="color:var(--text3);">—</span>
            </td>

            {{-- Врачи: визиты --}}
            <td class="num-cell" style="color:var(--purple);">
                <span x-show="row.doctor_visits > 0" x-text="fmt(row.doctor_visits)"></span>
                <span x-show="row.doctor_visits <= 0" style="color:var(--text3);">—</span>
            </td>

            {{-- Врачи: частота --}}
            <td class="pct-cell">
                <template x-if="row.freq_target_doc > 0">
                    <div>
                        <div class="pct-val" :style="'color:' + pctColor(row.freq_pct_doc)" x-text="row.freq_pct_doc + '%'"></div>
                        <div style="font-size:11px;color:var(--text3);" x-text="row.doctor_visits + ' / ' + row.freq_target_doc"></div>
                        <div class="pct-bar">
                            <div class="pct-bar-fill" :style="'background:' + pctColor(row.freq_pct_doc) + ';width:' + Math.min(row.freq_pct_doc, 100) + '%'"></div>
                        </div>
                    </div>
                </template>
                <template x-if="row.freq_target_doc <= 0">
                    <span style="color:var(--text3);">—</span>
                </template>
            </td>

            {{-- Аптеки: база --}}
            <td class="num-cell group-sep" style="color:var(--sky);">
                <span x-show="row.base_pharmacies > 0" class="num-big" x-text="row.base_pharmacies"></span>
                <span x-show="row.base_pharmacies <= 0" style="color:var(--text3);">—</span>
            </td>

            {{-- Аптеки: визиты --}}
            <td class="num-cell" style="color:var(--sky);">
                <span x-show="row.pharmacy_visits > 0" x-text="fmt(row.pharmacy_visits)"></span>
                <span x-show="row.pharmacy_visits <= 0" style="color:var(--text3);">—</span>
            </td>

            {{-- Аптеки: частота --}}
            <td class="pct-cell">
                <template x-if="row.freq_target_phar > 0">
                    <div>
                        <div class="pct-val" :style="'color:' + pctColor(row.freq_pct_phar)" x-text="row.freq_pct_phar + '%'"></div>
                        <div style="font-size:11px;color:var(--text3);" x-text="row.pharmacy_visits + ' / ' + row.freq_target_phar"></div>
                        <div class="pct-bar">
                            <div class="pct-bar-fill" :style="'background:' + pctColor(row.freq_pct_phar) + ';width:' + Math.min(row.freq_pct_phar, 100) + '%'"></div>
                        </div>
                    </div>
                </template>
                <template x-if="row.freq_target_phar <= 0">
                    <span style="color:var(--text3);">—</span>
                </template>
            </td>

            {{-- Ср. длительность --}}
            <td class="num-cell group-sep">
                <span x-show="row.avg_duration > 0" style="color:var(--text2);"><span x-text="row.avg_duration"></span><span style="font-size:11px;"> мин</span></span>
                <span x-show="row.avg_duration <= 0" style="color:var(--text3);">—</span>
            </td>
        </tr>
        </template>
        </tbody>
    </table>
    </div>
</div>

</div>

<script>
function leaderboard(initialData, initialMonth) {
    return {
        month: initialMonth,
        loading: false,
        data: initialData,
        sort: 'total_visits',
        dir: 'desc',

        get sortedRows() {
            const rows = [...this.data.rows];
            const s = this.sort, d = this.dir === 'desc' ? -1 : 1;
            rows.sort((a, b) => (a[s] - b[s]) * d);
            return rows;
        },
        sortBy(col) {
            if (this.sort === col) {
                this.dir = this.dir === 'desc' ? 'asc' : 'desc';
            } else {
                this.sort = col;
                this.dir = 'desc';
            }
        },
        sortIco(col) {
            if (this.sort !== col) return '↕';
            return this.dir === 'desc' ? '↓' : '↑';
        },
        rankClass(rank) {
            return rank === 1 ? 'rank-1' : rank === 2 ? 'rank-2' : rank === 3 ? 'rank-3' : 'rank-n';
        },
        pctColor(pct) {
            return pct >= 80 ? '#16a34a' : (pct >= 50 ? '#f59e0b' : '#ef4444');
        },
        visitPct(row) {
            const max = this.data.rows.reduce((m, r) => Math.max(m, r.total_visits), 0) || 1;
            return Math.round(row.total_visits / max * 100);
        },
        fmt(n) {
            return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        },

        exportUrl() {
            const params = new URLSearchParams({ month: this.month });
            return '{{ route('leaderboard.export') }}?' + params.toString();
        },

        async loadMonth() {
            this.loading = true;
            try {
                const params = new URLSearchParams({ month: this.month });
                const res = await fetch('{{ route('leaderboard.data') }}?' + params.toString(), {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });
                if (!res.ok) throw new Error('http_' + res.status);
                this.data = await res.json();

                const url = new URL(window.location.href);
                url.searchParams.set('month', this.month);
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
