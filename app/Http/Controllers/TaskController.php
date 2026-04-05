<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, string $current_team)
    {
        $this->authorize('create', Task::class);

        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
        ]);

        Task::create($validated);

        return back()->with('flash', ['message' => 'Task created successfully.']);
    }

    public function update(Request $request, string $current_team, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'status' => ['required', 'string', 'in:todo,in_progress,review,done'],
        ]);

        $task->update($validated);

        return back()->with('flash', ['message' => 'Task updated successfully.']);
    }

    public function destroy(string $current_team, Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return back()->with('flash', ['message' => 'Task deleted successfully.']);
    }
}
