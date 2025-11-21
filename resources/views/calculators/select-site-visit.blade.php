@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    {{-- Header card --}}
    <div class="rounded-lg bg-white shadow-sm border border-brand-100 p-6 flex items-center gap-3">
        <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-brand-600/10 text-brand-700">
            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <path d="M21 13a4 4 0 0 0-3-3.87" />
            </svg>
        </span>
        <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-wide uppercase">Select Site Visit</h1>
        </div>
    </div>

    {{-- Main card --}}
    <div class="rounded-lg bg-white shadow-sm border border-brand-100 p-6 space-y-6">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <form method="GET" action="{{ route('site-visit.select') }}" class="flex flex-1 gap-3 flex-col sm:flex-row sm:items-center">
                <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
                <input type="text"
                       name="search"
                       value="{{ $search ?? '' }}"
                       placeholder="Search by client, property, or location..."
                       class="flex-1 form-input border-brand-300 rounded focus:ring-brand-500 focus:border-brand-500 text-lg py-3">
                <x-brand-button type="submit">Search</x-brand-button>
                @if(!empty($search))
                    <x-brand-button href="{{ route('site-visit.select', array_filter(['redirect_to' => $redirectTo])) }}" variant="ghost">Clear</x-brand-button>
                @endif
            </form>
            <div class="flex gap-2 flex-wrap">
                <x-brand-button href="{{ route('contacts.index') }}" variant="outline">Manage Contacts</x-brand-button>
            </div>
        </div>

        @if(session('error'))
            <div class="p-4 rounded border border-amber-300 bg-amber-50 text-amber-900 text-sm">
                {{ session('error') }}
            </div>
        @endif

        @if(!$redirectTo)
            <div class="p-4 rounded border border-brand-200 bg-brand-50 text-brand-800 text-sm">
                No calculator destination was provided. Selecting a site visit will simply open the visit record.
            </div>
        @endif

        @if ($siteVisits->count())
            <div class="overflow-x-auto rounded-lg border border-brand-200">
                <table class="min-w-full border-collapse text-sm">
                    <thead class="bg-brand-50 text-brand-600 uppercase tracking-wide">
                        <tr>
                            <th class="px-4 py-3 border border-brand-200 bg-brand-100 text-left">Client</th>
                            <th class="px-4 py-3 border border-brand-200 bg-brand-100 text-left">Property</th>
                            <th class="px-4 py-3 border border-brand-200 bg-brand-100 text-left">Address</th>
                            <th class="px-4 py-3 border border-brand-200 bg-brand-100 text-left">Visit Date</th>
                            <th class="px-4 py-3 border border-brand-200 bg-brand-100 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-brand-900 text-base">
                        @foreach ($siteVisits as $visit)
                            @php
                                $client = $visit->client;
                                $property = $visit->property;
                                $rowShade = $loop->even ? 'bg-brand-100/60' : 'bg-white';
                            @endphp
                            <tr class="{{ $rowShade }} hover:bg-brand-100">
                                <td class="px-4 py-3 border border-brand-200">
                                    <div class="font-semibold text-brand-900">{{ $client?->name ?? 'Unknown Client' }}</div>
                                    <div class="text-sm text-brand-600">#{{ $client->id ?? '—' }} · {{ $client?->email ?? 'No Email' }}</div>
                                </td>
                                <td class="px-4 py-3 border border-brand-200">
                                    <div class="font-medium">{{ $property->name ?? 'Unassigned Property' }}</div>
                                    <div class="text-sm text-brand-600">{{ $property?->type ?? '' }}</div>
                                </td>
                                <td class="px-4 py-3 border border-brand-200">
                                    <div>{{ $property?->display_address ?? 'No address on file' }}</div>
                                    @if($property && $property->city)
                                        <div class="text-sm text-brand-600">{{ $property->city }}, {{ $property->state }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 border border-brand-200">
                                    {{ optional($visit->visit_date)->format('M d, Y') ?? 'No date recorded' }}
                                </td>
                                <td class="px-4 py-3 border border-brand-200">
                                    <div class="flex flex-wrap gap-2">
                                        <x-brand-button type="button"
                                                        size="sm"
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

            <div class="pt-4">
                {{ $siteVisits->links() }}
            </div>
        @else
            <p class="text-brand-600 text-lg">No site visits found. Try a different search or create a new visit from the Contacts section.</p>
        @endif
    </div>
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
                    const fullUrl = redirectTo.includes('?')
                        ? `${redirectTo}&site_visit_id=${siteVisitId}`
                        : `${redirectTo}?site_visit_id=${siteVisitId}`;
                    window.location.href = fullUrl;
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
