@extends('layout')

@section('content')
<br>

<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:6px;">
    <h1 style="font-size:20px;font-weight:700;color:#111827;margin:0;">Таргетные клиенты</h1>
</div>
<p style="font-size:13px;color:#9ca3af;margin-bottom:20px;">
    Таргет-лист (план покрытия) за выбранный месяц — все визиты независимо от статуса,
    это не факт выполненных визитов. Врачи: визит к врачу, ЛПУ/Прочие. Аптеки: визит в аптеку, аптечные учреждения.
</p>

<div x-data='targetClients(@json($doctorsSummary), @json($pharmaciesSummary), @json($departments), "{{ $month }}", "{{ $department }}", @json($error))'>
    <form @submit.prevent="loadMonth()"
          style="display:flex;align-items:flex-end;gap:10px;flex-wrap:wrap;margin-bottom:20px;">
        <div>
            <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;">Месяц</label>
            <input type="month" x-model="month"
                   style="padding:7px 10px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;">
        </div>
        <button type="submit" :disabled="loading"
                style="padding:8px 16px;background:#2563eb;color:#fff;border:none;border-radius:8px;
                       font-size:13px;font-weight:600;cursor:pointer;"
                :style="loading ? 'opacity:.6;cursor:default;' : ''"
                onmouseover="if(!this.disabled) this.style.background='#1d4ed8';" onmouseout="this.style.background='#2563eb';">
            <span x-text="loading ? 'Загрузка…' : 'Показать'"></span>
        </button>
    </form>

    <div x-show="error" x-cloak style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:14px 18px;color:#b91c1c;font-size:13px;margin-bottom:20px;" x-text="error"></div>

    <div x-show="!error">
        {{-- Фильтр по группе — меняет вид мгновенно, на клиенте, без запроса к серверу --}}
        <div style="margin-bottom:16px;">
            <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;">Группа</label>
            <select x-model="dept"
                    style="padding:7px 10px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;min-width:220px;">
                <option value="">Все группы</option>
                <template x-for="d in departments" :key="d">
                    <option :value="d" x-text="d"></option>
                </template>
            </select>
        </div>

        {{-- KPI --}}
        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;max-width:600px;margin-bottom:20px;">
            <div style="background:#fff;border:1px solid #f0f0f0;border-radius:12px;padding:16px 18px;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                <p style="font-size:11px;color:#6b7280;font-weight:500;margin-bottom:6px;">Уникальных врачей</p>
                <p style="font-size:26px;font-weight:700;color:#0ea5e9;line-height:1;" x-text="fmt(doctorsKpi())"></p>
            </div>
            <div style="background:#fff;border:1px solid #f0f0f0;border-radius:12px;padding:16px 18px;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                <p style="font-size:11px;color:#6b7280;font-weight:500;margin-bottom:6px;">Уникальных аптек</p>
                <p style="font-size:26px;font-weight:700;color:#16a34a;line-height:1;" x-text="fmt(pharmaciesKpi())"></p>
            </div>
        </div>

        {{-- Вкладки --}}
        <div style="display:flex;gap:8px;margin-bottom:16px;border-bottom:1px solid #f0f0f0;">
            <button @click="tab = 'doctors'" class="tc-tab" :class="{ 'tc-tab-active': tab === 'doctors' }">
                Врачи (<span x-text="fmt(doctorsKpi())"></span>)
            </button>
            <button @click="tab = 'pharmacies'" class="tc-tab" :class="{ 'tc-tab-active': tab === 'pharmacies' }">
                Аптеки (<span x-text="fmt(pharmaciesKpi())"></span>)
            </button>
        </div>

        {{-- Врачи --}}
        <div x-show="tab === 'doctors'">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:10px;">
                <div style="display:flex;gap:6px;">
                    <button @click="groupBy = 'department'" class="tc-subtab" :class="{ 'tc-subtab-active': groupBy === 'department' }">По отделу / сотруднику</button>
                    <button @click="groupBy = 'specialty'" class="tc-subtab" :class="{ 'tc-subtab-active': groupBy === 'specialty' }">По специальности</button>
                </div>
                <a :href="exportUrl('doctors')" class="tc-export-btn">Выгрузить врачей</a>
            </div>

            <div x-show="groupBy === 'department'">
                @include('components.target-clients-tree', ['treeExpr' => 'visibleTree(doctorsData)'])
            </div>
            <div x-show="groupBy === 'specialty'" x-cloak>
                @include('components.target-clients-specialty', ['listExpr' => 'visibleSpecialty()'])
            </div>
        </div>

        {{-- Аптеки --}}
        <div x-show="tab === 'pharmacies'" x-cloak>
            <div style="display:flex;justify-content:flex-end;margin-bottom:10px;">
                <a :href="exportUrl('pharmacies')" class="tc-export-btn">Выгрузить аптеки</a>
            </div>
            @include('components.target-clients-tree', ['treeExpr' => 'visibleTree(pharmaciesData)'])
        </div>
    </div>
