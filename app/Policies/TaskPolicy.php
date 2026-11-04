<?php

namespace App\Policies;

use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine whether the user can view tasks in the project.
     */
    public function viewAny(User $user, Project $project): bool
    {
        return $project->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can view the task.
     */
    public function view(User $user, Task $task): bool
    {
        return $task->project->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create tasks within the project.
     */
    public function create(User $user, Project $project): bool
    {
        return $project->members()
            ->where('user_id', $user->id)
            ->wherePivotIn('role', [
                ProjectRole::OWNER->value,
                ProjectRole::ADMIN->value,
                ProjectRole::MEMBER->value,
            ])
            ->exists();
    }

    /**
     * Determine whether the user can update the task details.
     */
    public function update(User $user, Task $task): bool
    {
        $member = $task->project->members()->where('user_id', $user->id)->first();
        if (! $member || ! $member->pivot) {
            return false;
        }

        $role = $member->pivot->role;

        // Owner and Admin can edit any task
        if (in_array($role, [ProjectRole::OWNER, ProjectRole::ADMIN], true)) {
            return true;
        }

        // Members can edit if they created or are assigned to the task
        if ($role === ProjectRole::MEMBER) {
            return $task->created_by_id === $user->id || $task->assigned_to_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can transition the task status (Kanban drag-and-drop).
     */
    public function updateStatus(User $user, Task $task): bool
    {
        $member = $task->project->members()->where('user_id', $user->id)->first();
        if (! $member || ! $member->pivot) {
            return false;
        }

        return $member->pivot->role !== ProjectRole::VIEWER;
    }

    /**
     * Determine whether the user can delete the task.
     */
    public function delete(User $user, Task $task): bool
    {
        $member = $task->project->members()->where('user_id', $user->id)->first();
        if (! $member || ! $member->pivot) {
            return false;
        }

        $role = $member->pivot->role;

        if (in_array($role, [ProjectRole::OWNER, ProjectRole::ADMIN], true)) {
            return true;
        }

        return $task->created_by_id === $user->id;
    }
}

