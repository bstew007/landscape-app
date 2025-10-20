@extends('layouts.sidebar')

@section('content')

@if(!isset($data['length']))
    <div class="bg-red-100 text-red-700 p-4 rounded mb-6">
        ⚠️ No estimate data available. Please calculate a retaining wall estimate first.
    </div>
    <a href="{{ route('calculators.wall.form') }}" class="text-blue-600 underline">
        ➡️ Go to Retaining Wall Calculator
    </a>
    @php return; @endphp
@endif

<div class="max-w-5xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">📊 Retaining Wall Estimate</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
        <div class="bg-white p-4 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Project Inputs</h2>
            <ul class="text-gray-700 space-y-1">
                <li><strong>Length:</strong> {{ $data['length'] }} ft</li>
                <li><strong>Height:</strong> {{ $data['height'] }} ft</li>
                <li><strong>Crew Size:</strong> {{ $data['crew_size'] }}</li>
                <li><strong>Build Method:</strong> {{ ucfirst($data['equipment']) }}</li>
                <li><strong>Wall System:</strong> {{ ucfirst(str_replace('_', ' ', $data['block_type'])) }}</li>
                <li><strong>Labor Rate:</strong> ${{ $data['labor_rate'] }}/hr</li>
                <li><strong>Markup:</strong> {{ $data['markup'] }}%</li>
            </ul>
        </div>

        <div class="bg-white p-4 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Labor Summary</h2>
            <ul class="text-gray-700 space-y-1">
                <li><strong>Wall Labor:</strong> {{ $data['labor_hours'] }} hrs</li>
                <li><strong>Overhead Hours:</strong> {{ $data['overhead_hours'] }} hrs</li>
                <li><strong>Total Hours:</strong> {{ $data['total_hours'] }} hrs</li>
                <li><strong>Labor Cost:</strong> ${{ number_format($data['labor_cost'], 2) }}</li>
            </ul>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <h2 class="text-xl font-semibold mb-4">📦 Materials & Costs</h2>
        <table class="min-w-full table-auto text-left text-sm text-gray-800">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2">Item</th>
                    <th class="px-4 py-2">Cost</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['materials'] as $label => $cost)
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ $label }}</td>
                        <td class="px-4 py-2">${{ number_format($cost, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="font-bold border-t-2">
                    <td class="px-4 py-2">Total Material Cost</td>
                    <td class="px-4 py-2">${{ number_format($data['material_total'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-semibold mb-4">💰 Pricing Summary</h2>
        <ul class="text-gray-800 text-lg space-y-2">
            <li><strong>Material Cost:</strong> ${{ number_format($data['material_total'], 2) }}</li>
            <li><strong>Labor Cost:</strong> ${{ number_format($data['labor_cost'], 2) }}</li>
            <li><strong>Subtotal:</strong> ${{ number_format($data['material_total'] + $data['labor_cost'], 2) }}</li>
            <li><strong>Markup ({{ $data['markup'] }}%):</strong> ${{ number_format($data['markup_amount'], 2) }}</li>
            <li class="text-2xl font-bold text-blue-700"><strong>Total Price:</strong> ${{ number_format($data['final_price'], 2) }}</li>
        </ul>
    </div>

    {{-- Save to Site Visit Form --}}
<div class="mt-10 bg-white p-6 rounded-lg shadow">
    <form method="POST" action="{{ route('site-visits.storeCalculation') }}">
        @csrf
        <input type="hidden" name="site_visit_id" value="{{ $siteVisit->id }}">
        <input type="hidden" name="calculation_type" value="retaining_wall">
        <input type="hidden" name="data" value='@json($data)'>

        <button type="submit"
                class="w-full sm:w-auto px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow">
            💾 Save to Site Visit
        </button>
    </form>
</div>


    <div class="mt-8">
        <a href="{{ route('calculators.wall.form') }}"
           class="inline-block px-6 py-3 bg-gray-700 text-white hover:bg-gray-800 rounded-lg font-semibold">
            🔁 New Estimate
        </a>
    </div>
</div>
@endsection
