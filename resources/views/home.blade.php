@extends('layout')

@section('content')
    @auth
        <x-container class="container mx-auto py-6">
            <!-- Боковое меню -->
            {{-- <x-side-menu class="col-span-2" /> --}}

            <!-- Основной контент -->
            <div class="col-span-10 p-8 bg-white relative">
                <!-- Включение шапки -->
                <x-header class="mb-6" />

                <!-- Сообщение об успехе -->
                <x-flash-message />

                <div class="absolute top-4 right-4 flex gap-2">
                    <!-- Кнопка для создания сотрудника -->
                    <a href="/create-employee" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-1.5 px-4 rounded-md shadow-sm transition duration-200 flex items-center text-sm">
                        + Create
                    </a>

                    <!-- Кнопка для экспорта в Excel -->
                    <a href="/export-excel" class="bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-1.5 px-4 rounded-md shadow-sm transition duration-200 flex items-center text-sm">
                        Export
                    </a>
                </div>

                <x-active-employee-checkbox />

                <x-search class="mb-6" />

                <h2 class="text-2xl font-bold mb-4 mt-6">
                    Список всех сотрудников (<span id="employee-count">{{ $employees->count() }}</span>)
                </h2>

                <div id="employees-container">
                    <x-employee-card :employees="$employees" :sort="$sort" :order="$order"/>
                </div>

                <!-- Подключаем внешний JS -->
                <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        let checkbox = document.getElementById('ticker'); // ID чекбокса из x-active-employee-checkbox

                        async function loadEmployees(activeOnly) {
                            try {
                                const response = await fetch(`/employees?active_only=${activeOnly}`, {
                                    headers: { "X-Requested-With": "XMLHttpRequest" } // Указываем, что это AJAX-запрос
                                });

                                const html = await response.text(); // Получаем HTML-код
                                document.getElementById("employees-container").innerHTML = html;

                                // Обновляем счетчик сотрудников
                                let parser = new DOMParser();
                                let doc = parser.parseFromString(html, 'text/html');
                                let count = doc.querySelectorAll('.employee-card').length;
                                document.getElementById("employee-count").textContent = count;

                            } catch (error) {
                                console.error("Ошибка загрузки сотрудников:", error);
                            }
                        }

                        checkbox.addEventListener('change', function () {
                            let activeOnly = checkbox.checked ? 1 : 0;
                            loadEmployees(activeOnly);
                        });
                    });
                </script>


                <script src="{{ asset('js/employees.js') }}" defer></script>
            </div>
        </x-container>
    @else
        <x-auth-container />
    @endauth

    <script src="{{ asset('js/search.js') }}"></script>
@endsection
