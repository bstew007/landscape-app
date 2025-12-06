<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>@yield('title', config('app.name', 'CFL'))</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .sidebar-accordion summary::-webkit-details-marker { display: none; }
        .sidebar-accordion summary {
            position: relative;
            transition: background 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
        }
        .sidebar-accordion summary::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 0.5rem;
            opacity: 0;
            background: linear-gradient(120deg, rgba(16,185,129,0.15), rgba(59,130,246,0.12));
            transition: opacity 0.2s ease;
            pointer-events: none;
        }
        .sidebar-accordion summary:hover::after {
            opacity: 1;
        }
        .sidebar-panel {
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            transition: max-height 0.3s ease, opacity 0.25s ease;
        }
        .sidebar-accordion[open] .sidebar-panel {
            max-height: 600px;
            opacity: 1;
        }
        /* Tablet-specific hamburger button */
        @media (min-width: 640px) and (max-width: 1023px) {
            .tablet-menu-btn {
                position: fixed;
                top: 1rem;
                left: 1rem;
                z-index: 60;
            }
        }
    </style>
</head>
<body class="bg-brand-900 text-brand-900 font-sans antialiased" x-data="{ sidebarOpen: false }">

<div class="min-h-screen flex">

    <!-- Mobile/Tablet Menu Button (visible on small and medium screens) -->
    <button @click="sidebarOpen = !sidebarOpen" 
            class="lg:hidden fixed top-4 left-4 z-50 h-12 w-12 rounded-lg bg-brand-800 text-white flex items-center justify-center shadow-lg hover:bg-brand-700 transition tablet-menu-btn">
        <svg x-show="!sidebarOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
        <svg x-show="sidebarOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

    {{-- Desktop Sidebar (hidden on mobile/tablet) --}}
    <aside class="w-64 bg-brand-900 text-brand-50 shadow-lg hidden lg:block overflow-y-auto">
        <div class="p-6 font-bold text-lg text-white">
            🌿 CFL Landscape
        </div>

        <nav class="mt-4 px-4 space-y-6 text-sm pb-8">
            <div class="space-y-2">
                <details class="group sidebar-accordion">
                    <summary class="list-none px-2 py-2 text-sm text-brand-50/90 hover:bg-brand-800/60 cursor-pointer rounded flex items-center justify-between">
                        <span class="inline-flex items-center">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M21 13a4 4 0 0 0-3-3.87"/></svg>
                            <span>CRM</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 9l6 6 6-6"/></svg>
                    </summary>
                    <div class="ml-4 mt-1 space-y-1 sidebar-panel">
                        <a href="{{ route('client-hub') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">
                            <span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M12 3L3 10v11h7v-7h4v7h7V10l-9-7z"/></svg><span>Home Dashboard</span></span>
                        </a>
                        <a href="{{ route('contacts.index') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">
                            <span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><circle cx="9" cy="8" r="3"/><circle cx="17" cy="10" r="3"/><path d="M2 21c0-3.314 2.686-6 6-6h2M22 21c0-3.314-2.686-6-6-6h-2"/></svg><span>Contacts</span></span>
                        </a>
                        <a href="{{ route('site-visit.select') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">
                            <span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M21 10h-3l-3 10-4-18-3 8H3"/></svg><span>Site Visits</span></span>
                        </a>
                        <a href="{{ route('todos.index') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">
                            <span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 12l2 2 4-4"/></svg><span>To-Do Board</span></span>
                        </a>
                    </div>
                </details>

                <details class="group sidebar-accordion">
                    <summary class="list-none px-2 py-2 text-sm text-brand-50/90 hover:bg-brand-800/60 cursor-pointer rounded flex items-center justify-between">
                        <span class="inline-flex items-center">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M7 2h7l5 5v13a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/><path d="M14 2v5h5"/></svg>
                            <span>ESTIMATES</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 9l6 6 6-6"/></svg>
                    </summary>
                    <div class="ml-4 mt-1 space-y-1 sidebar-panel">
                        <a href="{{ route('estimates.index') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Estimates List</a>
                    </div>
                </details>

                <details class="group sidebar-accordion">
                    <summary class="list-none px-2 py-2 text-sm text-brand-50/90 hover:bg-brand-800/60 cursor-pointer rounded flex items-center justify-between">
                        <span class="inline-flex items-center">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="M9 10h6M9 14h6"/></svg>
                            <span>JOBS</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 9l6 6 6-6"/></svg>
                    </summary>
                    <div class="ml-4 mt-1 space-y-1 sidebar-panel">
                        <a href="{{ route('jobs.index') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Job List</a>
                    </div>
                </details>

                <details class="group sidebar-accordion">
                    <summary class="list-none px-2 py-2 text-sm text-brand-50/90 hover:bg-brand-800/60 cursor-pointer rounded flex items-center justify-between">
                        <span class="inline-flex items-center">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                            <span>SCHEDULE</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 9l6 6 6-6"/></svg>
                    </summary>
                    <div class="ml-4 mt-1 space-y-1 sidebar-panel">
                        <a href="{{ route('calendar.index') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Site Visit Calendar</a>
                    </div>
                </details>

                @can('manage-catalogs')
                <details class="group sidebar-accordion">
                    <summary class="list-none px-2 py-2 text-sm text-brand-50/90 hover:bg-brand-800/60 cursor-pointer rounded flex items-center justify-between">
                        <span class="inline-flex items-center">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18M3 9h18"/></svg>
                            <span>PRICE LIST</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 9l6 6 6-6"/></svg>
                    </summary>
                    <div class="ml-4 mt-1 space-y-1 sidebar-panel">
                        <a href="{{ route('materials.index') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Materials Catalog</a>
                        <a href="{{ route('labor.index') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Labor Catalog</a>
                        <a href="{{ route('admin.material-categories.index') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Material Categories</a>
                    </div>
                </details>
                @endcan

                <details class="group sidebar-accordion">
                    <summary class="list-none px-2 py-2 text-sm text-brand-50/90 hover:bg-brand-800/60 cursor-pointer rounded flex items-center justify-between">
                        <span class="inline-flex items-center">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/></svg>
                            <span>TIMESHEETS</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 9l6 6 6-6"/></svg>
                    </summary>
                    <div class="ml-4 mt-1 space-y-1 sidebar-panel">
                        <a href="{{ route('timesheets.index') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Timesheet List</a>
                        @can('approve-timesheets')
                        <a href="{{ route('timesheets.approve') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Approve Timesheets</a>
                        @endcan
                    </div>
                </details>

                <details class="group sidebar-accordion">
                    <summary class="list-none px-2 py-2 text-sm text-brand-50/90 hover:bg-brand-800/60 cursor-pointer rounded flex items-center justify-between">
                        <span class="inline-flex items-center">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M14.7 6.3a5 5 0 1 0-8.4 5.4l-4 4a2 2 0 1 0 2.8 2.8l4-4a5 5 0 0 0 5.6-8.2z"/></svg>
                            <span>ASSETS & EQUIPMENT</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 9l6 6 6-6"/></svg>
                    </summary>
                    <div class="ml-4 mt-1 space-y-1 sidebar-panel">
                        <a href="{{ route('assets.index') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Asset Dashboard</a>
                        <a href="{{ route('assets.create') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Add Asset</a>
                        <a href="{{ route('assets.expenses.select-asset') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Add Expense</a>
                        <a href="{{ route('assets.issues.create') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Log Issue</a>
                        <a href="{{ route('assets.reminders.create') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Schedule Reminder</a>
                        <a href="{{ route('asset-reports.index') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Reports</a>
                    </div>
                </details>
            </div>
        </nav>

        {{-- Admin Section (moved to bottom) --}}
        @canany(['manage-catalogs', 'manage-users', 'view-reports'])
        <div class="mt-8 pt-4">
            <h2 class="text-xs font-semibold text-brand-300 uppercase tracking-wide px-4 mb-2">Admin</h2>
            <ul>
                <li>
                    <a href="{{ route('production-rates.index') }}"
                       class="block px-4 py-2 text-sm text-brand-50/90 hover:bg-brand-800/60">
                        <span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M12 3v4M6 7l-2-2M18 7l2-2M4 13h16M5 17h14M7 10h10"/></svg><span>Production Rates</span></span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.budgets.index') }}"
                       class="block px-4 py-2 text-sm text-brand-50/90 hover:bg-brand-800/60">
                        <span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M8 10h8M8 14h5"/></svg><span>Budget</span></span>
                    </a>
                </li>


                <li>
                    <details class="group sidebar-accordion">
                        <summary class="list-none px-4 py-2 text-sm text-brand-50/90 hover:bg-brand-800/60 cursor-pointer rounded flex items-center justify-between">
                            <span class="inline-flex items-center">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
                                <span>Settings</span>
                            </span>
                            <svg class="w-4 h-4 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 9l6 6 6-6"/></svg>
                        </summary>
                                                <div class="ml-6 mt-1 space-y-1 sidebar-panel">
                            @can('manage-users')
                            <a href="{{ route('admin.users.index') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Users</a>
                            @endcan
                            <a href="{{ route('admin.expense-approvals.index') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Expense Approvals</a>
                            <a href="{{ route('admin.company-settings.edit') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Company Settings</a>
                            <a href="{{ route('calculator.templates.gallery') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Calculator Templates</a>
                            <a href="{{ route('admin.divisions.index') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Divisions</a>
                            <a href="{{ route('admin.cost-codes.index') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Cost Codes</a>
                            <a href="{{ route('admin.expense-accounts.index') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Expense Accounts</a>
                        </div>
                    </details>
                </li>


            </ul>
        </div>
        @endcanany

    </aside>

      
    {{-- Mobile Sidebar --}}
    <div class="md:hidden fixed inset-0 bg-black bg-opacity-50 z-40" x-show="sidebarOpen" @click="sidebarOpen = false"></div>

    <aside class="fixed inset-y-0 left-0 bg-brand-900 text-brand-50 shadow-xl z-50 transform transition-transform duration-300 lg:hidden overflow-y-auto"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="w-72 sm:w-80">
        <div class="p-6 font-bold text-lg text-white flex items-center justify-between">
            <span>🌿 CFL Landscape</span>
            <button @click="sidebarOpen = false" class="h-8 w-8 rounded flex items-center justify-center hover:bg-brand-800">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <nav class="mt-4 px-4 space-y-6 text-sm pb-8">
            <div class="space-y-2">
                <details class="group sidebar-accordion">
                    <summary class="list-none px-2 py-2 text-sm text-brand-50/90 hover:bg-brand-800/60 cursor-pointer rounded flex items-center justify-between">
                        <span class="inline-flex items-center">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M21 13a4 4 0 0 0-3-3.87"/></svg>
                            <span>CRM</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 9l6 6 6-6"/></svg>
                    </summary>
                    <div class="ml-4 mt-1 space-y-1 sidebar-panel">
                        <a href="{{ route('client-hub') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">
                            <span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M12 3L3 10v11h7v-7h4v7h7V10l-9-7z"/></svg><span>Home Dashboard</span></span>
                        </a>
                        <a href="{{ route('contacts.index') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">
                            <span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><circle cx="9" cy="8" r="3"/><circle cx="17" cy="10" r="3"/><path d="M2 21c0-3.314 2.686-6 6-6h2M22 21c0-3.314-2.686-6-6-6h-2"/></svg><span>Contacts</span></span>
                        </a>
                        <a href="{{ route('site-visit.select') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">
                            <span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M21 10h-3l-3 10-4-18-3 8H3"/></svg><span>Site Visits</span></span>
                        </a>
                        <a href="{{ route('todos.index') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">
                            <span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 12l2 2 4-4"/></svg><span>To-Do Board</span></span>
                        </a>
                    </div>
                </details>
                <details class="group sidebar-accordion">
                    <summary class="list-none px-2 py-2 text-sm text-brand-50/90 hover:bg-brand-800/60 cursor-pointer rounded flex items-center justify-between">
                        <span class="inline-flex items-center">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M7 2h7l5 5v13a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/><path d="M14 2v5h5"/></svg>
                            <span>ESTIMATES</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 9l6 6 6-6"/></svg>
                    </summary>
                    <div class="ml-4 mt-1 space-y-1 sidebar-panel">
                        <a href="{{ route('estimates.index') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Estimates List</a>
                    </div>
                </details>

                <details class="group sidebar-accordion">
                    <summary class="list-none px-2 py-2 text-sm text-brand-50/90 hover:bg-brand-800/60 cursor-pointer rounded flex items-center justify-between">
                        <span class="inline-flex items-center">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="M9 10h6M9 14h6"/></svg>
                            <span>JOBS</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 9l6 6 6-6"/></svg>
                    </summary>
                    <div class="ml-4 mt-1 space-y-1 sidebar-panel">
                        <a href="{{ route('jobs.index') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Job List</a>
                    </div>
                </details>

                <details class="group sidebar-accordion">
                    <summary class="list-none px-2 py-2 text-sm text-brand-50/90 hover:bg-brand-800/60 cursor-pointer rounded flex items-center justify-between">
                        <span class="inline-flex items-center">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                            <span>SCHEDULE</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 9l6 6 6-6"/></svg>
                    </summary>
                    <div class="ml-4 mt-1 space-y-1 sidebar-panel">
                        <a href="{{ route('calendar.index') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Site Visit Calendar</a>
                    </div>
                </details>

                @can('manage-catalogs')
                <details class="group sidebar-accordion">
                    <summary class="list-none px-2 py-2 text-sm text-brand-50/90 hover:bg-brand-800/60 cursor-pointer rounded flex items-center justify-between">
                        <span class="inline-flex items-center">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18M3 9h18"/></svg>
                            <span>PRICE LIST</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 9l6 6 6-6"/></svg>
                    </summary>
                    <div class="ml-4 mt-1 space-y-1 sidebar-panel">
                        <a href="{{ route('materials.index') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Materials Catalog</a>
                        <a href="{{ route('labor.index') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Labor Catalog</a>
                        <a href="{{ route('admin.material-categories.index') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Material Categories</a>
                    </div>
                </details>
                @endcan

                <details class="group sidebar-accordion">
                    <summary class="list-none px-2 py-2 text-sm text-brand-50/90 hover:bg-brand-800/60 cursor-pointer rounded flex items-center justify-between">
                        <span class="inline-flex items-center">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/></svg>
                            <span>TIMESHEETS</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 9l6 6 6-6"/></svg>
                    </summary>
                    <div class="ml-4 mt-1 space-y-1 sidebar-panel">
                        <a href="{{ route('timesheets.index') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Timesheet List</a>
                        @can('approve-timesheets')
                        <a href="{{ route('timesheets.approve') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Approve Timesheets</a>
                        @endcan
                    </div>
                </details>

                <details class="group sidebar-accordion">
                    <summary class="list-none px-2 py-2 text-sm text-brand-50/90 hover:bg-brand-800/60 cursor-pointer rounded flex items-center justify-between">
                        <span class="inline-flex items-center">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M14.7 6.3a5 5 0 1 0-8.4 5.4l-4 4a2 2 0 1 0 2.8 2.8l4-4a5 5 0 0 0 5.6-8.2z"/></svg>
                            <span>ASSETS & EQUIPMENT</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 9l6 6 6-6"/></svg>
                    </summary>
                    <div class="ml-4 mt-1 space-y-1 sidebar-panel">
                        <a href="{{ route('assets.index') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Asset Dashboard</a>
                        <a href="{{ route('assets.create') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Add Asset</a>
                        <a href="{{ route('assets.expenses.select-asset') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Add Expense</a>
                        <a href="{{ route('assets.issues.create') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Log Issue</a>
                        <a href="{{ route('assets.reminders.create') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Schedule Reminder</a>
                        <a href="{{ route('asset-reports.index') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Reports</a>
                    </div>
                </details>

                <a href="{{ route('production-rates.index') }}" class="block px-2 py-2 rounded text-brand-50/90 hover:bg-brand-800/60">
                    <span class="inline-flex items-center">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Production Rates</span>
                    </span>
                </a>

                <a href="{{ route('admin.budgets.index') }}" class="block px-2 py-2 rounded text-brand-50/90 hover:bg-brand-800/60">
                    <span class="inline-flex items-center">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M8 10h8M8 14h5"/></svg>
                        <span>Budget</span>
                    </span>
                </a>

                <details class="group sidebar-accordion">
                    <summary class="list-none px-2 py-2 text-sm text-brand-50/90 hover:bg-brand-800/60 cursor-pointer rounded flex items-center justify-between">
                        <span class="inline-flex items-center">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
                            <span>Settings</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 9l6 6 6-6"/></svg>
                    </summary>
                    <div class="ml-4 mt-1 space-y-1 sidebar-panel">
                        @can('manage-users')
                        <a href="{{ route('admin.users.index') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Users</a>
                        @endcan
                        <a href="{{ route('admin.expense-approvals.index') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Expense Approvals</a>
                        <a href="{{ route('admin.company-settings.edit') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Company Settings</a>
                        <a href="{{ route('calculator.templates.gallery') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Calculator Templates</a>
                        <a href="{{ route('admin.divisions.index') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Divisions</a>
                        <a href="{{ route('admin.cost-codes.index') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Cost Codes</a>
                        <a href="{{ route('admin.expense-accounts.index') }}" class="block px-2 py-1 rounded text-brand-50/90 hover:bg-brand-800/60">Expense Accounts</a>
                    </div>
                </details>
            </div>
        </nav>

    </aside>

    {{-- Main Content --}}
    <div class="flex-1 flex flex-col min-h-screen bg-brand-900">

        {{-- Breadcrumb Slot --}}
        @isset($header)
            <div class="bg-brand-50 ">
                <div class="max-w-7xl mx-auto py-3 px-4 sm:px-6 lg:px-8 text-sm text-brand-600">
                    {{ $header }}
                </div>
            </div>
        @endisset

        {{-- Main Page Content with responsive padding --}}
        <main class="flex-1 p-2 sm:p-4 md:p-5 lg:p-6">
            <div class="h-full w-full rounded-[28px] sm:rounded-[32px] bg-brand-50 p-4 sm:p-6 md:p-7 lg:p-8 shadow-2xl">
                @yield('content')
            </div>
        </main>
    </div>
