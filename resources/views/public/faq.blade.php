<x-layouts.public title="FAQ" description="Pertanyaan umum seputar kost.">
    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
        <x-ui.page-header title="Pertanyaan Umum" description="Jawaban untuk pertanyaan yang sering diajukan." />

        @php
            $faqs = [
                ['q' => 'Bagaimana cara menyewa kamar?', 'a' => 'Hubungi pengelola untuk ketersediaan, lalu pengelola akan membuat data penghuni dan kontrak sewa Anda.'],
                ['q' => 'Kapan batas pembayaran sewa?', 'a' => 'Sesuai tanggal jatuh tempo pada tagihan bulanan yang dikirim melalui portal penghuni.'],
                ['q' => 'Bagaimana cara mengunggah bukti pembayaran?', 'a' => 'Buka menu Tagihan di portal penghuni, pilih tagihan, lalu unggah bukti transfer. Pengelola akan memverifikasi.'],
                ['q' => 'Bagaimana jika ada kerusakan fasilitas?', 'a' => 'Buat keluhan melalui menu Keluhan di portal. Teknisi akan menindaklanjuti dan status dapat Anda pantau.'],
                ['q' => 'Apakah deposit dikembalikan?', 'a' => 'Ya. Deposit dikembalikan saat check-out setelah memperhitungkan tagihan yang belum lunas dan kerusakan (bila ada).'],
                ['q' => 'Apakah harga sudah termasuk listrik dan air?', 'a' => 'Tergantung ketentuan kamar. Rincian biaya tertera pada tagihan yang Anda terima.'],
            ];
        @endphp

        <div class="mt-8 space-y-3">
            @foreach ($faqs as $faq)
                <div x-data="{ open: false }" class="rounded-xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-800">
                    <button type="button" @click="open = !open" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left">
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $faq['q'] }}</span>
                        <svg class="size-5 shrink-0 text-gray-400 transition" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="open" x-collapse class="px-5 pb-4 text-sm leading-6 text-gray-600 dark:text-gray-300">
                        {{ $faq['a'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.public>
