<div class="space-y-6">
    <x-ui.page-header title="Lantai" description="Kelola lantai pada setiap gedung.">
        <x-slot:actions>
            @can('floor.create')
                <x-ui.button :href="route('admin.floors.create')" wire:navigate>
                    <x-ui.icon name="plus" class="size-4" /> Tambah Lantai
                </x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card padding="p-0">
        <div class="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:items-center dark:border-gray-700">
            <div class="sm:w-72"><x-ui.search-input model="search" placeholder="Cari nama lantai..." /></div>
            <select wire:model.live="building" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Semua gedung</option>
                @foreach ($buildings as $b)
                    <option value="{{ $b->id }}">{{ $b->property?->name }} — {{ $b->name }}</option>
                @endforeach
            </select>
        </div>

        @if ($floors->isEmpty())
            <div class="p-6"><x-ui.empty-state title="Belum ada lantai" description="Tambahkan lantai pada gedung." /></div>
        @else
            <x-ui.table :headings="['Lantai', 'Gedung', 'Kamar', '']">
                @foreach ($floors as $floor)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900 dark:text-white">{{ $floor->name }}</p>
                            <p class="text-xs text-gray-500">Level {{ $floor->level }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $floor->building?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $floor->rooms_count }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                @can('floor.update')
                                    <x-ui.button :href="route('admin.floors.edit', $floor)" variant="secondary" size="sm" wire:navigate>Ubah</x-ui.button>
                                @endcan
                                @can('floor.delete')
                                    <x-ui.confirm-dialog title="Hapus lantai" message="Lantai akan dihapus jika tidak memiliki kamar." :action="'delete('.$floor->id.')'">
                                        <x-slot:trigger>
                                            <button type="button" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-danger-600 hover:bg-danger-50 dark:hover:bg-danger-950">Hapus</button>
                                        </x-slot:trigger>
                                    </x-ui.confirm-dialog>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
            <div class="border-t border-gray-100 p-4 dark:border-gray-700">{{ $floors->links() }}</div>
        @endif
    </x-ui.card>
</div>
