@extends('layouts.sidebar')

@section('content')
<div class="space-y-8">
    <section class="rounded-[20px] sm:rounded-[28px] lg:rounded-[32px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-4 sm:p-6 lg:p-8 shadow-2xl border border-brand-800/40 relative overflow-hidden">
        <div class="flex flex-wrap items-start gap-4 sm:gap-6">
            <div class="space-y-2 sm:space-y-3 max-w-2xl">
                <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Operations</p>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-semibold">Assets & Equipment</h1>
                <p class="text-xs sm:text-sm text-brand-100/85">Track vehicles, trailers, and landscape equipment with maintenance schedules and issue logging.</p>
            </div>
            <div class="flex flex-wrap gap-2 sm:gap-3 ml-auto w-full sm:w-auto">
                <x-secondary-button as="a" href="{{ route('assets.issues.create') }}" class="bg-white/10 text-white border-white/40 hover:bg-white/20 text-xs sm:text-sm flex-1 sm:flex-none justify-center">
                    <svg class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    Log Issue
                </x-secondary-button>
                <x-secondary-button as="a" href="{{ route('assets.reminders.create') }}" class="bg-white/10 text-white border-white/40 hover:bg-white/20 text-xs sm:text-sm flex-1 sm:flex-none justify-center">
                    <svg class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="13" r="7"/><path d="M12 10v4l3 2M7 3h3M14 3h3"/></svg>
                    Set Reminder
                </x-secondary-button>
                <x-brand-button href="{{ route('assets.create') }}" variant="muted" class="flex-1 sm:flex-none justify-center">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    Add Asset
                </x-brand-button>
            </div>
        </div>
        <dl class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mt-6 sm:mt-8 text-sm text-brand-100">
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Total Assets</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($summary['total']) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Active Fleet</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($summary['active']) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Service Due (14d)</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($summary['maintenance_due']) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Open Issues</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($summary['open_issues']) }}</dd>
            </div>
        </dl>
    </section>

    <section class="rounded-[20px] sm:rounded-[28px] lg:rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden">
        <div class="p-4 sm:p-5 lg:p-7 space-y-4 sm:space-y-6">
            <form method="GET" class="grid gap-3 sm:gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="block text-xs uppercase tracking-wide text-brand-400 mb-1.5 font-medium">Search</label>
                    <input type="text" name="search" placeholder="Asset name, VIN, assignment"
                           value="{{ $search }}" class="w-full rounded-full border-brand-200 bg-white text-sm px-4 py-2.5 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-wide text-brand-400 mb-1.5 font-medium">Status</label>
                    <select name="status" class="w-full rounded-full border-brand-200 bg-white text-sm px-4 py-2.5 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                        <option value="">All Statuses</option>
                        @foreach (\App\Models\Asset::STATUSES as $option)
                            <option value="{{ $option }}" @selected($status === $option)>{{ ucwords(str_replace('_', ' ', $option)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-wide text-brand-400 mb-1.5 font-medium">Type</label>
                    <select name="type" class="w-full rounded-full border-brand-200 bg-white text-sm px-4 py-2.5 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                        <option value="">All Types</option>
                        @foreach (\App\Models\Asset::TYPES as $option)
                            <option value="{{ $option }}" @selected($type === $option)>{{ ucwords(str_replace('_', ' ', $option)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-wide text-brand-400 mb-1.5 font-medium">Assigned To</label>
                    <select name="assigned_to" class="w-full rounded-full border-brand-200 bg-white text-sm px-4 py-2.5 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                        <option value="">All Assignments</option>
                        @foreach ($assignedOptions as $person)
                            <option value="{{ $person }}" @selected($assignedTo === $person)>{{ $person }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-wide text-brand-400 mb-1.5 font-medium">Service Window</label>
                    <select name="service_window" class="w-full rounded-full border-brand-200 bg-white text-sm px-4 py-2.5 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                        <option value="">Any</option>
                        <option value="upcoming" @selected($serviceWindow === 'upcoming')>Upcoming (30d)</option>
                        <option value="overdue" @selected($serviceWindow === 'overdue')>Overdue</option>
                    </select>
                </div>
                <div class="flex items-end md:col-span-3">
                    <x-brand-button type="submit" class="w-full justify-center" variant="outline">
                        <svg class="h-4 w-4 mr-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                        Apply Filters
                    </x-brand-button>
                </div>
            </form>
        </div>

        <div class="border-t border-brand-100/60">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3 p-4 sm:p-5">
                @forelse ($assets as $asset)
                    <div class="bg-white rounded-2xl border-2 border-brand-100 shadow-sm hover:shadow-md transition-all p-4 flex flex-col">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1 min-w-0">
                                <h2 class="text-lg font-bold text-brand-900 truncate">{{ $asset->name }}</h2>
                                <p class="text-sm text-brand-600">{{ ucwords(str_replace('_', ' ', $asset->type)) }}</p>
                                @if($asset->identifier)
                                    <p class="text-xs text-brand-400 font-mono">#{{ $asset->identifier }}</p>
                                @endif
                            </div>
                            <span class="text-xs font-bold rounded-full px-2.5 py-1 flex-shrink-0
                                @class([
                                    'bg-emerald-100 text-emerald-800 border border-emerald-300' => $asset->status === 'active',
                                    'bg-amber-100 text-amber-800 border border-amber-300' => $asset->status === 'in_maintenance',
                                    'bg-gray-100 text-gray-700 border border-gray-300' => $asset->status === 'retired',
                                ])">
                                {{ ucwords(str_replace('_', ' ', $asset->status)) }}
                            </span>
                        </div>
                        
                        <div class="space-y-2 text-sm text-brand-700 border-t border-brand-100 pt-3 mb-4">
                            <div class="flex items-center justify-between">
                                <span class="text-brand-500">Assigned:</span>
                                <span class="font-medium">{{ $asset->assigned_to ?: 'Unassigned' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-brand-500">Mileage/Hours:</span>
                                <span class="font-medium">{{ $asset->mileage_hours ?: 'N/A' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-brand-500">Open Issues:</span>
                                <span class="font-bold {{ ($asset->issues_count ?? 0) > 0 ? 'text-red-700' : 'text-brand-900' }}">{{ $asset->issues_count ?? 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-brand-500">Next Service:</span>
                                <span class="font-medium">{{ optional($asset->next_service_date)->format('M j, Y') ?? 'N/A' }}</span>
                            </div>
                        </div>
                        
                        <div class="mt-auto flex gap-2">
                            <x-brand-button href="{{ route('assets.show', $asset) }}" variant="outline" class="flex-1 justify-center">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                View
                            </x-brand-button>
                            <x-brand-button href="{{ route('assets.edit', $asset) }}" variant="ghost" class="flex-1 justify-center">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit
                            </x-brand-button>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center">
                        <svg class="h-12 w-12 mx-auto text-brand-300 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M14.7 6.3a5 5 0 1 0-8.4 5.4l-4 4a2 2 0 1 0 2.8 2.8l4-4a5 5 0 0 0 5.6-8.2z"/>
                        </svg>
                        <p class="text-brand-500 font-medium">No assets found</p>
                        <p class="text-sm text-brand-400 mt-1">Try adjusting your filters or add a new asset</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="px-4 sm:px-5 py-4 border-t border-brand-100/60">
            {{ $assets->links() }}
        </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-[20px] sm:rounded-[28px] lg:rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden">
            <div class="flex items-center justify-between px-4 sm:px-5 py-4 border-b border-brand-100">
                <div>
                    <h2 class="text-lg font-bold text-brand-900">Upcoming Services</h2>
                    <p class="text-xs text-brand-500">Next 8 scheduled</p>
                </div>
                <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-brand-600 text-white text-sm font-bold">{{ $upcomingServices->count() }}</span>
            </div>
            <div class="p-4 space-y-3">
                @forelse ($upcomingServices as $serviceAsset)
                    <div class="flex items-center justify-between border-2 border-brand-100 rounded-xl px-4 py-3 hover:border-brand-300 transition">
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-brand-900 truncate">{{ $serviceAsset->name }}</p>
                            <p class="text-xs text-brand-500">{{ $serviceAsset->assigned_to ?: 'Unassigned' }}</p>
                        </div>
                        <div class="text-right ml-4">
                            <p class="text-sm font-bold text-blue-700">{{ $serviceAsset->next_service_date->format('M j, Y') }}</p>
                            <p class="text-xs text-brand-400">{{ $serviceAsset->next_service_date->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-brand-400 text-center py-6">No upcoming services scheduled</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-[20px] sm:rounded-[28px] lg:rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden">
            <div class="flex items-center justify-between px-4 sm:px-5 py-4 border-b border-brand-100">
                <div>
                    <h2 class="text-lg font-bold text-brand-900">Overdue Services</h2>
                    <p class="text-xs text-brand-500">Requires immediate attention</p>
                </div>
                <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-red-600 text-white text-sm font-bold">{{ $overdueServices->count() }}</span>
            </div>
            <div class="p-4 space-y-3">
                @forelse ($overdueServices as $overdue)
                    <div class="flex items-center justify-between border-2 border-red-200 bg-red-50 rounded-xl px-4 py-3">
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-brand-900 truncate">{{ $overdue->name }}</p>
                            <p class="text-xs text-brand-500">{{ $overdue->assigned_to ?: 'Unassigned' }}</p>
                        </div>
                        <div class="text-right ml-4">
                            <p class="text-sm font-bold text-red-700">{{ $overdue->next_service_date->format('M j, Y') }}</p>
                            <p class="text-xs text-red-600">{{ $overdue->next_service_date->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6">
                        <svg class="h-10 w-10 mx-auto text-emerald-500 mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm font-medium text-emerald-700">All caught up!</p>
                        <p class="text-xs text-brand-400 mt-1">No overdue services</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-[20px] sm:rounded-[28px] lg:rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden">
            <div class="flex items-center justify-between px-4 sm:px-5 py-4 border-b border-brand-100">
                <h2 class="text-lg font-bold text-brand-900">Reminder Queue</h2>
                <span class="text-xs text-brand-500">Next {{ $reminderCandidates->count() }} assets</span>
            </div>
            <div class="p-4 space-y-3">
                @forelse ($reminderCandidates as $reminderAsset)
                    <div class="border-2 border-brand-100 rounded-xl px-4 py-3 flex items-center justify-between hover:border-brand-300 transition">
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-brand-900 truncate">{{ $reminderAsset->name }}</p>
                            <p class="text-xs text-brand-500">
                                Service {{ optional($reminderAsset->next_service_date)->format('M j, Y') }}
                                · Reminder {{ $reminderAsset->reminder_days_before }}d prior
                            </p>
                        </div>
                        <span class="text-xs font-bold text-brand-600 px-2 py-1 rounded-full bg-brand-50 border border-brand-200 ml-4">
                            {{ now()->diffInDays($reminderAsset->next_service_date, false) }}d
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-brand-400 text-center py-6">No reminders due within configured windows</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-[20px] sm:rounded-[28px] lg:rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden">
            <div class="px-4 sm:px-5 py-4 border-b border-brand-100">
                <h2 class="text-lg font-bold text-brand-900">Fleet Breakdown</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-brand-50/80 text-xs uppercase text-brand-500">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Type</th>
                            <th class="px-4 py-3 text-right font-semibold">Count</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-50">
                        @foreach ($typeBreakdown as $row)
                            <tr class="hover:bg-brand-50/50 transition">
                                <td class="px-4 py-3 text-brand-900 font-medium">{{ ucwords(str_replace('_', ' ', $row->type)) }}</td>
                                <td class="px-4 py-3 text-right font-bold text-brand-900">{{ $row->total }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection
