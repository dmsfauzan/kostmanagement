<x-layouts.tenant title="Beranda">
    <div class="rounded-xl bg-primary-600 p-5 text-white">
        <p class="text-sm text-primary-100">Halo,</p>
        <p class="text-lg font-semibold">{{ auth()->user()->name }}</p>
        <p class="mt-1 text-sm text-primary-100">
            @if ($tenant?->property)
                {{ $tenant->property->name }}
            @else
                Selamat datang di portal penghuni.
            @endif
        </p>
    </div>

    <x-ui.card padding="p-0">
        <a href="{{ route('tenant.lease') }}" wire:navigate class="flex items-center gap-3 p-5">
            <span class="inline-flex size-11 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-950 dark:text-primary-400">
                <x-ui.icon name="key" class="size-6" />
            </span>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Kamar Saya</p>
                @if ($lease)
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Kamar {{ $lease->room?->number }} · {{ $lease->room?->property?->name }}
                    </p>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada kamar yang terhubung.</p>
                @endif
            </div>
            <x-ui.status-badge :status="$lease?->status ?? \App\Enums\LeaseStatus::Draft" />
        </a>
    </x-ui.card>

    @if ($lease)
        <x-ui.card>
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Masa Sewa</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ $lease->start_date->translatedFormat('d M Y') }} – {{ $lease->end_date->translatedFormat('d M Y') }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold text-primary-600">{{ $lease->daysRemaining() }}</p>
                    <p class="text-xs text-gray-500">hari tersisa</p>
                </div>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-4 border-t border-gray-100 pt-4 dark:border-gray-700">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-400">Sewa / Bulan</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ \App\Support\Money::format($lease->rent_amount) }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-400">Deposit</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ \App\Support\Money::format($lease->deposit?->amount ?? $lease->deposit_amount) }}</p>
                </div>
            </div>
        </x-ui.card>
    @else
        <x-ui.card padding="p-0">
            <div class="flex items-center gap-3 p-5">
                <span class="inline-flex size-11 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                    <x-ui.icon name="document-text" class="size-6" />
                </span>
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Kontrak Sewa</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada kontrak aktif.</p>
                </div>
            </div>
        </x-ui.card>
    @endif

    <x-ui.card padding="p-0">
        <a href="{{ route('tenant.invoices') }}" wire:navigate class="block">
            <div class="flex items-center gap-3 p-5">
                <span class="inline-flex size-11 items-center justify-center rounded-lg bg-warning-50 text-warning-600 dark:bg-warning-950 dark:text-warning-400">
                    <x-ui.icon name="banknotes" class="size-6" />
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Tagihan Aktif</p>
                    @if ($currentInvoice)
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $currentInvoice->invoice_number }} · {{ \App\Support\Money::format($currentInvoice->amount_due) }} · Jt. tempo {{ $currentInvoice->due_date->translatedFormat('d M Y') }}</p>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada tagihan saat ini.</p>
                    @endif
                </div>
            </div>
        </a>
    </x-ui.card>

    <x-ui.card padding="p-0">
        <a href="{{ route('tenant.announcements') }}" wire:navigate class="block">
            <div class="flex items-start gap-3 p-5">
                <span class="inline-flex size-11 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-950 dark:text-primary-400">
                    <x-ui.icon name="megaphone" class="size-6" />
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Pengumuman</p>
                    @if ($latestAnnouncement)
                        <p class="mt-1 truncate text-sm text-gray-500 dark:text-gray-400">{{ $latestAnnouncement->title }}</p>
                    @else
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Belum ada pengumuman terbaru.</p>
                    @endif
                </div>
            </div>
        </a>
    </x-ui.card>
</x-layouts.tenant>
