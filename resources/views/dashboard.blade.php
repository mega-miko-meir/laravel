@extends('layout')

@section('content')

<h1 style="font-size:20px;font-weight:700;color:#111827;margin-bottom:20px;">Дашборд</h1>

@php
    $cards = [
        ['route' => 'hired_total',        'label' => 'Всего сотрудников',          'value' => $hired_total,        'color' => '#2563eb', 'bg' => '#eff6ff'],
        ['route' => 'on_maternity_leave', 'label' => 'В декрете',                  'value' => $on_maternity_leave, 'color' => '#9333ea', 'bg' => '#faf5ff'],
        ['route' => 'hired_this_month',   'label' => 'Нанятые в этом месяце',      'value' => $hired_this_month,   'color' => '#16a34a', 'bg' => '#f0fdf4'],
        ['route' => 'fired_this_month',   'label' => 'Уволенные в этом месяце',    'value' => $fired_this_month,   'color' => '#dc2626', 'bg' => '#fef2f2'],
        ['route' => 'hired_last_month',   'label' => 'Нанятые в прошлом месяце',   'value' => $hired_last_month,   'color' => '#16a34a', 'bg' => '#f0fdf4'],
        ['route' => 'fired_last_month',   'label' => 'Уволенные в прошлом месяце', 'value' => $fired_last_month,   'color' => '#dc2626', 'bg' => '#fef2f2'],
        ['route' => 'hired_this_year',    'label' => 'Нанятые в этом году',        'value' => $hired_this_year,    'color' => '#16a34a', 'bg' => '#f0fdf4'],
        ['route' => 'fired_this_year',    'label' => 'Уволенные в этом году',      'value' => $fired_this_year,    'color' => '#dc2626', 'bg' => '#fef2f2'],
    ];
@endphp

<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;max-width:680px;">
    @foreach($cards as $card)
        <a href="{{ route('employees.filtered', $card['route']) }}"
           style="display:block;background:#fff;border:1px solid #f0f0f0;border-radius:12px;
                  padding:18px 20px;text-decoration:none;
                  box-shadow:0 1px 3px rgba(0,0,0,.05);
                  transition:box-shadow .15s,transform .15s;"
           onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,.08)';this.style.transform='translateY(-1px)';"
           onmouseout="this.style.boxShadow='0 1px 3px rgba(0,0,0,.05)';this.style.transform='translateY(0)';">

            <p style="font-size:12px;color:#6b7280;font-weight:500;margin-bottom:8px;">
                {{ $card['label'] }}
            </p>
            <div style="display:flex;align-items:flex-end;justify-content:space-between;">
                <p style="font-size:32px;font-weight:700;color:{{ $card['color'] }};line-height:1;">
                    {{ $card['value'] }}
                </p>
                <div style="width:36px;height:36px;border-radius:10px;background:{{ $card['bg'] }};
                            display:flex;align-items:center;justify-content:center;">
                    <svg style="width:18px;height:18px;color:{{ $card['color'] }};"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        @if(str_contains($card['route'], 'hired') || $card['route'] === 'hired_total')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        @elseif($card['route'] === 'on_maternity_leave')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                        @endif
                    </svg>
                </div>
            </div>
        </a>
    @endforeach
</div>

@endsection
