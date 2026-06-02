@extends('layout')
@section('content')

@php
    $statusLabel = ['todo' => 'К выполнению', 'in_progress' => 'В работе', 'done' => 'Готово'];
    $statusColor = [
        'todo'        => ['bg' => '#f3f4f6', 'text' => '#6b7280', 'border' => '#e5e7eb'],
        'in_progress' => ['bg' => '#eff6ff', 'text' => '#2563eb', 'border' => '#bfdbfe'],
        'done'        => ['bg' => '#f0fdf4', 'text' => '#16a34a', 'border' => '#bbf7d0'],
    ];
    $nextStatus = ['todo' => 'in_progress', 'in_progress' => 'done', 'done' => 'todo'];
@endphp

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
    <h1 style="font-size:20px;font-weight:700;color:#111827;">Задачи</h1>
    <button onclick="document.getElementById('new-task-form').style.display=document.getElementById('new-task-form').style.display==='none'?'block':'none'"
            style="padding:7px 16px;background:#2563eb;color:#fff;border:none;border-radius:8px;
                   font-size:13px;font-weight:600;cursor:pointer;"
            onmouseover="this.style.background='#1d4ed8';"
            onmouseout="this.style.background='#2563eb';">
        + Новая задача
    </button>
</div>

{{-- Форма создания --}}
<div id="new-task-form" style="display:none;margin-bottom:16px;max-width:700px;">
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:16px 20px;box-shadow:0 1px 3px rgba(0,0,0,.05);">
        <p style="font-size:13px;font-weight:600;color:#111827;margin-bottom:12px;">Новая задача</p>
        <form action="{{ route('tasks.store') }}" method="POST">
            @csrf
            <div style="display:grid;gap:10px;">
                <input type="text" name="title" placeholder="Название задачи" required
                       style="width:100%;padding:8px 12px;border:1.5px solid #e5e7eb;border-radius:7px;
                              font-size:13px;color:#374151;box-sizing:border-box;outline:none;"
                       onfocus="this.style.borderColor='#2563eb';"
                       onblur="this.style.borderColor='#e5e7eb';">
                <textarea name="description" placeholder="Описание (необязательно)" rows="2"
                          style="width:100%;padding:8px 12px;border:1.5px solid #e5e7eb;border-radius:7px;
                                 font-size:13px;color:#374151;box-sizing:border-box;resize:vertical;outline:none;"
                          onfocus="this.style.borderColor='#2563eb';"
                          onblur="this.style.borderColor='#e5e7eb';"></textarea>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div>
                        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;
                                      text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Дедлайн</label>
                        <input type="date" name="deadline"
                               style="width:100%;padding:7px 10px;border:1.5px solid #e5e7eb;border-radius:7px;
                                      font-size:13px;color:#374151;box-sizing:border-box;outline:none;"
                               onfocus="this.style.borderColor='#2563eb';"
                               onblur="this.style.borderColor='#e5e7eb';">
                    </div>
                    <div>
                        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;
                                      text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Статус</label>
                        <select name="status"
                                style="width:100%;padding:7px 10px;border:1.5px solid #e5e7eb;border-radius:7px;
                                       font-size:13px;color:#374151;box-sizing:border-box;outline:none;background:#fff;"
                                onfocus="this.style.borderColor='#2563eb';"
                                onblur="this.style.borderColor='#e5e7eb';">
                            <option value="todo">К выполнению</option>
                            <option value="in_progress">В работе</option>
                        </select>
                    </div>
                </div>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px;">
                <button type="button"
                        onclick="document.getElementById('new-task-form').style.display='none'"
                        style="padding:7px 16px;font-size:13px;background:#fff;color:#374151;
                               border:1px solid #e5e7eb;border-radius:7px;cursor:pointer;"
                        onmouseover="this.style.background='#f3f4f6';"
                        onmouseout="this.style.background='#fff';">Отмена</button>
                <button type="submit"
                        style="padding:7px 16px;font-size:13px;font-weight:600;background:#2563eb;
                               color:#fff;border:none;border-radius:7px;cursor:pointer;"
                        onmouseover="this.style.background='#1d4ed8';"
                        onmouseout="this.style.background='#2563eb';">Создать</button>
            </div>
        </form>
    </div>
</div>

@if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:10px 14px;
                color:#15803d;font-size:13px;margin-bottom:14px;max-width:700px;">
        {{ session('success') }}
    </div>
@endif

@if($tasks->isEmpty())
    <p style="color:#9ca3af;font-size:13px;">Задач пока нет. Создайте первую!</p>
