<?php

namespace Database\Factories;

use App\Enums\InvitationStatus;
use App\Enums\ProjectRole;
use App\Models\Invitation;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invitation>
 */
class InvitationFactory extends Factory
{
    protected $model = Invitation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $plainToken = bin2hex(random_bytes(32));

        return [
            'project_id' => Project::factory(),
            'invited_by_id' => User::factory(),
            'email' => fake()->unique()->safeEmail(),
            'role' => fake()->randomElement([ProjectRole::MEMBER, ProjectRole::ADMIN, ProjectRole::VIEWER]),
            'token_hash' => hash('sha256', $plainToken),
            'status' => InvitationStatus::PENDING,
            'expires_at' => now()->addDays(7),
            'accepted_at' => null,
        ];
    }
}
