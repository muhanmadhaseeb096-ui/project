<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('projects.tasks.index', $project) }}" class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <a href="{{ route('projects.tasks.index', $project) }}" class="hover:text-indigo-600 font-semibold">{{ $project->name }}</a>
                    <span>/</span>
                    <span class="font-mono">#TASK-{{ $task->id }}</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @can('update', $task)
                    <a href="{{ route('projects.tasks.edit', [$project, $task]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 transition shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit
                    </a>
                @endcan

                @can('delete', $task)
                    <form method="POST" action="{{ route('projects.tasks.destroy', [$project, $task]) }}" onsubmit="return confirm('Are you sure you want to delete this task?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-semibold text-rose-600 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Task Content -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 space-y-6 shadow-sm">
                <!-- Title & Status row -->
                <div class="space-y-3 border-b border-slate-100 dark:border-slate-800 pb-5">
                    <div class="flex items-center justify-between gap-4">
                        <x-priority-badge :priority="$task->priority" />
                        <x-status-badge :status="$task->status" />
                    </div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white leading-tight">
                        {{ $task->title }}
                    </h1>
                </div>

                <!-- Description -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Description</h3>
                    <div class="prose dark:prose-invert max-w-none text-slate-700 dark:text-slate-300 text-sm whitespace-pre-line bg-slate-50/50 dark:bg-slate-800/30 p-4 rounded-xl border border-slate-100 dark:border-slate-800">
                        {{ $task->description ?: 'No detailed description provided for this task.' }}
                    </div>
                </div>

                <!-- Status Transition Quick Box -->
                @can('updateStatus', $task)
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Quick Status Transition</span>
                        <form method="POST" action="{{ route('projects.tasks.status', [$project, $task]) }}" class="flex items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="status" onchange="this.form.submit()" class="text-xs py-1.5 px-3 rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 focus:ring-indigo-500">
                                @foreach(\App\Enums\TaskStatus::cases() as $s)
                                    <option value="{{ $s->value }}" @selected($task->status === $s)>{{ $s->label() }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                @endcan
            </div>
        </div>

        <!-- Metadata Sidebar -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 space-y-5 shadow-sm">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 dark:border-slate-800 pb-2">Task Details</h3>

                <!-- Assignee -->
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500 font-medium">Assignee</span>
                    <div class="flex items-center gap-2">
                        <x-avatar :user="$task->assignee" size="xs" />
                        <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">
                            {{ $task->assignee ? $task->assignee->name : 'Unassigned' }}
                        </span>
                    </div>
                </div>

                <!-- Due Date -->
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500 font-medium">Target Due Date</span>
                    <span class="text-xs font-semibold {{ $task->isOverdue() ? 'text-rose-600 font-bold bg-rose-50 px-2 py-0.5 rounded' : 'text-slate-800 dark:text-slate-200' }}">
                        {{ $task->due_date ? $task->due_date->format('M d, Y') : 'No due date' }}
                    </span>
                </div>

                <!-- Completed At -->
                @if($task->completed_at)
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-500 font-medium">Completed On</span>
                        <span class="text-xs font-semibold text-emerald-600">
                            {{ $task->completed_at->format('M d, Y H:i') }}
                        </span>
                    </div>
                @endif

                <!-- Created By -->
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500 font-medium">Created By</span>
                    <div class="flex items-center gap-2">
                        <x-avatar :user="$task->creator" size="xs" />
                        <span class="text-xs text-slate-700 dark:text-slate-300">{{ $task->creator?->name }}</span>
                    </div>
                </div>

                <!-- Created At -->
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500 font-medium">Created</span>
                    <span class="text-xs text-slate-500">{{ $task->created_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

