<?php

namespace App\Policies;

use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any projects.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the project.
     */
    public function view(User $user, Project $project): bool
    {
        return $project->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create projects.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the project metadata.
     */
    public function update(User $user, Project $project): bool
    {
        return $project->members()
            ->where('user_id', $user->id)
            ->wherePivotIn('role', [ProjectRole::OWNER->value, ProjectRole::ADMIN->value])
            ->exists();
    }

    /**
     * Determine whether the user can manage team members and issue invitations.
     */
    public function manageMembers(User $user, Project $project): bool
    {
        return $project->members()
            ->where('user_id', $user->id)
            ->wherePivotIn('role', [ProjectRole::OWNER->value, ProjectRole::ADMIN->value])
            ->exists();
    }

    /**
     * Determine whether the user can delete the project.
     */
    public function delete(User $user, Project $project): bool
    {
        return $project->owner_id === $user->id;
    }
}

