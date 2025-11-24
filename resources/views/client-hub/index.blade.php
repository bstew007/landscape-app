@extends('layouts.sidebar')

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<div class="space-y-8">
    <section class="rounded-[20px] sm:rounded-[28px] lg:rounded-[32px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-4 sm:p-6 lg:p-8 shadow-2xl border border-brand-800/40 relative overflow-hidden">
        <div class="flex flex-wrap items-start gap-4 sm:gap-6">
            <div class="space-y-2 sm:space-y-3 max-w-2xl">
                <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Client Hub</p>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-semibold">Home Dashboard</h1>
                <p class="text-xs sm:text-sm text-brand-100/85">Manage clients, visits, and estimates from one unified command center.</p>
            </div>
            <div class="flex flex-wrap gap-2 sm:gap-3 ml-auto w-full sm:w-auto">
                <x-secondary-button as="a" href="{{ route('contacts.index') }}" class="bg-white/10 text-white border-white/40 hover:bg-white/20 text-xs sm:text-sm flex-1 sm:flex-none justify-center">
                    <svg class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3"/><circle cx="17" cy="10" r="3"/><path d="M2 21c0-3.314 2.686-6 6-6h2M22 21c0-3.314-2.686-6-6-6h-2"/></svg>
                    Manage Contacts
                </x-secondary-button>
                <x-brand-button href="{{ route('contacts.create') }}" variant="muted" class="flex-1 sm:flex-none justify-center">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    Add Contact
                </x-brand-button>
            </div>
        </div>
        <dl class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mt-6 sm:mt-8 text-sm text-brand-100">
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Clients</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($metrics['clients']) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Site Visits</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($metrics['site_visits']) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Upcoming Visits</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($metrics['upcoming_visits']) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Draft / Pending</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($metrics['pending_estimates']) }}</dd>
            </div>
        </dl>
    </section>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-[20px] sm:rounded-[28px] lg:rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden">
            <div class="flex items-center justify-between px-4 sm:px-5 py-4 border-b border-brand-100">
                <div>
                    <h2 class="text-lg font-bold text-brand-900">Recent Clients</h2>
                    <p class="text-xs text-brand-500">Latest {{ $recentClients->count() }} contacts</p>
                </div>
                <x-brand-button href="{{ route('contacts.index') }}" size="sm" variant="outline">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    View All
                </x-brand-button>
            </div>
            <div class="p-4 space-y-3">
                @forelse ($recentClients as $client)
                    <div class="flex items-center justify-between border-2 border-brand-100 rounded-xl px-4 py-3 hover:border-brand-300 transition">
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('clients.show', $client) }}" class="font-bold text-brand-900 hover:text-brand-700 hover:underline block truncate">
                                {{ $client->name }}
                            </a>
                            <p class="text-xs text-brand-500">{{ $client->company_name ?: 'Residential' }}</p>
                        </div>
                        <div class="text-right ml-4">
                            <p class="text-sm font-bold text-brand-900">{{ $client->site_visits_count }} visits</p>
                            <x-brand-button href="{{ route('clients.show', $client) }}" size="sm" variant="ghost">Open</x-brand-button>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-brand-400 text-center py-6">No clients yet</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-[20px] sm:rounded-[28px] lg:rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden">
            <div class="flex items-center justify-between px-4 sm:px-5 py-4 border-b border-brand-100">
                <div>
                    <h2 class="text-lg font-bold text-brand-900">Upcoming Site Visits</h2>
                    <p class="text-xs text-brand-500">Next {{ $upcomingVisits->count() }} scheduled</p>
                </div>
                <x-brand-button href="{{ route('site-visit.select') }}" size="sm" variant="outline">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14M5 12h14"/></svg>
                    Schedule
                </x-brand-button>
            </div>
            <div class="p-4 space-y-3">
                @forelse ($upcomingVisits as $visit)
                    <div class="flex items-center justify-between border-2 border-brand-100 rounded-xl px-4 py-3 hover:border-brand-300 transition">
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('contacts.site-visits.show', [$visit->client_id, $visit->id]) }}" class="font-bold text-brand-900 hover:text-brand-700 hover:underline block truncate">
                                {{ optional($visit->client)->name ?? 'Unknown Client' }}
                            </a>
                            <p class="text-xs text-brand-500">{{ optional($visit->property)->name ?? 'No property' }}</p>
                        </div>
                        <div class="text-right ml-4">
                            <p class="text-sm font-bold text-blue-700">{{ optional($visit->visit_date)->format('M j, g:ia') }}</p>
                            <x-brand-button href="{{ route('contacts.site-visits.show', [$visit->client_id, $visit->id]) }}" size="sm" variant="ghost">Open</x-brand-button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6">
                        <svg class="h-10 w-10 mx-auto text-brand-300 mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-sm font-medium text-brand-500">No upcoming visits</p>
                        <p class="text-xs text-brand-400 mt-1">Schedule visits from the CRM</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-[20px] sm:rounded-[28px] lg:rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden">
            <div class="flex items-center justify-between px-4 sm:px-5 py-4 border-b border-brand-100">
                <div>
                    <h2 class="text-lg font-bold text-brand-900">Recent Estimates</h2>
                    <p class="text-xs text-brand-500">Latest {{ $recentEstimates->count() }} proposals</p>
                </div>
                <x-brand-button href="{{ route('estimates.index') }}" size="sm" variant="outline">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    View All
                </x-brand-button>
            </div>
            <div class="p-4 space-y-3">
                @forelse ($recentEstimates as $estimate)
                    @php $estTotal = $estimate->grand_total > 0 ? $estimate->grand_total : $estimate->total; @endphp
                    <div class="flex items-center justify-between border-2 border-brand-100 rounded-xl px-4 py-3 hover:border-brand-300 transition">
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('estimates.show', $estimate) }}" class="font-bold text-brand-900 hover:text-brand-700 hover:underline block truncate">
                                {{ $estimate->title }}
                            </a>
                            <p class="text-xs text-brand-500">{{ $estimate->client->name }}</p>
                        </div>
                        <div class="text-right ml-4">
                            <p class="text-sm font-bold text-brand-900">{{ $estTotal ? '$' . number_format($estTotal, 2) : 'Pending' }}</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                @switch($estimate->status)
                                    @case('approved') bg-emerald-100 text-emerald-800 border border-emerald-200 @break
                                    @case('sent') bg-brand-50 text-brand-700 border border-brand-200 @break
                                    @case('pending') bg-amber-100 text-amber-700 border border-amber-200 @break
                                    @default bg-gray-100 text-gray-700 border border-gray-200
                                @endswitch">
                                {{ ucfirst($estimate->status) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6">
                        <svg class="h-10 w-10 mx-auto text-brand-300 mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M7 2h7l5 5v13a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/><path d="M14 2v5h5"/>
                        </svg>
                        <p class="text-sm font-medium text-brand-500">No estimates yet</p>
                        <x-brand-button href="{{ route('estimates.create') }}" size="sm" class="mt-3">Create Estimate</x-brand-button>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="rounded-[20px] sm:rounded-[28px] lg:rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden">
            <div class="flex items-center justify-between px-4 sm:px-5 py-4 border-b border-brand-100">
                <div>
                    <h2 class="text-lg font-bold text-brand-900">Your To‑Dos</h2>
                    <p class="text-xs text-brand-500">{{ $todos->count() }} active tasks</p>
                </div>
                <x-brand-button href="{{ route('todos.index') }}" size="sm" variant="outline">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 12l2 2 4-4"/></svg>
                    Open Board
                </x-brand-button>
            </div>
            <div class="p-4 space-y-3">
                @forelse ($todos as $todo)
                    <div class="border-2 border-brand-100 rounded-xl px-4 py-3 flex items-start justify-between hover:border-brand-300 transition">
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('todos.edit', $todo) }}" class="font-bold text-brand-900 hover:text-brand-700 hover:underline block truncate">
                                {{ $todo->title }}
                            </a>
                            <p class="text-xs text-brand-500">
                                Status: {{ Str::headline($todo->status) }}
                                @if($todo->due_date)
                                    <span class="text-brand-400">·</span>
                                    Due {{ $todo->due_date->format('M j') }}
                                @endif
                            </p>
                        </div>
                        @if($todo->priority)
                            <span class="text-xs px-2.5 py-1 rounded-full font-bold border ml-4
                                @switch($todo->priority)
                                    @case('urgent') bg-red-100 text-red-800 border-red-300 @break
                                    @case('high') bg-orange-100 text-orange-800 border-orange-300 @break
                                    @case('low') bg-gray-100 text-gray-700 border-gray-300 @break
                                    @default bg-blue-100 text-blue-700 border-blue-300
                                @endswitch">
                                {{ Str::headline($todo->priority) }}
                            </span>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-6">
                        <svg class="h-10 w-10 mx-auto text-emerald-500 mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm font-medium text-emerald-700">All caught up!</p>
                        <p class="text-xs text-brand-400 mt-1">No current to‑dos. Enjoy the day!</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    <div class="rounded-[20px] sm:rounded-[28px] lg:rounded-[32px] bg-gradient-to-br from-brand-700 to-brand-600 text-white p-6 shadow-xl border border-brand-600/40">
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex-1">
                <h3 class="text-lg font-bold">Quick Actions</h3>
                <p class="text-xs text-brand-100 mt-1">Jump to common workflows</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-secondary-button as="a" href="{{ route('estimates.create') }}" class="bg-white/10 text-white border-white/40 hover:bg-white/20 text-xs">
                    <svg class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 2h7l5 5v13a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/><path d="M14 2v5h5"/></svg>
                    New Estimate
                </x-secondary-button>
                <x-secondary-button as="a" href="{{ route('todos.create') }}" class="bg-white/10 text-white border-white/40 hover:bg-white/20 text-xs">
                    <svg class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 12l2 2 4-4"/></svg>
                    New To-Do
                </x-secondary-button>
                <x-secondary-button as="a" href="{{ route('calendar.index') }}" class="bg-white/10 text-white border-white/40 hover:bg-white/20 text-xs">
                    <svg class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    View Schedule
                </x-secondary-button>
            </div>
        </div>
    </div>
</div>
@endsection
