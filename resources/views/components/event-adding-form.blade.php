@props(['employee'])

@php
    $latestEvent   = $employee->events()->latest('event_date')->first();
    $currentStatus = optional($latestEvent)->event_type;

    $hasUnconfirmedTerritory = \Illuminate\Support\Facades\DB::table('employee_territory')
        ->where('employee_id', $employee->id)
        ->whereNull('unassigned_at')
        ->where('confirmed', 0)
        ->exists();

    $hasUnconfirmedTablet = \Illuminate\Support\Facades\DB::table('employee_tablet')
        ->where('employee_id', $employee->id)
        ->whereNull('returned_at')
        ->where('confirmed', 0)
        ->exists();

    $active = in_array($currentStatus, ['hired', 'return_from_leave']);

    if ($active) {
        $actions = [
            ['type' => 'dismissed',      'label' => 'Уволить',          'color' => '#dc2626', 'bg' => '#fee2e2'],
            ['type' => 'maternity_leave','label' => 'В декрет',          'color' => '#9333ea', 'bg' => '#f3e8ff'],
            ['type' => 'long_vacation',  'label' => 'Длит. отпуск',     'color' => '#d97706', 'bg' => '#fef3c7'],
            ['type' => 'change_position','label' => 'Смена должности',  'color' => '#0891b2', 'bg' => '#e0f2fe'],
        ];
    } elseif (in_array($currentStatus, ['maternity_leave', 'long_vacation'])) {
        $actions = [
            ['type' => 'return_from_leave', 'label' => 'Вернуть на работу', 'color' => '#16a34a', 'bg' => '#dcfce7'],
        ];
    } elseif ($currentStatus === 'dismissed') {
        $actions = [
            ['type' => 'hired', 'label' => 'Принять обратно', 'color' => '#16a34a', 'bg' => '#dcfce7'],
        ];
    } else {
        $actions = [
            ['type' => 'hired', 'label' => 'Принять на работу', 'color' => '#16a34a', 'bg' => '#dcfce7'],
        ];
    }
@endphp

<div id="editForm"
     style="display:none;position:absolute;top:calc(100% + 4px);left:0;z-index:100;
            width:270px;background:#fff;border:1px solid #e5e7eb;border-radius:10px;
            box-shadow:0 8px 24px rgba(0,0,0,.12);padding:14px;">
    <div x-data="{ activeAction: null }">

        {{-- Кнопки действий --}}
        <div style="display:flex;flex-wrap:wrap;gap:6px;">
            @foreach($actions as $action)
                <button type="button"
                        x-on:click="activeAction = activeAction === '{{ $action['type'] }}' ? null : '{{ $action['type'] }}'"
                        :style="activeAction === '{{ $action['type'] }}'
                            ? 'background:{{ $action['color'] }};color:#fff;border-color:{{ $action['color'] }};'
                            : 'background:{{ $action['bg'] }};color:{{ $action['color'] }};border-color:{{ $action['color'] }};'"
                        style="padding:5px 12px;font-size:12px;font-weight:600;border-radius:7px;
                               cursor:pointer;border:1.5px solid;transition:all .15s;">
                    {{ $action['label'] }}
                </button>
            @endforeach
        </div>

        {{-- Мини-форма с датой для каждого действия --}}
        @foreach($actions as $action)
            <div x-show="activeAction === '{{ $action['type'] }}'" x-cloak style="margin-top:8px;">
                <form action="{{ route('employees.updateStatusAndEvent', $employee->id) }}" method="POST"
                      onsubmit="return checkEventSubmit('{{ $action['type'] }}')">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="event_type" value="{{ $action['type'] }}">

                    <div style="display:flex;flex-direction:column;gap:8px;
                                padding:10px 12px;background:#f9fafb;
                                border-radius:8px;border:1px solid #e5e7eb;">
                        <div>
                            <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;">Дата</label>
                            <input type="date" name="event_date" value="{{ now()->format('Y-m-d') }}" required
                                   style="width:100%;padding:5px 8px;border:1.5px solid #e5e7eb;border-radius:6px;
                                          font-size:12px;color:#374151;outline:none;box-sizing:border-box;"
                                   onfocus="this.style.borderColor='#2563eb';"
                                   onblur="this.style.borderColor='#e5e7eb';">
                        </div>
                        <div style="display:flex;gap:6px;">
                            <button type="submit"
                                    style="flex:1;padding:6px 0;font-size:12px;font-weight:600;
                                           background:{{ $action['color'] }};color:#fff;
                                           border:none;border-radius:6px;cursor:pointer;"
                                    onmouseover="this.style.opacity='.85';"
                                    onmouseout="this.style.opacity='1';">
                                Подтвердить
                            </button>
                            <button type="button" x-on:click="activeAction = null"
                                    style="padding:6px 12px;font-size:12px;background:#fff;color:#6b7280;
                                           border:1px solid #e5e7eb;border-radius:6px;cursor:pointer;"
                                    onmouseover="this.style.background='#f3f4f6';"
                                    onmouseout="this.style.background='#fff';">
                                Отмена
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @endforeach

    </div>
</div>

<script>
function checkEventSubmit(eventType) {
    const unassignEvents = ['dismissed', 'maternity_leave', 'change_position', 'long_vacation'];

    if (!unassignEvents.includes(eventType)) {
        return confirm('Подтвердить изменение статуса?');
    }

    const unconfirmedTerritory = {{ $hasUnconfirmedTerritory ? 'true' : 'false' }};
    const unconfirmedTablet    = {{ $hasUnconfirmedTablet    ? 'true' : 'false' }};

    if (unconfirmedTerritory || unconfirmedTablet) {
        const parts = [];
        if (unconfirmedTerritory) parts.push('Территория');
        if (unconfirmedTablet)    parts.push('Планшет');
        const label  = parts.join(' и ');
        const suffix = parts.length > 1
            ? 'не подтверждены'
            : (unconfirmedTerritory ? 'не подтверждена' : 'не подтверждён');
        return confirm(label + ' ' + suffix + '. Всё равно продолжить?');
    }

    return confirm('Подтвердить изменение статуса?');
}
</script>
