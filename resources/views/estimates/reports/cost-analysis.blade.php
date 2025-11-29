<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cost Analysis Report - Estimate #{{ $estimate->id }}</title>
    @include('estimates.print-templates._styles')
    <style>
        .metric-box {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 2px solid #3b82f6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 12px;
        }
        .metric-label {
            font-size: 12px;
            color: #1e40af;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .metric-value {
            font-size: 24px;
            color: #1e40af;
            font-weight: 700;
            margin-top: 4px;
        }
        .section-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin-bottom: 15px;
        }
        .cost-breakdown-table td {
            padding: 8px 10px;
        }
        .total-row {
            background: #f3f4f6;
            font-weight: 700;
        }
    </style>
</head>
<body>
<div class="page">

@include('estimates.print-templates._header')

<!-- Report Title -->
<div style="text-align: center; margin-bottom: 20px; padding: 20px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; border-radius: 8px;">
    <h1 style="margin: 0; font-size: 28px; font-weight: 700;">Cost Analysis Report</h1>
    <p style="margin: 8px 0 0 0; font-size: 14px; opacity: 0.9;">Comprehensive breakdown of costs, pricing, and profit margins</p>
</div>

<!-- Executive Summary Metrics -->
<h2>Executive Summary</h2>
<div style="display: table; width: 100%; table-layout: fixed; margin-bottom: 20px;">
    <div style="display: table-cell; width: 32%; padding-right: 2%;">
        <div class="metric-box">
            <div class="metric-label">Total Revenue</div>
            <div class="metric-value">${{ number_format($estimate->revenue_total ?? 0, 2) }}</div>
        </div>
    </div>
    <div style="display: table-cell; width: 32%; padding: 0 1%;">
        <div class="metric-box">
            <div class="metric-label">Total Cost</div>
            <div class="metric-value">${{ number_format($totalCost, 2) }}</div>
        </div>
    </div>
    <div style="display: table-cell; width: 32%; padding-left: 2%;">
        <div class="metric-box" style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); border-color: #10b981;">
            <div class="metric-label" style="color: #065f46;">Gross Profit</div>
            <div class="metric-value" style="color: #065f46;">${{ number_format($grossProfit, 2) }}</div>
            <div style="font-size: 14px; color: #065f46; font-weight: 600; margin-top: 4px;">
                {{ $profitMargin }}% Margin
            </div>
        </div>
    </div>
</div>

