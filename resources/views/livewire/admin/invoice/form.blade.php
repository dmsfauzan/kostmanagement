<div class="mx-auto max-w-3xl space-y-6">
    <x-ui.page-header title="Tambah Tagihan" description="Buat tagihan manual untuk penghuni.">
        <x-slot:actions>
            <x-ui.button :href="route('admin.invoices')" variant="secondary" wire:navigate>Kembali</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <form wire:submit="save" class="space-y-6">
        <x-ui.card>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.select name="lease_id" label="Kontrak" wire:model="lease_id" :required="true" placeholder="Pilih kontrak..." class="sm:col-span-2">
                    @foreach ($leases as $lease)
                        <option value="{{ $lease->id }}">{{ $lease->tenant?->full_name }} — {{ $lease->room?->number }} ({{ $lease->code }})</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input name="period_start" label="Periode Mulai" type="date" wire:model="period_start" />
                <x-ui.input name="period_end" label="Periode Berakhir" type="date" wire:model="period_end" />
                <x-ui.textarea name="notes" label="Catatan" wire:model="notes" class="sm:col-span-2" />
            </div>
        </x-ui.card>

        <x-ui.card>
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Item Tagihan</h3>
                <x-ui.button type="button" wire:click="addItem" variant="secondary" size="sm">
                    <x-ui.icon name="plus" class="size-4" /> Tambah Item
                </x-ui.button>
            </div>

            <div class="space-y-4">
                @foreach ($items as $index => $item)
                    <div class="grid gap-3 border-b border-gray-100 pb-4 sm:grid-cols-12 dark:border-gray-700">
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-medium text-gray-500">Jenis</label>
                            <select wire:model="items.{{ $index }}.type" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                @foreach ($types as $type)
                                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                @endforeach
                            </select>
                            @error("items.$index.type")<p class="text-xs text-danger-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-4">
                            <label class="block text-xs font-medium text-gray-500">Deskripsi</label>
                            <input type="text" wire:model="items.{{ $index }}.description" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            @error("items.$index.description")<p class="text-xs text-danger-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-500">Qty</label>
                            <input type="number" min="1" wire:model="items.{{ $index }}.quantity" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            @error("items.$index.quantity")<p class="text-xs text-danger-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-500">Harga</label>
                            <input type="number" min="0" wire:model="items.{{ $index }}.unit_price" class="mt-1 block w-full rounded-lg border-gray-300 text-right text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            @error("items.$index.unit_price")<p class="text-xs text-danger-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="flex items-end sm:col-span-1">
                            <button type="button" wire:click="removeItem({{ $index }})" class="rounded-lg p-2 text-danger-600 hover:bg-danger-50 dark:hover:bg-danger-950" title="Hapus">
                                <x-ui.icon name="x-mark" class="size-4" />
                            </button>
                        </div>
                    </div>
                @endforeach
                @error('items')<p class="text-sm text-danger-600">{{ $message }}</p>@enderror
            </div>
        </x-ui.card>

        <div class="flex justify-end gap-3">
            <x-ui.button :href="route('admin.invoices')" variant="secondary" wire:navigate>Batal</x-ui.button>
            <x-ui.button type="submit">Simpan Tagihan</x-ui.button>
        </div>
    </form>
</div>
