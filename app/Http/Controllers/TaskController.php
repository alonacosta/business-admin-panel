<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;

class TaskController extends Controller
{
    use AuthorizesRequests;

    public function store(StoreTaskRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('create', Task::class);

        $project->tasks()->create([
            ...$request->validated(),
            'owner_id' => $request->user()->id,
        ]);

        return back()->with('success', 'Task created.');
    }

    public function update(UpdateTaskRequest $request, Project $project, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        abort_unless($task->project_id === $project->id, 404);

        $task->update($request->validated());

        return back()->with('success', 'Task updated.');
    }

    public function destroy(Project $project, Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        abort_unless($task->project_id === $project->id, 404);

        $task->delete();

        return back()->with('success', 'Task deleted.');
    }
}
