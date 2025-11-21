<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CFL') }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}" />

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-brand-50 via-white to-brand-100 px-4">
            <div class="text-center">
                <a href="/" class="inline-flex items-center">
                    <img src="{{ asset('images/logo.svg') }}" alt="{{ config('app.name', 'CFL') }} logo" class="h-48 w-auto drop-shadow-sm">
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-8 py-8 bg-gradient-to-br from-brand-50/50 via-white/40 to-white/10 backdrop-blur-md shadow-2xl ring-4 ring-brand-400 overflow-hidden rounded-2xl">
                <h1 class="mb-6 text-2xl font-semibold text-brand-800 text-center">Welcome back to Cape Fear Landscaping</h1>
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
