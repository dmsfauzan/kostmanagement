<x-layouts.public
    title="Kost Nyaman, Pengelolaan Modern"
    description="Sistem manajemen kost untuk hunian, kontrak, tagihan, pembayaran, dan maintenance."
>
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 -z-10 bg-gradient-to-b from-primary-50 via-white to-white dark:from-primary-950 dark:via-gray-900 dark:to-gray-900"></div>

        <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
            <div class="grid gap-10 lg:grid-cols-2 lg:items-center">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-primary-100 px-3 py-1 text-xs font-medium text-primary-700 dark:bg-primary-900 dark:text-primary-300">
                        <x-ui.icon name="building-office" class="size-4" />
                        Properti Terkelola
                    </span>

                    <h1 class="mt-4 text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl dark:text-white">
                        Kelola kost Anda dengan <span class="text-primary-600">lebih rapi</span>
                    </h1>

                    <p class="mt-4 text-lg leading-8 text-gray-600 dark:text-gray-300">
                        @if ($property)
                            Temukan hunian yang tepat di {{ $property->name }} dengan {{ $property->rooms_count }} pilihan kamar —
                        @endif
                        kelola operasional kost dari satu tempat, mulai dari hunian sampai keuangan.
                    </p>

                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        <x-ui.button href="{{ route('rooms') }}" size="lg">Lihat Kamar</x-ui.button>
                        <x-ui.button href="#fasilitas" variant="secondary" size="lg">Fasilitas</x-ui.button>
                    </div>

                    @if ($property)
                        <div class="mt-6 flex gap-6 text-sm">
                            <div>
                                <span class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $property->rooms_count }}</span>
                                <span class="ml-1 text-gray-500">kamar</span>
                            </div>
                            <div>
                                <span class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $property->buildings_count }}</span>
                                <span class="ml-1 text-gray-500">gedung</span>
                            </div>
                        </div>
                    @endif
                </div>

                @if ($featuredRooms->isNotEmpty())
                    <div class="grid grid-cols-2 gap-4">
                        @foreach ($featuredRooms->take(4) as $room)
                            <a href="{{ route('rooms.show', $room->slug) }}" class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $room->number }}</p>
                                <p class="text-xs text-gray-500">{{ $room->roomType?->name ?? 'Standard' }} · {{ $room->building?->name }}</p>
                                <p class="mt-2 text-sm font-semibold text-primary-600">{{ \App\Support\Money::format($room->price) }}<span class="text-xs font-normal text-gray-400">/bulan</span></p>
                                <x-ui.status-badge :status="$room->status" class="mt-2" />
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Kamar Tersedia</h2>
            <p class="mt-3 text-gray-600 dark:text-gray-400">Hunian yang siap ditempati hari ini.</p>
        </div>

        @if ($featuredRooms->isEmpty())
            <div class="mx-auto mt-10 max-w-xl">
                <x-ui.empty-state title="Belum ada kamar tersedia" description="Kamar akan tampil di sini saat tersedia." />
            </div>
        @else
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featuredRooms as $room)
                    <a href="{{ route('rooms.show', $room->slug) }}" class="group rounded-xl border border-gray-100 bg-white p-6 shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-lg font-semibold text-gray-900 group-hover:text-primary-600 dark:text-white dark:group-hover:text-primary-400">{{ $room->number }}</p>
                                <p class="text-sm text-gray-500">{{ $room->building?->name }} · {{ $room->floor?->level ? 'Lantai '.$room->floor->level : '' }}</p>
                            </div>
                            <x-ui.status-badge :status="$room->status" />
                        </div>
                        <p class="mt-3 text-base font-semibold text-gray-900 dark:text-white">{{ \App\Support\Money::format($room->price) }}<span class="text-sm font-normal text-gray-400">/bulan</span></p>
                        <p class="text-xs text-gray-500">{{ $room->roomType?->name ?? 'Tanpa tipe' }}</p>
                    </a>
                @endforeach
            </div>

            <div class="mt-8 text-center">
                <x-ui.button :href="route('rooms')" variant="secondary" size="lg">Lihat Semua Kamar</x-ui.button>
            </div>
        @endif
    </section>

    <section id="fasilitas" class="bg-gray-50 dark:bg-gray-800/40">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Fasilitas</h2>
                <p class="mt-3 text-gray-600 dark:text-gray-400">Kenyamanan yang Anda dapatkan sebagai penghuni.</p>
            </div>

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['icon' => 'key', 'title' => 'Kamar Nyaman', 'text' => 'Ruangan bersih, ventilasi baik, dan privasi terjaga.'],
                    ['icon' => 'building-office', 'title' => 'Lokasi Strategis', 'text' => 'Dekat kampus, perkantoran, dan transportasi umum.'],
                    ['icon' => 'receipt-percent', 'title' => 'Pembayaran Mudah', 'text' => 'Tagihan jelas dan pembayaran terverifikasi online.'],
                    ['icon' => 'wrench', 'title' => 'Maintenance Responsif', 'text' => 'Keluhan ditangani dengan riwayat status yang transparan.'],
                    ['icon' => 'user-group', 'title' => 'Komunitas Aman', 'text' => 'Penghuni terverifikasi dan aturan bersama yang dijaga.'],
                    ['icon' => 'chart-bar', 'title' => 'Pengelolaan Digital', 'text' => 'Kontrak, bukti bayar, dan pengumuman dalam satu portal.'],
                ] as $feature)
                    <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800">
                        <span class="inline-flex size-11 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-950 dark:text-primary-400">
                            <x-ui.icon :name="$feature['icon']" class="size-6" />
                        </span>
                        <h3 class="mt-4 font-semibold text-gray-900 dark:text-white">{{ $feature['title'] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $feature['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="kontak" class="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Hubungi Kami</h2>
        <p class="mt-3 text-gray-600 dark:text-gray-400">
            Masuk menggunakan akun yang diberikan pengelola untuk mulai mengelola, atau hubungi kami untuk informasi ketersediaan kamar.
        </p>
        <div class="mt-6 flex flex-wrap justify-center gap-3">
            <x-ui.button href="{{ route('login') }}" size="lg">Masuk Sekarang</x-ui.button>
            <x-ui.button :href="route('rooms')" variant="secondary" size="lg">Jelajahi Kamar</x-ui.button>
        </div>
    </section>
</x-layouts.public>
