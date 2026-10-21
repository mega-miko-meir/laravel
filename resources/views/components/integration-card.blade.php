@props(['integration'])

{{-- Одна карточка источника данных на странице /admin/integrations. Рендерит
     чекбоксы отчётов / выбор месяца только если интеграция их поддерживает
     (см. config/integrations.php) — остальным интеграциям (будущим: КМП и
     т.д.) достаточно просто не указывать supports_month/supports_reports,
     карточка сама подстроится под одну общую кнопку «Обновить». --}}

<div x-data="integrationSync(@js($integration['slug']), @js($integration))" x-init="init()"
     style="background:#fff;border:1px solid #f0f0f0;border-radius:12px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,.05);">

    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
        <span style="font-size:15px;font-weight:700;color:#111827;">{{ $integration['label'] }}</span>
        <span class="etl-badge" :style="`background:${badgeBg};color:${badgeColor};`" x-text="badgeLabel"></span>
    </div>

    @if(!empty($integration['description']))
        <p style="font-size:12px;color:#9ca3af;margin:5px 0 0;">{{ $integration['description'] }}</p>
    @endif

    <p style="font-size:11px;color:#9ca3af;margin:6px 0 0;">
        Последняя синхронизация: <span x-text="lastSyncLabel"></span>
    </p>

    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:14px;">
        @if(!empty($integration['supports_reports']))
            <div class="etl-reports">
                @foreach($integration['reports'] as $num => $name)
                    <label title="{{ $name }}">
                        <input type="checkbox" value="{{ $num }}" x-model.number="selectedReports" :disabled="running">
                        <span>{{ $num }}</span>
                    </label>
                @endforeach
            </div>
        @endif

        @if(!empty($integration['supports_month']))
            <input type="month" x-model="selectedMonth" :disabled="running"
                   value="{{ now()->format('Y-m') }}" max="{{ now()->format('Y-m') }}"
                   class="etl-month-input" title="Месяц для обновления">
        @endif

        <button type="button" @click="trigger()" :disabled="running" class="etl-btn">
            <svg x-show="!running" style="width:14px;height:14px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <svg x-show="running" x-cloak style="width:14px;height:14px;flex-shrink:0;color:#2563eb;animation:etl-spin 1s linear infinite;" fill="none" viewBox="0 0 24 24">
                <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span x-text="running ? 'Идёт обновление...' : 'Обновить'"></span>
        </button>

        {{-- Видно, только пока статус "running" — снимает отображаемую
             "зависшую" пометку без доступа к серверу (пишет тот же файл
             статуса от имени веб-сервера, которому всегда хватает прав, в
             отличие от SFTP-пользователя на сервере). Не останавливает
             реальный процесс, если он ещё жив — только сбрасывает индикатор. --}}
        <button type="button" x-show="running" x-cloak @click="resetStatus()"
                style="background:none;border:none;color:#9ca3af;font-size:12px;text-decoration:underline;cursor:pointer;padding:0;">
            Сбросить статус
        </button>
    </div>
</div>
