@props([
    'model',
    'label' => null,
    'required' => false,
    'hint' => null,
    'placeholder' => '0',
])

<div {{ $attributes->only('class')->merge(['class' => 'space-y-1.5']) }}>
    @if ($label)
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ $label }}
            @if ($required)
                <span class="text-danger-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-medium text-gray-400">Rp</span>
        <input
            type="number"
            min="0"
            step="1000"
            inputmode="numeric"
            placeholder="{{ $placeholder }}"
            @if ($required) required @endif
            wire:model="{{ $model }}"
            {{ $attributes->except(['class'])->merge(['class' => 'block w-full rounded-lg border-gray-300 pl-10 text-right text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white']) }}
        >
    </div>

    @error($model)
        <p class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
    @enderror

    @if ($hint)
        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</p>
    @endif
</div>
