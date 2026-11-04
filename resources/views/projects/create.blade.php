<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('projects.index') }}" class="text-slate-400 hover:text-slate-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Create New Project</h1>
                <p class="text-sm text-slate-500">Initialize a dedicated workspace for your team initiatives.</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <form method="POST" action="{{ route('projects.store') }}" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 space-y-6 shadow-sm">
            @csrf

            <!-- Project Name -->
            <div>
                <x-input-label for="name" :value="__('Project Name *')" />
                <x-text-input id="name" name="name" type="text" class="mt-1.5 block w-full" :value="old('name')" required autofocus placeholder="e.g. Mobile App Redesign" />
                <x-input-error class="mt-1" :messages="$errors->get('name')" />
            </div>

            <!-- Description -->
            <div>
                <x-input-label for="description" :value="__('Description')" />
                <textarea id="description" name="description" rows="4" class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 text-sm shadow-sm" placeholder="Summarize project objectives, target milestones, and key stakeholders...">{{ old('description') }}</textarea>
                <x-input-error class="mt-1" :messages="$errors->get('description')" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6" x-data="{ selectedColor: '{{ old('color', '#4F46E5') }}' }">
                <!-- Color Theme -->
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
                    <x-input-error class="mt-1" :messages="$errors->get('color')" />
                </div>

                <!-- Due Date -->
                <div>
                    <x-input-label for="due_date" :value="__('Target Delivery Deadline')" />
                    <x-text-input id="due_date" name="due_date" type="date" class="mt-1.5 block w-full" :value="old('due_date')" />
                    <x-input-error class="mt-1" :messages="$errors->get('due_date')" />
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('projects.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow transition">
                    Create Project
                </button>
            </div>
        </form>
    </div>
</x-app-layout>

