<x-layouts.admin title="Dashboard">
    <div class="space-y-6">
        <x-ui.page-header title="Dashboard" description="Ringkasan kondisi dan keuangan kost hari ini.">
            <x-slot:actions>
                <form method="GET" action="{{ route('admin.dashboard') }}">
                    <select name="property" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Semua properti</option>
                        @foreach ($properties as $id => $name)
                            <option value="{{ $id }}" @selected($selectedProperty == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </form>
            </x-slot:actions>
        </x-ui.page-header>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-ui.stat-card label="Okupansi" :value="$occupancy['occupancy_rate'].'%'" :hint="$occupancy['occupied'].' / '.$occupancy['total'].' kamar terisi'" color="primary">
                <x-slot:icon><x-ui.icon name="key" class="size-5" /></x-slot:icon>
            </x-ui.stat-card>
            <x-ui.stat-card label="Ditagih (Bulan Ini)" :value="\App\Support\Money::format($financial['billed'])" color="info">
                <x-slot:icon><x-ui.icon name="banknotes" class="size-5" /></x-slot:icon>
            </x-ui.stat-card>
            <x-ui.stat-card label="Diterima (Bulan Ini)" :value="\App\Support\Money::format($financial['collected'])" color="success">
                <x-slot:icon><x-ui.icon name="check" class="size-5" /></x-slot:icon>
            </x-ui.stat-card>
            <x-ui.stat-card label="Net Cash Flow" :value="\App\Support\Money::format($financial['net_cash_flow'])" :color="$financial['net_cash_flow'] >= 0 ? 'success' : 'danger'">
                <x-slot:icon><x-ui.icon name="chart-bar" class="size-5" /></x-slot:icon>
            </x-ui.stat-card>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <x-ui.stat-card label="Belum Dibayar (Outstanding)" :value="\App\Support\Money::format($financial['outstanding'])" color="warning" />
            <x-ui.stat-card label="Jatuh Tempo (Overdue)" :value="\App\Support\Money::format($financial['overdue'])" color="danger" />
            <x-ui.stat-card label="Pengeluaran (Bulan Ini)" :value="\App\Support\Money::format($financial['expenses'])" color="neutral" />
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <x-ui.card padding="p-0">
                <x-slot:header>
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Perlu Tindakan</h3>
                        <x-ui.badge variant="warning">Attention Center</x-ui.badge>
                    </div>
                </x-slot:header>
                <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                    <li class="flex items-center justify-between px-5 py-3">
                        <a href="{{ route('admin.payments', ['status' => 'pending']) }}" wire:navigate class="text-sm text-gray-700 hover:text-primary-600 dark:text-gray-200">Pembayaran menunggu verifikasi</a>
                        <x-ui.badge :variant="$pendingPayments->count() > 0 ? 'warning' : 'neutral'">{{ $pendingPayments->count() }}</x-ui.badge>
                    </li>
                    <li class="flex items-center justify-between px-5 py-3">
                        <a href="{{ route('admin.invoices', ['status' => 'overdue']) }}" wire:navigate class="text-sm text-gray-700 hover:text-primary-600 dark:text-gray-200">Invoice jatuh tempo</a>
                        <x-ui.badge :variant="$overdueInvoices->count() > 0 ? 'danger' : 'neutral'">{{ $overdueInvoices->count() }}</x-ui.badge>
                    </li>
                    <li class="flex items-center justify-between px-5 py-3">
                        <a href="{{ route('admin.maintenance') }}" wire:navigate class="text-sm text-gray-700 hover:text-primary-600 dark:text-gray-200">Keluhan lewat SLA</a>
                        <x-ui.badge :variant="$slaTickets->count() > 0 ? 'danger' : 'neutral'">{{ $slaTickets->count() }}</x-ui.badge>
                    </li>
                    <li class="flex items-center justify-between px-5 py-3">
                        <a href="{{ route('admin.leases') }}" wire:navigate class="text-sm text-gray-700 hover:text-primary-600 dark:text-gray-200">Kontrak berakhir ≤ 30 hari</a>
                        <x-ui.badge :variant="$expiringLeases->count() > 0 ? 'warning' : 'neutral'">{{ $expiringLeases->count() }}</x-ui.badge>
                    </li>
                    <li class="flex items-center justify-between px-5 py-3">
                        <a href="{{ route('admin.rooms', ['status' => 'maintenance']) }}" wire:navigate class="text-sm text-gray-700 hover:text-primary-600 dark:text-gray-200">Kamar dalam perbaikan</a>
                        <x-ui.badge :variant="$occupancy['maintenance'] > 0 ? 'warning' : 'neutral'">{{ $occupancy['maintenance'] }}</x-ui.badge>
                    </li>
                </ul>
            </x-ui.card>

            <x-ui.card>
                <x-slot:header><h3 class="text-sm font-semibold text-gray-900 dark:text-white">Okupansi Kamar</h3></x-slot:header>
                <dl class="grid grid-cols-2 gap-4">
                    @foreach ([
                        'Total Kamar' => $occupancy['total'],
                        'Terisi' => $occupancy['occupied'],
                        'Tersedia' => $occupancy['available'],
                        'Perbaikan' => $occupancy['maintenance'],
                        'Dipesan' => $occupancy['reserved'],
                        'Nonaktif' => $occupancy['inactive'],
                    ] as $label => $value)
                        <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-gray-700/40">
                            <dt class="text-xs uppercase tracking-wide text-gray-400">{{ $label }}</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
                <a href="{{ route('admin.rooms.map') }}" wire:navigate class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400">
                    Lihat peta kamar →
                </a>
            </x-ui.card>
        </div>

        <x-ui.card padding="p-0">
            <x-slot:header><h3 class="text-sm font-semibold text-gray-900 dark:text-white">6 Bulan Terakhir</h3></x-slot:header>
            <x-ui.table :headings="['Bulan', 'Ditagih', 'Diterima', 'Pengeluaran']">
                @foreach ($financial['monthly'] as $row)
                    <tr>
                        <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $row['month'] }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ \App\Support\Money::format($row['billed']) }}</td>
                        <td class="px-4 py-3 text-success-600">{{ \App\Support\Money::format($row['collected']) }}</td>
                        <td class="px-4 py-3 text-danger-600">{{ \App\Support\Money::format($row['expenses']) }}</td>
                    </tr>
                @endforeach
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.admin>
