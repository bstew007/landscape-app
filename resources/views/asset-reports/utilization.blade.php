@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <section class="rounded-[20px] sm:rounded-[28px] bg-gradient-to-br from-green-900 via-green-800 to-brand-700 text-white p-6 sm:p-8 shadow-2xl border border-green-800/40">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center flex-shrink-0">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-7 w-7 text-white">
                        <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-green-200/80">Asset Reports</p>
                    <h1 class="text-2xl sm:text-3xl font-semibold text-white mt-1">Utilization Report</h1>
                </div>
            </div>
            <x-brand-button href="{{ route('asset-reports.index') }}" variant="outline" class="border-white/30 text-white hover:bg-white/10">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Back to Reports
            </x-brand-button>
        </div>
    </section>

    {{-- Filters --}}
    <div class="rounded-2xl bg-white border-2 border-brand-100 shadow-sm p-6">
        <form method="GET" class="grid gap-4 md:grid-cols-3">
            <div>
                <label class="block text-sm font-semibold text-brand-800 mb-2">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
            </div>
            <div>
                <label class="block text-sm font-semibold text-brand-800 mb-2">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
            </div>
            <div class="flex items-end gap-3">
                <x-brand-button href="{{ route('asset-reports.utilization') }}" variant="secondary" class="flex-1">Clear</x-brand-button>
                <x-brand-button type="submit" variant="primary" class="flex-1">Apply</x-brand-button>
            </div>
        </form>
    </div>

    {{-- Results --}}
    <div class="rounded-2xl bg-white border-2 border-brand-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b-2 border-brand-100">
            <h2 class="text-lg font-bold text-brand-900">Asset Utilization Summary</h2>
            <p class="text-sm text-brand-600 mt-1">{{ \Carbon\Carbon::parse($startDate)->format('M j, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M j, Y') }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-brand-50 border-b-2 border-brand-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-brand-700 uppercase tracking-wider">Asset</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-brand-700 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-brand-700 uppercase tracking-wider">Times Used</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-brand-700 uppercase tracking-wider">Total Hours</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-brand-700 uppercase tracking-wider">Mileage/Hours Used</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-brand-700 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-100">
                    @forelse($assetStats as $asset)
                        <tr class="hover:bg-brand-50/50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-semibold text-brand-900">{{ $asset->name }}</div>
                                @if($asset->identifier)
                                    <div class="text-xs text-brand-500 font-mono">#{{ $asset->identifier }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-brand-700">
                                {{ ucwords(str_replace('_', ' ', $asset->type)) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="text-lg font-bold text-brand-900">{{ $asset->total_uses }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="text-lg font-bold text-green-600">{{ number_format($asset->total_usage_hours, 1) }}</span>
                                <span class="text-xs text-brand-500 ml-1">hrs</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($asset->total_mileage_used > 0)
                                    <span class="text-lg font-bold text-blue-600">{{ number_format($asset->total_mileage_used) }}</span>
                                @else
                                    <span class="text-sm text-brand-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="text-xs font-bold rounded-full px-2.5 py-1
                                    @if($asset->status === 'active') bg-emerald-100 text-emerald-800
                                    @elseif($asset->status === 'in_maintenance') bg-amber-100 text-amber-800
                                    @else bg-gray-100 text-gray-700
                                    @endif">
                                    {{ ucwords(str_replace('_', ' ', $asset->status)) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <svg class="h-12 w-12 mx-auto text-brand-300 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                                </svg>
                                <p class="text-brand-500 font-medium">No utilization data found</p>
                                <p class="text-sm text-brand-400 mt-1">Try adjusting your date range</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
