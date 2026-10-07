@props([
    'label',
    'value',
    'hint' => null,
    'color' => 'primary',
    'trend' => null,
])

@php
    $colors = [
        'primary' => 'bg-primary-50 text-primary-600 dark:bg-primary-950 dark:text-primary-400',
        'success' => 'bg-success-50 text-success-600 dark:bg-success-950 dark:text-success-400',
        'warning' => 'bg-warning-50 text-warning-600 dark:bg-warning-950 dark:text-warning-400',
        'danger' => 'bg-danger-50 text-danger-600 dark:bg-danger-950 dark:text-danger-400',
        'info' => 'bg-info-50 text-info-600 dark:bg-info-950 dark:text-info-400',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800']) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">{{ $label }}</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $value }}</p>
            @if ($hint)
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ $hint }}</p>
            @endif
        </div>

        @isset($icon)
            <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-lg {{ $colors[$color] ?? $colors['primary'] }}">
                {{ $icon }}
            </span>
        @endisset
    </div>

    @if ($trend)
        <p class="mt-3 text-xs font-medium text-gray-500 dark:text-gray-400">{{ $trend }}</p>
    @endif
</div>
