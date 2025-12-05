<div id="editModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-lg font-semibold mb-4">Редактировать PDF</h2>
        <form id="editForm" method="POST">
            @csrf
            @method('PATCH')
            <input type="hidden" id="record_pivot_id" name="record_pivot_id">
            <input type="hidden" id="field_name" name="field_name">

            <label for="pdf" class="block text-sm font-medium text-gray-600">Новая дата:</label>
            {{-- <input type="date" id="" name="date_value" class="w-full p-2 border rounded-lg mt-2"> --}}
            <input type="file" id="pdf" name="field" accept="application/pdf" required class="border rounded p-1 w-full mb-4">


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
    function openPdfModal(recordPivotId, fieldName, currentValue, recordType) {
        document.getElementById('record_pivot_id').value = recordPivotId;
        document.getElementById('field_name').value = fieldName;

        let formAction = '';
        formAction = `/employee-tablet/${recordPivotId}/update`;
        // document.getElementById('editForm').action = `/employee-territory/${recordPivotId}/update`;
        document.getElementById('editForm').action = formAction;
        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }
</script>
