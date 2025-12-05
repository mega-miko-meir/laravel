@extends('layout')

@section('content')
<div class="container mx-auto mt-20 p-4">
    <h1 class="text-xl font-bold mb-4">Загрузка Бриков</h1>

    <x-flash-message />

    @if($errors->any())
        <p class="text-red-500">{{ $errors->first() }}</p>
    @endif

    <form action="/upload-bricks" method="POST" enctype="multipart/form-data" class="mb-6">
        @csrf
        <input type="file" name="file" required class="border p-2">
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Загрузить</button>
    </form>

    {{-- <h1 class="text-xl font-bold mb-4">Загрузка Планшетов</h1>

    <x-flash-message />

    @if($errors->any())
        <p class="text-red-500">{{ $errors->first() }}</p>
    @endif

    <form action="/upload-tablets" method="POST" enctype="multipart/form-data" class="mb-6">
        @csrf
        <input type="file" name="file" required class="border p-2">
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Загрузить</button>
    </form>
 --}}



    <h1 class="text-xl font-bold mb-4">Загрузка привязка планшетов</h1>

    <x-flash-message />

    @if($errors->any())
        <p class="text-red-500">{{ $errors->first() }}</p>
    @endif

    <form action="/upload-tablets-assignment" method="POST" enctype="multipart/form-data" class="mb-6">
        @csrf
        <input type="file" name="file" required class="border p-2">
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Загрузить</button>
    </form>

    <h1 class="text-xl font-bold mb-4">Загрузка Сотрудников</h1>


    <x-flash-message />

    @if($errors->any())
        <p class="text-red-500">{{ $errors->first() }}</p>
    @endif

    <form action="/upload-employees" method="POST" enctype="multipart/form-data" class="mb-6">
        @csrf
        <input type="file" name="file" required class="border p-2">
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Загрузить</button>
    </form>
</div>
@endsection
