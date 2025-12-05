<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Console\View\Components\Task;

class TaskController extends Controller
{

    public function index()
    {
        $tasks = auth()->user()->tasks()->latest()->get();
        return view('tasks.index', compact('tasks'));
    }

    public function create()
    {
        return view('tasks.create');
    }



    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'status' => 'in:todo,in_progress,done',
            'deadline' => 'nullable|date'
        ]);

        auth()->user()->tasks()->create($data);

        return redirect()->route('tasks.index')->with('success', 'Задача создана');
    }


    public function show(Task $task)
    {
        $this->authorizeTask($task);
        return $task;
    }

    public function update(Request $request, Task $task)
    {
        $this->authorizeTask($task);

        $data = $request->validate([
            'title' => 'string',
            'description' => 'nullable|string',
            'status' => 'in:todo,in_progress,done',
            'deadline' => 'nullable|date'
        ]);

        $task->update($data);
        return $task;
    }

    public function destroy(Task $task)
    {
        $this->authorizeTask($task);
        $task->delete();
        return response()->json(['message' => 'Удалено']);
    }

    private function authorizeTask(Task $task)
    {
        if ($task->user_id !== auth()->id()) {
            abort(403, 'Нет доступа к задаче');
        }
    }
}

