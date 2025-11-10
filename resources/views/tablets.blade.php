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
                    <x-create-tablet-button />
                </div>

                <!-- Компонент поиска -->
                {{-- <x-search class="mb-6" action="{{route('tablets.search')}}" /> --}}
                <x-search class="mb-6" :action="route('tablets.search')" />

                <!-- Заголовок с количеством сотрудников -->
                <h2 class="text-2xl font-bold mb-4 mt-6">
                    Список всех планшетов ({{ $tablets->count() }})
                </h2>

                <!-- Список планшетов -->
                <div class="bg-white shadow-md rounded-lg">
                    <table class="w-full border-collapse border border-gray-300">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="border border-gray-300 px-4 py-2">Номер</th>
                                <th class="border border-gray-300 px-4 py-2">Серийный номер</th>
                                <th class="border border-gray-300 px-4 py-2">Сотрудник</th>
                                <th class="border border-gray-300 px-4 py-2">Выдача (PDF)</th>
                                <th class="border border-gray-300 px-4 py-2">Возврат (PDF)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tablets as $tablet)
                                <tr class="border border-gray-300">
                                    <td class="px-4 py-2">
                                        <a href="{{ route('tablets.show', $tablet->id) }}" class="text-blue-500 hover:underline">
                                            {{ $tablet->invent_number }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('tablets.show', $tablet->id) }}" class="text-blue-500 hover:underline">
                                            {{ $tablet->serial_number }}
                                        </a>
                                    </td>
                                    {{-- <td class="px-4 py-2">{{ $tablet->beeline_number }}</td> --}}
                                    <td class="px-4 py-2">
                                        @if ($tablet->employee)
                                            <a href="{{ route('employees.show', $tablet->employee->id) }}" class="text-blue-500 hover:underline">
                                                {{ $tablet->employee->full_name }}
                                            </a>
                                        @else
                                            Не назначен
                                        @endif
                                    </td>
                                    <td class="px-4 py-2">
                                        @if ($tablet->currentAssignment && $tablet->currentAssignment->pdf_path)
                                            <a href="{{ asset('storage/' . $tablet->currentAssignment->pdf_path) }}" class="text-blue-500 hover:underline" target="_blank">📄 PDF</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-2">
                                        @if ($tablet->currentAssignment && $tablet->currentAssignment->unassign_pdf)
                                            <a href="{{ asset('storage/' . $tablet->currentAssignment->unassign_pdf) }}" class="text-blue-500 hover:underline" target="_blank">📄 PDF</a>
                                        @else
                                            —
                                        @endif
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
