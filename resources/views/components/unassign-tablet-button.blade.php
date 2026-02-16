@props(['employee', 'tablet'])

<div class="flex flex-col space-y-2">
    {{-- <input type="date" name="returned_at" id="returned_at" value="{{now()->format("Y-d-m")}}"> --}}
    <button id="openModalBtn" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-1 px-3 rounded text-xs">
        Отвязать
    </button>
</div>

<!-- Затемненный фон и модальное окно -->
<div id="modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
        <h2 class="text-lg font-bold mb-4">Прикрепите PDF перед отвязкой</h2>

        <!-- Форма загрузки PDF -->
        <form id="uploadForm" action="{{ route('unassign-tablet', [$employee->id, $tablet->id]) }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            <input type="file" name="unassign_pdf" accept="application/pdf" class="border rounded p-1 w-full mb-4">
            <input type="date" name="returned_at" required class="border rounded p-1 w-full mb-4"  value="{{now()->format("Y-m-d")}}">

            <div class="flex justify-between">
                <button type="button" id="closeModalBtn" class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded">
                    Отмена
                </button>
                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
                    Отвязать
                </button>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const modal = document.getElementById("modal");
        const openModalBtn = document.getElementById("openModalBtn");
        const closeModalBtn = document.getElementById("closeModalBtn");
        const uploadForm = document.getElementById("uploadForm");

        // Открыть модальное окно
        openModalBtn.addEventListener("click", function () {
            modal.classList.remove("hidden");
        });

        // Закрыть модальное окно
        closeModalBtn.addEventListener("click", function () {
            modal.classList.add("hidden");
        });

        // При отправке формы скрываем модальное окно и обновляем страницу
        uploadForm.addEventListener("submit", function () {
            setTimeout(() => {
                location.reload();
            }, 1500);
        });
    });
</script>
