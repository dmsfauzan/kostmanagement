<x-layouts.public title="Fasilitas" description="Fasilitas yang tersedia untuk penghuni kost.">
    <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
        <x-ui.page-header title="Fasilitas" description="Kenyamanan yang kami sediakan untuk penghuni." />

        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                ['icon' => 'key', 'title' => 'Kamar Pribadi', 'text' => 'Kamar dengan kunci sendiri, jendela, dan ventilasi udara yang baik.'],
                ['icon' => 'wifi', 'title' => 'WiFi Cepat', 'text' => 'Internet stabil untuk bekerja dan belajar.'],
                ['icon' => 'squares-2x2', 'title' => 'Kamar Mandi', 'text' => 'Kamar mandi dalam atau berbagi yang dibersihkan rutin.'],
                ['icon' => 'building-office', 'title' => 'Parkir', 'text' => 'Area parkir motor dan mobil yang aman.'],
                ['icon' => 'cog', 'title' => 'Air & Listrik', 'text' => 'Pasokan air dan listrik yang terjaga.'],
                ['icon' => 'clipboard-list', 'title' => 'Keamanan', 'text' => 'Akses terkontrol dan lingkungan yang aman.'],
            ] as $item)
                <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800">
                    <span class="inline-flex size-11 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-950 dark:text-primary-400">
                        <x-ui.icon :name="$item['icon']" class="size-6" />
                    </span>
                    <h3 class="mt-4 font-semibold text-gray-900 dark:text-white">{{ $item['title'] }}</h3>
                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $item['text'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.public>
