<div class="space-y-6">
    <x-ui.page-header title="Tagihan" description="Kelola tagihan sewa dan biaya lainnya.">
        <x-slot:actions>
            @can('invoice.create')
                <x-ui.button :href="route('admin.invoices.create')" wire:navigate>
                    <x-ui.icon name="plus" class="size-4" /> Tambah Tagihan
                </x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-ui.stat-card label="Total Ditagih" :value="\App\Support\Money::format($summary['billed'])" color="primary">
            <x-slot:icon><x-ui.icon name="banknotes" class="size-5" /></x-slot:icon>
        </x-ui.stat-card>
        <x-ui.stat-card label="Belum Dibayar" :value="\App\Support\Money::format($summary['outstanding'])" color="warning">
            <x-slot:icon><x-ui.icon name="clock" class="size-5" /></x-slot:icon>
        </x-ui.stat-card>
        <x-ui.stat-card label="Jatuh Tempo" :value="\App\Support\Money::format($summary['overdue'])" color="danger">
            <x-slot:icon><x-ui.icon name="clock" class="size-5" /></x-slot:icon>
        </x-ui.stat-card>
    </div>

    <x-ui.card padding="p-0">
        <div class="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:items-center dark:border-gray-700">
            <div class="sm:w-72"><x-ui.search-input model="search" placeholder="Cari nomor / penghuni..." /></div>
            <select wire:model.live="status" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Semua status</option>
                @foreach ($statuses as $option)
                    <option value="{{ $option->value }}">{{ $option->label() }}</option>
                @endforeach
            </select>
            <select wire:model.live="period" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Semua periode</option>
                @foreach ($periods as $p)
                    <option value="{{ $p }}">{{ $p }}</option>
                @endforeach
            </select>
        </div>

        @if ($invoices->isEmpty())
            <div class="p-6"><x-ui.empty-state title="Belum ada tagihan" description="Tagihan bulanan dibuat otomatis atau tambahkan manual." /></div>
        @else
            <x-ui.table :headings="['Nomor', 'Penghuni', 'Periode', 'Jatuh Tempo', 'Total', 'Sisa', 'Status', '']">
                @foreach ($invoices as $invoice)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $invoice->invoice_number }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                            {{ $invoice->tenant?->full_name ?? '—' }}
                            <span class="block text-xs text-gray-400">{{ $invoice->lease?->room?->number }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $invoice->billing_period ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $invoice->due_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ \App\Support\Money::format($invoice->total_amount) }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ \App\Support\Money::format($invoice->amount_due) }}</td>
                        <td class="px-4 py-3"><x-ui.status-badge :status="$invoice->status" /></td>
                        <td class="px-4 py-3 text-right">
                            <x-ui.button :href="route('admin.invoices.show', $invoice->invoice_number)" variant="secondary" size="sm" wire:navigate>Detail</x-ui.button>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
            <div class="border-t border-gray-100 p-4 dark:border-gray-700">{{ $invoices->links() }}</div>
        @endif
    </x-ui.card>
</div>
