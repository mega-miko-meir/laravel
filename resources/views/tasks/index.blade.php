{{-- resources/views/tasks/index.blade.php --}}
@extends('layouts.app') {{-- Или layouts.master, если используешь другую структуру --}}

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">📋 Мои задачи</h1>

    <a href="{{ route('tasks') }}" class="btn btn-primary mb-3">➕ Новая задача</a>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($tasks->count())
        <div class="list-group">
            @foreach ($tasks as $task)
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>{{ $task->title }}</strong>
                        <div class="text-muted">{{ $task->description }}</div>
                        <small class="text-muted">Создано: {{ $task->created_at->format('d.m.Y H:i') }}</small>
                    </div>
                    <div>
                        <span class="badge {{ $task->is_completed ? 'bg-success' : 'bg-secondary' }}">
                            {{ $task->is_completed ? 'Выполнено' : 'Открыта' }}
                        </span>

                        <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-warning ms-2">✏️</a>

                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline-block"
                              onsubmit="return confirm('Удалить задачу?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">🗑️</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p>У вас пока нет задач.</p>
    @endif
</div>
@endsection
