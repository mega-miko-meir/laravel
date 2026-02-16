@extends('layout')

@section('content')
    @auth
        <x-container class="container mx-auto py-6">

            <div class="col-span-10 relative">

                <x-header class="mb-6" />

                <x-flash-message />

                <!-- Кнопки справа -->
                <div class="absolute top-4 right-4 flex gap-2">
                    <a
                        @can('admin')
                            href="/create-employee"
                        @endcan
                        class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-1.5 px-4 rounded-md shadow-sm transition duration-200 flex items-center text-sm">
                        + Добавить
                    </a>

                    <div class="relative inline-block">
                        <!-- КНОПКА ОТКРЫТИЯ -->
                        <button
                            type="button"
                            id="exportBtn"
                            class="bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-1.5 px-4 rounded-md shadow-sm transition duration-200 flex items-center text-sm">
                            Выгрузить
                        </button>


                        <!-- DROPDOWN МЕНЮ -->
                        <div id="exportDropdown"
                            class="hidden absolute right-0 mt-1 bg-white border rounded-lg shadow-lg p-4 w-72 z-[999]">

                            <form action="{{ route('export.excel') }}" method="POST">
                                @csrf

                                <p class="font-semibold mb-2">Выберите колонки:</p>

                                <div class="space-y-1 text-sm">

                                    <label class="flex items-center">
                                        <input type="checkbox" name="columns[]" value="full_name" checked class="mr-2">
                                        ФИО
                                    </label>

                                    <label class="flex items-center">
                                        <input type="checkbox" name="columns[]" value="first_name_eng" class="mr-2">
                                        ФИО англ
                                    </label>

                                    <label class="flex items-center">
                                        <input type="checkbox" name="columns[]" value="role" checked class="mr-2">
                                        Позиция
                                    </label>

                                    <label class="flex items-center">
                                        <input type="checkbox" name="columns[]" value="city" class="mr-2">
                                        Город
                                    </label>

                                    <label class="flex items-center">
                                        <input type="checkbox" name="columns[]" value="email" class="mr-2">
                                        Почта
                                    </label>

                                    <label class="flex items-center">
                                        <input type="checkbox" name="columns[]" value="team" checked class="mr-2">
                                        Группа
                                    </label>

                                    <label class="flex items-center">
                                        <input type="checkbox" name="columns[]" value="department" checked class="mr-2">
                                        Департамент
                                    </label>

                                    <label class="flex items-center">
                                        <input type="checkbox" name="columns[]" value="manager" class="mr-2">
                                        Менеджер
                                    </label>

                                    <label class="flex items-center">
                                        <input type="checkbox" name="columns[]" value="hiring_date" class="mr-2">
                                        Дата приема
                                    </label>
                                    <hr class="my-3">
                                    <p class="font-semibold mb-2 text-sm">Стаж работы:</p>

                                    <label class="flex items-center text-sm mb-2">
                                        <input type="checkbox"
                                            name="with_experience"
                                            value="1"
                                            class="mr-2">
                                        Выгружать стаж
                                    </label>

                                    <div class="text-sm">
                                        <label class="block text-gray-600 mb-1">
                                            На дату:
                                        </label>
                                        <input type="date"
                                            name="experience_date"
                                            value="{{ now()->toDateString() }}"
                                            class="w-full border rounded px-2 py-1 text-sm">
                                    </div>


                                </div>

                                <div class="mt-3 flex justify-end">
                                    <button type="submit"
                                            class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-3 py-1 rounded">
                                        Скачать
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>



                    <script>
                    const btn = document.getElementById('exportBtn');
                    const menu = document.getElementById('exportDropdown');

                    btn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        menu.classList.toggle('hidden');
                    });

                    // закрытие при клике вне
                    document.addEventListener('click', (e) => {
                        if (!menu.contains(e.target) && e.target !== btn) {
                            menu.classList.add('hidden');
                        }
                    });
                    </script>




                </div>
                {{-- <ul>
                    @foreach(App\Models\Role::all() as $role)
                        <li>ID: {{ $role->id }} — Name: {{ $role->name }}</li>
                    @endforeach
                </ul> --}}

                <!-- Фильтры -->




                <x-active-checkbox />
                <x-search class="mb-6" :action="route('employees.search')" />

                <!-- Заголовок -->
                <h2 class="text-2xl font-bold mb-4 mt-6">
                    Список сотрудников (<span id="employee-count">{{ $employees->count() }}</span>)
                </h2>

                <!-- Контейнер для ajax -->
                <div id="employees-container">
                    <x-employee-card :employees="$employees" :sort="$sort" :order="$order"/>
                </div>

            </div>
        </x-container>

        <!-- JS -->
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                const checkbox = document.getElementById("ticker");
                const container = document.getElementById("employees-container");
                const counter = document.getElementById("employee-count");

                if (!checkbox) return;

                async function loadEmployees(activeOnly) {
                    try {
                        const response = await fetch(`/employees?active_only=${activeOnly}`, {
                            headers: { "X-Requested-With": "XMLHttpRequest" }
                        });

                        const html = await response.text();
                        container.innerHTML = html;

                        // обновляем количество
                        counter.textContent = container.querySelectorAll(".employee-card").length;

                    } catch (e) {
                        console.error("Ошибка загрузки сотрудников:", e);
                    }
                }

                checkbox.addEventListener("change", () => {
                    loadEmployees(checkbox.checked ? 1 : 0);
                });
            });
        </script>

        <script src="{{ asset('js/search.js') }}" defer></script>
        <script src="{{ asset('js/employees.js') }}" defer></script>

    @else
        <x-auth-container />
    @endauth
@endsection
