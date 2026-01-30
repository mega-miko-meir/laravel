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
                <div class="absolute top-0 right-0 mr-4">
                    <x-create-tablet-button />
                </div>

                <div class="">
                    {{-- <h2 class="text-xl font-bold mb-3">Сотрудники без планшета</h2> --}}

                    <div x-data="{ open: false }" class="relative">
                        <!-- Кнопка раскрытия -->
                        <button @click="open = !open" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-all focus:outline-none">
                            Посмотреть сотрудников без планшета ({{ $availableEmployees->count() }})
                        </button>

                        <!-- Выпадающий список -->
                        <div
                            x-show="open"
                            @click.away="open = false"
                            x-cloak
                            class="absolute left-0 mt-2 w-80 bg-white shadow-lg rounded-lg border border-gray-200 z-50 max-h-64 overflow-y-auto"
                        >
                            <ul>
                                @forelse($availableEmployees as $employee)
                                    <li class="px-4 py-2 hover:bg-gray-100 border-b border-gray-100">
                                        <a href="{{ route('employees.show', $employee->id) }}" class="text-blue-600 hover:underline font-medium">
                                            {{ $employee->full_name }}
                                        </a>
                                    </li>
                                @empty
                                    <li class="px-4 py-2 text-gray-500">Нет сотрудников без планшета</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>



                <!-- Компонент поиска -->
                {{-- <x-search class="mb-6" action="{{route('tablets.search')}}" /> --}}
                <x-search class="mb-6" :action="route('tablets.search')" />

                <!-- Заголовок с количеством сотрудников -->
                <h2 class="text-2xl font-bold mb-4 mt-6">
                    Список свободных планшетов ({{ $freeTablets->count() }})
                </h2>

                <!-- Список свободных планшетов -->
                <div class="overflow-x-auto bg-white shadow rounded-lg mt-6 p-4">
                    <table class="w-full border-collapse text-sm text-gray-700">
                        <thead id="head-btn">
                            <tr class="bg-gray-100 text-gray-600 uppercase text-xs cursor-pointer">
                                <th class="px-4 py-3 text-left">Модель</th>
                                <th class="px-4 py-3 text-left">Номер</th>
                                <th class="px-4 py-3 text-left">Серийный номер</th>
                                <th class="px-4 py-3 text-left">Последний сотрудник</th>
                                <th class="px-4 py-3 text-left">Выдача (PDF)</th>
                                <th class="px-4 py-3 text-left">Возврат (PDF)</th>
                            </tr>
                        </thead>
                        <tbody id="body" class="hidden">
                            @foreach($freeTablets as $tablet)
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 text-gray-900 font-medium">
                                        <a href="{{ route('tablets.show', $tablet->id) }}" class="text-blue-500 hover:underline">
                                            {{ $tablet->invent_number }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        <a href="{{ route('tablets.show', $tablet->id) }}" class="text-blue-500 hover:underline">
                                            {{ $tablet->serial_number }}
                                        </a>
                                    </td>
                                    {{-- <td class="px-4 py-2">{{ $tablet->beeline_number }}</td> --}}
                                    <td class="px-4 py-3 text-gray-700">
                                        @if ($tablet->latestAssignment)
                                            <a href="{{ route('employees.show', $tablet->latestAssignment->employee->id) }}"
                                            class="text-blue-500 hover:underline">
                                                {{ $tablet->latestAssignment->employee->full_name }}
                                            </a>
                                        @else
                                            Не был использован
                                        @endif

                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        @if ($tablet->currentAssignment && $tablet->currentAssignment->pdf_path)
                                            <a href="{{ asset('storage/' . $tablet->currentAssignment->pdf_path) }}" class="text-blue-500 hover:underline" target="_blank">📄 PDF</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        @if ($tablet->currentAssignment && $tablet->currentAssignment->unassign_pdf)
                                            <a href="{{ asset('storage/' . $tablet->currentAssignment->unassign_pdf) }}" class="text-blue-500 hover:underline" target="_blank">📄 PDF</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach


                        </tbody>
                        <script>
                            const btn = document.getElementById('head-btn');
                            const body = document.getElementById('body');

                            btn.addEventListener('click', (e) => {
                                e.stopPropagation();
                                body.classList.toggle('hidden');
                            });
                        </script>
                    </table>
                </div>


                <!-- Заголовок с количеством сотрудников -->
                <h2 class="text-2xl font-bold mb-4 mt-6">
                    Список всех планшетов ({{ $tablets->count() }})
                </h2>

                <!-- Список планшетов -->
                <div class="overflow-x-auto bg-white shadow rounded-lg mt-6 p-4">
                    <table class="w-full border-collapse text-sm text-gray-700">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 uppercase text-xs">
                                <th class="px-4 py-3 text-left">Номер</th>
                                <th class="px-4 py-3 text-left">Серийный номер</th>
                                <th class="px-4 py-3 text-left">Сотрудник</th>
                                <th class="px-4 py-3 text-left">Статус</th>
                                <th class="px-4 py-3 text-left">Выдача (PDF)</th>
                                <th class="px-4 py-3 text-left">Возврат (PDF)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tablets as $tablet)
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 text-gray-900 font-medium">
                                        {{-- <a href="{{ route('tablets.show', $tablet->id) }}" class="text-blue-500 hover:underline"> --}}
                                            {{ $tablet->model }}
                                        {{-- </a> --}}
                                    </td>
                                    <td class="px-4 py-3 text-gray-900 font-medium">
                                        <a href="{{ route('tablets.show', $tablet->id) }}" class="text-blue-500 hover:underline">
                                            {{ $tablet->invent_number }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        <a href="{{ route('tablets.show', $tablet->id) }}" class="text-blue-500 hover:underline">
                                            {{ $tablet->serial_number }}
                                        </a>
                                    </td>
                                    {{-- <td class="px-4 py-2">{{ $tablet->beeline_number }}</td> --}}
                                    <td class="px-4 py-3 text-gray-700">
                                        @if ($tablet->current_employee)
                                            <a href="{{ route('employees.show', $tablet->current_employee->id) }}"
                                            class="text-blue-500 hover:underline">
                                                {{ $tablet->current_employee->sh_name }}
                                            </a>
                                        @else
                                            Не назначен
                                        @endif

                                    </td>
                                    <td class="px-4 py-3 text-gray-900 font-medium">
                                        {{ $tablet->status }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        @if ($tablet->currentAssignment && $tablet->currentAssignment->pdf_path)
                                            <a href="{{ asset('storage/' . $tablet->currentAssignment->pdf_path) }}" class="text-blue-500 hover:underline" target="_blank">📄 PDF</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        @if ($tablet->currentAssignment && $tablet->currentAssignment->unassign_pdf)
                                            <a href="{{ asset('storage/' . $tablet->currentAssignment->unassign_pdf) }}" class="text-blue-500 hover:underline" target="_blank">📄 PDF</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach


                        </tbody>
                        <script>
                            const btn = document.getElementById('head-btn');
                            const body = document.getElementById('body');

                            btn.addEventListener('click', (e) => {
                                e.stopPropagation();
                                body.classList.toggle('hidden');
                            });
                        </script>
                    </table>
                </div>


            </div>
        </x-container>
    @else
        <x-auth-container />
    @endauth

    <script src="{{ asset('js/search.js') }}"></script>
@endsection
