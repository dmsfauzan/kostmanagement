<div class="space-y-4">
    <x-ui.page-header title="Buat Keluhan" description="Jelaskan masalah yang Anda alami." />

    <form wire:submit="save" class="space-y-4">
        <x-ui.card>
            <div class="space-y-4">
                <x-ui.input name="title" label="Judul" wire:model="title" :required="true" placeholder="Mis. AC tidak dingin" />

                <div class="grid grid-cols-2 gap-4">
                    <x-ui.select name="category" label="Kategori" wire:model="category" :required="true" :options="collect($categories)->mapWithKeys(fn($c) => [$c->value => $c->label()])->all()" :placeholder="null" />
                    <x-ui.select name="priority" label="Prioritas" wire:model="priority" :required="true" :options="collect($priorities)->mapWithKeys(fn($p) => [$p->value => $p->label()])->all()" :placeholder="null" />
                </div>

                <x-ui.textarea name="description" label="Deskripsi" wire:model="description" :required="true" rows="4" placeholder="Jelaskan detail masalahnya..." />

                <x-ui.file-upload model="photos" label="Foto (opsional)" :multiple="true" hint="Maksimal 5 gambar" />
            </div>
        </x-ui.card>

        <x-ui.button type="submit" class="w-full">Kirim Keluhan</x-ui.button>
    </form>
</div>