@else
    <div style="display:flex;flex-direction:column;gap:8px;max-width:700px;">
        @foreach($tasks as $task)
            @php
                $sc = $statusColor[$task->status];
                $isOverdue = $task->deadline && $task->status !== 'done' && now()->toDateString() > $task->deadline;
                $isToday   = $task->deadline && $task->status !== 'done' && now()->toDateString() === $task->deadline;
            @endphp
            <div style="background:#fff;border:1px solid #f0f0f0;border-radius:10px;
                        padding:14px 16px;box-shadow:0 1px 3px rgba(0,0,0,.05);
                        {{ $isOverdue ? 'border-left:3px solid #dc2626;' : ($isToday ? 'border-left:3px solid #d97706;' : '') }}">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;">
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                            <span style="display:inline-block;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;
                                         background:{{ $sc['bg'] }};color:{{ $sc['text'] }};border:1px solid {{ $sc['border'] }};">
                                {{ $statusLabel[$task->status] }}
                            </span>
                            @if($task->deadline)
                                <span style="font-size:11px;
                                             color:{{ $isOverdue ? '#dc2626' : ($isToday ? '#d97706' : '#9ca3af') }};
                                             font-weight:{{ $isOverdue || $isToday ? '600' : '400' }};">
                                    {{ $isOverdue ? 'Просрочено · ' : ($isToday ? 'Сегодня · ' : '') }}{{ \Carbon\Carbon::parse($task->deadline)->format('d.m.Y') }}
                                </span>
                            @endif
                        </div>
                        <p style="font-size:13px;font-weight:600;color:{{ $task->status === 'done' ? '#9ca3af' : '#111827' }};
                                  margin:0 0 2px;{{ $task->status === 'done' ? 'text-decoration:line-through;' : '' }}">
                            {{ $task->title }}
                        </p>
                        @if($task->description)
                            <p style="font-size:12px;color:#6b7280;margin:0;">{{ $task->description }}</p>
                        @endif
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
                        {{-- Сменить статус --}}
                        @if($task->status !== 'done')
                            <form action="{{ route('tasks.update', $task) }}" method="POST">
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="{{ $nextStatus[$task->status] }}">
                                <button type="submit"
                                        title="{{ $task->status === 'todo' ? 'Взять в работу' : 'Завершить' }}"
                                        style="width:30px;height:30px;border-radius:7px;border:1.5px solid #e5e7eb;
                                               background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;"
                                        onmouseover="this.style.borderColor='#2563eb';this.style.background='#eff6ff';"
                                        onmouseout="this.style.borderColor='#e5e7eb';this.style.background='#fff';">
                                    @if($task->status === 'todo')
                                        <svg style="width:14px;height:14px;color:#2563eb;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @else
                                        <svg style="width:14px;height:14px;color:#16a34a;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @endif
                                </button>
                            </form>
                        @else
                            <form action="{{ route('tasks.update', $task) }}" method="POST">
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="todo">
                                <button type="submit" title="Вернуть в работу"
                                        style="width:30px;height:30px;border-radius:7px;border:1.5px solid #e5e7eb;
                                               background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;"
                                        onmouseover="this.style.borderColor='#6b7280';this.style.background='#f3f4f6';"
                                        onmouseout="this.style.borderColor='#e5e7eb';this.style.background='#fff';">
                                    <svg style="width:14px;height:14px;color:#6b7280;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                </button>
                            </form>
                        @endif

                        {{-- Редактировать --}}
                        <a href="{{ route('tasks.edit', $task) }}"
                           style="width:30px;height:30px;border-radius:7px;border:1.5px solid #e5e7eb;
                                  background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;text-decoration:none;"
                           onmouseover="this.style.borderColor='#2563eb';this.style.background='#eff6ff';"
                           onmouseout="this.style.borderColor='#e5e7eb';this.style.background='#fff';">
                            <svg style="width:13px;height:13px;color:#374151;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>

                        {{-- Удалить --}}
                        <form action="{{ route('tasks.destroy', $task) }}" method="POST"
                              onsubmit="return confirm('Удалить задачу?');">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    style="width:30px;height:30px;border-radius:7px;border:1.5px solid #e5e7eb;
                                           background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;"
                                    onmouseover="this.style.borderColor='#dc2626';this.style.background='#fef2f2';"
                                    onmouseout="this.style.borderColor='#e5e7eb';this.style.background='#fff';">
                                <svg style="width:13px;height:13px;color:#dc2626;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

@endsection
