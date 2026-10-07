<x-layouts.public title="Kamar Tersedia" description="Pilihan kamar kost tersedia, lengkap dengan harga dan fasilitas.">
    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        <x-ui.page-header title="Kamar Tersedia" description="Temukan kamar sesuai kebutuhan Anda.">
            <x-slot:actions>
                <a href="{{ url('/') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400">Beranda</a>
            </x-slot:actions>
        </x-ui.page-header>

        <form method="GET" action="{{ route('rooms') }}" class="mt-6 flex flex-col gap-3 rounded-xl border border-gray-100 bg-white p-4 sm:flex-row sm:items-end dark:border-gray-700 dark:bg-gray-800">
            <label class="flex-1">
                <span class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Cari Nomor</span>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Mis. A-101" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </label>
            <label class="flex-1">
                <span class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipe Kamar</span>
                <select name="room_type" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">Semua tipe</option>
                    @foreach ($roomTypes as $id => $name)
                        <option value="{{ $id }}" @selected((string) request('room_type') === (string) $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </label>
            <div class="flex gap-2">
                <x-ui.button type="submit" size="md">Cari</x-ui.button>
                <a href="{{ route('rooms') }}" class="inline-flex items-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:ring-gray-600">Atur Ulang</a>
            </div>
        </form>

        @if ($rooms->isEmpty())
            <div class="mt-8">
                <x-ui.empty-state title="Tidak ada kamar tersedia" description="Belum ada kamar yang tersedia saat ini atau yang sesuai filter Anda." />
            </div>
        @else
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($rooms as $room)
                    <a href="{{ route('rooms.show', $room->slug) }}" class="group rounded-xl border border-gray-100 bg-white p-6 shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-lg font-semibold text-gray-900 group-hover:text-primary-600 dark:text-white dark:group-hover:text-primary-400">{{ $room->number }}</p>
                                <p class="text-sm text-gray-500">{{ $room->building?->name }} · {{ $room->floor?->name ?? '—' }}</p>
                            </div>
                            <x-ui.status-badge :status="$room->status" />
                        </div>
                        <p class="mt-3 text-base font-semibold text-gray-900 dark:text-white">{{ \App\Support\Money::format($room->price) }}<span class="text-sm font-normal text-gray-400">/bulan</span></p>
                        <p class="text-xs text-gray-500">{{ $room->roomType?->name ?? 'Tanpa tipe' }} · {{ $room->size_sqm ?? '—' }} m²</p>

                        @if ($room->amenities->isNotEmpty())
                            <div class="mt-3 flex flex-wrap gap-1.5">
                                @foreach ($room->amenities->take(4) as $amenity)
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $amenity->name }}</span>
                                @endforeach
                            </div>
                        @endif
                    </a>
                @endforeach
            </div>

            <div class="mt-8">{{ $rooms->links() }}</div>
        @endif
    </div>
</x-layouts.public>
