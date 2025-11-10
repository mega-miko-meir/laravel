@props([
    'action',
    'method' => 'POST',
    'tablet' => null,
    'employees' => null, // список сотрудников, если нужно выбрать владельца
])

<x-flash-message />

<form action="{{ $action }}" method="POST" class="space-y-4">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    {{-- Модель планшета --}}
    <div>
        <label for="model" class="block text-sm font-medium text-gray-600">Model</label>
        <input
            type="text"
            name="model"
            id="model"
            placeholder="Tablet model"
            value="{{ old('model', $tablet->model ?? '') }}"
            class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
    </div>

    {{-- Серийный номер --}}
    <div>
        <label for="serial_number" class="block text-sm font-medium text-gray-600">Serial Number</label>
        <input
            type="text"
            name="serial_number"
            id="serial_number"
            placeholder="Enter serial number"
            value="{{ old('serial_number', $tablet->serial_number ?? '') }}"
            class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
    </div>


    {{-- Серийный номер --}}
    <div>
        <label for="invent_number" class="block text-sm font-medium text-gray-600">Invent Number</label>
        <input
            type="text"
            name="invent_number"
            id="invent_number"
            placeholder="Enter serial number"
            value="{{ old('invent_number', $tablet->invent_number ?? '') }}"
            class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
    </div>

    {{-- Билайн номер --}}
    <div>
        <label for="beeline_number" class="block text-sm font-medium text-gray-600">Beeline Number</label>
        <input
            type="text"
            name="beeline_number"
            id="beeline_number"
            placeholder="Enter serial number"
            value="{{ old('beeline_number', $tablet->beeline_number ?? '') }}"
            class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
    </div>


    {{-- Кнопка отправки --}}
    <div class="flex justify-end">
        <button type="submit"
                class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-lg transition duration-300">
            {{ $tablet ? 'Edit Tablet' : 'Add Tablet' }}
        </button>
    </div>
</form>
