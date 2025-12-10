@extends('layout')

@section('content')

<div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Дашборд</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        {{-- Всего сотрудников --}}
        <a href="{{ route('employees.filtered', 'hired_total') }}"
           class="block bg-white shadow rounded-xl p-5 hover:shadow-lg transition">
            <div class="text-gray-500 text-sm">Всего сотрудников</div>
            <div class="text-3xl font-bold text-blue-600 mt-2">{{ $hired_total }}</div>
        </a>

        {{-- В декрете --}}
        <a href="{{ route('employees.filtered', 'on_maternity_leave') }}"
           class="block bg-white shadow rounded-xl p-5 hover:shadow-lg transition">
            <div class="text-gray-500 text-sm">В декрете</div>
            <div class="text-3xl font-bold text-purple-600 mt-2">{{ $on_maternity_leave }}</div>
        </a>

        {{-- Нанятые в этом месяце --}}
        <a href="{{ route('employees.filtered', 'hired_this_month') }}"
           class="block bg-white shadow rounded-xl p-5 hover:shadow-lg transition">
            <div class="text-gray-500 text-sm">Нанятые в этом месяце</div>
            <div class="text-3xl font-bold text-green-600 mt-2">{{ $hired_this_month }}</div>
        </a>

        {{-- Уволенные в этом месяце --}}
        <a href="{{ route('employees.filtered', 'fired_this_month') }}"
           class="block bg-white shadow rounded-xl p-5 hover:shadow-lg transition">
            <div class="text-gray-500 text-sm">Уволенные в этом месяце</div>
            <div class="text-3xl font-bold text-red-600 mt-2">{{ $fired_this_month }}</div>
        </a>

        {{-- Нанятые в прошлом месяце --}}
        <a href="{{ route('employees.filtered', 'hired_last_month') }}"
           class="block bg-white shadow rounded-xl p-5 hover:shadow-lg transition">
            <div class="text-gray-500 text-sm">Нанятые в прошлом месяце</div>
            <div class="text-3xl font-bold text-green-600 mt-2">{{ $hired_last_month }}</div>
        </a>

        {{-- Уволенные в прошлом месяце --}}
        <a href="{{ route('employees.filtered', 'fired_last_month') }}"
           class="block bg-white shadow rounded-xl p-5 hover:shadow-lg transition">
            <div class="text-gray-500 text-sm">Уволенные в прошлом месяце</div>
            <div class="text-3xl font-bold text-red-600 mt-2">{{ $fired_last_month }}</div>
        </a>

        {{-- Нанятые в этом году --}}
        <a href="{{ route('employees.filtered', 'hired_this_year') }}"
           class="block bg-white shadow rounded-xl p-5 hover:shadow-lg transition">
            <div class="text-gray-500 text-sm">Нанятые в этом году</div>
            <div class="text-3xl font-bold text-green-600 mt-2">{{ $hired_this_year }}</div>
        </a>

        {{-- Уволенные в этом году --}}
        <a href="{{ route('employees.filtered', 'fired_this_year') }}"
           class="block bg-white shadow rounded-xl p-5 hover:shadow-lg transition">
            <div class="text-gray-500 text-sm">Уволенные в этом году</div>
            <div class="text-3xl font-bold text-red-600 mt-2">{{ $fired_this_year }}</div>
        </a>

    </div>
</div>
@endsection



