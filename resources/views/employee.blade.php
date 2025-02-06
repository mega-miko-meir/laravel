@extends('layout')

@section('content')
<div class="container mx-auto py-6">

    <x-flash-message />
    <x-back-button />

    <!-- Employee Information Section -->
    <x-employee-info :employee="$employee" />

    <!-- Territory Assignment Section -->
     <!-- Передаем переменные в компонент territory-assignment -->
     <x-territory-assignment
        :employee="$employee"
        :bricks="$bricks"
        :selectedBricks="$selectedBricks"
        :availableTerritories="$availableTerritories"
        :territoriesHistory="$territoriesHistory"

    />
    <br>

    <!-- Tablet Assignment Section -->
    <x-tablet-assignment
    :employee="$employee"
    :availableTablets="$availableTablets"
    :tabletHistories="$tabletHistories"
    />

</div>

@endsection


