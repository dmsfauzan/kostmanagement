<x-layouts.tenant title="Beranda">
    <div class="rounded-xl bg-primary-600 p-5 text-white">
        <p class="text-sm text-primary-100">Halo,</p>
        <p class="text-lg font-semibold">{{ auth()->user()->name }}</p>
        <p class="mt-1 text-sm text-primary-100">Senang melihat Anda kembali.</p>
    </div>

    <x-ui.card padding="p-0">
        <div class="flex items-center gap-3 p-5">
            <span class="inline-flex size-11 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                <x-ui.icon name="key" class="size-6" />
            </span>
            <div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Kamar Saya</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada kamar yang terhubung.</p>
            </div>
        </div>
    </x-ui.card>

    <x-ui.card padding="p-0">
        <div class="flex items-center gap-3 p-5">
            <span class="inline-flex size-11 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                <x-ui.icon name="banknotes" class="size-6" />
            </span>
            <div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Tagihan Aktif</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada tagihan saat ini.</p>
            </div>
        </div>
    </x-ui.card>

    <x-ui.card>
        <div class="flex items-start gap-3">
            <span class="inline-flex size-11 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-950 dark:text-primary-400">
                <x-ui.icon name="megaphone" class="size-6" />
            </span>
            <div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Pengumuman</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Belum ada pengumuman terbaru.</p>
            </div>
        </div>
    </x-ui.card>
</x-layouts.tenant>
