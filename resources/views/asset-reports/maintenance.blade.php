@extends('layouts.sidebar')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        {{-- Branded Header --}}
        <section class="rounded-[20px] sm:rounded-[28px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-6 sm:p-8 shadow-2xl border border-brand-800/40">
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center flex-shrink-0">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-7 w-7 text-white">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Asset Reports</p>
                    <h1 class="text-2xl sm:text-3xl font-semibold text-white mt-1">Maintenance History</h1>
                    <p class="text-sm text-brand-100/85 mt-1">Track all maintenance activities and service records.</p>
                </div>
                <a href="{{ route('asset-reports.index') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl text-sm font-medium transition-all">
                    Back to Reports
                </a>
            </div>
        </section>

        {{-- Filters --}}
        <div class="rounded-2xl bg-white border-2 border-brand-100 shadow-sm p-6">
            <form method="GET" action="{{ route('asset-reports.maintenance') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-brand-800 mb-2">Start Date</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" 
                        class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-brand-800 mb-2">End Date</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" 
                        class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-brand-800 mb-2">Asset</label>
                    <select name="asset_id" class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                        <option value="">All Assets</option>
                        @foreach($assets as $asset)
                            <option value="{{ $asset->id }}" {{ $assetId == $asset->id ? 'selected' : '' }}>
                                {{ $asset->name }}
                            </option>
                        @endforeach
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
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-2xl bg-white border-2 border-brand-100 shadow-sm p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-brand-600">Total Services</p>
                        <p class="text-2xl font-bold text-brand-900 mt-1">{{ $maintenanceRecords->count() }}</p>
                    </div>
                    <div class="h-12 w-12 rounded-xl bg-brand-100 flex items-center justify-center">
                        <svg class="h-6 w-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white border-2 border-orange-100 shadow-sm p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-orange-600">Completed Services</p>
                        <p class="text-2xl font-bold text-orange-900 mt-1">{{ $maintenanceRecords->whereNotNull('completed_at')->count() }}</p>
                    </div>
                    <div class="h-12 w-12 rounded-xl bg-orange-100 flex items-center justify-center">
                        <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white border-2 border-green-100 shadow-sm p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-green-600">Scheduled Services</p>
                        <p class="text-2xl font-bold text-green-900 mt-1">
                            {{ $maintenanceRecords->whereNull('completed_at')->count() }}
                        </p>
                    </div>
                    <div class="h-12 w-12 rounded-xl bg-green-100 flex items-center justify-center">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Maintenance Records Table --}}
        <div class="rounded-2xl bg-white border-2 border-brand-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b-2 border-brand-100">
                <h2 class="text-lg font-bold text-brand-900">Maintenance Records</h2>
            </div>

            @if($maintenanceRecords->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-brand-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-brand-900 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-brand-900 uppercase tracking-wider">Asset</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-brand-900 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-brand-900 uppercase tracking-wider">Notes</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-brand-900 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-brand-900 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-100">
                            @foreach($maintenanceRecords as $record)
                                <tr class="hover:bg-brand-50/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($record->completed_at)
                                            <span class="font-semibold text-brand-900">{{ $record->completed_at->format('M d, Y') }}</span>
                                        @else
                                            <span class="text-sm text-brand-500 italic">Scheduled: {{ $record->scheduled_at?->format('M d, Y') ?? 'N/A' }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            @if($record->asset->image_path)
                                                <img src="{{ asset('storage/' . $record->asset->image_path) }}" alt="{{ $record->asset->name }}" class="h-10 w-10 rounded-lg object-cover">
                                            @else
                                                <div class="h-10 w-10 rounded-lg bg-brand-100 flex items-center justify-center">
                                                    <svg class="h-5 w-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                    </svg>
                                                </div>
                                            @endif
                                            <div>
                                                <p class="font-semibold text-brand-900">{{ $record->asset->name }}</p>
                                                @if($record->asset->asset_tag)
                                                    <p class="text-xs text-brand-600">#{{ $record->asset->asset_tag }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold
                                            @if($record->type == 'Routine') bg-green-100 text-green-800
                                            @elseif($record->type == 'Repair') bg-orange-100 text-orange-800
                                            @elseif($record->type == 'Emergency') bg-red-100 text-red-800
                                            @else bg-brand-100 text-brand-800
                                            @endif">
                                            {{ $record->type ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-brand-900">{{ $record->notes ? Str::limit($record->notes, 50) : 'No notes' }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($record->completed_at)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-green-100 text-green-800">
                                                Completed
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-yellow-100 text-yellow-800">
                                                Scheduled
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('assets.show', $record->asset) }}" class="inline-flex items-center px-3 py-1.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition-all">
                                            View Asset
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <h3 class="mt-4 text-lg font-semibold text-brand-900">No Maintenance Records</h3>
                    <p class="mt-2 text-sm text-brand-600">No maintenance records found for the selected date range.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