</div>

<style>
.tc-tab {
    padding:8px 16px;background:none;border:none;border-bottom:2px solid transparent;
    font-size:13px;font-weight:600;color:#6b7280;cursor:pointer;margin-bottom:-1px;
}
.tc-tab-active { color:#2563eb;border-bottom-color:#2563eb; }
.tc-subtab {
    padding:6px 12px;background:#fff;border:1px solid #e5e7eb;border-radius:7px;
    font-size:12px;font-weight:600;color:#6b7280;cursor:pointer;
}
.tc-subtab-active { background:#eff6ff;border-color:#bfdbfe;color:#2563eb; }
.tc-export-btn {
    display:inline-flex;align-items:center;gap:6px;padding:8px 16px;
    background:#fff;color:#374151;border:1px solid #e5e7eb;border-radius:8px;
    font-size:13px;font-weight:600;text-decoration:none;
}
.tc-export-btn:hover { background:#f9fafb; }
.tc-chevron { transition: transform .15s; }
.tc-chevron-open { transform: rotate(90deg); }
</style>

<script>
function targetClients(doctorsData, pharmaciesData, departments, initialMonth, initialDept, initialError) {
    return {
        tab: 'doctors',
        groupBy: 'department',
        month: initialMonth,
        dept: initialDept || '',
        departments: departments || [],
        openDepts: {},
        loading: false,
        error: initialError || null,
        doctorsData: doctorsData || { kpi: 0, tree: [], specialtyTree: [], specialtyByDept: {} },
        pharmaciesData: pharmaciesData || { kpi: 0, tree: [] },

        fmt(n) {
            return String(n ?? 0).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        },
        visibleTree(data) {
            if (!this.dept) return data.tree;
            return data.tree.filter(d => d.department === this.dept);
        },
        deptKpi(data) {
            if (!this.dept) return data.kpi;
            const row = data.tree.find(d => d.department === this.dept);
            return row ? row.client_count : 0;
        },
        doctorsKpi() { return this.deptKpi(this.doctorsData); },
        pharmaciesKpi() { return this.deptKpi(this.pharmaciesData); },
        visibleSpecialty() {
            if (!this.dept) return this.doctorsData.specialtyTree || [];
            return (this.doctorsData.specialtyByDept && this.doctorsData.specialtyByDept[this.dept]) || [];
        },
        exportUrl(kind) {
            const route = kind === 'doctors'
                ? '{{ route('admin.target-clients.export.doctors') }}'
                : '{{ route('admin.target-clients.export.pharmacies') }}';
            const params = new URLSearchParams({ month: this.month, department: this.dept || '' });
            return route + '?' + params.toString();
        },
        async loadMonth() {
            this.loading = true;
            try {
                const params = new URLSearchParams({ month: this.month });
                const res = await fetch('{{ route('admin.target-clients.data') }}?' + params.toString(), {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });
                if (!res.ok) throw new Error('http_' + res.status);
                const data = await res.json();

                this.error = data.error;
                this.doctorsData = data.doctorsSummary || { kpi: 0, tree: [], specialtyTree: [], specialtyByDept: {} };
                this.pharmaciesData = data.pharmaciesSummary || { kpi: 0, tree: [] };
                this.departments = data.departments || [];
                this.dept = '';
                this.openDepts = {};

                const url = new URL(window.location.href);
                url.searchParams.set('month', this.month);
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
