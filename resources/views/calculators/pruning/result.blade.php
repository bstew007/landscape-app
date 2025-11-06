@extends('layouts.sidebar')

@section('content')
<div class="max-w-5xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">✂️ Pruning Estimate Summary</h1>

    {{-- Client Info --}}
    <div class="bg-white p-6 rounded-lg shadow mb-8 mt-10">
        <hr class="my-4">
        <h2 class="text-2xl font-semibold mb-4">👤 Client Information: {{ $siteVisit->client->name }}</h2>
        <table class="mb-6">
            <tr><td><strong>Name:</strong></td><td>{{ $siteVisit->client->first_name }} {{ $siteVisit->client->last_name }}</td></tr>
            <tr><td><strong>Email:</strong></td><td>{{ $siteVisit->client->email ?? '—' }}</td></tr>
            <tr><td><strong>Phone:</strong></td><td>{{ $siteVisit->client->phone ?? '—' }}</td></tr>
            <tr><td><strong>Address:</strong></td><td>{{ $siteVisit->client->address ?? '—' }}</td></tr>
            <tr><td><strong>Site Visit Date:</strong></td><td>{{ $siteVisit->created_at->format('F j, Y') }}</td></tr>
        </table>
    </div>

    {{-- Final Price --}}
    <div class="bg-white p-6 rounded-lg shadow mb-8">
        <p class="text-xl font-semibold mb-2">Final Price:</p>
        <p class="text-3xl font-bold text-green-700">${{ number_format($data['final_price'], 2) }}</p>
    </div>

    {{-- Materials Summary --}}
   @if (!empty($data['materials']))
    @include('calculators.partials.materials_table', [
        'materials' => $data['materials'],
        'material_total' => $data['material_total'] ?? 0
    ])
@endif

    {{-- Labor Breakdown --}}
    <div class="bg-white p-6 rounded-lg shadow mb-8">
        <h2 class="text-2xl font-semibold mb-4">👷 Labor Breakdown</h2>
        <ul class="space-y-2">
            @foreach ($data['labor_by_task'] as $task => $hours)
                <li class="flex justify-between border-b pb-1 capitalize">
                    <span>{{ str_replace('_', ' ', $task) }}</span>
                    <span>{{ number_format($hours, 2) }} hrs</span>
                </li>
            @endforeach
        </ul>
        <div class="flex justify-between mt-4">
            <span class="font-semibold">Base Labor:</span>
            <span>{{ number_format($data['labor_hours'], 2) }} hrs</span>
        </div>
        <div class="flex justify-between">
            <span class="font-semibold">Overhead:</span>
            <span>{{ number_format($data['overhead_hours'], 2) }} hrs</span>
        </div>
         <div class="flex justify-between">
        <span class="font-semibold">Drive Time:</span>
        <span>{{ number_format($data['drive_time_hours'], 2) }} hrs</span>
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

    {{-- 💰 Pricing Breakdown --}}
    <div class="bg-white p-6 rounded-lg shadow mb-8">
        <h2 class="text-2xl font-semibold mb-4">💰 Pricing Breakdown</h2>
        <div class="flex justify-between">
            <span>Labor Cost:</span>
            <span>${{ number_format($data['labor_cost'], 2) }}</span>
        </div>
        <div class="flex justify-between">
            <span>Material Cost:</span>
            <span>${{ number_format($data['material_total'], 2) }}</span>
        </div>
        <div class="flex justify-between border-t pt-2 mt-2 font-semibold">
            <span>Total Cost (Before Margin):</span>
            <span>${{ number_format($data['labor_cost'] + $data['material_total'], 2) }}</span>
        </div>
        <div class="flex justify-between mt-2">
            <span>Target Margin:</span>
            <span>{{ $data['markup'] }}%</span>
        </div>
        <div class="flex justify-between">
            <span>Markup (Dollar Amount):</span>
            <span>${{ number_format($data['markup_amount'], 2) }}</span>
        </div>
        <div class="flex justify-between font-bold text-lg mt-2 border-t pt-2">
            <span>Final Price (With Margin):</span>
            <span>${{ number_format($data['final_price'], 2) }}</span>
        </div>
    </div>

    {{-- Job Notes --}}
    @if (!empty($data['job_notes']))
        <div class="bg-yellow-50 p-4 rounded shadow mb-6 border border-yellow-300">
            <h2 class="text-xl font-semibold mb-2">📌 Job Notes</h2>
            <p class="text-gray-800 whitespace-pre-line">{{ $data['job_notes'] }}</p>
        </div>
    @endif

    {{-- Save Calculation --}}
    <form method="POST" action="{{ route('site-visits.storeCalculation') }}">
        @csrf
        <input type="hidden" name="calculation_type" value="pruning">
        <input type="hidden" name="site_visit_id" value="{{ $siteVisit->id }}">
        <input type="hidden" name="data" value="{{ json_encode($data) }}">

        <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold mb-4">
            💾 Save Calculation to Site Visit
        </button>
    </form>

    {{-- PDF Download --}}
    @isset($calculation)
        <a href="{{ route('calculators.pruning.downloadPdf', $calculation->id) }}"
           class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold mb-4 ml-4">
            📄 Download PDF
        </a>
    @endisset

    {{-- Back to Client --}}
    <div class="mt-6">
        <a href="{{ route('clients.show', $siteVisit->client_id) }}"
           class="bg-gray-600 hover:bg-gray-700 text-white px-5 py-3 rounded-lg font-semibold">
            🔙 Back to Client
        </a>
    </div>
</div>
@endsection

