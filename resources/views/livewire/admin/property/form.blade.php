<div class="mx-auto max-w-3xl space-y-6">
    <x-ui.page-header
        :title="$property ? 'Ubah Properti' : 'Tambah Properti'"
        description="Lengkapi informasi properti kost."
    >
        <x-slot:actions>
            <x-ui.button :href="route('admin.properties')" variant="secondary" wire:navigate>Kembali</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <form wire:submit="save" class="space-y-6">
        <x-ui.card>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.input name="name" label="Nama Properti" wire:model="name" :required="true" class="sm:col-span-2" />
                <x-ui.input name="phone" label="Telepon" wire:model="phone" />
                <x-ui.input name="email" label="Email" type="email" wire:model="email" />
                <x-ui.input name="address" label="Alamat" wire:model="address" class="sm:col-span-2" />
                <x-ui.input name="city" label="Kota" wire:model="city" />
                <x-ui.input name="province" label="Provinsi" wire:model="province" />
                <x-ui.input name="postal_code" label="Kode Pos" wire:model="postal_code" />
                <x-ui.select name="status" label="Status" wire:model="status" :required="true" :options="collect($statuses)->mapWithKeys(fn($s) => [$s->value => $s->label()])->all()" :placeholder="null" />
                <x-ui.textarea name="description" label="Deskripsi" wire:model="description" class="sm:col-span-2" />
            </div>
        </x-ui.card>

        <x-ui.card>
            <h3 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Berkas</h3>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-2">
                    <x-ui.file-upload model="logo" label="Logo" hint="1 gambar, maks 4 MB" />
                    @if ($existingLogo && ! $logo)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk(config('kost.disk.public'))->url($existingLogo) }}" alt="Logo" class="h-16 rounded-lg object-cover">
                    @endif
                </div>
                <div class="space-y-2">
                    <x-ui.file-upload model="cover" label="Gambar Sampul" hint="1 gambar, maks 4 MB" />
                    @if ($existingCover && ! $cover)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk(config('kost.disk.public'))->url($existingCover) }}" alt="Cover" class="h-16 rounded-lg object-cover">
                    @endif
                </div>
            </div>
        </x-ui.card>

        <div class="flex justify-end gap-3">
            <x-ui.button :href="route('admin.properties')" variant="secondary" wire:navigate>Batal</x-ui.button>
            <x-ui.button type="submit">Simpan</x-ui.button>
        </div>
    </form>
</div>
