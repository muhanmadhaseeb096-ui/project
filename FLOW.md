# System & Architectural Data Flow

## Project: CollabTask - Multi-Project & Team Task Management System
- **Document Version:** 1.0.0
- **Scope:** Runtime lifecycle, end-to-end data orchestration, and user interaction sequence models
- **Target Audience:** Full-Stack Engineers, System Architects, API Integrators

---

## 1. Architectural Data Flow: The Request Lifecycle

Every state-altering HTTP transaction in CollabTask traverses a deterministic five-tier pipeline:

```
[ Browser Client ]
        │  1. HTTP POST/PUT/PATCH (Form submission or Fetch API call)
        ▼
[ Routing & Global Middleware ]
        │  2. CSRF Token verification (`VerifyCsrfToken`)
        │  3. User Session Authentication (`auth`)
        │  4. Email Verification Check (`verified`)
        ▼
[ Form Request Layer ]
        │  5. Context Authorization: `authorize()` evaluates Model Policies / Gates
        │  6. Input Validation: `rules()` sanitizes & enforces integrity (enums, dates, DB lookups)
        ▼
[ Thin Controller ]
        │  7. Accepts typed `FormRequest` with guaranteed sanitized payload (`$request->validated()`)
        │  8. Injects Domain Service via Dependency Injection container
        │  9. Delegates work: `$taskService->createTask($project, $payload, auth()->user())`
        ▼
[ Domain Service Layer ]
        │ 10. Opens Database Transaction: `DB::transaction()`
        │ 11. Executes atomic multi-model Eloquent operations
        │ 12. Dispatches asynchronous domain Events / Queued Mailables (`Mail::queue()`)
        │ 13. Commits Database Transaction
        ▼
[ Presentation & Response ]
        │ 14. Controller returns `RedirectResponse` with status flash or Blade View
        ▼
[ Browser Client ]
        │ 15. DOM update via Blade or Alpine.js with visual flash confirmation
```

---

## 2. Sequence Diagram: Architectural Pipeline Walkthrough

The following sequence diagram details an authenticated user submitting a task creation request:

```mermaid
sequenceDiagram
    autonumber
    actor User as Team Member
    participant Web as Web Route & Middleware
    participant Req as StoreTaskRequest
    participant Ctrl as TaskController
    participant Policy as TaskPolicy
    participant Svc as TaskService
    participant DB as MySQL Database
    participant Queue as Laravel Queue Worker
    participant Mail as Mail Server (SMTP)

    User->>Web: POST /projects/{project}/tasks (Title, Priority, Assignee)
    Web->>Web: Verify CSRF, Authenticate Session
    Web->>Req: Instantiate StoreTaskRequest
    Req->>Policy: can('create', [Task::class, project])
    Policy-->>Req: true (User belongs to project)
    Req->>Req: Validate inputs & verify assignee is project member
    Req->>Ctrl: store(StoreTaskRequest, Project, TaskService)
    Ctrl->>Svc: createTask(project, validatedData, authUser)
    
    rect rgb(240, 245, 255)
        Note over Svc,DB: DB::transaction() Boundary
        Svc->>DB: Query current max(order_index) for target column
        DB-->>Svc: 3000
        Svc->>DB: INSERT INTO tasks (project_id, title, priority, order_index, ...)
        DB-->>Svc: Task Instance (ID: 104)
        Svc->>Queue: Dispatch TaskAssignedMail(task)
    end

    Svc-->>Ctrl: Return Task Model
    Ctrl-->>User: Redirect to /projects/{project}/tasks with Flash Success
    
    Queue->>Mail: Process queued job: Deliver email to assignee
    Mail-->>User: Inbound email notification arrives
```

---

## 3. User Interaction Flow 1: Project Creation & Initial Setup

This flow outlines how a project owner initiates a new project container, establishes ownership, and redirects to their new workspace.

