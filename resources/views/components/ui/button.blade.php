@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
])

@php
    $base = 'inline-flex items-center justify-center gap-2 font-semibold rounded-lg transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none dark:focus-visible:ring-offset-gray-900';

    $variants = [
        'primary' => 'bg-primary-600 text-white hover:bg-primary-700 focus-visible:ring-primary-500',
        'secondary' => 'bg-white text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus-visible:ring-primary-500 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-600 dark:hover:bg-gray-700',
        'danger' => 'bg-danger-600 text-white hover:bg-danger-700 focus-visible:ring-danger-500',
        'success' => 'bg-success-600 text-white hover:bg-success-700 focus-visible:ring-success-500',
        'warning' => 'bg-warning-500 text-white hover:bg-warning-600 focus-visible:ring-warning-400',
        'ghost' => 'text-gray-600 hover:bg-gray-100 focus-visible:ring-gray-400 dark:text-gray-300 dark:hover:bg-gray-800',
        'outline' => 'ring-1 ring-inset ring-primary-300 text-primary-700 hover:bg-primary-50 focus-visible:ring-primary-500 dark:ring-primary-700 dark:text-primary-300 dark:hover:bg-primary-950',
    ];

    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-5 py-2.5 text-base',
        'icon' => 'p-2 text-sm',
    ];

    $classes = trim($base.' '.($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md']));
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
