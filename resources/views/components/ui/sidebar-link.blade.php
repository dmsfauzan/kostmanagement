@props([
    'href',
    'icon' => 'home',
    'active' => false,
])

<a
    href="{{ $href }}"
    @class([
        'group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
        'bg-primary-50 text-primary-700 dark:bg-primary-950 dark:text-primary-300' => $active,
        'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-100' => ! $active,
    ])
>
    <x-ui.icon :name="$icon" @class([
        'size-5 shrink-0',
        'text-primary-600 dark:text-primary-400' => $active,
        'text-gray-400 group-hover:text-gray-500 dark:text-gray-500' => ! $active,
    ]) />
    <span>{{ $slot }}</span>
</a>
