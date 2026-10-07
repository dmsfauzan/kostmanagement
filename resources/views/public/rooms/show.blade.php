<x-layouts.public :title="$room->number" :description="'Kamar '.$room->number.' — '.($room->roomType?->name ?? 'Kost').' tersedia.'">
    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
        <nav class="mb-6 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('rooms') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Kamar</a>
            <span>›</span>
            <span class="font-medium text-gray-900 dark:text-white">{{ $room->number }}</span>
        </nav>

        <x-ui.card padding="p-0">
            <div class="p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $room->number }}</h1>
                        <p class="mt-1 text-sm text-gray-500">{{ $room->property?->name }} — {{ $room->building?->name }} · {{ $room->floor?->name }}</p>
                    </div>
                    <x-ui.status-badge :status="$room->status" />
                </div>

                <div class="mt-6 rounded-xl bg-gray-50 p-5 dark:bg-gray-800">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Harga sewa</p>
                    <p class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ \App\Support\Money::format($room->price) }}<span class="text-base font-normal text-gray-400">/bulan</span></p>
                    <p class="mt-1 text-sm text-gray-500">Deposit {{ \App\Support\Money::format($room->deposit) }} · {{ $room->size_sqm ?? '—' }} m² · {{ $room->roomType?->name ?? 'Tanpa tipe' }}</p>
                </div>

                @if ($room->photos->isNotEmpty())
                    <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach ($room->photos as $photo)
                            <img src="{{ $photo->url() }}" alt="Foto {{ $room->number }}" class="h-36 w-full rounded-lg object-cover">
                        @endforeach
                    </div>
                @endif

                @if ($room->description)
                    <div class="mt-6">
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Deskripsi</h2>
                        <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $room->description }}</p>
                    </div>
                @endif

                @if ($room->amenities->isNotEmpty())
                    <div class="mt-6">
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Fasilitas</h2>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($room->amenities as $amenity)
                                <span class="rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-200">{{ $amenity->name }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('login') }}" class="inline-flex items-center rounded-lg bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-700">Hubungi Pengelola</a>
                    <a href="{{ route('rooms') }}" class="inline-flex items-center rounded-lg bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:ring-gray-600">Kembali</a>
                </div>
            </div>
        </x-ui.card>

        @if ($otherRooms->isNotEmpty())
            <div class="mt-10">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Kamar lainnya</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-3">
                    @foreach ($otherRooms as $other)
                        <a href="{{ route('rooms.show', $other->slug) }}" class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm transition hover:shadow dark:border-gray-700 dark:bg-gray-800">
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $other->number }}</p>
                            <p class="text-xs text-gray-500">{{ $other->roomType?->name ?? '—' }}</p>
                            <p class="mt-2 text-sm font-semibold text-primary-600">{{ \App\Support\Money::format($other->price) }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts.public>
