<x-layouts.tenant title="Kontrak Sewa">
    <x-ui.page-header title="Kontrak Sewa" description="Informasi kamar dan masa sewa Anda." />

    @if ($activeLease)
        <x-ui.card>
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Kode Kontrak</p>
                    <p class="text-base font-semibold text-gray-900 dark:text-white">{{ $activeLease->code }}</p>
                </div>
                <x-ui.status-badge :status="$activeLease->status" />
            </div>

            <dl class="mt-4 grid grid-cols-2 gap-4 border-t border-gray-100 pt-4 dark:border-gray-700">
                @foreach ([
                    'Kamar' => $activeLease->room?->number,
                    'Properti' => $activeLease->room?->property?->name,
                    'Mulai' => $activeLease->start_date->translatedFormat('d M Y'),
                    'Berakhir' => $activeLease->end_date->translatedFormat('d M Y'),
                    'Sewa / Bulan' => \App\Support\Money::format($activeLease->rent_amount),
                    'Jatuh Tempo' => 'Tanggal '.$activeLease->due_day,
                ] as $label => $value)
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-gray-400">{{ $label }}</dt>
                        <dd class="mt-0.5 text-sm font-medium text-gray-900 dark:text-white">{{ $value ?: '—' }}</dd>
                    </div>
                @endforeach
            </dl>

            <div class="mt-4 rounded-lg bg-primary-50 p-4 dark:bg-primary-950">
                <p class="text-sm text-primary-800 dark:text-primary-200">
                    Sisa masa sewa: <span class="font-bold">{{ $activeLease->daysRemaining() }} hari</span>
                </p>
            </div>
        </x-ui.card>
    @else
        <x-ui.empty-state title="Belum ada kontrak aktif" description="Kontrak sewa Anda akan tampil di sini setelah diaktifkan pengelola." />
    @endif

    @if ($leases->isNotEmpty())
        <x-ui.card padding="p-0">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Riwayat Kontrak</h3>
            </div>
            <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach ($leases as $item)
                    <li class="flex items-center justify-between px-5 py-4">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $item->room?->number ?? '—' }} · {{ $item->code }}</p>
                            <p class="text-xs text-gray-500">{{ $item->start_date->format('d M Y') }} – {{ $item->end_date->format('d M Y') }}</p>
                        </div>
                        <x-ui.status-badge :status="$item->status" />
                    </li>
                @endforeach
            </ul>
        </x-ui.card>
    @endif
</x-layouts.tenant>
