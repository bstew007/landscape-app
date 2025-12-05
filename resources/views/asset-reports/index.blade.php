@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <section class="rounded-[20px] sm:rounded-[28px] lg:rounded-[32px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-6 sm:p-8 shadow-2xl border border-brand-800/40">
        <div class="flex items-center gap-4">
            <div class="h-14 w-14 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center flex-shrink-0">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-7 w-7 text-white">
                    <path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Asset Management</p>
                <h1 class="text-2xl sm:text-3xl font-semibold text-white mt-1">Reports & Analytics</h1>
                <p class="text-sm text-brand-100/85 mt-1">Comprehensive insights into asset usage, maintenance, and costs.</p>
            </div>
        </div>
    </section>

    {{-- Report Cards Grid --}}
    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        {{-- Usage Report --}}
        <a href="{{ route('asset-reports.usage') }}" class="group">
            <div class="rounded-2xl bg-white border-2 border-brand-100 shadow-sm p-6 hover:border-brand-400 hover:shadow-lg transition-all">
                <div class="flex items-start justify-between mb-4">
                    <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                        <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <svg class="h-5 w-5 text-brand-300 group-hover:text-brand-600 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-brand-900 mb-2">Usage Log Report</h3>
                <p class="text-sm text-brand-600">Track who used which assets, when they checked them out/in, and view inspection data.</p>
                <div class="mt-4 flex items-center gap-2 text-xs text-brand-500">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    <span>Filterable by date range, asset, user</span>
                </div>
            </div>
        </a>

        {{-- Utilization Report --}}
        <a href="{{ route('asset-reports.utilization') }}" class="group">
            <div class="rounded-2xl bg-white border-2 border-brand-100 shadow-sm p-6 hover:border-brand-400 hover:shadow-lg transition-all">
                <div class="flex items-start justify-between mb-4">
                    <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center">
                        <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                        </svg>
                    </div>
                    <svg class="h-5 w-5 text-brand-300 group-hover:text-brand-600 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-brand-900 mb-2">Utilization Report</h3>
                <p class="text-sm text-brand-600">See which assets are used most frequently, total hours logged, and mileage/hours accumulated.</p>
                <div class="mt-4 flex items-center gap-2 text-xs text-brand-500">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"/>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                    <span>Hours used, checkout frequency</span>
                </div>
            </div>
        </a>

        {{-- Maintenance Report --}}
        <a href="{{ route('asset-reports.maintenance') }}" class="group">
            <div class="rounded-2xl bg-white border-2 border-brand-100 shadow-sm p-6 hover:border-brand-400 hover:shadow-lg transition-all">
                <div class="flex items-start justify-between mb-4">
                    <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center">
                        <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                        </svg>
                    </div>
                    <svg class="h-5 w-5 text-brand-300 group-hover:text-brand-600 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-brand-900 mb-2">Maintenance History</h3>
                <p class="text-sm text-brand-600">Review all completed and scheduled maintenance activities with detailed logs.</p>
                <div class="mt-4 flex items-center gap-2 text-xs text-brand-500">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    <span>Filter by asset, type, date range</span>
                </div>
            </div>
        </a>

        {{-- Issues Report --}}
        <a href="{{ route('asset-reports.issues') }}" class="group">
            <div class="rounded-2xl bg-white border-2 border-brand-100 shadow-sm p-6 hover:border-brand-400 hover:shadow-lg transition-all">
                <div class="flex items-start justify-between mb-4">
                    <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center">
                        <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                    </div>
                    <svg class="h-5 w-5 text-brand-300 group-hover:text-brand-600 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-brand-900 mb-2">Issues & Repairs</h3>
                <p class="text-sm text-brand-600">Track all reported issues, severity levels, and resolution status across all assets.</p>
                <div class="mt-4 flex items-center gap-2 text-xs text-brand-500">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <span>Filter by status, severity, asset</span>
                </div>
            </div>
        </a>

        {{-- Costs Report --}}
        <a href="{{ route('asset-reports.costs') }}" class="group">
            <div class="rounded-2xl bg-white border-2 border-brand-100 shadow-sm p-6 hover:border-brand-400 hover:shadow-lg transition-all">
                <div class="flex items-start justify-between mb-4">
                    <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-amber-500 to-amber-600 flex items-center justify-center">
                        <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="1" x2="12" y2="23"/>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                    </div>
                    <svg class="h-5 w-5 text-brand-300 group-hover:text-brand-600 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-brand-900 mb-2">Cost Analysis</h3>
                <p class="text-sm text-brand-600">Analyze maintenance and operational costs per asset over time.</p>
                <div class="mt-4 flex items-center gap-2 text-xs text-brand-500">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    <span>Year-to-date by default</span>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection
