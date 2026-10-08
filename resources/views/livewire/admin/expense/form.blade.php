<div class="mx-auto max-w-3xl space-y-6">
    <x-ui.page-header :title="$expense ? 'Ubah Pengeluaran' : 'Tambah Pengeluaran'" description="Catat detail biaya operasional.">
        <x-slot:actions>
            <x-ui.button :href="route('admin.expenses')" variant="secondary" wire:navigate>Kembali</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <form wire:submit="save" class="space-y-6">
        <x-ui.card>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.select name="property_id" label="Properti" wire:model="property_id" :required="true" :options="$properties" :placeholder="null" class="sm:col-span-2" />
                <x-ui.select name="expense_category_id" label="Kategori" wire:model="expense_category_id" :options="$categories" placeholder="Pilih kategori..." />
                <x-ui.money-input model="amount" label="Nominal" :required="true" />
                <x-ui.input name="expense_date" label="Tanggal" type="date" wire:model="expense_date" :required="true" />
                <x-ui.input name="vendor" label="Vendor / Penerima" wire:model="vendor" />
                <x-ui.textarea name="description" label="Deskripsi" wire:model="description" class="sm:col-span-2" />
                <div class="sm:col-span-2">
                    <x-ui.file-upload model="receipt" label="Struk / Bukti (opsional)" accept="image/*,.pdf" hint="JPG, PNG, WebP, atau PDF hingga 4 MB" />
                    @if ($expense?->receipt_path)
                        <a href="{{ route('admin.expenses.receipt', $expense) }}" class="mt-2 inline-block text-sm font-medium text-primary-600">Unduh struk saat ini</a>
                    @endif
                </div>
            </div>
        </x-ui.card>

        <div class="flex justify-end gap-3">
            <x-ui.button :href="route('admin.expenses')" variant="secondary" wire:navigate>Batal</x-ui.button>
            <x-ui.button type="submit">Simpan</x-ui.button>
        </div>
    </form>
</div>
