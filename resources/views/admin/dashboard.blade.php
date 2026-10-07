<x-layouts.admin title="Dashboard">
    <div class="space-y-6">
        <x-ui.page-header
            title="Dashboard"
            description="Ringkasan kondisi operasional kost Anda hari ini."
        />

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-ui.stat-card label="Total Pengguna" :value="number_format($totalUsers)" color="primary">
                <x-slot:icon><x-ui.icon name="user-group" class="size-5" /></x-slot:icon>
            </x-ui.stat-card>

            <x-ui.stat-card
                label="Tim Internal"
                :value="number_format($roleCounts['owner'] + $roleCounts['admin'] + $roleCounts['finance'] + $roleCounts['technician'])"
                color="info"
            >
                <x-slot:icon><x-ui.icon name="key" class="size-5" /></x-slot:icon>
            </x-ui.stat-card>

            <x-ui.stat-card label="Penghuni" :value="number_format($roleCounts['tenant'])" color="success">
                <x-slot:icon><x-ui.icon name="user" class="size-5" /></x-slot:icon>
            </x-ui.stat-card>

            <x-ui.stat-card label="Teknisi" :value="number_format($roleCounts['technician'])" color="warning">
                <x-slot:icon><x-ui.icon name="wrench" class="size-5" /></x-slot:icon>
            </x-ui.stat-card>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <x-ui.card>
                    <x-slot:header>
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Modul Aplikasi</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Status pengembangan setiap modul.</p>
                            </div>
                        </div>
                    </x-slot:header>

                    <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ([
                            ['label' => 'Fondasi & Autentikasi', 'status' => 'Aktif', 'variant' => 'success'],
                            ['label' => 'Properti & Kamar', 'status' => 'Segera', 'variant' => 'warning'],
                            ['label' => 'Penghuni & Kontrak', 'status' => 'Segera', 'variant' => 'warning'],
                            ['label' => 'Tagihan & Pembayaran', 'status' => 'Segera', 'variant' => 'warning'],
                            ['label' => 'Maintenance', 'status' => 'Segera', 'variant' => 'warning'],
                            ['label' => 'Pengeluaran & Laporan', 'status' => 'Segera', 'variant' => 'warning'],
                        ] as $module)
                            <li class="flex items-center justify-between py-3">
                                <span class="text-sm text-gray-700 dark:text-gray-200">{{ $module['label'] }}</span>
                                <x-ui.badge :variant="$module['variant']">{{ $module['status'] }}</x-ui.badge>
                            </li>
                        @endforeach
                    </ul>
                </x-ui.card>
            </div>

            <x-ui.card>
                <x-slot:header>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Peran Pengguna</h2>
                </x-slot:header>

                <ul class="space-y-3">
                    @foreach (['owner' => 'Pemilik', 'admin' => 'Admin', 'finance' => 'Keuangan', 'technician' => 'Teknisi', 'tenant' => 'Penghuni'] as $role => $label)
                        <li class="flex items-center justify-between">
                            <span class="text-sm capitalize text-gray-600 dark:text-gray-300">{{ $label }}</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $roleCounts[$role] }}</span>
                        </li>
                    @endforeach
                </ul>
            </x-ui.card>
        </div>
    </div>
</x-layouts.admin>
