@props([
    'padding' => 'p-5',
])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800']) }}>
    @isset($header)
        <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-700">
            {{ $header }}
        </div>
    @endisset

    <div class="{{ $padding }}">
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="border-t border-gray-100 bg-gray-50 px-5 py-4 dark:border-gray-700 dark:bg-gray-800/50">
            {{ $footer }}
        </div>
    @endisset
</div>
