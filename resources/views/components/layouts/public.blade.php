@props(['title' => null, 'description' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if ($description)
        <meta name="description" content="{{ $description }}">
    @endif

    <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-white font-sans text-gray-900 antialiased dark:bg-gray-900 dark:text-gray-100">
    @include('partials.public.nav')

    <main>
        {{ $slot }}
    </main>

    @include('partials.public.footer')
</body>
</html>
