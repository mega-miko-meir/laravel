@props(['action'])

<div {{ $attributes->merge(['class' => 'p-4']) }}>
    <form action="{{$action}}" method="GET" class="max-w-lg mx-auto">
    {{-- <form action="{{$action ?? '/'}}" method="GET" class="max-w-lg mx-auto"> --}}
        <div class="relative border-2 border-gray-300 rounded-lg overflow-hidden">
            <div class="absolute top-1/2 left-3 transform -translate-y-1/2">
                <i class="fa fa-search text-gray-400 hover:text-gray-500"></i>
            </div>
            {{-- сохраняем active_only --}}
            <input type="hidden" name="active_only" value="{{ request('active_only', 1) }}">

            {{-- если нужно — и сортировку --}}
            <input type="hidden" name="sort" value="{{ request('sort', 'latest_event_date') }}">
            <input type="hidden" name="order" value="{{ request('order', 'desc') }}">

            <input
                type="text"
                name="search"
                class="h-14 w-full pl-12 pr-32 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700"
                placeholder="Поиск..."
            />
            <button
                type="submit"
                class="absolute top-0 right-0 h-full px-6 bg-blue-600 text-white font-semibold rounded-r-lg hover:bg-blue-700 transition-all"
            >
                Поиск
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        let searchInput = document.getElementById("search-input");

        if (!searchInput) {
            console.error("Поле поиска не найдено!");
            return;
        }

        searchInput.addEventListener("input", function () {
            let query = searchInput.value.trim();
            let sort = new URLSearchParams(window.location.search).get("sort") || "hiring_date";
            let order = new URLSearchParams(window.location.search).get("order") || "desc";
            let activeOnly = document.getElementById("ticker")?.checked ? 1 : 0;
            let employeesContainer = document.getElementById("employees-container");

            // Добавляем эффект загрузки
            employeesContainer.style.opacity = "0.5";

            fetch(`/employees/search?search=${query}&sort=${sort}&order=${order}&active_only=${activeOnly}`)
                .then(response => response.text())
                .then(html => {
                    employeesContainer.innerHTML = html;
                    employeesContainer.style.opacity = "1"; // Убираем эффект загрузки
                })
                .catch(error => {
                    console.error("Ошибка при поиске сотрудников:", error);
                    employeesContainer.style.opacity = "1";
                });
        });
    });
</script>
