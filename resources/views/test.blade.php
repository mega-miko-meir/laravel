{{-- <!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
      @vite('resources/css/app.css')
    <title>Document</title>
  </head>
  <body>
    <div class="max-w-md  m-24 rounded overflow-hidden shadow-lg">
      <img class="w-full" src="https://picsum.photos/400/300" alt="Blog Image">
      <div class="px-6 py-4">
        <h2 class="font-bold text-2xl mb-2">This is My Blog Title</h2>
        <p class="mt-3 text-gray-600 text-base">
        Lorem ipsum dolor sit amet, consectetur adipisicing elit. Voluptatibus quia, nulla! Maiores et perferendis eaque,
                exercitationem praesentium nihil.
        </p>
        <button class="mt-4 bg-blue-500 text-white font-bold py-2 px-4 rounded">
            Read More
        </button>
      </div>
    </div>
  </body>
</html> --}}


{{-- <!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Акт приемки передачи</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .table-container {
            width: 100%;
            border-collapse: collapse;
        }
        .table-container th, .table-container td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }
        .table-container th {
            background-color: #f2f2f2;
        }
        .table-container td {
            font-size: 14px;
        }
        .section-title {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1 class="section-title">АКТ ПРИЕМКИ-ПЕРЕДАЧИ ДОЛГОСРОЧНЫХ АКТИВОВ</h1>
    <p>Организация (индивидуальный предприниматель) - сдачик</p>
    <p>АО «Нобел Алматинская Фармацевтическая Фабрика» БИН 940 440 000 405</p>

    <table class="table-container">
        <tr>
            <th colspan="2">Наименование, характеристика</th>
            <th>Серийный номер</th>
            <th>Кол-во. Шт.</th>
            <th>Первоначальная стоимость, в KZT</th>
            <th>Комплектация</th>
        </tr>
        <tr>
            <td colspan="2">Планшет iPad (9th generation) WiFi+Cellular 256GB</td>
            <td>#Н/Д</td>
            <td>1</td>
            <td>-</td>
            <td>
                1. Чехол-1шт.<br>
                2. Оригинальное зарядное устройство - 1шт.<br>
                3. Оригинальный USB-шнур - 1шт.
            </td>
        </tr>
        <tr>
            <td colspan="5"></td>
            <td>Итого</td>
            <td>1</td>
        </tr>
    </table>

    <p>В момент приемки (передачи) активы находятся в:</p>
    <p>АО Нобел АФФ г Алматы ул Шевченко 162 E</p>

    <p>Краткая характеристика активов:</p>
    <p>Исправен и без видимых внешних изъянов</p>

    <h2 class="section-title">Передал в указанном количестве и комплектации</h2>
    <p>Акимбеков Мейжран, CRM-менеджер (ф.и.о, должность)</p>

    <h2 class="section-title">Принял в указанном количестве и комплектации</h2>
    <p>Избуллаева Альбина Хивуллаевна, ИИН 920712400732, МВД РК, 03.09.2015г. (ф.и.о, должность, ИИН, данные удостоверения личности)</p>

    <h2 class="section-title">Подтвердил передачу в указанном количестве и комплектации</h2>
    <p>Подпись (ф.и.о. должность)</p>

    <h2 class="section-title">Настоящий Акт составлен в двух экземплярах для каждой из сторон.</h2>

</body>
</html> --}}



@extends('layout')

@section('content')
    <h1>{{ $message }}</h1>
    <ul>
        @foreach ($bricks as $brick)
            <li>{{ $brick }}</li>
        @endforeach
    </ul>
    <a href="/">Go back</a>


@endsection
