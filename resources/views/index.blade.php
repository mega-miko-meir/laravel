@extends('layout')
@section('content')

{{-- Тулбар --}}
<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:20px;margin-top:24px;">

    <h1 style="font-size:20px;font-weight:700;color:#111827;margin:0;">
        Активность
        <span style="font-size:13px;font-weight:500;color:#9ca3af;margin-left:6px;">{{ $logs->total() }}</span>
    </h1>

    {{-- Выгрузить --}}
    <div x-data="{ open: false }" style="position:relative;">
        <button @click="open = !open"
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
                <p style="font-size:13px;font-weight:600;color:#374151;margin-bottom:12px;">Период выгрузки</p>

                <div style="margin-bottom:10px;">
                    <label style="display:block;font-size:11px;font-weight:600;text-transform:uppercase;
                                  letter-spacing:.05em;color:#9ca3af;margin-bottom:4px;">Дата начала</label>
                    <input type="date" name="from" required
                           style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:8px;
                                  font-size:13px;outline:none;box-sizing:border-box;">
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:11px;font-weight:600;text-transform:uppercase;
                                  letter-spacing:.05em;color:#9ca3af;margin-bottom:4px;">Дата окончания</label>
                    <input type="date" name="to" required
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

{{-- Таблица --}}
<div style="background:#fff;border:1px solid #f0f0f0;border-radius:12px;overflow:hidden;
            box-shadow:0 1px 3px rgba(0,0,0,.05);">
    <table style="width:100%;border-collapse:collapse;font-size:12px;">
        <thead>
            <tr style="background:#f9fafb;border-bottom:1px solid #f0f0f0;">
                <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;
                           text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">Пользователь</th>
                <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;
                           text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">URL</th>
                <th style="padding:10px 16px;text-align:center;font-size:10px;font-weight:600;
                           text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">Метод</th>
                <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;
                           text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">IP</th>
                <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;
                           text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">Дата</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr style="border-top:1px solid #f5f5f5;"
                    onmouseover="this.style.background='#fafafa';"
                    onmouseout="this.style.background='none';">

                    <td style="padding:9px 16px;color:#111827;font-weight:500;">
                        {{ $log->user?->full_name ?? '—' }}
                    </td>

                    <td style="padding:9px 16px;color:#6b7280;max-width:320px;
                               overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        {{ $log->url }}
                    </td>

                    <td style="padding:9px 16px;text-align:center;">
                        @php
                            $methodColors = [
                                'GET'    => 'background:#eff6ff;color:#2563eb;',
                                'POST'   => 'background:#f0fdf4;color:#16a34a;',
                                'PUT'    => 'background:#fffbeb;color:#d97706;',
                                'PATCH'  => 'background:#fffbeb;color:#d97706;',
                                'DELETE' => 'background:#fef2f2;color:#dc2626;',
                            ];
                            $style = $methodColors[$log->method] ?? 'background:#f3f4f6;color:#374151;';
                        @endphp
                        <span style="padding:2px 8px;border-radius:9999px;font-size:10px;font-weight:700;
                                     letter-spacing:.04em;{{ $style }}">
                            {{ $log->method }}
                        </span>
                    </td>

                    <td style="padding:9px 16px;color:#6b7280;font-family:monospace;font-size:11px;">
                        {{ $log->ip }}
                    </td>

                    <td style="padding:9px 16px;color:#9ca3af;white-space:nowrap;">
                        {{ $log->created_at->format('d.m.Y H:i') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:40px 16px;color:#9ca3af;font-size:13px;">
                        Нет данных
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:16px;">
    {{ $logs->links() }}
</div>

@endsection
