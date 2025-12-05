@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <section class="rounded-[20px] sm:rounded-[28px] bg-gradient-to-br from-blue-900 via-blue-800 to-brand-700 text-white p-6 sm:p-8 shadow-2xl border border-blue-800/40">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center flex-shrink-0">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-7 w-7 text-white">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-blue-200/80">Asset Reports</p>
                    <h1 class="text-2xl sm:text-3xl font-semibold text-white mt-1">Usage Log Report</h1>
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
        <form method="GET" class="grid gap-4 md:grid-cols-4">
            <div>
                <label class="block text-sm font-semibold text-brand-800 mb-2">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
            </div>
            <div>
                <label class="block text-sm font-semibold text-brand-800 mb-2">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
            </div>
            <div>
                <label class="block text-sm font-semibold text-brand-800 mb-2">Asset</label>
                <select name="asset_id" class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Assets</option>
                    @foreach($assets as $asset)
                        <option value="{{ $asset->id }}" {{ $assetId == $asset->id ? 'selected' : '' }}>{{ $asset->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-brand-800 mb-2">User</label>
                <select name="user_id" class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-4 flex justify-end gap-3">
                <x-brand-button href="{{ route('asset-reports.usage') }}" variant="secondary">Clear</x-brand-button>
                <x-brand-button type="submit" variant="primary">Apply Filters</x-brand-button>
            </div>
        </form>
    </div>

    {{-- Results --}}
    <div class="rounded-2xl bg-white border-2 border-brand-100 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-brand-900">Usage Logs ({{ $usageLogs->count() }} results)</h2>
        </div>

        @if($usageLogs->count() > 0)
            <div class="space-y-4">
                @foreach($usageLogs as $log)
                    <div class="border-2 border-brand-100 rounded-xl p-4 hover:border-brand-300 transition">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="font-bold text-brand-900 text-lg">{{ $log->asset->name ?? 'Unknown Asset' }}</h3>
                                    <span class="text-xs px-2.5 py-1 rounded-full {{ $log->isCheckedOut() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $log->isCheckedOut() ? 'Checked Out' : 'Returned' }}
                                    </span>
                                </div>
                                
                                <div class="grid md:grid-cols-2 gap-x-6 gap-y-2 text-sm text-brand-700">
                                    <div>
                                        <strong class="text-brand-800">User:</strong> {{ $log->user->name ?? 'Unknown' }}
                                    </div>
                                    <div>
                                        <strong class="text-brand-800">Checked Out:</strong> {{ $log->checked_out_at->format('M j, Y g:i A') }}
                                    </div>
                                    @if($log->checked_in_at)
                                        <div>
                                            <strong class="text-brand-800">Checked In:</strong> {{ $log->checked_in_at->format('M j, Y g:i A') }}
                                        </div>
                                        <div>
                                            <strong class="text-brand-800">Duration:</strong> {{ $log->checked_out_at->diffForHumans($log->checked_in_at, true) }}
                                        </div>
                                    @endif
                                    @if($log->mileage_out || $log->mileage_in)
                                        <div class="md:col-span-2">
                                            <strong class="text-brand-800">Mileage/Hours:</strong>
                                            {{ $log->mileage_out ? number_format($log->mileage_out) : 'N/A' }}
                                            @if($log->mileage_in)
                                                → {{ number_format($log->mileage_in) }}
                                                <span class="text-brand-500">({{ number_format($log->mileage_in - $log->mileage_out) }} used)</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                @if($log->notes)
                                    <div class="mt-3 p-3 bg-brand-50 rounded-lg">
                                        <p class="text-xs font-semibold text-brand-700 mb-1">Notes:</p>
                                        <p class="text-sm text-brand-800 whitespace-pre-line">{{ $log->notes }}</p>
                                    </div>
                                @endif

                                @if($log->inspection_data && count($log->inspection_data) > 0)
                                    <details class="mt-3">
                                        <summary class="text-sm font-semibold text-brand-600 cursor-pointer hover:text-brand-800">
                                            <span class="inline-flex items-center gap-1">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                                </svg>
                                                View Inspection Details ({{ count($log->inspection_data) }} items checked)
                                            </span>
                                        </summary>
                                        <div class="mt-2 p-3 bg-brand-50 rounded-lg">
                                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                                @foreach($log->inspection_data as $key => $value)
                                                    @if($value)
                                                        <div class="flex items-center gap-2 text-sm text-brand-700">
                                                            <svg class="h-4 w-4 text-green-600 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                                <path d="M5 13l4 4L19 7"/>
                                                            </svg>
                                                            <span>{{ ucwords(str_replace('_', ' ', $key)) }}</span>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </details>
                                @endif
                            </div>

                            <a href="{{ route('assets.show', $log->asset) }}" class="text-brand-600 hover:text-brand-800 text-sm font-medium whitespace-nowrap">
                                View Asset →
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <svg class="h-12 w-12 mx-auto text-brand-300 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-brand-500 font-medium">No usage logs found</p>
                <p class="text-sm text-brand-400 mt-1">Try adjusting your filter criteria</p>
            </div>
        @endif
    </div>
</div>
@endsection
