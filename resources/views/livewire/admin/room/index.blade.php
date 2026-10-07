<div class="space-y-6">
    <x-ui.page-header title="Kamar" description="Kelola seluruh kamar, harga, dan status hunian.">
        <x-slot:actions>
            <x-ui.button :href="route('admin.rooms.map')" variant="secondary" wire:navigate>
                <x-ui.icon name="squares-2x2" class="size-4" /> Peta Kamar
            </x-ui.button>
            @can('room.create')
                <x-ui.button :href="route('admin.rooms.create')" wire:navigate>
                    <x-ui.icon name="plus" class="size-4" /> Tambah Kamar
                </x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card padding="p-0">
        <div class="flex flex-col gap-3 border-b border-gray-100 p-4 dark:border-gray-700">
            <div class="flex flex-wrap items-center gap-3">
                <div class="min-w-[12rem] flex-1"><x-ui.search-input model="search" placeholder="Cari nomor kamar..." /></div>
                <select wire:model.live="status" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $option)
                        <option value="{{ $option->value }}">{{ $option->label() }}</option>
                    @endforeach
                </select>
                <select wire:model.live="property" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">Semua properti</option>
                    @foreach ($properties as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="building" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">Semua gedung</option>
                    @foreach ($buildings as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>

                <div class="flex items-center gap-1 rounded-lg bg-gray-100 p-1 dark:bg-gray-700">
                    <button type="button" wire:click="setView('table')" @class(['rounded-md px-2.5 py-1 text-xs font-medium', 'bg-white shadow-sm dark:bg-gray-800' => $view === 'table'])>
                        <x-ui.icon name="document-text" class="size-4" />
                    </button>
                    <button type="button" wire:click="setView('card')" @class(['rounded-md px-2.5 py-1 text-xs font-medium', 'bg-white shadow-sm dark:bg-gray-800' => $view === 'card'])>
                        <x-ui.icon name="squares-2x2" class="size-4" />
                    </button>
                </div>
            </div>
        </div>

        @if ($rooms->isEmpty())
            <div class="p-6">
                <x-ui.empty-state title="Belum ada kamar" description="Tambahkan kamar untuk mulai mengelola hunian.">
                    <x-slot:action>
                        @can('room.create')
                            <x-ui.button :href="route('admin.rooms.create')" wire:navigate>Tambah Kamar</x-ui.button>
                        @endcan
                    </x-slot:action>
                </x-ui.empty-state>
            </div>
        @elseif ($view === 'card')
            <div class="grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($rooms as $room)
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $room->number }}</p>
                                <p class="text-xs text-gray-500">{{ $room->building?->name }} · {{ $room->floor?->name }}</p>
                            </div>
                            <x-ui.status-badge :status="$room->status" />
                        </div>
                        <p class="mt-3 text-sm font-medium text-gray-700 dark:text-gray-200">{{ \App\Support\Money::format($room->price) }}<span class="text-xs font-normal text-gray-400">/bulan</span></p>
                        <p class="text-xs text-gray-500">{{ $room->roomType?->name ?? 'Tanpa tipe' }}</p>
                        <div class="mt-4 flex justify-end gap-2">
                            @can('room.update')
                                <x-ui.button :href="route('admin.rooms.edit', $room)" variant="secondary" size="sm" wire:navigate>Ubah</x-ui.button>
                            @endcan
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="border-t border-gray-100 p-4 dark:border-gray-700">{{ $rooms->links() }}</div>
        @else
            <x-ui.table :headings="['Kamar', 'Properti / Gedung', 'Tipe', 'Harga', 'Status', '']">
                @foreach ($rooms as $room)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900 dark:text-white">{{ $room->number }}</p>
                            <p class="text-xs text-gray-500">{{ $room->floor?->name }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                            {{ $room->property?->name }}<br><span class="text-xs text-gray-400">{{ $room->building?->name }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $room->roomType?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ \App\Support\Money::format($room->price) }}</td>
                        <td class="px-4 py-3"><x-ui.status-badge :status="$room->status" /></td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                @can('room.update')
                                    <x-ui.button :href="route('admin.rooms.edit', $room)" variant="secondary" size="sm" wire:navigate>Ubah</x-ui.button>
                                @endcan
                                @can('room.delete')
                                    <x-ui.confirm-dialog title="Hapus kamar" message="Kamar {{ $room->number }} akan dihapus." :action="'delete('.$room->id.')'">
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
            <div class="border-t border-gray-100 p-4 dark:border-gray-700">{{ $rooms->links() }}</div>
        @endif
    </x-ui.card>
</div>
