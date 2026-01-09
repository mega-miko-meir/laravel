@props(['employee'])

<!-- Форма обновления статуса (изначально скрыта) -->

@php
    $latestEventDate = optional($employee->events()->latest('event_date')->first())->event_date;
    $lastEventType = optional($employee->events()->latest('event_date')->first())->event_type;
@endphp

<form action="{{ route('employees.updateStatusAndEvent', $employee->id) }}" method="POST" id="editForm" class="bg-gray-50 p-4 rounded-lg shadow-sm hidden"
    onsubmit="return confirm('Are you sure you want to add an event and change the status?');">
    @csrf
    @method('PUT')
    <label for="event_type" class="block text-sm font-medium mb-1">Выберите событие:</label>
    <select name="event_type" id="event_type" class="w-full p-2 border rounded text-sm">
        {{-- <option value="new" {{$lastEventType === 'new' ? 'selected' : ''}}>New</option> --}}
        <option value="hired" {{$lastEventType === 'hired' ? 'selected' : ''}}>Hired</option>
        <option value="dismissed" {{$lastEventType === 'dismissed' ? 'selected' : ''}}>Dismissed</option>
        <option value="maternity_leave" {{$lastEventType === 'maternity_leave' ? 'selected' : ''}}>Maternity leave</option>
        <option value="change_position" {{$lastEventType === 'changed_position' ? 'selected' : ''}}>Changed position</option>
        <option value="long_vacation" {{$lastEventType === 'long_vacation' ? 'selected' : ''}}>Long vacation</option>
    </select>
    <label for="event_date" class="block text-sm font-medium mt-2">Event date:</label>
    <input type="date" name="event_date" id="event_date" class="w-full p-2 border rounded text-sm"
        {{-- value="{{ now()->format('Y-m-d') }}"> --}}
        {{-- value="{{ $latestEventDate ? \Carbon\Carbon::parse($latestEventDate)->format('Y-m-d') : '' }}" --}}
        value="{{now()->format("Y-m-d")}}"
        >

    <div class="flex justify-end mt-3">
        <button type="button" onclick="toggleEditForm()" class="px-4 py-2 text-sm text-gray-600 border rounded hover:bg-gray-100 mr-2">Отмена</button>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
            Обновить
        </button>
    </div>
</form>
