<div class="space-y-6">
    <x-ui.page-header title="Tipe Kamar" description="Kelola tipe kamar, harga, dan fasilitas bawaan.">
        <x-slot:actions>
            @can('room_type.create')
                <x-ui.button :href="route('admin.room-types.create')" wire:navigate>
                    <x-ui.icon name="plus" class="size-4" /> Tambah Tipe
                </x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card padding="p-0">
        <div class="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:items-center dark:border-gray-700">
            <div class="sm:w-72"><x-ui.search-input model="search" placeholder="Cari tipe kamar..." /></div>
            <select wire:model.live="property" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Semua properti</option>
                @foreach ($properties as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        @if ($types->isEmpty())
            <div class="p-6"><x-ui.empty-state title="Belum ada tipe kamar" description="Tambahkan tipe kamar terlebih dahulu." /></div>
        @else
            <x-ui.table :headings="['Tipe', 'Properti', 'Harga', 'Deposit', 'Kamar', '']">
                @foreach ($types as $type)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900 dark:text-white">{{ $type->name }}</p>
                            <p class="text-xs text-gray-500">{{ $type->size_sqm }} m² · {{ $type->capacity }} orang</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $type->property?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ \App\Support\Money::format($type->default_price) }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ \App\Support\Money::format($type->default_deposit) }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $type->rooms_count }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                @can('room_type.update')
                                    <x-ui.button :href="route('admin.room-types.edit', $type)" variant="secondary" size="sm" wire:navigate>Ubah</x-ui.button>
                                @endcan
                                @can('room_type.delete')
                                    <x-ui.confirm-dialog title="Hapus tipe kamar" message="Tipe kamar akan dihapus jika tidak digunakan kamar." :action="'delete('.$type->id.')'">
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
            <div class="border-t border-gray-100 p-4 dark:border-gray-700">{{ $types->links() }}</div>
        @endif
    </x-ui.card>
</div>
