@props(['employee'])

<!-- Кнопка для показа таблицы -->
<button id="showTableButton" class="mt-2 bg-blue-500 text-white px-3 py-1 text-sm rounded-md">КМП запрос</button>

<!-- Модальное окно -->
<div id="tableModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
    <div class="bg-white p-5 rounded-lg shadow-lg w-3/4 relative">
        <button id="copyTableBtn" class="absolute top-2 right-2 bg-gray-200 px-3 py-1 rounded">📋 Копировать</button>
        <button id="closeTableBtn" class="absolute top-2 left-2 bg-red-500 text-white px-3 py-1 rounded">✖ Закрыть</button>
        <br>
        <div id="tableContainer" class="mt-5">
            <table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%; text-align: left; border: 1px solid black;">
                <thead>
                    <tr style="background-color: #f2f2f2; border: 1px solid black;">
                        <th style="border: 1px solid black; padding: 8px;">ФИО</th>
                        <th style="border: 1px solid black; padding: 8px;">Должность</th>
                        <th style="border: 1px solid black; padding: 8px;">Группа</th>
                        <th style="border: 1px solid black; padding: 8px;">Город</th>
                        <th style="border: 1px solid black; padding: 8px;">РМ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @php
                            $parts = explode(' ', $employee->full_name);
                            $KMPName = count($parts) > 2 ? implode(' ', array_slice($parts, 0, 2)) : $employee->full_name
                        @endphp
                        <td style="border: 1px solid black; padding: 8px;">{{ $KMPName }}</td>
                        <td style="border: 1px solid black; padding: 8px;">{{ $employee->position }}</td>
                        <td style="border: 1px solid black; padding: 8px;">{{ $employee->territories->first()->team ?? ''}}</td>
                        <td style="border: 1px solid black; padding: 8px;">{{ $employee->territories->first()->city ?? '' }}</td>
                        <td style="border: 1px solid black; padding: 8px;">{{ $employee->territories->first()->parent->employee->full_name ?? '' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