```mermaid
sequenceDiagram
    autonumber
    actor Owner as Project Owner
    participant UI as Blade Project Modal (Alpine.js)
    participant Ctrl as ProjectController
    participant Svc as ProjectService
    participant DB as MySQL Database

    Owner->>UI: Clicks "+ New Project" in Sidebar
    UI-->>Owner: Displays Modal with Name, Description, Color, Due Date
    Owner->>UI: Fills Form & clicks "Create Project"
    UI->>Ctrl: POST /projects (StoreProjectRequest)
    Ctrl->>Svc: createProject(validatedData, owner)

    rect rgb(245, 255, 245)
        Note over Svc,DB: Atomic Project & Ownership Generation
        Svc->>Svc: Generate URL-safe slug: Str::slug(name)
        Svc->>DB: INSERT INTO projects (owner_id, name, slug, color, status, ...)
        DB-->>Svc: Project #42
        Svc->>DB: INSERT INTO project_user (project_id, user_id, role, joined_at) VALUES (42, owner.id, 'owner', now())
    end

    Svc-->>Ctrl: Project #42 instance
    Ctrl-->>Owner: 302 Redirect -> /projects/42/board
    Note over Owner: Active Workspace Context automatically switched to Project #42
```

### Edge Cases & Exception Handling:
- **Duplicate Slug:** If the owner already owns a project with the same name, the slug generator appends a random increment (e.g., `mobile-app-1`).
- **Validation Failure:** If required fields are omitted, the form re-renders with standard Laravel validation error bags (`$errors->get('name')`) and inputs preserved (`old('name')`).

---

## 4. User Interaction Flow 2: Team Member Invitation & Cryptographic Acceptance

This flow illustrates how new collaborators are securely onboarded without pre-generating accounts.

```mermaid
sequenceDiagram
    autonumber
    actor Admin as Project Admin / Owner
    actor Invitee as Team Invitee (External)
    participant UI as Project Team Settings
    participant InvCtrl as InvitationController
    participant InvSvc as InvitationService
    participant DB as MySQL Database
    participant Queue as Mail Queue
    participant GuestUI as Invitation Landing View

    Admin->>UI: Inputs email (alex@dev.test) & role ('member')
    UI->>InvCtrl: POST /projects/{project}/invitations (StoreInvitationRequest)
    InvCtrl->>InvSvc: issueInvitation(project, email, role, adminUser)

    rect rgb(255, 250, 240)
        Note over InvSvc: Token Cryptography
        InvSvc->>InvSvc: $plain = bin2hex(random_bytes(32)) (64 hex characters)
        InvSvc->>InvSvc: $hash = hash('sha256', $plain)
        InvSvc->>DB: INSERT INTO invitations (project_id, email, role, token_hash, expires_at: now + 7d)
        InvSvc->>Queue: Queue ProjectInvitationMail(plainToken, project, adminUser)
    end

    InvSvc-->>InvCtrl: Success
    InvCtrl-->>Admin: Flash "Invitation dispatched to alex@dev.test"
    Queue->>Invitee: Transactional Email: "You're invited to join [Project]! Click here."

    Note over Invitee,GuestUI: Acceptance Phase
    Invitee->>GuestUI: Clicks https://collabtask.test/invitations/{plainToken}
    GuestUI->>InvCtrl: GET /invitations/{plainToken} (show)
    InvCtrl->>DB: SELECT * FROM invitations WHERE token_hash = sha256(plainToken)
    
    alt Token Expired or Not Found
        DB-->>InvCtrl: NULL or expires_at < now()
        InvCtrl-->>Invitee: Render 404 / 410 "Invitation Expired or Invalid" view
    else Token Valid & Unclaimed
        DB-->>InvCtrl: Invitation Record Found
        InvCtrl-->>GuestUI: Render Invitation Acceptance Screen
        
        alt User is Already Logged In
            Invitee->>InvCtrl: POST /invitations/{plainToken}/accept
            InvCtrl->>InvSvc: acceptInvitation(invitation, auth()->user())
            InvSvc->>DB: INSERT INTO project_user (project_id, user_id, role)
            InvSvc->>DB: UPDATE invitations SET status='accepted', accepted_at=now()
            InvCtrl-->>Invitee: 302 Redirect -> /projects/{project}/board
        else User Has No Account
            Invitee->>InvCtrl: POST /invitations/{plainToken}/register (name, password)
            InvCtrl->>InvSvc: registerAndAccept(invitation, registerData)
            InvSvc->>DB: INSERT INTO users (name, email, password, email_verified_at=now())
            InvSvc->>DB: INSERT INTO project_user (project_id, user_id, role)
            InvSvc->>DB: UPDATE invitations SET status='accepted', accepted_at=now()
            InvCtrl-->>Invitee: Authenticate Session & Redirect -> /projects/{project}/board
        end
    end
```

