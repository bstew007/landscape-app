@extends('layouts.sidebar')

@section('content')
@php
    $totalVisits = collect($weeks)->flatten(1)->sum(fn($day) => $day['visits']->count());
    $upcomingVisits = collect($weeks)->flatten(1)->filter(fn($day) => $day['date']->isFuture())->sum(fn($day) => $day['visits']->count());
    $todayVisits = collect($weeks)->flatten(1)->filter(fn($day) => $day['is_today'])->first()['visits']->count() ?? 0;
    $uniqueClients = collect($weeks)->flatten(1)->flatMap(fn($day) => $day['visits'])->pluck('client_id')->filter()->unique()->count();
@endphp

<div class="space-y-8">
    <section class="rounded-[20px] sm:rounded-[28px] lg:rounded-[32px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-4 sm:p-6 lg:p-8 shadow-2xl border border-brand-800/40 relative overflow-hidden">
        <div class="flex flex-wrap items-start gap-4 sm:gap-6">
            <div class="space-y-2 sm:space-y-3 max-w-2xl">
                <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Schedule</p>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-semibold">Calendar</h1>
                <p class="text-xs sm:text-sm text-brand-100/85">{{ $currentMonth->format('F Y') }} – Track site visits, schedule field work, and coordinate your team's calendar.</p>
            </div>
            <div class="flex flex-wrap gap-2 sm:gap-3 ml-auto w-full sm:w-auto">
                <x-secondary-button as="a" href="{{ route('calendar.index', ['month' => $previousMonth->month, 'year' => $previousMonth->year]) }}" class="bg-white/10 text-white border-white/40 hover:bg-white/20 text-xs sm:text-sm flex-1 sm:flex-none justify-center">
                    <svg class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 18l-6-6 6-6"/></svg>
                    {{ $previousMonth->format('M Y') }}
                </x-secondary-button>
                <x-brand-button href="{{ route('calendar.index') }}" variant="muted" class="flex-1 sm:flex-none justify-center">
                    <svg class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    Today
                </x-brand-button>
                <x-secondary-button as="a" href="{{ route('calendar.index', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}" class="bg-white/10 text-white border-white/40 hover:bg-white/20 text-xs sm:text-sm flex-1 sm:flex-none justify-center">
                    {{ $nextMonth->format('M Y') }}
                    <svg class="h-4 w-4 ml-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 18l6-6-6-6"/></svg>
                </x-secondary-button>
            </div>
        </div>
        <dl class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mt-6 sm:mt-8 text-sm text-brand-100">
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">This Month</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($totalVisits) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Today</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($todayVisits) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Upcoming</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($upcomingVisits) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Active Clients</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($uniqueClients) }}</dd>
            </div>
        </dl>
    </section>

    <section class="rounded-[20px] sm:rounded-[28px] lg:rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden">
        @php
            $daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        @endphp

        <div class="grid grid-cols-7 bg-brand-50/80 text-center text-xs sm:text-sm font-semibold text-brand-500 uppercase tracking-wide border-b border-brand-100/60">
            @foreach ($daysOfWeek as $dayName)
                <div class="py-3 sm:py-4">{{ $dayName }}</div>
            @endforeach
        </div>

        @foreach ($weeks as $week)
            <div class="grid grid-cols-7 border-b last:border-b-0 border-brand-100/40">
                @foreach ($week as $day)
                    <div class="min-h-[100px] sm:min-h-[120px] border-r last:border-r-0 border-brand-100/40 p-2 sm:p-3 text-sm {{ $day['in_month'] ? 'bg-white' : 'bg-brand-50/30' }} transition hover:bg-brand-50/50">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-semibold text-sm sm:text-base {{ $day['is_today'] ? 'inline-flex items-center justify-center h-7 w-7 sm:h-8 sm:w-8 rounded-full bg-brand-600 text-white' : ($day['in_month'] ? 'text-brand-900' : 'text-brand-300') }}">
                                {{ $day['date']->format('j') }}
                            </span>
                            @if ($day['visits']->count() > 0)
                                <span class="inline-flex items-center justify-center rounded-full bg-brand-600 px-2 py-0.5 text-[10px] sm:text-xs font-bold text-white">
                                    {{ $day['visits']->count() }}
                                </span>
                            @endif
                        </div>

                        <div class="space-y-1.5">
                            @forelse ($day['visits'] as $visit)
                                <a href="{{ route('contacts.site-visits.show', [$visit->client_id, $visit->id]) }}"
                                   class="block rounded-lg border-l-4 {{ $day['date']->isFuture() ? 'border-brand-600 bg-brand-50' : 'border-emerald-600 bg-emerald-50' }} px-2 py-1.5 hover:shadow-md transition-shadow">
                                    <p class="font-semibold text-xs sm:text-sm {{ $day['date']->isFuture() ? 'text-brand-900' : 'text-emerald-900' }} truncate">
                                        {{ $visit->client->name }}
                                    </p>
                                    <p class="text-[10px] sm:text-xs {{ $day['date']->isFuture() ? 'text-brand-600' : 'text-emerald-700' }} truncate mt-0.5">
                                        {{ $visit->property->display_address ?? $visit->property->name ?? 'No address' }}
                                    </p>
                                </a>
                            @empty
                                @if ($day['in_month'])
                                    <p class="text-xs text-brand-300 text-center py-2">No visits</p>
                                @endif
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </section>

    <div class="rounded-[20px] sm:rounded-[28px] lg:rounded-[32px] bg-gradient-to-br from-brand-700 to-brand-600 text-white p-6 shadow-xl border border-brand-600/40">
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex-1">
                <h3 class="text-lg font-bold">Quick Actions</h3>
                <p class="text-xs text-brand-100 mt-1">Schedule visits and manage your calendar</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-secondary-button as="a" href="{{ route('site-visit.select') }}" class="bg-white/10 text-white border-white/40 hover:bg-white/20 text-xs">
                    <svg class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14M5 12h14"/></svg>
                    New Site Visit
                </x-secondary-button>
                <x-secondary-button as="a" href="{{ route('contacts.index') }}" class="bg-white/10 text-white border-white/40 hover:bg-white/20 text-xs">
                    <svg class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3"/><circle cx="17" cy="10" r="3"/><path d="M2 21c0-3.314 2.686-6 6-6h2M22 21c0-3.314-2.686-6-6-6h-2"/></svg>
                    Manage Contacts
                </x-secondary-button>
                <x-secondary-button as="a" href="{{ route('client-hub') }}" class="bg-white/10 text-white border-white/40 hover:bg-white/20 text-xs">
                    <svg class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><path d="M9 22V12h6v10"/></svg>
                    Client Hub
                </x-secondary-button>
            </div>
        </div>
    </div>
</div>
@endsection
