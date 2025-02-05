@extends('layout')

@section('content')

    <h1>Загрузка Бриков</h1>

    <x-flash-message />
    {{-- @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif --}}

    @if($errors->any())
        <p style="color: red;">{{ $errors->first() }}</p>
    @endif

    <form action="/uploadBricks" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file" required>
        <button type="submit">Загрузить</button>
    </form>
    <br>


    <h1>Загрузка Территории</h1>

    <x-flash-message />

    @if($errors->any())
        <p style="color: red;">{{ $errors->first() }}</p>
    @endif

    <form action="/uploadTerritories" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file" required>
        <button type="submit">Загрузить</button>
    </form>
    <br>


    <h1>Загрузка Сотрудников</h1>

    <x-flash-message />

    @if($errors->any())
        <p style="color: red;">{{ $errors->first() }}</p>
    @endif

    <form action="/uploadEmployees" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file" required>
        <button type="submit">Загрузить</button>
    </form>
    <br>
@endsection
