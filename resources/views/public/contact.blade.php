<x-layouts.public title="Kontak" description="Hubungi pengelola kost.">
    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
        <x-ui.page-header title="Kontak" description="Kami siap membantu Anda." />

        <div class="mt-8 grid gap-6 sm:grid-cols-2">
            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800">
                <span class="inline-flex size-11 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-950 dark:text-primary-400">
                    <x-ui.icon name="building-office" class="size-6" />
                </span>
                <h3 class="mt-4 font-semibold text-gray-900 dark:text-white">Alamat</h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Silakan hubungi pengelola untuk alamat lengkap properti.</p>
            </div>

            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800">
                <span class="inline-flex size-11 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-950 dark:text-primary-400">
                    <x-ui.icon name="user" class="size-6" />
                </span>
                <h3 class="mt-4 font-semibold text-gray-900 dark:text-white">Pengelola</h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Penghuni dapat mengirim keluhan langsung melalui portal penghuni.</p>
            </div>
        </div>

        <div class="mt-8 rounded-xl border border-primary-100 bg-primary-50 p-6 text-center dark:border-primary-900 dark:bg-primary-950">
            <p class="text-sm text-primary-800 dark:text-primary-200">Sudah menjadi penghuni? Masuk ke portal untuk melihat tagihan dan mengirim keluhan.</p>
            <div class="mt-4">
                <x-ui.button href="{{ route('login') }}">Masuk Portal</x-ui.button>
            </div>
        </div>
    </div>
</x-layouts.public>
