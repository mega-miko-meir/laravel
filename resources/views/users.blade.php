@extends('layout')

@section('content')

<div class="absolute top-4 right-4 flex gap-2 mt-10">
    <a href="/register"
        class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-1.5 px-4 rounded-md shadow-sm transition duration-200 flex items-center text-sm mt-6">
        + Create
    </a>
</div>
<h1 class="text-2xl font-bold mb-4 mt-10">Пользователи</h1>

<div class="bg-white shadow rounded-lg mt-6 p-4">
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm text-gray-700">
            <thead>
                <tr class="bg-gray-100 text-gray-600 uppercase text-xs">
                    <th class="px-4 py-3 border-b w-12 text-center">№</th>
                    <th class="px-4 py-3 border-b text-left">ФИО</th>
                    <th class="px-4 py-3 border-b text-left">Email</th>
                    <th class="px-4 py-3 border-b text-left">Позиция</th>
                    <th class="px-4 py-3 border-b text-left">Роль</th>
                    <th class="px-4 py-3 border-b text-left">Дата создания</th>
                    <th class="px-4 py-3 border-b text-left">Действие</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($users as $index => $user)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 border-b text-center font-medium text-gray-900">
                            {{ $index + 1 }}
                        </td>

                        <td class="px-4 py-3 border-b font-medium text-gray-900">
                            <a href="{{ route('users.show', ['id' => $user->id]) }}"
                            class="text-blue-600 hover:text-blue-800 hover:underline transition truncate block max-w-xs">
                                {{ $user->full_name }}
                            </a>
                        </td>


                        <td class="px-4 py-3 border-b">
                            {{ $user->email }}
                        </td>

                        <td class="px-4 py-3 border-b">
                            {{ $user->position }}
                        </td>

                        <td class="px-4 py-3 border-b">
                            {{ $user->role->name }}
                        </td>

                        <td class="px-4 py-3 border-b">
                            {{ $user->created_at->format('d.m.Y') }}
                        </td>
                        <td class="px-4 py-3 border-b flex items-center gap-3">
                            <a href="{{route('users.edit', $user->id)}}" class="text-blue-500 hover:text-blue-700 transition text-xl">
                                ✎
                            </a>
                            <form action="{{route('users.destroy', $user->id)}}" method="POST"
                                onsubmit="return confirm('Удалить пользователя?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                class="text-red-500 hover:text-red-700 transition text-sm">
                                ✕
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>


@endsection
