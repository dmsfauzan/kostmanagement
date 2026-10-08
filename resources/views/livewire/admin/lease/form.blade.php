<div class="mx-auto max-w-3xl space-y-6">
    <x-ui.page-header :title="$lease ? 'Ubah Kontrak' : 'Tambah Kontrak'" description="Buat atau ubah kontrak penghuni dengan kamar pada periode tertentu.">
        <x-slot:actions>
            <x-ui.button :href="route('admin.leases')" variant="secondary" wire:navigate>Kembali</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <form wire:submit="save" class="space-y-6">
        <x-ui.card>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.select name="property_id" label="Properti" wire:model.live="property_id" :required="true" :options="$properties" :placeholder="null" />
                <div class="space-y-1.5 sm:col-span-2" x-data="{ open: false }">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Penghuni <span class="text-danger-500">*</span></label>
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.300ms="tenantSearch" @focus="open = true" placeholder="Ketik untuk mencari penghuni (maks 50 hasil)..." class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400">
                        <div x-show="open" @click.outside="open = false" class="absolute z-10 mt-1 max-h-52 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-800">
                            @forelse ($tenants as $tenant)
                                <button type="button" wire:click="$set('tenant_id', {{ $tenant->id }})" @click="open = false" class="block w-full px-3 py-2 text-left text-sm {{ $tenant_id == $tenant->id ? 'font-semibold text-primary-600' : 'text-gray-700 dark:text-gray-200' }} hover:bg-gray-50 dark:hover:bg-gray-700">
                                    {{ $tenant->full_name }} <span class="text-xs text-gray-400">· {{ $tenant->phone }}</span>
                                </button>
                            @empty
                                <p class="px-3 py-2 text-sm text-gray-400">Tidak ada penghuni.</p>
                            @endforelse
                        </div>
                    </div>
                    @php $pickedTenant = $tenants->firstWhere('id', $tenant_id); @endphp
                    @if ($pickedTenant)
                        <p class="text-xs text-gray-500">Terpilih: <span class="font-medium">{{ $pickedTenant->full_name }}</span></p>
                    @endif
                    @error('tenant_id')<p class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>@enderror
                </div>
                <x-ui.select name="room_id" label="Kamar" wire:model.live="room_id" :required="true" :options="$rooms->mapWithKeys(fn($r) => [$r->id => $r->number.' · '.\App\Support\Money::format($r->price)])" placeholder="Pilih kamar..." class="sm:col-span-2" />
                <x-ui.input name="start_date" label="Tanggal Mulai" type="date" wire:model="start_date" :required="true" />
                <x-ui.input name="end_date" label="Tanggal Berakhir" type="date" wire:model="end_date" :required="true" />
                <x-ui.money-input model="rent_amount" label="Sewa / Bulan" :required="true" />
                <x-ui.money-input model="deposit_amount" label="Deposit" />
                <x-ui.select name="billing_cycle" label="Siklus Penagihan" wire:model="billing_cycle" :required="true" :options="collect($billingCycles)->mapWithKeys(fn($b) => [$b->value => $b->label()])->all()" :placeholder="null" />
                <x-ui.input name="due_day" label="Hari Jatuh Tempo (1-28)" type="number" wire:model="due_day" :required="true" />
                <x-ui.select name="late_fee_type" label="Jenis Denda" wire:model="late_fee_type" :required="true" :options="collect($lateFeeTypes)->mapWithKeys(fn($t) => [$t->value => $t->label()])->all()" :placeholder="null" />
                <x-ui.money-input model="late_fee_value" label="Nilai Denda" hint="Rupiah per hari atau persen (mis. 50000 atau 5)" />
                <x-ui.textarea name="notes" label="Catatan" wire:model="notes" class="sm:col-span-2" />
            </div>
        </x-ui.card>

        <div class="flex justify-end gap-3">
            <x-ui.button :href="route('admin.leases')" variant="secondary" wire:navigate>Batal</x-ui.button>
            <x-ui.button type="submit">Simpan Kontrak</x-ui.button>
        </div>
    </form>
</div>
