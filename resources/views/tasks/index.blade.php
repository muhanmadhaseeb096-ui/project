<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="w-4 h-4 rounded-full flex-shrink-0" style="background-color: {{ $project->color ?? '#4F46E5' }};"></span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $project->name }}</h1>
                    <p class="text-xs text-slate-500 font-mono">/projects/{{ $project->slug }}</p>
                </div>
            </div>

            <!-- Tab Navigation Links -->
            <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800 p-1 rounded-xl text-xs font-semibold">
                <a href="{{ route('projects.tasks.index', [$project, 'view' => 'kanban']) }}" class="px-3 py-1.5 rounded-lg {{ $currentView === 'kanban' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">
                    Kanban Board
                </a>
                <a href="{{ route('projects.tasks.index', [$project, 'view' => 'list']) }}" class="px-3 py-1.5 rounded-lg {{ $currentView === 'list' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">
                    Task List
                </a>
                <a href="{{ route('projects.members', $project) }}" class="px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900">
                    Team ({{ $members->count() }})
                </a>
                @can('update', $project)
                    <a href="{{ route('projects.edit', $project) }}" class="px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900">
                        Settings
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div x-data="{ createTaskModalOpen: false, quickStatus: 'todo' }" class="space-y-6">
        <!-- Control / Filter Bar -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm">
            <!-- Search & Filters Form -->
            <form method="GET" action="{{ route('projects.tasks.index', $project) }}" class="flex flex-wrap items-center gap-3 flex-1">
                <input type="hidden" name="view" value="{{ $currentView }}">

                <!-- Search Input -->
                <div class="relative min-w-[200px] flex-1 sm:flex-none">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search tasks..." class="w-full pl-9 pr-3 py-1.5 rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 text-xs focus:ring-indigo-500 focus:border-indigo-500">
                    <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <!-- Assignee Filter -->
                <select name="assignee" onchange="this.form.submit()" class="rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 text-xs py-1.5 focus:ring-indigo-500">
                    <option value="">All Assignees</option>
                    <option value="me" @selected($selectedAssignee === 'me')>Assigned to Me</option>
                    <option value="unassigned" @selected($selectedAssignee === 'unassigned')>Unassigned</option>
                    @foreach($members as $member)
                        <option value="{{ $member->id }}" @selected($selectedAssignee == $member->id)>{{ $member->name }}</option>
                    @endforeach
                </select>

                <!-- Priority Filter -->
                <select name="priority" onchange="this.form.submit()" class="rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 text-xs py-1.5 focus:ring-indigo-500">
                    <option value="">All Priorities</option>
                    @foreach(\App\Enums\TaskPriority::cases() as $priorityCase)
                        <option value="{{ $priorityCase->value }}" @selected($selectedPriority === $priorityCase->value)>{{ $priorityCase->label() }}</option>
                    @endforeach
                </select>

                @if($search || $selectedAssignee || $selectedPriority)
                    <a href="{{ route('projects.tasks.index', [$project, 'view' => $currentView]) }}" class="text-xs font-semibold text-rose-600 hover:text-rose-700">
                        Clear Filters
                    </a>
                @endif
            </form>

            <!-- New Task Trigger -->
            @can('create', [\App\Models\Task::class, $project])
                <button @click="createTaskModalOpen = true; quickStatus = 'todo'" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow transition flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Task
                </button>
            @endcan
        </div>

        <!-- VIEW 1: KANBAN BOARD -->
        @if($currentView === 'kanban')
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-start pb-8 overflow-x-auto">
                @php
                    $columns = [
                        [
                            'key' => \App\Enums\TaskStatus::TODO->value,
                            'title' => 'To Do',
                            'dotColor' => 'bg-slate-400',
                            'tasks' => $kanbanColumns[\App\Enums\TaskStatus::TODO->value],
                        ],
                        [
                            'key' => \App\Enums\TaskStatus::IN_PROGRESS->value,
                            'title' => 'In Progress',
                            'dotColor' => 'bg-sky-500',
                            'tasks' => $kanbanColumns[\App\Enums\TaskStatus::IN_PROGRESS->value],
                        ],
                        [
                            'key' => \App\Enums\TaskStatus::IN_REVIEW->value,
                            'title' => 'In Review',
                            'dotColor' => 'bg-indigo-500',
                            'tasks' => $kanbanColumns[\App\Enums\TaskStatus::IN_REVIEW->value],
                        ],
                        [
                            'key' => \App\Enums\TaskStatus::COMPLETED->value,
                            'title' => 'Completed',
                            'dotColor' => 'bg-emerald-500',
                            'tasks' => $kanbanColumns[\App\Enums\TaskStatus::COMPLETED->value],
                        ],
                    ];
                @endphp

                @foreach($columns as $col)
                    <div class="bg-slate-100/70 dark:bg-slate-900/60 rounded-2xl p-4 border border-slate-200/80 dark:border-slate-800 flex flex-col min-h-[500px]">
                        <!-- Column Header -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full {{ $col['dotColor'] }}"></span>
                                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $col['title'] }}</h3>
                                <span class="px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-800 text-[11px] font-bold text-slate-600 dark:text-slate-400">
                                    {{ $col['tasks']->count() }}
                                </span>
                            </div>

                            @can('create', [\App\Models\Task::class, $project])
                                <button @click="createTaskModalOpen = true; quickStatus = '{{ $col['key'] }}'" title="Add task in {{ $col['title'] }}" class="text-slate-400 hover:text-indigo-600 p-1 rounded-md hover:bg-white dark:hover:bg-slate-800 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </button>
                            @endcan
                        </div>

                        <!-- Card List -->
                        <div class="space-y-3 flex-1">
                            @forelse($col['tasks'] as $task)
                                <div class="group bg-white dark:bg-slate-800/90 rounded-xl p-4 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md hover:border-indigo-300 dark:hover:border-indigo-600 transition space-y-3">
                                    <div class="flex items-center justify-between gap-2">
                                        <x-priority-badge :priority="$task->priority" />
                                        @if($task->due_date)
                                            <span class="text-[11px] font-medium {{ $task->isOverdue() ? 'text-rose-600 font-bold bg-rose-50 dark:bg-rose-950/40 px-1.5 py-0.5 rounded border border-rose-200' : 'text-slate-400' }}">
                                                {{ $task->due_date->format('M d') }}
                                            </span>
                                        @endif
                                    </div>

                                    <a href="{{ route('projects.tasks.show', [$project, $task]) }}" class="block">
                                        <h4 class="text-sm font-semibold text-slate-900 dark:text-white group-hover:text-indigo-600 transition leading-snug line-clamp-2">
                                            {{ $task->title }}
                                        </h4>
                                        @if($task->description)
                                            <p class="text-xs text-slate-500 line-clamp-2 mt-1">{{ Str::limit($task->description, 80) }}</p>
                                        @endif
                                    </a>

                                    <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-700 text-xs">
                                        <!-- Assignee Info -->
                                        <div class="flex items-center gap-1.5">
                                            <x-avatar :user="$task->assignee" size="xs" />
                                            <span class="text-[11px] text-slate-500 truncate max-w-[90px]">
                                                {{ $task->assignee ? $task->assignee->name : 'Unassigned' }}
                                            </span>
                                        </div>

                                        <!-- Quick Status Switcher Dropdown -->
                                        @can('updateStatus', $task)
                                            <form method="POST" action="{{ route('projects.tasks.status', [$project, $task]) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <select name="status" onchange="this.form.submit()" class="text-[11px] py-0.5 px-2 rounded-lg border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-slate-600 dark:text-slate-300 focus:ring-indigo-500">
                                                    @foreach(\App\Enums\TaskStatus::cases() as $s)
                                                        <option value="{{ $s->value }}" @selected($task->status === $s)>{{ $s->label() }}</option>
                                                    @endforeach
                                                </select>
                                            </form>
                                        @endcan
                                    </div>
                                </div>
                            @empty
                                <div class="py-8 text-center text-xs text-slate-400 border border-dashed border-slate-200 dark:border-slate-800 rounded-xl">
                                    No tasks in this lane
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>

        <!-- VIEW 2: LIST VIEW -->
        @else
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                @if($tasks->isEmpty())
                    <div class="p-12 text-center text-sm text-slate-500">
                        No tasks match the active filters.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                            <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-bold text-slate-500 border-b border-slate-100 dark:border-slate-800">
                                <tr>
                                    <th class="px-6 py-4">Title</th>
                                    <th class="px-6 py-4">Priority</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4">Assignee</th>
                                    <th class="px-6 py-4">Due Date</th>
                                    <th class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach($tasks as $task)
                                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                        <td class="px-6 py-4">
                                            <a href="{{ route('projects.tasks.show', [$project, $task]) }}" class="font-semibold text-slate-900 dark:text-white hover:text-indigo-600 transition">
                                                {{ $task->title }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4">
                                            <x-priority-badge :priority="$task->priority" />
                                        </td>
                                        <td class="px-6 py-4">
                                            @can('updateStatus', $task)
                                                <form method="POST" action="{{ route('projects.tasks.status', [$project, $task]) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <select name="status" onchange="this.form.submit()" class="text-xs py-1 px-2.5 rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-800 focus:ring-indigo-500">
                                                        @foreach(\App\Enums\TaskStatus::cases() as $s)
                                                            <option value="{{ $s->value }}" @selected($task->status === $s)>{{ $s->label() }}</option>
                                                        @endforeach
                                                    </select>
                                                </form>
                                            @else
                                                <x-status-badge :status="$task->status" />
                                            @endcan
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <x-avatar :user="$task->assignee" size="xs" />
                                                <span class="text-xs">{{ $task->assignee ? $task->assignee->name : 'Unassigned' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-xs {{ $task->isOverdue() ? 'text-rose-600 font-bold' : 'text-slate-500' }}">
                                            {{ $task->due_date ? $task->due_date->format('M d, Y') : '—' }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('projects.tasks.show', [$project, $task]) }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">View</a>
                                                @can('update', $task)
                                                    <a href="{{ route('projects.tasks.edit', [$project, $task]) }}" class="text-xs text-slate-500 hover:text-slate-800">Edit</a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif

        <!-- Quick Add Task Modal -->
        @can('create', [\App\Models\Task::class, $project])
            <div x-show="createTaskModalOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" style="display: none;">
                <div @click.away="createTaskModalOpen = false" class="w-full max-w-xl bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Create New Task</h3>
                            <p class="text-xs text-slate-500">In project <strong class="text-slate-700 dark:text-slate-300">{{ $project->name }}</strong></p>
                        </div>
                        <button @click="createTaskModalOpen = false" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
                    </div>

                    <form method="POST" action="{{ route('projects.tasks.store', $project) }}" class="p-6 space-y-4">
                        @csrf

                        <!-- Hidden Status passed from column quick-add -->
                        <input type="hidden" name="status" :value="quickStatus">

                        <!-- Title -->
                        <div>
                            <x-input-label for="title" :value="__('Task Title *')" />
                            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" placeholder="e.g. Implement password reset flow" required autofocus />
                        </div>

                        <!-- Description -->
                        <div>
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 text-xs shadow-sm" placeholder="Provide background requirements, acceptance criteria, or links..."></textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Priority -->
                            <div>
                                <x-input-label for="priority" :value="__('Priority *')" />
                                <select id="priority" name="priority" class="mt-1 block w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 text-xs shadow-sm">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>

                            <!-- Assignee -->
                            <div>
                                <x-input-label for="assigned_to_id" :value="__('Assignee')" />
                                <select id="assigned_to_id" name="assigned_to_id" class="mt-1 block w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 text-xs shadow-sm">
                                    <option value="">Unassigned</option>
                                    @foreach($members as $member)
                                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Due Date -->
                        <div>
                            <x-input-label for="due_date" :value="__('Target Due Date')" />
                            <x-text-input id="due_date" name="due_date" type="date" class="mt-1 block w-full text-xs" />
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                            <button type="button" @click="createTaskModalOpen = false" class="px-4 py-2 rounded-xl text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 transition">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow transition">
                                Create Task
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endcan
    </div>
</x-app-layout>

