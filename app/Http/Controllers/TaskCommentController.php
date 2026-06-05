<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskCommentRequest;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;

class TaskCommentController extends Controller
{
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
}
