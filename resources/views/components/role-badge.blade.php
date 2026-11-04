@props(['role'])

@php
    $roleEnum = $role instanceof \App\Enums\ProjectRole 
        ? $role 
        : \App\Enums\ProjectRole::tryFrom($role) ?? \App\Enums\ProjectRole::MEMBER;
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold border ' . $roleEnum->badgeClasses()]) }}>
    {{ $roleEnum->label() }}
</span>

