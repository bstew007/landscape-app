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

  
   {{-- Final Price Summary --}}
    <div class="bg-white p-6 rounded-lg shadow mb-8">
        <p class="text-xl font-semibold mb-2">Final Price:</p>
        <p class="text-3xl font-bold text-green-700">${{ number_format($data['final_price'], 2) }}</p>
    </div>

    {{-- Materials Summary --}}
    <div class="bg-white p-6 rounded-lg shadow mb-8">
  @include('calculators.partials.materials_table', [
    'materials' => $data['materials'],
    'material_total' => $data['material_total']
])

    {{-- 📐 Quantities Summary --}}
<div class="bg-white p-6 rounded-lg shadow mb-8">
    <h2 class="text-2xl font-semibold mb-4">📐 Quantities</h2>
    <ul class="grid grid-cols-2 gap-y-2 text-gray-700">

        {{-- Mulch --}}
        @if (!empty($data['mulching']['materials']))
            <li>Mulch Type: 
                <strong>{{ $data['mulching']['materials']['label'] ?? '—' }}</strong>
            </li>
            <li>Estimated Cubic Yards: 
                <strong>{{ number_format($data['mulching']['materials']['qty'] ?? 0, 2) }} CY</strong>
            </li>
            <li>Install Depth: 
                <strong>{{ $data['mulching']['depth'] ?? '—' }} in</strong>
            </li>
            <li>Square Footage: 
                <strong>{{ number_format($data['mulching']['sqft'] ?? 0) }} sq ft</strong>
            </li>
        @endif

        {{-- Pine Needles --}}
        @if (!empty($data['pine_needles']['materials']))
            <li>Pine Needle Bales: 
                <strong>{{ number_format($data['pine_needles']['materials']['qty'] ?? 0) }}</strong>
            </li>
            <li>Estimated Coverage: 
                <strong>{{ number_format(($data['pine_needles']['materials']['qty'] ?? 0) * 45) }} sq ft</strong>
            </li>
            <li>Material Type: 
                <strong>Pine Straw</strong>
            </li>
        @endif

    </ul>
</div>

{{-- Labor Breakdown --}}
<div class="bg-white p-6 rounded-lg shadow mb-8">
    <h2 class="text-2xl font-semibold mb-4">👷 Labor Breakdown</h2>
    <ul class="space-y-2">
        @foreach ($data['labor_by_task'] as $task => $hours)
            <li class="flex justify-between border-b pb-1">
                <span>{{ $task }}</span>
                <span>{{ number_format($hours, 2) }} hrs</span>
            </li>
        @endforeach
    </ul>
    <div class="flex justify-between mt-4">
        <span class="font-semibold">Base Labor:</span>
        <span>{{ number_format($data['labor_hours'], 2) }} hrs</span>
    </div>
    <div class="flex justify-between">
        <span class="font-semibold">Overhead + Drive Time:</span>
        <span>{{ number_format($data['overhead_hours'], 2) }} hrs</span>
    </div>
    <div class="flex justify-between font-bold text-lg">
        <span>Total Labor Hours:</span>
        <span>{{ number_format($data['total_hours'], 2) }} hrs</span>
    </div>
    <div class="flex justify-between font-bold text-lg mt-2">
        <span>Labor Cost:</span>
        <span>${{ number_format($data['labor_cost'], 2) }}</span>
    </div>
</div>



    {{-- 💾 Save to Site Visit --}}
    <form method="POST" action="{{ route('site-visits.storeCalculation') }}">
        @csrf
        <input type="hidden" name="calculation_type" value="enhancements">
        <input type="hidden" name="site_visit_id" value="{{ $siteVisit->id }}">
        <input type="hidden" name="data" value="{{ htmlentities(json_encode($data)) }}">


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