</div>

<!-- Toasts -->
<div id="toastRoot" class="fixed bottom-4 right-4 z-[200] space-y-2"></div>
<script>
(function(){
  if (window.showToast) return; // don't redefine
  function el(tag, cls){ const e = document.createElement(tag); if(cls) e.className = cls; return e; }
  function makeToast(msg, type){
    const root = document.getElementById('toastRoot'); if (!root) return;
    const wrap = el('div', 'pointer-events-auto min-w-[220px] max-w-sm rounded shadow-lg border p-3 text-sm flex items-start gap-2 transition-all duration-300');
    const colors = type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : type === 'error' ? 'bg-red-50 border-red-200 text-red-900' : 'bg-gray-50 border-gray-200 text-gray-900';
    wrap.className += ' ' + colors;
    const text = el('div'); text.textContent = String(msg || '');
    const btn = el('button','ml-auto text-xs opacity-60 hover:opacity-100'); btn.textContent = 'Dismiss'; btn.onclick = ()=>{ wrap.style.opacity='0'; setTimeout(()=>wrap.remove(), 200); };
    wrap.appendChild(text); wrap.appendChild(btn); root.appendChild(wrap);
    setTimeout(()=>{ wrap.style.opacity='0'; setTimeout(()=>wrap.remove(), 250); }, 3500);
  }
  window.showToast = makeToast;
})();
</script>
@if (session('success'))
<script>
  document.addEventListener('DOMContentLoaded', function(){
    if (window.showToast) showToast(@json(session('success')), 'success');
  });
</script>
@endif
@if (session('error'))
<script>
  document.addEventListener('DOMContentLoaded', function(){
    if (window.showToast) showToast(@json(session('error')), 'error');
  });
</script>
@endif

{{-- Google Places – Extended Component Library (Place Picker) --}}
@if (config('services.google_places.key'))
  <script type="module" src="https://ajax.googleapis.com/ajax/libs/@googlemaps/extended-component-library/0.6.11/index.min.js"></script>
  <gmpx-api-loader key="{{ config('services.google_places.key') }}" solution-channel="GMP_GE_placepicker_v2"></gmpx-api-loader>
  <script>window.PLACES_COUNTRIES = "{{ config('services.google_places.country', 'us') }}".split(',');</script>
@endif

 @stack('scripts')
</body>
</html>
