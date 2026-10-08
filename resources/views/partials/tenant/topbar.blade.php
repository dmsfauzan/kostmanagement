<header class="sticky top-0 z-30 border-b border-gray-200 bg-white/90 backdrop-blur dark:border-gray-700 dark:bg-gray-900/90">
    <div class="flex h-16 items-center gap-3 px-4">
        <div class="min-w-0 flex-1">
            <p class="text-xs text-gray-500 dark:text-gray-400">Selamat datang,</p>
            <p class="truncate text-base font-semibold text-gray-900 dark:text-white">{{ auth()->user()->name }}</p>
        </div>

        @if (\Illuminate\Support\Facades\Route::has('tenant.notifications'))
            @php $unreadCount = auth()->user()->unreadNotifications()->count(); @endphp
            <a href="{{ route('tenant.notifications') }}" class="relative rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800" aria-label="Notifikasi">
                <x-ui.icon name="bell" class="size-6" />
                @if ($unreadCount > 0)
                    <span class="absolute -right-0.5 -top-0.5 inline-flex size-4 items-center justify-center rounded-full bg-danger-500 text-[10px] font-bold text-white">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                @endif
            </a>
        @endif

        <x-ui.avatar :name="auth()->user()->name" size="sm" />
    </div>
</header>
