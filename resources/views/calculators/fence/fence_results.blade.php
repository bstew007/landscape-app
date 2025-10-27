@extends('layouts.sidebar')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">
    <h1 class="text-3xl font-bold mb-6">🛠️ Fence Estimate Results</h1>

    <div class="bg-white p-6 rounded-lg shadow space-y-6">
        <div>
            <h2 class="text-xl font-semibold">Fence Type: {{ ucfirst($data['fence_type']) }}</h2>
            <p class="text-gray-600">Height: {{ $data['height'] }}'</p>
            <p class="text-gray-600">Total Length: {{ $data['total_length'] }} ft</p>
            <p class="text-gray-600">Adjusted Length (after gates): {{ $data['adjusted_length'] }} ft</p>
        </div>

        <div class="grid grid-cols-2 gap-6">
            <div>
                <h3 class="font-semibold mb-2">Gates</h3>
                <p>4' Gates: {{ $data['gate_4ft'] }}</p>
                <p>5' Gates: {{ $data['gate_5ft'] }}</p>
            </div>

            @if ($data['fence_type'] === 'wood')
            <div>
                <h3 class="font-semibold mb-2">Wood Fence Details</h3>
                <p>Total Posts: {{ $data['total_posts'] }}</p>
                <p>Pickets per Foot: {{ $data['pickets_per_foot'] }}</p>
                <p>Total Pickets: {{ $data['total_pickets'] }}</p>
                <p>Shadow Box: {{ ($data['shadow_box'] ?? false) ? 'Yes' : 'No' }}</p>
            </div>
            @else
            <div>
                <h3 class="font-semibold mb-2">Vinyl Fence Details</h3>
                <p>Panels: {{ $data['panel_count'] }}</p>
                <p>Line Posts: {{ $data['line_posts'] }}</p>
                <p>Corner Posts: {{ $data['corner_posts'] }}</p>
                <p>End Posts: {{ $data['end_posts'] }}</p>
                <p>Gate Posts: {{ $data['gate_posts'] }}</p>
                <p>Total Posts: {{ $data['total_posts'] }}</p>
            </div>
            @endif
        </div>

        <div class="mt-4">
            <h3 class="font-semibold mb-2">Concrete</h3>
            <p>50lb Bags of Concrete Needed: {{ $data['concrete_bags'] }}</p>
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('clients.show', $calculation->siteVisit->client_id) }}"
           class="inline-block bg-gray-600 text-white px-5 py-3 rounded hover:bg-gray-700">
            🔙 Back to Client
        </a>
    </div>
</div>
@endsection