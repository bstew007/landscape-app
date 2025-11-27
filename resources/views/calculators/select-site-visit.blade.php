@extends('layouts.sidebar')

@section('content')
@php
    $pageVisits = collect($siteVisits->items());
    $pageCount = $pageVisits->count();
    $clientCount = $pageVisits->pluck('client_id')->filter()->unique()->count();
    $propertyCount = $pageVisits->pluck('property_id')->filter()->unique()->count();
    $today = now()->startOfDay();
    $upcomingCount = $pageVisits->filter(function ($visit) use ($today) {
        $date = $visit->visit_date;
        if ($date instanceof \Illuminate\Support\Carbon) {
            return $date->gte($today);
        }
        return $date ? \Illuminate\Support\Carbon::parse($date)->gte($today) : false;
    })->count();
@endphp

<div class="space-y-8">
    <section class="rounded-[32px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-6 sm:p-8 shadow-2xl border border-brand-800/40 relative overflow-hidden">
        <div class="flex flex-wrap items-start gap-6">
            <div class="space-y-3 max-w-2xl">
                <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Calculators</p>
                <h1 class="text-3xl sm:text-4xl font-semibold">Select a Site Visit</h1>
                <p class="text-sm text-brand-100/85">Pair your calculator with the right client + property record so labor, photos, and notes stay synced.</p>
            </div>
            <div class="flex flex-wrap gap-3 ml-auto">
                <x-secondary-button as="a" href="{{ route('contacts.index') }}" class="bg-white/10 text-white border-white/40 hover:bg-white/20">
                    Manage Contacts
                </x-secondary-button>
                <x-secondary-button as="a" href="{{ route('client-hub') }}" class="bg-white/10 text-white border-white/40 hover:bg-white/20">
                    Client Hub
                </x-secondary-button>
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
                <dt class="text-xs uppercase tracking-wide text-brand-200">Clients</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($clientCount) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Properties</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($propertyCount) }}</dd>
            </div>
        </dl>
    </section>

    <section class="rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden">
        <div class="p-5 sm:p-7 space-y-6">
            <form method="GET" action="{{ route('site-visit.select') }}" class="flex flex-wrap items-center gap-3">
                <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
                <div class="flex items-center gap-3 flex-1 min-w-[240px]">
                    <span class="text-xs uppercase tracking-wide text-brand-400">Search</span>
                    <input type="text"
                           name="search"
                           value="{{ $search ?? '' }}"
                           placeholder="Client, property, or location"
                           class="flex-1 rounded-full border border-brand-200 bg-white px-4 py-2.5 text-sm focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div class="flex items-center gap-2">
                    @if(!empty($search))
                        <x-secondary-button as="a" href="{{ route('site-visit.select', array_filter(['redirect_to' => $redirectTo], fn($v) => $v)) }}" size="sm">Clear</x-secondary-button>
                    @endif
                    <x-brand-button type="submit" size="sm" variant="outline">
                        <svg class="h-4 w-4 mr-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                        Search
                    </x-brand-button>
                </div>
            </form>

            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="text-brand-400 uppercase tracking-wide">Calculator Target</span>
                @if($redirectTo)
                    <span class="px-3 py-1.5 rounded-full bg-brand-50 text-brand-800 border border-brand-200 text-[11px] break-all">{{ $redirectTo }}</span>
                @else
                    <span class="px-3 py-1.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[11px]">No redirect provided &ndash; selecting will open the site visit</span>
                @endif
            </div>

            @if(session('error'))
                <div class="p-4 bg-amber-50 text-amber-900 rounded-2xl border border-amber-200 text-sm">
                    {{ session('error') }}
                </div>
            @endif
        </div>

        <div class="border-t border-brand-100/60">
            @if ($siteVisits->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-brand-50/80 text-left text-[11px] uppercase tracking-wide text-brand-500">
                        <tr>
                            <th class="px-4 py-3">Client</th>
                            <th class="px-4 py-3">Property</th>
                            <th class="px-4 py-3">Address</th>
                            <th class="px-4 py-3">Visit Date</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-50 text-brand-900 text-sm">
                        @foreach ($siteVisits as $visit)
                            @php
                                $client = $visit->client;
                                $property = $visit->property;
                                $isUpcoming = optional($visit->visit_date)->gte($today);
                            @endphp
                            <tr class="transition hover:bg-brand-50/70">
                                <td class="px-4 py-3 align-top">
                                    <div class="font-semibold text-brand-900">{{ $client?->name ?? 'Unknown Client' }}</div>
                                    <p class="text-xs text-brand-400">
                                        #{{ $client?->id ?? '—' }} · {{ $client?->email ?? 'No email on file' }}
                                    </p>
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <div class="font-medium">{{ $property->name ?? 'Unassigned Property' }}</div>
                                    <p class="text-xs text-brand-400">{{ $property->type ?? 'Property' }}</p>
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
                                <td class="px-4 py-3 align-top">
                                    <div class="flex flex-wrap gap-2 justify-end">
                                        <x-brand-button type="button"
                                                        size="sm"
                                                        variant="outline"
                                                        data-select-visit="{{ $visit->id }}"
                                                        data-view-url="{{ route('contacts.site-visits.show', [$visit->client_id, $visit->id]) }}">
                                            Select
                                        </x-brand-button>
                                        <x-secondary-button href="{{ route('contacts.site-visits.show', [$visit->client_id, $visit->id]) }}" size="sm">
                                            View
                                        </x-secondary-button>
                                        <x-secondary-button href="{{ route('contacts.site-visits.edit', [$visit->client_id, $visit->id]) }}" size="sm">
                                            Edit
                                        </x-secondary-button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-4 border-t border-brand-100/60">
                    {{ $siteVisits->appends(['redirect_to' => $redirectTo, 'search' => $search])->links() }}
                </div>
            @else
                <div class="p-8 text-center text-brand-500 text-sm">No site visits found. Try a different search or create a new visit from the Contacts section.</div>
            @endif
        </div>
    </section>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const redirectTo = @json($redirectTo);
        document.querySelectorAll('[data-select-visit]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const siteVisitId = this.getAttribute('data-select-visit');
                if (!siteVisitId) return;

                if (redirectTo) {
                    const connector = redirectTo.includes('?') ? '&' : '?';
                    window.location.href = `${redirectTo}${connector}site_visit_id=${siteVisitId}`;
                } else {
                    const fallbackUrl = this.getAttribute('data-view-url');
                    window.location.href = fallbackUrl ?? "{{ route('client-hub') }}";
                }
            });
        });
    });
</script>
@endpush
@endsection
