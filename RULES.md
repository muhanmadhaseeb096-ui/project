# AI Coding & Engineering Guidelines

## Project: CollabTask - Multi-Project & Team Task Management System
- **Document Version:** 1.0.0
- **Scope:** All PHP, Blade, JavaScript, and Database artifacts in this repository
- **Standard:** PSR-12, Laravel 11/12 Idiomatic Architecture, Clean Code

---

## 1. Architectural Philosophy: The Separation of Concerns

To guarantee maintainability, testability, and strict data isolation, all code contributed to this codebase must adhere strictly to our Layered Architecture.

```
       [ HTTP Request / Web Client ]
                     │
                     ▼
       ┌───────────────────────────┐
       │     Route Middleware      │ ── (auth, verified, project.context)
       └─────────────┬─────────────┘
                     │
                     ▼
       ┌───────────────────────────┐
       │   Custom Form Request     │ ── (Authorization check & Input validation)
       └─────────────┬─────────────┘
                     │
                     ▼
       ┌───────────────────────────┐
       │      Thin Controller      │ ── (Orchestration only: delegates to Service)
       └─────────────┬─────────────┘
                     │
                     ▼
       ┌───────────────────────────┐
       │       Service Layer       │ ── (Business logic, DB transactions, events)
       └─────────────┬─────────────┘
                     │
                     ▼
       ┌───────────────────────────┐
       │    Eloquent Models / DB   │ ── (Pure data mapping, scopes, relationships)
       └─────────────┬─────────────┘
                     │
                     ▼
       ┌───────────────────────────┐
       │ Blade Component / View    │ ── (Reusable presentation markup)
       └───────────────────────────┘
```

---

## 2. Core Architectural Rules

### Rule 2.1: Controllers Must Remain Exceptionally Thin
- **Controllers do not contain raw SQL or complex Eloquent query chains.**
- **Controllers do not contain business logic or transaction boundaries.**
- A controller's sole purpose is to:
  1. Receive the typed `FormRequest`.
  2. Invoke the appropriate `Service` class method.
  3. Return a Blade view, a redirect with flash data, or a structured JSON response.

```php
// ❌ BAD: Fat Controller containing queries, business rules, and transactions
class TaskController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $request->validate(['title' => 'required']);
        if (!$project->members->contains(auth()->id())) {
            abort(403);
        }
        $task = DB::transaction(function() use ($request, $project) {
            return $project->tasks()->create($request->all());
        });
        Mail::to($task->assignee)->send(new TaskAssignedMail($task));
        return redirect()->back();
    }
}

// ✅ GOOD: Thin Controller delegating validation and domain execution
class TaskController extends Controller
{
    public function store(StoreTaskRequest $request, Project $project, TaskService $taskService): RedirectResponse
    {
        $task = $taskService->createTask($project, $request->validated(), auth()->user());

        return redirect()
            ->route('projects.tasks.index', $project)
            ->with('status', "Task '{$task->title}' was successfully created.");
    }
}
```

---

### Rule 2.2: Form Requests Handle Authorization & Validation
- **Every mutating request (`POST`, `PUT`, `PATCH`, `DELETE`) must have a dedicated Form Request class.**
- Never perform `$request->validate([...])` inside controller methods.
- The `authorize()` method in the Form Request must check the relevant Policy or Gate.
- Custom validation messages and attribute names must be provided for a polished user experience.

```php
namespace App\Http\Requests\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');
        return $this->user()->can('create', [Task::class, $project]);
    }

    public function rules(): array
    {
        /** @var Project $project */
        $project = $this->route('project');

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['required', new Enum(TaskPriority::class)],
            'status' => ['sometimes', new Enum(TaskStatus::class)],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
            'assigned_to_id' => [
                'nullable',
                'integer',
                // Enforce that assignee MUST be an active member of this project!
                Rule::exists('project_user', 'user_id')->where(function ($query) use ($project) {
                    $query->where('project_id', $project->id);
                }),
            ],
        ];
    }
}
```

---

### Rule 2.3: Service Layer Encapsulates Domain Logic & Transactions
- Place all business logic inside `app/Services/` classes (e.g., `ProjectService`, `TaskService`, `InvitationService`).
- Any operation modifying multiple records or requiring email dispatch must be wrapped in `DB::transaction()`.
- Fire domain events (e.g., `TaskCreated`, `MemberInvited`, `TaskStatusUpdated`) instead of hardcoding side effects.

```php
namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Mail\TaskAssignedMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class TaskService
{
    /**
     * Create a new task within a project context.
     *
     * @param Project $project
     * @param array<string, mixed> $data
     * @param User $creator
     * @return Task
     */
    public function createTask(Project $project, array $data, User $creator): Task
    {
        return DB::transaction(function () use ($project, $data, $creator) {
            $maxOrder = $project->tasks()
                ->where('status', $data['status'] ?? 'todo')
                ->max('order_index') ?? 0;

            $task = $project->tasks()->create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'priority' => $data['priority'],
                'status' => $data['status'] ?? 'todo',
                'due_date' => $data['due_date'] ?? null,
                'assigned_to_id' => $data['assigned_to_id'] ?? null,
                'created_by_id' => $creator->id,
                'order_index' => $maxOrder + 1000,
            ]);

            if ($task->assigned_to_id && $task->assigned_to_id !== $creator->id) {
                Mail::to($task->assignee->email)->queue(new TaskAssignedMail($task));
            }

            return $task;
        });
    }
}
```

