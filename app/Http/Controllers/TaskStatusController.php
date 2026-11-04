<?php

namespace App\Http\Controllers;

use App\Http\Requests\Task\UpdateTaskStatusRequest;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class TaskStatusController extends Controller
{
    public function __construct(
        protected TaskService $taskService
    ) {}

    /**
     * Update task status and order index (for Kanban drag & drop).
     */
    public function update(UpdateTaskStatusRequest $request, Project $project, Task $task): JsonResponse|RedirectResponse
    {
        $status = $request->validated('status');
        $orderIndex = $request->validated('order_index');

        $updatedTask = $this->taskService->transitionStatus($task, $status, $orderIndex);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $updatedTask->status->value,
                'status_label' => $updatedTask->status->label(),
                'completed_at' => $updatedTask->completed_at?->toIso8601String(),
            ]);
        }

        return redirect()->back()->with('success', 'Task status updated.');
    }
}
