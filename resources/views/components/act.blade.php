@props(['employee', 'tablet', 'hasPdf', 'pdfAssignment'])


<div class="text-[10px]">
    <div class="text-left font-bold mb-4">
        <br>
        <p>АО «Нобел Алматинская Фармацевтическая Фабрика» БИН 940 440 000 405</p>
    </div>
    <br>
    <br>
    <div class="flex justify-between items-center space-x-4">
        <p class="text-left mt-4 text-lg">АКТ ПРИЕМКИ-ПЕРЕДАЧИ ДОЛГОСРОЧНЫХ АКТИВОВ</p>
        <table class="w-1/6 table-auto border-collapse border border-black mb-5">
            <thead>
                <tr>
                    <th class="border border-black px-4 py-2 w-[280px] text-left">Номер документа</th>
                    <th class="border border-black px-4 py-2 text-left">Дата составления    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="border border-black px-4 py-2"></td>
                    <td class="border border-black px-4 py-2"></td>
                </tr>
            </tbody>
        </table>
    </div>
    <br>
    <table class="w-4/5 table-auto border-collapse border border-black mb-5">
        <thead>
            <tr>
                <th class="border border-black px-4 py-2 w-[280px] text-left">Наименование, характеристика</th>
                <th class="border border-black px-4 py-2 text-left">Серийный номер</th>
                <th class="border border-black px-4 py-2 w-[80px] text-left">Кол-во, шт.</th>
                <th class="border border-black px-4 py-2 w-[120px] text-left">Первоначальная стоимость, в KZT</th>
                <th class="border border-black px-4 py-2 text-left">Комплектация</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="border border-black px-4 py-2">
                    Планшет iPad (9th generation) WiFi+Cellular 256GB
                </td>
                <td class="border border-black px-4 py-2">{{$tablet->serial_number}}</td>
                <td class="border border-black px-4 py-2">1</td>
                <td class="border border-black px-4 py-2"></td>
                <td class="border border-black px-4 py-2">
                    1. Чехол - 1шт.<br>
                    2. Оригинальное зарядное устройство - 1шт.<br>
                    3. Оригинальный USB-шнур - 1шт.
                </td>
            </tr>
            <tr>
                <td colspan="2" class="border-none"></td>
                <td class="border border-black px-4 py-2">Итого</td>
                <td class="border border-black px-4 py-2">1</td>
                <td class="border-none"></td>
            </tr>
        </tbody>
    </table>

    <br>
    <p class="mb-2">
        В момент приемки (передачи) активы находятся в
        <strong>АО Нобел АФФ г. Алматы ул. Шевченко 162 Е</strong>
    </p>
    <br>
    <p class="mb-4">
        Краткая характеристика активов:
        <strong>Исправен и без видимых внешних изъянов</strong>
    </p>

    <br>
    <table class="w-4/5 mx-auto table-auto">
        <tr>
            <td class="py-2">Передал в указанном количестве и комплектации</td>
            <td class="py-2 font-bold">
                {{ $hasPdf ? $employee->full_name :'Акимбеков Мейржан, CRM-менеджер' }}
            </td>
        </tr>
        <tr>
            <td class="py-2">Принял в указанном количестве и комплектации</td>
            <td class="py-2 font-bold">
                {{ $hasPdf ? 'Акимбеков Мейржан, CRM-менеджер' : $employee->full_name }}
            </td>
        </tr>
        <tr>
            <td class="py-2">Подтвердил передачу в указанном количестве и комплектации</td>
            <td class="py-2"></td>
        </tr>
    </table>

    <br>
    <br>
    <br>
    <div class="text-left font-bold mt-4">
        <p>Настоящий акт составлен в двух экземплярах для каждой из сторон.</p>
    </div>
</div>

