<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Estimate #{{ $estimate->id }} - Proposal</title>
    <style>
        body { font-family: system-ui, sans-serif; padding: 40px; color: #1f2937; }
        .header { display: flex; justify-content: space-between; align-items: start; gap: 20px; border-bottom: 2px solid #e5e7eb; padding-bottom: 20px; margin-bottom: 30px; }
        .header-left { flex: 1; }
        .header-right { text-align: right; }
        h1 { margin: 0 0 10px 0; font-size: 28px; color: #111827; }
        .estimate-id { font-size: 14px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; }
        .template-badge { display: inline-block; padding: 4px 12px; background: #8b5cf6; color: white; border-radius: 4px; font-size: 12px; font-weight: 600; margin-top: 8px; }
        .client-info { margin: 20px 0; }
        .client-info p { margin: 5px 0; }
        .section-title { font-size: 18px; font-weight: 600; margin: 30px 0 15px 0; padding-bottom: 8px; border-bottom: 2px solid #8b5cf6; color: #8b5cf6; }
        .work-area { margin-bottom: 30px; page-break-inside: avoid; }
        .work-area-header { background: #f5f3ff; padding: 12px 15px; font-weight: 600; font-size: 16px; border-left: 4px solid #8b5cf6; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        .work-area-price { font-size: 20px; color: #059669; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background: #f9fafb; font-weight: 600; font-size: 12px; text-transform: uppercase; color: #6b7280; }
        td { font-size: 14px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .item-type { font-size: 11px; text-transform: uppercase; color: #6b7280; background: #f3f4f6; padding: 2px 6px; border-radius: 3px; display: inline-block; }
        .item-type.material { background: #dbeafe; color: #1e40af; }
        .item-type.labor { background: #fef3c7; color: #92400e; }
        .grand-total { background: #8b5cf6; color: white; font-size: 18px; padding: 20px; margin-top: 30px; }
        .notes { margin-top: 30px; padding: 20px; background: #f9fafb; border-left: 4px solid #3b82f6; }
        .notes h3 { margin-top: 0; color: #1f2937; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 12px; color: #6b7280; }
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
        <span class="template-badge">PROPOSAL</span>
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
    <h3>Project Overview</h3>
    <p style="line-height: 1.6; white-space: pre-wrap;">{{ $estimate->notes }}</p>
</div>
@endif

<!-- Work Areas & Items -->
<h2 class="section-title">Materials & Scope</h2>

@foreach($estimate->areas as $area)
    @php
        $areaItems = $itemsByArea->get($area->id, collect());
        if ($areaItems->isEmpty()) continue;
        
        $areaTotal = $areaItems->sum(function($item) {
            return $item->quantity * $item->unit_price;
        });
        
        // Show only materials
        $materials = $areaItems->filter(fn($item) => $item->item_type === 'material');
    @endphp
    
    <div class="work-area">
        <div class="work-area-header">
            <span>{{ $area->name }}</span>
            <span class="work-area-price">${{ number_format($areaTotal, 2) }}</span>
        </div>
        
        @if($area->description)
        <div style="background: #fffbeb; padding: 10px 15px; margin-bottom: 10px; border-left: 3px solid #f59e0b; font-size: 14px; color: #78350f; line-height: 1.5;">
            <strong>Work Area Notes:</strong> {{ $area->description }}
        </div>
        @endif
        
        @if($materials->isNotEmpty())
        <div style="margin-bottom: 20px;">
            <h4 style="margin: 10px 0 5px 0; font-size: 14px; text-transform: uppercase; color: #6b7280; font-weight: 600;">Materials Included</h4>
            <table>
                <thead>
                    <tr>
                        <th>Material</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-center">Unit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($materials as $item)
                        <tr>
                            <td>
                                {{ $item->name }}
                                @if($item->description)
                                    <br><small style="color: #6b7280;">{{ $item->description }}</small>
                                @endif
                            </td>
                            <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                            <td class="text-center">{{ $item->unit }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p style="padding: 20px; text-align: center; color: #6b7280; font-style: italic;">Labor only - no materials for this area</p>
        @endif
    </div>
@endforeach

<!-- Grand Total -->
<div class="grand-total">
    <table style="width: 100%; margin: 0;">
        <tr>
            <td style="padding: 0; border: none; font-size: 18px;">Project Subtotal:</td>
            <td class="text-right" style="padding: 0; border: none; width: 200px; font-size: 18px;">${{ number_format($estimate->revenue_total, 2) }}</td>
        </tr>
        @if($estimate->tax_total > 0)
        <tr>
            <td style="padding: 5px 0 0 0; border: none; font-size: 16px;">Tax:</td>
            <td class="text-right" style="padding: 5px 0 0 0; border: none; font-size: 16px;">${{ number_format($estimate->tax_total, 2) }}</td>
        </tr>
        @endif
        <tr>
            <td style="padding: 10px 0 0 0; border: none; border-top: 1px solid rgba(255,255,255,0.3); font-size: 22px; font-weight: 700;">TOTAL INVESTMENT:</td>
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

<!-- Footer -->
<div class="footer">
    <p>Thank you for considering our proposal!</p>
    <p>We look forward to working with you on this project.</p>
</div>

</body>
</html>
