@extends('layout')

@section('content')

<x-back-button />

<x-flash-message />

<div style="display:flex;flex-wrap:wrap;gap:24px;width:100%;padding:16px 0;">

    {{-- Левая колонка --}}
    <div style="flex:0 0 38%;min-width:280px;max-width:420px;">
        <x-employee-info :employee="$employee" :currentStatus="$currentStatus" />
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

@endsection
