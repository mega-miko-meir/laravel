@props(['employee'])

<div class="mt-6 bg-white p-4 rounded-lg shadow-md relative">
    <!-- Заголовок и статус -->
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-semibold text-gray-800">Информация о сотруднике</h1>
        <div class="flex items-center gap-2">
            <x-status-badge :status="$employee->status" />
            <button onclick="toggleEditForm()" class="text-blue-600 text-sm hover:underline">Редактировать</button>
        </div>
    </div>

    <!-- Данные о сотруднике -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4 text-m text-gray-700">
        <p><span class="font-medium">Имя:</span> {{ $employee->first_name }} {{ $employee->last_name }}</p>
        <p><span class="font-medium">Email:</span> {{ $employee->email }}</p>
        <p><span class="font-medium">Должность:</span> {{ $employee->position }}</p>
        @if($employee->territories->isNotEmpty())
            <p><span class="font-medium">Команда:</span> {{ $employee->territories->first()->team }}</p>
            <p><span class="font-medium">Город:</span> {{ $employee->territories->first()->city }}</p>
            <p><span class="font-medium">Роль:</span> {{ $employee->territories->first()->role }}</p>
            <p><span class="font-medium">Менеджер:</span> {{ $employee->territories->first()->manager_id }}</p>
        @endif
    </div>

    <!-- Дата найма -->
    <p class="text-sm text-gray-700 mb-4">
        <span class="font-medium">Дата найма:</span> {{ \Carbon\Carbon::parse($employee->hiring_date)->format('d.m.Y') }}
    </p>

    <!-- Форма обновления статуса (изначально скрыта) -->
    <form action="{{ route('employees.updateStatusAndEvent', $employee->id) }}" method="POST" id="editForm" class="bg-gray-50 p-4 rounded-lg shadow-sm hidden">
        @csrf
        @method('PUT')
        <label for="status" class="block text-sm font-medium mb-1">Выберите статус:</label>
        <select name="status" id="status" class="w-full p-2 border rounded text-sm">
            <option value="new" {{ $employee->status === 'new' ? 'selected' : '' }}>Новый</option>
            <option value="active" {{ $employee->status === 'active' ? 'selected' : '' }}>Активен</option>
            <option value="dismissed" {{ $employee->status === 'dismissed' ? 'selected' : '' }}>Уволен</option>
            <option value="maternity_leave" {{ $employee->status === 'maternity_leave' ? 'selected' : '' }}>Декрет</option>
            <option value="long_vacation" {{ $employee->status === 'long_vacation' ? 'selected' : '' }}>Длительный отпуск</option>
        </select>

        <div class="flex justify-end mt-3">
            <button type="button" onclick="toggleEditForm()" class="px-4 py-2 text-sm text-gray-600 border rounded hover:bg-gray-100 mr-2">Отмена</button>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                Обновить
            </button>
        </div>
    </form>

    <x-edit-employee-button :employee="$employee"/>

    <!-- Кнопка для показа таблицы -->
    <button id="showTableButton" class="mt-2 bg-blue-500 text-white px-3 py-1 text-sm rounded-md">КМП запрос</button>

    <!-- Модальное окно -->
    <div id="tableModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
        <div class="bg-white p-5 rounded-lg shadow-lg w-3/4 relative">
            <button id="copyTableBtn" class="absolute top-2 right-2 bg-gray-200 px-3 py-1 rounded">📋 Копировать</button>
            <button id="closeTableBtn" class="absolute top-2 left-2 bg-red-500 text-white px-3 py-1 rounded">✖ Закрыть</button>

            <div id="tableContainer" class="mt-5">
                <table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%; text-align: left; border: 1px solid black;">
                    <thead>
                        <tr style="background-color: #f2f2f2; border: 1px solid black;">
                            <th style="border: 1px solid black; padding: 8px;">ФИО</th>
                            <th style="border: 1px solid black; padding: 8px;">Должность</th>
                            <th style="border: 1px solid black; padding: 8px;">Группа</th>
                            <th style="border: 1px solid black; padding: 8px;">Город</th>
                            <th style="border: 1px solid black; padding: 8px;">РМ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="border: 1px solid black; padding: 8px;">{{ $employee->full_name }}</td>
                            <td style="border: 1px solid black; padding: 8px;">{{ $employee->position }}</td>
                            <td style="border: 1px solid black; padding: 8px;">{{ $employee->territories->first()->team ?? ''}}</td>
                            <td style="border: 1px solid black; padding: 8px;">{{ $employee->territories->first()->city ?? '' }}</td>
                            <td style="border: 1px solid black; padding: 8px;">{{ $employee->territories->first()->manager_id ?? '' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleEditForm() {
        document.getElementById('editForm').classList.toggle('hidden');
    }

    document.getElementById("showTableButton").addEventListener("click", function () {
        document.getElementById("tableModal").classList.remove("hidden");
    });

    document.getElementById("closeTableBtn").addEventListener("click", function () {
        document.getElementById("tableModal").classList.add("hidden");
    });

    document.getElementById("copyTableBtn").addEventListener("click", async function () {
        let table = document.querySelector("#tableContainer table");
        let text = "";

        for (let row of table.rows) {
            let rowData = [];
            for (let cell of row.cells) {
                rowData.push(cell.innerText);
            }
            text += rowData.join("\t") + "\n";
        }

        try {
            await navigator.clipboard.writeText(text);
            alert("Таблица скопирована!");
        } catch (err) {
            console.error("Ошибка копирования: ", err);
            alert("Не удалось скопировать таблицу.");
        }
    });
</script>
