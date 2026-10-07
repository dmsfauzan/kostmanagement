<div class="mx-auto max-w-3xl space-y-6">
    <x-ui.page-header :title="$room ? 'Ubah Kamar' : 'Tambah Kamar'" description="Isi data kamar, harga, dan status hunian.">
        <x-slot:actions>
            <x-ui.button :href="route('admin.rooms')" variant="secondary" wire:navigate>Kembali</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <form wire:submit="save" class="space-y-6">
        <x-ui.card>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.select name="property_id" label="Properti" wire:model.live="property_id" :required="true" :options="$properties" :placeholder="null" class="sm:col-span-2" />
                <x-ui.select name="building_id" label="Gedung" wire:model.live="building_id" :required="true" :options="$buildings" :placeholder="null" />
                <x-ui.select name="floor_id" label="Lantai" wire:model.live="floor_id" :required="true" :placeholder="null">
                    @foreach ($floors as $floor)
                        <option value="{{ $floor->id }}">{{ $floor->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input name="number" label="Nomor Kamar" wire:model="number" :required="true" placeholder="Mis. A-101" />
                <x-ui.select name="room_type_id" label="Tipe Kamar" wire:model.live="room_type_id" :options="$roomTypes" :placeholder="null" />
                <x-ui.money-input model="price" label="Harga / Bulan" :required="true" />
                <x-ui.money-input model="deposit" label="Deposit" hint="Dana yang ditahan" />
                <x-ui.input name="size_sqm" label="Luas (m²)" type="number" wire:model="size_sqm" />
                <x-ui.select name="status" label="Status" wire:model="status" :required="true" :options="collect($statuses)->mapWithKeys(fn($s) => [$s->value => $s->label()])->all()" :placeholder="null" />
                <x-ui.textarea name="description" label="Deskripsi" wire:model="description" class="sm:col-span-2" />
            </div>
        </x-ui.card>

        <x-ui.card>
            <h3 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Fasilitas Kamar</h3>
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                @forelse ($amenities as $amenity)
                    <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-700">
                        <input type="checkbox" value="{{ $amenity->id }}" wire:model="selectedAmenities" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        {{ $amenity->name }}
                    </label>
                @empty
                    <p class="text-sm text-gray-500">Belum ada fasilitas.</p>
                @endforelse
            </div>
        </x-ui.card>

        <x-ui.card>
            <h3 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Foto</h3>
            @if ($room && $room->photos->isNotEmpty())
                <div class="mb-4 grid grid-cols-3 gap-2">
                    @foreach ($room->photos as $photo)
                        <img src="{{ $photo->url() }}" alt="Foto {{ $room->number }}" class="h-24 w-full rounded-lg object-cover">
                    @endforeach
                </div>
            @endif
            <x-ui.file-upload model="photos" label="Foto Kamar" :multiple="true" />
        </x-ui.card>

        <div class="flex justify-end gap-3">
            <x-ui.button :href="route('admin.rooms')" variant="secondary" wire:navigate>Batal</x-ui.button>
            <x-ui.button type="submit">Simpan</x-ui.button>
        </div>
    </form>
</div>
