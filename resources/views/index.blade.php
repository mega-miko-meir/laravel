@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Мои задачи</h1>
    <a href="{{ route('tasks.create') }}" class="btn btn-primary mb-3">Создать задачу</a>

    @foreach($tasks as $task)
        <div class="card mb-2">
            <div class="card-body">
                <h5>{{ $task->title }}</h5>
                <p>{{ $task->description }}</p>
                <p>Статус: {{ $task->status }} | Дедлайн: {{ $task->deadline }}</p>
                <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-secondary">Редактировать</a>

                <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger" onclick="return confirm('Удалить?')">Удалить</button>
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection
