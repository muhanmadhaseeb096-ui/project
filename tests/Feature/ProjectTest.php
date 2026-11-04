<?php

namespace Tests\Feature;

use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_projects_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('projects.index'));

        $response->assertOk();
    }

    public function test_user_can_create_a_project_and_becomes_owner(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('projects.store'), [
            'name' => 'Alpha Initiative',
            'description' => 'Test project description',
            'color' => '#4F46E5',
            'start_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
        ]);

        $project = Project::where('name', 'Alpha Initiative')->first();

        $this->assertNotNull($project);
        $this->assertEquals($user->id, $project->owner_id);
        $response->assertRedirect(route('projects.tasks.index', $project));

        // Verify pivot membership
        $this->assertTrue(
            $project->members()
                ->where('user_id', $user->id)
                ->wherePivot('role', ProjectRole::OWNER->value)
                ->exists()
        );
    }

    public function test_user_cannot_view_unaffiliated_project(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $project = Project::factory()->create(['owner_id' => $owner->id]);
        $project->members()->attach($owner->id, ['role' => ProjectRole::OWNER->value]);

        $response = $this->actingAs($stranger)->get(route('projects.tasks.index', $project));

        $response->assertForbidden();
    }

    public function test_owner_can_delete_project_but_regular_member_cannot(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $project = Project::factory()->create(['owner_id' => $owner->id]);
        $project->members()->attach($owner->id, ['role' => ProjectRole::OWNER->value]);
        $project->members()->attach($member->id, ['role' => ProjectRole::MEMBER->value]);

        // Regular member attempt
        $res1 = $this->actingAs($member)->delete(route('projects.destroy', $project));
        $res1->assertForbidden();
        $this->assertNotSoftDeleted($project);

        // Owner attempt
        $res2 = $this->actingAs($owner)->delete(route('projects.destroy', $project));
        $res2->assertRedirect(route('projects.index'));
        $this->assertSoftDeleted($project);
    }
}

