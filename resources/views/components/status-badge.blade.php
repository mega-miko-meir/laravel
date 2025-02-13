@props(['status'])

@php
    $colors = [
        'new' => 'bg-green-500 text-white',
        'active' => 'bg-blue-500 text-white',
        'dismissed' => 'bg-red-500 text-white',
        'maternity_leave' => 'bg-yellow-500 text-black',
        'long_vacation' => 'bg-yellow-500 text-black'
    ];

    $class = $colors[$status] ?? 'bg-gray-500 text-white';
@endphp

<span class="px-2 py-1 rounded {{ $class }}">
    {{ ucfirst(str_replace('_', ' ', $status)) }}
</span>
