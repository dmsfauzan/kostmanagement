@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-gray-50 font-sans text-gray-900 antialiased dark:bg-gray-900 dark:text-gray-100" x-data="{ sidebarOpen: false }">
    <div
        x-cloak
        x-show="sidebarOpen"
        @click="sidebarOpen = false"
        class="fixed inset-0 z-40 bg-gray-900/60 backdrop-blur-sm lg:hidden"
    ></div>

    <aside
        x-cloak
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-gray-200 bg-white transition-transform duration-200 lg:translate-x-0 dark:border-gray-700 dark:bg-gray-800"
    >
        @include('partials.admin.sidebar')
    </aside>

    <div class="lg:pl-64">
        @include('partials.admin.topbar', ['title' => $title])

        <main class="px-4 py-6 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>
    </div>
</body>
</html>
