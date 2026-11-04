# Product Requirement Document (PRD)

## Project: CollabTask - Multi-Project & Team Task Management System
- **Document Version:** 1.0.0
- **Status:** Approved / Production-Grade
- **Target Audience:** Engineering Leads, Full-Stack Developers, Product Managers, QA Engineers
- **System Architecture:** Monolithic Laravel 11/12 with Blade, Tailwind CSS, and MySQL

---

## 1. Executive Summary

### 1.1 Problem Statement
Modern project teams struggle with bloated, overly complex project management tools that introduce high cognitive friction, convoluted permission schemes, and sluggish client-side heavy single-page application (SPA) architectures. Small-to-medium teams require a fast, clean, and reliable tool that provides robust multi-project boundaries, swift task triage across well-defined lifecycle stages, and effortless member onboarding without friction.

### 1.2 Product Vision
**CollabTask** provides a high-performance, server-rendered web application that delivers desktop-class speed, clear project tenant boundaries, seamless cryptographic team invitations, and intuitive task tracking (via modern Kanban and list views). By leveraging Laravel’s robust architectural capabilities (Eloquent ORM, Form Requests, Services, Policies, and Blade components), CollabTask ensures zero data leaks between projects while maintaining a lightweight, modern UI.

### 1.3 Core Objectives & KPIs
- **Frictionless Onboarding:** Sub-60-second invitation-to-collaboration workflow via signed cryptographic email tokens.
- **Strict Data Isolation:** 100% tenant-level project isolation preventing horizontal privilege escalation.
- **Zero Ambiguity Task Lifecycle:** Deterministic 4-stage task progression (`Todo`, `In-Progress`, `In-Review`, `Completed`) with full assignee accountability and priority classification.
- **Performance:** Sub-200ms page response time on 95th percentile requests under standard team loads.

---

## 2. User Personas & Role Matrix

### 2.1 User Personas

#### Persona 1: Sarah Jenkins – Project Owner / Workspace Administrator
- **Role:** Engineering Manager / Product Lead
- **Characteristics:** Manages multiple cross-functional projects simultaneously. Needs high-level visibility across project health, the ability to spin up new projects rapidly, invite team members with specific roles, and safeguard project settings.
- **Core Jobs-to-be-Done (JTBD):**
  - "I want to create distinct projects for different clients or internal products so that work and member rosters remain strictly compartmentalized."
  - "I want to invite team members via their corporate email address and assign them an appropriate access level without manual credential provisioning."
  - "I want to transfer ownership or archive projects once deliverables are finalized."

#### Persona 2: Alex Rivera – Team Member / Contributor
- **Role:** Software Engineer / UI Designer / Technical Writer
- **Characteristics:** Works across 2 to 3 projects. Needs a distraction-free environment to inspect assigned tasks, understand priority urgency, update task statuses as work progresses, and meet target due dates.
- **Core Jobs-to-be-Done (JTBD):**
  - "I want to click an email invitation link, authenticate, and immediately see the tasks relevant to the project I was invited to."
  - "I want to filter tasks by 'Assigned to Me' and easily drag or transition a card from 'In-Progress' to 'In-Review'."
  - "I want clear visual priority indicators so I never miss an urgent deadline."

### 2.2 Role & Permission Matrix

The application enforces a Role-Based Access Control (RBAC) model per project via the `project_user` pivot relationship:

| Action / Capability | Project Owner | Project Admin | Team Member | Viewer |
|---|:---:|:---:|:---:|:---:|
| **Delete Project** | ✅ | ❌ | ❌ | ❌ |
| **Transfer Project Ownership** | ✅ | ❌ | ❌ | ❌ |
| **Update Project Metadata (Name, Color, Dates)** | ✅ | ✅ | ❌ | ❌ |
| **Invite New Members via Email** | ✅ | ✅ | ❌ | ❌ |
| **Revoke / Resend Pending Invitations** | ✅ | ✅ | ❌ | ❌ |
| **Remove Existing Members** | ✅ | ✅ (Members/Viewers only) | ❌ | ❌ |
| **Create New Tasks** | ✅ | ✅ | ✅ | ❌ |
| **Edit Any Task Metadata** | ✅ | ✅ | ❌ | ❌ |
| **Edit Assigned Task Metadata / Status** | ✅ | ✅ | ✅ | ❌ |
| **Delete Tasks** | ✅ | ✅ | ❌ (Unless Creator) | ❌ |
| **Assign / Reassign Tasks** | ✅ | ✅ | ✅ | ❌ |
| **View Project Board, Lists & Tasks** | ✅ | ✅ | ✅ | ✅ |

---

## 3. Detailed Functional Requirements (FRD)

### Module 1: User Authentication & Profiles

