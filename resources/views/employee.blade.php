@extends('layout')

@section('content')

<x-back-button />
<x-flash-message />

@php
    $hasVisits = !empty($visitStats);
    $hasKmp    = !empty($kmpStats);
    $defaultTab = 'profile';
@endphp

<style>
.emp-tabs { display:flex;gap:2px;background:#f1f5f9;border-radius:10px;padding:4px;width:fit-content;margin-bottom:24px; }
.emp-tab  { padding:7px 20px;font-size:13px;font-weight:500;border-radius:7px;border:none;cursor:pointer;transition:all .15s;line-height:1; }
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
</style>

<div x-data="{ tab: '{{ $defaultTab }}' }" style="width:100%;padding:8px 0;">

    {{-- Tab bar --}}
    <div class="emp-tabs">
        <button class="emp-tab" :data-active="tab === 'profile'" @click="tab = 'profile'">
            Профиль
        </button>
        @if($hasVisits)
        <button class="emp-tab" :data-active="tab === 'visits'" @click="tab = 'visits'">
            Визиты CRM
        </button>
        @endif
        @if($hasKmp)
        <button class="emp-tab" :data-active="tab === 'kmp'" @click="tab = 'kmp'">
            KMP Продажи
        </button>
        @endif
    </div>

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
                            @if($employee->crm_employee_id)
                                <div class="bind-value">ID: {{ $employee->crm_employee_id }}</div>
                            @else
                                <div class="bind-empty">Не привязан</div>
                            @endif
                        </div>
                        <a href="{{ route('admin.crm-mapping') }}" class="bind-action">
                            {{ $employee->crm_employee_id ? 'Изменить' : 'Привязать' }}
                        </a>
                    </div>

                    <div class="bind-row">
                        <div>
                            <div class="bind-label">KMP</div>
                            @if($employee->kmp_employee_name)
                                <div class="bind-value">{{ $employee->kmp_employee_name }}</div>
                            @else
                                <div class="bind-empty">Не привязан</div>
                            @endif
                        </div>
                        <a href="{{ route('admin.kmp-mapping') }}" class="bind-action">
                            {{ $employee->kmp_employee_name ? 'Изменить' : 'Привязать' }}
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
        <x-visit-stats :stats="$visitStats" />
        @if($employee->crm_employee_id)
        <div style="margin-top:12px;text-align:right;">
            <a href="{{ route('calls.index', ['crm_employee_id' => $employee->crm_employee_id]) }}"
               style="font-size:13px;color:#2563eb;text-decoration:none;font-weight:500;"
               onmouseover="this.style.textDecoration='underline'"
               onmouseout="this.style.textDecoration='none'">
                Открыть полный отчёт по визитам →
            </a>
        </div>
        @endif
    </div>
    @endif

    {{-- ── KMP Продажи ── --}}
    @if($hasKmp)
    <div x-show="tab === 'kmp'" style="max-width:560px;">
        <x-kmp-stats :stats="$kmpStats" />
    </div>
    @endif

</div>

@endsection
