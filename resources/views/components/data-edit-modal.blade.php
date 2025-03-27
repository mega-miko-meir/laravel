<div id="editModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-lg font-semibold mb-4">Редактировать дату</h2>
        <form id="editForm" method="POST">
            @csrf
            @method('PATCH')
            <input type="hidden" id="record_pivot_id" name="record_pivot_id">
            <input type="hidden" id="field_name" name="field_name">
            <input type="hidden" id="record_type" name="record_type">

            <label for="new_date" class="block text-sm font-medium text-gray-600">Новая дата:</label>
            <input type="date" id="new_date" name="date_value" class="w-full p-2 border rounded-lg mt-2">

            <div class="mt-4 flex justify-end">
                <button type="button" onclick="closeEditModal()" class="mr-2 px-4 py-2 bg-gray-300 rounded">
                    Отмена
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded">
                    Сохранить
                </button>
            </div>
        </form>
    </div>
</div>


<script>
    function openEditModal(recordPivotId, fieldName, currentValue, recordType) {
        document.getElementById('record_pivot_id').value = recordPivotId;
        document.getElementById('field_name').value = fieldName;
        document.getElementById('new_date').value = currentValue || '';
        document.getElementById('record_type').value = recordType;

        let formAction = '';
        if(recordType === 'territory'){
            formAction = `/employee-territory/${recordPivotId}/update`;
        } else if (recordType === 'tablet'){
            formAction = `/employee-tablet/${recordPivotId}/update`;
        }
        // document.getElementById('editForm').action = `/employee-territory/${recordPivotId}/update`;
        document.getElementById('editForm').action = formAction;
        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }
</script>
