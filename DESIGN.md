# UI/UX & Design System Specification

## Project: CollabTask - Multi-Project & Team Task Management System
- **Document Version:** 1.0.0
- **Aesthetic Direction:** Modern, sleek, high-density SaaS (inspired by Linear, Raycast, and GitHub Projects)
- **Frontend Stack:** Tailwind CSS 3.4+, Blade Components, Alpine.js, Inter / Plus Jakarta Sans Typography

---

## 1. UI/UX Theme & Aesthetic Philosophy

CollabTask is designed for speed, clarity, and sustained focus. The visual interface strikes a careful balance between high information density and airy visual hierarchy. 

### Key Principles:
1. **Subtle Elevation over Heavy Skeuomorphism:** Flat neutral surfaces layered with hairline borders (`border-slate-200 / border-slate-700`) and soft, ambient drop shadows (`shadow-sm`, `shadow-md`).
2. **Deterministic Semantic Color Coding:** Colors are never purely decorative; they communicate project context, priority urgency, and lifecycle progression unambiguously.
3. **Typography First:** High legibility with crisp font hierarchy utilizing tabular numbers for dates, metrics, and badges.
4. **Fluid Responsiveness:** Seamless transitions from multi-column desktop Kanban boards to touch-friendly mobile bottom-sheet drawers and vertical task streams.

---

## 2. Design Tokens & Color Palette

### 2.1 Neutral Surfaces & Backgrounds

| Token | Class Name | Light Mode Hex | Dark Mode Hex | Usage |
|---|---|:---:|:---:|---|
| **App Canvas** | `bg-surface-canvas` | `#F8FAFC` (`slate-50`) | `#0B0F19` (`slate-950`) | Global page backdrop behind board columns |
| **Surface Card** | `bg-surface-card` | `#FFFFFF` (`white`) | `#1E293B` (`slate-800`) | Task cards, modal bodies, dropdown menus |
| **Sidebar / Chrome** | `bg-surface-sidebar` | `#FFFFFF` (`white`) | `#0F172A` (`slate-900`) | Fixed left sidebar, navigation bar |
| **Subtle Neutral** | `bg-surface-muted` | `#F1F5F9` (`slate-100`) | `#334155` (`slate-700`) | Kanban column track background, table headers |
| **Hairline Border** | `border-subtle` | `#E2E8F0` (`slate-200`) | `#1E293B` (`slate-800`) | Card borders, divider rules, inputs |
| **Hover Border** | `border-hover` | `#CBD5E1` (`slate-300`) | `#475569` (`slate-600`) | Focused inputs, card hover state |

### 2.2 Typography & Text Colors

- **Font Family:** `font-sans` configured to `Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif`.
- **Text Tokens:**
  - **Primary Text:** `text-slate-900` (`#0F172A`) / Dark: `text-slate-100` (`#F1F5F9`)
  - **Secondary / Supporting Text:** `text-slate-600` (`#475569`) / Dark: `text-slate-400` (`#94A3B8`)
  - **Muted / Timestamp Text:** `text-slate-400` (`#94A3B8`) / Dark: `text-slate-500` (`#64748B`)

### 2.3 Primary Accent Palette (Brand)

The brand utilizes an authoritative, modern Indigo palette:

```css
--brand-50:  #EEF2FF;
--brand-100: #E0E7FF;
--brand-500: #6366F1;
--brand-600: #4F46E5; /* Primary Button & Active State */
--brand-700: #4338CA; /* Button Hover State */
--brand-900: #312E81;
```

- Primary Button: `bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition-all focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2`
- Active Sidebar Link: `bg-indigo-50 text-indigo-700 font-semibold border-r-2 border-indigo-600`

### 2.4 Semantic Priority Badges

Priority badges convey operational urgency without overwhelming the card layout:

| Priority | Background | Text Color | Border Color | Visual Indicator |
|---|---|---|---|---|
| **Low** | `bg-slate-100` | `text-slate-700` | `border-slate-200` | Blue-gray circle icon / arrow down |
| **Medium** | `bg-blue-50` | `text-blue-700` | `border-blue-200` | Equal icon / horizontal bar |
| **High** | `bg-amber-50` | `text-amber-800` | `border-amber-200` | Upward chevron / warning triangle |
| **Urgent** | `bg-rose-50` | `text-rose-700` | `border-rose-200` | Pulsing red dot / exclamation mark |

