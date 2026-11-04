<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(
        protected ProjectService $projectService
    ) {}

    /**
     * Display a listing of all projects the user is affiliated with.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Project::class);

        $projects = $request->user()->projects()
            ->with('owner')
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => function ($query) {
                    $query->where('status', TaskStatus::COMPLETED->value);
                },
                'members',
            ])
            ->latest('projects.created_at')
            ->paginate(12);

        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new project.
     */
    public function create(): View
    {
        $this->authorize('create', Project::class);

        return view('projects.create');
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $project = $this->projectService->createProject($request->validated(), $request->user());

        return redirect()
            ->route('projects.tasks.index', $project)
            ->with('success', "Project '{$project->name}' created successfully!");
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project): RedirectResponse
    {
        $this->authorize('view', $project);

        return redirect()->route('projects.tasks.index', $project);
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(Project $project): View
    {
        $this->authorize('update', $project);

        return view('projects.edit', compact('project'));
    }

    /**
     * Update the specified project in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $this->projectService->updateProject($project, $request->validated());

        return redirect()
            ->route('projects.edit', $project)
            ->with('success', 'Project settings updated successfully.');
    }

    /**
     * Remove the specified project from storage.
     */
    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $this->projectService->deleteProject($project);

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project was successfully deleted.');
    }

    /**
     * Show project team roster and invitation management.
     */
    public function members(Project $project): View
    {
        $this->authorize('view', $project);

        $members = $project->members()->orderBy('project_user.role')->get();
        $invitations = $project->invitations()->with('inviter')->latest()->get();

        return view('projects.members', compact('project', 'members', 'invitations'));
    }
}