<!-- Cost Breakdown by Category -->
<h2>Cost Breakdown by Category</h2>
<table class="cost-breakdown-table">
    <thead>
        <tr>
            <th style="width: 40%;">Category</th>
            <th class="text-right" style="width: 20%;">Cost</th>
            <th class="text-right" style="width: 20%;">Revenue</th>
            <th class="text-right" style="width: 20%;">Profit</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Materials</strong></td>
            <td class="text-right">${{ number_format($materialsCost, 2) }}</td>
            <td class="text-right">${{ number_format($materialsRevenue, 2) }}</td>
            <td class="text-right" style="color: #10b981; font-weight: 600;">${{ number_format($materialsRevenue - $materialsCost, 2) }}</td>
        </tr>
        <tr>
            <td><strong>Labor</strong></td>
            <td class="text-right">${{ number_format($laborCost, 2) }}</td>
            <td class="text-right">${{ number_format($laborRevenue, 2) }}</td>
            <td class="text-right" style="color: #10b981; font-weight: 600;">${{ number_format($laborRevenue - $laborCost, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td><strong>TOTAL</strong></td>
            <td class="text-right">${{ number_format($totalCost, 2) }}</td>
            <td class="text-right">${{ number_format($estimate->revenue_total ?? 0, 2) }}</td>
            <td class="text-right" style="color: #10b981;">${{ number_format($grossProfit, 2) }}</td>
        </tr>
    </tbody>
</table>

<!-- Work Area Analysis -->
<h2 style="margin-top: 30px;">Work Area Analysis</h2>
@foreach($estimate->areas as $area)
    @php
        $areaItems = $estimate->items->where('area_id', $area->id);
        if ($areaItems->isEmpty()) continue;
        
        $areaCost = $areaItems->sum(function($item) {
            return $item->quantity * ($item->unit_cost ?? 0);
        });
        $areaRevenue = $areaItems->sum(function($item) {
            return $item->quantity * $item->unit_price;
        });
        $areaProfit = $areaRevenue - $areaCost;
        $areaMargin = $areaRevenue > 0 ? round(($areaProfit / $areaRevenue) * 100, 1) : 0;
    @endphp
    
    <div class="section-box">
        <div style="overflow: hidden; margin-bottom: 10px;">
            <h3 style="float: left; margin: 0; font-size: 16px; color: #1f2937;">{{ $area->name }}</h3>
            <span style="float: right; font-size: 14px; color: #3b82f6; font-weight: 600;">{{ $areaMargin }}% Margin</span>
        </div>
        
        <table style="width: 100%; font-size: 12px; margin: 0;">
            <tr>
                <td style="padding: 4px 0; border: none;"><strong>Cost:</strong></td>
                <td style="padding: 4px 0; border: none; text-align: right;">${{ number_format($areaCost, 2) }}</td>
                <td style="padding: 4px 0; border: none; width: 20px;"></td>
                <td style="padding: 4px 0; border: none;"><strong>Revenue:</strong></td>
                <td style="padding: 4px 0; border: none; text-align: right;">${{ number_format($areaRevenue, 2) }}</td>
            </tr>
            <tr>
                <td style="padding: 4px 0; border: none;"><strong>Items:</strong></td>
                <td style="padding: 4px 0; border: none; text-align: right;">{{ $areaItems->count() }}</td>
                <td style="padding: 4px 0; border: none;"></td>
                <td style="padding: 4px 0; border: none;"><strong>Profit:</strong></td>
                <td style="padding: 4px 0; border: none; text-align: right; color: #10b981; font-weight: 600;">${{ number_format($areaProfit, 2) }}</td>
            </tr>
        </table>
        
        <!-- Item Details -->
        <details style="margin-top: 10px;">
            <summary style="cursor: pointer; font-size: 12px; color: #3b82f6; font-weight: 600;">View Item Details</summary>
            <table style="width: 100%; margin-top: 8px; font-size: 11px;">
                <thead>
                    <tr style="background: #f3f4f6;">
                        <th style="padding: 4px 6px; text-align: left;">Item</th>
                        <th style="padding: 4px 6px; text-align: center;">Qty</th>
                        <th style="padding: 4px 6px; text-align: right;">Cost</th>
                        <th style="padding: 4px 6px; text-align: right;">Price</th>
                        <th style="padding: 4px 6px; text-align: right;">Profit</th>
                        <th style="padding: 4px 6px; text-align: right;">Margin %</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($areaItems as $item)
                        @php
                            $itemCost = $item->quantity * ($item->unit_cost ?? 0);
                            $itemRevenue = $item->quantity * $item->unit_price;
                            $itemProfit = $itemRevenue - $itemCost;
                            $itemMargin = $itemRevenue > 0 ? round(($itemProfit / $itemRevenue) * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td style="padding: 4px 6px; border-bottom: 1px solid #e5e7eb;">{{ $item->name }}</td>
                            <td style="padding: 4px 6px; border-bottom: 1px solid #e5e7eb; text-align: center;">{{ number_format($item->quantity, 2) }}</td>
                            <td style="padding: 4px 6px; border-bottom: 1px solid #e5e7eb; text-align: right;">${{ number_format($itemCost, 2) }}</td>
                            <td style="padding: 4px 6px; border-bottom: 1px solid #e5e7eb; text-align: right;">${{ number_format($itemRevenue, 2) }}</td>
                            <td style="padding: 4px 6px; border-bottom: 1px solid #e5e7eb; text-align: right; color: {{ $itemProfit >= 0 ? '#10b981' : '#ef4444' }};">${{ number_format($itemProfit, 2) }}</td>
                            <td style="padding: 4px 6px; border-bottom: 1px solid #e5e7eb; text-align: right;">{{ $itemMargin }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </details>
    </div>
@endforeach

<!-- Recommendations -->
<h2 style="margin-top: 30px;">Recommendations</h2>
<div style="background: #fffbeb; border-left: 4px solid #f59e0b; padding: 15px; margin-bottom: 20px;">
    <h3 style="margin: 0 0 10px 0; color: #78350f; font-size: 14px;">Cost Optimization Opportunities</h3>
    <ul style="margin: 0; padding-left: 20px; color: #78350f; font-size: 13px; line-height: 1.6;">
        @if($profitMargin < 20)
            <li>Current profit margin ({{ $profitMargin }}%) is below recommended 20-30% threshold</li>
        @endif
        @if($materialsCost > 0 && ($materialsRevenue - $materialsCost) / $materialsCost * 100 < 50)
            <li>Consider increasing materials markup to improve margins</li>
        @endif
        @if($laborCost > 0 && ($laborRevenue - $laborCost) / $laborCost * 100 < 30)
            <li>Review labor rates to ensure adequate coverage of overhead and profit</li>
        @endif
        <li>Target profit margin for landscape projects: 20-30%</li>
        <li>Recommended materials markup: 50-100%</li>
        <li>Recommended labor markup: 30-50% over base cost</li>
    </ul>
</div>

<!-- Footer -->
<div class="footer">
    <p><strong>Report Generated:</strong> {{ now()->format('F j, Y g:i A') }}</p>
    <p>This report is confidential and for internal use only.</p>
</div>

</div>
</body>
</html>
