<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskStoreRequest;
use App\Http\Requests\TaskUpdateRequest;
use App\Models\Task;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = auth()->user()->tasks()
            ->orderByRaw("FIELD(status,'todo','in_progress','done')")
            ->orderBy('deadline')
            ->get();

        return view('tasks.index', compact('tasks'));
    }

    public function create()
    {
        return redirect()->route('tasks.index');
    }

    public function store(TaskStoreRequest $request)
    {
        $data = $request->validated();
        $data['status'] = $data['status'] ?? 'todo';

        auth()->user()->tasks()->create($data);

        return redirect()->route('tasks.index')->with('success', 'Задача создана');
    }

    public function edit(Task $task)
    {
        $this->authorizeTask($task);
        return view('tasks.edit', compact('task'));
    }

    public function show(Task $task)
    {
        $this->authorizeTask($task);
        return redirect()->route('tasks.edit', $task);
    }

    public function update(TaskUpdateRequest $request, Task $task)
    {
        $this->authorizeTask($task);
        $task->update($request->validated());

        return redirect()->route('tasks.index')->with('success', 'Задача обновлена');
    }

    public function destroy(Task $task)
    {
        $this->authorizeTask($task);
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Задача удалена');
    }

    private function authorizeTask(Task $task)
    {
        if ($task->user_id !== auth()->id()) {
            abort(403);
        }
    }
}
