<div class="mx-auto max-w-3xl space-y-6">
    <x-ui.page-header :title="$floor ? 'Ubah Lantai' : 'Tambah Lantai'" description="Informasi lantai pada gedung.">
        <x-slot:actions>
            <x-ui.button :href="route('admin.floors')" variant="secondary" wire:navigate>Kembali</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <form wire:submit="save" class="space-y-6">
        <x-ui.card>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.select name="building_id" label="Gedung" wire:model="building_id" :required="true" :placeholder="null" class="sm:col-span-2">
                    @foreach ($buildings as $b)
                        <option value="{{ $b->id }}">{{ $b->property?->name }} — {{ $b->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input name="name" label="Nama Lantai" wire:model="name" :required="true" />
                <x-ui.input name="level" label="Level" type="number" wire:model="level" :required="true" hint="Contoh: 1, 2, -1 untuk basement" />
                <x-ui.textarea name="description" label="Deskripsi" wire:model="description" class="sm:col-span-2" />
            </div>
        </x-ui.card>

        <div class="flex justify-end gap-3">
            <x-ui.button :href="route('admin.floors')" variant="secondary" wire:navigate>Batal</x-ui.button>
            <x-ui.button type="submit">Simpan</x-ui.button>
        </div>
    </form>
</div>
