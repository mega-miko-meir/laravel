@extends('layout')

@section('content')

<div style="max-width:640px;margin:32px auto 0;">

    {{-- Заголовок --}}
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
        <a href="/territories"
           style="width:32px;height:32px;display:inline-flex;align-items:center;justify-content:center;
                  border-radius:8px;color:#6b7280;text-decoration:none;border:1px solid #e5e7eb;background:#fff;"
           onmouseover="this.style.background='#f9fafb';"
           onmouseout="this.style.background='#fff';">
            <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 style="font-size:20px;font-weight:700;color:#111827;margin:0;">
            {{ isset($territory) ? 'Редактировать территорию' : 'Создать территорию' }}
        </h1>
    </div>

    <x-territory-form
        :territory="$territory ?? null"
        :parentTerritories="$parentTerritories ?? null"
        action="{{ isset($territory) ? route('territory.edit', $territory->id) : route('territory.create') }}"
        method="{{ isset($territory) ? 'PUT' : 'POST' }}"
    />

</div>

@endsection
