@props(['employee'])

<div x-data="{ open: false }">

    <button @click="open = true"
            style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;
                   background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;border-radius:8px;
                   font-size:12px;font-weight:600;cursor:pointer;"
            onmouseover="this.style.background='#dbeafe';"
            onmouseout="this.style.background='#eff6ff';">
        <svg style="width:14px;height:14px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        КМП запрос
    </button>

    {{-- Модал --}}
    <div x-show="open" x-cloak
         style="position:fixed;inset:0;z-index:60;display:flex;align-items:center;
                justify-content:center;background:rgba(0,0,0,.45);">
        <div @click.outside="open = false"
             style="background:#fff;border-radius:14px;box-shadow:0 20px 60px rgba(0,0,0,.2);
                    padding:24px;width:100%;max-width:560px;margin:0 16px;position:relative;">

            {{-- Заголовок --}}
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                <p style="font-size:15px;font-weight:700;color:#111827;">КМП запрос</p>
                <div style="display:flex;gap:8px;">
                    <button @click="
                        const tbl = $el.closest('.relative').querySelector('table');
                        const html = '<html><head><meta charset=UTF-8><style>table{border-collapse:collapse;width:100%}th,td{border:1px solid #000;padding:8px;text-align:left}th{background:#f2f2f2}</style></head><body>' + tbl.outerHTML + '</body></html>';
                        navigator.clipboard.write([new ClipboardItem({'text/html': new Blob([html],{type:'text/html'})})]).then(()=>alert('Скопировано!')).catch(()=>alert('Ошибка копирования'));
                    "
                            style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;
                                   background:#f3f4f6;color:#374151;border:none;border-radius:7px;
                                   font-size:12px;font-weight:500;cursor:pointer;"
                            onmouseover="this.style.background='#e5e7eb';"
                            onmouseout="this.style.background='#f3f4f6';">
                        <svg style="width:13px;height:13px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                        </svg>
                        Копировать
                    </button>
                    <button @click="open = false"
                            style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;
                                   background:#fee2e2;color:#ef4444;border:none;border-radius:7px;cursor:pointer;"
                            onmouseover="this.style.background='#fecaca';"
                            onmouseout="this.style.background='#fee2e2';">
                        <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Таблица --}}
            @php
                $parts   = explode(' ', $employee->full_name);
                $kmpName = count($parts) > 2 ? implode(' ', array_slice($parts, 0, 2)) : $employee->full_name;
            @endphp
            <div style="overflow-x:auto;">
                <table style="border-collapse:collapse;width:100%;font-size:13px;">
                    <thead>
                        <tr style="background:#f9fafb;">
                            @foreach(['ФИО','Должность','Группа','Город','РМ'] as $col)
                                <th style="border:1px solid #e5e7eb;padding:10px 12px;
                                           font-size:11px;font-weight:600;text-align:left;
                                           color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">
                                    {{ $col }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="border:1px solid #e5e7eb;padding:10px 12px;color:#1f2937;">{{ $kmpName }}</td>
                            <td style="border:1px solid #e5e7eb;padding:10px 12px;color:#1f2937;">{{ $employee->position }}</td>
                            <td style="border:1px solid #e5e7eb;padding:10px 12px;color:#1f2937;">{{ $employee->territories->first()->team ?? '—' }}</td>
                            <td style="border:1px solid #e5e7eb;padding:10px 12px;color:#1f2937;">{{ $employee->territories->first()->city ?? '—' }}</td>
                            <td style="border:1px solid #e5e7eb;padding:10px 12px;color:#1f2937;">{{ $employee->territories->first()->parent->employee->full_name ?? '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
