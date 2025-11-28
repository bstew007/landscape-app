<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Estimate #{{ $estimate->id }} - Proposal</title>
    @include('estimates.print-templates._styles')
</head>
<body>
<div class="page">

@include('estimates.print-templates._header')

<!-- Document Title -->
<div class="document-title clearfix">
    <h1>Landscape Proposal</h1>
    <div class="subtitle">Estimate #{{ $estimate->id }} • {{ $estimate->title }}</div>
</div>

<!-- Client & Estimate Information -->
<div class="info-section clearfix">
    <div class="info-grid">
        <div class="info-col">
            <div class="info-label">Client</div>
            <div class="info-value">{{ $estimate->client->name }}</div>
            
            @if($estimate->property)
                <div class="info-label">Property</div>
                <div class="info-value">{{ $estimate->property->name }}</div>
                
                @if($estimate->property->address)
                    <div class="info-label">Location</div>
                    <div class="info-value">
                        {{ $estimate->property->address }}<br>
                        {{ $estimate->property->city }}, {{ $estimate->property->state }} {{ $estimate->property->postal_code }}
                    </div>
                @endif
            @endif
        </div>
        
        <div class="info-col">
            <div class="info-label">Proposal Date</div>
            <div class="info-value">{{ $estimate->created_at->format('F j, Y') }}</div>
            
            <div class="info-label">Status</div>
            <div class="info-value">{{ ucfirst($estimate->status) }}</div>
            
            @if($estimate->expires_at)
                <div class="info-label">Valid Until</div>
                <div class="info-value">{{ $estimate->expires_at->format('F j, Y') }}</div>
            @endif
        </div>
    </div>
</div>

<!-- Project Notes -->
@if($estimate->notes)
<div class="notes-section">
    <h3>Project Overview</h3>
    <p>{{ $estimate->notes }}</p>
</div>
@endif

<!-- Work Areas & Items -->
<h2>Materials & Scope</h2>

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

</body>
</html>
