<div class="mx-auto max-w-3xl space-y-6">
    <x-ui.page-header title="Move-Out" :description="'Kontrak '.$lease->code.' — '.$lease->tenant?->full_name">
        <x-slot:actions>
            <x-ui.button :href="route('admin.leases.show', $lease)" variant="secondary" wire:navigate>Kembali</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <form wire:submit="process" class="space-y-6">
        <x-ui.card>
            <h3 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Pengakhiran</h3>
            <x-ui.textarea name="termination_reason" label="Alasan Berakhir" wire:model="termination_reason" :required="true" placeholder="Mis. kontrak selesai, penghengkang awal, dsb." />
        </x-ui.card>

        <x-ui.card>
            <h3 class="mb-2 text-sm font-semibold text-gray-900 dark:text-white">Settlement Deposit</h3>
            <p class="mb-4 text-sm text-gray-500">
                Nominal deposit: <span class="font-semibold text-gray-900 dark:text-white">{{ \App\Support\Money::format($lease->deposit?->amount ?? $lease->deposit_amount) }}</span>.
                Potongan + pengembalian tidak boleh melebihi nominal.
            </p>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.money-input model="deduction" label="Potongan / Deduction" />
                <x-ui.money-input model="refund" label="Dikembalikan / Refund" />
                <x-ui.textarea name="notes" label="Catatan Settlement" wire:model="notes" class="sm:col-span-2" placeholder="Mis. kerusakan pintu, sisa tagihan listrik." />
            </div>
        </x-ui.card>

        <div class="flex justify-end gap-3">
            <x-ui.button :href="route('admin.leases.show', $lease)" variant="secondary" wire:navigate>Batal</x-ui.button>
            <x-ui.button type="submit">Proses Move-Out</x-ui.button>
        </div>
    </form>
</div>
