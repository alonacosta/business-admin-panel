<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Project::class);

        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        $allowedSorts = ['created_at', 'due_date', 'name', 'status'];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $projects = Project::query()
            ->with('owner:id,name,email')
            ->search($request->input('search'))
            ->status($request->input('status'))
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        return Inertia('projects/Index', [
            'projects' => $projects,
            'statuses' => collect(ProjectStatus::cases())->map(fn (ProjectStatus $status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ]),
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
                'sort' => $sort,
                'direction' => $direction,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request)
    {
        $this->authorize('create', Project::class);

        Project::create([
            ...$request->validated(),
            'owner_id' => $request->user()->id,
        ]);

        return redirect()->route('projects.index')->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $this->authorize('view', $project);

        $project->load([
            'owner:id,name,email',
            'tasks.owner:id,name,email',
        ]);

        return Inertia('projects/Show', [
            'project' => $project,
            'statuses' => collect(ProjectStatus::cases())->map(fn (ProjectStatus $status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ]),
            'taskStatuses' => collect(TaskStatus::cases())->map(fn (TaskStatus $status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ]),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $this->authorize('update', $project);

        $project->update($request->validated());

        return redirect()->route('projects.index')->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
    }
}
