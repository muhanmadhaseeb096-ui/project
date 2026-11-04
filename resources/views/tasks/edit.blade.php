<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('projects.tasks.show', [$project, $task]) }}" class="text-slate-400 hover:text-slate-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Edit Task #{{ $task->id }}</h1>
                <p class="text-sm text-slate-500">Project: <span class="font-semibold text-indigo-600">{{ $project->name }}</span></p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <form method="POST" action="{{ route('projects.tasks.update', [$project, $task]) }}" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 space-y-6 shadow-sm">
            @csrf
            @method('PUT')

            <!-- Title -->
            <div>
                <x-input-label for="title" :value="__('Task Title *')" />
                <x-text-input id="title" name="title" type="text" class="mt-1.5 block w-full" :value="old('title', $task->title)" required />
                <x-input-error class="mt-1" :messages="$errors->get('title')" />
            </div>

            <!-- Description -->
            <div>
                <x-input-label for="description" :value="__('Description / Acceptance Criteria')" />
                <textarea id="description" name="description" rows="4" class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 text-sm shadow-sm">{{ old('description', $task->description) }}</textarea>
                <x-input-error class="mt-1" :messages="$errors->get('description')" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Priority -->
                <div>
                    <x-input-label for="priority" :value="__('Priority Level *')" />
                    <select id="priority" name="priority" class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 text-sm shadow-sm">
                        @foreach(\App\Enums\TaskPriority::cases() as $priority)
                            <option value="{{ $priority->value }}" @selected(old('priority', $task->priority->value) === $priority->value)>{{ $priority->label() }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-1" :messages="$errors->get('priority')" />
                </div>

                <!-- Status -->
                <div>
                    <x-input-label for="status" :value="__('Status *')" />
                    <select id="status" name="status" class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 text-sm shadow-sm">
                        @foreach(\App\Enums\TaskStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected(old('status', $task->status->value) === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-1" :messages="$errors->get('status')" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Assignee -->
                <div>
                    <x-input-label for="assigned_to_id" :value="__('Assign Collaborator')" />
                    <select id="assigned_to_id" name="assigned_to_id" class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 text-sm shadow-sm">
                        <option value="">Unassigned</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}" @selected(old('assigned_to_id', $task->assigned_to_id) == $member->id)>{{ $member->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-1" :messages="$errors->get('assigned_to_id')" />
                </div>

                <!-- Due Date -->
                <div>
                    <x-input-label for="due_date" :value="__('Target Due Date')" />
                    <x-text-input id="due_date" name="due_date" type="date" class="mt-1.5 block w-full text-sm" :value="old('due_date', $task->due_date?->format('Y-m-d'))" />
                    <x-input-error class="mt-1" :messages="$errors->get('due_date')" />
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('projects.tasks.show', [$project, $task]) }}" class="px-4 py-2 rounded-xl text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 transition">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow transition">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</x-app-layout>

