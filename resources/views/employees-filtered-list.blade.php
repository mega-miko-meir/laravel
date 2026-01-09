@extends('layout')

@section('content')
    <h1 class="text-2xl font-bold mb-4 mt-10">{{ $title }}</h1>

    <table class="min-w-full bg-white shadow rounded">
        <thead>
            <tr>
                <th class="px-4 py-2">ФИО</th>
                <th class="px-4 py-2">Имя</th>
                <th class="px-4 py-2">Фамилия</th>
                <th class="px-4 py-2">Тип события</th>
                <th class="px-4 py-2">Дата</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($employees as $emp)
                <tr class="border-b">
                    <td class="px-4 py-2">
                        <a href="/employee/{{ $emp->id }}" class="text-blue-500 hover:underline">
                        {{ $emp->full_name }}
                    </a>
                    </td>
                    {{-- <td class="px-4 py-2">{{ $emp->first_name }}</td> --}}

                    <td class="px-4 py-2">{{ $emp->employee_territory()->latest('assigned_at')->first()->team ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $emp->last_name }}</td>
                    <td class="px-4 py-2">{{ $emp->event_type }}</td>
                    <td class="px-4 py-2">{{ \Carbon\Carbon::parse($emp->event_date)->format('d.m.Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
