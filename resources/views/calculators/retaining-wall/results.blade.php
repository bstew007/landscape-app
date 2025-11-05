@extends('layouts.sidebar')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <h1 class="text-3xl font-bold mb-6">📊 Retaining Wall Data</h1>
    
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

   {{-- Materials Summary --}}
@include('calculators.partials.materials_table', [
    'materials' => $data['materials'],
    'material_total' => $data['material_total']
])



    {{-- Quantities --}}
    <div class="bg-white p-6 rounded-lg shadow mb-8 mt-10">
        <h2 class="text-2xl font-semibold mb-4">📐 Quantities</h2>
        <ul class="grid grid-cols-1 md:grid-cols-2 gap-y-2 gap-x-6 text-gray-700">
            <li>Wall Area: <strong>{{ number_format($data['length'] * $data['height'], 2) }} sqft</strong></li>
            <li>Block Count: <strong>{{ $data['block_count'] }}</strong></li>
            <li>Capstones: <strong>{{ $data['cap_count'] }}</strong></li>
            <li>Gravel: <strong>{{ $data['gravel_tons'] }} tons</strong></li>
            <li>Topsoil: <strong>{{ $data['topsoil_yards'] }} cu yd</strong></li>
            <li>Drain Pipe: <strong>{{ $data['length'] }} ft</strong></li>
            <li>Underlayment: <strong>{{ $data['fabric_area'] }} sqft</strong></li>
            <li>Geogrid Layers: <strong>{{ $data['geogrid_layers'] }}</strong> ({{ $data['geogrid_lf'] }} lf)</li>
            <li>Adhesive Tubes: <strong>{{ $data['adhesive_tubes'] }}</strong></li>
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
            <span class="font-semibold">Wall Labor:</span>
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

   {{-- Allan Block Components --}}
@if (($data['block_system'] ?? 'standard') === 'allan_block')
    <div class="mt-8 border-t pt-6">
        <h2 class="text-xl font-bold mb-4">🧱 Allan Block Components</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto border border-gray-300 rounded shadow-sm text-sm">
                <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="px-4 py-2 border-b">Component</th>
                        <th class="px-4 py-2 border-b text-right">Quantity</th>
                        <th class="px-4 py-2 border-b text-right">Labor Hours</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="px-4 py-2 border-b">Straight Wall Area</td>
                        <td class="px-4 py-2 border-b text-right">{{ number_format($data['ab_straight_sqft'] ?? 0, 2) }} sqft</td>
                        <td class="px-4 py-2 border-b text-right">{{ number_format($data['labor_by_task']['ab_straight_wall'] ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2 border-b">Curved Wall Area</td>
                        <td class="px-4 py-2 border-b text-right">{{ number_format($data['ab_curved_sqft'] ?? 0, 2) }} sqft</td>
                        <td class="px-4 py-2 border-b text-right">{{ number_format($data['labor_by_task']['ab_curved_wall'] ?? 0, 2) }}</td>
                    </tr>

                    {{-- Show stairs only if present --}}
                    @if(($data['ab_step_count'] ?? 0) > 0)
                        <tr>
                            <td class="px-4 py-2 border-b">Stairs</td>
                            <td class="px-4 py-2 border-b text-right">{{ $data['ab_step_count'] }} steps</td>
                            <td class="px-4 py-2 border-b text-right">{{ number_format($data['labor_by_task']['ab_stairs'] ?? 0, 2) }}</td>
                        </tr>
                    @endif

                    <tr>
                        <td class="px-4 py-2">Columns</td>
                        <td class="px-4 py-2 text-right">{{ $data['ab_column_count'] ?? 0 }} columns</td>
                        <td class="px-4 py-2 text-right">{{ number_format($data['labor_by_task']['ab_columns'] ?? 0, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endif


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

    {{-- Save to Site Visit --}}
    <form method="POST" action="{{ route('site-visits.storeCalculation') }}">
        @csrf
        <input type="hidden" name="calculation_type" value="retaining_wall">
        <input type="hidden" name="site_visit_id" value="{{ $siteVisit->id }}">
        <input type="hidden" name="data" value="{{ json_encode($data) }}">
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold mb-4">
            💾 Save Calculation to Site Visit
        </button>
    </form>

    {{-- PDF Download Button --}}
    @if (isset($calculation))
        <div class="mt-4">
            <a href="{{ route('calculations.wall.downloadPdf', $calculation->id) }}"
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
