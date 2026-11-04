<?php

namespace App\Models;

use App\Enums\ProjectRole;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProjectUser extends Pivot
{
    /**
     * The table associated with the pivot model.
     *
     * @var string
     */
    protected $table = 'project_user';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => ProjectRole::class,
            'joined_at' => 'datetime',
        ];
    }

    public function isOwner(): bool
    {
        return $this->role === ProjectRole::OWNER;
    }

    public function isAdmin(): bool
    {
        return $this->role === ProjectRole::ADMIN;
    }

    public function isAdminOrOwner(): bool
    {
        return in_array($this->role, [ProjectRole::OWNER, ProjectRole::ADMIN], true);
    }
}

