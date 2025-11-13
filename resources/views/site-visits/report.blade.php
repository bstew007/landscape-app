@php
    use Illuminate\Support\Facades\Storage;
@endphp

@extends('layouts.sidebar')

@section('content')
<div class="max-w-5xl mx-auto py-10 space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold">Site Visit Report</h1>
            <p class="text-gray-600">
                Visit Date: {{ optional($siteVisit->visit_date ?? $siteVisit->created_at)->format('F j, Y') }}
            </p>
        </div>
        <a href="{{ route('site-visits.report.pdf', $siteVisit) }}"
           class="bg-brand-700 hover:bg-brand-800 text-white px-4 py-2 rounded">
            Download PDF
        </a>
    </div>

    <section class="bg-white p-6 rounded shadow space-y-2">
        <h2 class="text-2xl font-semibold">Client Information</h2>
        <p><strong>Name:</strong> {{ $siteVisit->client->name }}</p>
        <p><strong>Email:</strong> {{ $siteVisit->client->email ?? 'N/A' }}</p>
        <p><strong>Phone:</strong> {{ $siteVisit->client->phone ?? 'N/A' }}</p>
        <p><strong>Site Notes:</strong> {{ $siteVisit->notes ?? 'N/A' }}</p>
    </section>

    @if ($siteVisit->property)
        <section class="bg-white p-6 rounded shadow space-y-2">
            <h2 class="text-2xl font-semibold">Property Details</h2>
            <p><strong>Name:</strong> {{ $siteVisit->property->name }}</p>
            <p><strong>Type:</strong> {{ ucfirst($siteVisit->property->type) }}</p>
            <p><strong>Address:</strong> {{ $siteVisit->property->display_address ?? 'N/A' }}</p>
            <p>
                <strong>On-site Contact:</strong>
                {{ $siteVisit->property->contact_name ?? 'N/A' }}
                @if($siteVisit->property->contact_phone)
                    — {{ $siteVisit->property->contact_phone }}
                @endif
                @if($siteVisit->property->contact_email)
                    ({{ $siteVisit->property->contact_email }})
                @endif
            </p>
        </section>
    @endif

    @if ($siteVisit->photos->count())
        <section class="bg-white p-6 rounded shadow">
            <h2 class="text-2xl font-semibold mb-4">Photos</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-200 text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 border-b text-left">Preview</th>
                            <th class="px-4 py-2 border-b text-left">Caption</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($siteVisit->photos as $photo)
                            <tr>
                                <td class="px-4 py-2 border-b">
                                    <img src="{{ Storage::disk('public')->url($photo->path) }}"
                                         alt="{{ $photo->caption ?? 'Site photo' }}"
                                         class="w-48 h-32 object-cover rounded">
                                </td>
                                <td class="px-4 py-2 border-b align-top">{{ $photo->caption ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    @foreach ($calculationsByType as $type => $calculations)
        @php
            $calculation = $calculations->first();
            $viewPath = "calculators.reports.$type";
        @endphp
        @if (View::exists($viewPath))
            @include($viewPath, ['calculation' => $calculation, 'siteVisit' => $siteVisit])
        @else
            <section class="bg-white p-6 rounded shadow">
                <h2 class="text-2xl font-semibold mb-4">{{ ucwords(str_replace('_', ' ', $type)) }}</h2>
                <p class="text-gray-600">No report view defined for this calculator yet.</p>
            </section>
        @endif
    @endforeach

    @if ($reportSummary->count())
        <section class="bg-white p-6 rounded shadow space-y-4">
            <h2 class="text-2xl font-semibold">Calculator Summary</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-200 text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 border-b text-left">Calculator</th>
                            <th class="px-4 py-2 border-b text-right">Labor Cost</th>
                            <th class="px-4 py-2 border-b text-right">Material Cost</th>
                            <th class="px-4 py-2 border-b text-right">Total Cost</th>
                            <th class="px-4 py-2 border-b text-right">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reportSummary as $row)
                            <tr>
                                <td class="px-4 py-2 border-b">{{ $row['label'] }}</td>
                                <td class="px-4 py-2 border-b text-right">${{ number_format($row['labor'], 2) }}</td>
                                <td class="px-4 py-2 border-b text-right">${{ number_format($row['materials'], 2) }}</td>
                                <td class="px-4 py-2 border-b text-right">${{ number_format($row['cost'], 2) }}</td>
                                <td class="px-4 py-2 border-b text-right">${{ number_format($row['price'], 2) }}</td>
                            </tr>
                        @endforeach
                        <tr class="bg-gray-50 font-semibold">
                            <td class="px-4 py-2 border-t text-right">Totals:</td>
                            <td class="px-4 py-2 border-t text-right">${{ number_format($reportTotals['labor'], 2) }}</td>
                            <td class="px-4 py-2 border-t text-right">${{ number_format($reportTotals['materials'], 2) }}</td>
                            <td class="px-4 py-2 border-t text-right">${{ number_format($reportTotals['cost'], 2) }}</td>
                            <td class="px-4 py-2 border-t text-right">${{ number_format($reportTotals['price'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    <div class="flex justify-between">
        <a href="{{ route('clients.show', $siteVisit->client_id) }}"
           class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
            Back to Client
        </a>
                  <a href="{{ route('contacts.site-visits.show', [$siteVisit->client_id, $siteVisit->id]) }}"
             class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
            View Site Visit
        </a>
    </div>
</div>
@endsection
