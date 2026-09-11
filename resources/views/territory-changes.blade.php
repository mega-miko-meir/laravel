@extends('layout')

@section('content')
<br>

<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:6px;">
    <h1 style="font-size:20px;font-weight:700;color:#111827;margin:0;">Смена территории/группы/менеджера</h1>
</div>
<p style="font-size:13px;color:#9ca3af;margin-bottom:20px;">
    Сотрудники, у которых за период появилась новая территория, и при этом изменилась группа
    и/или менеджер по сравнению с предыдущей территорией. Первое назначение сотрудника
    (нет предыдущей территории для сравнения) в выборку не попадает.
</p>

<form method="GET" action="{{ route('admin.territory-changes') }}"
      style="display:flex;align-items:flex-end;gap:10px;flex-wrap:wrap;margin-bottom:16px;">
    <div>
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;">С даты</label>
        <input type="date" name="date_from" value="{{ $from }}"
               style="padding:7px 10px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;">
    </div>
    <div>
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;">По дату</label>
        <input type="date" name="date_to" value="{{ $to }}"
               style="padding:7px 10px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;">
    </div>
    <button type="submit"
            style="padding:8px 16px;background:#2563eb;color:#fff;border:none;border-radius:8px;
                   font-size:13px;font-weight:600;cursor:pointer;"
            onmouseover="this.style.background='#1d4ed8';" onmouseout="this.style.background='#2563eb';">
        Показать
    </button>
    <a href="{{ route('admin.territory-changes.export', ['date_from' => $from, 'date_to' => $to]) }}"
       style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;
              background:#fff;color:#374151;border:1px solid #e5e7eb;border-radius:8px;
              font-size:13px;font-weight:600;text-decoration:none;"
       onmouseover="this.style.background='#f9fafb';" onmouseout="this.style.background='#fff';">
        Выгрузить в Excel
    </a>

    <span style="font-size:12px;color:#9ca3af;margin-left:auto;">Найдено: {{ $changes->count() }}</span>
</form>

<div style="background:#fff;border:1px solid #f0f0f0;border-radius:12px;
            box-shadow:0 1px 3px rgba(0,0,0,.05);overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
        <thead>
            <tr style="background:#f9fafb;border-bottom:1px solid #f0f0f0;">
                <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">ФИО</th>
                <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Дата смены</th>
                <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Группа</th>
                <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Территория</th>
                <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;color:#6b7280;">Менеджер</th>
            </tr>
        </thead>
        <tbody>
            @forelse($changes as $c)
                <tr style="border-bottom:1px solid #f9fafb;">
                    <td style="padding:10px 16px;">
                        <a href="/employee/{{ $c->employee_id }}" style="color:#2563eb;text-decoration:none;font-weight:500;">
                            {{ $c->full_name }}
                        </a>
                    </td>
                    <td style="padding:10px 16px;color:#374151;">
                        {{ \Carbon\Carbon::parse($c->changed_at)->format('d.m.Y') }}
                    </td>
                    <td style="padding:10px 16px;color:#374151;">
                        @if($c->old_team !== $c->new_team)
                            <span style="color:#9ca3af;">{{ $c->old_team ?? '—' }}</span>
                            →
                            <span style="font-weight:600;">{{ $c->new_team ?? '—' }}</span>
                        @else
                            <span style="color:#9ca3af;">{{ $c->new_team ?? '—' }}</span>
                        @endif
                    </td>
                    <td style="padding:10px 16px;color:#374151;">
                        <span style="color:#9ca3af;">{{ $c->old_territory }}</span>
                        →
                        {{ $c->new_territory }}
                    </td>
                    <td style="padding:10px 16px;color:#374151;">
                        @if($c->old_manager !== $c->new_manager)
                            <span style="color:#9ca3af;">{{ $c->old_manager ?? '—' }}</span>
                            →
                            <span style="font-weight:600;">{{ $c->new_manager ?? '—' }}</span>
                        @else
                            <span style="color:#9ca3af;">{{ $c->new_manager ?? '—' }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="padding:32px;text-align:center;color:#9ca3af;font-size:13px;">
                        За этот период смен группы/менеджера не найдено
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
