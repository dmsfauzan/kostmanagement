<div class="mx-auto max-w-3xl space-y-6">
    <x-ui.page-header :title="$roomType ? 'Ubah Tipe Kamar' : 'Tambah Tipe Kamar'" description="Harga dan fasilitas bawaan tipe kamar.">
        <x-slot:actions>
            <x-ui.button :href="route('admin.room-types')" variant="secondary" wire:navigate>Kembali</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <form wire:submit="save" class="space-y-6">
        <x-ui.card>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.select name="property_id" label="Properti" wire:model="property_id" :required="true" :options="$properties" :placeholder="null" class="sm:col-span-2" />
                <x-ui.input name="name" label="Nama Tipe" wire:model="name" :required="true" />
                <x-ui.input name="capacity" label="Kapasitas (orang)" type="number" wire:model="capacity" :required="true" />
                <x-ui.money-input model="default_price" label="Harga Sewa" hint="Harga sewa per bulan" />
                <x-ui.money-input model="default_deposit" label="Deposit" />
                <x-ui.input name="size_sqm" label="Luas (m²)" type="number" wire:model="size_sqm" />
                <x-ui.textarea name="description" label="Deskripsi" wire:model="description" class="sm:col-span-2" />
            </div>
        </x-ui.card>

        <x-ui.card>
            <h3 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Fasilitas Bawaan</h3>
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                @forelse ($amenities as $amenity)
                    <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-700">
                        <input type="checkbox" value="{{ $amenity->id }}" wire:model="selectedAmenities" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        {{ $amenity->name }}
                    </label>
                @empty
                    <p class="text-sm text-gray-500">Belum ada fasilitas. Tambahkan di menu Fasilitas.</p>
                @endforelse
            </div>
        </x-ui.card>

        <div class="flex justify-end gap-3">
            <x-ui.button :href="route('admin.room-types')" variant="secondary" wire:navigate>Batal</x-ui.button>
            <x-ui.button type="submit">Simpan</x-ui.button>
        </div>
    </form>
</div>
