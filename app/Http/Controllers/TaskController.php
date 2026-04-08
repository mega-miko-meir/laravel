<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskStoreRequest;
use App\Http\Requests\TaskUpdateRequest;
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



    public function store(TaskStoreRequest $request)
    {
        $data = $request->validated();

        auth()->user()->tasks()->create($data);

        return redirect()->route('tasks.index')->with('success', 'Задача создана');
    }


    public function show(Task $task)
    {
        $this->authorizeTask($task);
        return $task;
    }

    public function update(TaskUpdateRequest $request, Task $task)
    {
        $this->authorizeTask($task);

        $data = $request->validated();

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

