<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskCommentRequest;
use App\Http\Requests\UpdateTaskCommentRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;

class TaskCommentController extends Controller
{
    use AuthorizesRequests;

    public function store(
        StoreTaskCommentRequest $request,
        Project $project,
        Task $task,
    ): RedirectResponse {
        abort_unless($task->project_id === $project->id, 404);

        $task->comments()->create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        return back()->with('success', 'Comment created.');
    }

    public function update(UpdateTaskCommentRequest $request, Project $project, Task $task, TaskComment $comment): RedirectResponse
    {
        abort_unless($task->project_id === $project->id, 404);
        abort_unless($comment->task_id === $task->id, 404);

        $this->authorize('update', $comment);

        $comment->update($request->validated());

        return back()->with('success', 'Comment updated.');
    }

    public function destroy(Project $project, Task $task, TaskComment $comment): RedirectResponse
    {
        abort_unless($task->project_id === $project->id, 404);
        abort_unless($comment->task_id === $task->id, 404);

        $this->authorize('delete', $comment);

        $comment->delete();

        return back()->with('success', 'Comment deleted.');
    }
}
