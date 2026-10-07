@props([
    'title' => 'Konfirmasi',
    'message' => 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
    'confirmLabel' => 'Ya, lanjutkan',
    'cancelLabel' => 'Batal',
    'action' => null,
    'variant' => 'danger',
])

@php
    $buttonVariants = [
        'danger' => 'bg-danger-600 text-white hover:bg-danger-700',
        'primary' => 'bg-primary-600 text-white hover:bg-primary-700',
    ];
@endphp

<div x-data="{ open: false }" {{ $attributes }}>
    <span @click="open = true" class="contents">{{ $trigger }}</span>

    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div x-show="open" x-transition.opacity @click="open = false" class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>

            <div x-show="open" x-transition class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $message }}</p>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="open = false" class="inline-flex items-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:ring-gray-600">
                        {{ $cancelLabel }}
                    </button>

                    @if ($action)
                        <button type="button" wire:click="{{ $action }}" @click="open = false" class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold {{ $buttonVariants[$variant] ?? $buttonVariants['danger'] }}">
                            {{ $confirmLabel }}
                        </button>
                    @else
                        <span @click="open = false" class="contents">{{ $confirm }}</span>
                    @endif
                </div>
            </div>
        </div>
    </template>
</div>