---

## 5. User Interaction Flow 3: Task Lifecycle & Status Transition

Tasks progress deterministically through their states, triggering notifications, updating sorting order, and capturing completion timestamps.

### 5.1 State Machine Diagram

```mermaid
stateDiagram-v2
    [*] --> Todo : Task Created
    Todo --> InProgress : Picked up by Assignee
    InProgress --> InReview : Work Completed / PR Opened
    InReview --> InProgress : Changes Requested
    InReview --> Completed : Approved & Merged
    Todo --> Completed : Direct Close
    Completed --> InProgress : Reopened (completed_at cleared)
    Completed --> [*] : Archived
```

### 5.2 Sequence Diagram: Drag-and-Drop / Inline Status Transition

```mermaid
sequenceDiagram
    autonumber
    actor Dev as Assignee / Contributor
    participant Kanban as Interactive Kanban Board
    participant Ctrl as TaskStatusController
    participant Svc as TaskService
    participant DB as MySQL Database

    Dev->>Kanban: Drags Card #104 from "In-Progress" to "Completed"
    Kanban->>Ctrl: PATCH /projects/42/tasks/104/status { status: "completed", order_index: 1000 }
    
    Ctrl->>Ctrl: Authorize via TaskPolicy ('updateStatus')
    Ctrl->>Svc: transitionTaskStatus(task, 'completed', newOrder)

    rect rgb(240, 255, 240)
        Note over Svc,DB: State Transition & Timestamp Capture
        Svc->>DB: UPDATE tasks SET status = 'completed', completed_at = NOW(), order_index = 1000 WHERE id = 104
        Svc->>Svc: Fire TaskCompletedEvent(task)
    end

    Svc-->>Ctrl: Updated Task Model
    Ctrl-->>Kanban: HTTP 200 JSON { success: true, completed_at: "2026-11-04 09:00:00" }
    Kanban-->>Dev: Card displays green Completed badge & celebration micro-animation
```

---

## 6. Security Scoping & Cross-Tenant Boundary Verification

To guarantee complete multi-project isolation, every request is checked against the following decision tree:

```
                      Inbound Request to /projects/{project}/tasks/{task}
                                              │
                                              ▼
                             Is the User Authenticated?
                                   /             \
                             No   /               \  Yes
                                 ▼                 ▼
                       Redirect to /login    Does User belong to {project}
                                             (via project_user pivot)?
                                                   /             \
                                             No   /               \  Yes
                                                 ▼                 ▼
                                           HTTP 403 Forbidden    Does {task} belong to {project}
                                                                 (project_id matches)?
                                                                       /             \
                                                                 No   /               \  Yes
                                                                     ▼                 ▼
                                                               HTTP 404 Not Found    Does User have permission
                                                                                     for this action (Policy)?
                                                                                           /             \
                                                                                     No   /               \  Yes
                                                                                         ▼                 ▼
                                                                                   HTTP 403 Forbidden    Execute Request
```

This strict architectural boundary guarantees that even if a malicious user alters an ID parameter in the URL or payload, horizontal data leakage across project boundaries is mathematically impossible.

