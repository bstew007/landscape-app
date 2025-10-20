<div class="mt-8">
    <h2 class="text-xl font-bold mb-4">🧮 Estimators</h2>

    @if ($siteVisit)
        <a href="{{ route('calculators.wall.form', ['site_visit_id' => $siteVisit->id]) }}"
           class="inline-block px-6 py-3 bg-blue-600 text-white hover:bg-blue-700 rounded shadow">
            ➕ Retaining Wall Calculator
        </a>
        <p class="text-sm text-gray-500 mt-2">
            Last site visit: {{ optional($siteVisit->visit_date)->format('F j, Y') ?? 'N/A' }}
        </p>
    @else
        <p class="text-gray-700 mb-4">No site visit found for this client. You’ll need to create one to access estimators.</p>

        <a href="{{ route('clients.site-visits.create', $client) }}"
           class="inline-block px-6 py-3 bg-green-600 text-white hover:bg-green-700 rounded shadow">
            ➕ Create Site Visit
        </a>
    @endif
</div>
