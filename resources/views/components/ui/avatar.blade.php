@props([
    'name' => '',
    'src' => null,
    'size' => 'md',
])

@php
    $sizes = [
        'sm' => 'size-8 text-xs',
        'md' => 'size-10 text-sm',
        'lg' => 'size-14 text-lg',
    ];

    $parts = preg_split('/\s+/', trim((string) $name)) ?: [];
    $initials = collect($parts)
        ->take(2)
        ->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
    $initials = $initials !== '' ? $initials : '?';
@endphp

@if ($src)
    <img src="{{ $src }}" alt="{{ $name }}" {{ $attributes->merge(['class' => ($sizes[$size] ?? $sizes['md']).' rounded-full object-cover']) }}>
@else
    <span {{ $attributes->merge(['class' => ($sizes[$size] ?? $sizes['md']).' inline-flex items-center justify-center rounded-full bg-primary-100 font-semibold text-primary-700 dark:bg-primary-900 dark:text-primary-200']) }}>
        {{ $initials }}
    </span>
@endif
