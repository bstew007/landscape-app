@extends('layouts.sidebar')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <h1 class="text-3xl font-bold mb-6">📊 Fence Estimate Summary</h1>

    <hr class="my-4">

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


    {{-- Final Price Summary --}}
    <div class="bg-white p-6 rounded-lg shadow mb-8">
        <p class="text-xl font-semibold mb-2">Final Price:</p>
        <p class="text-3xl font-bold text-green-700">${{ number_format($data['final_price'], 2) }}</p>
    </div>

    <div class="bg-white p-6 rounded-lg shadow mb-8">
  <h2 class="text-2xl font-semibold mb-4">🧱 Materials Summary</h2>
<table class="w-full border-collapse text-sm mb-6">
    <thead>
        <tr class="bg-gray-100 text-left border-b">
            <th class="p-2">Material</th>
            <th class="p-2 text-right">Qty</th>
            <th class="p-2 text-right">Unit Cost</th>
            <th class="p-2 text-right">Total</th>
        </tr>
    </thead>
    <tbody>
    @foreach($data['materials'] as $label => $item)
    @if(is_array($item) && isset($item['qty'], $item['unit_cost'], $item['total']))
        <tr>
            <td>{{ $label }}</td>
            <td style="text-align: right;">{{ $item['qty'] }}</td>
            <td style="text-align: right;">${{ number_format($item['unit_cost'], 2) }}</td>
            <td style="text-align: right;">${{ number_format($item['total'], 2) }}</td>
        </tr>
    @endif
@endforeach
        <tr class="font-bold bg-gray-100">
                    <td colspan="3" class="px-4 py-2 text-right">Total Material Cost:</td>
                    <td class="px-4 py-2 text-right">${{ number_format($data['material_total'], 2) }}</td>
                </tr>
</tbody>
</table>
</div>


    {{-- Labor Breakdown --}}
    <div class="bg-white p-6 rounded-lg shadow mb-8 mt-10">
        <h2 class="text-2xl font-semibold mb-4">👷 Labor Breakdown</h2>
        <ul class="space-y-2">
            <li class="flex justify-between"><span>Base Labor Hours:</span><span>{{ number_format($data['labor_hours'], 2) }} hrs</span></li>
            <li class="flex justify-between"><span>Overhead + Drive Time:</span><span>{{ number_format($data['overhead_hours'], 2) }} hrs</span></li>
            <li class="flex justify-between font-bold text-lg"><span>Total Labor Hours:</span><span>{{ number_format($data['total_hours'], 2) }} hrs</span></li>
            <li class="flex justify-between font-bold text-lg"><span>Labor Cost:</span><span>${{ number_format($data['labor_cost'], 2) }}</span></li>
        </ul>
    </div>

    @if (!empty($labor_breakdown))
    <div class="mt-6">
        <h2 class="text-xl font-bold mb-2">🛠️ Labor Breakdown</h2>
        <table class="table-auto w-full text-sm border border-gray-300">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2 text-left border-b">Task</th>
                    <th class="px-4 py-2 text-left border-b">Hours</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($labor_breakdown as $task => $hours)
                    <tr>
                        <td class="px-4 py-2 border-b">{{ ucwords(str_replace('_', ' ', $task)) }}</td>
                        <td class="px-4 py-2 border-b">{{ number_format($hours, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif


     <div class="section">
        <h2>Pricing Breakdown</h2>
        <table>
            <tbody>
                <tr>
                    <td><strong>Labor Cost</strong></td>
                    <td style="text-align: right;">${{ number_format($data['labor_cost'], 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Material Cost</strong></td>
                    <td style="text-align: right;">${{ number_format($data['material_total'], 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Total Cost (Before Margin)</strong></td>
                    <td style="text-align: right;">${{ number_format($data['labor_cost'] + $data['material_total'], 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Target Margin</strong></td>
                    <td style="text-align: right;">{{ $data['markup'] }}%</td>
                </tr>
                <tr>
                    <td><strong>Markup (Dollar Amount)</strong></td>
                    <td style="text-align: right;">${{ number_format($data['markup_amount'], 2) }}</td>
                </tr>
                <tr style="font-weight: bold;">
                    <td><strong>Final Price (With Margin)</strong></td>
                    <td style="text-align: right;">${{ number_format($data['final_price'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>


    {{-- Job Notes --}}
    @if (!empty($data['job_notes']))
    <div class="bg-yellow-50 p-4 rounded shadow mb-6 border border-yellow-300">
        <h2 class="text-xl font-semibold mb-2">📌 Job Notes</h2>
        <p class="text-gray-800 whitespace-pre-line">{{ $data['job_notes'] }}</p>
    </div>
    @endif

    {{-- Job Notes --}}
    @if (!empty($data['job_notes']))
    <div class="bg-yellow-50 p-4 rounded shadow mb-6 border border-yellow-300">
        <h2 class="text-xl font-semibold mb-2">📌 Job Notes</h2>
        <p class="text-gray-800 whitespace-pre-line">{{ $data['job_notes'] }}</p>
    </div>
    @endif

    {{-- Save to Site Visit --}}
    <form method="POST" action="{{ route('site-visits.storeCalculation') }}">
        @csrf
        <input type="hidden" name="calculation_type" value="fence">
        <input type="hidden" name="site_visit_id" value="{{ $siteVisit->id }}">
        {{-- Keep the data available for saving to Site Visit --}}
<input type="hidden" name="data" value="{{ htmlentities(json_encode($data)) }}">

        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold mb-4">
            💾 Save Calculation to Site Visit
        </button>
    </form>

    {{-- PDF Download Button --}}
@if (isset($calculation))
    <div class="mt-4">
        <a href="{{ route('calculations.fence.downloadPdf', $calculation->id) }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold"
           target="_blank">
            🧾 Download PDF Estimate
        </a>
    </div>
@endif


    {{-- Back Button --}}
    <div class="mt-6">
        <a href="{{ route('clients.show', $siteVisit->client_id) }}"
           class="bg-gray-600 hover:bg-gray-700 text-white px-5 py-3 rounded-lg font-semibold">
            🔙 Back to Client
        </a>
    </div>
</div>

@endsection


