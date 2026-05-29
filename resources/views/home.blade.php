@extends('layout')

@section('content')
@auth

<br>
{{-- Тулбар --}}
<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px;">

    <h1 style="font-size:20px;font-weight:700;color:#111827;">
        Сотрудники
        <span style="font-size:13px;font-weight:500;color:#9ca3af;margin-left:6px;">
            <span id="employee-count">{{ $employees->count() }}</span>
        </span>
    </h1>

    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">

        {{-- Переключатель активных --}}
        <x-active-checkbox label="Активные" />

        {{-- Экспорт --}}
        <div x-data="{ exportOpen: false }" style="position:relative;">
            <button @click="exportOpen = !exportOpen"
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
                <svg style="width:13px;height:13px;color:#9ca3af;" :class="{'rotate-180':exportOpen}"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="exportOpen" @click.away="exportOpen=false" x-cloak
                 style="position:absolute;right:0;top:calc(100% + 6px);width:280px;
                        background:#fff;border:1px solid #e5e7eb;border-radius:10px;
                        box-shadow:0 4px 20px rgba(0,0,0,.1);z-index:50;padding:16px;">

                <form action="{{ route('export.excel') }}" method="POST">
                    @csrf
                    <p style="font-size:13px;font-weight:600;color:#374151;margin-bottom:10px;">Выберите колонки:</p>

                    <div style="display:flex;flex-direction:column;gap:6px;font-size:12px;color:#374151;">
                        @foreach([
                            ['full_name','ФИО',true],['first_name_eng','ФИО англ',false],
                            ['role','Позиция',true],['city','Город',false],
                            ['email','Почта',false],['team','Группа',true],
                            ['department','Департамент',true],['manager','Менеджер',false],
                            ['hiring_date','Дата приема',false],
                        ] as [$val,$lbl,$chk])
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                                <input type="checkbox" name="columns[]" value="{{ $val }}" {{ $chk ? 'checked' : '' }}
                                       style="width:14px;height:14px;accent-color:#2563eb;">
                                {{ $lbl }}
                            </label>
                        @endforeach

                        <hr style="border:none;border-top:1px solid #f0f0f0;margin:6px 0;">
                        <p style="font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Стаж работы:</p>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                            <input type="checkbox" name="with_experience" value="1"
                                   style="width:14px;height:14px;accent-color:#2563eb;">
                            Выгружать стаж
                        </label>
                        <div style="margin-top:4px;">
                            <p style="font-size:11px;color:#9ca3af;margin-bottom:4px;">На дату:</p>
                            <input type="date" name="experience_date" value="{{ now()->toDateString() }}"
                                   style="width:100%;padding:6px 8px;border:1px solid #e5e7eb;border-radius:7px;
                                          font-size:12px;outline:none;box-sizing:border-box;">
                        </div>
                    </div>

                    <div style="display:flex;justify-content:flex-end;margin-top:12px;">
                        <button type="submit"
                                style="padding:6px 16px;background:#2563eb;color:#fff;border:none;
                                       border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;"
                                onmouseover="this.style.background='#1d4ed8';"
                                onmouseout="this.style.background='#2563eb';">
                            Скачать
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @can('editor')
            <a href="/create-employee"
               style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;
                      background:#2563eb;color:#fff;border:none;border-radius:8px;
                      font-size:13px;font-weight:600;text-decoration:none;"
               onmouseover="this.style.background='#1d4ed8';"
               onmouseout="this.style.background='#2563eb';">
                <svg style="width:15px;height:15px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Добавить
            </a>
        @endcan
    </div>
</div>

{{-- Поиск --}}
<div style="margin-bottom:16px;">
    <div style="position:relative;max-width:520px;">
        <svg style="position:absolute;left:10px;top:50%;transform:translateY(-50%);
                    width:16px;height:16px;color:#9ca3af;pointer-events:none;"
             fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input id="live-search"
               type="text"
               value="{{ request('search') }}"
               placeholder="Имя, email, группа, город..."
               autocomplete="off"
               style="width:100%;padding:8px 36px 8px 34px;border:1px solid #e5e7eb;
                      border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;background:#fff;"
               onfocus="this.style.borderColor='#2563eb';"
               onblur="this.style.borderColor='#e5e7eb';">
        {{-- Кнопка очистки --}}
        <button id="search-clear"
                onclick="clearSearch()"
                style="display:none;position:absolute;right:10px;top:50%;transform:translateY(-50%);
                       background:none;border:none;cursor:pointer;color:#9ca3af;padding:0;line-height:1;"
                title="Очистить">
            <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>

{{-- Таблица сотрудников --}}
<div id="employees-container">
    <x-employee-card :employees="$employees" :sort="$sort" :order="$order"/>
</div>

<script>
(function () {
    let searchTimer = null;
    let currentSort  = '{{ request('sort', 'latest_event_date') }}';
    let currentOrder = '{{ request('order', 'desc') }}';

    const input     = document.getElementById('live-search');
    const clearBtn  = document.getElementById('search-clear');
    const container = document.getElementById('employees-container');
    const counter   = document.getElementById('employee-count');

    function getActiveOnly() {
        const cb = document.getElementById('ticker');
        return cb ? (cb.checked ? 1 : 0) : 1;
    }

    function updateCounter() {
        const rows = container.querySelectorAll('tbody tr');
        // Если одна строка с colspan — это «не найдено», показываем 0
        const isEmpty = rows.length === 1 && rows[0].querySelector('td[colspan]');
        counter.textContent = isEmpty ? 0 : rows.length;
    }

    function doSearch() {
        const params = new URLSearchParams({
            search:      input.value,
            active_only: getActiveOnly(),
            sort:        currentSort,
            order:       currentOrder,
        });

        fetch('/?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.text())
        .then(html => {
            container.innerHTML = html;
            updateCounter();
        })
        .catch(console.error);
    }

    // Живой поиск с дебаунсом 300 мс
    input.addEventListener('input', function () {
        clearBtn.style.display = this.value ? 'block' : 'none';
        clearTimeout(searchTimer);
        searchTimer = setTimeout(doSearch, 300);
    });

    // Очистка
    window.clearSearch = function () {
        input.value = '';
        clearBtn.style.display = 'none';
        doSearch();
        input.focus();
    };

    // Переключатель «Активные»
    const checkbox = document.getElementById('ticker');
    if (checkbox) {
        checkbox.addEventListener('change', doSearch);
    }

    // Показать кнопку очистки если поиск уже введён при загрузке
    if (input.value) clearBtn.style.display = 'block';
})();
</script>

@else
    <x-auth-container />
@endauth
@endsection