- **FR-1.1: Standard Authentication Pipeline**
  - Secure registration with `name`, `email`, `password`, and `password_confirmation`.
  - Passwords hashed using Bcrypt / Argon2id with work factor >= 12.
  - Standard session-based authentication with "Remember Me" capability using secure HTTP-only cookies.
  - Password reset mechanism with signed, time-limited reset tokens (60 minutes).
  - Rate limiting enforced on all authentication routes: maximum 5 attempts per minute per IP.

- **FR-1.2: User Profile Management**
  - Users can view and modify their profile details (Full Name, Display Name, Bio, Notification Preferences).
  - Profile avatar support: users can upload an avatar (PNG/JPG/WebP, max 2MB) or fall back to an automatically generated SVG avatar based on user initials and deterministic background colors.
  - Ability to change password requiring current password verification.

- **FR-1.3: Email Verification**
  - Mandatory email verification (`MustVerifyEmail` interface) for self-registered users before accessing project workspace creation.
  - Direct invitation acceptance bypasses separate manual verification if the invitee registers through their unique token link.

---

### Module 2: Multi-Project Management

- **FR-2.1: Multi-Project Creation & Ownership**
  - Any authenticated and verified user can create multiple projects.
  - Upon project creation:
    - The creator is automatically recorded as `owner_id`.
    - A record is created in the `project_user` pivot table setting the creator's role to `owner`.
    - A URL-friendly unique slug is auto-generated from the project name (scoped unique per owner).
  - Required project fields: `name`, `description` (optional markdown), `color` (hex string, e.g., `#4F46E5`), `start_date`, `due_date`.

- **FR-2.2: Multi-Project Isolation & Switching**
  - Users can belong to unlimited projects as an owner, admin, member, or viewer.
  - Navigation sidebar features a **Project Switcher** displaying all active projects the user has access to.
  - Selecting a project sets the current workspace context and persists active project state in user preferences / session.
  - All project-related routes are strictly scoped by project ID or project slug (e.g., `/projects/{project}/tasks`).

- **FR-2.3: Project Updating & Archival**
  - Project Owners and Admins can update project metadata (name, description, color, target dates, status).
  - Supported project statuses: `Active`, `On Hold`, `Archived`, `Completed`.
  - Soft-deleting a project (`SoftDeletes`) preserves all child tasks and invitations in the database while hiding them from active navigation.
  - Only the Project Owner can permanently destroy or restore an archived project.

---

### Module 3: Team Invitations & Onboarding Flow

- **FR-3.1: Invitation Creation & Dispatch**
  - Project Owners and Admins can invite team members by entering:
    - Recipient's email address.
    - Assigned Project Role (`admin`, `member`, `viewer`).
    - Optional personal invitation message.
  - Business validations:
    - Recipient must not already be an active member of the project.
    - Recipient must not have a pending, unexpired invitation for the same project.
  - System generates a cryptographically secure 64-character token via `bin2hex(random_bytes(32))`.
  - The SHA-256 hash of the token (`hash('sha256', $plainToken)`) is stored in the `invitations` table alongside an expiration timestamp set to 7 days (168 hours) from generation.
  - An asynchronous queued mailable (`ProjectInvitationMail`) is dispatched to the recipient with the raw unhashed token embedded in a signed URL: `/invitations/{token}`.

- **FR-3.2: Invitation Inspection & Acceptance Flow**
  - When the recipient navigates to `/invitations/{token}`:
    - System queries the hashed token; if not found or `expires_at < now()`, renders an informative `InvitationExpiredOrInvalid` view with an option to request a new invite.
    - If valid, renders the Invitation Landing page displaying project name, inviter's name, assigned role, and project description.
  - **Path A: User is already logged in with matching email:**
    - Single-click "Accept Invitation" button.
    - Inserts `project_user` pivot record with the assigned role.
    - Updates invitation status to `accepted` and sets `accepted_at = now()`.
    - Redirects directly to the project dashboard with a welcome flash alert.
  - **Path B: User is already logged in with a different email:**
    - Displays clear warning that the invitation was addressed to a different email address.
    - Provides options to log out and switch accounts or link the invitation.
  - **Path C: User does not have an account:**
    - User is presented with a streamlined registration form with their email pre-filled and locked (read-only).
    - Upon entering their name and password, the user account is created, email is marked verified automatically, project membership is attached, invitation is marked `accepted`, and user is authenticated directly into the project.

- **FR-3.3: Invitation Management & Revocation**
  - Project Admins and Owners can view all `Pending`, `Accepted`, and `Expired` invitations in the Project Settings -> Team tab.
  - Admins can revoke (delete) a pending invitation at any time before acceptance, instantly invalidating the token.
  - Admins can trigger a "Resend Invitation" action, which extends the expiration by 7 days and re-queues the notification email.

---

### Module 4: Task Lifecycle & Collaboration

