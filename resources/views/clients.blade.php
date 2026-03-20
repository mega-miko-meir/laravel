@extends('layout')
@section('content')


<form method="GET" class="flex flex-col gap-4 mt-10 w-full">

    <div class="flex flex-wrap gap-4">

        <div x-data="{ selected: '{{ request('organization_type') }}' }" class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Тип клиента</label>

            @php
                $types = ['Специалист', 'Аптека'];
            @endphp

            <div class="inline-flex rounded-lg bg-gray-100 p-1">

                @foreach($types as $type)
                    <button type="button"
                        @click="selected = '{{ $type }}'"
                        :class="selected === '{{ $type }}'
                            ? 'bg-white shadow text-blue-600'
                            : 'text-gray-500 hover:text-gray-700'"
                        class="px-3 py-1.5 text-sm font-medium rounded-md transition">

                        {{ $type }}
                    </button>
                @endforeach

            </div>

            <!-- скрытое поле -->
            <input type="hidden" name="organization_type" :value="selected">
        </div>

        <!-- Регион -->
        <div x-data="filterComponent('brick_label', {{ json_encode($regions) }}, {{ json_encode(request('brick_label', [])) }})"
            @click.outside="open = false"
            class="w-64 relative font-sans">

            <label class="text-sm font-medium text-gray-700 block">Регион</label>

            <!-- Контейнер -->
            <div class="rounded-lg min-h-[32px] flex flex-col gap-1 cursor-text focus-within:ring-2 focus-within:ring-blue-400"
                @click="open = true">

                <!-- Теги -->
                <template x-for="item in selected" :key="item">
                    <div class="flex items-center justify-between border border-purple-300 px-2 py-1 rounded text-sm w-full" >
                        <span class="truncate" x-text="item"></span>
                        <button type="button" @click.stop="remove(item)"
                                class="text-purple-600 hover:text-white rounded-full w-4 h-4 flex items-center justify-center text-xs">
                            ✕
                        </button>
                    </div>
                </template>

                <!-- Поиск -->
                <input type="text"
                    x-model="search"
                    @input="filter()"
                    placeholder="Поиск..."
                    class="outline-none text-sm px-2 py-2 mt-1 border border-gray-200 rounded w-full">
            </div>

            <!-- Dropdown -->
            <div x-show="open"
                class="absolute bg-white border border-gray-300 mt-1 w-full max-h-60 overflow-y-auto z-10 rounded shadow-lg">
                <template x-for="item in filtered" :key="item">
                    <label class="flex items-center gap-2 p-2 hover:bg-gray-100 cursor-pointer text-xs">
                        <input type="checkbox"
                            :value="item"
                            @change="toggle(item)"
                            :checked="selected.includes(item)"
                            class="rounded border-gray-300">
                        <span x-text="item" class="truncate text-xs"></span>
                    </label>
                </template>
            </div>

            <!-- hidden -->
            <template x-for="item in selected">
                <input type="hidden" name="brick_label[]" :value="item">
            </template>

        </div>

        <!-- Город -->
        <div x-data="filterComponent('city', {{ json_encode($cities) }}, {{ json_encode(request('city', [])) }})"
            @click.outside="open = false"
            class="w-64 relative font-sans">

            <label class="text-sm font-medium text-gray-700 block">Город</label>

            <!-- Контейнер тегов + поиск -->
            <div class="rounded-lg min-h-[32px] flex flex-col gap-1 cursor-text focus-within:ring-2 focus-within:ring-blue-400"
                @click="open = true">

                <!-- Теги в столбик -->
                <template x-for="item in selected" :key="item">
                    <div class="flex items-center justify-between bg-green-100 text-green-800 border border-green-300 px-2 py-1 rounded text-sm w-full">
                        <span class="truncate" x-text="item"></span>
                        <button type="button" @click.stop="remove(item)"
                                class="text-green-600 hover:text-white rounded-full w-4 h-4 flex items-center justify-center text-xs">
                            ✕
                        </button>
                    </div>
                </template>

                <!-- Input поиска под тегами -->
                <input type="text"
                    x-model="search"
                    @input="filter()"
                    placeholder="Поиск..."
                    class="outline-none text-sm px-2 py-2 mt-1 border border-gray-200 rounded w-full">
            </div>

            <!-- Dropdown -->
            <div x-show="open"
                class="absolute bg-white border border-gray-300 mt-1 w-full max-h-60 overflow-y-auto z-10 rounded shadow-lg">
                <template x-for="item in filtered" :key="item">
                    <label class="flex items-center gap-2 p-2 hover:bg-gray-100 cursor-pointer text-xs">
                        <input type="checkbox"
                            :value="item"
                            @change="toggle(item)"
                            :checked="selected.includes(item)"
                            class="rounded border-gray-300 text-sm">
                        <span x-text="item" class="truncate text-xs"></span>
                    </label>
                </template>
            </div>

            <!-- скрытые поля для формы -->
            <template x-for="item in selected">
                <input type="hidden" name="city[]" :value="item">
            </template>

        </div>

        <!-- Специальность -->
        <div x-data="filterComponent('specialty', {{ json_encode($specialties) }}, {{ json_encode(request('specialty', [])) }})"
             @click.outside="open = false"
             class="w-64 relative font-sans">

            <label class="text-sm font-medium text-gray-700 block">Специальность</label>

            <div class="rounded-lg min-h-[32px] flex flex-col gap-1 cursor-text focus-within:ring-2 focus-within:ring-blue-400"
                 @click="open = true">

                <!-- Теги в столбик -->
                <template x-for="item in selected" :key="item">
                    <div class="flex items-center justify-between bg-blue-100 text-blue-800 border border-blue-300 px-2 py-1 rounded text-sm w-full">
                        <span class="truncate" x-text="item"></span>
                        <button type="button" @click.stop="remove(item)"
                                class="text-blue-600 hover:text-white rounded-full w-4 h-4 flex items-center justify-center text-xs">
                            ✕
                        </button>
                    </div>
                </template>

                <!-- Input поиска под тегами -->
                <input type="text"
                       x-model="search"
                       @input="filter()"
                       placeholder="Поиск..."
                       class="outline-none text-sm px-2 py-2 mt-1 border border-gray-200 rounded">
            </div>

            <!-- Dropdown -->
            <div x-show="open"
                 class="absolute bg-white border border-gray-300 mt-1 w-full max-h-60 overflow-y-auto z-10 rounded shadow-lg">
                <template x-for="item in filtered" :key="item">
                    <label class="flex items-center gap-2 p-2 hover:bg-gray-100 cursor-pointer">
                        <input type="checkbox"
                               :value="item"
                               @change="toggle(item)"
                               :checked="selected.includes(item)"
                               class="rounded border-gray-300 text-sm">
                        <span x-text="item" class="text-sm"></span>
                    </label>
                </template>
            </div>

            <!-- скрытые поля -->
            <template x-for="item in selected">
                <input type="hidden" name="specialty[]" :value="item">
            </template>

        </div>

        <!-- ФИО -->
        <div x-data="{ search: '{{ request('full_name') }}' }"
             class="flex flex-col gap-1 w-64">

            <label class="text-sm font-medium text-gray-700">ФИО/Название</label>

            <input type="text"
                   name="full_name"
                   x-model="search"
                   placeholder="Поиск..."
                   class="outline-none text-sm px-2 py-2 mt-1 border border-gray-200 rounded w-full">
        </div>

    </div>

    <!-- Кнопка Фильтр -->
    <div class="flex justify-start mt-2">
        <button type="submit"
                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-1.5 text-sm font-medium rounded-md shadow-sm transition-all duration-200">
            Фильтр
        </button>
    </div>




