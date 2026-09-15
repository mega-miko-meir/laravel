@extends('layout')

@section('content')
<style>
.dvp-chevron { transition: transform .15s; }
.dvp-chevron-open { transform: rotate(90deg); }
.dvp-loading { opacity:.6; pointer-events:none; transition:opacity .15s; }
</style>
<br>

<div x-data='doubleVisitPlan(@json($initialData), "{{ $month }}")' :class="{ 'dvp-loading': loading }">

<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:6px;">
    <h1 style="font-size:20px;font-weight:700;color:#111827;margin:0;">План двойных визитов РМ</h1>
</div>
<p style="font-size:13px;color:#9ca3af;margin-bottom:20px;">
    План на медпреда — 2 визита в месяц; в месяце приёма на работу — по дате приёма
    (до 10 числа — 2, с 11 по 24 — 1, с 25 и позже — 0). Факт — уникальные дни двойного
    визита (медпред + дата) из Nobel CRM. В общий факт РМ каждый медпред даёт не более
    100% своего личного плана — перевыполнение одного не маскирует недовыполнение другого.
</p>

<div style="display:flex;align-items:flex-end;gap:10px;flex-wrap:wrap;margin-bottom:16px;">
    <div>
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;">Месяц</label>
        <input type="month" x-model="month"
               style="padding:7px 10px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;">
    </div>
    <button type="button" @click="loadMonth()" :disabled="loading"
            style="padding:8px 16px;background:#2563eb;color:#fff;border:none;border-radius:8px;
                   font-size:13px;font-weight:600;cursor:pointer;"
            onmouseover="if(!this.disabled) this.style.background='#1d4ed8';" onmouseout="this.style.background='#2563eb';">
        <span x-text="loading ? 'Загрузка…' : 'Показать'"></span>
    </button>
    <a :href="exportUrl()" x-show="!data.error"
       style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;
              background:#fff;color:#374151;border:1px solid #e5e7eb;border-radius:8px;
              font-size:13px;font-weight:600;text-decoration:none;"
       onmouseover="this.style.background='#f9fafb';" onmouseout="this.style.background='#fff';">
        Выгрузить в Excel
    </a>
</div>

<div x-show="data.error" x-cloak style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:14px 18px;color:#b91c1c;font-size:13px;" x-text="data.error"></div>

