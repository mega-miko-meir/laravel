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
        <span class="font-medium">Дата найма:</span>
        {{ $employee->hiring_date ? \Carbon\Carbon::parse($employee->hiring_date)->format('d.m.Y') : '-'}}
    </p>

    @if ($employee->status === 'dismissed' && $employee->firing_date)
        <p class="text-sm text-gray-700 mb-4">
            <span class="font-medium">Дата увольнения:</span>
            {{ $employee->firing_date ? \Carbon\Carbon::parse($employee->firing_date)->format('d.m.Y') : '-'}}
        </p>
    @endif
    <x-edit-employee-button :employee="$employee"/>

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
        <label for="event_date" class="block text-sm font-medium mt-2">Дата события:</label>
        <input type="date" name="event_date" id="event_date" class="w-full p-2 border rounded text-sm"
            value="{{ now()->format('Y-m-d') }}">

        <div class="flex justify-end mt-3">
            <button type="button" onclick="toggleEditForm()" class="px-4 py-2 text-sm text-gray-600 border rounded hover:bg-gray-100 mr-2">Отмена</button>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                Обновить
            </button>
        </div>
    </form>

    <div class="bg-white p-4 rounded-lg shadow-sm mt-4">
        <h3 class="text-lg font-semibold mb-2">Учётные данные</h3>
        @foreach ($employee->credentials as $credential)
            <div class="mb-2">
                <p class="text-sm"><span class="font-medium">{{ strtoupper($credential->system) }}:</span></p>
                <p class="text-sm">Логин: <span class="font-mono">{{ $credential->login }}</span></p>
                <p class="text-sm">Пароль: <span class="font-mono text-red-600">{{ $credential->password }}</span></p>
            </div>
        @endforeach
    </div>







    <div class="mt-6 bg-white p-4 rounded-lg shadow-md">
        <!-- Кнопка добавить логин -->
        <button onclick="toggleCredentialForm()" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Добавить или изменить логин
        </button>

        <!-- Форма добавления/обновления логина (скрыта по умолчанию) -->
        <form action="{{ route('employees.updateCredentials', $employee->id) }}" method="POST" id="credentialForm" class="mt-4 bg-gray-50 p-4 rounded-lg shadow hidden">
            @csrf
            @method('PUT')

            <label for="system" class="block text-sm font-medium mb-1">Выберите систему:</label>
            <select name="system" id="system" class="w-full p-2 border rounded">
                <option value="crm">CRM</option>
                <option value="tablet">Планшет</option>
                <option value="kmp">КМП</option>
            </select>

            <label for="user_name" class="block text-sm font-medium mt-2 mb-1">Имя пользователя:</label>
            <input type="text" name="user_name" id="login" class="w-full p-2 border rounded">

            <label for="login" class="block text-sm font-medium mt-2 mb-1">Логин:</label>
            <input type="text" name="login" id="login" class="w-full p-2 border rounded">

            <label for="password" class="block text-sm font-medium mt-2 mb-1">Пароль:</label>
            <input type="text" name="password" id="password" class="w-full p-2 border rounded">

            <label for="add_password" class="block text-sm font-medium mt-2 mb-1">Доп пароль:</label>
            <input type="text" name="add_password" id="add_password" class="w-full p-2 border rounded">

            <div class="flex justify-end mt-4">
                <button type="button" onclick="toggleCredentialForm()" class="px-4 py-2 text-sm text-gray-600 border rounded hover:bg-gray-100 mr-2">
                    Отмена
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                    Сохранить
                </button>
            </div>
        </form>
    </div>
    <br>
    <script>
        function toggleCredentialForm() {
            document.getElementById('credentialForm').classList.toggle('hidden');
        }

        function deleteCredential(id) {
            if (confirm("Удалить логин?")) {
                fetch(`/employees/credentials/${id}`, {
                    method: "DELETE",
                    headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" }
                }).then(() => location.reload());
            }
        }
    </script>

    <!-- Кнопка для показа таблицы -->
    <button id="showTableButton" class="mt-2 bg-blue-500 text-white px-3 py-1 text-sm rounded-md">КМП запрос</button>

    <!-- Модальное окно -->
    <div id="tableModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
        <div class="bg-white p-5 rounded-lg shadow-lg w-3/4 relative">
            <button id="copyTableBtn" class="absolute top-2 right-2 bg-gray-200 px-3 py-1 rounded">📋 Копировать</button>
            <button id="closeTableBtn" class="absolute top-2 left-2 bg-red-500 text-white px-3 py-1 rounded">✖ Закрыть</button>
            <br>
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
                            @php
                                $parts = explode(' ', $employee->full_name);
                                $KMPName = count($parts) > 2 ? implode(' ', array_slice($parts, 0, 2)) : $employee->full_name
                            @endphp
                            <td style="border: 1px solid black; padding: 8px;">{{ $KMPName }}</td>
                            <td style="border: 1px solid black; padding: 8px;">{{ $employee->position }}</td>
                            <td style="border: 1px solid black; padding: 8px;">{{ $employee->territories->first()->team ?? ''}}</td>
                            <td style="border: 1px solid black; padding: 8px;">{{ $employee->territories->first()->city ?? '' }}</td>
                            <td style="border: 1px solid black; padding: 8px;">{{ $employee->territories->first()->parent->employee->full_name ?? '' }}</td>
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
        let table = document.querySelector("#tableContainer table").outerHTML;
        let html = `
            <html>
            <head>
                <meta charset="UTF-8">
                <style>
                    table { border-collapse: collapse; width: 100%; }
                    th, td { border: 1px solid black; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                </style>
            </head>
            <body>${table}</body>
            </html>
        `;

        try {
            await navigator.clipboard.write([
                new ClipboardItem({ "text/html": new Blob([html], { type: "text/html" }) })
            ]);
            alert("Таблица скопирована в HTML-формате!");
        } catch (err) {
            console.error("Ошибка копирования: ", err);
            alert("Не удалось скопировать таблицу.");
        }
        document.getElementById("tableModal").classList.add("hidden");
    });
</script>

