<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name', 'CFL') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts & Styles -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased text-gray-800">

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-md p-6 space-y-6">
            <!-- Logo -->
            <div class="text-2xl font-bold text-blue-600 flex items-center gap-2">
                <svg class="w-8 h-8 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M3 3h18v2H3V3zm0 16h18v2H3v-2zm0-8h18v2H3v-2z" />
                </svg>
                CFL
            </div>

            <!-- Navigation -->
            <nav class="space-y-3 text-lg">
                <a href="{{ route('clients.index') }}"
                   class="block px-3 py-2 rounded-md hover:bg-blue-100 hover:text-blue-800 {{ request()->routeIs('clients.*') ? 'bg-blue-200 text-blue-900 font-semibold' : '' }}">
                    🧑 Clients
                </a>
                <a href="#"
                   class="block px-3 py-2 rounded-md hover:bg-blue-100 hover:text-blue-800">
                    📝 Site Visits
                </a>
                <!-- Add more links as needed -->
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6">
            @yield('content')
        </main>
    </div>

</body>
</html>
