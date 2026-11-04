@props([
    'user' => null,
    'size' => 'md', // xs, sm, md, lg
])

@php
    $dimensions = match($size) {
        'xs' => 'w-5 h-5 text-[10px]',
        'sm' => 'w-6 h-6 text-xs',
        'md' => 'w-8 h-8 text-xs',
        'lg' => 'w-10 h-10 text-sm',
        'xl' => 'w-12 h-12 text-base',
        default => 'w-8 h-8 text-xs',
    };

    $initials = $user ? $user->initials() : '?';
    $name = $user ? $user->name : 'Unassigned';
    $avatarUrl = $user?->avatar_url;
@endphp

@if($avatarUrl)
    <img src="{{ $avatarUrl }}" alt="{{ $name }}" title="{{ $name }}" {{ $attributes->merge(['class' => "{$dimensions} rounded-full object-cover border border-slate-200 dark:border-slate-700"]) }}>
@else
    <div title="{{ $name }}" {{ $attributes->merge(['class' => "{$dimensions} rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300 font-semibold flex items-center justify-center border border-indigo-200 dark:border-indigo-800 tracking-wider"]) }}>
        {{ $initials }}
    </div>
@endif

