@extends('layout')

@section('content')
<style>
.dvp-chevron { transition: transform .15s; }
.dvp-chevron-open { transform: rotate(90deg); }
</style>
<br>

<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:6px;">
    <h1 style="font-size:20px;font-weight:700;color:#111827;margin:0;">План двойных визитов РМ</h1>
</div>
<p style="font-size:13px;color:#9ca3af;margin-bottom:20px;">
    План на медпреда — 2 визита в месяц; в месяце приёма на работу — по дате приёма
    (до 10 числа — 2, с 11 по 24 — 1, с 25 и позже — 0). Факт — уникальные дни двойного
    визита (медпред + дата) из Nobel CRM. В общий факт РМ каждый медпред даёт не более
    100% своего личного плана — перевыполнение одного не маскирует недовыполнение другого.
</p>

<form method="GET" action="{{ route('admin.double-visit-plan') }}"
      style="display:flex;align-items:flex-end;gap:10px;flex-wrap:wrap;margin-bottom:16px;">
    <div>
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;">С даты</label>
        <input type="date" name="date_from" value="{{ $from }}"
               style="padding:7px 10px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;">
    </div>
    <div>
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;">По дату</label>
        <input type="date" name="date_to" value="{{ $to }}"
               style="padding:7px 10px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;">
    </div>
    <button type="submit"
            style="padding:8px 16px;background:#2563eb;color:#fff;border:none;border-radius:8px;
                   font-size:13px;font-weight:600;cursor:pointer;"
            onmouseover="this.style.background='#1d4ed8';" onmouseout="this.style.background='#2563eb';">
        Показать
    </button>
    @if(!$error)
        <a href="{{ route('admin.double-visit-plan.export', ['date_from' => $from, 'date_to' => $to]) }}"
           style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;
                  background:#fff;color:#374151;border:1px solid #e5e7eb;border-radius:8px;
                  font-size:13px;font-weight:600;text-decoration:none;"
           onmouseover="this.style.background='#f9fafb';" onmouseout="this.style.background='#fff';">
            Выгрузить в Excel
        </a>
    @endif
</form>

@php
    function kpiColor($kpi) {
        return $kpi === null ? '#9ca3af' : ($kpi >= 100 ? '#16a34a' : ($kpi >= 60 ? '#d97706' : '#dc2626'));
    }
    function fmtKpi($kpi) {
        return $kpi === null ? '—' : str_replace('.', ',', $kpi).'%';
    }
    function repStatusTooltip($status) {
        if (!$status || !$status->last_hired_date) {
            return 'Нет данных о приёме на работу';
        }
        $lines = ['Принят: ' . \Carbon\Carbon::parse($status->last_hired_date)->format('d.m.Y')];
        if (!$status->is_active) {
            $label = $status->inactive_type === 'maternity_leave' ? 'В декрете с' : 'Уволен(а)';
            $lines[] = $label . ': ' . \Carbon\Carbon::parse($status->inactive_since)->format('d.m.Y');
        }
        return implode("\n", $lines);
    }
@endphp

@if($error)
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:14px 18px;color:#b91c1c;font-size:13px;">
        {{ $error }}
    </div>
@else
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
            <tbody x-data="{ openRm: null }">
                @forelse($report as $r)
                    <tr @click="openRm = (openRm === {{ $r->rm_id }} ? null : {{ $r->rm_id }})"
                        style="border-bottom:1px solid #f9fafb;cursor:pointer;"
                        onmouseover="this.style.background='#fafafa';" onmouseout="this.style.background='none';">
                        <td style="padding:10px 16px;color:#9ca3af;">
                            <svg class="dvp-chevron" :class="{ 'dvp-chevron-open': openRm === {{ $r->rm_id }} }"
                                 style="width:12px;height:12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </td>
                        <td style="padding:10px 16px;font-weight:600;">{{ $r->rm_name }}</td>
                        <td style="padding:10px 16px;color:#6b7280;">{{ $r->territory }}</td>
                        <td style="padding:10px 16px;text-align:right;font-weight:600;color:#374151;">{{ $r->fact }}</td>
                        <td style="padding:10px 16px;text-align:right;font-weight:600;color:#374151;">{{ $r->plan }}</td>
                        <td style="padding:10px 16px;text-align:right;font-weight:700;color:{{ kpiColor($r->kpi_percent) }};">
                            {{ fmtKpi($r->kpi_percent) }}
                        </td>
                    </tr>
                    <tr x-show="openRm === {{ $r->rm_id }}" x-cloak style="border-bottom:1px solid #f9fafb;">
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
                                    @forelse($r->reps as $rep)
                                        <tr style="border-bottom:1px solid #f0f0f0;">
                                            <td style="padding:7px 14px;">
                                                <span style="border-bottom:1px dotted #9ca3af;cursor:help;"
                                                      title="{{ repStatusTooltip($rep->status) }}">{{ $rep->rep_name }}</span>
                                            </td>
                                            <td style="padding:7px 14px;text-align:right;">{{ $rep->fact }}</td>
                                            <td style="padding:7px 14px;text-align:right;">{{ $rep->plan }}</td>
                                            <td style="padding:7px 14px;text-align:right;font-weight:600;color:{{ kpiColor($rep->kpi_percent) }};">
                                                {{ fmtKpi($rep->kpi_percent) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" style="padding:10px 14px;color:#9ca3af;">Нет медпредов с планом или фактом за период</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:32px;text-align:center;color:#9ca3af;font-size:13px;">Нет данных за этот период</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endif

@endsection
