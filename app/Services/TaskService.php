<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Mail\TaskAssignedMail;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class TaskService
{
    /**
     * Create a task inside a project and notify assignee.
     *
     * @param  array<string, mixed>  $data
     */
    public function createTask(Project $project, array $data, User $creator): Task
    {
        return DB::transaction(function () use ($project, $data, $creator) {
            $status = isset($data['status'])
                ? ($data['status'] instanceof TaskStatus ? $data['status']->value : $data['status'])
                : TaskStatus::TODO->value;

            $maxOrder = (int) ($project->tasks()
                ->where('status', $status)
                ->max('order_index') ?? 0);

            $completedAt = ($status === TaskStatus::COMPLETED->value) ? now() : null;

            $task = $project->tasks()->create([
                'created_by_id' => $creator->id,
                'assigned_to_id' => $data['assigned_to_id'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'priority' => $data['priority'],
                'status' => $status,
                'due_date' => $data['due_date'] ?? null,
                'order_index' => $maxOrder + 1000,
                'completed_at' => $completedAt,
            ]);

            if ($task->assigned_to_id && $task->assigned_to_id !== $creator->id) {
                $assignee = User::find($task->assigned_to_id);
                if ($assignee) {
                    Mail::to($assignee->email)->queue(new TaskAssignedMail($task));
                }
            }

            return $task;
        });
    }

    /**
     * Update an existing task and trigger alerts on assignee change.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateTask(Task $task, array $data, User $updater): Task
    {
        return DB::transaction(function () use ($task, $data, $updater) {
            $oldAssigneeId = $task->assigned_to_id;
            $status = isset($data['status'])
                ? ($data['status'] instanceof TaskStatus ? $data['status']->value : $data['status'])
                : $task->status->value;

            $completedAt = $task->completed_at;
            if ($status === TaskStatus::COMPLETED->value && ! $task->completed_at) {
                $completedAt = now();
            } elseif ($status !== TaskStatus::COMPLETED->value && $task->completed_at) {
                $completedAt = null;
            }

            $task->fill([
                'title' => $data['title'] ?? $task->title,
                'description' => $data['description'] ?? $task->description,
                'priority' => $data['priority'] ?? $task->priority,
                'status' => $status,
                'due_date' => $data['due_date'] ?? $task->due_date,
                'assigned_to_id' => array_key_exists('assigned_to_id', $data) ? $data['assigned_to_id'] : $task->assigned_to_id,
                'completed_at' => $completedAt,
            ]);

            $task->save();

            // Notify newly assigned user
            if ($task->assigned_to_id && $task->assigned_to_id !== $oldAssigneeId && $task->assigned_to_id !== $updater->id) {
                $assignee = User::find($task->assigned_to_id);
                if ($assignee) {
                    Mail::to($assignee->email)->queue(new TaskAssignedMail($task));
                }
            }

            return $task;
        });
    }

    /**
     * Transition a task's status and update order index.
     */
    public function transitionStatus(Task $task, TaskStatus|string $newStatus, ?int $orderIndex = null): Task
    {
        return DB::transaction(function () use ($task, $newStatus, $orderIndex) {
            $statusValue = $newStatus instanceof TaskStatus ? $newStatus->value : $newStatus;

            $completedAt = $task->completed_at;
            if ($statusValue === TaskStatus::COMPLETED->value && ! $task->completed_at) {
                $completedAt = now();
            } elseif ($statusValue !== TaskStatus::COMPLETED->value && $task->completed_at) {
                $completedAt = null;
            }

            $task->status = $statusValue;
            $task->completed_at = $completedAt;

            if ($orderIndex !== null) {
                $task->order_index = $orderIndex;
            }

            $task->save();

            return $task;
        });
    }

    /**
     * Delete a task.
     */
    public function deleteTask(Task $task): bool
    {
        return (bool) $task->delete();
    }
}
