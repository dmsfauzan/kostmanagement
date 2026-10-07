<x-layouts.public title="Tentang" description="Tentang pengelolaan kost kami.">
    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
        <x-ui.page-header title="Tentang Kami" description="Pengelolaan kost yang rapi, transparan, dan modern." />

        <div class="mt-8 space-y-5 text-sm leading-7 text-gray-600 dark:text-gray-300">
            <p>
                Kami mengelola properti kost dengan pendekatan yang rapi dan transparan. Mulai dari ketersediaan kamar,
                kontrak sewa, tagihan bulanan, hingga keluhan maintenance — semuanya dicatat dan dapat dipantau.
            </p>
            <p>
                Tujuan kami sederhana: penghuni merasa nyaman dan aman, sementara pengelolaan berjalan efisien dan akurat.
                Setiap pembayaran divertifikasi, setiap keluhan ditindaklanjuti, dan setiap perubahan penting tercatat.
            </p>

            <div class="grid gap-4 pt-4 sm:grid-cols-3">
                @foreach ([
                    ['title' => 'Transparan', 'text' => 'Tagihan dan pembayaran jelas.'],
                    ['title' => 'Responsif', 'text' => 'Keluhan ditangani cepat.'],
                    ['title' => 'Aman', 'text' => 'Data & penghuni terlindungi.'],
                ] as $value)
                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-800">
                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ $value['title'] }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $value['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-layouts.public>
