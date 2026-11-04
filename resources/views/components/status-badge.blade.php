@props(['status'])

@php
    $statusEnum = $status instanceof \App\Enums\TaskStatus 
        ? $status 
        : \App\Enums\TaskStatus::tryFrom($status) ?? \App\Enums\TaskStatus::TODO;
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium border ' . $statusEnum->badgeClasses()]) }}>
    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
    {{ $statusEnum->label() }}
</span>

