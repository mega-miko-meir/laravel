@extends('layout')

@section('content')
<div class="max-w-5xl mx-auto mt-10">

    <!-- Заголовок -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Карточка пользователя</h1>
        <p class="text-gray-500 mt-1">Полная информация о сотруднике</p>
    </div>

    <!-- Карточка -->
    <div class="bg-white shadow-md rounded-xl p-8 border border-gray-100">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

            <div>
                <h2 class="text-sm text-gray-500 uppercase font-semibold">ФИО</h2>
                <p class="text-lg font-medium text-gray-900 mt-1">
                    {{ $user->full_name }}
                </p>
            </div>

            <div>
                <h2 class="text-sm text-gray-500 uppercase font-semibold">Email</h2>
                <p class="text-lg font-medium text-gray-900 mt-1">
                    {{ $user->email }}
                </p>
            </div>

            <div>
                <h2 class="text-sm text-gray-500 uppercase font-semibold">Позиция</h2>
                <p class="text-lg font-medium text-gray-900 mt-1">
                    {{ $user->position ?? '—' }}
                </p>
            </div>

            <div>
                <h2 class="text-sm text-gray-500 uppercase font-semibold">Дата создания</h2>
                <p class="text-lg font-medium text-gray-900 mt-1">
                    {{ $user->created_at->format('d.m.Y') }}
                </p>
            </div>

        </div>

        <!-- Кнопки -->
        <div class="mt-8 flex items-center gap-3">

            <a href="{{ route('users.index') }}"
               class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg transition font-medium">
                ← Назад
            </a>

            <a href=""
               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-medium">
                ✏️ Редактировать
            </a>

        </div>

    </div>

</div>
@endsection
