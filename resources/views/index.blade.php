@extends('layout')
@section('content')

<style>
    .al-tab { border:1px solid #d1d5db; border-radius:6px; padding:5px 12px; font-size:12px; font-weight:600; cursor:pointer; background:#fff; color:#374151; }
    .al-tab-active { background:#1d4ed8; color:#fff; border-color:#1d4ed8; }
</style>

{{-- Тулбар --}}
<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:20px;margin-top:24px;">

    <h1 style="font-size:20px;font-weight:700;color:#111827;margin:0;">
        Активность
        <span id="al-count" style="font-size:13px;font-weight:500;color:#9ca3af;margin-left:6px;">{{ $logs->total() }}</span>
    </h1>

    {{-- Выгрузить --}}
    <div x-data="{ open: false }" style="position:relative;">
        <button @click="open = !open; if (open) syncExportFilters();"
                style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;
                       background:#fff;color:#374151;border:1px solid #e5e7eb;border-radius:8px;
                       font-size:13px;font-weight:500;cursor:pointer;"
                onmouseover="this.style.background='#f9fafb';"
                onmouseout="this.style.background='#fff';">
            <svg style="width:14px;height:14px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Выгрузить
            <svg style="width:13px;height:13px;color:#9ca3af;" :class="{'rotate-180':open}"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open" @click.away="open=false" x-cloak
             style="position:absolute;right:0;top:calc(100% + 6px);width:260px;
                    background:#fff;border:1px solid #e5e7eb;border-radius:10px;
                    box-shadow:0 4px 20px rgba(0,0,0,.1);z-index:50;padding:16px;">
            <form method="GET" action="{{ route('activity.export') }}">
                <p style="font-size:13px;font-weight:600;color:#374151;margin-bottom:4px;">Период выгрузки</p>
                <p style="font-size:11px;color:#9ca3af;margin-bottom:12px;">
                    Текущие фильтры (поиск/метод/пользователь) применятся автоматически.
                </p>

                {{-- Текущие фильтры экрана — переносим в выгрузку. Значения
                     синхронизируются JS'ом при открытии панели (см. syncExportFilters),
                     поэтому отражают live-состояние, а не только то, что было
                     при первой загрузке страницы. --}}
                <input type="hidden" name="q" id="export-q" value="{{ $filters['search'] }}">
                <input type="hidden" name="user_id" id="export-user_id" value="{{ $filters['userId'] }}">
                <input type="hidden" name="hide_own" id="export-hide_own" value="{{ $filters['hideOwn'] ? '1' : '0' }}">
                <span id="export-methods">
                    @foreach($filters['methods'] as $m)
                        <input type="hidden" name="method[]" value="{{ $m }}">
                    @endforeach
                </span>

                <div style="margin-bottom:10px;">
                    <label style="display:block;font-size:11px;font-weight:600;text-transform:uppercase;
                                  letter-spacing:.05em;color:#9ca3af;margin-bottom:4px;">Дата начала</label>
                    <input type="date" name="from" required value="{{ $filters['from'] }}"
                           style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:8px;
                                  font-size:13px;outline:none;box-sizing:border-box;">
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:11px;font-weight:600;text-transform:uppercase;
                                  letter-spacing:.05em;color:#9ca3af;margin-bottom:4px;">Дата окончания</label>
                    <input type="date" name="to" required value="{{ $filters['to'] }}"
                           style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:8px;
                                  font-size:13px;outline:none;box-sizing:border-box;">
                </div>

                <button type="submit"
                        style="width:100%;padding:8px;background:#2563eb;color:#fff;border:none;
                               border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;"
                        onmouseover="this.style.background='#1d4ed8';"
                        onmouseout="this.style.background='#2563eb';">
                    Скачать Excel
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Фильтры --}}
<form id="al-filters" method="GET" action="{{ route('activity.logs') }}"
      onsubmit="applyFilters(); return false;"
      style="background:#fff;border:1px solid #f0f0f0;border-radius:12px;padding:14px 16px;margin-bottom:16px;
             display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">

    <div style="flex:1;min-width:200px;">
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;">Поиск</label>
        <input type="text" name="q" value="{{ $filters['search'] }}" placeholder="URL, IP, пользователь..."
               oninput="debouncedApplyFilters()"
               style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:8px;
                      font-size:13px;outline:none;box-sizing:border-box;">
    </div>

    <div>
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;">С</label>
        <input type="date" name="from" value="{{ $filters['from'] }}" onchange="applyFilters()"
               style="padding:8px 10px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;">
    </div>

    <div>
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;">По</label>
        <input type="date" name="to" value="{{ $filters['to'] }}" onchange="applyFilters()"
               style="padding:8px 10px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;">
    </div>

    <div style="min-width:180px;">
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;">Пользователь</label>
        <select name="user_id" onchange="onUserChange(this)"
                style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:8px;
                       font-size:13px;outline:none;box-sizing:border-box;background:#fff;">
            <option value="">Все пользователи</option>
            @foreach($users as $u)
                <option value="{{ $u->id }}" {{ (string) $filters['userId'] === (string) $u->id ? 'selected' : '' }}>
                    {{ $u->full_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;">Метод</label>
        <div style="display:flex;gap:6px;">
            @foreach($methods as $m)
                @php $checked = in_array($m, $filters['methods']); @endphp
                <label class="al-tab {{ $checked ? 'al-tab-active' : '' }}" style="user-select:none;">
                    <input type="checkbox" name="method[]" value="{{ $m }}" {{ $checked ? 'checked' : '' }}
                           onchange="this.closest('label').classList.toggle('al-tab-active', this.checked); applyFilters();"
                           style="display:none;">
                    {{ $m }}
                </label>
            @endforeach
        </div>
    </div>

    <div style="display:flex;align-items:center;gap:6px;padding-bottom:8px;">
        {{-- Чекбокс без name — браузер не отправляет незаполненные чекбоксы,
             поэтому реальное значение всегда идёт через скрытое поле, которое
             JS обновляет перед фильтрацией. --}}
        <input type="hidden" name="hide_own" id="hide_own_value" value="{{ $filters['hideOwn'] ? '1' : '0' }}">
        <input type="checkbox" id="hide_own" {{ $filters['hideOwn'] ? 'checked' : '' }}
               {{ $filters['userId'] ? 'disabled' : '' }}
               onchange="document.getElementById('hide_own_value').value = this.checked ? '1' : '0'; applyFilters();"
               style="width:15px;height:15px;accent-color:#2563eb;">
        <label for="hide_own" style="font-size:13px;color:#374151;cursor:pointer;white-space:nowrap;">
            Скрыть мои действия
        </label>
    </div>

    <div style="display:flex;gap:8px;">
        <button type="submit"
                style="padding:8px 18px;background:#2563eb;color:#fff;border:none;border-radius:8px;
                       font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap;">
            Применить
        </button>
        <a href="{{ route('activity.logs') }}" id="al-reset"
           style="padding:8px 14px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;
                  color:#374151;text-decoration:none;white-space:nowrap;
                  {{ ($filters['search'] || $filters['userId'] || $filters['methods'] || $filters['from'] || $filters['to'] || !$filters['hideOwn']) ? '' : 'display:none;' }}">
            Сбросить
        </a>
    </div>
</form>

{{-- Таблица (перерисовывается целиком при каждой смене фильтра, без полной
     перезагрузки страницы; клик по пагинации внутри — обычная навигация,
     GET-параметры фильтров она сохраняет за счёт withQueryString() на бэке) --}}
<div id="al-table">
    @include('components.activity-log-table', ['logs' => $logs])
</div>

<script>
let alFilterTimer = null;

function debouncedApplyFilters() {
    clearTimeout(alFilterTimer);
    alFilterTimer = setTimeout(applyFilters, 300);
}

function onUserChange(select) {
    // Выбор конкретного пользователя делает "скрыть мои действия" бессмысленным —
    // сервер всё равно игнорирует hide_own, когда задан user_id (см. контроллер),
    // но чекбокс лучше явно отключить, чтобы не выглядело как рабочая комбинация.
    document.getElementById('hide_own').disabled = !!select.value;
    applyFilters();
}

function applyFilters() {
    const form = document.getElementById('al-filters');
    const params = new URLSearchParams(new FormData(form));

    const url = '{{ route('activity.logs') }}?' + params.toString();
    const table = document.getElementById('al-table');
    table.style.opacity = '.6';

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
        .then(r => {
            const total = r.headers.get('X-Total-Count');
            if (total !== null) document.getElementById('al-count').textContent = total;
            return r.text();
        })
        .then(html => {
            table.innerHTML = html;
            table.style.opacity = '1';

            // URL адресной строки и панель экспорта — держим в актуальном состоянии.
            window.history.replaceState({}, '', url);
            const hasFilters = params.get('q') || params.get('user_id') || params.getAll('method[]').length
                || params.get('from') || params.get('to') || params.get('hide_own') !== '1';
            document.getElementById('al-reset').style.display = hasFilters ? '' : 'none';
        })
        .catch(() => { table.style.opacity = '1'; });
}

function syncExportFilters() {
    const form = document.getElementById('al-filters');
    document.getElementById('export-q').value = form.q.value;
    document.getElementById('export-user_id').value = form.user_id.value;
    document.getElementById('export-hide_own').value = document.getElementById('hide_own_value').value;

    const methodsBox = document.getElementById('export-methods');
    methodsBox.innerHTML = '';
    form.querySelectorAll('input[name="method[]"]:checked').forEach(cb => {
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'method[]';
        hidden.value = cb.value;
        methodsBox.appendChild(hidden);
    });
}
</script>

@endsection
