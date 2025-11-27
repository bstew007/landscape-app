<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Estimate #{{ $estimate->id }} - Labor Only</title>
    <style>
        body { font-family: system-ui, sans-serif; padding: 40px; color: #1f2937; }
        .header { display: flex; justify-content: space-between; align-items: start; gap: 20px; border-bottom: 2px solid #e5e7eb; padding-bottom: 20px; margin-bottom: 30px; }
        .header-left { flex: 1; }
        .header-right { text-align: right; }
        h1 { margin: 0 0 10px 0; font-size: 28px; color: #111827; }
        .estimate-id { font-size: 14px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; }
        .template-badge { display: inline-block; padding: 4px 12px; background: #f59e0b; color: white; border-radius: 4px; font-size: 12px; font-weight: 600; margin-top: 8px; }
        .client-info { margin: 20px 0; }
        .client-info p { margin: 5px 0; }
        .section-title { font-size: 18px; font-weight: 600; margin: 30px 0 15px 0; padding-bottom: 8px; border-bottom: 2px solid #f59e0b; color: #f59e0b; }
        .work-area { margin-bottom: 30px; page-break-inside: avoid; }
        .work-area-header { background: #fffbeb; padding: 12px 15px; font-weight: 600; font-size: 16px; border-left: 4px solid #f59e0b; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background: #f9fafb; font-weight: 600; font-size: 12px; text-transform: uppercase; color: #6b7280; }
        td { font-size: 14px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { background: #f9fafb; font-weight: 600; }
        .grand-total { background: #f59e0b; color: white; font-size: 16px; padding: 15px; margin-top: 20px; }
        .empty-message { text-align: center; padding: 40px; color: #6b7280; font-style: italic; }
        .summary-box { background: #fffbeb; padding: 20px; border-left: 4px solid #f59e0b; margin-top: 20px; }
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
        <span class="template-badge">LABOR ONLY</span>
    </div>
    <div class="header-right">
        <p><strong>Date:</strong> {{ $estimate->created_at->format('M j, Y') }}</p>
        <p><strong>Status:</strong> {{ ucfirst($estimate->status) }}</p>
    </div>
</div>

<!-- Client Information -->
<div class="client-info">
    <p><strong>Client:</strong> {{ $estimate->client->name }}</p>
    @if($estimate->property)
        <p><strong>Property:</strong> {{ $estimate->property->name }}</p>
    @endif
</div>

<!-- Client Notes -->
@if($estimate->notes)
<div style="margin: 20px 0; padding: 20px; background: #f9fafb; border-left: 4px solid #f59e0b;">
    <h3 style="margin-top: 0; color: #1f2937;">Project Notes</h3>
    <p style="line-height: 1.6; white-space: pre-wrap;">{{ $estimate->notes }}</p>
</div>
@endif

<!-- Labor Items by Work Area -->
<h2 class="section-title">Labor Schedule</h2>

@php
    $hasLabor = false;
    $totalLabor = 0;
    $totalHours = 0;
@endphp

@foreach($estimate->areas as $area)
    @php
        $areaItems = $itemsByArea->get($area->id, collect());
        if ($areaItems->isEmpty()) continue;
        
        $areaTotal = $areaItems->sum(function($item) {
            return $item->quantity * $item->unit_price;
        });
        
        $areaHours = $areaItems->sum('quantity');
        
        $totalLabor += $areaTotal;
        $totalHours += $areaHours;
        $hasLabor = true;
    @endphp
    
    <div class="work-area">
        <div class="work-area-header">{{ $area->name }}</div>
        
        @if($area->description)
        <div style="background: #eff6ff; padding: 10px 15px; margin-bottom: 10px; border-left: 3px solid #3b82f6; font-size: 14px; color: #1e3a8a; line-height: 1.5;">
            <strong>Work Area Notes:</strong> {{ $area->description }}
        </div>
        @endif
        
        <table>
            <thead>
                <tr>
                    <th>Labor Type</th>
                    <th class="text-center">Hours</th>
                    <th class="text-right">Rate</th>
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
                        <td class="text-center">{{ number_format($item->quantity, 2) }} hrs</td>
                        <td class="text-right">${{ number_format($item->unit_price, 2) }}/hr</td>
                        <td class="text-right">${{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td class="text-right">{{ $area->name }} Subtotal:</td>
                    <td class="text-center">{{ number_format($areaHours, 2) }} hrs</td>
                    <td></td>
                    <td class="text-right">${{ number_format($areaTotal, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
@endforeach

@if(!$hasLabor)
    <div class="empty-message">
        <p>No labor items found in this estimate.</p>
    </div>
@else
    <!-- Labor Summary -->
    <div class="summary-box">
        <table style="width: 100%; margin: 0;">
            <tr>
                <td style="padding: 0; border: none;"><strong>Total Labor Hours:</strong></td>
                <td class="text-right" style="padding: 0; border: none; width: 150px;">{{ number_format($totalHours, 2) }} hrs</td>
            </tr>
            <tr>
                <td style="padding: 0; border: none;"><strong>Average Rate:</strong></td>
                <td class="text-right" style="padding: 0; border: none;">${{ $totalHours > 0 ? number_format($totalLabor / $totalHours, 2) : '0.00' }}/hr</td>
            </tr>
        </table>
    </div>
    
    <!-- Total -->
    <div class="grand-total">
        <table style="width: 100%; margin: 0;">
            <tr>
                <td class="text-right" style="padding: 0; border: none;">TOTAL LABOR:</td>
                <td class="text-right" style="padding: 0; border: none; width: 150px;">${{ number_format($totalLabor, 2) }}</td>
            </tr>
        </table>
    </div>
@endif

</body>
</html>
