{{-- Плоская таблица: специальность -> число уникальных врачей, целиком на клиенте:
     $listExpr — JS-выражение (родительский x-data targetClients()), возвращающее
     массив [{specialty, client_count}], уже отфильтрованный по выбранному отделу. --}}
<div style="background:#fff;border:1px solid #f0f0f0;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.05);">
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
        <thead>
            <tr style="background:#f9fafb;border-bottom:1px solid #f0f0f0;">
                <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Специальность</th>
                <th style="padding:10px 16px;text-align:right;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Уникальных врачей</th>
            </tr>
        </thead>
        <tbody>
            <template x-if="({{ $listExpr }}).length === 0">
                <tr>
                    <td colspan="2" style="padding:32px;text-align:center;color:#9ca3af;font-size:13px;">Нет данных за этот период</td>
                </tr>
            </template>
            <template x-for="s in {{ $listExpr }}" :key="s.specialty">
                <tr style="border-bottom:1px solid #f9fafb;">
                    <td style="padding:10px 16px;color:#111827;" x-text="s.specialty"></td>
                    <td style="padding:10px 16px;text-align:right;font-weight:600;color:#2563eb;" x-text="fmt(s.client_count)"></td>
                </tr>
            </template>
        </tbody>
    </table>
</div>
