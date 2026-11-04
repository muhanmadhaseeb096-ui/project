<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->catchPhrase();

        return [
            'owner_id' => User::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(5),
            'description' => fake()->paragraph(),
            'color' => fake()->randomElement(['#4F46E5', '#0284C7', '#059669', '#D97706', '#E11D48', '#7C3AED']),
            'status' => 'active',
            'start_date' => now(),
            'due_date' => now()->addMonths(2),
        ];
    }
}
