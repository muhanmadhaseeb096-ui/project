<?php

namespace App\Http\Controllers;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the user's primary application dashboard.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Load active projects the user belongs to
        $projects = $user->projects()
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => function ($query) {
                    $query->where('status', TaskStatus::COMPLETED->value);
                },
                'members',
            ])
            ->latest('project_user.joined_at')
            ->take(6)
            ->get();

        // Tasks assigned to this user that are not completed
        $assignedTasks = Task::with('project')
            ->where('assigned_to_id', $user->id)
            ->where('status', '!=', TaskStatus::COMPLETED->value)
            ->orderByRaw("CASE WHEN priority = 'urgent' THEN 1 WHEN priority = 'high' THEN 2 WHEN priority = 'medium' THEN 3 ELSE 4 END")
            ->orderBy('due_date', 'asc')
            ->take(10)
            ->get();

        $stats = [
            'total_projects' => $user->projects()->count(),
            'pending_tasks' => Task::where('assigned_to_id', $user->id)
                ->where('status', '!=', TaskStatus::COMPLETED->value)
                ->count(),
            'urgent_tasks' => Task::where('assigned_to_id', $user->id)
                ->where('priority', TaskPriority::URGENT->value)
                ->where('status', '!=', TaskStatus::COMPLETED->value)
                ->count(),
            'completed_tasks' => Task::where('assigned_to_id', $user->id)
                ->where('status', TaskStatus::COMPLETED->value)
                ->count(),
        ];

        return view('dashboard', compact('projects', 'assignedTasks', 'stats'));
    }
}
