<div class="relative" x-data="{ open: false }">
    <input
        type="search"
        wire:model.live.debounce.300ms="term"
        placeholder="Cari penghuni, kamar, tagihan..."
        @focus="open = true"
        @input="open = true"
        @click.away="open = false"
        class="block w-64 rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
    >

    @if ($term !== '')
        <div
            x-show="open"
            @click.away="open = false"
            class="absolute left-0 top-full z-40 mt-2 max-h-96 w-80 overflow-y-auto rounded-xl border border-gray-100 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"
        >
            @if (empty($groups))
                <p class="px-4 py-6 text-center text-sm text-gray-400">Tidak ada hasil untuk "{{ $term }}".</p>
            @else
                <div class="p-2">
                    @foreach ($groups as $group)
                        <div class="mb-1">
                            <p class="px-3 py-1 text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $group['label'] }}</p>
                            @foreach ($group['items'] as $item)
                                <a href="{{ $item['url'] }}" wire:navigate @click="open = false" class="block rounded-lg px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $item['label'] }}</p>
                                    @if ($item['subtitle'])
                                        <p class="text-xs text-gray-500">{{ $item['subtitle'] }}</p>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($term !== '')
                <button type="button" wire:click="clear" class="w-full border-t border-gray-100 px-4 py-2 text-xs font-medium text-gray-500 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                    Bersihkan
                </button>
            @endif
        </div>
    @endif
</div>
