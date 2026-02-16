<div id="editModalPdf"
     class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center">

    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
        <h2 class="text-lg font-semibold mb-4">Загрузить PDF</h2>

        <form id="editFormPdf"
              method="POST"
              enctype="multipart/form-data">

            @csrf
            <input type="hidden" id="record_pivot_id" name="record_pivot_id">
            <input type="hidden" id="field_name" name="field_name">

            <label class="block text-sm font-medium text-gray-600">
                PDF файл:
            </label>

            <input type="file"
                   name="pdf_file"
                   accept="application/pdf"
                   required
                   class="border rounded p-1 w-full mb-4">


            <div class="flex justify-end gap-2">
                <button type="button"
                        onclick="closePdfModal()"
                        class="px-4 py-2 bg-gray-300 rounded">
                    Отмена
                </button>

                <button type="submit"
                        class="px-4 py-2 bg-green-500 text-white rounded">
                    ⬆ Upload
                </button>
            </div>
        </form>
    </div>
</div>


<script>
function openPdfModal(recordPivotId) {

    document.getElementById('editFormPdf').action =
        `/upload-pdf/${recordPivotId}`;

    document.getElementById('editModalPdf').classList.remove('hidden');
}

function closePdfModal() {
    document.getElementById('editModalPdf').classList.add('hidden');
}
</script>
