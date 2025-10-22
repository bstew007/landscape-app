@extends('layouts.sidebar')

@section('content')
<div class="max-w-5xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">📊 Paver Patio Estimate Summary</h1>

    {{-- Final Price --}}
    <div class="bg-white p-6 rounded-lg shadow mb-8">
        <p class="text-xl font-semibold mb-2">Final Price:</p>
        <p class="text-3xl font-bold text-green-700">${{ number_format($data['final_price'], 2) }}</p>
    </div>

    {{-- Materials --}}
    <div class="bg-white p-6 rounded-lg shadow mb-8">
        <h2 class="text-2xl font-semibold mb-4">🧱 Materials</h2>
        <ul class="space-y-2">
            @foreach ($data['materials'] as $label => $cost)
                <li class="flex justify-between border-b pb-1">
                    <span>{{ $label }}</span>
                    <span>${{ number_format($cost, 2) }}</span>
                </li>
            @endforeach
        </ul>
        <div class="flex justify-between mt-4 font-bold">
            <span>Total Material Cost:</span>
            <span>${{ number_format($data['material_total'], 2) }}</span>
        </div>
    </div>

    {{-- Quantities --}}
    <div class="bg-white p-6 rounded-lg shadow mb-8">
        <h2 class="text-2xl font-semibold mb-4">📐 Quantities</h2>
        <ul class="grid grid-cols-2 gap-y-2 text-gray-700">
            <li>Area: <strong>{{ number_format($data['area_sqft'], 2) }} sqft</strong></li>
            <li>Paver Count: <strong>{{ $data['paver_count'] }}</strong></li>
            <li>Base Material: <strong>{{ $data['base_tons'] }} tons (#78 gravel)</strong></li>
        </ul>
    </div>

    {{-- Labor Breakdown --}}
    <div class="bg-white p-6 rounded-lg shadow mb-8">
        <h2 class="text-2xl font-semibold mb-4">👷 Labor Breakdown</h2>
        <ul class="space-y-2">
            @foreach ($data['labor_by_task'] as $task => $hours)
                <li class="flex justify-between border-b pb-1 capitalize">
                    <span>{{ str_replace('_', ' ', $task) }}</span>
                    <span>{{ $hours }} hrs</span>
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

    {{-- Markup --}}
    <div class="bg-white p-6 rounded-lg shadow mb-8">
        <h2 class="text-2xl font-semibold mb-4">💰 Markup</h2>
        <div class="flex justify-between">
            <span>Markup (20%):</span>
            <span>${{ number_format($data['markup_amount'], 2) }}</span>
        </div>
    </div>

    {{-- Save Calculation --}}
    <form method="POST" action="{{ route('site-visits.storeCalculation') }}">
        @csrf
        <input type="hidden" name="calculation_type" value="paver_patio">
        <input type="hidden" name="site_visit_id" value="{{ $siteVisit->id }}">
        <input type="hidden" name="data" value="{{ json_encode($data) }}">

        <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold mb-4">
            💾 Save Calculation to Site Visit
        </button>
    </form>

    {{-- PDF Button (if saved already) --}}
    @isset($calculation)
        <a href="{{ route('calculations.patio.downloadPdf', $calculation->id) }}"
           class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold mb-4 ml-4">
            📄 Download PDF
        </a>
    @endisset

    {{-- Back Button --}}
    <div class="mt-6">
        <a href="{{ route('clients.show', $siteVisit->client_id) }}"
           class="bg-gray-600 hover:bg-gray-700 text-white px-5 py-3 rounded-lg font-semibold">
            🔙 Back to Client
        </a>
    </div>
</div>
@endsection
