<nav class="sticky top-0 z-30 border-b border-gray-100 bg-white/90 backdrop-blur dark:border-gray-700 dark:bg-gray-900/90">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-6 px-4 sm:px-6 lg:px-8">
        <a href="{{ url('/') }}" class="flex items-center gap-2.5">
            <span class="inline-flex size-9 items-center justify-center rounded-lg bg-primary-600 text-white">
                <x-ui.icon name="building-office" class="size-5" />
            </span>
            <span class="text-sm font-bold tracking-tight text-gray-900 dark:text-white">
                Kost<span class="text-primary-600">Management</span>
            </span>
        </a>

        <div class="hidden items-center gap-6 text-sm font-medium text-gray-600 sm:flex dark:text-gray-300">
            <a href="{{ url('/') }}" class="hover:text-gray-900 dark:hover:text-white">Beranda</a>
            @if (\Illuminate\Support\Facades\Route::has('rooms'))
                <a href="{{ route('rooms') }}" class="hover:text-gray-900 dark:hover:text-white">Kamar</a>
            @endif
            <a href="{{ url('/#fasilitas') }}" class="hover:text-gray-900 dark:hover:text-white">Fasilitas</a>
            <a href="{{ url('/#kontak') }}" class="hover:text-gray-900 dark:hover:text-white">Kontak</a>
        </div>

        <div class="flex items-center gap-2">
            @auth
                @if (auth()->user()->hasRole('tenant'))
                    <a href="{{ route('tenant.dashboard') }}" class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">Portal</a>
                @elseif (auth()->user()->hasAnyRole(['owner', 'admin', 'finance', 'technician']))
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">Admin</a>
                @else
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">Dashboard</a>
                @endif
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">Masuk</a>
            @endauth
        </div>
    </div>
</nav>
