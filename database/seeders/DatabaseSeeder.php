<?php

namespace Database\Seeders;

use App\Enums\InvitationStatus;
use App\Enums\ProjectRole;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Invitation;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Owner User
        $owner = User::firstOrCreate(
            ['email' => 'owner@collabtask.test'],
            [
                'name' => 'Sarah Jenkins (Owner)',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Create Default Team Member User
        $member = User::firstOrCreate(
            ['email' => 'member@collabtask.test'],
            [
                'name' => 'Alex Rivera (Collaborator)',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // 3. Create Additional Collaborators
        $dev = User::firstOrCreate(
            ['email' => 'david@collabtask.test'],
            [
                'name' => 'David Kim',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $designer = User::firstOrCreate(
            ['email' => 'elena@collabtask.test'],
            [
                'name' => 'Elena Rostova',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // 4. Create Flagship Project 1: Mobile App V2
        $project1 = Project::firstOrCreate(
            ['slug' => 'mobile-app-v2', 'owner_id' => $owner->id],
            [
                'name' => 'Mobile App V2',
                'description' => 'Overhaul customer mobile application with modern UX, offline data sync, and instant push notifications.',
                'color' => '#4F46E5',
                'status' => 'active',
                'start_date' => now()->subWeeks(2),
                'due_date' => now()->addWeeks(6),
            ]
        );

        // Attach members to Project 1
        $project1->members()->syncWithoutDetaching([
            $owner->id => ['role' => ProjectRole::OWNER->value, 'joined_at' => now()->subWeeks(2)],
            $member->id => ['role' => ProjectRole::ADMIN->value, 'joined_at' => now()->subDays(10)],
            $dev->id => ['role' => ProjectRole::MEMBER->value, 'joined_at' => now()->subDays(8)],
            $designer->id => ['role' => ProjectRole::VIEWER->value, 'joined_at' => now()->subDays(5)],
        ]);

        // 5. Create Flagship Project 2: Cloud Infrastructure Migration
        $project2 = Project::firstOrCreate(
            ['slug' => 'cloud-migration', 'owner_id' => $owner->id],
            [
                'name' => 'Cloud Infrastructure Migration',
                'description' => 'Migrate internal monolithic workloads to containerized auto-scaling Kubernetes clusters on AWS.',
                'color' => '#059669',
                'status' => 'active',
                'start_date' => now()->subMonth(),
                'due_date' => now()->addMonth(),
            ]
        );

        $project2->members()->syncWithoutDetaching([
            $owner->id => ['role' => ProjectRole::OWNER->value, 'joined_at' => now()->subMonth()],
            $member->id => ['role' => ProjectRole::MEMBER->value, 'joined_at' => now()->subWeeks(3)],
        ]);

        // 6. Populate Sample Tasks for Project 1
        $tasksData = [
            [
                'title' => 'Design responsive authentication wireframes',
                'description' => 'Craft Figma wireframes for user sign in, password recovery, and email verification screens adhering to the design system.',
                'priority' => TaskPriority::HIGH,
                'status' => TaskStatus::COMPLETED,
                'assigned_to_id' => $designer->id,
                'due_date' => now()->subDays(2),
                'completed_at' => now()->subDay(),
            ],
            [
                'title' => 'Configure OAuth social providers & tokens',
                'description' => 'Integrate Google and GitHub OAuth 2.0 flow using Laravel Socialite and secure token exchanges.',
                'priority' => TaskPriority::MEDIUM,
                'status' => TaskStatus::IN_REVIEW,
                'assigned_to_id' => $member->id,
                'due_date' => now()->addDays(2),
                'completed_at' => null,
            ],
            [
                'title' => 'Implement offline cache & SQLite sync engine',
                'description' => 'Build local client queue ensuring tasks can be triaged offline and synchronized when network connectivity is re-established.',
                'priority' => TaskPriority::URGENT,
                'status' => TaskStatus::IN_PROGRESS,
                'assigned_to_id' => $dev->id,
                'due_date' => now()->addDays(4),
                'completed_at' => null,
            ],
            [
                'title' => 'Audit CSRF & rate limit policies',
                'description' => 'Verify throttle boundaries on authentication and invitation dispatch endpoints.',
                'priority' => TaskPriority::LOW,
                'status' => TaskStatus::TODO,
                'assigned_to_id' => $owner->id,
                'due_date' => now()->addDays(10),
                'completed_at' => null,
            ],
            [
                'title' => 'Setup automated CI/CD pipeline on GitHub Actions',
                'description' => 'Run Laravel Pint, PHPUnit test suite in parallel, and generate build artifacts on every push to main.',
                'priority' => TaskPriority::MEDIUM,
                'status' => TaskStatus::TODO,
                'assigned_to_id' => null,
                'due_date' => now()->addDays(14),
                'completed_at' => null,
            ],
        ];

        $order = 1000;
        foreach ($tasksData as $data) {
            Task::firstOrCreate(
                [
                    'project_id' => $project1->id,
                    'title' => $data['title'],
                ],
                [
                    'created_by_id' => $owner->id,
                    'assigned_to_id' => $data['assigned_to_id'],
                    'description' => $data['description'],
                    'priority' => $data['priority'],
                    'status' => $data['status'],
                    'due_date' => $data['due_date'],
                    'order_index' => $order,
                    'completed_at' => $data['completed_at'],
                ]
            );
            $order += 1000;
        }

        // 7. Create Demo Pending Invitation
        $demoPlainToken = 'demo-token-1234567890abcdef1234567890abcdef';
        Invitation::firstOrCreate(
            [
                'project_id' => $project1->id,
                'email' => 'newcollaborator@example.com',
            ],
            [
                'invited_by_id' => $owner->id,
                'role' => ProjectRole::MEMBER,
                'token_hash' => hash('sha256', $demoPlainToken),
                'status' => InvitationStatus::PENDING,
                'expires_at' => now()->addDays(7),
            ]
        );
    }
}
