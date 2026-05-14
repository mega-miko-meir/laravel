@props(['employee' => null, 'bricks', 'selectedBricks', 'territory'])

<div x-data="{
    open: false,
    search: '',
    get filtered() {
        return this.search
            ? {{ Js::from($bricks->pluck('description', 'code')) }}.filter
                ? null
                : null
            : null;
    }
}" style="margin-top:4px;">

    {{-- Текущие брики --}}
    <div style="margin-bottom:10px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
            <p style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#9ca3af;">
                Брики ({{ $selectedBricks->count() }})
            </p>
            <button @click="open = !open"
                    style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;
                           background:#fff;color:#374151;border:1px solid #e5e7eb;border-radius:7px;
                           font-size:11px;font-weight:600;cursor:pointer;"
                    onmouseover="this.style.background='#f9fafb';"
                    onmouseout="this.style.background='#fff';">
                <svg style="width:11px;height:11px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Добавить
            </button>
        </div>

        @if($selectedBricks->isNotEmpty())
            <div style="display:flex;flex-direction:column;gap:2px;">
                @foreach($selectedBricks as $brick)
                    <div style="display:flex;align-items:center;justify-content:space-between;
                                padding:6px 10px;background:#f8fafc;border:1px solid #e2e8f0;
                                border-radius:7px;font-size:12px;">
                        <span style="color:#374151;">{{ $brick->description }}</span>
                        @if(isset($territory))
                            <form action="{{ route('assign.bricks', [$territory->id, $brick->id]) }}"
                                  method="POST"
                                  x-data x-on:submit.prevent="if(confirm('Удалить брик?')) $el.submit()">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        style="width:20px;height:20px;display:flex;align-items:center;justify-content:center;
                                               background:none;border:none;cursor:pointer;color:#d1d5db;border-radius:4px;flex-shrink:0;"
                                        onmouseover="this.style.color='#ef4444';this.style.background='#fef2f2';"
                                        onmouseout="this.style.color='#d1d5db';this.style.background='none';">
                                    <svg style="width:12px;height:12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <p style="font-size:12px;color:#9ca3af;font-style:italic;">Нет привязанных бриков</p>
        @endif
    </div>

    {{-- Dropdown добавления бриков --}}
    <div x-show="open" x-cloak
         style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;
                box-shadow:0 4px 16px rgba(0,0,0,.08);overflow:hidden;">

        @if(isset($territory) && is_null(optional($territory->pivot)->unassigned_at))
            <form action="{{ route('assign.bricks', [$territory->id]) }}" method="POST">
                @csrf

                {{-- Поиск --}}
                <div style="padding:10px;border-bottom:1px solid #f0f0f0;">
                    <input type="text" x-model="search" placeholder="Поиск брика..."
                           id="brick-search-{{ $territory->id }}"
                           style="width:100%;padding:7px 10px;border:1px solid #e5e7eb;border-radius:7px;
                                  font-size:12px;outline:none;box-sizing:border-box;"
                           oninput="filterBricks_{{ $territory->id }}(this.value)">
                </div>

                {{-- Список чекбоксов --}}
                <div id="brick-list-{{ $territory->id }}"
                     style="max-height:200px;overflow-y:auto;padding:6px 0;">
                    @foreach($bricks as $brick)
                        <label id="brick-item-{{ $territory->id }}-{{ $brick->id }}"
                               style="display:flex;align-items:center;gap:8px;padding:7px 12px;
                                      cursor:pointer;font-size:12px;color:#374151;"
                               onmouseover="this.style.background='#f8fafc';"
                               onmouseout="this.style.background='none';">
                            <input type="checkbox" name="bricks[]" value="{{ $brick->code }}"
                                   style="width:14px;height:14px;accent-color:#2563eb;flex-shrink:0;">
                            <span>{{ $brick->description }}</span>
                        </label>
                    @endforeach
                </div>

                {{-- Кнопки --}}
                <div style="padding:10px;border-top:1px solid #f0f0f0;display:flex;gap:8px;justify-content:flex-end;">
                    <button type="button" @click="open = false"
                            style="padding:6px 14px;font-size:12px;color:#374151;background:#fff;
                                   border:1px solid #e5e7eb;border-radius:7px;cursor:pointer;">
                        Отмена
                    </button>
                    <button type="submit"
                            style="padding:6px 14px;font-size:12px;font-weight:600;color:#fff;
                                   background:#2563eb;border:none;border-radius:7px;cursor:pointer;"
                            onmouseover="this.style.background='#1d4ed8';"
                            onmouseout="this.style.background='#2563eb';">
                        Добавить
                    </button>
                </div>
            </form>
        @else
            <p style="padding:14px;font-size:12px;color:#9ca3af;">Нет доступных территорий для назначения бриков.</p>
        @endif
    </div>

</div>

<script>
function filterBricks_{{ $territory->id }}(term) {
    const list = document.getElementById('brick-list-{{ $territory->id }}');
    if (!list) return;
    list.querySelectorAll('label').forEach(label => {
        const match = label.textContent.toLowerCase().includes(term.toLowerCase());
        label.style.display = match ? 'flex' : 'none';
    });
}
</script>
