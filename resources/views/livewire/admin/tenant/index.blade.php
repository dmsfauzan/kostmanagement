<div class="space-y-6">
    <x-ui.page-header title="Penghuni" description="Kelola data penghuni, akun portal, dan riwayat sewa.">
        <x-slot:actions>
            @can('tenant.create')
                <x-ui.button :href="route('admin.tenants.create')" wire:navigate>
                    <x-ui.icon name="plus" class="size-4" /> Tambah Penghuni
                </x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card padding="p-0">
        <div class="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:items-center dark:border-gray-700">
            <div class="sm:w-72"><x-ui.search-input model="search" placeholder="Cari nama / email / NIK..." /></div>
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
        </div>

        @if ($tenants->isEmpty())
            <div class="p-6"><x-ui.empty-state title="Belum ada penghuni" description="Tambahkan penghuni pertama Anda." /></div>
        @else
            <x-ui.table :headings="['Nama', 'Kontak', 'Properti', 'Akun', 'Status', '']">
                @foreach ($tenants as $tenant)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.tenants.show', $tenant) }}" wire:navigate class="font-medium text-gray-900 hover:text-primary-600 dark:text-white">{{ $tenant->full_name }}</a>
                            <p class="text-xs text-gray-500">{{ $tenant->nik ? 'NIK '.$tenant->nik : '' }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                            <p>{{ $tenant->phone }}</p>
                            <p class="text-xs text-gray-500">{{ $tenant->email }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $tenant->property?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @if ($tenant->user?->isActive())
                                <x-ui.badge variant="success">Aktif</x-ui.badge>
                            @elseif ($tenant->hasAccount())
                                <x-ui.badge variant="warning">Diundang</x-ui.badge>
                            @else
                                <x-ui.badge variant="neutral">Belum ada</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-4 py-3"><x-ui.status-badge :status="$tenant->status" /></td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <x-ui.button :href="route('admin.tenants.show', $tenant)" variant="secondary" size="sm" wire:navigate>Detail</x-ui.button>
                                @can('tenant.update')
                                    <x-ui.button :href="route('admin.tenants.edit', $tenant)" variant="secondary" size="sm" wire:navigate>Ubah</x-ui.button>
                                @endcan
                                @can('tenant.update')
                                    <x-ui.confirm-dialog title="Hapus penghuni" message="Hapus data penghuni {{ $tenant->full_name }}?" :action="'delete('.$tenant->id.')'">
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
            <div class="border-t border-gray-100 p-4 dark:border-gray-700">{{ $tenants->links() }}</div>
        @endif
    </x-ui.card>
</div>
