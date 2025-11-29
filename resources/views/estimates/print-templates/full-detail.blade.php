<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Estimate #{{ $estimate->id }} - Full Detail</title>
    @include('estimates.print-templates._styles')
</head>
<body>
<div class="page">

@include('estimates.print-templates._header')

<!-- Project Notes -->
@if($estimate->notes)
<div class="notes-section">
    <h3>Project Overview</h3>
    <p>{{ $estimate->notes }}</p>
</div>
@endif

<!-- Work Areas & Line Items -->
<h2>Work Areas & Detailed Pricing</h2>

@foreach($estimate->areas as $area)
    @php
        $areaItems = $itemsByArea->get($area->id, collect());
        if ($areaItems->isEmpty()) continue;
        
        $areaTotal = $areaItems->sum(function($item) {
            return $item->quantity * $item->unit_price;
        });
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
                        <td class="font-medium">
                            {{ $item->name }}
                            @if($item->description)
                                <br><small style="color: #6b7280; font-weight: normal;">{{ $item->description }}</small>
                            @endif
                        </td>
                        <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                        <td class="text-center">{{ $item->unit }}</td>
                        <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right font-medium">${{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endforeach

<!-- Summary Totals -->
<div style="margin-top: 40px;">
    <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 2px solid #f59e0b; border-radius: 8px; padding: 20px; margin-bottom: 15px;">
        <div style="overflow: hidden; padding: 8px 0;">
            <span style="float: left; font-size: 18px; font-weight: 700; color: #78350f;">Project Subtotal:</span>
            <span style="float: right; font-size: 20px; font-weight: 700; color: #78350f;">${{ number_format($estimate->revenue_total, 2) }}</span>
        </div>
    </div>
    
    @if($estimate->tax_total > 0)
    <div style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); border: 2px solid #3b82f6; border-radius: 8px; padding: 20px; margin-bottom: 15px;">
        <div style="overflow: hidden; padding: 8px 0;">
            <span style="float: left; font-size: 18px; font-weight: 700; color: #1e40af;">Tax:</span>
            <span style="float: right; font-size: 20px; font-weight: 700; color: #1e40af;">${{ number_format($estimate->tax_total, 2) }}</span>
        </div>
    </div>
    @endif
    
    <div class="grand-total">
        <div style="overflow: hidden;">
            <span style="float: left; font-size: 22px; font-weight: 700;">TOTAL INVESTMENT:</span>
            <span style="float: right; font-size: 28px; font-weight: 700;">${{ number_format($estimate->grand_total, 2) }}</span>
        </div>
    </div>
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

</div>
</body>
</html>
