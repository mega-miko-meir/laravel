@extends('layout')

@section('content')
<div class="container mx-auto py-6">
    <div class="bg-white shadow-md rounded-lg p-6">
        <x-back-button />
        <h2 class="text-2xl font-bold mb-4">Детали территории</h2>

        <p><strong>Территория:</strong> {{ $territory->territory_name }}</p>
        <p><strong>Группа:</strong> {{ $territory->team }}</p>
        <p><strong>Департамент:</strong> {{ $territory->department }}</p>
        <div x-data="{ showForm: false }">
            <p>
                <strong>Сотрудник:</strong>
                {{ $territory->employee->full_name ?? 'Не назначен' }}

                @if(!$territory->employee)
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

        <p><strong>Менеджер:</strong> {{ $territory->parent && $territory->parent->employee ? $territory->parent->employee->first_name . ' ' .$territory->parent->employee->last_name : '' }}</p>

        <x-edit-territory-button :territory="$territory"/>
        <br>
        @if ($territory->role === 'Rep')
            <x-checkbox :employee="$employee" :bricks="$bricks" :selectedBricks="$selectedBricks" />
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
                            {{ $record->pivot->assigned_at ? \Carbon\Carbon::parse($record->pivot->assigned_at)->format('d.m.Y') : '—' }}
                            <button onclick="openEditModal('{{ $record->pivot->id }}', 'assigned_at', '{{ $record->pivot->assigned_at }}')"
                                class="ml-2 text-blue-500 hover:underline text-sm">✎</button>
                        </td>
                        <td class="border border-gray-300 px-4 py-2">
                            {{ $record->pivot->unassigned_at ? \Carbon\Carbon::parse($record->pivot->unassigned_at)->format('d.m.Y') : '—' }}
                            <button onclick="openEditModal('{{ $record->pivot->id }}', 'unassigned_at', '{{ $record->pivot->unassigned_at }}')"
                                class="ml-2 text-blue-500 hover:underline text-sm">✎</button>
                        </td>
                    </tr>
                @endforeach

                <div id="editModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center">
                    <div class="bg-white p-6 rounded-lg shadow-lg">
                        <h2 class="text-lg font-semibold mb-4">Редактировать дату</h2>
                        <form id="editForm" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" id="record_pivot_id" name="record_pivot_id">
                            <input type="hidden" id="field_name" name="field_name">

                            <label for="new_date" class="block text-sm font-medium text-gray-600">Новая дата:</label>
                            <input type="date" id="new_date" name="date_value" class="w-full p-2 border rounded-lg mt-2">

                            <div class="mt-4 flex justify-end">
                                <button type="button" onclick="closeEditModal()" class="mr-2 px-4 py-2 bg-gray-300 rounded">
                                    Отмена
                                </button>
                                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded">
                                    Сохранить
                                </button>
                            </div>
                        </form>
                    </div>
                </div>


                <script>
                    function openEditModal(recordPivotId, fieldName, currentValue) {
                        document.getElementById('record_pivot_id').value = recordPivotId;
                        document.getElementById('field_name').value = fieldName;
                        document.getElementById('new_date').value = currentValue || '';
                        document.getElementById('editForm').action = `/employee-territory/${recordPivotId}/update`;
                        document.getElementById('editModal').classList.remove('hidden');
                    }

                    function closeEditModal() {
                        document.getElementById('editModal').classList.add('hidden');
                    }
                </script>


            </tbody>
        </table>

    </div>
</div>
@endsection

