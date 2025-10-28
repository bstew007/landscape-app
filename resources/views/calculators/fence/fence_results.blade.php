@extends('layouts.sidebar')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <h1 class="text-3xl font-bold mb-6">📊 Fence Estimate Summary</h1>

    <h1>Cape Fear Landscaping</h1>
    <hr class="my-4">

    <h3>Client Information: {{ $siteVisit->client->name }}</h3>
    <table class="mb-6">
        <tr><td><strong>Name:</strong></td><td>{{ $siteVisit->client->full_name }}</td></tr>
        <tr><td><strong>Address:</strong></td><td>{{ $siteVisit->client->address ?? '—' }}</td></tr>
        <tr><td><strong>Site Visit Date:</strong></td><td>{{ $siteVisit->created_at->format('F j, Y') }}</td></tr>
    </table>

    {{-- Final Price Summary --}}
    <div class="bg-white p-6 rounded-lg shadow mb-8">
        <p class="text-xl font-semibold mb-2">Final Price:</p>
        <p class="text-3xl font-bold text-green-700">${{ number_format($data['final_price'], 2) }}</p>
    </div>

   <h2 class="text-xl font-bold mt-6 mb-2">Materials Summary</h2>

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
            <tr class="border-b">
                <td class="p-2">{{ $label }}</td>
                <td class="p-2 text-right">{{ $item['qty'] }}</td>
                <td class="p-2 text-right">${{ number_format($item['unit_cost'], 2) }}</td>
                <td class="p-2 text-right font-semibold">${{ number_format($item['total'], 2) }}</td>
            </tr>
        @else
            {{-- fallback for legacy data or bad structure --}}
            <tr class="border-b bg-red-50 text-red-600">
                <td class="p-2">{{ $label }}</td>
                <td colspan="3" class="p-2 text-right font-semibold">⚠️ Invalid material structure</td>
            </tr>
        @endif
    @endforeach
</tbody>
</table>

<div class="flex justify-between font-bold text-lg">
    <span>Total Material Cost:</span>
    <span>${{ number_format($data['material_total'], 2) }}</span>
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


    {{-- Markup Section --}}
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
</div>

@endsection


