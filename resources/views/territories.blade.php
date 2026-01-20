@extends('layout')

@section('content')
    @auth
        <x-container class="container mx-auto py-6">
            <!-- Боковое меню -->
            {{-- <x-side-menu class="col-span-2" /> --}}

            <!-- Основной контент -->
            <div class="col-span-10 relative">
                <!-- Включение шапки -->
                <x-header class="mb-6" />

                <!-- Сообщение об успехе -->
                <x-flash-message />

                <!-- Кнопка для создания сотрудника -->
                <div class="absolute top-0 right-0 mt-4 mr-4">
                    <x-create-territory-button />
                </div>

                <!-- Компонент поиска -->
                {{-- <x-search class="mb-6" action="{{route('tablets.search')}}" /> --}}
                <x-search class="mb-6" :action="route('territories.search')" />

                <!-- Заголовок с количеством сотрудников -->
                <h2 class="text-2xl font-bold mb-4 mt-6">
                    Список всех территории ({{ $territories->count() }})
                </h2>

                <!-- Список планшетов -->
                <div class="overflow-x-auto bg-white shadow rounded-lg mt-6 p-4">
                    <table class="w-full border-collapse text-sm text-gray-700">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 uppercase text-xs">
                                <th class="px-4 py-3 text-left">
                                    <a href="{{ route('territories.search', [
                                        'sort' => 'territory_name',
                                        'order' => request('order') === 'asc' ? 'desc' : 'asc'
                                    ]) }}">
                                        Территория
                                        @if($sort === 'territory_name')
                                            {!! $order === 'asc' ? '↑' : '↓' !!}
                                        @endif
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-left">Позиция</th>
                                <th class="px-4 py-3 text-left">Группа</th>
                                <th class="px-4 py-3 text-left">Департамент</th>
                                <th class="px-4 py-3 text-left">Сотрудник</th>
                                <th class="px-4 py-3 text-left">Город</th>
                                <th class="px-4 py-3 text-left">Менеджер</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($territories as $territory)
                                <tr class="border-b hover:bg-gray-50 transition">
                                    {{-- Территория --}}
                                    <td class="px-4 py-3 text-gray-900 font-medium">
                                        <a href="{{ route('territories.show', $territory->id) }}"
                                            class="text-blue-500 hover:underline">
                                            {{ $territory->territory_name }}
                                        </a>
                                    </td>

                                    {{-- Позиция --}}
                                    <td class="px-4 py-3 text-gray-900 font-medium">
                                        <a href="{{ route('territories.show', $territory->id) }}"
                                        class="text-blue-500 hover:underline">
                                            {{ $territory->role }}
                                        </a>
                                    </td>

                                    {{-- Группа --}}
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ $territory->team ?? '-' }}
                                    </td>

                                    {{-- Департамент --}}
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ $territory->department ?? '-' }}
                                    </td>

                                    {{-- Сотрудник --}}
                                    <td class="px-4 py-3 text-gray-700">
                                        @php
                                            $employeeTerritory = \App\Models\EmployeeTerritory::where('territory_id', $territory->id)
                                                ->whereNull('unassigned_at')
                                                ->latest('assigned_at')
                                                ->first();

                                            $employee = $employeeTerritory?->employee;
                                        @endphp

                                        @if ($employee)
                                            <a href="{{ route('employees.show', $employee->id) }}"
                                            class="text-blue-500 hover:underline">
                                                {{ $employee->full_name }}
                                            </a>
                                        @else
                                            <span class="text-gray-400 italic">Не назначен</span>
                                        @endif
                                    </td>

                                    {{-- Группа --}}
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ $territory->city ?? '-' }}
                                    </td>

                                    {{-- Менеджер --}}
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ $territory->manager_id ?? '-' }}
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
