<div class="mx-auto max-w-3xl space-y-6">
    <x-ui.page-header :title="$announcement ? 'Ubah Pengumuman' : 'Tambah Pengumuman'" description="Tulis pengumuman dan pilih target penerima.">
        <x-slot:actions>
            <x-ui.button :href="route('admin.announcements')" variant="secondary" wire:navigate>Kembali</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <form wire:submit="save" class="space-y-6">
        <x-ui.card>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.input name="title" label="Judul" wire:model="title" :required="true" class="sm:col-span-2" />
                <x-ui.textarea name="body" label="Isi Pengumuman" wire:model="body" :required="true" rows="6" class="sm:col-span-2" />

                <x-ui.select name="target_type" label="Target" wire:model.live="target_type" :required="true" :options="collect($targets)->mapWithKeys(fn($t) => [$t->value => $t->label()])->all()" :placeholder="null" />
                @if (! empty($targetOptions))
                    <x-ui.select name="target_id" label="Pilih Target" wire:model="target_id" :options="$targetOptions" placeholder="Pilih..." />
                @endif

                <x-ui.input name="publish_at" label="Jadwal Terbit" type="datetime-local" wire:model="publish_at" hint="Kosongkan untuk terbit saat ini" />
                <x-ui.input name="expires_at" label="Kedaluwarsa" type="datetime-local" wire:model="expires_at" hint="Kosongkan untuk tanpa batas" />

                <div class="sm:col-span-2">
                    <x-ui.file-upload model="attachment" label="Lampiran (opsional)" accept="image/*,.pdf" hint="JPG, PNG, WebP, atau PDF hingga 4 MB" />
                </div>
            </div>
        </x-ui.card>

        <div class="flex justify-end gap-3">
            <x-ui.button :href="route('admin.announcements')" variant="secondary" wire:navigate>Batal</x-ui.button>
            <x-ui.button type="submit">Simpan</x-ui.button>
        </div>
    </form>
</div>
