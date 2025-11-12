@extends('layouts.sidebar')

@section('content')
<div class="max-w-5xl mx-auto py-10 space-y-6">
    <div>
        <p class="text-sm uppercase tracking-wide text-gray-500">Planting Summary</p>
        <h1 class="text-3xl font-bold text-gray-900">🌿 Planting Estimate</h1>
        <p class="text-gray-600">Labor includes facing and watering each plant.</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-xl font-semibold mb-2">Final Price</p>
        <p class="text-3xl font-bold text-green-700">${{ number_format($data['final_price'], 2) }}</p>
    </div>

    @if (!empty($data['materials']))
        @include('calculators.partials.materials_table', [
            'materials' => $data['materials'],
            'material_total' => $data['material_total'] ?? 0,
        ])
    @endif

    <section class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-semibold mb-4">⏱️ Labor Breakdown</h2>
        <ul class="space-y-2">
            @foreach ($data['labor_by_task'] as $task => $hours)
                <li class="flex justify-between border-b pb-1">
                    <span>{{ $task }}</span>
                    <span>{{ number_format($hours, 2) }} hrs</span>
                </li>
            @endforeach
        </ul>
        <div class="flex justify-between mt-4 text-sm text-gray-700">
            <span>Base Labor</span>
            <span>{{ number_format($data['labor_hours'], 2) }} hrs</span>
        </div>
        <div class="flex justify-between text-sm text-gray-700">
            <span>Drive Time</span>
            <span>{{ number_format($data['drive_time_hours'] ?? 0, 2) }} hrs</span>
        </div>
        <div class="flex justify-between text-sm text-gray-700">
            <span>Overhead</span>
            <span>{{ number_format($data['overhead_hours'] ?? 0, 2) }} hrs</span>
        </div>
        <div class="flex justify-between text-sm text-gray-700">
            <span>Site Visits</span>
            <span>{{ $data['visits'] ?? 'N/A' }}</span>
        </div>
        <div class="flex justify-between font-semibold text-lg mt-2">
            <span>Total Labor Hours</span>
            <span>{{ number_format($data['total_hours'], 2) }} hrs</span>
        </div>
        <div class="flex justify-between font-semibold text-lg">
            <span>Labor Cost</span>
            <span>${{ number_format($data['labor_cost'], 2) }}</span>
        </div>
    </section>

    <section class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-semibold mb-4">💰 Pricing Breakdown</h2>
        <div class="flex justify-between">
            <span>Labor Cost</span>
            <span>${{ number_format($data['labor_cost'], 2) }}</span>
        </div>
        <div class="flex justify-between">
            <span>Material Cost</span>
            <span>${{ number_format($data['material_total'], 2) }}</span>
        </div>
        <div class="flex justify-between border-t mt-2 pt-2 font-semibold">
            <span>Total Cost</span>
            <span>${{ number_format(($data['labor_cost'] ?? 0) + ($data['material_total'] ?? 0), 2) }}</span>
        </div>
        <div class="flex justify-between border-t mt-2 pt-2 font-bold text-lg">
            <span>Final Price</span>
            <span>${{ number_format($data['final_price'], 2) }}</span>
        </div>
    </section>

    @if (!empty($data['job_notes']))
        <section class="bg-yellow-50 border border-yellow-200 rounded p-4">
            <h2 class="text-lg font-semibold mb-1">📝 Job Notes</h2>
            <p class="text-gray-800 whitespace-pre-line">{{ $data['job_notes'] }}</p>
        </section>
    @endif

    <form method="POST" action="{{ route('site-visits.storeCalculation') }}">
        @csrf
        <input type="hidden" name="calculation_type" value="planting">
        <input type="hidden" name="site_visit_id" value="{{ $siteVisit->id }}">
        <input type="hidden" name="data" value="{{ json_encode($data) }}">
        @if (!empty($siteVisit->estimate_id))
            <input type="hidden" name="estimate_id" value="{{ $siteVisit->estimate_id }}">
        @endif
        <div class="flex flex-wrap gap-3">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">
                💾 Save to Site Visit
            </button>
            <button type="submit" name="replace" value="1" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-semibold">
                💾 Save & Replace on Estimate
            </button>
        </div>
    </form>

    @isset($calculation)
        <a href="{{ route('calculators.planting.downloadPdf', $calculation->id) }}"
           class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold">
            ⬇️ Download PDF
        </a>
    @endisset

    <div>
        <a href="{{ route('clients.show', $siteVisit->client_id) }}"
           class="inline-flex items-center px-5 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-semibold">
            ⬅️ Back to Client
        </a>
    </div>
</div>
@endsection