#### Blade Component Token Reference (`<x-priority-badge :priority="$priority" />`):
```html
@php
$classes = match($priority) {
    \App\Enums\TaskPriority::LOW => 'bg-slate-50 text-slate-600 border-slate-200 ring-slate-500/10',
    \App\Enums\TaskPriority::MEDIUM => 'bg-blue-50 text-blue-700 border-blue-200 ring-blue-600/10',
    \App\Enums\TaskPriority::HIGH => 'bg-amber-50 text-amber-800 border-amber-200 ring-amber-600/10',
    \App\Enums\TaskPriority::URGENT => 'bg-rose-50 text-rose-700 border-rose-200 ring-rose-600/20 animate-pulse',
};
@endphp
<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-xs font-medium border ring-1 ring-inset {{ $classes }}">
    {{ $priority->label() }}
</span>
```

### 2.5 Semantic Status Column & Badge Colors

| Status | Column Header Accent | Badge Classes | Description |
|---|---|---|---|
| **Todo** | `bg-slate-400` | `bg-slate-100 text-slate-800 border-slate-200` | Unstarted, queued items |
| **In-Progress** | `bg-sky-500` | `bg-sky-50 text-sky-700 border-sky-200` | Active execution |
| **In-Review** | `bg-indigo-500` | `bg-indigo-50 text-indigo-700 border-indigo-200` | PR opened / QA inspection |
| **Completed** | `bg-emerald-500` | `bg-emerald-50 text-emerald-700 border-emerald-200` | Successfully delivered & closed |

---

## 3. Component Blueprints & Wireframes

### 3.1 App Shell & Navigation Layout

The application employs an asymmetric 2-column layout: a collapsible 260px fixed sidebar paired with a responsive, scrollable main content viewport.

```
+-----------------------------------------------------------------------------------------+
| [CollabTask Logo]     | Search (Ctrl+K)              [+ New Task]  [Bell]  [Alex Rivera] |
+-----------------------+-----------------------------------------------------------------+
| [Project Switcher v]  | Projects > Mobile App V2 > Kanban Board                         |
|   * Mobile App V2 (#) | Filters: [Assignee: All v] [Priority: All v]  [+ View: Board|List] |
|   * Marketing Q4   (#)|-----------------------------------------------------------------|
|                       |  TODO (4)      | IN-PROGRESS (2) | IN-REVIEW (1) | COMPLETED (8) |
| NAVIGATION            | +------------+ | +-------------+ | +-----------+ | +-----------+ |
|   [] Dashboard        | | Card #1    | | | Card #5     | | | Card #7   | | | Card #8   | |
|   [] Kanban Board     | | High Badge | | | Urgent Badge| | | Med Badge | | | Low Badge | |
|   [] List View        | | [Avatar]   | | | [Avatar]    | | | [Avatar]  | | | [Avatar]  | |
|   [] Team Members (6) | +------------+ | +-------------+ | +-----------+ | +-----------+ |
|   [] Project Settings | +------------+ |                 |               |               |
|                       | | Card #2    | |                 |               |               |
| TEAM SHORTLIST        | | Med Badge  | |                 |               |               |
|   (o) Sarah (Admin)   | +------------+ |                 |               |               |
|   (o) Alex (Dev)      |                |                 |               |               |
|   (+) Invite Member   |                |                 |               |               |
+-----------------------+-----------------------------------------------------------------+
```

#### Blueprint: Project Switcher Dropdown
- Located at the pinnacle of the left sidebar.
- Displays the current project's colored dot badge, project title, and role badge.
- Clicking expands an Alpine.js animated dropdown containing:
  - Searchable list of all user-affiliated projects.
  - Active checkmark next to the current project context.
  - Divider followed by a `+ Create New Project` action button that opens the Project Creation Modal.

#### Blueprint: Navigation Sidebar
- Quick links with feather-weight SVG icons:
  - **Overview / Dashboard** (`heroicon-o-home`)
  - **Board (Kanban)** (`heroicon-o-view-columns`)
  - **Task List** (`heroicon-o-bars-3-bottom-left`)
  - **Team Roster** (`heroicon-o-user-group`)
  - **Project Settings** (`heroicon-o-cog-6-tooth`) (Visible only to Owners/Admins)
- Bottom-docked profile widget featuring user avatar, email, notification status, and account settings toggle.

---

### 3.2 Kanban Board & Task Card Blueprint

