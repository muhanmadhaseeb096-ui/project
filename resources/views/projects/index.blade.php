<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Projects Directory</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">All workspaces and active team initiatives you belong to.</p>
            </div>
            <a href="{{ route('projects.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create Project
            </a>
        </div>
    </x-slot>

    @if($projects->isEmpty())
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-12 text-center border border-dashed border-slate-300 dark:border-slate-700 max-w-xl mx-auto my-12">
            <div class="w-16 h-16 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center text-indigo-600 mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">No projects found</h3>
            <p class="text-sm text-slate-500 mt-2 mb-6">You are not a member of any project yet. Create your first project or ask a project manager to invite you.</p>
            <a href="{{ route('projects.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition shadow">
                + Create New Project
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($projects as $project)
                @php
                    $progress = $project->tasks_count > 0 
                        ? round(($project->completed_tasks_count / $project->tasks_count) * 100) 
                        : 0;
                @endphp
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 flex flex-col justify-between hover:shadow-lg hover:border-slate-300 dark:hover:border-slate-700 transition">
                    <div>
                        <!-- Header with Color & Status -->
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="flex items-center gap-2.5">
                                <span class="w-4 h-4 rounded-full flex-shrink-0" style="background-color: {{ $project->color ?? '#4F46E5' }};"></span>
                                <a href="{{ route('projects.tasks.index', $project) }}" class="font-bold text-base text-slate-900 dark:text-white hover:text-indigo-600 transition">
                                    {{ $project->name }}
                                </a>
                            </div>
                            <x-role-badge :role="$project->pivot->role" />
                        </div>

                        <!-- Description -->
                        <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 mb-5 h-8">
                            {{ $project->description ?: 'No detailed description available.' }}
                        </p>

                        <!-- Progress Bar -->
                        <div class="space-y-1.5 mb-5">
                            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                                <span>Task Completion</span>
                                <span>{{ $project->completed_tasks_count }}/{{ $project->tasks_count }} ({{ $progress }}%)</span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                                <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300" style="width: {{ $progress }}%;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-1.5 text-xs text-slate-500">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span>{{ $project->members_count }} {{ Str::plural('collaborator', $project->members_count) }}</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('projects.members', $project) }}" title="Team Roster" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                            </a>
                            <a href="{{ route('projects.tasks.index', $project) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-700 bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition">
                                Open Board &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $projects->links() }}
        </div>
    @endif
</x-app-layout>

