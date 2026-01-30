@extends('layout')

@section('content')
<x-back-button />
<div class="container mx-auto py-6">
    <x-flash-message />
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-4">Детали территории</h2>
        <div x-data="{ showForm: false }">
            <p>
                <strong>Сотрудник:</strong>
                {{ $employee->full_name ?? 'Не назначен' }}

                @if(!$employee)
                    <button @click="showForm = !showForm"
                        class="ml-2 px-3 py-1 bg-blue-500 text-white text-sm rounded hover:bg-blue-700 transition">
                        Назначить
                    </button>
                @endif
            </p>

            <!-- Форма назначения сотрудника -->
            <div x-show="showForm" class="mt-4 p-4 bg-gray-100 border rounded">
                <form action="{{ route('assign.employee', $territory->id) }}" method="POST">
                    @csrf
                    <label for="employee" class="block text-sm font-medium text-gray-600">Выберите сотрудника</label>
                    <select id="employee" name="employee_id" class="w-full p-3 border rounded-lg mt-2">
                        @foreach ($availableEmployees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                        @endforeach
                    </select>

                    <input type="date" name="assigned_at" class="mt-2 w-full p-2 border rounded-lg" value="{{ now()->format('Y-m-d') }}">

                    <button type="submit" class="mt-4 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                        Назначить
                    </button>
                </form>
            </div>
        </div>
        <p><strong>Территория:</strong> {{ $territory->territory_name }}</p>
        <p><strong>Позиция:</strong> {{ $territory->role }}</p>
        <p><strong>Группа:</strong> {{ $territory->team }}</p>
        <p><strong>Департамент:</strong> {{ $territory->department }}</p>
        <p><strong>Город:</strong> {{ $territory->city }}</p>


        {{-- <p><strong>Менеджер:</strong> <a href="{{route('employees.show', $territory->parent->employee->id)}}" class="text-blue-500 hover:underline">{{ $territory->parent && $territory->parent->employee ? $territory->parent->employee->first_name . ' ' .$territory->parent->employee->last_name : '' }}</a></p> --}}

        <p>
            <strong>Менеджер:</strong>
            @if($territory->parent?->employee)
                <a href="{{ route('employees.show', $territory->parent->employee->id) }}"
                class="text-blue-500 hover:underline">
                    {{ $territory->parent->employee->first_name . ' ' . $territory->parent->employee->last_name }}
                </a>
            @else
                <span class="text-gray-500">Нет менеджера</span>
            @endif
        </p>


        <x-edit-territory-button :territory="$territory"/>
        <br>
        @if ($territory->role === 'Rep')
            <x-checkbox :bricks="$bricks" :selectedBricks="$selectedBricks" :territory="$territory" />
        @else
            <x-child-territories :territory="$territory" />
        @endif
        <h3 class="text-xl font-semibold mt-6">История пользователей</h3>
        <table class="w-full border-collapse border border-gray-300">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border border-gray-300 px-4 py-2">ID</th>
                    <th class="border border-gray-300 px-4 py-2">ФИО</th>
                    <th class="border border-gray-300 px-4 py-2">Дата привзяки</th>
                    <th class="border border-gray-300 px-4 py-2">Дата отвязки</th>
                </tr>
            </thead>
            <tbody>
                @foreach($previousUsers as $record)
                    <tr>
                        <td class="border border-gray-300 px-4 py-2">{{$record->pivot->id}}</td>
                        <td class="border border-gray-300 px-4 py-2">
                            <a href="{{ route('employees.show', $record->id) }}" class="text-blue-600 hover:underline">
                                {{ $record->full_name }}
                            </a>
                        </td>
                        <td class="border border-gray-300 px-4 py-2">
                            {{ $record->pivot->assigned_at ? \Carbon\Carbon::parse($record->pivot->assigned_at)->format('d.m.Y') : '-' }}
                            <button onclick="openEditModal('{{ $record->pivot->id }}', 'assigned_at', '{{ $record->pivot->assigned_at }}', 'territory')"
                                class="ml-2 text-blue-500 hover:underline text-sm">✎</button>
                        </td>
                        <td class="border border-gray-300 px-4 py-2">
                            {{ $record->pivot->unassigned_at ? \Carbon\Carbon::parse($record->pivot->unassigned_at)->format('d.m.Y') : 'Текущий пользователь' }}
                            <button onclick="openEditModal('{{ $record->pivot->id }}', 'unassigned_at', '{{ $record->pivot->unassigned_at }}', 'territory')"
                                class="ml-2 text-blue-500 hover:underline text-sm">✎</button>
                        </td>
                    </tr>
                @endforeach

                <x-data-edit-modal />


            </tbody>
        </table>

    </div>
</div>
@endsection

