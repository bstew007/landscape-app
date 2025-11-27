<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Estimate #{{ $estimate->id }} - Full Detail</title>
    <style>
        body { font-family: system-ui, sans-serif; padding: 40px; color: #1f2937; }
        .header { display: flex; justify-content: space-between; align-items: start; gap: 20px; border-bottom: 2px solid #e5e7eb; padding-bottom: 20px; margin-bottom: 30px; }
        .header-left { flex: 1; }
        .header-right { text-align: right; }
        h1 { margin: 0 0 10px 0; font-size: 28px; color: #111827; }
        .estimate-id { font-size: 14px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; }
        .client-info { margin: 20px 0; }
        .client-info p { margin: 5px 0; }
        .section-title { font-size: 18px; font-weight: 600; margin: 30px 0 15px 0; padding-bottom: 8px; border-bottom: 2px solid #059669; color: #059669; }
        .work-area { margin-bottom: 30px; page-break-inside: avoid; }
        .work-area-header { background: #f3f4f6; padding: 12px 15px; font-weight: 600; font-size: 16px; border-left: 4px solid #059669; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background: #f9fafb; font-weight: 600; font-size: 12px; text-transform: uppercase; color: #6b7280; }
        td { font-size: 14px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { background: #f9fafb; font-weight: 600; }
        .grand-total { background: #059669; color: white; font-size: 16px; padding: 15px; margin-top: 20px; }
        .notes { margin-top: 30px; padding: 20px; background: #f9fafb; border-left: 4px solid #3b82f6; }
        .notes h3 { margin-top: 0; color: #1f2937; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 12px; color: #6b7280; }
        @media print {
            body { padding: 20px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<!-- Header -->
<div class="header">
    <div class="header-left">
        <p class="estimate-id">Estimate #{{ $estimate->id }}</p>
        <h1>{{ $estimate->title }}</h1>
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

<!-- Work Areas & Line Items -->
<h2 class="section-title">Work Areas & Pricing</h2>

@foreach($estimate->areas as $area)
    @php
        $areaItems = $itemsByArea->get($area->id, collect());
        if ($areaItems->isEmpty()) continue;
        
        $areaTotal = $areaItems->sum(function($item) {
            return $item->quantity * $item->unit_price;
        });
    @endphp
    
    <div class="work-area">
        <div class="work-area-header">{{ $area->name }}</div>
        
        @if($area->description)
        <div style="background: #fffbeb; padding: 10px 15px; margin-bottom: 10px; border-left: 3px solid #f59e0b; font-size: 14px; color: #78350f; line-height: 1.5;">
            <strong>Work Area Notes:</strong> {{ $area->description }}
        </div>
        @endif
        
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-center">Qty</th>
                    <th class="text-center">Unit</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($areaItems as $item)
                    <tr>
                        <td>
                            {{ $item->name }}
                            @if($item->description)
                                <br><small style="color: #6b7280;">{{ $item->description }}</small>
                            @endif
                        </td>
                        <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                        <td class="text-center">{{ $item->unit }}</td>
                        <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right">${{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="4" class="text-right">{{ $area->name }} Subtotal:</td>
                    <td class="text-right">${{ number_format($areaTotal, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
@endforeach

<!-- Summary Totals -->
<table style="margin-top: 30px;">
    <tr>
        <td class="text-right" style="border: none; padding-right: 20px;"><strong>Subtotal:</strong></td>
        <td class="text-right" style="border: none; width: 150px;">${{ number_format($estimate->revenue_total, 2) }}</td>
    </tr>
    @if($estimate->tax_total > 0)
    <tr>
        <td class="text-right" style="border: none; padding-right: 20px;"><strong>Tax:</strong></td>
        <td class="text-right" style="border: none;">${{ number_format($estimate->tax_total, 2) }}</td>
    </tr>
    @endif
    <tr class="grand-total">
        <td class="text-right" style="padding-right: 20px; border: none;">TOTAL:</td>
        <td class="text-right" style="border: none;">${{ number_format($estimate->grand_total, 2) }}</td>
    </tr>
</table>

<!-- Terms & Conditions -->
@if($estimate->terms)
<div class="notes" style="border-left-color: #6b7280;">
    <h3>Terms & Conditions</h3>
    <p style="line-height: 1.6; white-space: pre-wrap;">{{ $estimate->terms }}</p>
</div>
@endif

<!-- Footer -->
<div class="footer">
    <p>Thank you for your business!</p>
    <p>This estimate is valid for 30 days from the date above.</p>
</div>

</body>
</html>
