<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Estimate #{{ $estimate->id }} - Labor Only</title>
    @include('estimates.print-templates._styles')
    <style>
        .work-area-header { background: #eff6ff; padding: 12px 15px; font-weight: 600; font-size: 16px; border-left: 4px solid #3b82f6; margin-bottom: 10px; }
        .grand-total { background: #3b82f6; color: white; font-size: 16px; padding: 15px; margin-top: 20px; }
        .empty-message { text-align: center; padding: 40px; color: #6b7280; font-style: italic; }
        .summary-box { background: #eff6ff; padding: 20px; border-left: 4px solid #3b82f6; margin-top: 20px; }
    </style>
</head>
<body>
<div class="page">

@include('estimates.print-templates._header')

<!-- Client Notes -->
@if($estimate->notes)
<div style="margin: 20px 0; padding: 20px; background: #f9fafb; border-left: 4px solid #3b82f6;">
    <h3 style="margin-top: 0; color: #1f2937;">Project Notes</h3>
    <p style="line-height: 1.6; white-space: pre-wrap;">{{ $estimate->notes }}</p>
</div>
@endif

<!-- Labor Items by Work Area -->
<h2>Labor Schedule</h2>

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
    <div style="margin-top: 30px;">
        <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 2px solid #f59e0b; border-radius: 8px; padding: 20px; margin-bottom: 15px;">
            <div style="overflow: hidden; padding: 4px 0;">
                <span style="float: left; font-size: 16px; font-weight: 600; color: #78350f;">Total Labor Hours:</span>
                <span style="float: right; font-size: 18px; font-weight: 700; color: #78350f;">{{ number_format($totalHours, 2) }} hrs</span>
            </div>
            <div style="overflow: hidden; padding: 4px 0; margin-top: 8px;">
                <span style="float: left; font-size: 16px; font-weight: 600; color: #78350f;">Average Rate:</span>
                <span style="float: right; font-size: 18px; font-weight: 700; color: #78350f;">${{ $totalHours > 0 ? number_format($totalLabor / $totalHours, 2) : '0.00' }}/hr</span>
            </div>
        </div>
        
        <div class="grand-total">
            <div style="overflow: hidden;">
                <span style="float: left; font-size: 22px; font-weight: 700;">TOTAL LABOR:</span>
                <span style="float: right; font-size: 28px; font-weight: 700;">${{ number_format($totalLabor, 2) }}</span>
            </div>
        </div>
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