</form>


<!-- БЛОК ВЫГРУЗКИ -->
<div x-data="{ open: false }" class="absolute right-5 inline-block">

    <!-- КНОПКА -->
    <button type="button"
            @click="open = !open"
            class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 text-sm font-medium rounded-md shadow-sm transition">
        Выгрузить
    </button>

    <!-- DROPDOWN -->
    <div x-show="open"
            @click.outside="open = false"
            class="absolute top-0 right-0 mt-1 bg-white border rounded-lg shadow-lg p-4 w-72 z-50">

        <form action="{{ route('export.onekey') }}" method="POST">
            @csrf

            <p class="font-semibold mb-2 text-sm">Выберите колонки:</p>

            <div class="space-y-1 text-sm max-h-60 overflow-y-auto">
                <label class="flex items-center">
                    <input type="checkbox" name="columns[]" value="full_name" checked class="mr-2">
                    ФИО
                </label>

                <label class="flex items-center">
                    <input type="checkbox" name="columns[]" value="organization_type" checked class="mr-2">
                    Тип клиента
                </label>

                <label class="flex items-center">
                    <input type="checkbox" name="columns[]" value="specialty" checked class="mr-2">
                    Специальность
                </label>

                <label class="flex items-center">
                    <input type="checkbox" name="columns[]" value="specialty2" class="mr-2">
                    Спец. 2
                </label>

                <label class="flex items-center">
                    <input type="checkbox" name="columns[]" value="parent_organization" class="mr-2">
                    Род. организация
                </label>

                <label class="flex items-center">
                    <input type="checkbox" name="columns[]" value="workplace" checked class="mr-2">
                    Место работы
                </label>

                <label class="flex items-center">
                    <input type="checkbox" name="columns[]" value="primary_address" checked class="mr-2">
                    Адрес
                </label>

                <label class="flex items-center">
                    <input type="checkbox" name="columns[]" value="onekey_id" checked class="mr-2">
                    OneKey ID
                </label>

                <label class="flex items-center">
                    <input type="checkbox" name="columns[]" value="brick_label" checked class="mr-2">
                    Регион
                </label>

                <label class="flex items-center">
                    <input type="checkbox" name="columns[]" value="city" checked class="mr-2">
                    Город
                </label>

                <label class="flex items-center">
                    <input type="checkbox" name="columns[]" value="brick_name" class="mr-2">
                    Brick
                </label>

                <label class="flex items-center">
                    <input type="checkbox" name="columns[]" value="coordinates"class="mr-2">
                    Координаты
                </label>

                <input type="hidden" name="full_name" value="{{ request('full_name') }}">
                @foreach(request('specialty', []) as $item)
                    <input type="hidden" name="specialty[]" value="{{ $item }}">
                @endforeach

                @foreach(request('city', []) as $item)
                    <input type="hidden" name="city[]" value="{{ $item }}">
                @endforeach

                @foreach(request('brick_label', []) as $item)
                    <input type="hidden" name="brick_label[]" value="{{ $item }}">
                @endforeach

                <input type="hidden" name="organization_type" value="{{ request('organization_type') }}">
                <div class="mt-3 flex justify-end">
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-3 py-1 rounded">
                        Скачать
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function filterComponent(name, options, selectedInit) {
        return {
            open: false,
            search: '',
            options: options,
            filtered: options,
            selected: selectedInit,

            filter() {
                this.filtered = this.options.filter(i =>
                    i.toLowerCase().includes(this.search.toLowerCase())
                );
            },

            toggle(item) {
            if (this.selected.includes(item)) {
                this.selected = this.selected.filter(i => i !== item);
            } else {
                this.selected.push(item);
            }

            this.submitForm();
        },

        remove(item) {
            this.selected = this.selected.filter(i => i !== item);
            this.submitForm();
        },

        // submitForm() {
        //     this.$nextTick(() => {
        //         this.$root.closest('form').submit();
        //     });
        // }


            // toggle(item) {
            //     if (this.selected.includes(item)) {
            //         this.selected = this.selected.filter(i => i !== item);
            //     } else {
            //         this.selected.push(item);
            //     }
            // },

            // remove(item) {
            //     this.selected = this.selected.filter(i => i !== item);
            // }
        }
    }
