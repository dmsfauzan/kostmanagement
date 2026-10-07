@props([
    'headings' => [],
])

<div class="overflow-x-auto">
    <table {{ $attributes->merge(['class' => 'min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700']) }}>
        @if (! empty($headings))
            <thead class="bg-gray-50 dark:bg-gray-700/40">
                <tr>
                    @foreach ($headings as $heading)
                        <th scope="col" class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            {{ $heading }}
                        </th>
                    @endforeach
                </tr>
            </thead>
        @endif

        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            {{ $slot }}
        </tbody>
    </table>
</div>
