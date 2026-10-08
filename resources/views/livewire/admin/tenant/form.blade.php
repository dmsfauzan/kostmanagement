<div class="mx-auto max-w-3xl space-y-6">
    <x-ui.page-header :title="$tenant ? 'Ubah Penghuni' : 'Tambah Penghuni'" description="Data pribadi, kontak darurat, dan kendaraan.">
        <x-slot:actions>
            <x-ui.button :href="route('admin.tenants')" variant="secondary" wire:navigate>Kembali</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <form wire:submit="save" class="space-y-6">
        <x-ui.card>
            <h3 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Data Pribadi</h3>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.input name="full_name" label="Nama Lengkap" wire:model="full_name" :required="true" class="sm:col-span-2" />
                <x-ui.input name="nik" label="NIK" wire:model="nik" />
                <x-ui.select name="gender" label="Jenis Kelamin" wire:model="gender" :options="collect($genders)->mapWithKeys(fn($g) => [$g->value => $g->label()])->all()" placeholder="Pilih..." />
                <x-ui.input name="birth_date" label="Tanggal Lahir" type="date" wire:model="birth_date" />
                <x-ui.select name="property_id" label="Properti" wire:model="property_id" :options="$properties" placeholder="Pilih properti..." />
                <x-ui.input name="phone" label="Telepon" wire:model="phone" :required="true" />
                <x-ui.input name="email" label="Email" type="email" wire:model="email" :required="! $tenant" hint="Digunakan untuk undangan akun portal." />
                <x-ui.textarea name="address" label="Alamat" wire:model="address" class="sm:col-span-2" />
            </div>
        </x-ui.card>

        <x-ui.card>
            <h3 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Kontak Darurat & Kendaraan</h3>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.input name="emergency_name" label="Nama Kontak Darurat" wire:model="emergency_name" />
                <x-ui.input name="emergency_relationship" label="Hubungan" wire:model="emergency_relationship" />
                <x-ui.input name="emergency_phone" label="Telepon Darurat" wire:model="emergency_phone" />
                <x-ui.input name="vehicle_type" label="Jenis Kendaraan" wire:model="vehicle_type" placeholder="Motor / Mobil" />
                <x-ui.input name="vehicle_number" label="Nomor Kendaraan" wire:model="vehicle_number" />
                <x-ui.textarea name="notes" label="Catatan" wire:model="notes" class="sm:col-span-2" />
            </div>
        </x-ui.card>

        @unless ($tenant)
            <x-ui.card>
                <label class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" wire:model="skip_invite" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    Jangan kirim undangan aktivasi sekarang
                </label>
            </x-ui.card>
        @endunless

        <div class="flex justify-end gap-3">
            <x-ui.button :href="route('admin.tenants')" variant="secondary" wire:navigate>Batal</x-ui.button>
            <x-ui.button type="submit">Simpan</x-ui.button>
        </div>
    </form>
</div>
