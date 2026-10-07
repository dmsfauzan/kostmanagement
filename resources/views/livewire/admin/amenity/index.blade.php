<div class="space-y-6">
    <x-ui.page-header title="Fasilitas" description="Daftar fasilitas yang dapat dipasang pada kamar dan tipe kamar." />

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-ui.card padding="p-0">
                <div class="border-b border-gray-100 p-4 dark:border-gray-700">
                    <div class="sm:w-80"><x-ui.search-input model="search" placeholder="Cari fasilitas..." /></div>
                </div>

                @if ($amenities->isEmpty())
                    <div class="p-6"><x-ui.empty-state title="Belum ada fasilitas" description="Tambahkan fasilitas seperti AC, WiFi, atau kamar mandi dalam." /></div>
                @else
                    <x-ui.table :headings="['Nama', 'Kategori', 'Status', '']">
                        @foreach ($amenities as $amenity)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $amenity->name }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $amenity->category ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <x-ui.badge :variant="$amenity->is_active ? 'success' : 'neutral'">{{ $amenity->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui.badge>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @can('amenity.delete')
                                        <x-ui.confirm-dialog title="Hapus fasilitas" message="Fasilitas akan dihapus dari semua kamar." :action="'delete('.$amenity->id.')'">
                                            <x-slot:trigger>
                                                <button type="button" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-danger-600 hover:bg-danger-50 dark:hover:bg-danger-950">Hapus</button>
                                            </x-slot:trigger>
                                        </x-ui.confirm-dialog>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </x-ui.table>
                    <div class="border-t border-gray-100 p-4 dark:border-gray-700">{{ $amenities->links() }}</div>
                @endif
            </x-ui.card>
        </div>

        @can('amenity.create')
            <x-ui.card>
                <h3 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Tambah Fasilitas</h3>
                <form wire:submit="create" class="space-y-4">
                    <x-ui.input name="name" label="Nama" wire:model="name" :required="true" placeholder="Mis. AC, WiFi" />
                    <x-ui.input name="category" label="Kategori" wire:model="category" placeholder="Mis. kamar, umum" />
                    <x-ui.button type="submit" class="w-full">Tambah</x-ui.button>
                </form>
            </x-ui.card>
        @endcan
    </div>
</div>
