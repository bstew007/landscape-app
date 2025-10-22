<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ config('app.name', 'Landscape Estimator') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://unpkg.com/alpinejs" defer></script>
</head>
<body class="bg-gray-100 text-gray-900 font-sans antialiased" x-data="{ sidebarOpen: false }">

<div class="min-h-screen flex">

    {{-- Sidebar --}}
    <aside class="w-64 bg-white shadow-md hidden md:block">
        <div class="p-6 font-bold text-lg border-b text-gray-700">
            🌿 CFL Landscape
        </div>

        <nav class="mt-4 px-4 space-y-6 text-sm">
            <div>
                <h3 class="text-xs text-gray-500 uppercase tracking-wide mb-1">Client Hub</h3>
                <a href="{{ route('clients.index') }}" class="block px-2 py-1 rounded hover:bg-blue-100">👥 Clients</a>
            </div>

            <div>
                <h3 class="text-xs text-gray-500 uppercase tracking-wide mb-1">Estimators</h3>
                <a href="{{ route('calculators.index') }}" class="block px-2 py-1 rounded hover:bg-blue-100">📊 Calculator Dashboard</a>
                <a href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.wall.form')]) }}" class="block px-2 py-1 rounded hover:bg-blue-100">🧱 Retaining Wall</a>
                <a href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.patio.form')]) }}" class="block px-2 py-1 rounded hover:bg-blue-100">🧮 Paver Patio</a>
                <a href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.enhancements.form')]) }}" class="block px-2 py-1 rounded hover:bg-blue-100">🌿 Enhancements</a>
            </div>
        </nav>
    </aside>

    {{-- Mobile Sidebar --}}
    <div class="md:hidden fixed inset-0 bg-black bg-opacity-50 z-40" x-show="sidebarOpen" @click="sidebarOpen = false"></div>

    <aside class="fixed inset-y-0 left-0 bg-white w-64 shadow-md z-50 transform transition-transform duration-300 md:hidden"
           x-show="sidebarOpen"
           x-transition:enter="transform transition-transform duration-300"
           x-transition:enter-start="-translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transform transition-transform duration-300"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="-translate-x-full">
        <div class="p-6 font-bold text-lg border-b text-gray-700">🌿 CFL Landscape</div>

        <nav class="mt-4 px-4 space-y-6 text-sm">
            <div>
                <h3 class="text-xs text-gray-500 uppercase tracking-wide mb-1">Client Hub</h3>
                <a href="{{ route('clients.index') }}" class="block px-2 py-1 rounded hover:bg-blue-100">👥 Clients</a>
            </div>
            <div>
                <h3 class="text-xs text-gray-500 uppercase tracking-wide mb-1">Estimators</h3>
                <a href="{{ route('calculators.index') }}" class="block px-2 py-1 rounded hover:bg-blue-100">📊 Calculator Dashboard</a>
                <a href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.wall.form')]) }}" class="block px-2 py-1 rounded hover:bg-blue-100">🧱 Retaining Wall</a>
                <a href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.patio.form')]) }}" class="block px-2 py-1 rounded hover:bg-blue-100">🧮 Paver Patio</a>
                <a href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.enhancements.form')]) }}" class="block px-2 py-1 rounded hover:bg-blue-100">🌿 Enhancements</a>
            </div>
        </nav>
    </aside>

    {{-- Main Content --}}
    <div class="flex-1 flex flex-col min-h-screen">

        {{-- Top Nav --}}
        <header class="sticky top-0 z-30 bg-white shadow-md">
            <div class="flex items-center justify-between px-4 py-3">
                <button @click="sidebarOpen = true" class="md:hidden text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div class="font-semibold text-lg text-gray-800">CFL Estimating</div>
                <div></div> {{-- Placeholder for spacing or user menu --}}
            </div>
        </header>

        {{-- Breadcrumb Slot --}}
        @isset($header)
            <div class="bg-gray-100 border-b">
                <div class="max-w-7xl mx-auto py-3 px-4 sm:px-6 lg:px-8 text-sm text-gray-600">
                    {{ $header }}
                </div>
            </div>
        @endisset

        {{-- Main Page Content --}}
        <main class="flex-1 p-6">
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>

