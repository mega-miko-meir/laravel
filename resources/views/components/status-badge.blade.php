@props(['status'])

@php
    $colors = [
        'hired' => 'bg-green-500 text-white',
        'return_from_leave' => 'bg-blue-500 text-white',
        'dismissed' => 'bg-red-500 text-white',
        'maternity_leave' => 'bg-yellow-500 text-black',
        'long_vacation' => 'bg-pink-500 text-black',
        'changed_position' => 'bg-green-800 text-black'
    ];

    // $class = $colors[$employee->events()->latest('event_date')->first()?->event_type ?? 'unknown'] ?? 'bg-gray-500 text-white';
    $class = $colors[$status] ?? 'bg-gray-500 text-white';
@endphp

<span class="px-2 py-1 rounded {{ $class }}">
    {{ ucfirst(str_replace('_', ' ', $status)) }}
</span>
