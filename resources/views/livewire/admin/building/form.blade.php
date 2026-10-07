<div class="mx-auto max-w-3xl space-y-6">
    <x-ui.page-header :title="$building ? 'Ubah Gedung' : 'Tambah Gedung'" description="Informasi gedung pada properti.">
        <x-slot:actions>
            <x-ui.button :href="route('admin.buildings')" variant="secondary" wire:navigate>Kembali</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <form wire:submit="save" class="space-y-6">
        <x-ui.card>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.select name="property_id" label="Properti" wire:model="property_id" :required="true" :options="$properties" :placeholder="null" class="sm:col-span-2" />
                <x-ui.input name="name" label="Nama Gedung" wire:model="name" :required="true" />
                <x-ui.input name="code" label="Kode" wire:model="code" hint="Contoh: A, B, UTAMA" />
                <x-ui.input name="floor_count" label="Jumlah Lantai" type="number" wire:model="floor_count" />
                <x-ui.select name="status" label="Status" wire:model="status" :required="true" :options="collect($statuses)->mapWithKeys(fn($s) => [$s->value => $s->label()])->all()" :placeholder="null" />
                <x-ui.textarea name="description" label="Deskripsi" wire:model="description" class="sm:col-span-2" />
            </div>
        </x-ui.card>

        <div class="flex justify-end gap-3">
            <x-ui.button :href="route('admin.buildings')" variant="secondary" wire:navigate>Batal</x-ui.button>
            <x-ui.button type="submit">Simpan</x-ui.button>
        </div>
    </form>
</div>
