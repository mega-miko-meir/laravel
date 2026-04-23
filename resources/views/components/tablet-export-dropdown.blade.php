{{-- resources/views/components/tablet-export-dropdown.blade.php --}}

<div class="relative" x-data="{ open: false }">
    <button @click="open = !open"
            class="flex items-center gap-1 bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-2 px-4 rounded-lg shadow-sm transition text-sm whitespace-nowrap">
        Выгрузить
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-show="open"
         @click.away="open = false"
         x-cloak
         class="absolute right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg p-4 w-72 z-[999]">

        <form action="{{ route('export.tablets') }}" method="POST">
            @csrf

            <p class="font-semibold text-sm mb-3 text-gray-700">Выберите колонки:</p>

            <div class="space-y-1.5 text-sm text-gray-700">

                <p class="text-[10px] text-gray-400 uppercase tracking-wide pt-1 pb-0.5 font-semibold">Планшет</p>
                <label class="flex items-center gap-2"><input type="checkbox" name="columns[]" value="invent_number" checked class="rounded"> Инв. номер</label>
                <label class="flex items-center gap-2"><input type="checkbox" name="columns[]" value="serial_number" checked class="rounded"> Серийный номер</label>
                <label class="flex items-center gap-2"><input type="checkbox" name="columns[]" value="model" checked class="rounded"> Модель</label>
                <label class="flex items-center gap-2"><input type="checkbox" name="columns[]" value="imei" class="rounded"> IMEI</label>
                <label class="flex items-center gap-2"><input type="checkbox" name="columns[]" value="beeline_number" class="rounded"> Билайн номер</label>
                {{-- <label class="flex items-center gap-2"><input type="checkbox" name="columns[]" value="beeline_number_status" class="rounded"> Статус Билайн</label> --}}
                <label class="flex items-center gap-2"><input type="checkbox" name="columns[]" value="status" checked class="rounded"> Статус планшета</label>

                <p class="text-[10px] text-gray-400 uppercase tracking-wide pt-2 pb-0.5 font-semibold">Сотрудник</p>
                <label class="flex items-center gap-2"><input type="checkbox" name="columns[]" value="employee_name" checked class="rounded"> Сотрудник</label>
                <label class="flex items-center gap-2"><input type="checkbox" name="columns[]" value="employee_city" class="rounded"> Город сотрудника</label>
                <label class="flex items-center gap-2"><input type="checkbox" name="columns[]" value="employee_manager" class="rounded"> Менеджер сотрудника</label>
                <label class="flex items-center gap-2"><input type="checkbox" name="columns[]" value="position" class="rounded"> Должность</label>
                {{-- <label class="flex items-center gap-2"><input type="checkbox" name="columns[]" value="position" class="rounded"> Должность</label> --}}
                <label class="flex items-center gap-2"><input type="checkbox" name="columns[]" value="assigned_at" class="rounded"> Дата привязки</label>
                {{-- <label class="flex items-center gap-2"><input type="checkbox" name="columns[]" value="returned_at" class="rounded"> Дата возврата</label> --}}

                <p class="text-[10px] text-gray-400 uppercase tracking-wide pt-2 pb-0.5 font-semibold">Ответственное лицо</p>
                <label class="flex items-center gap-2"><input type="checkbox" name="columns[]" value="responsible_name" class="rounded"> Ответственное лицо</label>
                <label class="flex items-center gap-2"><input type="checkbox" name="columns[]" value="responsible_city" class="rounded"> Город ответственного</label>

            </div>

            <div class="mt-4 flex justify-end">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-1.5 rounded-lg">
                    Скачать
                </button>
            </div>
        </form>
    </div>
</div>
