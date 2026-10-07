@php
    $items = array_values(array_filter([
        ['route' => 'tenant.dashboard', 'label' => 'Beranda', 'icon' => 'home'],
        ['route' => 'tenant.invoices', 'label' => 'Tagihan', 'icon' => 'banknotes'],
        ['route' => 'tenant.payments', 'label' => 'Bayar', 'icon' => 'credit-card'],
        ['route' => 'tenant.maintenance', 'label' => 'Keluhan', 'icon' => 'wrench'],
        \Illuminate\Support\Facades\Route::has('profile') ? ['route' => 'profile', 'label' => 'Profil', 'icon' => 'user'] : null,
    ], fn ($item) => $item && \Illuminate\Support\Facades\Route::has($item['route'])));
@endphp

<nav class="fixed inset-x-0 bottom-0 z-30 border-t border-gray-200 bg-white pb-[env(safe-area-inset-bottom)] dark:border-gray-700 dark:bg-gray-800">
    <div class="mx-auto grid max-w-lg grid-cols-{{ max(count($items), 1) }}">
        @foreach ($items as $item)
            @php $active = request()->routeIs($item['route']); @endphp
            <a href="{{ route($item['route']) }}" @class([
                'flex flex-col items-center justify-center gap-0.5 py-2.5 text-xs font-medium transition',
                'text-primary-600 dark:text-primary-400' => $active,
                'text-gray-400 hover:text-gray-600 dark:text-gray-500' => ! $active,
            ])>
                <x-ui.icon :name="$item['icon']" class="size-6" />
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>
