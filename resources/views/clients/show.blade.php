@extends('layouts.sidebar')

@section('content')
<div class="max-w-6xl mx-auto py-10 space-y-8">
    @if(session('success'))
        <div class="p-4 bg-green-100 text-green-800 border border-green-200 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white p-6 rounded-lg shadow text-gray-800 space-y-2">
        <h1 class="text-3xl font-bold mb-2">Client Details</h1>
        <p><strong>Name:</strong> {{ $client->first_name }} {{ $client->last_name }}</p>
        <p><strong>Company:</strong> {{ $client->company_name ?? 'N/A' }}</p>
        <p><strong>Email:</strong> {{ $client->email ?? 'N/A' }}</p>
        <p><strong>Phone:</strong> {{ $client->phone ?? 'N/A' }}</p>
        <p><strong>Billing Address:</strong> {{ $client->address ?? 'N/A' }}</p>
    </div>

    <div class="bg-white p-6 rounded-lg shadow text-gray-800">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-semibold">Properties</h2>
            <a href="{{ route('clients.properties.create', $client) }}"
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                Add Property
            </a>
        </div>

        @forelse ($properties as $property)
            <div class="border border-gray-200 rounded-lg p-4 mb-4 last:mb-0">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <p class="text-xl font-semibold">
                            {{ $property->name }}
                            @if($property->is_primary)
                                <span class="ml-2 inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800">
                                    Primary
                                </span>
                            @endif
                        </p>
                        <p class="text-gray-600">
                            {{ $property->display_address ?? 'No address on file' }}
                        </p>
                        <p class="text-gray-600">
                            <strong>Type:</strong> {{ ucfirst($property->type) }}
                        </p>
                        <p class="text-gray-600">
                            <strong>Property Contact:</strong>
                            {{ $property->contact_name ?? 'N/A' }}
                            @if($property->contact_phone)
                                — {{ $property->contact_phone }}
                            @endif
                            @if($property->contact_email)
                                ({{ $property->contact_email }})
                            @endif
                        </p>
                        @if($property->notes)
                            <p class="text-gray-600"><strong>Notes:</strong> {{ $property->notes }}</p>
                        @endif
                    </div>
                    <div class="text-sm text-gray-500">
                        <p><strong>Site Visits:</strong> {{ $property->site_visits_count }}</p>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('clients.site-visits.create', ['client' => $client->id, 'property_id' => $property->id]) }}"
                       class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md">
                        Schedule Visit
                    </a>
                    <a href="{{ route('clients.properties.edit', [$client, $property]) }}"
                       class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-md">
                        Edit Property
                    </a>
                    <form method="POST" action="{{ route('clients.properties.destroy', [$client, $property]) }}"
                          onsubmit="return confirm('Deleting this property will remove all of its site visits. Continue?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-gray-600">
                No properties added yet. Create one to track separate locations and site contacts.
            </p>
        @endforelse
    </div>

    <div class="bg-white p-6 rounded-lg shadow text-gray-800">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-semibold">Recent Site Visits</h2>
            <a href="{{ route('clients.site-visits.index', $client) }}"
               class="text-blue-600 hover:underline">
                View All
            </a>
        </div>

        @if ($recentVisits->count())
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-gray-700">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-4 py-2">Date</th>
                            <th class="px-4 py-2">Property</th>
                            <th class="px-4 py-2">Notes</th>
                            <th class="px-4 py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($recentVisits as $visit)
                        <tr class="border-t">
                            <td class="px-4 py-2">{{ optional($visit->visit_date)->format('M d, Y') ?? 'N/A' }}</td>
                            <td class="px-4 py-2">
                                {{ optional($visit->property)->name ?? 'Unassigned' }}
                                <div class="text-sm text-gray-500">
                                    {{ optional($visit->property)->display_address ?? '' }}
                                </div>
                            </td>
                            <td class="px-4 py-2">{{ \Illuminate\Support\Str::limit($visit->notes, 80) ?? 'N/A' }}</td>
                            <td class="px-4 py-2">
                                <a href="{{ route('clients.site-visits.show', [$client, $visit]) }}"
                                   class="text-blue-600 hover:underline">View</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-600">No site visits recorded yet.</p>
        @endif
    </div>

    <div class="bg-white p-6 rounded-lg shadow text-gray-800">
        <h2 class="text-2xl font-semibold mb-4">Add Calculations to Site Visit</h2>

        @if (($siteVisitOptions ?? collect())->isNotEmpty())
            <div class="mb-4 space-y-2">
                <label for="client-site-visit-select" class="block text-sm font-semibold text-gray-700">
                    Choose site visit to attach calculations
                </label>
                <select id="client-site-visit-select" class="form-select w-full max-w-md">
                    @foreach ($siteVisitOptions as $option)
                        <option value="{{ $option->id }}" @selected(optional($siteVisit)->id === $option->id)>
                            {{ optional($option->visit_date)->format('M j, Y') ?? 'Visit #' . $option->id }}
                            @if($option->property)
                                - {{ $option->property->name }}
                            @endif
                        </option>
                    @endforeach
                </select>
                <p id="selected-site-visit-meta" class="text-xs text-gray-500">
                    @if($siteVisit)
                        Selected visit: {{ optional($siteVisit->visit_date)->format('F j, Y') ?? 'N/A' }}
                        @if($siteVisit->property)
                            - {{ $siteVisit->property->name }}
                        @endif
                    @endif
                </p>
            </div>

            @php
                $calculatorLinks = [
                    ['label' => 'Retaining Wall Calculator', 'template' => route('calculators.wall.form', ['site_visit_id' => '__SITE_ID__'])],
                    ['label' => 'Paver Calculator', 'template' => route('calculators.patio.form', ['site_visit_id' => '__SITE_ID__'])],
                    ['label' => 'Fence Calculator', 'template' => route('calculators.fence.form', ['site_visit_id' => '__SITE_ID__'])],
                    ['label' => 'Pruning Calculator', 'template' => route('calculators.pruning.form', ['site_visit_id' => '__SITE_ID__'])],
                    ['label' => 'Weeding Calculator', 'template' => route('calculators.weeding.form', ['site_visit_id' => '__SITE_ID__'])],
                    ['label' => 'Mulching Calculator', 'template' => route('calculators.mulching.form', ['site_visit_id' => '__SITE_ID__'])],
                    ['label' => 'Planting Calculator', 'template' => route('calculators.planting.form', ['site_visit_id' => '__SITE_ID__'])],
                    ['label' => 'Pine Needle Calculator', 'template' => route('calculators.pine_needles.form', ['site_visit_id' => '__SITE_ID__'])],
                    ['label' => 'Synthetic Turf Calculator', 'template' => route('calculators.syn_turf.form', ['site_visit_id' => '__SITE_ID__'])],
                    ['label' => 'Turf Mowing Calculator', 'template' => route('calculators.turf_mowing.form', ['site_visit_id' => '__SITE_ID__'])],
                ];
                $selectedId = optional($siteVisit)->id;
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4" id="calculator-link-grid">
                @foreach ($calculatorLinks as $link)
                    @php
                        $href = $selectedId ? str_replace('__SITE_ID__', $selectedId, $link['template']) : '#';
                    @endphp
                    <a href="{{ $href }}"
                       class="px-6 py-3 bg-blue-600 text-white hover:bg-blue-700 rounded shadow text-center {{ $selectedId ? '' : 'opacity-50 pointer-events-none' }}"
                       data-template-href="{{ $link['template'] }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>
        @else
            <p class="text-gray-700 mb-4">
                No site visit found for this client. Create one to access the calculators.
            </p>

            <a href="{{ route('clients.site-visits.create', $client) }}"
               class="inline-block px-6 py-3 bg-green-600 text-white hover:bg-green-700 rounded shadow">
                Create Site Visit
            </a>
        @endif
    </div>
</div>
@endsection

@push('scripts')
@if (($siteVisitOptions ?? collect())->isNotEmpty())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const select = document.getElementById('client-site-visit-select');
        const links = document.querySelectorAll('#calculator-link-grid [data-template-href]');
        const meta = document.getElementById('selected-site-visit-meta');

        if (!select) return;

        const siteVisits = @json($siteVisitSummaries ?? []);

        const updateLinks = () => {
            const value = select.value;
            links.forEach((link) => {
                const template = link.dataset.templateHref;
                if (value) {
                    link.href = template.replace('__SITE_ID__', value);
                    link.classList.remove('opacity-50', 'pointer-events-none');
                } else {
                    link.href = '#';
                    link.classList.add('opacity-50', 'pointer-events-none');
                }
            });

            if (meta) {
                const visit = siteVisits.find((v) => String(v.id) === String(value));
                if (visit) {
                    meta.textContent = `Selected visit: ${visit.date ?? 'N/A'}${visit.property ? ' - ' + visit.property : ''}`;
                } else {
                    meta.textContent = '';
                }
            }
        };

        select.addEventListener('change', updateLinks);
        updateLinks();
    });
</script>
@endif
@endpush




