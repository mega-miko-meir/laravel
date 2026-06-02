@extends('layout')
@section('content')

<div style="max-width:560px;">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
        <a href="{{ route('tasks.index') }}"
           style="display:flex;align-items:center;justify-content:center;width:30px;height:30px;
                  border-radius:7px;border:1px solid #e5e7eb;background:#fff;text-decoration:none;"
           onmouseover="this.style.background='#f3f4f6';"
           onmouseout="this.style.background='#fff';">
            <svg style="width:14px;height:14px;color:#374151;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 style="font-size:20px;font-weight:700;color:#111827;">Редактировать задачу</h1>
    </div>

    <div style="background:#fff;border:1px solid #f0f0f0;border-radius:10px;padding:20px 24px;box-shadow:0 1px 3px rgba(0,0,0,.05);">
        <form action="{{ route('tasks.update', $task) }}" method="POST">
            @csrf @method('PUT')
            <div style="display:grid;gap:12px;">
                <div>
                    <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;
                                  text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Название</label>
                    <input type="text" name="title" value="{{ old('title', $task->title) }}" required
                           style="width:100%;padding:8px 12px;border:1.5px solid #e5e7eb;border-radius:7px;
                                  font-size:13px;color:#374151;box-sizing:border-box;outline:none;"
                           onfocus="this.style.borderColor='#2563eb';"
                           onblur="this.style.borderColor='#e5e7eb';">
                </div>
                <div>
                    <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;
                                  text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Описание</label>
                    <textarea name="description" rows="3"
                              style="width:100%;padding:8px 12px;border:1.5px solid #e5e7eb;border-radius:7px;
                                     font-size:13px;color:#374151;box-sizing:border-box;resize:vertical;outline:none;"
                              onfocus="this.style.borderColor='#2563eb';"
                              onblur="this.style.borderColor='#e5e7eb';">{{ old('description', $task->description) }}</textarea>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;
                                      text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Дедлайн</label>
                        <input type="date" name="deadline"
                               value="{{ old('deadline', $task->deadline) }}"
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
                            @foreach(['todo' => 'К выполнению', 'in_progress' => 'В работе', 'done' => 'Готово'] as $val => $lbl)
                                <option value="{{ $val }}" {{ old('status', $task->status) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:16px;">
                <a href="{{ route('tasks.index') }}"
                   style="padding:7px 16px;font-size:13px;background:#fff;color:#374151;
                          border:1px solid #e5e7eb;border-radius:7px;text-decoration:none;cursor:pointer;"
                   onmouseover="this.style.background='#f3f4f6';"
                   onmouseout="this.style.background='#fff';">Отмена</a>
                <button type="submit"
                        style="padding:7px 16px;font-size:13px;font-weight:600;background:#2563eb;
                               color:#fff;border:none;border-radius:7px;cursor:pointer;"
                        onmouseover="this.style.background='#1d4ed8';"
                        onmouseout="this.style.background='#2563eb';">Сохранить</button>
            </div>
        </form>
    </div>
</div>

@endsection
