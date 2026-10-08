<div class="space-y-6">
    <x-ui.page-header :title="'Kontrak '.$lease->code" :description="'Penghuni: '.($lease->tenant?->full_name ?? '—')">
        <x-slot:actions>
            <x-ui.button :href="route('admin.leases.move-out', $lease)" variant="secondary" wire:navigate>Move-Out</x-ui.button>
            @can('lease.update')
                <x-ui.button :href="route('admin.leases.edit', $lease)" wire:navigate>Ubah</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-2">
            <x-slot:header><h3 class="text-sm font-semibold text-gray-900 dark:text-white">Detail Kontrak</h3></x-slot:header>
            <dl class="grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Status</dt>
                    <dd class="mt-1"><x-ui.status-badge :status="$lease->status" /></dd>
                </div>
                @foreach ([
                    'Kode' => $lease->code,
                    'Kamar' => $lease->room?->number.' — '.$lease->room?->property?->name,
                    'Mulai' => $lease->start_date->translatedFormat('d F Y'),
                    'Berakhir' => $lease->end_date->translatedFormat('d F Y'),
                    'Sewa / Bulan' => \App\Support\Money::format($lease->rent_amount),
                    'Deposit' => \App\Support\Money::format($lease->deposit_amount),
                    'Siklus' => $lease->billing_cycle->label(),
                    'Jatuh Tempo' => 'Tanggal '.$lease->due_day,
                    'Denda' => $lease->late_fee_type->label(),
                    'Dibuat' => $lease->created_at->translatedFormat('d F Y'),
                    'Alasan Berakhir' => $lease->termination_reason,
                    'Catatan' => $lease->notes,
                ] as $label => $value)
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ $label }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $value ?: '—' }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-ui.card>

        <x-ui.card>
            <x-slot:header><h3 class="text-sm font-semibold text-gray-900 dark:text-white">Deposit</h3></x-slot:header>
            @if ($lease->deposit)
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Nominal</dt>
                        <dd class="text-sm font-semibold text-gray-900 dark:text-white">{{ \App\Support\Money::format($lease->deposit->amount) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Status</dt>
                        <dd class="mt-1"><x-ui.status-badge :status="$lease->deposit->status" /></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Potongan / Pengembalian</dt>
                        <dd class="text-sm text-gray-900 dark:text-white">{{ \App\Support\Money::format($lease->deposit->deduction_amount) }} / {{ \App\Support\Money::format($lease->deposit->refund_amount) }}</dd>
                    </div>
                </dl>
            @else
                <p class="text-sm text-gray-500">Deposit dibuat saat kontrak diaktifkan.</p>
            @endif

            @can('lease.update')
                @if ($lease->isActive())
                    <x-ui.button :href="route('admin.leases.move-out', $lease)" variant="secondary" class="mt-4 w-full" wire:navigate>Proses Move-Out</x-ui.button>
                @else
                    <x-ui.button wire:click="activate" class="mt-4 w-full">Aktifkan Kontrak</x-ui.button>
                @endif
            @endcan
        </x-ui.card>
    </div>

    <x-ui.card>
        <x-slot:header><h3 class="text-sm font-semibold text-gray-900 dark:text-white">Riwayat Status</h3></x-slot:header>
        @if ($lease->statusHistories->isNotEmpty())
            <ol class="space-y-3">
                @foreach ($lease->statusHistories as $history)
                    <li class="flex items-start gap-3 text-sm">
                        <span class="mt-1 size-2 shrink-0 rounded-full bg-primary-500"></span>
                        <div>
                            <p class="text-gray-900 dark:text-white">
                                @if ($history->from_status) {{ \Illuminate\Support\Str::headline($history->from_status) }} → @endif
                                <span class="font-semibold">{{ \Illuminate\Support\Str::headline($history->to_status) }}</span>
                            </p>
                            <p class="text-xs text-gray-500">{{ $history->reason }} · {{ $history->actor?->name }} · {{ $history->created_at->translatedFormat('d M Y H:i') }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        @else
            <p class="text-sm text-gray-500">Belum ada riwayat status.</p>
        @endif
    </x-ui.card>
</div>
