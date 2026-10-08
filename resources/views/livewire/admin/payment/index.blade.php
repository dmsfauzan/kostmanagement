<div class="space-y-6">
    <x-ui.page-header title="Pembayaran" description="Tinjau pembayaran dan bukti transfer penghuni.">
        <x-slot:actions>
            @can('payment.view')
                <x-ui.button :href="route('admin.payment-methods')" variant="secondary" wire:navigate>Metode Pembayaran</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-ui.stat-card label="Menunggu Verifikasi" :value="\App\Support\Money::format($summary['pending'])" color="warning">
            <x-slot:icon><x-ui.icon name="clock" class="size-5" /></x-slot:icon>
        </x-ui.stat-card>
        <x-ui.stat-card label="Terverifikasi" :value="\App\Support\Money::format($summary['verified'])" color="success">
            <x-slot:icon><x-ui.icon name="check" class="size-5" /></x-slot:icon>
        </x-ui.stat-card>
        <x-ui.card>
            <p class="text-sm text-gray-500">Total Pembayaran</p>
            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ \App\Support\Money::format($summary['pending'] + $summary['verified']) }}</p>
        </x-ui.card>
    </div>

    <x-ui.card padding="p-0">
        <div class="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:items-center dark:border-gray-700">
            <div class="sm:w-72"><x-ui.search-input model="search" placeholder="Cari referensi / penghuni..." /></div>
            <select wire:model.live="status" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Semua status</option>
                @foreach (\App\Enums\PaymentStatus::cases() as $option)
                    <option value="{{ $option->value }}">{{ $option->label() }}</option>
                @endforeach
            </select>
            <select wire:model.live="method" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Semua metode</option>
                @foreach ($methods as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        @if ($payments->isEmpty())
            <div class="p-6"><x-ui.empty-state title="Belum ada pembayaran" description="Pembayaran penghuni akan tampil di sini." /></div>
        @else
            <x-ui.table :headings="['Tanggal', 'Penghuni', 'Tagihan', 'Jumlah', 'Metode', 'Status', '']">
                @foreach ($payments as $payment)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $payment->paid_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $payment->tenant?->full_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $payment->invoice?->invoice_number ?? '—' }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ \App\Support\Money::format($payment->amount) }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $payment->method?->name ?? '—' }}</td>
                        <td class="px-4 py-3"><x-ui.status-badge :status="$payment->status" /></td>
                        <td class="px-4 py-3 text-right">
                            <x-ui.button :href="route('admin.payments.show', $payment)" variant="secondary" size="sm" wire:navigate>Detail</x-ui.button>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
            <div class="border-t border-gray-100 p-4 dark:border-gray-700">{{ $payments->links() }}</div>
        @endif
    </x-ui.card>
</div>
