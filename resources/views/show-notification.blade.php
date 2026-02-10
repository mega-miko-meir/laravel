@extends('layout')

@section('content')
    <h1 class="text-xl font-bold mt-10 mb-4">Просмотр уведомления</h1>

    <div class="bg-white p-4 rounded shadow">
        <h2 class="font-semibold text-lg mb-2">{{ $notification->data['title'] ?? 'Без заголовка' }}</h2>
        <p class="mb-2">От: {{ $notification->data['user_name'] ?? 'Неизвестный пользователь' }}</p>

        <p class="text-gray-800 leading-relaxed whitespace-pre-line mb-4">
            {{ $notification->data['message'] ?? 'Текст сообщения отсутствует' }}
        </p>

        {{-- СЕКЦИЯ СО СКРИНШОТОМ --}}
        @if(!empty($notification->data['screenshot']))
            <div class="mt-4 border-t pt-4">
                <p class="text-sm font-medium text-gray-500 mb-2">Прикрепленное изображение:</p>
                <a href="{{ $notification->data['screenshot'] }}" target="_blank">
                    <img src="{{ $notification->data['screenshot'] }}"
                         alt="Screenshot"
                         class="max-w-full h-auto rounded border shadow-sm hover:shadow-md transition-shadow cursor-zoom-in">
                </a>
            </div>
        @endif

        <p class="text-xs text-gray-400 mt-4">Дата: {{ $notification->created_at->format('d.m.Y H:i') }}</p>
    </div>

    <div class="mt-4">
        <a href="{{ route('admin.notifications') }}" class="text-blue-600 hover:underline">
            ← Назад к уведомлениям
        </a>
    </div>
@endsection
