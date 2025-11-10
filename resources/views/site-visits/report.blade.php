@php
    use Illuminate\Support\Facades\Storage;
@endphp

@extends('layouts.sidebar')

@section('content')
<div class="max-w-5xl mx-auto py-10 space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold">📋 Site Visit Report</h1>
            <p class="text-gray-600">Visit Date: {{ optional($siteVisit->visit_date ?? $siteVisit->created_at)->format('F j, Y') }}</p>
        </div>
        <a href="{{ route('site-visits.report.pdf', $siteVisit) }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
            📄 Download PDF
        </a>
    </div>

    <section class="bg-white p-6 rounded shadow space-y-2">
        <h2 class="text-2xl font-semibold">👤 Client Information</h2>
        <p><strong>Name:</strong> {{ $siteVisit->client->name }}</p>
        <p><strong>Email:</strong> {{ $siteVisit->client->email ?? '—' }}</p>
        <p><strong>Phone:</strong> {{ $siteVisit->client->phone ?? '—' }}</p>
        <p><strong>Address:</strong> {{ $siteVisit->client->address ?? '—' }}</p>
        <p><strong>Site Notes:</strong> {{ $siteVisit->notes ?? '—' }}</p>
    </section>

    @if ($siteVisit->photos->count())
        <section class="bg-white p-6 rounded shadow">
            <h2 class="text-2xl font-semibold mb-4">📷 Photos</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($siteVisit->photos as $photo)
                    <div>
                        <img src="{{ Storage::disk('public')->url($photo->path) }}"
                             alt="{{ $photo->caption ?? 'Site photo' }}"
                             class="w-full h-48 object-cover rounded">
                        <p class="mt-2 text-sm text-gray-700">{{ $photo->caption ?? '—' }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @foreach ($calculationsByType as $type => $calculations)
        @php
            $calculation = $calculations->first();
            $viewPath = "calculators.reports.$type";
        @endphp
        @if (View::exists($viewPath))
            @include($viewPath, ['calculation' => $calculation, 'siteVisit' => $siteVisit])
        @else
            <section class="bg-white p-6 rounded shadow">
                <h2 class="text-2xl font-semibold mb-4">{{ ucwords(str_replace('_', ' ', $type)) }}</h2>
                <p class="text-gray-600">No report view defined for this calculator yet.</p>
            </section>
        @endif
    @endforeach

    <div class="flex justify-between">
        <a href="{{ route('clients.show', $siteVisit->client_id) }}"
           class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
            🔙 Back to Client
        </a>
        <a href="{{ route('clients.site-visits.show', [$siteVisit->client_id, $siteVisit->id]) }}"
           class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
            📁 View Site Visit
        </a>
    </div>
</div>
@endsection
