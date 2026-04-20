@props([
    'action',
    'method' => 'POST',
    'tablet' => null,
    'employees' => null, // список сотрудников, если нужно выбрать владельца
    'responsibles'
])

<x-flash-message />

<form action="{{ $action }}" method="POST" class="space-y-6">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <strong class="font-semibold">Пожалуйста, исправьте ошибки:</strong>
            <ul class="mt-2 list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-6 md:grid-cols-2">
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700">Статус планшета</label>
            @php
                $statuses = [
                    'active' => 'Активен',
                    'lost' => 'Потерян',
                    'damaged' => 'Повреждён',
                    'written-off' => 'Списан',
                    'admin' => 'Админ',
                ];
                $currentStatus = old('status', $tablet->status ?? 'active');
            @endphp
            <select
                name="status"
                id="status"
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white p-3 text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" @selected($currentStatus === $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="model" class="block text-sm font-medium text-gray-700">Модель</label>
            <input
                type="text"
                name="model"
                id="model"
                placeholder="Введите модель планшета"
                value="{{ old('model', $tablet->model ?? '') }}"
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white p-3 text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
        </div>

        <div>
            <label for="serial_number" class="block text-sm font-medium text-gray-700">Серийный номер</label>
            <input
                type="text"
                name="serial_number"
                id="serial_number"
                placeholder="Введите серийный номер"
                value="{{ old('serial_number', $tablet->serial_number ?? '') }}"
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white p-3 text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
        </div>

        <div>
            <label for="invent_number" class="block text-sm font-medium text-gray-700">Инвентарный номер</label>
            <input
                type="text"
                name="invent_number"
                id="invent_number"
                placeholder="Введите инвентарный номер"
                value="{{ old('invent_number', $tablet->invent_number ?? '') }}"
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white p-3 text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
        </div>

        <div>
            <label for="imei" class="block text-sm font-medium text-gray-700">IMEI</label>
            <input
                type="text"
                name="imei"
                id="imei"
                placeholder="Введите IMEI"
                value="{{ old('imei', $tablet->imei ?? '') }}"
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white p-3 text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
        </div>

        <div>
            <label for="beeline_number" class="block text-sm font-medium text-gray-700">Номер Beeline</label>
            <input
                type="text"
                name="beeline_number"
                id="beeline_number"
                placeholder="Введите номер Beeline"
                value="{{ old('beeline_number', $tablet->beeline_number ?? '') }}"
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white p-3 text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
        </div>
        <div>
            <label for="responsible_id" class="block text-sm font-medium text-gray-700">Ответственное лицо</label>
            <select
                name="responsible_id"
                id="responsible_id"
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white p-3 text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <option value="">— Не выбрано —</option>
                @foreach($responsibles as $employee)
                    <option value="{{ $employee->id }}"
                        {{ old('responsible_id', $tablet->responsible_id ?? '') == $employee->id ? 'selected' : '' }}>
                        {{ $employee->full_name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center">
        <button type="submit"
                class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        >
            {{ $tablet ? 'Сохранить изменения' : 'Добавить планшет' }}
        </button>
        @if($tablet)
            <a href="{{ route('tablets.show', ['tablet' => $tablet->id]) }}" class="text-sm text-gray-500 hover:text-gray-700">Отмена</a>
        @endif
    </div>
</form>