<div x-show="!data.error">
    <p style="font-size:12px;color:#9ca3af;margin-bottom:10px;">Клик по строке РМ — раскрыть список медпредов.</p>
    <div style="background:#fff;border:1px solid #f0f0f0;border-radius:12px;
                box-shadow:0 1px 3px rgba(0,0,0,.05);overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#f9fafb;border-bottom:1px solid #f0f0f0;">
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;width:20px;"></th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">РМ / Медпред</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Территория</th>
                    <th style="padding:10px 16px;text-align:right;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Факт</th>
                    <th style="padding:10px 16px;text-align:right;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">План</th>
                    <th style="padding:10px 16px;text-align:right;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">% KPI</th>
                </tr>
            </thead>
            <tbody>
                <template x-if="data.report.length === 0">
                    <tr>
                        <td colspan="6" style="padding:32px;text-align:center;color:#9ca3af;font-size:13px;">Нет данных за этот период</td>
                    </tr>
                </template>
            </tbody>
            {{-- <tbody> как корень x-for (а не <tr>) — может содержать НЕСКОЛЬКО
                 строк за одну итерацию (основная строка РМ + раскрываемая строка
                 деталей), не ломая структуру таблицы и не искажая ширины колонок
                 между разными РМ (в отличие от варианта с вложенной таблицей). --}}
            <template x-for="r in data.report" :key="r.rm_id">
                <tbody>
                    <tr @click="openRm = (openRm === r.rm_id ? null : r.rm_id)"
                        style="border-bottom:1px solid #f9fafb;cursor:pointer;"
                        onmouseover="this.style.background='#fafafa';" onmouseout="this.style.background='none';">
                        <td style="padding:10px 16px;color:#9ca3af;width:20px;">
                            <svg class="dvp-chevron" :class="{ 'dvp-chevron-open': openRm === r.rm_id }"
                                 style="width:12px;height:12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </td>
                        <td style="padding:10px 16px;font-weight:600;" x-text="r.rm_name"></td>
                        <td style="padding:10px 16px;color:#6b7280;" x-text="r.territory"></td>
                        <td style="padding:10px 16px;text-align:right;font-weight:600;color:#374151;" x-text="r.fact"></td>
                        <td style="padding:10px 16px;text-align:right;font-weight:600;color:#374151;" x-text="r.plan"></td>
                        <td style="padding:10px 16px;text-align:right;font-weight:700;" :style="'color:' + kpiColor(r.kpi_percent)" x-text="fmtKpi(r.kpi_percent)"></td>
                    </tr>
                    <tr x-show="openRm === r.rm_id" x-cloak style="border-bottom:1px solid #f9fafb;">
                        <td></td>
                        <td colspan="5" style="padding:0 16px 10px 16px;">
                            <table style="width:100%;border-collapse:collapse;font-size:12px;background:#f9fafb;border-radius:8px;overflow:hidden;">
                                <thead>
                                    <tr style="border-bottom:1px solid #f0f0f0;">
                                        <th style="padding:7px 14px;text-align:left;font-weight:600;color:#9ca3af;">Медпред</th>
                                        <th style="padding:7px 14px;text-align:right;font-weight:600;color:#9ca3af;">Факт</th>
                                        <th style="padding:7px 14px;text-align:right;font-weight:600;color:#9ca3af;">План</th>
                                        <th style="padding:7px 14px;text-align:right;font-weight:600;color:#9ca3af;">% KPI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-if="r.reps.length === 0">
                                        <tr><td colspan="4" style="padding:10px 14px;color:#9ca3af;">Нет медпредов с планом или фактом за период</td></tr>
                                    </template>
                                    <template x-for="rep in r.reps" :key="rep.employee_id">
                                        <tr style="border-bottom:1px solid #f0f0f0;">
                                            <td style="padding:7px 14px;">
                                                <span style="border-bottom:1px dotted #9ca3af;cursor:help;"
                                                      :title="repTooltip(rep.status)" x-text="rep.rep_name"></span>
                                            </td>
                                            <td style="padding:7px 14px;text-align:right;" x-text="rep.fact"></td>
                                            <td style="padding:7px 14px;text-align:right;" x-text="rep.plan"></td>
                                            <td style="padding:7px 14px;text-align:right;font-weight:600;" :style="'color:' + kpiColor(rep.kpi_percent)" x-text="fmtKpi(rep.kpi_percent)"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </template>
        </table>
    </div>
</div>

</div>

<script>
function doubleVisitPlan(initialData, initialMonth) {
    return {
        month: initialMonth,
        loading: false,
        data: initialData,
        openRm: null,

        fmtKpi(kpi) {
            return (kpi === null || kpi === undefined) ? '—' : String(kpi).replace('.', ',') + '%';
        },
        kpiColor(kpi) {
            if (kpi === null || kpi === undefined) return '#9ca3af';
            return kpi >= 100 ? '#16a34a' : (kpi >= 60 ? '#d97706' : '#dc2626');
        },
        fmtDate(d) {
            if (!d) return '';
            const dt = new Date(d);
            return String(dt.getDate()).padStart(2, '0') + '.' + String(dt.getMonth() + 1).padStart(2, '0') + '.' + dt.getFullYear();
        },
        repTooltip(status) {
            if (!status || !status.last_hired_date) return 'Нет данных о приёме на работу';
            const lines = ['Принят: ' + this.fmtDate(status.last_hired_date)];
            if (!status.is_active) {
                const label = status.inactive_type === 'maternity_leave' ? 'В декрете с' : 'Уволен(а)';
                lines.push(label + ': ' + this.fmtDate(status.inactive_since));
            }
            return lines.join('\n');
        },

        exportUrl() {
            const params = new URLSearchParams({ month: this.month });
            return '{{ route('admin.double-visit-plan.export') }}?' + params.toString();
        },

        async loadMonth() {
            this.loading = true;
            try {
                const params = new URLSearchParams({ month: this.month });
                const res = await fetch('{{ route('admin.double-visit-plan.data') }}?' + params.toString(), {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });
                if (!res.ok) throw new Error('http_' + res.status);
                const json = await res.json();
                this.data = json;
                this.openRm = null;

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
