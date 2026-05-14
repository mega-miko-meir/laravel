<div id="editModalPdf"
     style="display:none;position:fixed;inset:0;z-index:60;
            align-items:center;justify-content:center;
            background:rgba(0,0,0,.45);">

    <div style="background:#fff;border-radius:14px;padding:24px;
                width:100%;max-width:360px;margin:0 16px;
                box-shadow:0 20px 60px rgba(0,0,0,.2);">

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <p id="editModalPdfTitle" style="font-size:15px;font-weight:700;color:#111827;">
                Загрузить PDF
            </p>
            <button onclick="closePdfModal()"
                    style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;
                           background:#f3f4f6;border:none;border-radius:7px;cursor:pointer;"
                    onmouseover="this.style.background='#e5e7eb';"
                    onmouseout="this.style.background='#f3f4f6';">
                <svg style="width:14px;height:14px;color:#6b7280;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="editFormPdf" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <input type="hidden" id="pdf_pivot_id"  name="record_pivot_id">
            <input type="hidden" id="pdf_field_name" name="field_name">

            <label style="display:block;font-size:11px;font-weight:600;text-transform:uppercase;
                          letter-spacing:.05em;color:#9ca3af;margin-bottom:6px;">
                PDF файл
            </label>

            <input type="file" name="pdf_value" accept="application/pdf" required
                   style="width:100%;font-size:12px;color:#374151;border:1px solid #e5e7eb;
                          border-radius:8px;padding:7px 8px;box-sizing:border-box;margin-bottom:16px;">

            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" onclick="closePdfModal()"
                        style="padding:8px 16px;font-size:13px;color:#374151;background:#fff;
                               border:1px solid #e5e7eb;border-radius:8px;cursor:pointer;">
                    Отмена
                </button>
                <button type="submit"
                        style="padding:8px 16px;font-size:13px;font-weight:600;color:#fff;
                               background:#2563eb;border:none;border-radius:8px;cursor:pointer;"
                        onmouseover="this.style.background='#1d4ed8';"
                        onmouseout="this.style.background='#2563eb';">
                    Сохранить
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openPdfModal(pivotId, fieldName) {
    fieldName = fieldName || 'pdf_path';

    document.getElementById('pdf_pivot_id').value   = pivotId;
    document.getElementById('pdf_field_name').value = fieldName;

    document.getElementById('editFormPdf').action =
        `/employee-tablet/${pivotId}/updatePdf`;

    document.getElementById('editModalPdfTitle').textContent =
        fieldName === 'unassign_pdf' ? 'Акт возврата (PDF)' : 'Акт выдачи (PDF)';

    const modal = document.getElementById('editModalPdf');
    modal.style.display = 'flex';
}

function closePdfModal() {
    document.getElementById('editModalPdf').style.display = 'none';
}
</script>
