{{-- @extends('layouts.app')

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
@endsection --}}


@extends('layout')

@section('content')
<div class="max-w-7xl mx-auto px-4">

    <div class="flex items-center justify-between mb-6 mt-10">
        <h1 class="text-2xl font-bold">
            Статистика активности
        </h1>

        <div x-data="{ open: false }" class="relative">
            <button
                @click="open = !open"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm"
            >
                📥 Выгрузить
            </button>

            {{-- popup --}}
            <div x-show="open" x-cloak
                class="absolute right-0 mt-2 bg-white border rounded-lg shadow p-4 w-64 z-50">

                <form method="GET" action="{{ route('activity.export') }}" class="space-y-3">
                    <div>
                        <label class="text-xs text-gray-500">Дата начала</label>
                        <input type="date" name="from"
                            class="w-full border rounded px-2 py-1 text-sm" required>
                    </div>

                    <div>
                        <label class="text-xs text-gray-500">Дата окончания</label>
                        <input type="date" name="to"
                            class="w-full border rounded px-2 py-1 text-sm" required>
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700 text-sm"
                    >
                        Скачать CSV
                    </button>
                </form>
            </div>
        </div>
    </div>


    <div class="inline-block bg-white shadow rounded-lg">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-300 text-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left">Пользователь</th>
                    <th class="px-4 py-3 text-left">URL</th>
                    <th class="px-4 py-3">Метод</th>
                    <th class="px-4 py-3">IP</th>
                    <th class="px-4 py-3">Дата</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-4 py-2">
                            {{ $log->user?->full_name ?? 'Гость' }}
                        </td>
                        <td class="px-4 py-2 text-gray-600">
                            {{ $log->url }}
                        </td>
                        <td class="px-4 py-2 text-center">
                            <span class="px-2 py-1 rounded text-xs
                                {{ $log->method === 'GET' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                                {{ $log->method }}
                            </span>
                        </td>
                        <td class="px-4 py-2">
                            {{ $log->ip }}
                        </td>
                        <td class="px-4 py-2 text-gray-500">
                            {{ $log->created_at->format('d.m.Y H:i') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-6 text-gray-500">
                            Данных пока нет
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>

</div>
@endsection
