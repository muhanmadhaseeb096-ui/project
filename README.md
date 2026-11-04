# CollabTask - Multi-Project & Team Task Management System

[![Laravel Version](https://img.shields.io/badge/Laravel-11.x%20%2F%2012.x-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.3%20%7C%208.4-777BB4?style=for-the-badge&logo=php)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.4+-38B2AC?style=for-the-badge&logo=tailwind-css)](https://tailwindcss.com)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](LICENSE)

**CollabTask** is an enterprise-grade, multi-tenant capable Project and Task Management platform architected with **Laravel**, **Blade Templates**, **Tailwind CSS**, and **MySQL**. It empowers organizations and cross-functional teams to manage multiple concurrent projects, coordinate workflows through granular task lifecycles, and onboard collaborators via secure cryptographic email invitations.

---

## Table of Contents

- [Core Capabilities](#core-capabilities)
- [Architecture & Tech Stack](#architecture--tech-stack)
- [Prerequisites](#prerequisites)
- [Step-by-Step Local Setup](#step-by-step-local-setup)
- [Environment Configuration](#environment-configuration)
- [Database Migrations & Seeders](#database-migrations--seeders)
- [Running Background Queues & Assets](#running-background-queues--assets)
- [Project Directory Structure](#project-directory-structure)
- [Testing & Quality Assurance](#testing--quality-assurance)
- [GitHub Contribution Guidelines](#github-contribution-guidelines)
- [Security & Vulnerability Disclosure](#security--vulnerability-disclosure)
- [License](#license)

---

## Core Capabilities

1. **Multi-Project Workspace Engine**
   - Independent project spaces with strict tenant-level authorization.
   - Project metadata tracking: brand color accents, target deadlines, team rosters, and custom slugs.
   - Global project switcher preserving user navigation context.

2. **Cryptographic Team Invitation Workflow**
   - Asynchronous invitation dispatch via transactional email.
   - 64-character high-entropy cryptographic invitation tokens hashed via SHA-256 at rest.
   - Dual-path acceptance flow: seamless onboarding for existing registered users and guided registration for new invitees with automated project membership linking.

3. **Granular Task Lifecycle Management**
   - Strict project scoping ensuring tasks cannot bleed across project boundaries.
   - 4-Tier Priority Matrix: `Low`, `Medium`, `High`, `Urgent`.
   - 4-Stage State Machine: `Todo`, `In-Progress`, `In-Review`, `Completed` with automatic timestamp capture upon completion.
   - Member assignment validation enforcing that assignees must hold an active project membership.
   - Dual interface support: High-density interactive Kanban board and structured tabular List view.

---

## Architecture & Tech Stack

- **Backend Framework:** Laravel 11.x / 12.x (PHP 8.3 / 8.4)
- **Frontend Architecture:** Laravel Blade Templating Engine with atomic, reusable components
- **Styling & Design System:** Tailwind CSS 3.4+ configured with custom semantic design tokens
- **Micro-Interactions & Modals:** Alpine.js 3.x / Modern Vanilla JavaScript
- **Build Toolchain:** Vite 6.x
- **Relational Database:** MySQL 8.0+ / MariaDB 10.11+ (InnoDB engine, `utf8mb4_unicode_ci`)
- **Queue & Mail Subsystem:** Redis / Database Queue driver paired with SMTP/Resend/Mailgun mail transports
- **Code Quality:** Laravel Pint (PSR-12), PHPStan / Larastan (Level 8+), PHPUnit 11+

---

## Prerequisites

Ensure your local development environment meets the following baseline requirements:

| Tool / Runtime | Minimum Version | Recommended |
|---|---|---|
| **PHP** | `^8.3` | `8.4.x` (with `pdo_mysql`, `mbstring`, `openssl`, `bcmath`, `curl`, `xml`, `zip`) |
| **Composer** | `^2.6` | `2.8.x` |
| **Node.js** | `^20.0` (LTS) | `22.x` |
| **npm** | `^10.0` | `10.8.x` |
| **MySQL** | `^8.0` | `8.0.36+` |

---

## Step-by-Step Local Setup

Follow these commands in sequence to establish a fully working local development instance:

### 1. Clone the Repository

```bash
git clone https://github.com/your-org/collab-task.git
cd collab-task
```

### 2. Install PHP Dependencies

Install all production and development dependencies via Composer:

```bash
composer install
```

### 3. Initialize Environment File

Copy the template environment file and create your local configuration:

```bash
cp .env.example .env
```

### 4. Generate Application Encryption Key

Generate a 32-byte AES-256 application key:

```bash
php artisan key:generate
```

### 5. Configure Local Database & Services

Open `.env` in your editor and update the database and application settings:

```env
APP_NAME="CollabTask"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=collab_task
DB_USERNAME=root
DB_PASSWORD=secret

QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database

MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="no-reply@collabtask.test"
MAIL_FROM_NAME="${APP_NAME}"
```

> **Tip:** For local email testing, use Mailpit (`http://localhost:8025`) or configure `MAIL_MAILER=log` to inspect invitation emails directly in `storage/logs/laravel.log`.

### 6. Execute Database Migrations and Seeders

Ensure MySQL is running and the database exists, then run the schema migrations along with dummy data:

```bash
# Create database if not existing (optional CLI helper)
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS collab_task CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Execute migrations with comprehensive demo seeders
php artisan migrate --seed
```

The database seeder creates standard demonstration credentials:
- **Admin / Project Owner:** `owner@collabtask.test` (Password: `password`)
- **Collaborator / Member:** `member@collabtask.test` (Password: `password`)

### 7. Install Frontend Dependencies & Compile Assets

Install JavaScript and CSS packages, then start the Vite hot-module-replacement (HMR) server:

```bash
npm install
npm run dev
```

### 8. Start Background Queue Worker

Open a dedicated terminal window to process asynchronous email invitations and task assignment alerts:

```bash
php artisan queue:work --tries=3 --timeout=90
```

### 9. Serve the Application

In another terminal, start the PHP development server:

```bash
php artisan serve
```

Access the application at: [http://localhost:8000](http://localhost:8000)

---

## Project Directory Structure

```
collab-task/
├── app/
│   ├── Enums/                     # Backed PHP Enums (TaskStatus, TaskPriority, ProjectRole)
│   ├── Http/
│   │   ├── Controllers/           # Thin controllers orchestrating requests
│   │   │   ├── Auth/              # Authentication controllers (Breeze/Custom)
│   │   │   ├── ProjectController.php
│   │   │   ├── TaskController.php
│   │   │   └── InvitationController.php
│   │   ├── Requests/              # Form Requests containing authorization & rules
│   │   │   ├── Project/           # StoreProjectRequest, UpdateProjectRequest
│   │   │   ├── Task/              # StoreTaskRequest, UpdateTaskStatusRequest
│   │   │   └── Invitation/        # StoreInvitationRequest, AcceptInvitationRequest
│   │   └── Middleware/            # Multi-project context & permission filters
│   ├── Mail/                      # Mailable classes (ProjectInvitationMail, TaskAssignedMail)
│   ├── Models/                    # Eloquent models (User, Project, Task, Invitation, ProjectUser)
│   ├── Policies/                  # Granular authorization policies (ProjectPolicy, TaskPolicy)
│   └── Services/                  # Core domain logic (ProjectService, TaskService, InvitationService)
├── bootstrap/                     # Application bootstrap and middleware pipeline
├── config/                        # Framework & custom package configuration
├── database/
│   ├── factories/                 # Eloquent factories for realistic model generation
│   ├── migrations/                # Strongly-typed schema migrations with foreign key cascades
│   └── seeders/                   # Seeders populating projects, tasks, and users
├── resources/
│   ├── css/                       # Tailwind CSS input and custom utility layers
│   ├── js/                        # Alpine.js setup, drag-and-drop kanban handlers
│   └── views/
│       ├── components/            # Atomic Blade components (modal, badge, button, task-card)
│       ├── layouts/               # Master AppShell, Guest, and Navigation layouts
│       ├── projects/              # Project index, show, settings views
│       ├── tasks/                 # Kanban board, list view, task detail view
│       └── invitations/           # Invitation landing and acceptance views
├── routes/
│   ├── web.php                    # Application web routes grouped by auth & verified
│   └── console.php                # Artisan commands and scheduled tasks
├── storage/                       # Application logs, file attachments, and cache
├── tests/
│   ├── Feature/                   # Comprehensive end-to-end HTTP tests
│   └── Unit/                      # Isolated service, enum, and policy tests
└── vite.config.js                 # Vite asset bundler configuration
```

---

## Testing & Quality Assurance

Run the automated test suite to verify project health, role authorization, and data isolation:

```bash
# Run all PHPUnit test suites
php artisan test

# Run tests in parallel with compact output
php artisan test --parallel --compact

# Run specific domain tests
php artisan test --filter=ProjectTest
php artisan test --filter=InvitationTest
php artisan test --filter=TaskLifecycleTest

# Code style linting and formatting with Laravel Pint
./vendor/bin/pint --format agent
```

---

## GitHub Contribution Guidelines

We enforce rigorous engineering standards to maintain system stability, security, and clean architecture.

### Branching Model

- `main`: Production-ready branch. Direct commits are strictly prohibited.
- `develop`: Integration branch for accepted pull requests.
- Feature branches: `feature/{issue-number}-{short-description}` (e.g., `feature/102-task-due-date-filter`)
- Bugfix branches: `fix/{issue-number}-{short-description}` (e.g., `fix/108-invitation-token-expiry`)
- Hotfix branches: `hotfix/{version}` (e.g., `hotfix/1.0.1`)

### Conventional Commits Format

Every commit message must follow the [Conventional Commits](https://www.conventionalcommits.org/) specification:

- `feat(tasks)`: Add drag-and-drop status update endpoint
- `fix(invitations)`: Prevent replay attacks on accepted invitation tokens
- `refactor(services)`: Extract task assignee notification into isolated event listener
- `test(projects)`: Add feature test for tenant isolation between unshared projects
- `docs(readme)`: Update MySQL prerequisites and local setup instructions

### Pull Request Checklist

Before submitting a Pull Request, ensure:

1. [ ] A corresponding issue exists and is linked (`Closes #123`).
2. [ ] All migrations include reversible `down()` methods and appropriate cascade constraints.
3. [ ] All business logic resides in Services; Controllers remain thin.
4. [ ] Every mutating endpoint has an accompanying Form Request with strict validation.
5. [ ] Policies guard every resource operation (`$this->authorize()`).
6. [ ] Automated tests are written and passing with 100% policy test coverage.
7. [ ] `./vendor/bin/pint` has been executed with zero style violations.
8. [ ] No raw queries are introduced; Eloquent eager loading is leveraged to prevent N+1 queries.

---

## Security & Vulnerability Disclosure

Security is treated as a first-class citizen. All input is sanitized, output is escaped by default via Blade, CSRF tokens are verified on all state-altering requests, and rate-limiting is enforced on sensitive endpoints.

If you identify a security vulnerability, please do **NOT** open a public issue. Email our architectural security team directly at **security@collabtask.test**.

---

## License

The CollabTask application is open-sourced software licensed under the [MIT License](LICENSE).
