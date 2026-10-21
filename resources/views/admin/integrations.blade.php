@extends('layout')
@section('content')

<style>
    @keyframes etl-spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

    /* Alpine's :style replaces the whole style="" attribute on init — classes
       + :style ТОЛЬКО для действительно динамичных свойств, см. CLAUDE.md. */
    .etl-btn {
        display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px;
        background: #fff; color: #374151; border: 1px solid #e2e8f0; border-radius: 8px;
        font-size: 13px; font-weight: 600; cursor: pointer; white-space: nowrap;
    }
    .etl-btn:hover:not(:disabled) { background: #f9fafb; border-color: #c7d2fe; }
    .etl-btn:disabled { cursor: not-allowed; opacity: .65; }
    .etl-month-input {
        padding: 6px 10px; border: 1px solid #e2e8f0; border-radius: 8px;
        font-size: 13px; color: #374151; outline: none; background: #fff;
    }
    .etl-month-input:focus { border-color: #2563eb; }
    .etl-reports {
        display: flex; align-items: center; gap: 6px; padding: 5px 10px;
        border: 1px solid #e2e8f0; border-radius: 8px; background: #fff;
    }
    .etl-reports label {
        display: flex; align-items: center; gap: 3px; font-size: 12px;
        color: #6b7280; cursor: pointer; user-select: none;
    }
    .etl-reports input[type="checkbox"] { width: 13px; height: 13px; accent-color: #2563eb; cursor: pointer; }
    .etl-badge {
        display: inline-flex; align-items: center; padding: 2px 9px;
        border-radius: 9999px; font-size: 11px; font-weight: 600;
    }
</style>

<div style="margin-bottom:20px;">
    <h1 style="font-size:22px;font-weight:700;color:#1e3a8a;margin:0;">Интеграции</h1>
    <p style="font-size:13px;color:#64748b;margin:4px 0 0;">
        Ручной запуск синхронизации данных из внешних систем — на случай, если автозапуск по расписанию не сработал.
    </p>
</div>

<div style="display:flex;flex-direction:column;gap:14px;max-width:760px;">
    @forelse($integrations as $integration)
        <x-integration-card :integration="$integration" />
    @empty
        <p style="font-size:13px;color:#9ca3af;">Пока не настроено ни одной интеграции.</p>
    @endforelse
</div>

{{-- Toast с результатом запуска --}}
<div x-data="etlToast()" x-show="visible" x-cloak
     :style="`position:fixed;bottom:20px;right:20px;z-index:9999;max-width:360px;padding:12px 18px;
             border-radius:8px;color:#fff;font-size:13px;font-weight:500;line-height:1.4;
             box-shadow:0 4px 16px rgba(0,0,0,.18);background:${ok ? '#16a34a' : '#dc2626'};`"
     x-text="message"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
</div>

<script>
    window.etlToastState = window.etlToastState || { visible: false, ok: true, message: '', _hideTimer: null };

    function etlToast() {
        return window.etlToastState;
    }

    function etlShowToast(ok, message) {
        const s = window.etlToastState;
        s.ok = ok;
        s.message = message;
        s.visible = true;
        clearTimeout(s._hideTimer);
        s._hideTimer = setTimeout(() => { s.visible = false; }, 6000);
    }

    function etlFormatDate(raw) {
        if (!raw) return 'ещё не запускалось';
        // "2026-10-21 14:00:56" -> "21.10.2026 14:00"
        const m = raw.match(/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2})/);
        return m ? `${m[3]}.${m[2]}.${m[1]} ${m[4]}:${m[5]}` : raw;
    }

    const ETL_BADGES = {
        success: ['Успешно',          '#dcfce7', '#15803d'],
        failed:  ['Ошибка',           '#fee2e2', '#b91c1c'],
        running: ['Выполняется...',   '#dbeafe', '#1d4ed8'],
        idle:    ['Ещё не запускалось', '#f3f4f6', '#6b7280'],
    };

    function integrationSync(slug, integration) {
        return {
            slug: slug,
            running: false,
            pollTimer: null,
            selectedMonth: '{{ now()->format('Y-m') }}',
            selectedReports: integration.reports ? Object.keys(integration.reports).map(Number) : [],
            badgeLabel: 'Ещё не запускалось',
            badgeBg: '#f3f4f6',
            badgeColor: '#6b7280',
            lastSyncLabel: 'ещё не запускалось',

            init() {
                this.applyStatus(integration.status || { status: 'idle' }, true);
            },

            trigger() {
                if (this.running) return;
                if (integration.supports_reports && this.selectedReports.length === 0) {
                    etlShowToast(false, 'Выберите хотя бы один отчёт');
                    return;
                }

                this.running = true;
                const body = {};
                if (integration.supports_month) body.month = this.selectedMonth;
                if (integration.supports_reports) body.reports = this.selectedReports;

                fetch(`{{ url('/admin/integrations') }}/${this.slug}/run`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(body),
                })
                    .then(r => r.json().then(data => ({ ok: r.ok, data })))
                    .then(({ ok, data }) => {
                        if (!ok) {
                            this.running = false;
                            etlShowToast(false, data.message || 'Не удалось запустить обновление');
                            return;
                        }
                        this.startPolling();
                    })
                    .catch(() => {
                        this.running = false;
                        etlShowToast(false, 'Не удалось запустить обновление');
                    });
            },

            startPolling() {
                this.running = true;
                clearInterval(this.pollTimer);
                this.pollTimer = setInterval(() => this.checkStatus(false), 4000);
            },

            resetStatus() {
                if (!confirm('Сбросить статус? Если синхронизация ещё реально выполняется в фоне, это её НЕ остановит — только уберёт пометку "Выполняется..." с экрана. Сбрасывайте, только если уверены, что процесс завис.')) {
                    return;
                }
                fetch(`{{ url('/admin/integrations') }}/${this.slug}/reset`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                })
                    .then(r => r.json())
                    .then(data => {
                        clearInterval(this.pollTimer);
                        this.pollTimer = null;
                        this.applyStatus(data, true);
                    })
                    .catch(() => etlShowToast(false, 'Не удалось сбросить статус'));
            },

            checkStatus(silent) {
                fetch(`{{ url('/admin/integrations') }}/${this.slug}/status`, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                })
                    .then(r => r.json())
                    .then(data => this.applyStatus(data, silent))
                    .catch(() => {});
            },

            applyStatus(data, silent) {
                const wasRunning = this.running;
                const status = data.status || 'idle';

                if (status === 'running') {
                    this.running = true;
                    if (!this.pollTimer) this.startPolling();
                } else {
                    this.running = false;
                    clearInterval(this.pollTimer);
                    this.pollTimer = null;

                    if (!silent && wasRunning) {
                        if (status === 'success') etlShowToast(true, data.message || 'Данные успешно обновлены');
                        else if (status === 'failed') etlShowToast(false, data.message || 'Обновление завершилось с ошибкой');
                    }
                }

                const [label, bg, color] = ETL_BADGES[status] || ETL_BADGES.idle;
                this.badgeLabel = label;
                this.badgeBg = bg;
                this.badgeColor = color;
                this.lastSyncLabel = etlFormatDate(data.finished_at || data.started_at);
            },
        };
    }
</script>

@endsection
