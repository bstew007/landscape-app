<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Estimate #{{ $estimate->id }} - Summary</title>
    <style>
        body { font-family: system-ui, sans-serif; padding: 40px; color: #1f2937; }
        .header { display: flex; justify-content: space-between; align-items: start; gap: 20px; border-bottom: 2px solid #e5e7eb; padding-bottom: 20px; margin-bottom: 30px; }
        .header-left { flex: 1; }
        .header-right { text-align: right; }
        h1 { margin: 0 0 10px 0; font-size: 28px; color: #111827; }
        .estimate-id { font-size: 14px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; }
        .template-badge { display: inline-block; padding: 4px 12px; background: #6b7280; color: white; border-radius: 4px; font-size: 12px; font-weight: 600; margin-top: 8px; }
        .client-info { margin: 20px 0; }
        .client-info p { margin: 5px 0; }
        .section-title { font-size: 18px; font-weight: 600; margin: 30px 0 15px 0; padding-bottom: 8px; border-bottom: 2px solid #6b7280; color: #6b7280; }
        .work-area { margin-bottom: 20px; padding: 20px; background: #f9fafb; border-left: 4px solid #6b7280; page-break-inside: avoid; }
        .work-area-name { font-size: 18px; font-weight: 600; color: #111827; margin-bottom: 10px; }
        .work-area-description { color: #6b7280; margin-bottom: 15px; line-height: 1.6; }
        .work-area-total { font-size: 20px; font-weight: 700; color: #059669; }
        .grand-total { background: #111827; color: white; font-size: 18px; padding: 20px; margin-top: 30px; }
        .notes { margin-top: 30px; padding: 20px; background: #f9fafb; border-left: 4px solid #3b82f6; }
        .notes h3 { margin-top: 0; color: #1f2937; }
        @media print {
            body { padding: 20px; }
        }
    </style>
</head>
<body>

<!-- Header -->
<div class="header">
    <div class="header-left">
        <p class="estimate-id">Estimate #{{ $estimate->id }}</p>
        <h1>{{ $estimate->title }}</h1>
        <span class="template-badge">SUMMARY</span>
    </div>
    <div class="header-right">
        <p><strong>Date:</strong> {{ $estimate->created_at->format('M j, Y') }}</p>
        <p><strong>Status:</strong> {{ ucfirst($estimate->status) }}</p>
        @if($estimate->expires_at)
            <p><strong>Expires:</strong> {{ $estimate->expires_at->format('M j, Y') }}</p>
        @endif
    </div>
</div>

<!-- Client Information -->
<div class="client-info">
    <p><strong>Client:</strong> {{ $estimate->client->name }}</p>
    @if($estimate->property)
        <p><strong>Property:</strong> {{ $estimate->property->name }}</p>
        @if($estimate->property->address)
            <p><strong>Address:</strong> {{ $estimate->property->address }}, {{ $estimate->property->city }}, {{ $estimate->property->state }} {{ $estimate->property->postal_code }}</p>
        @endif
    @endif
</div>

<!-- Client Notes -->
@if($estimate->notes)
<div class="notes" style="margin-top: 20px;">
    <h3>Project Notes</h3>
    <p style="line-height: 1.6; white-space: pre-wrap;">{{ $estimate->notes }}</p>
</div>
@endif

<!-- Work Area Summaries -->
<h2 class="section-title">Project Scope</h2>

@foreach($estimate->areas as $area)
    @php
        $areaItems = $itemsByArea->get($area->id, collect());
        if ($areaItems->isEmpty()) continue;
        
        $areaTotal = $areaItems->sum(function($item) {
            return $item->quantity * $item->unit_price;
        });
    @endphp
    
    <div class="work-area">
        <div class="work-area-name">{{ $area->name }}</div>
        @if($area->description)
            <div class="work-area-description">{{ $area->description }}</div>
        @endif
        <div class="work-area-total">${{ number_format($areaTotal, 2) }}</div>
    </div>
@endforeach

<!-- Grand Total -->
<div class="grand-total">
    <table style="width: 100%; margin: 0;">
        <tr>
            <td style="padding: 0; border: none; font-size: 18px;">Subtotal:</td>
            <td class="text-right" style="padding: 0; border: none; width: 200px; font-size: 18px;">${{ number_format($estimate->revenue_total, 2) }}</td>
        </tr>
        @if($estimate->tax_total > 0)
        <tr>
            <td style="padding: 5px 0 0 0; border: none; font-size: 16px;">Tax:</td>
            <td class="text-right" style="padding: 5px 0 0 0; border: none; font-size: 16px;">${{ number_format($estimate->tax_total, 2) }}</td>
        </tr>
        @endif
        <tr>
            <td style="padding: 10px 0 0 0; border: none; border-top: 1px solid rgba(255,255,255,0.3); font-size: 22px; font-weight: 700;">TOTAL:</td>
            <td class="text-right" style="padding: 10px 0 0 0; border: none; border-top: 1px solid rgba(255,255,255,0.3); font-size: 22px; font-weight: 700;">${{ number_format($estimate->grand_total, 2) }}</td>
        </tr>
    </table>
</div>

<!-- Terms & Conditions -->
@if($estimate->terms)
<div class="notes" style="border-left-color: #6b7280;">
    <h3>Terms & Conditions</h3>
    <p style="line-height: 1.6; white-space: pre-wrap;">{{ $estimate->terms }}</p>
</div>
@endif

</body>
</html>
