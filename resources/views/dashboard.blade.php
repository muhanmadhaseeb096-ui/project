<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Workspace Overview</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Welcome back, {{ Auth::user()->name }}. Here is what's happening today.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('projects.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Project
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Metrics Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Metric 1: Total Projects -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Active Projects</span>
                <span class="p-2 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900 dark:text-white">{{ $stats['total_projects'] }}</span>
                <span class="text-xs text-slate-400">workspaces</span>
            </div>
        </div>

        <!-- Metric 2: Pending Tasks -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Assigned To Me</span>
                <span class="p-2 rounded-lg bg-sky-50 dark:bg-sky-950/50 text-sky-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900 dark:text-white">{{ $stats['pending_tasks'] }}</span>
                <span class="text-xs text-slate-400">pending tasks</span>
            </div>
        </div>

        <!-- Metric 3: Urgent Tasks -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Urgent Priority</span>
                <span class="p-2 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-rose-600 dark:text-rose-400">{{ $stats['urgent_tasks'] }}</span>
                <span class="text-xs text-slate-400">require immediate action</span>
            </div>
        </div>

        <!-- Metric 4: Completed Tasks -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Completed By Me</span>
                <span class="p-2 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ $stats['completed_tasks'] }}</span>
                <span class="text-xs text-slate-400">tasks delivered</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left 2 Cols: My Active Projects -->
        <div class="lg:col-span-2 space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>My Projects</span>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300">{{ $projects->count() }}</span>
                </h2>
                <a href="{{ route('projects.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                    View all projects &rarr;
                </a>
            </div>

            @if($projects->isEmpty())
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-10 text-center border border-dashed border-slate-300 dark:border-slate-700">
                    <div class="w-12 h-12 rounded-full bg-indigo-50 dark:bg-indigo-950 flex items-center justify-center text-indigo-600 mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">No active projects</h3>
                    <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">Create your first project to start organizing tasks, inviting team members, and shipping work.</p>
                    <a href="{{ route('projects.create') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition">
                        + Create Your First Project
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($projects as $project)
                        @php
                            $progress = $project->tasks_count > 0 
                                ? round(($project->completed_tasks_count / $project->tasks_count) * 100) 
                                : 0;
                        @endphp
                        <a href="{{ route('projects.tasks.index', $project) }}" class="group block bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 hover:border-indigo-400 dark:hover:border-indigo-600 hover:shadow-md transition">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-3.5 h-3.5 rounded-full" style="background-color: {{ $project->color ?? '#4F46E5' }};"></span>
                                    <h3 class="font-bold text-slate-900 dark:text-white group-hover:text-indigo-600 transition truncate max-w-[180px]">{{ $project->name }}</h3>
                                </div>
                                <x-role-badge :role="$project->pivot->role" />
                            </div>

                            <p class="text-xs text-slate-500 line-clamp-2 mb-4 h-8">{{ $project->description ?: 'No description provided.' }}</p>

                            <!-- Progress Bar -->
                            <div class="space-y-1.5 mb-4">
                                <div class="flex items-center justify-between text-[11px] text-slate-500 font-medium">
                                    <span>Progress</span>
                                    <span>{{ $project->completed_tasks_count }}/{{ $project->tasks_count }} tasks ({{ $progress }}%)</span>
                                </div>
                                <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-indigo-600 h-1.5 rounded-full transition-all duration-300" style="width: {{ $progress }}%;"></div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-500">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    {{ $project->members_count }} {{ Str::plural('member', $project->members_count) }}
                                </span>
                                @if($project->due_date)
                                    <span>Due {{ $project->due_date->format('M d, Y') }}</span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Right 1 Col: Urgent & Assigned Tasks -->
        <div class="space-y-4">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center justify-between">
                <span>Action Items</span>
                <span class="text-xs font-normal text-slate-500">Assigned to me</span>
            </h2>

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                @if($assignedTasks->isEmpty())
                    <div class="p-8 text-center text-slate-500 text-sm">
                        <svg class="w-8 h-8 text-emerald-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        All clear! You have no open tasks assigned.
                    </div>
                @else
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($assignedTasks as $task)
                            <a href="{{ route('projects.tasks.show', [$task->project, $task]) }}" class="p-4 block hover:bg-slate-50 dark:hover:bg-slate-800/60 transition group">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full" style="background-color: {{ $task->project->color ?? '#4F46E5' }};"></span>
                                            <span class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">{{ $task->project->name }}</span>
                                        </div>
                                        <h4 class="text-sm font-semibold text-slate-900 dark:text-white group-hover:text-indigo-600 transition leading-snug">{{ $task->title }}</h4>
                                    </div>
                                    <x-priority-badge :priority="$task->priority" />
                                </div>
                                <div class="mt-2.5 flex items-center justify-between text-xs text-slate-500">
                                    <x-status-badge :status="$task->status" />
                                    @if($task->due_date)
                                        <span class="{{ $task->isOverdue() ? 'text-rose-600 font-bold' : '' }}">
                                            {{ $task->due_date->format('M d') }}
                                        </span>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
