<?php

namespace App\Policies;

use App\Enums\ProjectRole;
use App\Models\Invitation;
use App\Models\Project;
use App\Models\User;

class InvitationPolicy
{
    /**
     * Determine whether the user can create invitations for the project.
     */
    public function create(User $user, Project $project): bool
    {
        return $project->members()
            ->where('user_id', $user->id)
            ->wherePivotIn('role', [ProjectRole::OWNER->value, ProjectRole::ADMIN->value])
            ->exists();
    }

    /**
     * Determine whether the user can resend the invitation.
     */
    public function resend(User $user, Invitation $invitation): bool
    {
        return $invitation->project->members()
            ->where('user_id', $user->id)
            ->wherePivotIn('role', [ProjectRole::OWNER->value, ProjectRole::ADMIN->value])
            ->exists();
    }

    /**
     * Determine whether the user can revoke/delete the invitation.
     */
    public function delete(User $user, Invitation $invitation): bool
    {
        return $invitation->project->members()
            ->where('user_id', $user->id)
            ->wherePivotIn('role', [ProjectRole::OWNER->value, ProjectRole::ADMIN->value])
            ->exists();
    }
}

