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
                <x-ui.select name="tenant_id" label="Penghuni" wire:model="tenant_id" :required="true" placeholder="Pilih penghuni...">
                    @foreach ($tenants as $tenant)
                        <option value="{{ $tenant->id }}">{{ $tenant->full_name }} — {{ $tenant->phone }}</option>
                    @endforeach
                </x-ui.select>
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
