@extends('layout')

@section('content')

<x-flash-message />

<style>
.emp-tabs { display:flex;gap:2px;background:#f1f5f9;border-radius:10px;padding:3px; }
.emp-tab  { padding:5px 16px;font-size:13px;font-weight:500;border-radius:7px;border:none;cursor:pointer;transition:all .15s;line-height:1; }
.emp-tab[data-active="true"]  { background:#fff;color:#1e3a8a;font-weight:600;box-shadow:0 1px 4px rgba(0,0,0,.10); }
.emp-tab[data-active="false"] { background:transparent;color:#64748b; }
.emp-tab[data-active="false"]:hover { color:#1e3a8a; }

.bind-row { display:flex;align-items:center;justify-content:space-between;padding:9px 0;border-bottom:1px solid #f5f5f5; }
.bind-row:last-child { border-bottom:none; }
.bind-label { font-size:11px;color:#9ca3af;margin-bottom:2px;text-transform:uppercase;letter-spacing:.05em; }
.bind-value { font-size:13px;font-weight:500;color:#1f2937; }
.bind-empty { font-size:13px;color:#d1d5db; }
.bind-action { font-size:12px;color:#2563eb;text-decoration:none;white-space:nowrap;flex-shrink:0; }
.bind-action:hover { text-decoration:underline; }

/* Skeleton / shimmer для лениво загружаемых блоков CRM и KMP */
.skeleton-card { margin-top:16px;background:#fff;border-radius:12px;border:1px solid #f0f0f0;
                 box-shadow:0 1px 3px rgba(0,0,0,.06);padding:18px;display:flex;flex-direction:column;gap:12px; }
.skeleton-line { height:14px;border-radius:6px;background:linear-gradient(90deg,#f1f5f9 25%,#f8fafc 50%,#f1f5f9 75%);
                 background-size:200% 100%;animation:skeleton-shimmer 1.4s ease-in-out infinite; }
@keyframes skeleton-shimmer { 0% { background-position:200% 0; } 100% { background-position:-200% 0; } }
</style>

<div x-data="{
        tab: 'profile',
        visitsLoaded: false, visitsLoading: false, visitsHtml: '',
        kmpLoaded: false, kmpLoading: false, kmpHtml: '',
        loadVisits() {
            if (this.visitsLoaded || this.visitsLoading) return;
            this.visitsLoading = true;
            fetch('{{ route('employees.visitStats', $employee->id) }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.text())
                .then(html => { this.visitsHtml = html; this.visitsLoaded = true; })
                .catch(() => { this.visitsHtml = '<div style=\'padding:16px;color:#9ca3af;font-size:13px;\'>Не удалось загрузить данные</div>'; this.visitsLoaded = true; })
                .finally(() => this.visitsLoading = false);
        },
        loadKmp() {
            if (this.kmpLoaded || this.kmpLoading) return;
            this.kmpLoading = true;
            fetch('{{ route('employees.kmpStats', $employee->id) }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.text())
                .then(html => { this.kmpHtml = html; this.kmpLoaded = true; })
                .catch(() => { this.kmpHtml = '<div style=\'padding:16px;color:#9ca3af;font-size:13px;\'>Не удалось загрузить данные</div>'; this.kmpLoaded = true; })
                .finally(() => this.kmpLoading = false);
        },
     }" style="width:100%;padding:4px 0;">

    {{-- Назад + вкладки в одной строке --}}
    <div style="display:flex;align-items:center;gap:16px;margin-bottom:20px;">
        <a href="javascript:void(0);" onclick="window.history.back();"
           style="display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:500;flex-shrink:0;
                  color:#64748b;background:#fff;padding:5px 12px;border:1px solid #e2e8f0;
                  border-radius:8px;box-shadow:0 1px 2px rgba(0,0,0,.04);text-decoration:none;transition:all .15s;"
           onmouseover="this.style.color='#4f46e5';this.style.borderColor='#c7d2fe';this.style.background='#f5f3ff';"
           onmouseout="this.style.color='#64748b';this.style.borderColor='#e2e8f0';this.style.background='#fff';">
            <svg style="width:13px;height:13px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Назад
        </a>
        <div class="emp-tabs">
            <button class="emp-tab" :data-active="tab === 'profile'" @click="tab = 'profile'">
                Профиль
        </button>
        @if($hasVisits)
        <button class="emp-tab" :data-active="tab === 'visits'" @click="tab = 'visits'; loadVisits()">
            Визиты CRM
        </button>
        @endif
        @if($hasKmp)
        <button class="emp-tab" :data-active="tab === 'kmp'" @click="tab = 'kmp'; loadKmp()">
            KMP Продажи
        </button>
        @endif
        </div>{{-- /emp-tabs --}}
    </div>{{-- /flex row --}}

    {{-- ── Профиль ── --}}
    <div x-show="tab === 'profile'">
        <div style="display:flex;flex-wrap:wrap;gap:24px;width:100%;">

            {{-- Левая колонка --}}
            <div style="flex:0 0 38%;min-width:280px;max-width:420px;display:flex;flex-direction:column;gap:16px;">

                <x-employee-info :employee="$employee" :currentStatus="$currentStatus" />

                {{-- Привязки CRM / KMP --}}
                @can('admin')
                <div style="background:#fff;border-radius:12px;border:1px solid #f0f0f0;
                            box-shadow:0 1px 3px rgba(0,0,0,.06);padding:14px 18px;">
                    <div style="font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;
                                color:#94a3b8;margin-bottom:10px;">Внешние системы</div>

                    <div class="bind-row">
                        <div>
                            <div class="bind-label">CRM</div>
                            @if($employee->crmIds->isNotEmpty())
                                <div class="bind-value">
                                    ID: {{ $employee->crmIds->pluck('crm_employee_id')->implode(', ') }}
                                </div>
                            @else
                                <div class="bind-empty">Не привязан</div>
                            @endif
                        </div>
                        <a href="{{ route('admin.crm-mapping') }}" class="bind-action">
                            {{ $employee->crmIds->isNotEmpty() ? 'Изменить' : 'Привязать' }}
                        </a>
                    </div>

                    <div class="bind-row">
                        <div>
                            <div class="bind-label">KMP</div>
                            @if($employee->kmpNames->isNotEmpty())
                                <div class="bind-value">
                                    {{ $employee->kmpNames->pluck('kmp_employee_name')->implode(', ') }}
                                </div>
                            @else
                                <div class="bind-empty">Не привязан</div>
                            @endif
                        </div>
                        <a href="{{ route('admin.kmp-mapping') }}" class="bind-action">
                            {{ $employee->kmpNames->isNotEmpty() ? 'Изменить' : 'Привязать' }}
                        </a>
                    </div>
                </div>
                @endcan

            </div>

            {{-- Правая колонка --}}
            <div style="flex:1 1 400px;min-width:300px;display:flex;flex-direction:column;gap:20px;">

                <x-territory-assignment
                    :employee="$employee"
                    :bricks="$bricks"
                    :selectedBricks="$selectedBricks"
                    :availableTerritories="$availableTerritories"
                    :territoriesHistory="$territoriesHistory"
                    :lastTerritory="$lastTerritory"
                />

                <x-tablet-assignment
                    :employee="$employee"
                    :availableTablets="$availableTablets"
                    :tabletHistories="$tabletHistories"
                    :lastTablet="$lastTablet"
                />

            </div>
        </div>
    </div>

    {{-- ── Визиты CRM ── --}}
    @if($hasVisits)
    <div x-show="tab === 'visits'" style="max-width:560px;">
        <template x-if="!visitsLoaded">
            <div class="skeleton-card">
                <div class="skeleton-line" style="width:40%;"></div>
                <div class="skeleton-line" style="height:52px;"></div>
                <div class="skeleton-line" style="height:52px;"></div>
                <div class="skeleton-line" style="width:70%;"></div>
            </div>
        </template>
        <div x-show="visitsLoaded" x-html="visitsHtml"></div>
        <div style="margin-top:12px;text-align:right;">
            <a href="{{ route('calls.index', ['employee_id' => $employee->id]) }}"
               style="font-size:13px;color:#2563eb;text-decoration:none;font-weight:500;"
               onmouseover="this.style.textDecoration='underline'"
               onmouseout="this.style.textDecoration='none'">
                Открыть полный отчёт по визитам →
            </a>
        </div>
    </div>
    @endif

    {{-- ── KMP Продажи ── --}}
    @if($hasKmp)
    <div x-show="tab === 'kmp'" style="max-width:560px;">
        <template x-if="!kmpLoaded">
            <div class="skeleton-card">
                <div class="skeleton-line" style="width:40%;"></div>
                <div class="skeleton-line" style="height:52px;"></div>
                <div class="skeleton-line" style="height:52px;"></div>
                <div class="skeleton-line" style="width:70%;"></div>
            </div>
        </template>
        <div x-show="kmpLoaded" x-html="kmpHtml"></div>
    </div>
    @endif

</div>

@endsection
