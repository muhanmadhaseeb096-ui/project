<?php

namespace Tests\Feature;

use App\Enums\ProjectRole;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Mail\TaskAssignedMail;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TaskLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_member_can_create_task_and_assignee_is_notified(): void
    {
        Mail::fake();

        $creator = User::factory()->create();
        $assignee = User::factory()->create();

        $project = Project::factory()->create(['owner_id' => $creator->id]);
        $project->members()->attach($creator->id, ['role' => ProjectRole::OWNER->value]);
        $project->members()->attach($assignee->id, ['role' => ProjectRole::MEMBER->value]);

        $response = $this->actingAs($creator)->post(route('projects.tasks.store', $project), [
            'title' => 'Implement API Token Auth',
            'description' => 'Detailed task instructions',
            'priority' => TaskPriority::HIGH->value,
            'status' => TaskStatus::TODO->value,
            'assigned_to_id' => $assignee->id,
            'due_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('projects.tasks.index', $project));

        $task = Task::where('title', 'Implement API Token Auth')->first();
        $this->assertNotNull($task);
        $this->assertEquals($assignee->id, $task->assigned_to_id);
        $this->assertEquals($creator->id, $task->created_by_id);

        Mail::assertQueued(TaskAssignedMail::class, function ($mail) use ($assignee) {
            return $mail->hasTo($assignee->email);
        });
    }

    public function test_cannot_assign_task_to_user_who_is_not_a_project_member(): void
    {
        $creator = User::factory()->create();
        $outsider = User::factory()->create();

        $project = Project::factory()->create(['owner_id' => $creator->id]);
        $project->members()->attach($creator->id, ['role' => ProjectRole::OWNER->value]);

        $response = $this->actingAs($creator)->post(route('projects.tasks.store', $project), [
            'title' => 'Invalid Assignment Task',
            'priority' => TaskPriority::LOW->value,
            'assigned_to_id' => $outsider->id,
        ]);

        $response->assertSessionHasErrors('assigned_to_id');
        $this->assertDatabaseMissing('tasks', ['title' => 'Invalid Assignment Task']);
    }

    public function test_transitioning_task_to_completed_captures_completed_at_timestamp(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);
        $project->members()->attach($user->id, ['role' => ProjectRole::OWNER->value]);

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'created_by_id' => $user->id,
            'status' => TaskStatus::IN_PROGRESS,
            'completed_at' => null,
        ]);

        $response = $this->actingAs($user)->patch(route('projects.tasks.status', [$project, $task]), [
            'status' => TaskStatus::COMPLETED->value,
            'order_index' => 500,
        ]);

        $response->assertRedirect();
        $task->refresh();

        $this->assertEquals(TaskStatus::COMPLETED, $task->status);
        $this->assertNotNull($task->completed_at);
        $this->assertEquals(500, $task->order_index);

        // Moving back to In-Progress resets completed_at to null
        $this->actingAs($user)->patch(route('projects.tasks.status', [$project, $task]), [
            'status' => TaskStatus::IN_PROGRESS->value,
        ]);

        $task->refresh();
        $this->assertEquals(TaskStatus::IN_PROGRESS, $task->status);
        $this->assertNull($task->completed_at);
    }

    public function test_scoped_route_model_binding_prevents_cross_project_task_access(): void
    {
        $user = User::factory()->create();

        $projectA = Project::factory()->create(['owner_id' => $user->id]);
        $projectB = Project::factory()->create(['owner_id' => $user->id]);

        $projectA->members()->attach($user->id, ['role' => ProjectRole::OWNER->value]);
        $projectB->members()->attach($user->id, ['role' => ProjectRole::OWNER->value]);

        $taskB = Task::factory()->create([
            'project_id' => $projectB->id,
            'created_by_id' => $user->id,
        ]);

        // Accessing taskB through projectA URL should throw 404 because of scopeBindings()
        $response = $this->actingAs($user)->get(route('projects.tasks.show', [$projectA, $taskB]));

        $response->assertNotFound();
    }
}

