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
                        + Create
                    </a>

                    <a href="/export-excel"
                       class="bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-1.5 px-4 rounded-md shadow-sm transition duration-200 flex items-center text-sm">
                        Export
                    </a>
                </div>
                <ul>
                    @foreach(App\Models\Role::all() as $role)
                        <li>ID: {{ $role->id }} — Name: {{ $role->name }}</li>
                    @endforeach
                </ul>

                <!-- Фильтры -->
                <x-active-employee-checkbox />
                <x-search class="mb-6" />

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
