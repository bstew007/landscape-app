@extends('layouts.sidebar')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        {{-- Branded Header --}}
        <section class="rounded-[20px] sm:rounded-[28px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-6 sm:p-8 shadow-2xl border border-brand-800/40">
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center flex-shrink-0">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-7 w-7 text-white">
                        <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Asset Reports</p>
                    <h1 class="text-2xl sm:text-3xl font-semibold text-white mt-1">Cost Analysis</h1>
                    <p class="text-sm text-brand-100/85 mt-1">Track purchase costs, maintenance expenses, and total cost of ownership.</p>
                </div>
                <a href="{{ route('asset-reports.index') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl text-sm font-medium transition-all">
                    Back to Reports
                </a>
            </div>
        </section>

        {{-- Filters --}}
        <div class="rounded-2xl bg-white border-2 border-brand-100 shadow-sm p-6">
            <form method="GET" action="{{ route('asset-reports.costs') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-brand-800 mb-2">Asset Type</label>
                    <select name="asset_type" class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                        <option value="">All Types</option>
                        <option value="Vehicle" {{ request('asset_type') == 'Vehicle' ? 'selected' : '' }}>Vehicles</option>
                        <option value="Equipment" {{ request('asset_type') == 'Equipment' ? 'selected' : '' }}>Equipment</option>
                        <option value="Tool" {{ request('asset_type') == 'Tool' ? 'selected' : '' }}>Tools</option>
                        <option value="Trailer" {{ request('asset_type') == 'Trailer' ? 'selected' : '' }}>Trailers</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-brand-800 mb-2">Sort By</label>
                    <select name="sort_by" class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                        <option value="total_cost" {{ request('sort_by') == 'total_cost' ? 'selected' : '' }}>Total Cost (High to Low)</option>
                        <option value="purchase_cost" {{ request('sort_by') == 'purchase_cost' ? 'selected' : '' }}>Purchase Cost</option>
                        <option value="maintenance_cost" {{ request('sort_by') == 'maintenance_cost' ? 'selected' : '' }}>Maintenance Cost</option>
                        <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Asset Name</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-xl transition-all shadow-sm">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="rounded-2xl bg-white border-2 border-brand-100 shadow-sm p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-brand-600">Total Assets</p>
                        <p class="text-2xl font-bold text-brand-900 mt-1">{{ $assetCosts->count() }}</p>
                    </div>
                    <div class="h-12 w-12 rounded-xl bg-brand-100 flex items-center justify-center">
                        <svg class="h-6 w-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white border-2 border-green-100 shadow-sm p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-green-600">Total Purchase Cost</p>
                        <p class="text-2xl font-bold text-green-900 mt-1">${{ number_format($totalPurchaseCost, 2) }}</p>
                    </div>
                    <div class="h-12 w-12 rounded-xl bg-green-100 flex items-center justify-center">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white border-2 border-orange-100 shadow-sm p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-orange-600">Total Expenses</p>
                        <p class="text-2xl font-bold text-orange-900 mt-1">${{ number_format($totalMaintenanceCost, 2) }}</p>
                        <div class="mt-2 text-xs text-orange-700 space-y-0.5">
                            <div>Fuel: ${{ number_format($totalFuelCost, 2) }}</div>
                            <div>Repairs: ${{ number_format($totalRepairCost, 2) }}</div>
                            <div>General: ${{ number_format($totalGeneralCost, 2) }}</div>
                        </div>
                    </div>
                    <div class="h-12 w-12 rounded-xl bg-orange-100 flex items-center justify-center">
                        <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white border-2 border-red-100 shadow-sm p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-red-600">Total Cost of Ownership</p>
                        <p class="text-2xl font-bold text-red-900 mt-1">${{ number_format($totalCostOfOwnership, 2) }}</p>
                    </div>
                    <div class="h-12 w-12 rounded-xl bg-red-100 flex items-center justify-center">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cost Breakdown Table --}}
        <div class="rounded-2xl bg-white border-2 border-brand-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b-2 border-brand-100">
                <h2 class="text-lg font-bold text-brand-900">Asset Cost Breakdown</h2>
            </div>

            @if($assetCosts->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-brand-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-brand-900 uppercase tracking-wider">Asset</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-brand-900 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-brand-900 uppercase tracking-wider">Purchase Cost</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-brand-900 uppercase tracking-wider">Fuel</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-brand-900 uppercase tracking-wider">Repairs</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-brand-900 uppercase tracking-wider">General</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-brand-900 uppercase tracking-wider">Total Expenses</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-brand-900 uppercase tracking-wider">Total Issues</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-brand-900 uppercase tracking-wider">Total Cost</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-brand-900 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-100">
                            @foreach($assetCosts as $asset)
                                <tr class="hover:bg-brand-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            @if($asset->image_path)
                                                <img src="{{ asset('storage/' . $asset->image_path) }}" alt="{{ $asset->name }}" class="h-10 w-10 rounded-lg object-cover">
                                            @else
                                                <div class="h-10 w-10 rounded-lg bg-brand-100 flex items-center justify-center">
                                                    <svg class="h-5 w-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                    </svg>
                                                </div>
                                            @endif
                                            <div>
                                                <p class="font-semibold text-brand-900">{{ $asset->name }}</p>
                                                @if($asset->asset_tag)
                                                    <p class="text-xs text-brand-600">#{{ $asset->asset_tag }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-brand-100 text-brand-800">
                                            {{ $asset->asset_type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="font-semibold text-green-700">
                                            ${{ number_format($asset->purchase_cost ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="font-semibold text-blue-700">
                                            ${{ number_format($asset->fuel_cost ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="font-semibold text-purple-700">
                                            ${{ number_format($asset->repair_cost ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="font-semibold text-indigo-700">
                                            ${{ number_format($asset->general_cost ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="font-semibold text-orange-700">
                                            ${{ number_format($asset->total_maintenance_cost, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if($asset->issue_count > 0)
                                            <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold bg-red-100 text-red-800">
                                                {{ $asset->issue_count }} issue{{ $asset->issue_count != 1 ? 's' : '' }}
                                            </span>
                                        @else
                                            <span class="text-sm text-brand-400">None</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="font-bold text-red-700 text-lg">
                                            ${{ number_format(($asset->purchase_cost ?? 0) + $asset->total_maintenance_cost, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('assets.show', $asset) }}" class="inline-flex items-center px-3 py-1.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition-all">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="mt-4 text-lg font-semibold text-brand-900">No Cost Data Available</h3>
                    <p class="mt-2 text-sm text-brand-600">No assets found matching your filter criteria.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
