<div class="space-y-6">
    <x-ui.page-header :title="'Pembayaran #'.$payment->id" :description="\App\Support\Money::format($payment->amount).' untuk '.$payment->invoice?->invoice_number">
        <x-slot:actions>
            <x-ui.button :href="route('admin.payments')" variant="secondary" wire:navigate>Kembali</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-2">
            <x-slot:header><h3 class="text-sm font-semibold text-gray-900 dark:text-white">Detail Pembayaran</h3></x-slot:header>
            <dl class="grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-400">Status</dt>
                    <dd class="mt-1"><x-ui.status-badge :status="$payment->status" /></dd>
                </div>
                @foreach ([
                    'Jumlah' => \App\Support\Money::format($payment->amount),
                    'Tanggal Bayar' => $payment->paid_at->translatedFormat('d F Y'),
                    'Penghuni' => $payment->tenant?->full_name,
                    'Tagihan' => $payment->invoice?->invoice_number,
                    'Metode' => $payment->method?->name,
                    'Referensi' => $payment->reference,
                    'Catatan' => $payment->notes,
                ] as $label => $value)
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-gray-400">{{ $label }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $value ?: '—' }}</dd>
                    </div>
                @endforeach
                @if ($payment->rejection_reason)
                    <div class="sm:col-span-2"><dt class="text-xs uppercase tracking-wide text-gray-400">Alasan Ditolak</dt><dd class="mt-1 text-sm text-danger-600">{{ $payment->rejection_reason }}</dd></div>
                @endif
            </dl>
        </x-ui.card>

        <x-ui.card>
            <x-slot:header><h3 class="text-sm font-semibold text-gray-900 dark:text-white">Aksi</h3></x-slot:header>
            <div class="space-y-3">
                @if ($payment->proof_path)
                    <x-ui.button :href="route('admin.payments.proof', $payment)" variant="secondary" class="w-full">Unduh Bukti</x-ui.button>
                @else
                    <p class="text-sm text-gray-500">Tidak ada bukti pembayaran.</p>
                @endif

                @if ($payment->isPending())
                    @can('payment.verify')
                        <x-ui.button wire:click="verify" class="w-full">Verifikasi Pembayaran</x-ui.button>
                    @endcan
                    @can('payment.reject')
                        <form wire:submit="reject" class="space-y-2">
                            <x-ui.textarea name="rejection_reason" label="Alasan Ditolak" wire:model="rejection_reason" :required="true" />
                            <x-ui.button type="submit" variant="danger" class="w-full">Tolak Pembayaran</x-ui.button>
                        </form>
                    @endcan
                @endif

                @if ($payment->isVerified())
                    @can('payment.verify')
                        <x-ui.confirm-dialog title="Kembalikan pembayaran" message="Pembayaran akan dikembalikan dan tagihan diperbarui." action="refund">
                            <x-slot:trigger>
                                <button type="button" class="w-full rounded-lg bg-warning-500 px-4 py-2 text-sm font-semibold text-white hover:bg-warning-600">Refund</button>
                            </x-slot:trigger>
                        </x-ui.confirm-dialog>
                    @endcan
                @endif

                @if ($payment->isPending())
                    @can('payment.verify')
                        <x-ui.confirm-dialog title="Batalkan pembayaran" message="Pembayaran akan dibatalkan." action="cancel">
                            <x-slot:trigger>
                                <button type="button" class="w-full rounded-lg bg-white px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:ring-gray-600">Batalkan</button>
                            </x-slot:trigger>
                        </x-ui.confirm-dialog>
                    @endcan
                @endif
            </div>
        </x-ui.card>
    </div>
</div>
