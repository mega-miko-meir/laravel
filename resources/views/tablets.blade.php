@extends('layout')

@section('content')
@auth
<x-container class="container mx-auto py-4 px-4">

    {{-- Шапка --}}
    <div class="flex items-center justify-between mb-4">
        <x-header />
        <x-create-tablet-button />
    </div>

    <x-flash-message />

    {{-- Дашборд --}}
    <x-tablet-dashboard />

    {{-- Тулбар: поиск + без планшета + выгрузить --}}
    <x-tablet-toolbar :availableEmployees="$availableEmployees" :count="$count" />

    {{-- Свободные планшеты --}}
    <x-tablet-free-table :freeTablets="$freeTablets" :freeCount="$freeTablets->count()" />

    {{-- Заголовок основной таблицы --}}
    <div class="flex items-center gap-2 mb-2">
        <span class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Все планшеты</span>
        <span class="bg-gray-100 text-gray-600 text-xs font-bold px-2 py-0.5 rounded-full">
            {{ $tablets->count() }}
        </span>
    </div>

    {{-- Основная таблица --}}
    <x-tablet-table :tablets="$tablets" />

</x-container>
@else
    <x-auth-container />
@endauth

<script src="{{ asset('js/search.js') }}"></script>
@endsection
