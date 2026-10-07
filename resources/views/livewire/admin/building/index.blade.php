<div class="space-y-6">
    <x-ui.page-header title="Gedung" description="Kelola gedung pada setiap properti.">
        <x-slot:actions>
            @can('building.create')
                <x-ui.button :href="route('admin.buildings.create')" wire:navigate>
                    <x-ui.icon name="plus" class="size-4" /> Tambah Gedung
                </x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card padding="p-0">
        <div class="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:items-center dark:border-gray-700">
            <div class="sm:w-72"><x-ui.search-input model="search" placeholder="Cari nama / kode..." /></div>
            <select wire:model.live="property" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Semua properti</option>
                @foreach ($properties as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        @if ($buildings->isEmpty())
            <div class="p-6"><x-ui.empty-state title="Belum ada gedung" description="Tambahkan gedung untuk properti Anda." /></div>
        @else
            <x-ui.table :headings="['Gedung', 'Properti', 'Lantai', 'Kamar', 'Status', '']">
                @foreach ($buildings as $building)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900 dark:text-white">{{ $building->name }}</p>
                            <p class="text-xs text-gray-500">{{ $building->code }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $building->property?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $building->floors_count }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $building->rooms_count }}</td>
                        <td class="px-4 py-3"><x-ui.status-badge :status="$building->status" /></td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                @can('building.update')
                                    <x-ui.button :href="route('admin.buildings.edit', $building)" variant="secondary" size="sm" wire:navigate>Ubah</x-ui.button>
                                @endcan
                                @can('building.delete')
                                    <x-ui.confirm-dialog title="Hapus gedung" message="Gedung akan dihapus jika tidak memiliki lantai atau kamar." :action="'delete('.$building->id.')'">
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
            <div class="border-t border-gray-100 p-4 dark:border-gray-700">{{ $buildings->links() }}</div>
        @endif
    </x-ui.card>
</div>
