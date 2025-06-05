@extends('layout')

@section('content')
<div class="container mx-auto py-6">
    <div class="bg-white shadow-md rounded-lg p-6">
        <x-back-button />
        <h2 class="text-2xl font-bold mb-4">Детали планшета</h2>

        <p><strong>Модель:</strong> {{ $tablet->model }}</p>
        <p><strong>Серийный номер:</strong> {{ $tablet->invent_number }}</p>
        <p><strong>Серийный номер:</strong> {{ $tablet->serial_number }}</p>
        <p><strong>IMEI номер:</strong> {{ $tablet->imei }}</p>
        <p><strong>Билайн номер:</strong> {{ $tablet->beeline_number }}</p>
        <div x-data="{ showForm: false }" >
            <p>
                <strong>Сотрудник:</strong>
                @if(!$previousUsers->first() || $previousUsers->first()->pivot->returned_at)
                    Не назначен<button @click="showForm = !showForm" class="bg-blue-500 ml-2 px-3 py-1 text-white text-sm rounded hover:bg-blue-700 transition">Назначить</button>
                @else
                     {{$previousUsers ? $previousUsers->first()->full_name : 'Не назначен' }}
                @endif
            </p>
            <div x-show="showForm" class="mt-4 pt-4 bg-gray-100 border rounded">
                <form action="{{route('assign.employee2', $tablet->id)}}" method="POST">
                    @csrf
                    <label for="employee" class="block text-sm font-medium text-gray-600"></label>
                    <select name="employee_id" id="employee_id" class="w-full p-3 border rounded-lg mt-2">
                        @foreach ($availableEmployees as $employee)
                            <option value="{{$employee->id}}">{{$employee->full_name}}</option>
                        @endforeach
                    </select>
                    <input type="date" name="assigned_at" id="assigned_at" value="{{now()->format('Y-m-d')}}" class="mt-2 w-full p-2 border rounded-lg">
                    <button type="submit" class="mt-4 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Assign</button>
                </form>
            </div>
        </div>
        <h3 class="text-xl font-semibold mt-6">История пользователей</h3>
        <table class="w-full border-collapse border border-gray-300">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border border-gray-300 px-4 py-2">ФИО</th>
                    <th class="border border-gray-300 px-4 py-2">Дата выдачи</th>
                    <th class="border border-gray-300 px-4 py-2">Дата возврата</th>
                    <th class="border border-gray-300 px-4 py-2">Выдача (PDF)</th>
                    <th class="border border-gray-300 px-4 py-2">Возврат (PDF)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($previousUsers as $record)
                    <tr>
                        {{-- <td>{{$record->pivot->id}}</td> --}}
                        <td class="border border-gray-300 px-4 py-2">
                            <a href="{{route('employees.show', $record->id)}}" class="text-blue-500 hover:underline">{{ $record->full_name }}</a>
                        </td>
                        <td class="border border-gray-300 px-4 py-2">{{ $record->pivot->assigned_at ? \Carbon\Carbon::parse($record->pivot->assigned_at)->format('d.m.Y')  : '—'}}
                            <button
                                onclick="openEditModal('{{ $record->pivot->id }}', 'assigned_at', '{{ $record->pivot->assigned_at }}', 'tablet')"
                                class="ml-2 text-blue-500 hover:underline text-sm">✎
                            </button>
                        </td>
                        <td class="border border-gray-300 px-4 py-2">{{ $record->pivot->returned_at ? \Carbon\Carbon::parse($record->pivot->returned_at)->format('d.m.Y')  : '—'}}
                            <button
                                onclick="openEditModal('{{ $record->pivot->id }}', 'returned_at', '{{ $record->pivot->returned_at }}', 'tablet')"
                                class="ml-2 text-blue-500 hover:underline text-sm">✎
                            </button>
                        </td>
                        {{-- <td class="border border-gray-300 px-4 py-2">{{ $record->pivot->returned_at ?? '—' }}</td> --}}
                        <td class="border border-gray-300 px-4 py-2">
                            @if($record->pivot->pdf_path)
                                <a href="{{ asset('storage/' . $record->pivot->pdf_path) }}" class="text-blue-500 hover:underline" target="_blank">📄 PDF</a>
                                <button onclick="openPdfModal('{{ $record->pivot->id }}', 'pdf_path', '{{ $record->pivot->pdf_path }}', 'tablet')"
                                    class="ml-2 text-blue-500 hover:underline text-sm">✎</button>
                            @else
                                —
                            @endif
                        </td>
                        <td class="border border-gray-300 px-4 py-2">
                            @if($record->pivot->unassign_pdf)
                                <a href="{{ asset('storage/' . $record->pivot->unassign_pdf) }}" class="text-blue-500 hover:underline" target="_blank">📄 PDF</a>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
                <x-data-edit-modal />

            </tbody>
        </table>

    </div>
</div>
@endsection

