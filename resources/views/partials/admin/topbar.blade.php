<header class="sticky top-0 z-30 flex h-16 items-center gap-4 border-b border-gray-200 bg-white/80 px-4 backdrop-blur sm:px-6 lg:px-8 dark:border-gray-700 dark:bg-gray-900/80">
    <button
        type="button"
        @click="sidebarOpen = true"
        class="-ml-1 rounded-lg p-2 text-gray-500 hover:bg-gray-100 lg:hidden dark:text-gray-400 dark:hover:bg-gray-800"
        aria-label="Buka menu"
    >
        <x-ui.icon name="menu" class="size-6" />
    </button>

    <div class="min-w-0 flex-1">
        <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">
            {{ $title ?? 'Dashboard' }}
        </p>
        <p class="hidden text-xs text-gray-500 sm:block dark:text-gray-400">
            {{ now()->translatedFormat('l, d F Y') }}
        </p>
    </div>

    <div class="flex items-center gap-2">
        <div class="hidden items-center gap-2 sm:flex">
            <x-ui.avatar :name="auth()->user()->name" size="sm" />
            <div class="leading-tight">
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ auth()->user()->name }}</p>
                <p class="text-xs capitalize text-gray-500 dark:text-gray-400">{{ auth()->user()->getRoleNames()->first() }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800" title="Keluar">
                <x-ui.icon name="logout" class="size-5" />
                <span class="hidden sm:inline">Keluar</span>
            </button>
        </form>
    </div>
</header>
