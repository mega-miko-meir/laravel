{{-- Таблица + пагинация — отдельный partial, чтобы AJAX-фильтрация могла
     перерисовывать только его, без полного рендера страницы. --}}
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
                <tr style="border-top:1px solid #f5f5f5;{{ $log->user_id === auth()->id() ? 'background:#fffbeb;' : '' }}"
                    onmouseover="this.style.background='#fafafa';"
                    onmouseout="this.style.background='{{ $log->user_id === auth()->id() ? '#fffbeb' : 'none' }}';">

                    <td style="padding:9px 16px;color:#111827;font-weight:500;">
                        {{ $log->user?->full_name ?? '—' }}
                        @if($log->user_id === auth()->id())
                            <span style="font-size:10px;color:#b45309;font-weight:600;">(вы)</span>
                        @endif
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
                        Нет данных по выбранным фильтрам
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:16px;">
    {{ $logs->links() }}
</div>
