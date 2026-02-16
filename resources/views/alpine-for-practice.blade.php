@extends('layout')

@section('content')

{{-- <body>
    <div x-data="{
    search: '',
    employees: @js($employees),

    employees: {{ $employees->map(fn($e) => ['id' => $e->id, 'full_name' => $e->full_name])->toJson() }},

    get filteredEmployees() {
        if (!this.search) return this.employees;
        return this.employees.filter(e =>
            e.full_name.toLowerCase().includes(this.search.toLowerCase())
        );
    }
}">

    <input type="text" x-model="search" placeholder="Поиск по сотрудникам..." class="border p-2 w-full mt-8 mb-4">

    <div class="border rounded divide-y">
        <template x-for="employee in filteredEmployees" :key="employee.id">
            <div class="p-3 hover:bg-gray-50 flex justify-between items-center">
                <span x-text="employee.full_name"></span>
                <button @click="console.log('Выбран ID:', employee.id)" class="text-blue-500 text-sm">Выбрать</button>
            </div>
        </template>

        <div x-show="filteredEmployees.length === 0" class="p-3 text-gray-400 text-center">
            Сотрудник не найден
        </div>
    </div>
</div>

</body> --}}


<div x-data="{
    search: '',
    showOnlyActive: false, {{-- Состояние чекбокса --}}
    employees: @js($employees), {{-- Предположим, у каждого есть поле status --}}

    get filteredEmployees() {
        return this.employees.filter(e => {
            {{-- Условие 1: Поиск по имени --}}
            const matchName = e.full_name.toLowerCase().includes(this.search.toLowerCase());

            {{-- Условие 2: Если чекбокс включен, проверяем активность. Если выключен — пропускаем всех --}}
            const matchActive = this.showOnlyActive ? (e.status === 'active') : true;

            return matchName && matchActive;
        });
    }
}">
    <div class="flex flex-col gap-4 mb-4">
        <!-- Поиск -->
        <input type="text" x-model="search" placeholder="Поиск..." class="border p-2 w-full mt-8">

        <!-- Чекбокс -->
        <label class="flex items-center gap-2 cursor-pointer text-sm">
            <input type="checkbox" x-model="showOnlyActive" class="rounded border-gray-300">
            <span>Только активные сотрудники</span>
        </label>

        <div class="text-xs text-gray-500 mt-1">
        Найдено: <span class="font-bold text-blue-600" x-text="filteredEmployees.length"></span>
        из <span x-text="employees.length"></span>
    </div>
    </div>



    <!-- Список -->
    <ul class="border rounded divide-y">
        <template x-for="employee in filteredEmployees" :key="employee.id">
            <li class="p-2 flex justify-between">
                <span x-text="employee.full_name"></span>
                <span x-show="employee.status" class="text-xs text-green-600 bg-green-50 px-2 rounded-full">Active</span>
            </li>
        </template>
    </ul>
</div>




<script src="{{ asset('js/search.js') }}"></script>
@endsection
