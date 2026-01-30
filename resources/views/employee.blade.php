@extends('layout')

@section('content')
<x-back-button />
<div class="container mx-auto py-6">

    <x-flash-message />

    <!-- Обертка для двух колонок -->
    <div class="flex flex-wrap lg:flex-nowrap gap-6 w-full">

        <!-- Левая колонка: Employee Information (40%) -->
        <div class="w-full lg:w-2/5 lg:basis-2/5 lg:shrink-0 min-w-0">
            <x-employee-info :employee="$employee" :currentStatus="$currentStatus" />
        </div>

        <!-- Правая колонка: Territory и Tablet (60%) -->
        <div class="w-full lg:w-3/5 lg:basis-3/5 lg:shrink-0 min-w-0 flex flex-col gap-6">

            <!-- Territory Assignment Section -->
            <x-territory-assignment
                :employee="$employee"
                :bricks="$bricks"
                :selectedBricks="$selectedBricks"
                :availableTerritories="$availableTerritories"
                :territoriesHistory="$territoriesHistory"
                :lastTerritory="$lastTerritory"
            />

            <!-- Tablet Assignment Section -->
            <x-tablet-assignment
                :employee="$employee"
                :availableTablets="$availableTablets"
                :tabletHistories="$tabletHistories"
                :lastTablet="$lastTablet"
            />

        </div>
    </div>

</div>
@endsection
