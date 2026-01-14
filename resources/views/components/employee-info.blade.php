@props(['employee', 'currentStatus'])

<div class="mt-6 bg-white p-4 rounded-lg shadow-md relative">
    <!-- Заголовок и статус -->
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-semibold text-gray-800">Информация о сотруднике</h1>
        <div class="flex items-center gap-2">
            {{ \Carbon\Carbon::parse($employee->events()->latest('event_date')->first()?->event_date)->format('d.m.Y') }} -
            <x-status-badge :status="$employee->events()->latest('event_date')->first()?->event_type" />
            <button onclick="toggleEditForm()" class="text-blue-600 text-sm hover:underline">Edit</button>
        </div>
    </div>

    <!-- Данные о сотруднике -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4 text-m text-gray-700">
        <p><span class="font-medium">Имя:</span> {{ $employee->full_name}}</p>
        <p><span class="font-medium">Email:</span> {{ $employee->email }}</p>
        <p><span class="font-medium">Должность:</span> {{ $employee->position }}</p>
        {{-- <p><span class="font-medium">Команда:</span> {{ $employee->employee_territory()->latest('assigned_at')->first()->team ?? '-' }}</p> --}}
        <p><span class="font-medium">Команда2:</span> {{ $employee->current_team ?? '-' }}</p>
        {{-- <p><span class="font-medium">Город:</span> {{ $employee->employee_territory()->latest('assigned_at')->first()->city ?? '-' }}</p> --}}
        <p><span class="font-medium">Город:</span> {{ $employee->current_city ?? '-' }}</p>
        {{-- <p><span class="font-medium">Менеджер:</span> {{ $employee->employee_territory()->latest('assigned_at')->first()->parent->employee->full_name ?? '-' }}</p> --}}
        <p><span class="font-medium">Менеджер:</span> {{ $employee->current_manager ?? '-' }}</p>
        {{-- @if($employee->territories->isNotEmpty())
            <p><span class="font-medium">Команда:</span> {{ $employee->territories->first()->team }}</p>

            <p><span class="font-medium">Город:</span> {{ $employee->territories->first()->city }}</p>
            <p><span class="font-medium">Роль:</span> {{ $employee->territories->first()->role }}</p>
            <p><span class="font-medium">Менеджер:</span> {{ $employee->territories->first()->parent->employee->full_name ?? '' }}</p>
        @endif --}}
    </div>

    <x-edit-employee-button :employee="$employee"/>

    <x-event-adding-form :employee="$employee" />

    <br>
    <x-kmp-request :employee="$employee" />

    <!-- Заполнение паролей и отображение паролей -->
    <x-credentials :employee="$employee" />

</div>

<div x-data="{open:false}" class="bg-white shadow-md rounded-lg p-4 mt-6">
    <button
    {{-- onclick="toggleTerritoryHistory()" --}}
    x-on:click="open = !open"
    class="w-full text-left font-semibold text-lg text-gray-700 border-b pb-2 mb-3 flex justify-between items-center">
        История событий
        <svg :class="{'rotate-180': open}" id="territoryArrowIcon" class="w-5 h-5 transition-transform transform rotate-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <ul x-show="open" id="eventHistoryList" class="text-sm text-gray-600 space-y-2" style="display:none">
        @foreach($employee->events()->orderBy('event_date', 'desc')->get() as $event)
            <li class="flex justify-between items-center border-b py-2">
                <div>
                    <span class="text-sm text-gray-500 ml-2">
                        {{ \Carbon\Carbon::parse($event->event_date)->format('d.m.Y') }} -
                        <x-status-badge :status="$event->event_type" />
                    </span>
                </div>

                <!-- Кнопка удаления -->
                <form action="{{ route('events.destroy', $event->id) }}" method="POST" onsubmit="return confirm('Удалить событие?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-500 hover:text-red-700 text-lg font-bold">
                        ×
                    </button>
                </form>
            </li>
        @endforeach
    </ul>

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

