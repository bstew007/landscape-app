@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <x-page-header title="Site Visits" eyebrow="Client">
        <x-slot:actions>
            <x-brand-button href="{{ route('contacts.site-visits.create', $client) }}">+ New Site Visit</x-brand-button>
        </x-slot:actions>
    </x-page-header>

    @if(session('success'))
        <div class="p-4 bg-accent-50 text-accent-900 rounded border border-accent-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-lg bg-white shadow-sm border border-brand-100 p-6 space-y-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <form method="GET" action="{{ route('contacts.site-visits.index', $client) }}" class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
                <input type="text"
                       name="search"
                       value="{{ $search ?? '' }}"
                       placeholder="Search by property, address, or notes..."
                       class="flex-1 form-input border-brand-300 rounded focus:ring-brand-500 focus:border-brand-500 text-lg py-3">
                <x-brand-button type="submit">Search</x-brand-button>
                @if(!empty($search))
                    <x-brand-button href="{{ route('contacts.site-visits.index', $client) }}" variant="ghost">Clear</x-brand-button>
                @endif
            </form>
            <div class="flex flex-wrap gap-2">
                <x-secondary-button href="{{ route('client-hub') }}" size="sm">Client Hub</x-secondary-button>
                <x-secondary-button href="{{ route('contacts.show', $client) }}" size="sm">Back to Contact</x-secondary-button>
            </div>
        </div>

        @if($siteVisits->count())
            <div class="overflow-x-auto rounded-lg border border-brand-200">
                <table class="min-w-full border-collapse text-base">
                    <thead class="bg-brand-50 text-brand-600 uppercase tracking-wide">
                        <tr>
                            <th class="px-4 py-3 border border-brand-200 bg-brand-100 text-left">Property</th>
                            <th class="px-4 py-3 border border-brand-200 bg-brand-100 text-left">Address</th>
                            <th class="px-4 py-3 border border-brand-200 bg-brand-100 text-left">Visit Date</th>
                            <th class="px-4 py-3 border border-brand-200 bg-brand-100 text-left">Notes</th>
                            <th class="px-4 py-3 border border-brand-200 bg-brand-100 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-brand-900 text-lg">
                        @foreach ($siteVisits as $visit)
                            @php
                                $property = $visit->property;
                                $rowShade = $loop->even ? 'bg-brand-100/70' : 'bg-white';
                            @endphp
                            <tr class="{{ $rowShade }} hover:bg-brand-100">
                                <td class="px-4 py-3 border border-brand-200">
                                    <div class="font-semibold text-brand-900">
                                        {{ $property->name ?? 'Unassigned Property' }}
                                        @if(optional($property)->is_primary)
                                            <span class="badge bg-brand-50 text-brand-700 border border-brand-200 ml-2">Primary</span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-brand-600">Client: {{ $client->name }}</div>
                                </td>
                                <td class="px-4 py-3 border border-brand-200">
                                    <div>{{ $property->display_address ?? 'No address on file' }}</div>
                                    @if($property && ($property->city || $property->state))
                                        <div class="text-sm text-brand-600">{{ $property->city }}, {{ $property->state }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 border border-brand-200">{{ optional($visit->visit_date)->format('M d, Y') ?? 'No date recorded' }}</td>
                                <td class="px-4 py-3 border border-brand-200 text-brand-700">
                                    {{ $visit->notes ? \Illuminate\Support\Str::limit($visit->notes, 70) : 'No notes added' }}
                                </td>
                                <td class="px-4 py-3 border border-brand-200">
                                    <div class="flex flex-wrap gap-2">
                                        <x-brand-button href="{{ route('contacts.site-visits.show', [$client, $visit]) }}" size="sm">View</x-brand-button>
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

            <div class="pt-4">
                {{ $siteVisits->links() }}
            </div>
        @else
            <p class="text-brand-600 text-lg">No site visits yet. Use the ?New Site Visit? button to get started.</p>
        @endif
    </div>
</div>
@endsection
