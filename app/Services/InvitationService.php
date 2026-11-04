<?php

namespace App\Services;

use App\Enums\InvitationStatus;
use App\Mail\ProjectInvitationMail;
use App\Models\Invitation;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class InvitationService
{
    /**
     * Issue and dispatch a cryptographic email invitation.
     */
    public function sendInvitation(
        Project $project,
        string $email,
        string $role,
        User $inviter,
        ?string $customMessage = null
    ): Invitation {
        return DB::transaction(function () use ($project, $email, $role, $inviter, $customMessage) {
            $normalizedEmail = strtolower(trim($email));
            $plainToken = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $plainToken);

            $invitation = Invitation::create([
                'project_id' => $project->id,
                'invited_by_id' => $inviter->id,
                'email' => $normalizedEmail,
                'role' => $role,
                'token_hash' => $tokenHash,
                'status' => InvitationStatus::PENDING->value,
                'expires_at' => now()->addDays(7),
            ]);

            Mail::to($normalizedEmail)->queue(
                new ProjectInvitationMail($invitation, $plainToken, $customMessage)
            );

            return $invitation;
        });
    }

    /**
     * Locate a valid, actionable invitation by plain token.
     */
    public function findValidInvitation(string $plainToken): ?Invitation
    {
        $tokenHash = hash('sha256', $plainToken);

        return Invitation::where('token_hash', $tokenHash)
            ->where('status', InvitationStatus::PENDING->value)
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Accept invitation for an existing authenticated user.
     */
    public function acceptInvitation(Invitation $invitation, User $user): bool
    {
        return DB::transaction(function () use ($invitation, $user) {
            // Attach user to project members
            $invitation->project->members()->syncWithoutDetaching([
                $user->id => [
                    'role' => $invitation->role->value,
                    'joined_at' => now(),
                ],
            ]);

            $invitation->status = InvitationStatus::ACCEPTED;
            $invitation->accepted_at = now();
            $invitation->save();

            return true;
        });
    }

    /**
     * Register a new user and accept their project invitation atomically.
     *
     * @param  array<string, mixed>  $data
     */
    public function registerAndAccept(Invitation $invitation, array $data): User
    {
        return DB::transaction(function () use ($invitation, $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $invitation->email,
                'password' => Hash::make($data['password']),
                'email_verified_at' => now(),
            ]);

            $this->acceptInvitation($invitation, $user);

            return $user;
        });
    }

    /**
     * Resend an existing invitation with an extended expiration.
     */
    public function resendInvitation(Invitation $invitation, User $resender): Invitation
    {
        return DB::transaction(function () use ($invitation, $resender) {
            $plainToken = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $plainToken);

            $invitation->token_hash = $tokenHash;
            $invitation->expires_at = now()->addDays(7);
            $invitation->status = InvitationStatus::PENDING;
            $invitation->invited_by_id = $resender->id;
            $invitation->save();

            Mail::to($invitation->email)->queue(
                new ProjectInvitationMail($invitation, $plainToken)
            );

            return $invitation;
        });
    }

    /**
     * Revoke / cancel a pending invitation.
     */
    public function revokeInvitation(Invitation $invitation): bool
    {
        return (bool) $invitation->delete();
    }
}
