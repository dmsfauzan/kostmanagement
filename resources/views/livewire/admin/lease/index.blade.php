<div class="space-y-6">
    <x-ui.page-header title="Kontrak Sewa" description="Kelola kontrak sewa dan periode hunian.">
        <x-slot:actions>
            @can('lease.create')
                <x-ui.button :href="route('admin.leases.create')" wire:navigate>
                    <x-ui.icon name="plus" class="size-4" /> Tambah Kontrak
                </x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card padding="p-0">
        <div class="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:items-center dark:border-gray-700">
            <div class="sm:w-72"><x-ui.search-input model="search" placeholder="Cari kode / nama penghuni..." /></div>
            <select wire:model.live="status" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Semua status</option>
                @foreach ($statuses as $option)
                    <option value="{{ $option->value }}">{{ $option->label() }}</option>
                @endforeach
            </select>
            <select wire:model.live="tenant" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Semua penghuni</option>
                @foreach ($tenants as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        @if ($leases->isEmpty())
            <div class="p-6"><x-ui.empty-state title="Belum ada kontrak" description="Buat kontrak sewa untuk penghuni dan kamar." /></div>
        @else
            <x-ui.table :headings="['Kode', 'Penghuni', 'Kamar', 'Periode', 'Sewa', 'Status', '']">
                @foreach ($leases as $lease)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $lease->code }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $lease->tenant?->full_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $lease->room?->number ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $lease->start_date->format('d/m/Y') }} – {{ $lease->end_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ \App\Support\Money::format($lease->rent_amount) }}</td>
                        <td class="px-4 py-3"><x-ui.status-badge :status="$lease->status" /></td>
                        <td class="px-4 py-3">
                            <x-ui.button :href="route('admin.leases.show', $lease)" variant="secondary" size="sm" wire:navigate>Detail</x-ui.button>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
            <div class="border-t border-gray-100 p-4 dark:border-gray-700">{{ $leases->links() }}</div>
        @endif
    </x-ui.card>
</div>
