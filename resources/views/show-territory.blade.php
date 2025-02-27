@extends('layout')

@section('content')
<div class="container mx-auto py-6">
    <div class="bg-white shadow-md rounded-lg p-6">
        <x-back-button />
        <h2 class="text-2xl font-bold mb-4">Детали территории</h2>

        <p><strong>Территория:</strong> {{ $territory->territory_name }}</p>
        <p><strong>Группа:</strong> {{ $territory->team }}</p>
        <p><strong>Департамент:</strong> {{ $territory->department }}</p>
        <p><strong>Сотрудник:</strong> {{$territory->employee->full_name ?? 'Не назначен' }}</p>
        <p><strong>Менеджер:</strong> {{ $territory->parent ? $territory->parent->employee->first_name . ' ' .$territory->parent->employee->last_name : '' }}</p>

        <x-edit-territory-button :territory="$territory"/>
        <br>
        @if ($territory->role === 'Rep')
            <x-checkbox :employee="$employee" :bricks="$bricks" :selectedBricks="$selectedBricks" />
        @endif
        <h3 class="text-xl font-semibold mt-6">История пользователей</h3>
        <table class="w-full border-collapse border border-gray-300">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border border-gray-300 px-4 py-2">ФИО</th>
                    <th class="border border-gray-300 px-4 py-2">Дата привзяки</th>
                    <th class="border border-gray-300 px-4 py-2">Дата отвязки</th>
                </tr>
            </thead>
            <tbody>
                @foreach($previousUsers as $record)
                    <tr>
                        <td class="border border-gray-300 px-4 py-2">{{ $record->full_name }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $record->pivot->assigned_at ?? '—' }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $record->pivot->unassigned_at ?? '—' }}</td>
                @endforeach
            </tbody>
        </table>

    </div>
</div>
@endsection

