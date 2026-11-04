<?php

namespace App\Services;

use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectService
{
    /**
     * Create a new project and assign ownership.
     *
     * @param  array<string, mixed>  $data
     */
    public function createProject(array $data, User $owner): Project
    {
        return DB::transaction(function () use ($data, $owner) {
            $baseSlug = Str::slug($data['name']);
            if (empty($baseSlug)) {
                $baseSlug = 'project-'.Str::random(6);
            }

            $slug = $baseSlug;
            $counter = 1;
            while (Project::where('owner_id', $owner->id)->where('slug', $slug)->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }

            $project = Project::create([
                'owner_id' => $owner->id,
                'name' => $data['name'],
                'slug' => $slug,
                'description' => $data['description'] ?? null,
                'color' => $data['color'] ?? '#4F46E5',
                'status' => 'active',
                'start_date' => $data['start_date'] ?? null,
                'due_date' => $data['due_date'] ?? null,
            ]);

            // Automatically attach the creator as Project Owner in the pivot
            $project->members()->attach($owner->id, [
                'role' => ProjectRole::OWNER->value,
                'joined_at' => now(),
            ]);

            return $project;
        });
    }

    /**
     * Update project attributes.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateProject(Project $project, array $data): Project
    {
        return DB::transaction(function () use ($project, $data) {
            if (isset($data['name']) && $data['name'] !== $project->name) {
                $baseSlug = Str::slug($data['name']);
                $slug = $baseSlug;
                $counter = 1;
                while (Project::where('owner_id', $project->owner_id)
                    ->where('slug', $slug)
                    ->where('id', '!=', $project->id)
                    ->exists()) {
                    $slug = "{$baseSlug}-{$counter}";
                    $counter++;
                }
                $project->slug = $slug;
            }

            $project->fill([
                'name' => $data['name'] ?? $project->name,
                'description' => $data['description'] ?? $project->description,
                'color' => $data['color'] ?? $project->color,
                'status' => $data['status'] ?? $project->status,
                'start_date' => $data['start_date'] ?? $project->start_date,
                'due_date' => $data['due_date'] ?? $project->due_date,
            ]);

            $project->save();

            return $project;
        });
    }

    /**
     * Delete / archive a project.
     */
    public function deleteProject(Project $project): bool
    {
        return (bool) $project->delete();
    }
}
