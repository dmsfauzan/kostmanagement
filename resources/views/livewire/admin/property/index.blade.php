<div class="space-y-6">
    <x-ui.page-header title="Properti" description="Kelola properti kost yang Anda miliki.">
        <x-slot:actions>
            @can('property.create')
                <x-ui.button :href="route('admin.properties.create')" wire:navigate>
                    <x-ui.icon name="plus" class="size-4" /> Tambah Properti
                </x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card padding="p-0">
        <div class="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:items-center sm:justify-between dark:border-gray-700">
            <div class="sm:w-80">
                <x-ui.search-input model="search" placeholder="Cari nama atau kota..." />
            </div>

            <select wire:model.live="status" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Semua status</option>
                @foreach ($statuses as $option)
                    <option value="{{ $option->value }}">{{ $option->label() }}</option>
                @endforeach
            </select>
        </div>

        @if ($properties->isEmpty())
            <div class="p-6">
                <x-ui.empty-state title="Belum ada properti" description="Mulai dengan menambahkan properti pertama Anda.">
                    <x-slot:action>
                        @can('property.create')
                            <x-ui.button :href="route('admin.properties.create')" wire:navigate>Tambah Properti</x-ui.button>
                        @endcan
                    </x-slot:action>
                </x-ui.empty-state>
            </div>
        @else
            <x-ui.table :headings="['Nama', 'Kota', 'Gedung', 'Kamar', 'Status', '']">
                @foreach ($properties as $property)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900 dark:text-white">{{ $property->name }}</p>
                            <p class="text-xs text-gray-500">{{ $property->address }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $property->city ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $property->buildings_count }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $property->rooms_count }}</td>
                        <td class="px-4 py-3"><x-ui.status-badge :status="$property->status" /></td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                @can('property.update')
                                    <x-ui.button :href="route('admin.properties.edit', $property)" variant="secondary" size="sm" wire:navigate>Ubah</x-ui.button>
                                    <x-ui.confirm-dialog
                                        title="Hapus properti"
                                        message="Properti akan dihapus. Tindakan ini dapat dipulihkan oleh administrator."
                                        :action="'delete('.$property->id.')'"
                                    >
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

            <div class="border-t border-gray-100 p-4 dark:border-gray-700">{{ $properties->links() }}</div>
        @endif
    </x-ui.card>
</div>
