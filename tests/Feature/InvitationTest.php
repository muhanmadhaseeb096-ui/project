<?php

namespace Tests\Feature;

use App\Enums\InvitationStatus;
use App\Enums\ProjectRole;
use App\Mail\ProjectInvitationMail;
use App\Models\Invitation;
use App\Models\Project;
use App\Models\User;
use App\Services\InvitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_send_invitation_with_cryptographic_token(): void
    {
        Mail::fake();

        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->id]);
        $project->members()->attach($owner->id, ['role' => ProjectRole::OWNER->value]);

        $response = $this->actingAs($owner)->post(route('projects.invitations.store', $project), [
            'email' => 'collaborator@example.com',
            'role' => 'member',
            'message' => 'Welcome to the team!',
        ]);

        $response->assertRedirect(route('projects.members', $project));

        $invitation = Invitation::where('email', 'collaborator@example.com')->first();
        $this->assertNotNull($invitation);
        $this->assertEquals(64, strlen($invitation->token_hash));
        $this->assertEquals(InvitationStatus::PENDING, $invitation->status);
        $this->assertEquals(ProjectRole::MEMBER, $invitation->role);

        Mail::assertQueued(ProjectInvitationMail::class, function ($mail) {
            return $mail->hasTo('collaborator@example.com');
        });
    }

    public function test_authenticated_user_can_accept_invitation(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->id]);
        $project->members()->attach($owner->id, ['role' => ProjectRole::OWNER->value]);

        $invitee = User::factory()->create(['email' => 'invitee@example.com']);

        $service = app(InvitationService::class);
        $plainToken = 'test-token-abcdef1234567890abcdef1234567890';
        $invitation = Invitation::create([
            'project_id' => $project->id,
            'invited_by_id' => $owner->id,
            'email' => 'invitee@example.com',
            'role' => ProjectRole::MEMBER->value,
            'token_hash' => hash('sha256', $plainToken),
            'status' => InvitationStatus::PENDING->value,
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->actingAs($invitee)->post(route('invitations.accept', ['token' => $plainToken]));

        $response->assertRedirect(route('projects.tasks.index', $project));
        $invitation->refresh();

        $this->assertEquals(InvitationStatus::ACCEPTED, $invitation->status);
        $this->assertNotNull($invitation->accepted_at);
        $this->assertTrue($project->members()->where('user_id', $invitee->id)->exists());
    }

    public function test_guest_can_register_and_accept_invitation(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->id]);
        $project->members()->attach($owner->id, ['role' => ProjectRole::OWNER->value]);

        $plainToken = 'guest-token-1234567890abcdef1234567890abcdef';
        $invitation = Invitation::create([
            'project_id' => $project->id,
            'invited_by_id' => $owner->id,
            'email' => 'newguest@example.com',
            'role' => ProjectRole::ADMIN->value,
            'token_hash' => hash('sha256', $plainToken),
            'status' => InvitationStatus::PENDING->value,
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->post(route('invitations.register', ['token' => $plainToken]), [
            'name' => 'Alice Cooper',
            'password' => 'SecureP@ssw0rd!',
            'password_confirmation' => 'SecureP@ssw0rd!',
        ]);

        $response->assertRedirect(route('projects.tasks.index', $project));

        $newUser = User::where('email', 'newguest@example.com')->first();
        $this->assertNotNull($newUser);
        $this->assertAuthenticatedAs($newUser);

        $this->assertTrue(
            $project->members()
                ->where('user_id', $newUser->id)
                ->wherePivot('role', ProjectRole::ADMIN->value)
                ->exists()
        );
    }

    public function test_cannot_accept_expired_invitation(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        $plainToken = 'expired-token-1234567890abcdef1234567890';
        Invitation::create([
            'project_id' => $project->id,
            'invited_by_id' => $owner->id,
            'email' => 'expired@example.com',
            'role' => ProjectRole::MEMBER->value,
            'token_hash' => hash('sha256', $plainToken),
            'status' => InvitationStatus::PENDING->value,
            'expires_at' => now()->subDay(), // Expired
        ]);

        $response = $this->get(route('invitations.show', ['token' => $plainToken]));
        $response->assertOk();
        $response->assertSeeText('Invitation Expired or Invalid');
    }
}

