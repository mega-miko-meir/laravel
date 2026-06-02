@props(['employee', 'currentStatus'])

@php
    $initials = collect(explode(' ', trim($employee->full_name)))
        ->take(2)->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');

    $photoUrl = $employee->photo_path
        ? \Illuminate\Support\Facades\Storage::url($employee->photo_path)
        : null;

    $statusColors = [
        'hired'             => ['bg' => '#dcfce7', 'text' => '#15803d', 'label' => 'Работает'],
        'return_from_leave' => ['bg' => '#dbeafe', 'text' => '#1d4ed8', 'label' => 'Вернулся'],
        'dismissed'         => ['bg' => '#fee2e2', 'text' => '#b91c1c', 'label' => 'Уволен'],
        'maternity_leave'   => ['bg' => '#fef9c3', 'text' => '#854d0e', 'label' => 'В декрете'],
        'long_vacation'     => ['bg' => '#fce7f3', 'text' => '#9d174d', 'label' => 'Отпуск'],
        'changed_position'  => ['bg' => '#d1fae5', 'text' => '#065f46', 'label' => 'Смена должности'],
    ];
    $st = $statusColors[$currentStatus] ?? ['bg' => '#f3f4f6', 'text' => '#374151', 'label' => ucfirst($currentStatus ?? '—')];

    $latestEvent = $employee->events()->latest('event_date')->first();
@endphp

<div style="display:flex;flex-direction:column;gap:1rem;">

