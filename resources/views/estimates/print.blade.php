<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Estimate #{{ $estimate->id }}</title>
    <style>
        body { font-family: system-ui, sans-serif; padding: 20px; color: #1f2937; font-size: 13px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; margin-bottom: 16px; }
        .header-left { flex: 1; }
        .header-right { text-align: right; }
        .logo { width: 140px; height: auto; object-fit: contain; display: block; margin-left: auto; }
        h1 { font-size: 24px; margin: 0 0 4px 0; }
        h2 { font-size: 16px; margin: 20px 0 8px 0; border-bottom: 2px solid #1f2937; padding-bottom: 4px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { padding: 6px 8px; border: 1px solid #d1d5db; text-align: left; }
        th { background: #f3f4f6; font-size: 11px; text-transform: uppercase; letter-spacing: .05em; font-weight: 600; }
        
        .info-table { width: 100%; }
        .info-table td { padding: 5px 8px; }
        .info-table td:first-child { font-weight: 600; width: 35%; background: #f9fafb; }
        
        .two-column-layout { display: flex; gap: 16px; margin-bottom: 12px; }
        .two-column-layout > div { flex: 1; }
        
        .total { font-weight: 700; text-align: right; font-size: 1.1rem; margin-top: 8px; }
        .page-break { page-break-before: always; margin-top: 24px; }
        
        @media print {
            body { padding: 10px; }
            .page-break { margin-top: 16px; }
        }
    </style>
</head>
<body>
<!-- Header with Logo -->
<div class="header">
    <div class="header-left">
        <h1>Estimate #{{ $estimate->id }}</h1>
        <p style="margin: 0; font-size: 15px; color: #6b7280;">{{ $estimate->title }}</p>
    </div>
    <div class="header-right">
        <img src="{{ public_path('images/logo.png') }}" alt="{{ config('app.name') }} Logo" class="logo">
    </div>
</div>

<!-- Two-Column Layout for Company and Client Info -->
<div class="two-column-layout">
    <!-- Company Information -->
    <div>
        <table class="info-table">
            <tr>
                <td colspan="2" style="background: #1f2937; color: white; font-weight: 700; text-align: center;">Company Information</td>
            </tr>
            <tr>
                <td>Company Name</td>
                <td>{{ config('app.name') }}</td>
            </tr>
            <tr>
                <td>Address</td>
                <td>{{ env('COMPANY_ADDRESS', '123 Business St') }}</td>
            </tr>
            <tr>
                <td>City, State ZIP</td>
                <td>{{ env('COMPANY_CITY_STATE_ZIP', 'City, ST 12345') }}</td>
            </tr>
            <tr>
                <td>Phone</td>
                <td>{{ env('COMPANY_PHONE', '(555) 123-4567') }}</td>
            </tr>
            <tr>
                <td>Email</td>
                <td>{{ env('COMPANY_EMAIL', 'info@company.com') }}</td>
            </tr>
        </table>
    </div>
    
    <!-- Client Information -->
    <div>
        <table class="info-table">
            <tr>
                <td colspan="2" style="background: #1f2937; color: white; font-weight: 700; text-align: center;">Client Information</td>
            </tr>
            <tr>
                <td>Client Name</td>
                <td>{{ $estimate->client->name }}</td>
            </tr>
            <tr>
                <td>Property</td>
                <td>{{ $estimate->property->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Status</td>
                <td>{{ ucfirst($estimate->status) }}</td>
            </tr>
            <tr>
                <td>Created Date</td>
                <td>{{ $estimate->created_at->format('M j, Y') }}</td>
            </tr>
            <tr>
                <td>Expires</td>
                <td>{{ optional($estimate->expires_at)->format('M j, Y') ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>
</div>

@php
    $scopeSummaries = $scopeSummaries ?? [];
@endphp

<!-- Scope & Terms -->
@if($estimate->notes || $estimate->terms)
<section style="margin-bottom: 12px;">
    <h2>Scope & Terms</h2>
    @if($estimate->notes)
    <p style="margin: 6px 0;">{{ $estimate->notes }}</p>
    @endif
    @if($estimate->terms)
    <p style="margin: 6px 0;"><strong>Terms:</strong> {{ $estimate->terms }}</p>
    @endif
</section>
@endif

<!-- Line Items -->
<section style="margin-bottom: 12px;">
    <h2>Line Items</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 50%;">Description</th>
                <th style="width: 12%; text-align: center;">Qty</th>
                <th style="width: 19%; text-align: right;">Price</th>
                <th style="width: 19%; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($estimate->line_items ?? [] as $item)
            <tr>
                <td>{{ $item['label'] ?? 'Item' }}</td>
                <td style="text-align: center;">{{ $item['qty'] ?? 1 }}</td>
                <td style="text-align: right;">${{ number_format($item['price'] ?? 0, 2) }}</td>
                <td style="text-align: right;">${{ number_format(($item['qty'] ?? 1) * ($item['price'] ?? 0), 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="4" style="text-align: center; color: #6b7280; font-size: 12px;">No line items yet.</td></tr>
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
            <h3 style="margin: 12px 0 6px 0; font-size: 14px; font-weight: 600;">{{ $summary['title'] }}</h3>
            @if (!empty($summary['measurements']))
                <table style="margin-bottom: 8px;">
                    <thead>
                        <tr>
                            <th style="width: 60%;">Measurement</th>
                            <th style="width: 40%;">Value</th>
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
                <table style="margin-bottom: 8px;">
                    <thead>
                        <tr>
                            <th style="width: 45%;">Material</th>
                            <th style="width: 20%; text-align: center;">Quantity</th>
                            <th style="width: 35%;">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($summary['materials'] as $material)
                            <tr>
                                <td>{{ $material['label'] }}</td>
                                <td style="text-align: center;">{{ $material['value'] }}</td>
                                <td>{{ $material['meta'] ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @endforeach
    </section>
@endif

<!-- Acceptance -->
<section style="margin-top: 20px;">
    <h2>Acceptance</h2>
    <p style="margin: 6px 0 10px 0; font-size: 12px;">By signing below you are approving this estimate and authorizing {{ config('app.name') }} to schedule the work described.</p>
    <table style="border: 1px solid #d1d5db;">
        <tr>
            <td style="padding: 10px; border: none;">
                <input type="checkbox" style="width: 16px; height: 16px; margin-right: 6px; vertical-align: middle;"> I accept this estimate as quoted.
            </td>
        </tr>
        <tr>
            <td style="padding: 10px 10px 6px 10px; border: none; border-top: 1px solid #e5e7eb;">
                <strong>Client Signature:</strong> ___________________________________________
            </td>
        </tr>
        <tr>
            <td style="padding: 6px 10px 10px 10px; border: none;">
                <strong>Date:</strong> ______________________
            </td>
        </tr>
    </table>
</section>
</body>
</html>
