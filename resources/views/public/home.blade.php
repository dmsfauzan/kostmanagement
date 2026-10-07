<x-layouts.public
    title="Kost Nyaman, Pengelolaan Modern"
    description="Sistem manajemen kost untuk hunian, kontrak, tagihan, pembayaran, dan maintenance."
>
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 -z-10 bg-gradient-to-b from-primary-50 via-white to-white dark:from-primary-950 dark:via-gray-900 dark:to-gray-900"></div>

        <div class="mx-auto max-w-4xl px-4 py-20 text-center sm:px-6 lg:px-8 lg:py-28">
            <span class="inline-flex items-center gap-2 rounded-full bg-primary-100 px-3 py-1 text-xs font-medium text-primary-700 dark:bg-primary-900 dark:text-primary-300">
                <x-ui.icon name="building-office" class="size-4" />
                Manajemen Kost Terpadu
            </span>

            <h1 class="mt-6 text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl dark:text-white">
                Kelola kost Anda dengan <span class="text-primary-600">lebih rapi</span>
            </h1>

            <p class="mx-auto mt-5 max-w-2xl text-lg leading-8 text-gray-600 dark:text-gray-300">
                Pantau kamar, penghuni, kontrak, tagihan, pembayaran, sampai keluhan maintenance dalam satu sistem
                yang cepat dan aman.
            </p>

            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <x-ui.button href="{{ route('login') }}" size="lg">Masuk ke Portal</x-ui.button>
                <x-ui.button href="#fitur" variant="secondary" size="lg">Lihat Fitur</x-ui.button>
            </div>
        </div>
    </section>

    <section id="fitur" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Semua yang Anda butuhkan</h2>
            <p class="mt-3 text-gray-600 dark:text-gray-400">
                Satu platform untuk seluruh operasional kost, dari hunian sampai keuangan.
            </p>
        </div>

        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                ['icon' => 'key', 'title' => 'Kamar & Properti', 'text' => 'Kelola properti, gedung, lantai, tipe kamar, harga, dan status hunian.'],
                ['icon' => 'user-group', 'title' => 'Penghuni & Kontrak', 'text' => 'Data penghuni, dokumen, kontrak sewa, check-in, dan check-out.'],
                ['icon' => 'banknotes', 'title' => 'Tagihan & Pembayaran', 'text' => 'Tagihan otomatis bulanan, verifikasi pembayaran, dan bukti transfer.'],
                ['icon' => 'receipt-percent', 'title' => 'Pengeluaran', 'text' => 'Catat biaya operasional: listrik, air, gaji, kebersihan, dan lainnya.'],
                ['icon' => 'wrench', 'title' => 'Maintenance', 'text' => 'Kelola keluhan penghuni dengan prioritas, penugasan, dan riwayat.'],
                ['icon' => 'chart-bar', 'title' => 'Laporan', 'text' => 'Laporan hunian dan keuangan yang siap diunduh dalam beberapa format.'],
            ] as $feature)
                <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-800">
                    <span class="inline-flex size-11 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-950 dark:text-primary-400">
                        <x-ui.icon :name="$feature['icon']" class="size-6" />
                    </span>
                    <h3 class="mt-4 font-semibold text-gray-900 dark:text-white">{{ $feature['title'] }}</h3>
                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $feature['text'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section id="kontak" class="bg-gray-50 dark:bg-gray-800/40">
        <div class="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Siap mengelola kost lebih efisien?</h2>
            <p class="mt-3 text-gray-600 dark:text-gray-400">
                Masuk menggunakan akun yang diberikan pengelola untuk mulai mengelola.
            </p>
            <div class="mt-6 flex justify-center">
                <x-ui.button href="{{ route('login') }}" size="lg">Masuk Sekarang</x-ui.button>
            </div>
        </div>
    </section>
</x-layouts.public>
