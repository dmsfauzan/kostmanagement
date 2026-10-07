<x-layouts.public title="Aturan Kost" description="Aturan dan tata tertib penghuni kost.">
    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
        <x-ui.page-header title="Aturan Kost" description="Demi kenyamanan bersama, mohon patuhi tata tertib berikut." />

        <ol class="mt-8 space-y-3">
            @foreach ([
                'Pembayaran sewa dilakukan paling lambat sesuai tanggal jatuh tempo yang tertera pada tagihan.',
                'Menjaga kebersihan kamar dan area bersama.',
                'Tidak membawa tamu menginap tanpa izin pengelola.',
                'Tidak melakukan perubahan struktur bangunan tanpa persetujuan.',
                'Menjaga ketenangan, terutama pada malam hari.',
                'Melaporkan kerusakan atau keluhan melalui portal penghuni.',
                'Tidak menyimpan barang berbahaya atau terlarang.',
                'Menjaga keamanan dengan mengunci kamar saat keluar.',
            ] as $index => $rule)
                <li class="flex gap-3 rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-800">
                    <span class="inline-flex size-7 shrink-0 items-center justify-center rounded-full bg-primary-50 text-sm font-semibold text-primary-700 dark:bg-primary-950 dark:text-primary-300">{{ $index + 1 }}</span>
                    <span class="text-sm leading-6 text-gray-700 dark:text-gray-200">{{ $rule }}</span>
                </li>
            @endforeach
        </ol>
    </div>
</x-layouts.public>
