@props([
    'tabs' => [],
    'active' => null,
])

@php
    $names = array_keys($tabs);
    $active ??= $names[0] ?? null;
@endphp

<div x-data="{ tab: @js($active) }" {{ $attributes }}>
    <div class="flex gap-1 overflow-x-auto border-b border-gray-200 dark:border-gray-700">
        @foreach ($tabs as $key => $label)
            <button
                type="button"
                @click="tab = @js($key)"
                :class="tab === @js($key)
                    ? 'border-primary-600 text-primary-600 dark:text-primary-400'
                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                class="whitespace-nowrap border-b-2 px-4 py-2.5 text-sm font-medium transition"
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div class="pt-4">
        {{ $slot }}
    </div>
</div>
