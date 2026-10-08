<div class="space-y-6">
    <x-ui.page-header title="Pengumuman" description="Buat dan kelola pengumuman untuk penghuni.">
        <x-slot:actions>
            @can('announcement.create')
                <x-ui.button :href="route('admin.announcements.create')" wire:navigate>
                    <x-ui.icon name="plus" class="size-4" /> Tambah Pengumuman
                </x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card padding="p-0">
        <div class="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:items-center dark:border-gray-700">
            <div class="sm:w-80"><x-ui.search-input model="search" placeholder="Cari judul..." /></div>
            <select wire:model.live="status" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Semua status</option>
                @foreach ($statuses as $option)
                    <option value="{{ $option->value }}">{{ $option->label() }}</option>
                @endforeach
            </select>
        </div>

        @if ($announcements->isEmpty())
            <div class="p-6"><x-ui.empty-state title="Belum ada pengumuman" description="Buat pengumuman untuk penghuni." /></div>
        @else
            <x-ui.table :headings="['Judul', 'Target', 'Penerima', 'Status', '']">
                @foreach ($announcements as $announcement)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900 dark:text-white">{{ $announcement->title }}</p>
                            <p class="text-xs text-gray-500">{{ $announcement->publish_at?->translatedFormat('d M Y H:i') ?? 'Belum terjadwal' }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $announcement->target_type->label() }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $announcement->recipients_count }}</td>
                        <td class="px-4 py-3"><x-ui.status-badge :status="$announcement->status" /></td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                @can('announcement.update')
                                    @if ($announcement->status->value === 'draft')
                                        <x-ui.button wire:click="publish({{ $announcement->id }})" size="sm">Terbitkan</x-ui.button>
                                    @elseif ($announcement->status->value === 'published')
                                        <x-ui.button wire:click="archive({{ $announcement->id }})" variant="secondary" size="sm">Arsipkan</x-ui.button>
                                    @endif
                                    <x-ui.button :href="route('admin.announcements.edit', $announcement)" variant="secondary" size="sm" wire:navigate>Ubah</x-ui.button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
            <div class="border-t border-gray-100 p-4 dark:border-gray-700">{{ $announcements->links() }}</div>
        @endif
    </x-ui.card>
</div>
