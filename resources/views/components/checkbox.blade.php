@props(['employee', 'bricks', 'selectedBricks'])

<div class="relative inline-block text-left">
    <!-- Кнопка для отображения выпадающего списка -->
    <button id="toggle-dropdown" type="button" class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none">
        Добавить брики
    </button>
    <br>

    <!-- Выпадающий список -->
    <div id="dropdown-menu" class="hidden whitespace-nowrap absolute right-100 mt-2 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-10">
        <!-- Поисковая строка -->
        <div class="p-2">
            <input
                id="search-bricks"
                type="text"
                placeholder="Поиск..."
                class="w-full px-4 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"
            />
        </div>

        <!-- Список с чекбоксами -->
        {{-- @if($employee->territories->isNotEmpty()) --}}
        {{-- @if(optional($employee)->territories->isNotEmpty()) --}}
        @if(optional($employee)->territories && $employee->territories->isNotEmpty())

        <form action="{{ route('assign.bricks', [$employee->territories->first()->id]) }}" method="POST">
            @csrf
            <div id="brick-list" class="max-h-48 overflow-y-auto">
                @foreach($bricks as $brick)
                    <label class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer">
                        <input type="checkbox" name="bricks[]" value="{{ $brick->code }}" class="form-checkbox h-4 w-4 text-blue-600 rounded focus:ring-blue-500" />
                        <span class="ml-2">{{ $brick->description }}</span>
                    </label>
                @endforeach
            </div>
            <button type="submit" id="submit-bricks" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 focus:outline-none">
                Добавить выбранные
            </button>
        </form>
        @else
        <p>No bricks assigned</p>
        @endif

    </div>
</div>

<div class="territory-info">

    <!-- Кнопка для раскрытия/свертывания информации о бриках -->
    {{-- <button type="button" class="bg-blue-500 text-white p-2 rounded" id="toggle-bricks-btn">
        Показать брики
    </button> --}}
    <!-- Скрытый список бриков -->

    <table id="bricks-list" class="w-full max-w-md border rounded-lg shadow-sm divide-y divide-gray-200">
        <thead id="table-head" class="bg-gray-100 text-gray-700 text-sm uppercase font-semibold">
            <tr>
                <th class="px-4 py-3 text-left tracking-wider">
                    Брики
                </th>
                <th id="action-column" class="px-4 py-3 text-left tracking-wider">
                    {{-- Действия --}}
                </th>
            </tr>
        </thead>
        <tbody id="table-body" class="hidden bg-white divide-y divide-gray-200">
            @if($selectedBricks->isNotEmpty())
                @foreach($selectedBricks as $brick)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            {{ $brick->description }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            <form action="{{ route('assign.bricks', [$employee->territories->first()->id, $brick->id]) }}" method="POST" onsubmit="return confirm('Вы уверены, что хотите удалить этот brick?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 font-medium">Удалить</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="2" class="px-4 py-3 text-sm text-gray-500 text-center">
                        Нет привязанных бриков.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
    const toggleDropdown = document.getElementById('toggle-dropdown');
    const dropdownMenu = document.getElementById('dropdown-menu');
    const submitBricks = document.getElementById('submit-bricks');
    const searchBricks = document.getElementById('search-bricks');
    const brickList = document.getElementById('brick-list');

    // Открыть/закрыть выпадающий список
    toggleDropdown.addEventListener('click', () => {
        dropdownMenu.classList.toggle('hidden');
    });

    // Фильтрация списка
    searchBricks.addEventListener('input', () => {
        const searchTerm = searchBricks.value.toLowerCase();
        const labels = brickList.querySelectorAll('label');

        labels.forEach((label) => {
            const text = label.textContent.toLowerCase();
            label.style.display = text.includes(searchTerm) ? 'flex' : 'none';
            });
        });
    });

    document.addEventListener("DOMContentLoaded", function() {
        const tbody = document.getElementById('table-body');
        const thead = document.getElementById('table-head');

        // Устанавливаем начальное состояние из localStorage
        tbody.classList.toggle('hidden', localStorage.getItem('tableExpanded') !== 'true');

        // Переключение видимости при клике
        thead.addEventListener('click', function() {
            const isHidden = tbody.classList.toggle('hidden');
            localStorage.setItem('tableExpanded', !isHidden);
        });
    });

</script>
