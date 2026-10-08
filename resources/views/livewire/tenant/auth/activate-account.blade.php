<div>
    <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Aktivasi Akun Penghuni</h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        Atur nama dan kata sandi Anda untuk mengaktifkan akses portal penghuni.
    </p>

    <form wire:submit="activate" class="mt-6 space-y-4">
        <x-ui.input name="name" label="Nama Lengkap" wire:model="name" :required="true" />

        <div>
            <x-ui.input name="password" label="Kata Sandi" type="password" wire:model="password" :required="true" autocomplete="new-password" />
        </div>

        <x-ui.input name="password_confirmation" label="Konfirmasi Kata Sandi" type="password" wire:model="password_confirmation" :required="true" autocomplete="new-password" />

        <x-ui.button type="submit" class="w-full">Aktifkan Akun</x-ui.button>
    </form>
</div>
