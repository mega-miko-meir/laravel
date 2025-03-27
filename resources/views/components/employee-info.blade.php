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
        <p><span class="font-medium">Имя:</span> {{ $employee->full_name}}</p>
        <p><span class="font-medium">Email:</span> {{ $employee->email }}</p>
        <p><span class="font-medium">Должность:</span> {{ $employee->position }}</p>
        @if($employee->territories->isNotEmpty())
            <p><span class="font-medium">Команда:</span> {{ $employee->territories->first()->team }}</p>
            <p><span class="font-medium">Город:</span> {{ $employee->territories->first()->city }}</p>
            <p><span class="font-medium">Роль:</span> {{ $employee->territories->first()->role }}</p>
            <p><span class="font-medium">Менеджер:</span> {{ $employee->territories->first()->parent->employee->full_name ?? '' }}</p>
        @endif
        <p>
            <span class="font-medium">Дата найма/увольнения:</span>
            {{ $employee->hiring_date && optional($employee->events()->latest()->first())->event_date
                ? \Carbon\Carbon::parse($employee->events()->latest()->first()->event_date)->format('d.m.Y')
                : '-'
            }} -
            {{ $employee->firing_date ? \Carbon\Carbon::parse($employee->firing_date)->format('d.m.Y') : '-' }}
        </p>
    </div>


    {{-- @if ($employee->status === 'dismissed' && $employee->firing_date)
        <p class="text-sm text-gray-700 mb-4">
            <span class="font-medium">Дата увольнения:</span>
            {{ $employee->firing_date ? \Carbon\Carbon::parse($employee->firing_date)->format('d.m.Y') : '-'}}
        </p>
    @endif --}}
    <x-edit-employee-button :employee="$employee"/>

    <!-- Форма обновления статуса (изначально скрыта) -->
    <form action="{{ route('employees.updateStatusAndEvent', $employee->id) }}" method="POST" id="editForm" class="bg-gray-50 p-4 rounded-lg shadow-sm hidden"
        onsubmit="return confirm('Are you sure you want to add an event and change the status?');">
        @csrf
        @method('PUT')
        <label for="status" class="block text-sm font-medium mb-1">Выберите статус:</label>
        <select name="status" id="status" class="w-full p-2 border rounded text-sm">
            <option value="active" {{ $employee->status === 'active' ? 'selected' : '' }}>Hired</option>
            <option value="dismissed" {{ $employee->status === 'dismissed' ? 'selected' : '' }}>Dismissed</option>
            <option value="maternity_leave" {{ $employee->status === 'maternity_leave' ? 'selected' : '' }}>Maternity leave</option>
            <option value="change_position" {{ $employee->status === 'changed_position' ? 'selected' : '' }}>Changed position</option>
            <option value="long_vacation" {{ $employee->status === 'long_vacation' ? 'selected' : '' }}>Long vacation</option>
        </select>
        <label for="event_date" class="block text-sm font-medium mt-2">Event date:</label>
        <input type="date" name="event_date" id="event_date" class="w-full p-2 border rounded text-sm"
            value="{{ now()->format('Y-m-d') }}">

        <div class="flex justify-end mt-3">
            <button type="button" onclick="toggleEditForm()" class="px-4 py-2 text-sm text-gray-600 border rounded hover:bg-gray-100 mr-2">Отмена</button>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                Обновить
            </button>
        </div>
    </form>

    <x-kmp-request :employee="$employee" />

    <!-- Заполнение паролей и отображение паролей -->
    <x-credentials :employee="$employee" />

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

