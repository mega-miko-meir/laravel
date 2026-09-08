@extends('layout')

@section('content')
<br>

<h1 style="font-size:20px;font-weight:700;color:#111827;margin-bottom:4px;">Проверка данных</h1>
<p style="font-size:13px;color:#9ca3af;margin-bottom:20px;">
    Несостыковки между таблицами, которые стоит проверить руками. Раздел с 0 — сворачивается, всё остальное открыто по умолчанию.
</p>

<div style="max-width:900px;">

    <x-dq-section title="Дублирующиеся события" count="{{ $duplicateEvents->count() }}"
                  description="Один и тот же тип события с одной и той же датой записан больше одного раза.">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#f9fafb;border-bottom:1px solid #f0f0f0;">
                    <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">ФИО</th>
                    <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Событие</th>
                    <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Дата</th>
                    <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Записей</th>
                </tr>
            </thead>
            <tbody>
                @foreach($duplicateEvents as $row)
                    <tr style="border-bottom:1px solid #f9fafb;">
                        <td style="padding:9px 18px;">
                            <a href="/employee/{{ $row->employee_id }}" style="color:#2563eb;text-decoration:none;">{{ $row->full_name }}</a>
                        </td>
                        <td style="padding:9px 18px;"><x-status-badge :status="$row->event_type" /></td>
                        <td style="padding:9px 18px;color:#374151;">{{ \Carbon\Carbon::parse($row->event_date)->format('d.m.Y') }}</td>
                        <td style="padding:9px 18px;color:#b91c1c;font-weight:600;">{{ $row->cnt }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-dq-section>

    <x-dq-section title="Должность не совпадает с территорией" count="{{ $positionMismatches->count() }}"
                  description="У активного сотрудника employees.position отличается от роли на последней назначенной территории.">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#f9fafb;border-bottom:1px solid #f0f0f0;">
                    <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">ФИО</th>
                    <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">employees.position</th>
                    <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Роль по территории</th>
                </tr>
            </thead>
            <tbody>
                @foreach($positionMismatches as $row)
                    <tr style="border-bottom:1px solid #f9fafb;">
                        <td style="padding:9px 18px;">
                            <a href="/employee/{{ $row->id }}" style="color:#2563eb;text-decoration:none;">{{ $row->full_name }}</a>
                        </td>
                        <td style="padding:9px 18px;color:#374151;">{{ $row->position }}</td>
                        <td style="padding:9px 18px;color:#b91c1c;font-weight:600;">{{ $row->territory_role }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-dq-section>

    <x-dq-section title="Активные без привязки CRM" count="{{ $activeWithoutCrm->count() }}"
                  description="У сотрудника нет ни одного привязанного CRM-аккаунта — на карточке не будет блока визитов.">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#f9fafb;border-bottom:1px solid #f0f0f0;">
                    <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">ФИО</th>
                    <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Должность</th>
                </tr>
            </thead>
            <tbody>
                @foreach($activeWithoutCrm as $row)
                    <tr style="border-bottom:1px solid #f9fafb;">
                        <td style="padding:9px 18px;">
                            <a href="/employee/{{ $row->id }}" style="color:#2563eb;text-decoration:none;">{{ $row->full_name }}</a>
                        </td>
                        <td style="padding:9px 18px;color:#374151;">{{ $row->position }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-dq-section>

    <x-dq-section title="Активные без привязки KMP" count="{{ $activeWithoutKmp->count() }}"
                  description="У сотрудника нет ни одного привязанного имени КМП — на карточке не будет блока продаж.">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#f9fafb;border-bottom:1px solid #f0f0f0;">
                    <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">ФИО</th>
                    <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Должность</th>
                </tr>
            </thead>
            <tbody>
                @foreach($activeWithoutKmp as $row)
                    <tr style="border-bottom:1px solid #f9fafb;">
                        <td style="padding:9px 18px;">
                            <a href="/employee/{{ $row->id }}" style="color:#2563eb;text-decoration:none;">{{ $row->full_name }}</a>
                        </td>
                        <td style="padding:9px 18px;color:#374151;">{{ $row->position }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-dq-section>

    <x-dq-section title="Территории на уволенных" count="{{ $territoriesHeldByDismissed->count() }}"
                  description="Территория числится активно назначенной (не снята) сотруднику, чей статус — уволен.">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#f9fafb;border-bottom:1px solid #f0f0f0;">
                    <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Уволенный сотрудник</th>
                    <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Территория</th>
                    <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Назначена</th>
                </tr>
            </thead>
            <tbody>
                @foreach($territoriesHeldByDismissed as $row)
                    <tr style="border-bottom:1px solid #f9fafb;">
                        <td style="padding:9px 18px;">
                            <a href="/employee/{{ $row->employee_id }}" style="color:#2563eb;text-decoration:none;">{{ $row->full_name }}</a>
                        </td>
                        <td style="padding:9px 18px;">
                            <a href="{{ route('territories.show', $row->territory_id) }}" style="color:#2563eb;text-decoration:none;">{{ $row->territory_name }}</a>
                        </td>
                        <td style="padding:9px 18px;color:#374151;">{{ \Carbon\Carbon::parse($row->assigned_at)->format('d.m.Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-dq-section>

    <x-dq-section title="Планшеты на уволенных" count="{{ $tabletsHeldByDismissed->count() }}"
                  description="Планшет числится выданным (не возвращён) сотруднику, чей статус — уволен.">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#f9fafb;border-bottom:1px solid #f0f0f0;">
                    <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Уволенный сотрудник</th>
                    <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Планшет</th>
                    <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Выдан</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tabletsHeldByDismissed as $row)
                    <tr style="border-bottom:1px solid #f9fafb;">
                        <td style="padding:9px 18px;">
                            <a href="/employee/{{ $row->employee_id }}" style="color:#2563eb;text-decoration:none;">{{ $row->full_name }}</a>
                        </td>
                        <td style="padding:9px 18px;">
                            <a href="{{ route('tablets.show', $row->tablet_id) }}" style="color:#2563eb;text-decoration:none;">{{ $row->invent_number }}</a>
                        </td>
                        <td style="padding:9px 18px;color:#374151;">{{ \Carbon\Carbon::parse($row->assigned_at)->format('d.m.Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-dq-section>

    <x-dq-section title="Ответственный за планшет уволен" count="{{ $tabletsResponsibleDismissed->count() }}"
                  description="tablets.responsible_id указывает на сотрудника, который уже уволен.">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#f9fafb;border-bottom:1px solid #f0f0f0;">
                    <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Уволенный сотрудник</th>
                    <th style="padding:9px 18px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Планшет</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tabletsResponsibleDismissed as $row)
                    <tr style="border-bottom:1px solid #f9fafb;">
                        <td style="padding:9px 18px;">
                            <a href="/employee/{{ $row->employee_id }}" style="color:#2563eb;text-decoration:none;">{{ $row->full_name }}</a>
                        </td>
                        <td style="padding:9px 18px;">
                            <a href="{{ route('tablets.show', $row->tablet_id) }}" style="color:#2563eb;text-decoration:none;">{{ $row->invent_number }}</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-dq-section>

</div>

@endsection
