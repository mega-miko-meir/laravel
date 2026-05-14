@props(['employee', 'tablet', 'record' => null])

@php
    $hasPdf     = $record?->pdf_path ?? ($tablet->pdfAssignment?->pdf_path ?? null);
    $assignedAt = $record?->assigned_at
        ? \Carbon\Carbon::parse($record->assigned_at)->format('Y-m-d')
        : now()->format('Y-m-d');
    $tabletId   = $record?->tablet_id ?? $tablet->id;
@endphp

@if($hasPdf)
    <a href="{{ asset('storage/' . $hasPdf) }}" target="_blank"
       style="display:inline-flex;align-items:center;gap:5px;padding:5px 10px;
              background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;border-radius:7px;
              font-size:11px;font-weight:600;text-decoration:none;"
       onmouseover="this.style.background='#dbeafe';"
       onmouseout="this.style.background='#eff6ff';">
        <svg style="width:12px;height:12px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Акт выдачи
    </a>
@else
    <form action="/upload-assign-pdf/{{ $employee->id }}/{{ $tabletId }}"
          method="POST" enctype="multipart/form-data"
          style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
        @csrf
        <input type="file" name="pdf_file" accept="application/pdf"
               style="font-size:11px;color:#374151;border:1px solid #e5e7eb;border-radius:7px;
                      padding:4px 6px;max-width:160px;">
        <input type="date" name="assigned_at" value="{{ $assignedAt }}"
               style="font-size:11px;border:1px solid #e5e7eb;border-radius:7px;padding:4px 6px;">
        <button type="submit"
                style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;
                       background:#fff;color:#16a34a;border:1px solid #86efac;border-radius:7px;
                       font-size:11px;font-weight:600;cursor:pointer;"
                onmouseover="this.style.background='#f0fdf4';"
                onmouseout="this.style.background='#fff';">
            <svg style="width:12px;height:12px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            PDF
        </button>
    </form>
@endif
