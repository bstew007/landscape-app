@extends('layouts.sidebar')

@section('content')
@php
    $pageVisits = collect($siteVisits->items());
    $pageCount = $pageVisits->count();
    $today = now()->startOfDay();
    $upcomingCount = $pageVisits->filter(function ($visit) use ($today) {
        $date = $visit->visit_date;
        if ($date instanceof \Illuminate\Support\Carbon) {
            return $date->gte($today);
        }
        return $date ? \Illuminate\Support\Carbon::parse($date)->gte($today) : false;
    })->count();
    $completedCount = max(0, $pageCount - $upcomingCount);
    $propertyCount = $pageVisits->pluck('property_id')->filter()->unique()->count();
    $notesCount = $pageVisits->filter(fn($visit) => filled($visit->notes))->count();
@endphp

<div class="space-y-8">
    <section class="rounded-[32px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-6 sm:p-8 shadow-2xl border border-brand-800/40 relative overflow-hidden">
        <div class="flex flex-wrap items-start gap-6">
            <div class="space-y-3 max-w-2xl">
                <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Site Visits</p>
                <h1 class="text-3xl sm:text-4xl font-semibold">Field Operations for {{ $client->name }}</h1>
                <p class="text-sm text-brand-100/85">Keep every property walkthrough, field note, and calculator export aligned with this client&mdash;from scheduling to follow-up.</p>
            </div>
            <div class="flex flex-wrap gap-3 ml-auto">
                <x-secondary-button as="a" href="{{ route('contacts.show', $client) }}" class="bg-white/10 text-white border-white/40 hover:bg-white/20">Back to Contact</x-secondary-button>
                <x-brand-button href="{{ route('contacts.site-visits.create', $client) }}" variant="muted">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    New Site Visit
                </x-brand-button>
            </div>
        </div>
        <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8 text-sm text-brand-100">
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">On This Page</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($pageCount) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Upcoming</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($upcomingCount) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Completed</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($completedCount) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Properties</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($propertyCount) }}</dd>
            </div>
        </dl>
    </section>

    <section class="rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden">
        <div class="p-5 sm:p-7 space-y-6">
            <form method="GET" action="{{ route('contacts.site-visits.index', $client) }}" class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-3 flex-1 min-w-[240px]">
                    <span class="text-xs uppercase tracking-wide text-brand-400">Search</span>
                    <input type="text"
                           name="search"
                           value="{{ $search ?? '' }}"
                           placeholder="Property, address, or notes"
                           class="flex-1 rounded-full border border-brand-200 bg-white px-4 py-2.5 text-sm focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div class="flex items-center gap-2">
                    @if(!empty($search))
                        <x-secondary-button as="a" href="{{ route('contacts.site-visits.index', $client) }}" size="sm">Clear</x-secondary-button>
                    @endif
                    <x-brand-button type="submit" size="sm">Search</x-brand-button>
                </div>
            </form>

            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="text-brand-400 uppercase tracking-wide">Quick Links</span>
                <a href="{{ route('client-hub') }}" class="px-3 py-1.5 rounded-full border border-brand-200 text-brand-700 hover:bg-brand-50">Client Hub</a>
                <a href="{{ route('contacts.show', $client) }}" class="px-3 py-1.5 rounded-full border border-brand-200 text-brand-700 hover:bg-brand-50">Contact Overview</a>
                <span class="ml-auto text-brand-300">{{ number_format($notesCount) }} visits have notes</span>
            </div>

            @if(session('success'))
                <div class="p-4 bg-accent-50 text-accent-900 rounded-2xl border border-accent-200 text-sm">
                    {{ session('success') }}
                </div>
            @endif
        </div>

        <div class="border-t border-brand-100/60">
            @if($siteVisits->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-brand-50/80 text-left text-[11px] uppercase tracking-wide text-brand-500">
                        <tr>
                            <th class="px-4 py-3">Property</th>
                            <th class="px-4 py-3">Address</th>
                            <th class="px-4 py-3">Visit Date</th>
                            <th class="px-4 py-3">Notes</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-50 text-brand-900 text-sm">
                        @foreach ($siteVisits as $visit)
                            @php
                                $property = $visit->property;
                                $notesPreview = $visit->notes ? \Illuminate\Support\Str::limit($visit->notes, 80) : 'No notes added';
                                $isUpcoming = optional($visit->visit_date)->gte($today);
                            @endphp
                            <tr class="transition hover:bg-brand-50/70">
                                <td class="px-4 py-3 align-top">
                                    <div class="font-semibold text-brand-900 flex items-center gap-2">
                                        {{ $property->name ?? 'Unassigned Property' }}
                                        @if(optional($property)->is_primary)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-brand-50 text-brand-700 border border-brand-200 text-[11px]">Primary</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-brand-400">Client: {{ $client->name }}</p>
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <div>{{ $property->display_address ?? 'No address on file' }}</div>
                                    @if($property && ($property->city || $property->state))
                                        <div class="text-xs text-brand-400">{{ $property->city }}, {{ $property->state }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <div class="font-medium {{ $isUpcoming ? 'text-emerald-700' : 'text-brand-900' }}">
                                        {{ optional($visit->visit_date)->format('M d, Y') ?? 'No date recorded' }}
                                    </div>
                                    <div class="text-xs text-brand-400">{{ $isUpcoming ? 'Scheduled' : 'Completed' }}</div>
                                </td>
                                <td class="px-4 py-3 align-top text-brand-700">{{ $notesPreview }}</td>
                                <td class="px-4 py-3 align-top">
                                    <div class="flex flex-wrap gap-2 justify-end">
                                        <x-brand-button href="{{ route('contacts.site-visits.show', [$client, $visit]) }}" size="sm" variant="outline">View</x-brand-button>
                                        <x-secondary-button href="{{ route('contacts.site-visits.edit', [$client, $visit]) }}" size="sm">Edit</x-secondary-button>
                                        <form action="{{ route('contacts.site-visits.destroy', [$client, $visit]) }}" method="POST" onsubmit="return confirm('Delete this site visit?');">
                                            @csrf
                                            @method('DELETE')
                                            <x-danger-button size="sm">Delete</x-danger-button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-4 border-t border-brand-100/60">
                    {{ $siteVisits->links() }}
                </div>
            @else
                <div class="p-8 text-center text-brand-500 text-sm">No site visits yet. Use "New Site Visit" to get started.</div>
            @endif
        </div>
    </section>
</div>
@endsection
