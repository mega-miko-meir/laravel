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

                <!-- Кнопка для создания сотрудника -->
                <div class="absolute top-0 right-0 mt-4 mr-4">
                    <x-create-employee-button />
                </div>

                <!-- Компонент поиска -->
                {{-- <x-search class="mb-6" action="{{route('tablets.search')}}" /> --}}
                <x-search class="mb-6" :action="route('territories.search')" />

                <!-- Заголовок с количеством сотрудников -->
                <h2 class="text-2xl font-bold mb-4 mt-6">
                    Список всех территории ({{ $territories->count() }})
                </h2>

                <!-- Список планшетов -->
                <div class="bg-white shadow-md rounded-lg">
                    <table class="w-full border-collapse border border-gray-300">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="border border-gray-300 px-4 py-2">Территория</th>
                                <th class="border border-gray-300 px-4 py-2">Группа</th>
                                <th class="border border-gray-300 px-4 py-2">Департамент</th>
                                <th class="border border-gray-300 px-4 py-2">Сотрудник</th>
                                <th class="border border-gray-300 px-4 py-2">Менеджер</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($territories as $territory)
                                <tr class="border border-gray-300">
                                    <td class="px-4 py-2">
                                        <a href="{{ route('territories.show', $territory->id) }}" class="text-blue-500 hover:underline">
                                            {{ $territory->territory_name }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ $territory->team }}
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ $territory->department }}
                                    </td>
                                    <td class="px-4 py-2">
                                        @if ($territory->employee)
                                            <a href="{{ route('employees.show', $territory->employee->id) }}" class="text-blue-500 hover:underline">
                                                {{ $territory->employee->full_name }}
                                            </a>
                                        @else
                                            Не назначен
                                        @endif
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ $territory->manager_id }}
                                    </td>
                                </tr>
                            @endforeach


                        </tbody>
                    </table>
                </div>


            </div>
        </x-container>
    @else
        <x-auth-container />
    @endauth

    <script src="{{ asset('js/search.js') }}"></script>
@endsection
