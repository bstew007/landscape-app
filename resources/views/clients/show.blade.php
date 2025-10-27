@extends('layouts.sidebar')
@section('content')

 <div class="max-w-4xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">👤 Client Details</h1>

    <div class="bg-white p-6 rounded-lg shadow text-gray-800 space-y-2 mb-6">
        <p><strong>Name:</strong> {{ $client->first_name }} {{ $client->last_name }}</p>
        <p><strong>Email:</strong> {{ $client->email ?? '—' }}</p>
        <p><strong>Phone:</strong> {{ $client->phone ?? '—' }}</p>
        <p><strong>Address:</strong> {{ $client->address ?? '—' }}</p>
    </div>   
<div class="mt-8">
    <h2 class="text-xl font-bold mb-4">🧮 Add New Calculations to Site Visit</h2>

    @if ($siteVisit)
        <a href="{{ route('calculators.wall.form', ['site_visit_id' => $siteVisit->id]) }}"
           class="inline-block px-6 py-3 bg-blue-600 text-white hover:bg-blue-700 rounded shadow">
            ➕ Retaining Wall Calculator
        </a>

        <a href="{{ route('calculators.patio.form', ['site_visit_id' => $siteVisit->id]) }}"
           class="inline-block px-6 py-3 bg-blue-600 text-white hover:bg-blue-700 rounded shadow">
            ➕ Paver Calculator
        </a>

         <a href="{{ route('fence.form', ['site_visit_id' => $siteVisit->id]) }}"
           class="inline-block px-6 py-3 bg-blue-600 text-white hover:bg-blue-700 rounded shadow">
            ➕ Fence Calculator
        </a>

        <a href="{{ route('calculators.enhancements.form', ['site_visit_id' => $siteVisit->id]) }}"
           class="inline-block px-6 py-3 bg-blue-600 text-white hover:bg-blue-700 rounded shadow">
            ➕ Enhancements Calculator
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
@endsection
