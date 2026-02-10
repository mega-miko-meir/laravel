@extends('layout')

@section('content')

    <h1 class="text-lg font-bold mt-10 mb-4">Сообщения</h1>

    @forelse($notifications as $notification)
        <a href="{{ route('admin.notifications.show', $notification->id) }}"
           class="block p-3 border rounded mb-2
                  {{ $notification->read_at ? 'bg-gray-50' : 'bg-white' }}">

            <div class="font-semibold">
                {{ $notification->data['title'] }}
            </div>

            <div class="text-sm text-gray-500">
                От: {{ $notification->data['user_name'] }}
            </div>
            <div>
                <p class="text-xs text-gray-400 mt-2">Дата: {{ $notification->created_at->format('d.m.Y H:i') }}</p>
            </div>
        </a>
    @empty
        <p class="text-gray-400">Сообщений нет</p>
    @endforelse

{{-- </x-layouts.admin> --}}

@endsection
