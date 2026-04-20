@extends('layout')

@section('content')
<x-flash-message />
<x-back-button />
<div class="container mx-auto py-6">
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-lg font-bold mb-4">Детали планшета</h2>

        <p><strong>Статус:</strong> {{ $tablet->status }}</p>
        <p><strong>Модель:</strong> {{ $tablet->model }}</p>
        <p><strong>Инвентарный номер:</strong> {{ $tablet->invent_number }}</p>
        <p><strong>Серийный номер:</strong> {{ $tablet->serial_number }}</p>
        <p><strong>IMEI номер:</strong> {{ $tablet->imei }}</p>
        <p><strong>Билайн номер:</strong> {{ $tablet->beeline_number }}</p>
        <div x-data="{ showForm: false }" >
            <p>
                <strong>Сотрудник:</strong>
                @if(!$previousUsers->first() || $previousUsers->first()->pivot->returned_at)
                    Не назначен
                    <button @click="showForm = !showForm" class="bg-blue-500 ml-2 px-3 py-1 text-white text-sm rounded hover:bg-blue-700 transition">
                        Назначить
                    </button>

                    <p>
                        <strong>Ответственное лицо:</strong>
                        {{-- Добавляем проверку через тернарный оператор или null-safe --}}
                        {{ $tablet->responsible ? $tablet->responsible->full_name : 'Не указано' }}
                    </p>
                @else
                    {{-- Здесь тоже лучше использовать проверку, чтобы избежать подобных ошибок в будущем --}}
                    {{ $previousUsers->first() ? $previousUsers->first()->full_name : 'Не назначен' }}
                @endif
            </p>
            <div x-show="showForm" class="mt-4 pt-4 bg-gray-100 border rounded">
                <div x-data="{
                    showModal: false,
                    employeeCity: '',
                    responsibleCity: '',
                    async checkAndSubmit(e) {
                        e.preventDefault();
                        const employeeId = document.getElementById('employee_id').value;
                        const tabletId   = {{ $tablet->id }};
                        if (!employeeId) { e.target.submit(); return; }

                        const res  = await fetch(`/api/city-check?employee_id=${employeeId}&tablet_id=${tabletId}`);
                        const data = await res.json();

                        if (!data.match && data.responsible_city) {
                            this.employeeCity    = data.employee_city ?? '—';
                            this.responsibleCity = data.responsible_city ?? '—';
                            this.showModal = true;
                        } else {
                            e.target.submit();
                        }
                    }
                }">
                    <form id="assign-form" action="{{ route('assign.employee2', $tablet->id) }}" method="POST"
                        @submit="checkAndSubmit($event)">
                        @csrf
                        <label for="employee" class="block text-sm font-medium text-gray-600"></label>
                        <select name="employee_id" id="employee_id" class="w-full p-3 border rounded-lg mt-2">
                            @foreach ($availableEmployees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                            @endforeach
                        </select>
                        <input type="date" name="assigned_at" id="assigned_at"
                            value="{{ now()->format('Y-m-d') }}" class="mt-2 w-full p-2 border rounded-lg">
                        <button type="submit"
                                class="mt-4 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                            Назначить
                        </button>
                    </form>

                    {{-- Модальное окно --}}
                    <div x-show="showModal" x-cloak
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
                        <div class="bg-white rounded-xl shadow-lg p-6 max-w-sm w-full mx-4">
                            <p class="text-sm font-semibold text-gray-800 mb-2">Города не совпадают</p>
                            <p class="text-sm text-gray-600 mb-4">
                                Город сотрудника: <strong x-text="employeeCity"></strong><br>
                                Город планшета (ответственного): <strong x-text="responsibleCity"></strong><br><br>
                                Вы хотите привязать планшет к этому сотруднику?
                            </p>
                            <div class="flex gap-3 justify-end">
                                <button @click="showModal = false"
                                        class="px-4 py-2 text-sm rounded border text-gray-600 hover:bg-gray-100">
                                    Отмена
                                </button>
                                <button @click="showModal = false; $nextTick(() => document.getElementById('assign-form').submit())"
                                        class="px-4 py-2 text-sm rounded bg-blue-500 text-white hover:bg-blue-600">
                                    Привязать
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <x-edit-tablet-button :tablet="$tablet" />
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
                        <td class="border border-gray-300 px-4 py-2">{{ $record->pivot->returned_at ? \Carbon\Carbon::parse($record->pivot->returned_at)->format('d.m.Y')  : 'Текущий пользователь'}}
                            <button
                                onclick="openEditModal('{{ $record->pivot->id }}', 'returned_at', '{{ $record->pivot->returned_at }}', 'tablet')"
                                class="ml-2 text-blue-500 hover:underline text-sm">✎
                            </button>
                        </td>
                        <td class="border border-gray-300 px-4 py-2">
                            <div class="flex items-center gap-2">

                                @if($record->pivot->pdf_path)
                                    <a href="{{ asset('storage/' . $record->pivot->pdf_path) }}"
                                    class="text-blue-500 hover:underline"
                                    target="_blank">
                                        📄 PDF
                                    </a>
                                @else
                                    <span class="text-gray-400 text-sm">—</span>
                                @endif

                                {{-- Карандаш ВСЕГДА --}}
                                <button
                                    onclick="openPdfModal('{{ $record->pivot->id }}')"
                                    class="text-gray-500 hover:text-blue-600 text-sm"
                                    title="{{ $record->pivot->pdf_path ? 'Редактировать PDF' : 'Добавить PDF' }}"
                                >
                                    ✏️
                                </button>

                            </div>
                        </td>


                        <td class="border border-gray-300 px-4 py-2">
                            <div class="flex items-center gap-2">

                                @if($record->pivot->unassign_pdf)
                                    <a href="{{ asset('storage/' . $record->pivot->unassign_pdf) }}"
                                    class="text-blue-500 hover:underline"
                                    target="_blank">
                                        📄 PDF
                                    </a>
                                @else
                                    <span class="text-gray-400 text-sm">—</span>
                                @endif

                                {{-- Карандаш ВСЕГДА --}}
                                <button
                                    onclick="openPdfModal('{{ $record->pivot->id }}', 'unassign_pdf')"
                                    class="text-gray-500 hover:text-blue-600 text-sm"
                                    title="{{ $record->pivot->unassign_pdf ? 'Редактировать PDF' : 'Добавить PDF' }}"
                                >
                                    ✏️
                                </button>

                            </div>
                        </td>

                    </tr>
                @endforeach
                <x-data-edit-modal />
                <x-pdf-edit-modal />

            </tbody>
        </table>

    </div>
</div>
@endsection

