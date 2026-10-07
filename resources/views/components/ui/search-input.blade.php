@props([
    'model' => 'search',
    'placeholder' => 'Cari...',
])

<div class="relative">
    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
        <x-ui.icon name="search" class="size-4" />
    </span>
    <input
        type="search"
        wire:model.live.debounce.300ms="{{ $model }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => 'block w-full rounded-lg border-gray-300 pl-9 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400']) }}
    >
</div>
