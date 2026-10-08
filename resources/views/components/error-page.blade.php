@props([
    'code' => '500',
    'title' => 'Terjadi kesalahan',
    'message' => 'Silakan coba lagi beberapa saat lagi.',
    'requestId' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $code }} · {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full items-center justify-center bg-gray-50 p-6 font-sans text-gray-900 antialiased dark:bg-gray-900 dark:text-gray-100">
    <div class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <p class="text-5xl font-bold tracking-tight text-primary-600">{{ $code }}</p>
        <h1 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $message }}</p>

        @if ($requestId)
            <p class="mt-3 text-xs text-gray-400">Kode referensi: <span class="font-mono">{{ $requestId }}</span></p>
        @endif

        <div class="mt-6 flex justify-center gap-3">
            <a href="{{ url('/') }}" class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">Kembali ke Beranda</a>
            @auth
                <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:ring-gray-600">Dashboard</a>
            @endauth
        </div>
    </div>
</body>
</html>