- **FR-4.1: Task Schema & Creation**
  - Tasks always belong to a single project (`project_id`).
  - Required fields:
    - `title`: String (max 255 chars, required).
    - `description`: Text (Markdown supported, optional).
    - `priority`: Enum [`low`, `medium`, `high`, `urgent`], default `medium`.
    - `status`: Enum [`todo`, `in_progress`, `in_review`, `completed`], default `todo`.
    - `assigned_to_id`: Foreign key referencing `users.id` (nullable). Must belong to the project.
    - `due_date`: Date or DateTime (nullable).
    - `order_index`: Integer for vertical card sorting within board columns.

- **FR-4.2: Assignee Validation & Notifications**
  - The form request `StoreTaskRequest` validates that `assigned_to_id` exists in the `project_user` pivot table for the given project.
  - If a task is assigned or reassigned to a user other than the creator, an asynchronous `TaskAssignedNotification` is dispatched via database notification and transactional email.

- **FR-4.3: Status Progression & State Machine**
  - Tasks transition deterministically through statuses:
    1. `todo` (Backlog / Ready for pickup)
    2. `in_progress` (Under active development)
    3. `in_review` (Awaiting review, QA, or pull request merge)
    4. `completed` (Work finalized and approved)
  - Whenever a task transitions to `completed`:
    - The `completed_at` timestamp is set to `now()`.
  - If a task is moved backward from `completed` to an active state:
    - The `completed_at` timestamp is cleared (`null`).
  - Status updates can be triggered via drag-and-drop on the Kanban board (submitting an AJAX/Fetch PATCH request) or via the task detail modal dropdown.

- **FR-4.4: Task Filtering & Views**
  - **Kanban View:** Visual 4-column board with draggable cards, priority badges, due date warnings (red text when `due_date < today`), and assignee avatar.
  - **List View:** Dense, sortable table supporting filters:
    - Filter by Assignee (All, Unassigned, Assigned to Me, Specific Member).
    - Filter by Priority (`low`, `medium`, `high`, `urgent`).
    - Filter by Status.
    - Search by Keyword (matches title or description).

---

## 4. Non-Functional Requirements (NFR)

### 4.1 Security & Compliance
- **CSRF Defense:** Every state-altering HTTP request (`POST`, `PUT`, `PATCH`, `DELETE`) is guarded by Laravel's `VerifyCsrfToken` middleware.
- **SQL Injection Prevention:** 100% of queries executed through Eloquent ORM or parameterized PDO statements. Zero raw string interpolation.
- **Cross-Site Scripting (XSS):** Blade's default `{{ $var }}` HTML escaping engine is enforced throughout. User-submitted markdown in descriptions is parsed using a hardened Markdown converter paired with HTML Purifier (stripping `<script>`, `<iframe>`, inline event handlers, and javascript pseudo-protocols).
- **Cryptographic Token Storage:** Plaintext invitation tokens are never saved to the database. Only SHA-256 digests are stored, preventing token harvesting if a database dump is compromised.
- **Rate Limiting:**
  - Invitation dispatch: 10 invitations per hour per user.
  - Token acceptance attempts: 10 attempts per minute per IP.
  - General API/Web mutating routes: 60 requests per minute per authenticated user.

### 4.2 Multi-Project Data Isolation & Authorization
- **Tenant Isolation Principle:** A project represents an isolated administrative boundary. No user can read, query, update, or delete tasks belonging to a project they are not an active member of.
- **Policy Enforcement:**
  - Every controller method invokes `$this->authorize()` referencing `ProjectPolicy` or `TaskPolicy`.
  - Automatic Scoped Route Model Binding:
    ```php
    Route::scopeBindings()->group(function () {
        Route::resource('projects.tasks', TaskController::class);
    });
    ```
    This guarantees that requesting `/projects/1/tasks/99` will return an automatic HTTP 404 if Task 99 belongs to Project 2.

### 4.3 Performance & Scalability
- **Page Response Budget:** Server response times under 200ms at the 95th percentile (P95) for dashboard, kanban, and list views.
- **Elimination of N+1 Queries:** Strict eager loading enforced in local development via `Model::preventLazyLoading(! app()->isProduction())`.
- **Database Indexing:**
  - Composite indexes on `tasks(project_id, status, order_index)` for lightning-fast board rendering.
  - Composite indexes on `tasks(project_id, assigned_to_id)` for quick user workload filtering.
  - Unique composite index on `project_user(project_id, user_id)` preventing duplicate memberships.
  - Unique index on `invitations(token_hash)`.

### 4.4 Reliability & Fault Tolerance
- **Database Transactions:** All multi-step database mutations (such as project creation with owner attachment, or invitation acceptance with membership attachment) run inside `DB::transaction()` blocks.
- **Asynchronous Mail Queuing:** Transactional email sending is never executed synchronously during the HTTP request cycle; all mailables implement `ShouldQueue` and are handled by worker daemons.
- **Graceful Error Handling:** Production environment masks database exceptions, serving customized HTTP 403, 404, and 500 Blade error views adhering to the SaaS design system.

