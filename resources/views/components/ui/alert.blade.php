@props([
    'type' => 'info',
    'title' => null,
    'dismissible' => false,
])

@php
    $variants = [
        'success' => ['bg-success-50', 'text-success-800', 'ring-success-600/20', 'text-success-500'],
        'warning' => ['bg-warning-50', 'text-warning-800', 'ring-warning-600/20', 'text-warning-500'],
        'danger' => ['bg-danger-50', 'text-danger-800', 'ring-danger-600/20', 'text-danger-500'],
        'info' => ['bg-info-50', 'text-info-800', 'ring-info-600/20', 'text-info-500'],
    ];

    [$bg, $text, $ring, $iconColor] = $variants[$type] ?? $variants['info'];

    $icons = [
        'success' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        'warning' => 'M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z',
        'danger' => 'M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        'info' => 'M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z',
    ];
@endphp

<div
    @if ($dismissible)
        x-data="{ show: true }"
        x-show="show"
    @endif
    {{ $attributes->merge(['class' => "flex gap-3 rounded-lg p-4 ring-1 ring-inset {$bg} {$text} {$ring}"]) }}
>
    <svg class="size-5 shrink-0 {{ $iconColor }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$type] ?? $icons['info'] }}" />
    </svg>

    <div class="flex-1 text-sm">
        @if ($title)
            <p class="font-semibold">{{ $title }}</p>
        @endif
        <div @class(['mt-1' => (bool) $title])>{{ $slot }}</div>
    </div>

    @if ($dismissible)
        <button type="button" @click="show = false" class="shrink-0 opacity-60 hover:opacity-100">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
        </button>
    @endif
</div>
