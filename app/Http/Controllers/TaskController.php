<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function __construct(
        protected TaskService $taskService
    ) {}

    /**
     * Display Kanban board or List view of tasks within a project.
     */
    public function index(Request $request, Project $project): View
    {
        $this->authorize('viewAny', [Task::class, $project]);

        $currentView = $request->input('view', 'kanban'); // 'kanban' or 'list'
        $selectedAssignee = $request->input('assignee');
        $selectedPriority = $request->input('priority');
        $selectedStatus = $request->input('status');
        $search = $request->input('search');

        $query = $project->tasks()
            ->with(['assignee', 'creator'])
            ->when($selectedAssignee, function ($q, $assignee) use ($request) {
                if ($assignee === 'me') {
                    $q->where('assigned_to_id', $request->user()->id);
                } elseif ($assignee === 'unassigned') {
                    $q->whereNull('assigned_to_id');
                } else {
                    $q->where('assigned_to_id', $assignee);
                }
            })
            ->when($selectedPriority, fn ($q, $priority) => $q->where('priority', $priority))
            ->when($selectedStatus, fn ($q, $status) => $q->where('status', $status))
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            });

        // For Kanban board, group tasks by their 4 status categories
        $tasks = $query->orderBy('order_index', 'asc')->get();

        $kanbanColumns = [
            TaskStatus::TODO->value => $tasks->where('status', TaskStatus::TODO),
            TaskStatus::IN_PROGRESS->value => $tasks->where('status', TaskStatus::IN_PROGRESS),
            TaskStatus::IN_REVIEW->value => $tasks->where('status', TaskStatus::IN_REVIEW),
            TaskStatus::COMPLETED->value => $tasks->where('status', TaskStatus::COMPLETED),
        ];

        $members = $project->members()->get();

        return view('tasks.index', compact(
            'project',
            'tasks',
            'kanbanColumns',
            'members',
            'currentView',
            'selectedAssignee',
            'selectedPriority',
            'selectedStatus',
            'search'
        ));
    }

    /**
     * Show the form for creating a new task.
     */
    public function create(Project $project): View
    {
        $this->authorize('create', [Task::class, $project]);

        $members = $project->members()->get();

        return view('tasks.create', compact('project', 'members'));
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(StoreTaskRequest $request, Project $project): RedirectResponse
    {
        $task = $this->taskService->createTask($project, $request->validated(), $request->user());

        return redirect()
            ->route('projects.tasks.index', $project)
            ->with('success', "Task '{$task->title}' created successfully!");
    }

    /**
     * Display the specified task.
     */
    public function show(Project $project, Task $task): View
    {
        $this->authorize('view', $task);

        $task->load(['project', 'assignee', 'creator']);
        $members = $project->members()->get();

        return view('tasks.show', compact('project', 'task', 'members'));
    }

    /**
     * Show the form for editing the specified task.
     */
    public function edit(Project $project, Task $task): View
    {
        $this->authorize('update', $task);

        $members = $project->members()->get();

        return view('tasks.edit', compact('project', 'task', 'members'));
    }

    /**
     * Update the specified task in storage.
     */
    public function update(UpdateTaskRequest $request, Project $project, Task $task): RedirectResponse
    {
        $this->taskService->updateTask($task, $request->validated(), $request->user());

        return redirect()
            ->route('projects.tasks.index', $project)
            ->with('success', 'Task updated successfully.');
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Project $project, Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $this->taskService->deleteTask($task);

        return redirect()
            ->route('projects.tasks.index', $project)
            ->with('success', 'Task deleted successfully.');
    }
}
