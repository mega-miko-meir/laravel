<div id="editModal"
     style="display:none;position:fixed;inset:0;z-index:60;
            align-items:center;justify-content:center;
            background:rgba(0,0,0,.45);">
    <div style="background:#fff;border-radius:14px;padding:24px;
                width:100%;max-width:320px;margin:0 16px;
                box-shadow:0 20px 60px rgba(0,0,0,.2);">

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <p style="font-size:15px;font-weight:700;color:#111827;">Редактировать дату</p>
            <button onclick="closeEditModal()"
                    style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;
                           background:#f3f4f6;border:none;border-radius:7px;cursor:pointer;color:#6b7280;"
                    onmouseover="this.style.background='#e5e7eb';"
                    onmouseout="this.style.background='#f3f4f6';">
                <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="editForm" method="POST">
            @csrf
            @method('PATCH')
            <input type="hidden" id="record_pivot_id" name="record_pivot_id">
            <input type="hidden" id="field_name"      name="field_name">
            <input type="hidden" id="record_type"     name="record_type">

            <label style="display:block;font-size:11px;font-weight:600;text-transform:uppercase;
                          letter-spacing:.05em;color:#9ca3af;margin-bottom:6px;">
                Новая дата
            </label>
            <input type="date" id="new_date" name="date_value"
                   style="width:100%;padding:9px 10px;border:1px solid #e5e7eb;border-radius:8px;
                          font-size:13px;outline:none;box-sizing:border-box;margin-bottom:16px;">

            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" onclick="closeEditModal()"
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
function openEditModal(pivotId, fieldName, currentValue, recordType) {
    document.getElementById('record_pivot_id').value = pivotId;
    document.getElementById('field_name').value      = fieldName;
    document.getElementById('new_date').value        = currentValue ? currentValue.substring(0,10) : '';
    document.getElementById('record_type').value     = recordType;

    const actions = { territory: `/employee-territory/${pivotId}/update`, tablet: `/employee-tablet/${pivotId}/update` };
    document.getElementById('editForm').action = actions[recordType] || '';

    const modal = document.getElementById('editModal');
    modal.style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}
</script>
