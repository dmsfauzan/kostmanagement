@props([
    'variant' => 'neutral',
])

@php
    $variants = [
        'primary' => 'bg-primary-50 text-primary-700 ring-primary-600/20 dark:bg-primary-950 dark:text-primary-300 dark:ring-primary-500/30',
        'success' => 'bg-success-50 text-success-700 ring-success-600/20 dark:bg-success-950 dark:text-success-300 dark:ring-success-500/30',
        'warning' => 'bg-warning-50 text-warning-700 ring-warning-600/20 dark:bg-warning-950 dark:text-warning-300 dark:ring-warning-500/30',
        'danger' => 'bg-danger-50 text-danger-700 ring-danger-600/20 dark:bg-danger-950 dark:text-danger-300 dark:ring-danger-500/30',
        'info' => 'bg-info-50 text-info-700 ring-info-600/20 dark:bg-info-950 dark:text-info-300 dark:ring-info-500/30',
        'neutral' => 'bg-gray-100 text-gray-700 ring-gray-500/20 dark:bg-gray-700 dark:text-gray-300 dark:ring-gray-500/30',
    ];

    $classes = $variants[$variant] ?? $variants['neutral'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset '.$classes]) }}>
    {{ $slot }}
</span>
