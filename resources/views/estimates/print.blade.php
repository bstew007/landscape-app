<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Estimate #{{ $estimate->id }}</title>
    <style>
        body { font-family: system-ui, sans-serif; padding: 40px; color: #1f2937; }
        .header { display: flex; justify-content: space-between; align-items: center; gap: 20px; border-bottom: 1px solid #e5e7eb; padding-bottom: 20px; margin-bottom: 20px; }
        .header-left { flex: 1; }
        .header-right { text-align: right; }
        .logo { width: 160px; height: auto; object-fit: contain; display: block; margin-left: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { padding: 8px; border: 1px solid #e5e7eb; }
        th { background: #f9fafb; font-size: 12px; text-transform: uppercase; letter-spacing: .05em; }
        .total { font-weight: 700; text-align: right; font-size: 1.25rem; }
        .page-break { page-break-before: always; margin-top: 32px; }
    </style>
</head>
<body>
<div class="header">
    <div class="header-left">
        <h1>Estimate #{{ $estimate->id }}</h1>
        <p>{{ $estimate->title }}</p>
    </div>
    <div class="header-right">
        <img src="{{ public_path('images/logo.png') }}" alt="{{ config('app.name') }} Logo" class="logo">
        <p><strong>Status:</strong> {{ ucfirst($estimate->status) }}</p>
        <p><strong>Created:</strong> {{ $estimate->created_at->format('M j, Y') }}</p>
        <p><strong>Expires:</strong> {{ optional($estimate->expires_at)->format('M j, Y') ?? 'N/A' }}</p>
    </div>
</div>

<p><strong>Client:</strong> {{ $estimate->client->name }}</p>
<p><strong>Property:</strong> {{ $estimate->property->name ?? 'N/A' }}</p>
<p><strong>Linked Visit:</strong> {{ optional($estimate->siteVisit)->visit_date?->format('M j, Y') ?? 'N/A' }}</p>

@php
    $scopeSummaries = $scopeSummaries ?? [];
@endphp

<section>
    <h2>Scope & Terms</h2>
    <p>{{ $estimate->notes }}</p>
    <p><strong>Terms:</strong> {{ $estimate->terms }}</p>
</section>

<section>
    <h2>Line Items</h2>
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($estimate->line_items ?? [] as $item)
            <tr>
                <td>{{ $item['label'] ?? 'Item' }}</td>
                <td>{{ $item['qty'] ?? 1 }}</td>
                <td>${{ number_format($item['price'] ?? 0, 2) }}</td>
                <td>${{ number_format(($item['qty'] ?? 1) * ($item['price'] ?? 0), 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-sm text-gray-500">No line items yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    <p class="total">
        Total: ${{ number_format($estimate->total ?? 0, 2) }}
    </p>
</section>

@if (!empty($scopeSummaries))
    <section class="page-break">
        <h2>Estimate Details</h2>
        @foreach ($scopeSummaries as $summary)
            <h3 style="margin-top:16px;">{{ $summary['title'] }}</h3>
            @if (!empty($summary['measurements']))
                <table>
                    <thead>
                        <tr>
                            <th>Measurement</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($summary['measurements'] as $measurement)
                            <tr>
                                <td>{{ $measurement['label'] }}</td>
                                <td>{{ $measurement['value'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if (!empty($summary['materials']))
                <table>
                    <thead>
                        <tr>
                            <th>Material</th>
                            <th>Quantity</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($summary['materials'] as $material)
                            <tr>
                                <td>{{ $material['label'] }}</td>
                                <td>{{ $material['value'] }}</td>
                                <td>{{ $material['meta'] ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @endforeach
    </section>
@endif

<section style="margin-top:32px;">
    <h2>Acceptance</h2>
    <p style="margin-bottom:16px;">By signing below you are approving this estimate and authorizing {{ config('app.name') }} to schedule the work described.</p>
    <table style="width:100%; border: none;">
        <tr>
            <td style="padding:12px 0; border-bottom:1px solid #d1d5db;">
                <input type="checkbox" style="width:18px; height:18px; margin-right:8px;"> I accept this estimate as quoted.
            </td>
        </tr>
        <tr>
            <td style="padding-top:20px;">
                <strong>Client Signature:</strong> ___________________________________________
            </td>
        </tr>
        <tr>
            <td style="padding-top:12px;">
                <strong>Date:</strong> ______________________
            </td>
        </tr>
    </table>
</section>
</body>
</html>