{{-- ══════════════════════════════════ --}}
{{-- ОСНОВНАЯ КАРТОЧКА                  --}}
{{-- ══════════════════════════════════ --}}
<div style="background:#fff;border-radius:12px;border:1px solid #f0f0f0;box-shadow:0 1px 3px rgba(0,0,0,.06);overflow:hidden;">

    {{-- Шапка: аватар + имя + статус --}}
    <div style="display:flex;align-items:flex-start;gap:16px;padding:20px;border-bottom:1px solid #f5f5f5;">

        {{-- Аватар с загрузкой --}}
        <div
            x-data="{
                preview: {{ $photoUrl ? json_encode($photoUrl) : 'null' }},
                uploading: false,
                csrf: {{ json_encode(csrf_token()) }},
                url: {{ json_encode(route('employees.uploadPhoto', $employee)) }},
                handleFile(e) {
                    const file = e.target.files[0];
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onload = ev => { this.preview = ev.target.result; };
                    reader.readAsDataURL(file);
                    this.uploading = true;
                    const fd = new FormData();
                    fd.append('_token', this.csrf);
                    fd.append('photo', file);
                    fetch(this.url, { method: 'POST', body: fd })
                        .then(() => { this.uploading = false; })
                        .catch(() => { this.uploading = false; });
                }
            }"
            style="position:relative;flex-shrink:0;width:64px;height:64px;"
        >
            {{-- Круг аватара --}}
            <div style="width:64px;height:64px;border-radius:50%;overflow:hidden;
                        display:flex;align-items:center;justify-content:center;
                        background:#e0e7ff;border:2px solid #c7d2fe;box-sizing:border-box;">
                <template x-if="preview">
                    <img :src="preview" style="width:100%;height:100%;object-fit:cover;">
                </template>
                <template x-if="!preview">
                    <span style="font-size:22px;font-weight:700;color:#4f46e5;user-select:none;">{{ $initials }}</span>
                </template>
            </div>

            {{-- Оверлей камеры --}}
            <label style="position:absolute;inset:0;border-radius:50%;display:flex;
                          align-items:center;justify-content:center;cursor:pointer;
                          background:rgba(0,0,0,0);transition:background .2s;"
                   onmouseover="this.style.background='rgba(0,0,0,0.45)';this.querySelector('svg').style.opacity='1';"
                   onmouseout="this.style.background='rgba(0,0,0,0)';this.querySelector('svg').style.opacity='0';"
                   title="Загрузить фото">
                <svg style="width:20px;height:20px;color:white;opacity:0;transition:opacity .2s;pointer-events:none;flex-shrink:0;"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <input type="file" accept="image/jpeg,image/png,image/webp"
                       style="position:absolute;opacity:0;width:0;height:0;overflow:hidden;"
                       @change="handleFile($event)">
            </label>

            {{-- Спиннер --}}
            <div x-show="uploading" x-cloak
                 style="position:absolute;inset:0;border-radius:50%;
                        display:flex;align-items:center;justify-content:center;
                        background:rgba(255,255,255,0.85);">
                <svg style="width:20px;height:20px;color:#4f46e5;animation:spin 1s linear infinite;"
                     fill="none" viewBox="0 0 24 24">
                    <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
            </div>
        </div>

        {{-- Имя + статус --}}
        <div style="flex:1;min-width:0;">
            <h1 style="font-size:17px;font-weight:700;color:#111827;line-height:1.3;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                {{ $employee->full_name }}
            </h1>
            <p style="font-size:12px;color:#9ca3af;margin-top:2px;">{{ $employee->position ?? '—' }}</p>

            <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-top:8px;">
                <span style="display:inline-flex;align-items:center;padding:2px 8px;
                             border-radius:9999px;font-size:11px;font-weight:600;
                             background:{{ $st['bg'] }};color:{{ $st['text'] }};">
                    {{ $st['label'] }}
                </span>
                @if($latestEvent)
                    <span style="font-size:11px;color:#9ca3af;">
                        с {{ \Carbon\Carbon::parse($latestEvent->event_date)->format('d.m.Y') }}
                    </span>
                @endif
            </div>

            <div style="position:relative;display:inline-block;margin-top:6px;">
                <button onclick="toggleEditForm()"
                        style="font-size:12px;color:#2563eb;background:none;border:none;cursor:pointer;padding:0;"
                        onmouseover="this.style.textDecoration='underline';"
                        onmouseout="this.style.textDecoration='none';">
                    Изменить статус
                </button>
                <x-event-adding-form :employee="$employee" />
            </div>
        </div>

        {{-- Кнопка редактирования --}}
        <div style="flex-shrink:0;">
            <x-edit-employee-button :employee="$employee"/>
        </div>
    </div>

    {{-- Информационные поля --}}
    <div style="padding:16px 20px;display:grid;grid-template-columns:1fr 1fr;gap:14px 24px;">

        @php
            $infoFields = [
                ['label' => 'Email',      'value' => $employee->email ?? '—',
                 'icon'  => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                ['label' => 'Город',      'value' => $employee->current_city ?? '—',
                 'icon'  => 'M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z'],
                ['label' => 'Должность',  'value' => $employee->position ?? '—',
                 'icon'  => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                ['label' => 'Группа',     'value' => $employee->current_team ?? '—',
                 'icon'  => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
            ];
        @endphp

        @foreach($infoFields as $f)
            <div style="display:flex;align-items:flex-start;gap:8px;">
                <svg style="width:14px;height:14px;flex-shrink:0;margin-top:2px;color:#d1d5db;"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $f['icon'] }}"/>
                </svg>
                <div style="min-width:0;">
                    <p style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#9ca3af;">
                        {{ $f['label'] }}
                    </p>
                    <p style="font-size:13px;color:#1f2937;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        {{ $f['value'] }}
                    </p>
                </div>
            </div>
        @endforeach

        {{-- Менеджер --}}
        <div style="display:flex;align-items:flex-start;gap:8px;">
            <svg style="width:14px;height:14px;flex-shrink:0;margin-top:2px;color:#d1d5db;"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <div>
                <p style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#9ca3af;">Менеджер</p>
                @if($employee->current_manager)
                    <a href="{{ route('employees.show', $employee->current_manager->id) }}"
                       style="font-size:13px;color:#2563eb;text-decoration:none;"
                       onmouseover="this.style.textDecoration='underline';"
                       onmouseout="this.style.textDecoration='none';">
                        {{ $employee->current_manager->sh_name }}
                    </a>
                @else
                    <p style="font-size:13px;color:#1f2937;">—</p>
                @endif
            </div>
        </div>

        @if($employee->current_role === 'Rep')
            <div style="display:flex;align-items:flex-start;gap:8px;">
                <svg style="width:14px;height:14px;flex-shrink:0;margin-top:2px;color:#d1d5db;"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <div>
                    <p style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#9ca3af;">ФФМ</p>
                    @if($employee->FFM)
                        <a href="{{ route('employees.show', $employee->FFM->id) }}"
                           style="font-size:13px;color:#2563eb;text-decoration:none;"
                           onmouseover="this.style.textDecoration='underline';"
                           onmouseout="this.style.textDecoration='none';">
                            {{ $employee->FFM->sh_name }}
                        </a>
                    @else
                        <p style="font-size:13px;color:#1f2937;">—</p>
                    @endif
                </div>
            </div>
        @endif

    </div>

    {{-- KMP --}}
    <div style="border-top:1px solid #f5f5f5;padding:12px 20px;">
        <x-kmp-request :employee="$employee" />
    </div>

    {{-- Credentials --}}
    <div style="border-top:1px solid #f5f5f5;">
        <x-credentials :employee="$employee" />
    </div>

</div>

{{-- ══════════════════════════════════ --}}
{{-- ИСТОРИЯ СОБЫТИЙ                    --}}
{{-- ══════════════════════════════════ --}}
<div x-data="{ open: false }"
     style="background:#fff;border-radius:12px;border:1px solid #f0f0f0;box-shadow:0 1px 3px rgba(0,0,0,.06);">

    <button x-on:click="open = !open"
            style="width:100%;display:flex;align-items:center;justify-content:space-between;
                   padding:16px 20px;background:none;border:none;cursor:pointer;text-align:left;
                   border-radius:12px;"
            onmouseover="this.style.background='#f9fafb';"
            onmouseout="this.style.background='none';">
        <span style="font-size:14px;font-weight:600;color:#1f2937;">История событий</span>
        <svg :class="{ 'rotate-180': open }"
             style="width:16px;height:16px;color:#9ca3af;flex-shrink:0;transition:transform .2s;"
             fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-show="open" x-cloak>
        @php
            $eventLabels = [
                'hired'             => 'Принят',
                'dismissed'         => 'Уволен',
                'return_from_leave' => 'Вернулся из отпуска',
                'maternity_leave'   => 'Декретный отпуск',
                'change_position'   => 'Смена должности',
                'long_vacation'     => 'Длительный отпуск',
                'new'               => 'Новый',
            ];
        @endphp
        <ul style="padding:0 20px 16px;margin:0;list-style:none;">
            @forelse($employee->events()->orderBy('event_date', 'desc')->get() as $event)
                <li x-data="{ editing: false }"
                    style="padding:8px 0;border-bottom:1px solid #f9fafb;">

                    {{-- Режим просмотра --}}
                    <div x-show="!editing" style="display:flex;align-items:center;justify-content:space-between;">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <span style="font-size:11px;color:#9ca3af;width:72px;flex-shrink:0;">
                                {{ \Carbon\Carbon::parse($event->event_date)->format('d.m.Y') }}
                            </span>
                            <x-status-badge :status="$event->event_type" />
                        </div>
                        <div style="display:flex;align-items:center;gap:4px;">
                            {{-- Редактировать --}}
                            <button type="button" x-on:click="editing = true"
                                    style="width:24px;height:24px;display:flex;align-items:center;justify-content:center;
                                           border-radius:6px;background:none;border:none;cursor:pointer;color:#d1d5db;"
                                    onmouseover="this.style.color='#2563eb';this.style.background='#eff6ff';"
                                    onmouseout="this.style.color='#d1d5db';this.style.background='none';">
                                <svg style="width:13px;height:13px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            {{-- Удалить --}}
                            <form action="{{ route('events.destroy', $event->id) }}" method="POST"
                                  x-on:submit.prevent="if(confirm('Удалить событие?')) $el.submit()">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        style="width:24px;height:24px;display:flex;align-items:center;justify-content:center;
                                               border-radius:6px;background:none;border:none;cursor:pointer;color:#d1d5db;"
                                        onmouseover="this.style.color='#ef4444';this.style.background='#fef2f2';"
                                        onmouseout="this.style.color='#d1d5db';this.style.background='none';">
                                    <svg style="width:13px;height:13px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Режим редактирования --}}
                    <div x-show="editing" x-cloak>
                        <form action="{{ route('events.update', $event->id) }}" method="POST"
                              onsubmit="return confirm('Сохранить изменения?');">
                            @csrf @method('PATCH')
                            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                <select name="event_type"
                                        style="padding:4px 8px;border:1.5px solid #e5e7eb;border-radius:6px;
                                               font-size:12px;color:#374151;outline:none;background:#fff;"
                                        onfocus="this.style.borderColor='#2563eb';"
                                        onblur="this.style.borderColor='#e5e7eb';">
                                    @foreach($eventLabels as $val => $lbl)
                                        <option value="{{ $val }}" {{ $event->event_type === $val ? 'selected' : '' }}>
                                            {{ $lbl }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="date" name="event_date"
                                       value="{{ \Carbon\Carbon::parse($event->event_date)->format('Y-m-d') }}"
                                       style="padding:4px 8px;border:1.5px solid #e5e7eb;border-radius:6px;
                                              font-size:12px;color:#374151;outline:none;"
                                       onfocus="this.style.borderColor='#2563eb';"
                                       onblur="this.style.borderColor='#e5e7eb';">
                                <button type="submit"
                                        style="padding:4px 10px;font-size:12px;font-weight:600;background:#2563eb;
                                               color:#fff;border:none;border-radius:6px;cursor:pointer;"
                                        onmouseover="this.style.background='#1d4ed8';"
                                        onmouseout="this.style.background='#2563eb';">
                                    Сохранить
                                </button>
                                <button type="button" x-on:click="editing = false"
                                        style="padding:4px 10px;font-size:12px;background:#fff;color:#6b7280;
                                               border:1px solid #e5e7eb;border-radius:6px;cursor:pointer;"
                                        onmouseover="this.style.background='#f3f4f6';"
                                        onmouseout="this.style.background='#fff';">
                                    Отмена
                                </button>
                            </div>
                        </form>
                    </div>

                </li>
            @empty
                <li style="padding:16px 0;font-size:13px;text-align:center;color:#9ca3af;">Нет событий</li>
            @endforelse
        </ul>
    </div>

</div>
</div>

<style>
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>

<script>
function toggleEditForm() {
    const el = document.getElementById('editForm');
    if (el) el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

document.addEventListener('click', function (e) {
    const panel = document.getElementById('editForm');
    if (!panel || panel.style.display === 'none') return;
    if (!panel.contains(e.target) && !e.target.closest('button[onclick="toggleEditForm()"]')) {
        panel.style.display = 'none';
    }
});
</script>
