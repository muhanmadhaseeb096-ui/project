@props(['priority'])

@php
    $priorityEnum = $priority instanceof \App\Enums\TaskPriority 
        ? $priority 
        : \App\Enums\TaskPriority::tryFrom($priority) ?? \App\Enums\TaskPriority::MEDIUM;
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold border ' . $priorityEnum->badgeClasses()]) }}>
    @if($priorityEnum === \App\Enums\TaskPriority::URGENT)
        <span class="w-1.5 h-1.5 rounded-full bg-rose-600 animate-ping"></span>
    @endif
    {{ $priorityEnum->label() }}
</span>

