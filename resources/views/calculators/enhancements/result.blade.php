@extends('layouts.sidebar')

@section('content')
<div class="max-w-5xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">🌿 Landscape Enhancements Summary</h1>

    {{-- 📌 Client Info --}}
    <div class="bg-white p-6 rounded-lg shadow mb-8">
        <h2 class="text-2xl font-semibold mb-4">👤 Client Information: {{ $siteVisit->client->name }}</h2>
        <table class="mb-6">
            <tr><td><strong>Name:</strong></td><td>{{ $siteVisit->client->first_name }} {{ $siteVisit->client->last_name }}</td></tr>
            <tr><td><strong>Email:</strong></td><td>{{ $siteVisit->client->email ?? '—' }}</td></tr>
            <tr><td><strong>Phone:</strong></td><td>{{ $siteVisit->client->phone ?? '—' }}</td></tr>
            <tr><td><strong>Address:</strong></td><td>{{ $siteVisit->client->address ?? '—' }}</td></tr>
            <tr><td><strong>Site Visit Date:</strong></td><td>{{ $siteVisit->created_at->format('F j, Y') }}</td></tr>
        </table>
    </div>

    {{-- 🔧 Enhancement Details --}}
    @if (isset($pruning['tasks']) && count($pruning['tasks']))
        @include('calculators.enhancements.partials.pruning_summary', ['pruning' => $pruning])
    @endif

    @if (isset($mulching['cubic_yards']))
        @include('calculators.enhancements.partials.mulching_summary', ['mulching' => $mulching])
    @endif

    @if (isset($weeding['tasks']) && count($weeding['tasks']))
        @include('calculators.enhancements.partials.weeding_summary', ['weeding' => $weeding])
    @endif

    @if (isset($pine_needles['tasks']) && count($pine_needles['tasks']))
        @include('calculators.enhancements.partials.pine_needles_summary', ['pine_needles' => $pine_needles])
    @endif

    {{-- 💼 Labor Totals --}}
    <div class="bg-white p-6 rounded-lg shadow mb-8">
        <h2 class="text-2xl font-semibold mb-4">👷 Labor Summary</h2>
        @php
            $totalHours = 0;
            $totalCost = 0;

            foreach (['pruning', 'mulching', 'weeding', 'pine_needles'] as $section) {
                if (isset($$section)) {
                    $totalHours += $$section['labor_hours'] ?? 0;
                    $totalCost += $$section['labor_cost'] ?? 0;
                }
            }
        @endphp

        <div class="flex justify-between">
            <span>Total Labor Hours:</span>
            <span class="font-bold">{{ number_format($totalHours, 2) }} hrs</span>
        </div>
        <div class="flex justify-between">
            <span>Total Labor Cost:</span>
            <span class="font-bold">${{ number_format($totalCost, 2) }}</span>
        </div>
    </div>

    {{-- 💾 Save to Site Visit --}}
    <form method="POST" action="{{ route('site-visits.storeCalculation') }}">
        @csrf
        <input type="hidden" name="calculation_type" value="enhancements">
        <input type="hidden" name="site_visit_id" value="{{ $siteVisit->id }}">
        <input type="hidden" name="data" value="{{ htmlentities(json_encode(compact('pruning', 'mulching', 'weeding', 'pine_needles'))) }}">

        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold mb-4">
            💾 Save Calculation to Site Visit
        </button>
    </form>

    {{-- 🧾 Download PDF --}}
    @if (isset($calculation))
        <div class="mt-4">
            <a href="{{ route('calculators.enhancements.downloadPdf', $calculation->id) }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold"
               target="_blank">
                🧾 Download PDF Estimate
            </a>
        </div>
    @endif

    {{-- 🔙 Back to Client --}}
    <div class="mt-6">
        <a href="{{ route('clients.show', $siteVisit->client_id) }}"
           class="bg-gray-600 hover:bg-gray-700 text-white px-5 py-3 rounded-lg font-semibold">
            🔙 Back to Client
        </a>
    </div>
</div>
@endsection
