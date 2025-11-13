@extends('layouts.sidebar')

@section('content')
<div class="max-w-5xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">🏟️ Synthetic Turf Estimate Summary</h1>

    @include('calculators.partials.client_info', ['siteVisit' => $siteVisit])

    <div class="bg-white p-6 rounded-lg shadow mb-8">
        <p class="text-xl font-semibold mb-2">Final Price:</p>
        <p class="text-3xl font-bold text-green-700">${{ number_format($data['final_price'], 2) }}</p>
    </div>

    <div class="bg-white p-6 rounded-lg shadow mb-8">
        <h2 class="text-2xl font-semibold mb-4">📐 Project Inputs</h2>
        <table class="w-full text-left">
            <tr>
                <td class="font-semibold pr-4 py-1">Area:</td>
                <td>{{ number_format($data['area_sqft'] ?? 0, 2) }} sq ft</td>
            </tr>
            <tr>
                <td class="font-semibold pr-4 py-1">Edging:</td>
                <td>{{ number_format($data['edging_linear_ft'] ?? 0, 2) }} linear ft</td>
            </tr>
            <tr>
                <td class="font-semibold pr-4 py-1">Turf:</td>
                <td>{{ $data['turf_name'] ?? ucfirst($data['turf_grade'] ?? '') }} @ ${{ number_format($data['turf_unit_cost'] ?? 0, 2) }}/sq ft</td>
            </tr>
        </table>
    </div>

    @if (!empty($data['materials']))
        @include('calculators.partials.materials_table', [
            'materials' => $data['materials'],
            'material_total' => $data['material_total'] ?? 0
        ])
    @endif

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
            <span class="font-semibold">Job Site Visits:</span>
            <span>{{ $data['visits'] ?? 'N/A' }}</span>
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
            <span>Total Cost:</span>
            <span>${{ number_format($data['labor_cost'] + $data['material_total'], 2) }}</span>
        </div>
        <div class="flex justify-between font-bold text-lg mt-2 border-t pt-2">
            <span>Final Price:</span>
            <span>${{ number_format($data['final_price'], 2) }}</span>
        </div>
    </div>

    @if (!empty($data['job_notes']))
        <div class="bg-yellow-50 p-4 rounded shadow mb-6 border border-yellow-300">
            <h2 class="text-xl font-semibold mb-2">📌 Job Notes</h2>
            <p class="text-gray-800 whitespace-pre-line">{{ $data['job_notes'] }}</p>
        </div>
    @endif

    {{-- Actions --}}
    @php $downloadUrl = isset($calculation) ? route('calculators.syn_turf.downloadPdf', $calculation->id) : null; @endphp
    @include('calculators.partials.actions', [
        'calculationType' => 'syn_turf',
        'siteVisit' => $siteVisit,
        'data' => $data,
        'calculation' => $calculation ?? null,
        'downloadPdfUrl' => $downloadUrl,
    ])
</div>
@endsection
