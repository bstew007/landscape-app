<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Estimate #{{ $estimate->id }} - Summary</title>
    @include('estimates.print-templates._styles')
    <style>
        .work-area { margin-bottom: 20px; padding: 20px; background: #f9fafb; border-left: 4px solid #3b82f6; page-break-inside: avoid; }
        .work-area-name { font-size: 18px; font-weight: 600; color: #111827; margin-bottom: 10px; }
        .work-area-description { color: #6b7280; margin-bottom: 15px; line-height: 1.6; }
        .work-area-total { font-size: 20px; font-weight: 700; color: #3b82f6; }
        .grand-total { background: #1f2937; color: white; font-size: 18px; padding: 20px; margin-top: 30px; }
        .notes { margin-top: 30px; padding: 20px; background: #f9fafb; border-left: 4px solid #3b82f6; }
        .notes h3 { margin-top: 0; color: #1f2937; }
    </style>
</head>
<body>
<div class="page">

@include('estimates.print-templates._header')

<!-- Client Notes -->
@if($estimate->notes)
<div class="notes" style="margin-top: 20px;">
    <h3>Project Notes</h3>
    <p style="line-height: 1.6; white-space: pre-wrap;">{{ $estimate->notes }}</p>
</div>
@endif

<!-- Work Area Summaries -->
<h2>Project Scope</h2>

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
                {{ $area->description }}
            </div>
        @endif
    </div>
@endforeach

<!-- Grand Total -->
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
