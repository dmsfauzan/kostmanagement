@php
    $groups = [
        'Utama' => [
            ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'home', 'perm' => 'dashboard.view'],
        ],
        'Properti' => [
            ['route' => 'admin.properties', 'label' => 'Properti', 'icon' => 'building-office', 'perm' => 'property.view'],
            ['route' => 'admin.buildings', 'label' => 'Gedung', 'icon' => 'building-office', 'perm' => 'building.view'],
            ['route' => 'admin.floors', 'label' => 'Lantai', 'icon' => 'squares-2x2', 'perm' => 'floor.view'],
            ['route' => 'admin.room-types', 'label' => 'Tipe Kamar', 'icon' => 'document-text', 'perm' => 'room_type.view'],
            ['route' => 'admin.rooms', 'label' => 'Kamar', 'icon' => 'key', 'perm' => 'room.view'],
            ['route' => 'admin.amenities', 'label' => 'Fasilitas', 'icon' => 'squares-2x2', 'perm' => 'amenity.view'],
        ],
        'Penghuni' => [
            ['route' => 'admin.tenants', 'label' => 'Penghuni', 'icon' => 'user-group', 'perm' => 'tenant.view'],
            ['route' => 'admin.leases', 'label' => 'Kontrak Sewa', 'icon' => 'document-text', 'perm' => 'lease.view'],
        ],
        'Keuangan' => [
            ['route' => 'admin.invoices', 'label' => 'Tagihan', 'icon' => 'banknotes', 'perm' => 'invoice.view'],
            ['route' => 'admin.payments', 'label' => 'Pembayaran', 'icon' => 'credit-card', 'perm' => 'payment.view'],
            ['route' => 'admin.expenses', 'label' => 'Pengeluaran', 'icon' => 'receipt-percent', 'perm' => 'expense.view'],
        ],
        'Operasional' => [
            ['route' => 'admin.maintenance', 'label' => 'Maintenance', 'icon' => 'wrench', 'perm' => 'maintenance.view'],
            ['route' => 'admin.announcements', 'label' => 'Pengumuman', 'icon' => 'megaphone', 'perm' => 'announcement.view'],
        ],
        'Laporan' => [
            ['route' => 'admin.reports', 'label' => 'Laporan', 'icon' => 'chart-bar', 'perm' => 'report.view'],
        ],
        'Sistem' => [
            ['route' => 'admin.users', 'label' => 'Pengguna', 'icon' => 'user-group', 'perm' => 'user.view'],
            ['route' => 'admin.roles', 'label' => 'Peran & Izin', 'icon' => 'key', 'perm' => 'role.view'],
            ['route' => 'admin.settings', 'label' => 'Pengaturan', 'icon' => 'cog', 'perm' => 'settings.view'],
            ['route' => 'admin.audit-logs', 'label' => 'Audit Log', 'icon' => 'clipboard-list', 'perm' => 'audit.view'],
        ],
    ];
@endphp

<div class="flex h-full flex-col">
    <div class="flex h-16 shrink-0 items-center gap-2 border-b border-gray-200 px-5 dark:border-gray-700">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
            <span class="inline-flex size-9 items-center justify-center rounded-lg bg-primary-600 text-white">
                <x-ui.icon name="building-office" class="size-5" />
            </span>
            <span class="text-sm font-bold tracking-tight text-gray-900 dark:text-white">
                Kost<span class="text-primary-600">Management</span>
            </span>
        </a>
    </div>

    <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-4">
        @foreach ($groups as $title => $items)
            @php
                $visible = collect($items)->filter(fn ($item) => \Illuminate\Support\Facades\Route::has($item['route'])
                    && (! isset($item['perm']) || auth()->user()?->can($item['perm'])));
            @endphp

            @if ($visible->isNotEmpty())
                <div>
                    <p class="px-3 pb-1.5 text-xs font-semibold uppercase tracking-wider text-gray-400">{{ $title }}</p>
                    <div class="space-y-1">
                        @foreach ($visible as $item)
                            <x-ui.sidebar-link
                                :href="route($item['route'])"
                                :icon="$item['icon']"
                                :active="request()->routeIs($item['active'] ?? $item['route'])"
                            >
                                {{ $item['label'] }}
                            </x-ui.sidebar-link>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </nav>

    <div class="border-t border-gray-200 p-3 dark:border-gray-700">
        <div class="flex items-center gap-3 rounded-lg bg-gray-50 px-3 py-2.5 dark:bg-gray-900/50">
            <x-ui.avatar :name="auth()->user()->name" size="sm" />
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ auth()->user()->name }}</p>
                <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->getRoleNames()->first() ?? 'user' }}</p>
            </div>
        </div>
    </div>
</div>
