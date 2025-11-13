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
            <span>Total Cost:</span>
            <span>${{ number_format($data['labor_cost'] + $data['material_total'], 2) }}</span>
        </div>
        <div class="flex justify-between font-bold text-lg mt-2 border-t pt-2">
            <span>Final Price:</span>
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

    {{-- Actions --}}
    @php $downloadUrl = isset($calculation) ? route('calculators.pruning.downloadPdf', $calculation->id) : null; @endphp
    @include('calculators.partials.actions', [
        'calculationType' => 'pruning',
        'siteVisit' => $siteVisit,
        'data' => $data,
        'calculation' => $calculation ?? null,
        'downloadPdfUrl' => $downloadUrl,
    ])
</div>
@endsection
