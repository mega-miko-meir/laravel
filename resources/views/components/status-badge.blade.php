@props(['status'])

@php
    $map = [
        'hired'             => ['bg' => '#dcfce7', 'text' => '#15803d', 'label' => 'Принят'],
        'return_from_leave' => ['bg' => '#dbeafe', 'text' => '#1d4ed8', 'label' => 'Вернулся'],
        'dismissed'         => ['bg' => '#fee2e2', 'text' => '#b91c1c', 'label' => 'Уволен'],
        'maternity_leave'   => ['bg' => '#fef9c3', 'text' => '#854d0e', 'label' => 'Декрет'],
        'long_vacation'     => ['bg' => '#fce7f3', 'text' => '#9d174d', 'label' => 'Отпуск'],
        'changed_position'  => ['bg' => '#d1fae5', 'text' => '#065f46', 'label' => 'Смена роли'],
    ];
    $s = $map[$status] ?? ['bg' => '#f3f4f6', 'text' => '#374151', 'label' => ucfirst(str_replace('_', ' ', $status))];
@endphp

<span style="display:inline-flex;align-items:center;padding:2px 8px;border-radius:9999px;
             font-size:11px;font-weight:600;background:{{ $s['bg'] }};color:{{ $s['text'] }};">
    {{ $s['label'] }}
</span>