</script>

<!-- Блок с количеством найденных клиентов -->
<div class="text-xs text-gray-500 mt-2 mb-2 flex items-center justify-between">
    <div>
        Найдено:
        <span class="font-bold text-blue-600">{{ $clients->total() }}</span>
    </div>
</div>

<table class="min-w-full text-sm mt-4 border">
    <thead class="bg-gray-100">
        <tr>
            <th class="p-2 text-left">ФИО</th>
            <th class="p-2 text-left">Специальность</th>
            <th class="p-2 text-left">ЛПУ</th>
            <th class="p-2 text-left">Регион</th>
            <th class="p-2 text-left">Город</th>
            <th class="p-2 text-left">Брик</th>
        </tr>
    </thead>
    <tbody class="text-xs">
        @forelse($clients as $client)
            <tr class="border-t hover:bg-gray-50 text-xs">
                <td class="p-2">{{ $client->full_name }}</td>
                <td class="p-2">{{ $client->specialty }}</td>
                <td class="p-2">{{ $client->workplace }}</td>
                <td class="p-2">{{ $client->brick_label }}</td>
                <td class="p-2">{{ $client->city }}</td>
                <td class="p-2">{{ $client->brick_name }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center p-4 text-gray-500">
                    Нет данных
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="mt-4">
    {{ $clients->appends(request()->query())->links() }}
</div>



@endsection
