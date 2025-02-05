@props(['employee', 'availableTablets'])

<div class="mt-8 bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-xl font-semibold text-gray-800">Tablet Assignment</h2>

    @if($employee->tablets->isNotEmpty())
        <div class="mt-4">
            <p class="text-lg text-gray-600">
                <span class="font-medium text-gray-800">Tablets:</span>
                <ul class="space-y-4">
                    @foreach($employee->tablets as $tablet)
                        <li class="flex items-center justify-between text-sm text-gray-600 py-2 border-b border-gray-200">
                            <span>{{ $tablet->invent_number }} - {{ $tablet->serial_number }}</span>
                            <div class="flex items-center space-x-4">
                                <form action="/print-act/{{$employee->id}}/{{$tablet->id}}" method="POST">
                                    @csrf
                                    <button class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                                        APP
                                    </button>
                                </form>

                                @if($tablet->pdfAssignment && $tablet->pdfAssignment->pdf_path)
                                    <!-- Если PDF загружен, показываем ссылку -->
                                    <a href="{{ asset('storage/' . $tablet->pdfAssignment->pdf_path) }}" target="_blank" class="text-blue-500 underline">
                                        Открыть PDF
                                    </a>
                                @else
                                    <!-- Если PDF нет, показываем форму загрузки -->
                                    <form action="/upload-assign-pdf/{{ $employee->id }}/{{ $tablet->id }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <label class="font-bold">Выберите PDF:</label>
                                        <input type="file" name="pdf_file" accept="application/pdf" required class="border rounded p-1">
                                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
                                            Загрузить
                                        </button>
                                    </form>
                                @endif

                                <form action="{{ route('unassign-tablet', ['employee' => $employee->id, 'tablet' => $tablet->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to unassign the tablet?');">
                                    @csrf
                                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">
                                        Unassign tablet
                                    </button>
                                </form>


                                <!-- Кнопка Unassign tablet -->
                                <button id="openModalBtn" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">
                                    Unassign with PDF
                                </button>

                                <!-- Затемненный фон и модальное окно -->
                                <div id="modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden">
                                    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
                                        <h2 class="text-lg font-bold mb-4">Прикрепите PDF перед отвязкой</h2>

                                        <!-- Форма загрузки PDF -->
                                        <form id="uploadForm" action="{{ route('upload-unassign-pdf', [$employee->id, $tablet->id]) }}"
                                            method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <input type="file" name="unassign_pdf" accept="application/pdf" required class="border rounded p-1 w-full mb-4">

                                            <div class="flex justify-between">
                                                <button type="button" id="closeModalBtn" class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded">
                                                    Отмена
                                                </button>
                                                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
                                                    Загрузить PDF
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





                                {{-- @if($tablet->pdfAssignment && $tablet->pdfAssignment->pdf_path) --}}
                                    {{-- <form action="/upload-unassign-pdf" method="POST" enctype="multipart/form-data" class="mb-4">
                                        @csrf
                                        <label class="font-bold">Прикрепите PDF перед отвязкой:</label>
                                        <input type="file" name="unassign_pdf" accept="application/pdf" required class="border rounded p-1">
                                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
                                            Загрузить PDF
                                        </button>
                                    </form> --}}
                                {{-- @endif --}}

                            </div>

                        </li>

                    @endforeach
                </ul>
            </p>
        </div>
    @else
        <p class="text-lg text-gray-600">No tablets assigned</p>
    @endif

    <!-- Tablet Assign Form -->
    <form action="/assign-tablet/{{$employee->id}}" method="POST" class="mt-4">
        @csrf
        <label for="tablet" class="block text-sm font-medium text-gray-600">Assign Tablet</label>
        <select id="tablet" name="tablet_id" class="w-full p-3 border rounded-lg mt-2">
            <option value="">No Tablet</option>
            @foreach ($availableTablets as $tablet)
                <option value="{{ $tablet->id }}">{{ $tablet->invent_number }} - {{ $tablet->serial_number }} - {{ $tablet->old_employee_id ?? ''}}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-primary mt-4 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Assign</button>
    </form>
</div>

<script>
    document.getElementById('toggle-bricks-btn').addEventListener('click', function () {
        const bricksList = document.getElementById('bricks-list');
        bricksList.classList.toggle('hidden'); // Показываем или скрываем список
    });
</script>
