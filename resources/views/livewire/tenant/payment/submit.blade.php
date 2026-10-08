<div class="mx-auto max-w-xl space-y-6">
    <x-ui.page-header title="Bayar Tagihan" description="Kirim bukti pembayaran Anda untuk diverifikasi." />

    <form wire:submit="save" class="space-y-6">
        <x-ui.card>
            <div class="space-y-4">
                <x-ui.select name="invoice_id" label="Tagihan" wire:model.live="invoice_id" :required="true" placeholder="Pilih tagihan...">
                    @foreach ($invoices as $invoice)
                        <option value="{{ $invoice->id }}">{{ $invoice->invoice_number }} · {{ $invoice->lease?->room?->number }} · Sisa {{ \App\Support\Money::format($invoice->amount_due) }}</option>
                    @endforeach
                </x-ui.select>

                <x-ui.money-input model="amount" label="Nominal Pembayaran" :required="true" />

                <x-ui.input name="paid_at" label="Tanggal Bayar" type="date" wire:model="paid_at" :required="true" />

                <x-ui.select name="payment_method_id" label="Metode Pembayaran" wire:model.live="payment_method_id" :options="$methods->pluck('name', 'id')" placeholder="Pilih metode..." />

                @if ($methods->firstWhere('id', $payment_method_id))
                    <p class="rounded-lg bg-gray-50 p-3 text-xs text-gray-500 dark:bg-gray-700 dark:text-gray-300">
                        {{ $methods->firstWhere('id', $payment_method_id)->instructions ?? 'Transfer ke nomor yang tertera, lalu unggah bukti.' }}
                    </p>
                @endif

                <x-ui.input name="reference" label="Nomor Referensi" wire:model="reference" placeholder="Mis. nomor transaksi bank" />

                <x-ui.file-upload model="proof" label="Bukti Pembayaran" accept="image/*,.pdf" hint="JPG, PNG, WebP, atau PDF hingga 4 MB" />

                <x-ui.textarea name="notes" label="Catatan" wire:model="notes" />
            </div>
        </x-ui.card>

        <x-ui.button type="submit" class="w-full">Kirim Bukti Pembayaran</x-ui.button>
    </form>
</div>