---

### Rule 2.4: Blade Components for Presentation UI
- Avoid repetitive HTML blocks in view templates.
- Extract common UI elements into atomic Blade components (`resources/views/components/`):
  - `<x-button>` (Variants: primary, secondary, danger, ghost)
  - `<x-badge>` (Variants: success, warning, danger, neutral)
  - `<x-modal>` (Reusable dialog with Alpine.js state)
  - `<x-priority-badge :priority="$task->priority" />`
  - `<x-status-badge :status="$task->status" />`
  - `<x-avatar :user="$member" size="sm|md|lg" />`
  - `<x-kanban.column :status="$status" :tasks="$tasks" />`
- Use Blade `@props` to define typed input variables with default fallbacks.

---

## 3. PSR-12, Code Style & Naming Conventions

### 3.1 Strict Typing & Signatures
- **PHP 8.3/8.4 features must be embraced:**
  - Strict type hints on every method parameter.
  - Explicit return types on every method (`void`, `bool`, `RedirectResponse`, `View`, etc.).
  - Constructor property promotion for service injection.
  - PHP 8.1+ backed String Enums for fixed sets of choices.
- Run Laravel Pint before finalizing any commit:
  ```bash
  vendor/bin/pint --format agent
  ```

### 3.2 Naming Matrix

| Element | Format | Example |
|---|---|---|
| **Class & Interface** | `PascalCase` | `ProjectService`, `TaskRepositoryInterface` |
| **Method Name** | `camelCase` (descriptive verb) | `inviteMember()`, `transitionStatus()` |
| **Variable Name** | `camelCase` | `$activeProjects`, `$invitationToken` |
| **Database Table** | `snake_case` (plural) | `projects`, `project_user`, `invitations` |
| **Pivot Table** | `singular_singular` (alphabetical) | `project_user` |
| **Database Column** | `snake_case` | `assigned_to_id`, `expires_at`, `order_index` |
| **Foreign Key** | `{singular_model}_id` | `project_id`, `created_by_id` |
| **Route Name** | `snake_case.dot_notation` | `projects.tasks.index`, `invitations.accept` |
| **Blade View File** | `kebab-case.blade.php` | `task-card.blade.php`, `kanban-board.blade.php` |
| **Backed Enum** | `PascalCase` (Class), `TitleCase` (Cases) | `TaskPriority::Urgent`, `TaskStatus::InReview` |

---

## 4. Authorization, Policies & Multi-Tenant Security Rules

### Rule 4.1: Model Policies Guard Every Action
- Every model (`Project`, `Task`, `Invitation`) must have a corresponding Policy registered in Laravel.
- **Never rely on checking `$user->id == $project->owner_id` directly in controllers or views.**
- Always invoke `$this->authorize('update', $project)` in controllers and `@can('update', $project)` in Blade views.

```php
namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Determine whether the user can view the project workspace.
     */
    public function view(User $user, Project $project): bool
    {
        return $project->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can manage project settings and invite members.
     */
    public function manage(User $user, Project $project): bool
    {
        return $project->members()
            ->where('user_id', $user->id)
            ->wherePivotIn('role', ['owner', 'admin'])
            ->exists();
    }

    /**
     * Determine whether the user can permanently delete or transfer ownership.
     */
    public function delete(User $user, Project $project): bool
    {
        return $project->owner_id === $user->id;
    }
}
```

---

### Rule 4.2: Scoped Route Model Binding
- For nested resources such as `/projects/{project}/tasks/{task}`, activate scoped bindings in `routes/web.php` or route definitions:
  ```php
  Route::resource('projects.tasks', TaskController::class)->scoped();
  ```
- This ensures Laravel automatically validates that `{task}` belongs to `{project}` before the controller is even invoked. If a malicious user passes the ID of another project's task, an HTTP 404 is thrown immediately.

---

### Rule 4.3: Cryptographic Token Safety for Invitations
- Plaintext invitation tokens must never be persisted to the database.
- Generation & storage procedure:
  ```php
  $plainToken = bin2hex(random_bytes(32)); // 64 hex characters
  $hashedToken = hash('sha256', $plainToken);

  $invitation = Invitation::create([
      'project_id' => $project->id,
      'email' => $recipientEmail,
      'token_hash' => $hashedToken,
      'expires_at' => now()->addDays(7),
  ]);
  ```
- Lookup procedure:
  ```php
  $hashedInput = hash('sha256', $providedPlainToken);
  $invitation = Invitation::where('token_hash', $hashedInput)
      ->where('expires_at', '>', now())
      ->whereNull('accepted_at')
      ->firstOrFail();
  ```

---

### Rule 4.4: Elimination of N+1 Queries
- Never iterate over Eloquent relations in Blade loops without eager loading (`with(...)`).
- In `AppServiceProvider::boot()`:
  ```php
  Model::preventLazyLoading(! app()->isProduction());
  Model::shouldBeStrict(! app()->isProduction());
  ```
- Always eager load relations in queries:
  ```php
  $tasks = $project->tasks()
      ->with(['assignee', 'creator'])
      ->orderBy('order_index')
      ->get();
  ```

