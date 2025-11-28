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

<!-- Document Title -->
<div class="document-title clearfix">
    <h1>Landscape Estimate</h1>
    <div class="subtitle">Estimate #{{ $estimate->id }} • {{ $estimate->title }}</div>
    <span class="template-badge">Full Detail</span>
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
            <div class="info-label">Estimate Date</div>
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
        @if($area->description)
        <div class="notes-section" style="margin-bottom: 15px; background: #fffbeb; border-left-color: #f59e0b;">
            <h3 style="color: #78350f; font-size: 12px;">{{ $area->name }} - Work Area Notes</h3>
            <p style="color: #78350f;">{{ $area->description }}</p>
        </div>
        @endif
        
        <table>
            <thead>
                <tr class="area-header">
                    <th colspan="5">{{ $area->name }}</th>
                </tr>
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
                <tr class="area-total">
                    <td colspan="4" class="text-right"><strong>{{ $area->name }} Subtotal</strong></td>
                    <td class="text-right"><strong>${{ number_format($areaTotal, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
@endforeach

<!-- Summary Totals -->
<div style="margin-top: 40px;">
    <div class="totals-section">
        <table>
            <tr class="totals-row">
                <td class="totals-label">Subtotal</td>
                <td class="totals-value">${{ number_format($estimate->revenue_total, 2) }}</td>
            </tr>
            
            @if($estimate->tax_total > 0)
            <tr class="totals-row">
                <td class="totals-label">Tax</td>
                <td class="totals-value">${{ number_format($estimate->tax_total, 2) }}</td>
            </tr>
            @endif
            
            <tr class="grand-total">
                <td class="totals-label">TOTAL</td>
                <td class="totals-value">${{ number_format($estimate->grand_total, 2) }}</td>
            </tr>
        </table>
    </div>
</div>

<!-- Terms & Conditions -->
@if($estimate->terms)
<div class="notes-section clearfix" style="margin-top: 60px;">
    <h3>Terms & Conditions</h3>
    <p>{{ $estimate->terms }}</p>
</div>
@endif

<!-- Acceptance Section -->
<div class="acceptance-section">
    <h2>Acceptance & Authorization</h2>
    <div class="acceptance-checkbox">
        @php $company = \App\Models\CompanySetting::getSettings(); @endphp
        <input type="checkbox" style="width:18px; height:18px; margin-right:10px; vertical-align: middle;">
        <span style="font-weight: 600;">I accept this estimate as quoted and authorize {{ $company->company_name }} to proceed with the work described above.</span>
    </div>
    
    <p style="margin: 20px 0 10px 0;"><strong>Client Signature:</strong> <span class="signature-line"></span></p>
    <p style="margin: 10px 0;"><strong>Date:</strong> <span class="signature-line" style="width: 200px;"></span></p>
</div>

<!-- Footer -->
<div class="footer">
    @php $company = \App\Models\CompanySetting::getSettings(); @endphp
    <p><strong>{{ $company->company_name }}</strong></p>
    @if($company->phone || $company->email)
        <p>
            @if($company->phone) {{ $company->phone }} @endif
            @if($company->phone && $company->email) • @endif
            @if($company->email) {{ $company->email }} @endif
        </p>
    @endif
    <p style="margin-top: 8px;">Thank you for your business! This estimate is valid for 30 days.</p>
</div>

</div>
</body>
</html>
