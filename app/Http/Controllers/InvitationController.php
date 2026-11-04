<?php

namespace App\Http\Controllers;

use App\Http\Requests\Invitation\RegisterAndAcceptRequest;
use App\Http\Requests\Invitation\StoreInvitationRequest;
use App\Models\Invitation;
use App\Models\Project;
use App\Services\InvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function __construct(
        protected InvitationService $invitationService
    ) {}

    /**
     * Dispatch an email invitation to join a project.
     */
    public function store(StoreInvitationRequest $request, Project $project): RedirectResponse
    {
        $this->invitationService->sendInvitation(
            $project,
            $request->validated('email'),
            $request->validated('role'),
            $request->user(),
            $request->validated('message')
        );

        return redirect()
            ->route('projects.members', $project)
            ->with('success', "Invitation sent to {$request->validated('email')}.");
    }

    /**
     * Show the invitation landing and acceptance page.
     */
    public function show(string $token): View
    {
        $invitation = $this->invitationService->findValidInvitation($token);

        if (! $invitation) {
            return view('invitations.invalid');
        }

        $invitation->load(['project', 'inviter']);

        return view('invitations.show', [
            'invitation' => $invitation,
            'token' => $token,
            'user' => Auth::user(),
        ]);
    }

    /**
     * Accept the invitation for an existing authenticated user.
     */
    public function accept(Request $request, string $token): RedirectResponse
    {
        $invitation = $this->invitationService->findValidInvitation($token);

        if (! $invitation) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'This invitation is expired or invalid.');
        }

        $this->invitationService->acceptInvitation($invitation, $request->user());

        return redirect()
            ->route('projects.tasks.index', $invitation->project)
            ->with('success', "Welcome to {$invitation->project->name}!");
    }

    /**
     * Register a new user and accept their project invitation.
     */
    public function registerAndAccept(RegisterAndAcceptRequest $request, string $token): RedirectResponse
    {
        $invitation = $this->invitationService->findValidInvitation($token);

        if (! $invitation) {
            return redirect()
                ->route('login')
                ->with('error', 'This invitation has expired or is invalid.');
        }

        $user = $this->invitationService->registerAndAccept($invitation, $request->validated());

        Auth::login($user);

        return redirect()
            ->route('projects.tasks.index', $invitation->project)
            ->with('success', "Welcome to CollabTask and the {$invitation->project->name} workspace!");
    }

    /**
     * Resend an unaccepted invitation with extended expiration.
     */
    public function resend(Request $request, Project $project, Invitation $invitation): RedirectResponse
    {
        $this->authorize('resend', $invitation);

        $this->invitationService->resendInvitation($invitation, $request->user());

        return redirect()
            ->route('projects.members', $project)
            ->with('success', "Invitation resent to {$invitation->email}.");
    }

    /**
     * Revoke / cancel a pending invitation.
     */
    public function destroy(Project $project, Invitation $invitation): RedirectResponse
    {
        $this->authorize('delete', $invitation);

        $this->invitationService->revokeInvitation($invitation);

        return redirect()
            ->route('projects.members', $project)
            ->with('success', 'Invitation has been revoked.');
    }
}
