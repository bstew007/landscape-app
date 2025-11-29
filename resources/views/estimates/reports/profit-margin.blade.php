<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Profit Margin Analysis - Estimate #{{ $estimate->id }}</title>
    @include('estimates.print-templates._styles')
    <style>
        .profit-box {
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 12px;
        }
        .profit-box.positive {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            border: 2px solid #10b981;
        }
        .profit-box.warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px solid #f59e0b;
        }
        .profit-box.negative {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border: 2px solid #ef4444;
        }
        .metric-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .metric-value {
            font-size: 24px;
            font-weight: 700;
            margin-top: 4px;
        }
        .profit-box.positive .metric-label { color: #065f46; }
        .profit-box.positive .metric-value { color: #065f46; }
        .profit-box.warning .metric-label { color: #78350f; }
        .profit-box.warning .metric-value { color: #78350f; }
        .profit-box.negative .metric-label { color: #991b1b; }
        .profit-box.negative .metric-value { color: #991b1b; }
        .area-row.high-margin {
            background-color: #d1fae5 !important;
        }
        .area-row.medium-margin {
            background-color: #fef3c7 !important;
        }
        .area-row.low-margin {
            background-color: #fee2e2 !important;
        }
        .margin-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }
        .margin-badge.high {
            background: #10b981;
            color: white;
        }
        .margin-badge.medium {
            background: #f59e0b;
            color: white;
        }
        .margin-badge.low {
            background: #ef4444;
            color: white;
        }
    </style>
</head>
<body>
<div class="page">

@include('estimates.print-templates._header')

<!-- Report Title -->
<div style="text-align: center; margin-bottom: 20px; padding: 20px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border-radius: 8px;">
    <h1 style="margin: 0; font-size: 28px; font-weight: 700;">Profit Margin Analysis</h1>
    <p style="margin: 8px 0 0 0; font-size: 14px; opacity: 0.9;">Comprehensive profitability breakdown by work area and category</p>
</div>

<!-- Overall Profitability Metrics -->
<h2>Overall Profitability</h2>
<div style="display: table; width: 100%; table-layout: fixed; margin-bottom: 20px;">
    <div style="display: table-cell; width: 32%; padding-right: 1%;">
        <div class="profit-box {{ $grossProfitMargin >= 25 ? 'positive' : ($grossProfitMargin >= 15 ? 'warning' : 'negative') }}">
            <div class="metric-label">Gross Profit</div>
            <div class="metric-value">${{ number_format($grossProfit, 2) }}</div>
            <div class="metric-label" style="margin-top: 6px;">{{ number_format($grossProfitMargin, 1) }}% Margin</div>
        </div>
    </div>
    <div style="display: table-cell; width: 32%; padding: 0 1%;">
        <div class="profit-box {{ $netProfitMargin >= 15 ? 'positive' : ($netProfitMargin >= 8 ? 'warning' : 'negative') }}">
            <div class="metric-label">Net Profit</div>
            <div class="metric-value">${{ number_format($netProfit, 2) }}</div>
            <div class="metric-label" style="margin-top: 6px;">{{ number_format($netProfitMargin, 1) }}% Margin</div>
        </div>
    </div>
    <div style="display: table-cell; width: 32%; padding-left: 1%;">
        <div class="profit-box {{ $totalRevenue >= $totalCost * 1.3 ? 'positive' : ($totalRevenue >= $totalCost * 1.15 ? 'warning' : 'negative') }}">
            <div class="metric-label">Total Revenue</div>
            <div class="metric-value">${{ number_format($totalRevenue, 2) }}</div>
            <div class="metric-label" style="margin-top: 6px;">{{ $totalCost > 0 ? number_format(($totalRevenue / $totalCost - 1) * 100, 0) : '0' }}% Markup</div>
        </div>
    </div>
</div>

<!-- Profit Breakdown by Category -->
<h2>Profit by Category</h2>
<table>
    <thead>
        <tr>
            <th style="width: 30%;">Category</th>
            <th class="text-right" style="width: 17%;">Cost</th>
            <th class="text-right" style="width: 17%;">Revenue</th>
            <th class="text-right" style="width: 18%;">Gross Profit</th>
            <th class="text-right" style="width: 18%;">Margin %</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Materials</strong></td>
            <td class="text-right">${{ number_format($materialsCost, 2) }}</td>
            <td class="text-right">${{ number_format($materialsRevenue, 2) }}</td>
            <td class="text-right">${{ number_format($materialsRevenue - $materialsCost, 2) }}</td>
            <td class="text-right">
                <strong>{{ $materialsRevenue > 0 ? number_format((($materialsRevenue - $materialsCost) / $materialsRevenue) * 100, 1) : '0.0' }}%</strong>
                @php
                    $matMargin = $materialsRevenue > 0 ? (($materialsRevenue - $materialsCost) / $materialsRevenue) * 100 : 0;
                @endphp
                @if($matMargin >= 30)
                    <span class="margin-badge high">HIGH</span>
                @elseif($matMargin >= 20)
                    <span class="margin-badge medium">GOOD</span>
                @else
                    <span class="margin-badge low">LOW</span>
                @endif
            </td>
        </tr>
        <tr>
            <td><strong>Labor</strong></td>
            <td class="text-right">${{ number_format($laborCost, 2) }}</td>
            <td class="text-right">${{ number_format($laborRevenue, 2) }}</td>
            <td class="text-right">${{ number_format($laborRevenue - $laborCost, 2) }}</td>
            <td class="text-right">
                <strong>{{ $laborRevenue > 0 ? number_format((($laborRevenue - $laborCost) / $laborRevenue) * 100, 1) : '0.0' }}%</strong>
                @php
                    $labMargin = $laborRevenue > 0 ? (($laborRevenue - $laborCost) / $laborRevenue) * 100 : 0;
                @endphp
                @if($labMargin >= 35)
                    <span class="margin-badge high">HIGH</span>
                @elseif($labMargin >= 25)
                    <span class="margin-badge medium">GOOD</span>
                @else
                    <span class="margin-badge low">LOW</span>
                @endif
            </td>
        </tr>
        <tr class="total-row">
            <td><strong>COMBINED TOTAL</strong></td>
            <td class="text-right"><strong>${{ number_format($totalCost, 2) }}</strong></td>
            <td class="text-right"><strong>${{ number_format($totalRevenue, 2) }}</strong></td>
            <td class="text-right"><strong>${{ number_format($grossProfit, 2) }}</strong></td>
            <td class="text-right"><strong>{{ number_format($grossProfitMargin, 1) }}%</strong></td>
        </tr>
    </tbody>
</table>

<!-- Profit by Work Area -->
<h2 style="margin-top: 30px;">Profitability by Work Area</h2>
<table>
    <thead>
        <tr>
            <th style="width: 30%;">Work Area</th>
            <th class="text-right" style="width: 14%;">Total Cost</th>
            <th class="text-right" style="width: 14%;">Revenue</th>
            <th class="text-right" style="width: 14%;">Profit</th>
            <th class="text-right" style="width: 14%;">Margin %</th>
            <th class="text-right" style="width: 14%;">% of Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($profitByArea as $area)
            @php
                $marginClass = '';
                if ($area['margin'] >= 25) {
                    $marginClass = 'high-margin';
                } elseif ($area['margin'] >= 15) {
                    $marginClass = 'medium-margin';
                } else {
                    $marginClass = 'low-margin';
                }
            @endphp
            <tr class="area-row {{ $marginClass }}">
                <td><strong>{{ $area['name'] }}</strong></td>
                <td class="text-right">${{ number_format($area['cost'], 2) }}</td>
                <td class="text-right">${{ number_format($area['revenue'], 2) }}</td>
                <td class="text-right">${{ number_format($area['profit'], 2) }}</td>
                <td class="text-right"><strong>{{ number_format($area['margin'], 1) }}%</strong></td>
                <td class="text-right">{{ number_format($area['percent_of_total'], 1) }}%</td>
            </tr>
        @endforeach
    </tbody>
</table>

<!-- Performance Indicators -->
<h2 style="margin-top: 30px;">Performance Indicators</h2>
<div style="display: table; width: 100%; table-layout: fixed;">
    <div style="display: table-cell; width: 49%; padding-right: 1%; vertical-align: top;">
        <div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 15px;">
            <h3 style="margin: 0 0 10px 0; color: #1e40af; font-size: 14px;">Margin Benchmarks</h3>
            <table style="width: 100%; font-size: 13px; color: #1e40af;">
                <tr>
                    <td style="padding: 4px 0;"><strong>Industry Standard:</strong></td>
                    <td style="text-align: right;">20-30%</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0;"><strong>Your Gross Margin:</strong></td>
                    <td style="text-align: right; font-weight: 700;">{{ number_format($grossProfitMargin, 1) }}%</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0;"><strong>Your Net Margin:</strong></td>
                    <td style="text-align: right; font-weight: 700;">{{ number_format($netProfitMargin, 1) }}%</td>
                </tr>
                <tr style="border-top: 2px solid #3b82f6;">
                    <td style="padding: 8px 0 0 0;"><strong>Performance:</strong></td>
                    <td style="text-align: right; padding-top: 8px;">
                        @if($grossProfitMargin >= 25)
                            <span class="margin-badge high">EXCELLENT</span>
                        @elseif($grossProfitMargin >= 20)
                            <span class="margin-badge medium">GOOD</span>
                        @elseif($grossProfitMargin >= 15)
                            <span class="margin-badge medium">FAIR</span>
                        @else
                            <span class="margin-badge low">NEEDS IMPROVEMENT</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>
    
    <div style="display: table-cell; width: 49%; padding-left: 1%; vertical-align: top;">
        <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px;">
            <h3 style="margin: 0 0 10px 0; color: #78350f; font-size: 14px;">Markup Analysis</h3>
            <table style="width: 100%; font-size: 13px; color: #78350f;">
                <tr>
                    <td style="padding: 4px 0;"><strong>Materials Markup:</strong></td>
                    <td style="text-align: right;">{{ $materialsCost > 0 ? number_format((($materialsRevenue - $materialsCost) / $materialsCost) * 100, 0) : '0' }}%</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0;"><strong>Recommended:</strong></td>
                    <td style="text-align: right;">50-100%</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; padding-top: 8px;"><strong>Labor Markup:</strong></td>
                    <td style="text-align: right; padding-top: 8px;">{{ $laborCost > 0 ? number_format((($laborRevenue - $laborCost) / $laborCost) * 100, 0) : '0' }}%</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0;"><strong>Recommended:</strong></td>
                    <td style="text-align: right;">30-50%</td>
                </tr>
            </table>
        </div>
    </div>
</div>

<!-- Detailed Area Analysis -->
<h2 style="margin-top: 30px;">Detailed Work Area Breakdown</h2>
@foreach($profitByArea as $area)
    <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-left: 4px solid 
        @if($area['margin'] >= 25) #10b981 @elseif($area['margin'] >= 15) #f59e0b @else #ef4444 @endif;
        padding: 12px; margin-bottom: 15px; page-break-inside: avoid;">
        
        <div style="overflow: hidden; margin-bottom: 10px;">
            <h3 style="float: left; margin: 0; font-size: 16px; color: #1f2937;">{{ $area['name'] }}</h3>
            <span style="float: right; font-size: 14px; font-weight: 700; color: 
                @if($area['margin'] >= 25) #10b981 @elseif($area['margin'] >= 15) #f59e0b @else #ef4444 @endif;">
                {{ number_format($area['margin'], 1) }}% margin
            </span>
        </div>
        
        <table style="width: 100%; font-size: 12px; margin: 0;">
            <tr style="background: #ffffff;">
                <td style="padding: 8px; border: 1px solid #e5e7eb;"><strong>Materials Cost:</strong></td>
                <td style="padding: 8px; border: 1px solid #e5e7eb; text-align: right;">${{ number_format($area['materials_cost'], 2) }}</td>
                <td style="padding: 8px; border: 1px solid #e5e7eb;"><strong>Materials Revenue:</strong></td>
                <td style="padding: 8px; border: 1px solid #e5e7eb; text-align: right;">${{ number_format($area['materials_revenue'], 2) }}</td>
            </tr>
            <tr style="background: #f9fafb;">
                <td style="padding: 8px; border: 1px solid #e5e7eb;"><strong>Labor Cost:</strong></td>
                <td style="padding: 8px; border: 1px solid #e5e7eb; text-align: right;">${{ number_format($area['labor_cost'], 2) }}</td>
                <td style="padding: 8px; border: 1px solid #e5e7eb;"><strong>Labor Revenue:</strong></td>
                <td style="padding: 8px; border: 1px solid #e5e7eb; text-align: right;">${{ number_format($area['labor_revenue'], 2) }}</td>
            </tr>
            <tr style="background: #ffffff; font-weight: 700;">
                <td style="padding: 8px; border: 1px solid #e5e7eb;">Total Cost:</td>
                <td style="padding: 8px; border: 1px solid #e5e7eb; text-align: right;">${{ number_format($area['cost'], 2) }}</td>
                <td style="padding: 8px; border: 1px solid #e5e7eb;">Total Revenue:</td>
                <td style="padding: 8px; border: 1px solid #e5e7eb; text-align: right;">${{ number_format($area['revenue'], 2) }}</td>
            </tr>
            <tr style="background: 
                @if($area['margin'] >= 25) #d1fae5 @elseif($area['margin'] >= 15) #fef3c7 @else #fee2e2 @endif;
                font-weight: 700; color: 
                @if($area['margin'] >= 25) #065f46 @elseif($area['margin'] >= 15) #78350f @else #991b1b @endif;">
                <td style="padding: 8px; border: 1px solid #e5e7eb;">GROSS PROFIT:</td>
                <td style="padding: 8px; border: 1px solid #e5e7eb; text-align: right;" colspan="3">
                    ${{ number_format($area['profit'], 2) }} ({{ number_format($area['margin'], 1) }}% margin)
                </td>
            </tr>
        </table>
    </div>
@endforeach

<!-- Recommendations -->
<h2 style="margin-top: 30px;">Profitability Recommendations</h2>
<div style="background: #d1fae5; border-left: 4px solid #10b981; padding: 15px;">
    <h3 style="margin: 0 0 10px 0; color: #065f46; font-size: 14px;">Optimization Strategies</h3>
    <ul style="margin: 0; padding-left: 20px; color: #065f46; font-size: 13px; line-height: 1.6;">
        @if($grossProfitMargin < 20)
            <li><strong>Overall margin is below industry standard (20-30%).</strong> Consider reviewing pricing structure or reducing costs.</li>
        @endif
        
        @if($materialsCost > 0 && (($materialsRevenue - $materialsCost) / $materialsCost) * 100 < 50)
            <li><strong>Materials markup is below recommended 50-100%.</strong> Review material pricing to ensure adequate margins.</li>
        @endif
        
        @if($laborCost > 0 && (($laborRevenue - $laborCost) / $laborCost) * 100 < 30)
            <li><strong>Labor markup is below recommended 30-50%.</strong> Consider adjusting hourly billing rates.</li>
        @endif
        
        @foreach($profitByArea as $area)
            @if($area['margin'] < 15)
                <li><strong>{{ $area['name'] }}</strong> has low profitability ({{ number_format($area['margin'], 1) }}%). Review pricing and costs for this work area.</li>
            @endif
        @endforeach
        
        @if($grossProfitMargin >= 25)
            <li><strong>Excellent overall margins!</strong> Your pricing strategy is working well. Monitor for consistency across all projects.</li>
        @endif
        
        <li>Target gross profit margins of 25-30% for sustainable growth and overhead coverage.</li>
        <li>Review material supplier pricing regularly to ensure competitive costs.</li>
        <li>Track actual vs. estimated hours to improve labor cost accuracy.</li>
    </ul>
</div>

<!-- Footer -->
<div class="footer">
    <p><strong>Report Generated:</strong> {{ now()->format('F j, Y g:i A') }}</p>
    <p>This analysis is based on estimated costs and pricing. Actual profitability may vary based on project execution.</p>
</div>

</div>
</body>
</html>
