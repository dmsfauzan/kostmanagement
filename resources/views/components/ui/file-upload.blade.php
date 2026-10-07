@props([
    'model',
    'label' => null,
    'multiple' => false,
    'accept' => 'image/jpeg,image/png,image/webp',
    'hint' => null,
])

<div class="space-y-1.5" x-data>
    @if ($label)
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
    @endif

    <label class="flex cursor-pointer flex-col items-center justify-center gap-1 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-6 text-center transition hover:border-primary-400 hover:bg-primary-50/50 dark:border-gray-600 dark:bg-gray-800/50 dark:hover:border-primary-600">
        <x-ui.icon name="download" class="size-6 rotate-180 text-gray-400" />
        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Pilih berkas</span>
        <span class="text-xs text-gray-400">{{ $hint ?? 'JPG, PNG, WebP hingga 4 MB' }}</span>

        <input
            type="file"
            wire:model="{{ $model }}"
            @if ($multiple) multiple @endif
            accept="{{ $accept }}"
            class="sr-only"
        >
    </label>

    <div wire:loading wire:target="{{ $model }}" class="text-xs text-primary-600">Mengunggah...</div>

    @error($model)
        <p class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
    @enderror
    @error($model.'.*')
        <p class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
    @enderror
</div>