#### Kanban Column Specification:
- Container: `flex flex-col flex-shrink-0 w-80 bg-slate-100/70 rounded-xl p-3 border border-slate-200/80`
- Column Header:
  - Color indicator dot representing status.
  - Column name (e.g., `In-Progress`).
  - Item count badge (`rounded-full bg-slate-200 px-2 py-0.5 text-xs font-semibold text-slate-700`).
  - Quick add button (`+`) invoking task creation pre-selected to that status.

#### Task Card Specification:
- Card Container: `group relative bg-white p-4 rounded-lg border border-slate-200 shadow-sm hover:shadow-md hover:border-slate-300 transition-all cursor-grab active:cursor-grabbing`
- Top Row: Priority badge (left-aligned) alongside due date indicator (right-aligned).
  - Normal due date: `text-xs text-slate-500 font-medium`
  - Overdue date: `text-xs text-rose-600 font-bold bg-rose-50 px-1.5 py-0.5 rounded border border-rose-200`
- Title: `text-sm font-semibold text-slate-900 group-hover:text-indigo-600 transition-colors line-clamp-2 mt-2`
- Description snippet: `text-xs text-slate-500 line-clamp-2 mt-1`
- Footer Row (flex items-center justify-between border-t border-slate-100 pt-3 mt-3):
  - Task reference identifier: `text-xs font-mono text-slate-400` (e.g., `#TASK-104`)
  - Assignee Avatar: 24x24px rounded-full avatar circle displaying profile image or uppercase initials. If unassigned, displays a dashed outline placeholder `+ Assign`.

---

### 3.3 Modal Blueprints

All modals share an accessible Alpine.js backdrop overlay with smooth scale and opacity animations (`x-transition:enter="ease-out duration-200"`), keyboard escape listening, and focus trapping.

#### 1. Create / Edit Project Modal
```html
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
  <div class="w-full max-w-lg bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
      <h3 class="text-lg font-bold text-slate-900">Create New Project</h3>
      <button class="text-slate-400 hover:text-slate-600">&times;</button>
    </div>
    <form class="p-6 space-y-4">
      <div>
        <label class="block text-xs font-semibold uppercase text-slate-500 tracking-wider mb-1">Project Name *</label>
        <input type="text" placeholder="e.g. Mobile App Redesign" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
      </div>
      <div>
        <label class="block text-xs font-semibold uppercase text-slate-500 tracking-wider mb-1">Description</label>
        <textarea rows="3" placeholder="Brief project scope, goals and context..." class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"></textarea>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold uppercase text-slate-500 tracking-wider mb-1">Accent Color</label>
          <div class="flex items-center gap-2">
            <!-- 5 Color Swatches (Indigo, Sky, Emerald, Amber, Rose) + Hex Input -->
            <input type="color" value="#4F46E5" class="h-9 w-9 rounded border border-slate-300 cursor-pointer">
            <span class="text-xs font-mono text-slate-500">#4F46E5</span>
          </div>
        </div>
        <div>
          <label class="block text-xs font-semibold uppercase text-slate-500 tracking-wider mb-1">Target Due Date</label>
          <input type="date" class="w-full px-3.5 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm">
        </div>
      </div>
      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg">Cancel</button>
        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm">Create Project</button>
      </div>
    </form>
  </div>
</div>
```

#### 2. Create / Edit Task Modal
- Header: Displays project name breadcrumb + modal action title (`New Task in [Project Name]`).
- Fields:
  - **Task Title:** Full-width autofocus text input.
  - **Description:** Markdown-capable text editor with preview toggle.
  - **Status & Priority Row:** Dual 2-column segmented picker.
  - **Assignee Selector:** Searchable dropdown listing project members with avatar, full name, and email. Includes option for `Unassigned`.
  - **Due Date:** Native HTML5 date picker with clear quick-actions (`Today`, `Tomorrow`, `Next Week`).
- Footer: "Create & Add Another" secondary action button alongside primary "Create Task".

#### 3. Team Member Invitation Modal
- Purpose: Dispatches cryptographic email invitations to join the project workspace.
- Fields:
  - **Email Address:** Required email input validated against RFC standards.
  - **Project Role Selector:** Radio card selection:
    - `Admin`: Full management over project tasks, settings, and member invitations.
    - `Member` (Default): Can create, update, assign, and transition tasks.
    - `Viewer`: Read-only access to Kanban boards and task lists.
  - **Personal Invitation Note:** Optional short greeting displayed in the invitation email.
- Pending Roster Sub-table:
  - Shows recent invitations for the project with status pills (`Pending`, `Accepted`, `Expired`), token expiration countdown, and a "Resend" or "Revoke" button.

