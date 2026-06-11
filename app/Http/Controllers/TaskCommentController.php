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

        $this->authorize('create', TaskComment::class);

        $validated = $request->validated();

        $comment = $task->comments()->create([
            'content' => $validated['content'],
            'user_id' => $request->user()->id,
        ]);

        $this->syncMentions(
            $comment,
            $validated['mentioned_user_ids'] ?? [],
        );

        return back()->with('success', 'Comment created.');
    }

    public function update(UpdateTaskCommentRequest $request, Project $project, Task $task, TaskComment $comment): RedirectResponse
    {
        abort_unless($task->project_id === $project->id, 404);
        abort_unless($comment->task_id === $task->id, 404);

        $this->authorize('update', $comment);

        $validated = $request->validated();

        $comment->update([
            'content' => $validated['content'],
        ]);

        $this->syncMentions(
            $comment,
            $validated['mentioned_user_ids'] ?? [],
        );

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

    private function syncMentions(TaskComment $comment, array $userIds = []): void
    {
        $comment->mentions()->delete();

        $comment->mentions()->createMany(
            collect($userIds)
                ->unique()
                ->map(fn (int $userId) => ['user_id' => $userId])
                ->values()
                ->all()
        );
    }
}
