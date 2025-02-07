@extends('layout')

@section('content')
<div class="container mx-auto py-6">
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-4">Детали планшета</h2>

        <p><strong>Модель:</strong> {{ $tablet->model }}</p>
        <p><strong>Серийный номер:</strong> {{ $tablet->invent_number }}</p>
        <p><strong>Серийный номер:</strong> {{ $tablet->serial_number }}</p>
        <p><strong>IMEI номер:</strong> {{ $tablet->imei }}</p>
        <p><strong>Билайн номер:</strong> {{ $tablet->beeline_number }}</p>

        <h3 class="text-xl font-semibold mt-6">История пользователей</h3>
        <table class="w-full border-collapse border border-gray-300">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border border-gray-300 px-4 py-2">ФИО</th>
                    <th class="border border-gray-300 px-4 py-2">Дата выдачи</th>
                    <th class="border border-gray-300 px-4 py-2">Дата возврата</th>
                    <th class="border border-gray-300 px-4 py-2">Выдача (PDF)</th>
                    <th class="border border-gray-300 px-4 py-2">Возврат (PDF)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($previousUsers as $record)
                    <tr>
                        <td class="border border-gray-300 px-4 py-2">{{ $record->full_name }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $record->assigned_at ?? '—' }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $record->returned_at ?? '—' }}</td>
                        <td class="border border-gray-300 px-4 py-2">
                            @if($record->pdf_path)
                                <a href="{{ asset('storage/' . $record->pdf_path) }}" class="text-blue-500 hover:underline" target="_blank">📄 PDF</a>
                            @else
                                —
                            @endif
                        </td>
                        <td class="border border-gray-300 px-4 py-2">
                            @if($record->unassign_pdf)
                                <a href="{{ asset('storage/' . $record->unassign_pdf) }}" class="text-blue-500 hover:underline" target="_blank">📄 PDF</a>
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
@endsection

