<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="w-4 h-4 rounded-full" style="background-color: {{ $project->color ?? '#4F46E5' }};"></span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $project->name }}</h1>
                    <p class="text-xs text-slate-500 font-mono">/projects/{{ $project->slug }}</p>
                </div>
            </div>

            <!-- Tab Navigation Links -->
            <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800 p-1 rounded-xl text-xs font-semibold">
                <a href="{{ route('projects.tasks.index', $project) }}" class="px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900">
                    Kanban Board
                </a>
                <a href="{{ route('projects.tasks.index', [$project, 'view' => 'list']) }}" class="px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900">
                    Task List
                </a>
                <a href="{{ route('projects.members', $project) }}" class="px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900">
                    Team ({{ $project->members()->count() }})
                </a>
                <a href="{{ route('projects.edit', $project) }}" class="px-3 py-1.5 rounded-lg bg-white dark:bg-slate-700 text-indigo-600 dark:text-white shadow-sm">
                    Settings
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-8">
        <!-- Project Settings Form -->
        <form method="POST" action="{{ route('projects.update', $project) }}" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 space-y-6 shadow-sm">
            @csrf
            @method('PUT')

            <h2 class="text-lg font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3">Project Metadata</h2>

            <!-- Project Name -->
            <div>
                <x-input-label for="name" :value="__('Project Name *')" />
                <x-text-input id="name" name="name" type="text" class="mt-1.5 block w-full" :value="old('name', $project->name)" required />
                <x-input-error class="mt-1" :messages="$errors->get('name')" />
            </div>

            <!-- Description -->
            <div>
                <x-input-label for="description" :value="__('Description')" />
                <textarea id="description" name="description" rows="4" class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm shadow-sm">{{ old('description', $project->description) }}</textarea>
                <x-input-error class="mt-1" :messages="$errors->get('description')" />
            </div>

            <!-- Project Status & Color -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6" x-data="{ selectedColor: '{{ old('color', $project->color ?? '#4F46E5') }}' }">
                <div>
                    <x-input-label for="status" :value="__('Project Lifecycle Status')" />
                    <select id="status" name="status" class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm shadow-sm">
                        <option value="active" @selected(old('status', $project->status) === 'active')>Active</option>
                        <option value="on_hold" @selected(old('status', $project->status) === 'on_hold')>On Hold</option>
                        <option value="archived" @selected(old('status', $project->status) === 'archived')>Archived</option>
                        <option value="completed" @selected(old('status', $project->status) === 'completed')>Completed</option>
                    </select>
                    <x-input-error class="mt-1" :messages="$errors->get('status')" />
                </div>

                <div>
                    <x-input-label for="color" :value="__('Accent Color')" />
                    <div class="mt-2 flex items-center gap-2">
                        <input type="color" id="color" name="color" x-model="selectedColor" class="h-10 w-12 rounded-lg border border-slate-300 dark:border-slate-700 cursor-pointer bg-white dark:bg-slate-800 p-1">
                        <div class="flex items-center gap-1.5">
                            @foreach(['#4F46E5', '#0284C7', '#059669', '#D97706', '#E11D48', '#7C3AED'] as $preset)
                                <button type="button" @click="selectedColor = '{{ $preset }}'" class="w-6 h-6 rounded-full border border-black/10 hover:scale-110 transition flex items-center justify-center" style="background-color: {{ $preset }};">
                                    <span x-show="selectedColor === '{{ $preset }}'" class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Due Date -->
            <div>
                <x-input-label for="due_date" :value="__('Target Due Date')" />
                <x-text-input id="due_date" name="due_date" type="date" class="mt-1.5 block w-full" :value="old('due_date', $project->due_date?->format('Y-m-d'))" />
                <x-input-error class="mt-1" :messages="$errors->get('due_date')" />
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100 dark:border-slate-800">
                <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow transition">
                    Save Changes
                </button>
            </div>
        </form>

        <!-- Danger Zone: Delete Project (Only Owner) -->
        @can('delete', $project)
            <div class="bg-rose-50/50 dark:bg-rose-950/20 rounded-2xl border border-rose-200 dark:border-rose-900/60 p-6 sm:p-8 space-y-4">
                <h3 class="text-base font-bold text-rose-700 dark:text-rose-400">Danger Zone</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400">Deleting this project will remove all affiliated tasks, column statuses, and pending invitations. This operation cannot be easily undone.</p>
                <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Are you sure you want to permanently delete this project and all its tasks?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 transition shadow">
                        Delete Project
                    </button>
                </form>
            </div>
        @endcan
    </div>
</x-app-layout>

