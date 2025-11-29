<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Labor Hours Summary - Estimate #{{ $estimate->id }}</title>
    @include('estimates.print-templates._styles')
    <style>
        .hours-box {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px solid #f59e0b;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 12px;
        }
        .hours-label {
            font-size: 12px;
            color: #78350f;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .hours-value {
            font-size: 24px;
            color: #78350f;
            font-weight: 700;
            margin-top: 4px;
        }
        .labor-category {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-left: 4px solid #f59e0b;
            padding: 12px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="page">

@include('estimates.print-templates._header')

<!-- Report Title -->
<div style="text-align: center; margin-bottom: 20px; padding: 20px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; border-radius: 8px;">
    <h1 style="margin: 0; font-size: 28px; font-weight: 700;">Labor Hours Summary</h1>
    <p style="margin: 8px 0 0 0; font-size: 14px; opacity: 0.9;">Complete breakdown of labor hours by work area and category</p>
</div>

<!-- Summary Metrics -->
<h2>Labor Summary</h2>
<div style="display: table; width: 100%; table-layout: fixed; margin-bottom: 20px;">
    <div style="display: table-cell; width: 24%; padding-right: 1%;">
        <div class="hours-box">
            <div class="hours-label">Total Hours</div>
            <div class="hours-value">{{ number_format($totalHours, 1) }}</div>
        </div>
    </div>
    <div style="display: table-cell; width: 24%; padding: 0 1%;">
        <div class="hours-box">
            <div class="hours-label">Total Labor Cost</div>
            <div class="hours-value">${{ number_format($totalLaborCost, 2) }}</div>
        </div>
    </div>
    <div style="display: table-cell; width: 24%; padding: 0 1%;">
        <div class="hours-box">
            <div class="hours-label">Avg. Cost Rate</div>
            <div class="hours-value">${{ $totalHours > 0 ? number_format($totalLaborCost / $totalHours, 2) : '0.00' }}/hr</div>
        </div>
    </div>
    <div style="display: table-cell; width: 24%; padding-left: 1%;">
        <div class="hours-box">
            <div class="hours-label">Avg. Bill Rate</div>
            <div class="hours-value">${{ $totalHours > 0 ? number_format($totalLaborRevenue / $totalHours, 2) : '0.00' }}/hr</div>
        </div>
    </div>
</div>

<!-- Hours by Work Area -->
<h2>Labor Hours by Work Area</h2>
<table>
    <thead>
        <tr>
            <th style="width: 40%;">Work Area</th>
            <th class="text-center" style="width: 15%;">Hours</th>
            <th class="text-right" style="width: 15%;">Labor Cost</th>
            <th class="text-right" style="width: 15%;">Revenue</th>
            <th class="text-right" style="width: 15%;">Avg Rate</th>
        </tr>
    </thead>
    <tbody>
        @foreach($laborByArea as $areaData)
            <tr>
                <td><strong>{{ $areaData['name'] }}</strong></td>
                <td class="text-center">{{ number_format($areaData['hours'], 1) }} hrs</td>
                <td class="text-right">${{ number_format($areaData['cost'], 2) }}</td>
                <td class="text-right">${{ number_format($areaData['revenue'], 2) }}</td>
                <td class="text-right">${{ $areaData['hours'] > 0 ? number_format($areaData['revenue'] / $areaData['hours'], 2) : '0.00' }}/hr</td>
            </tr>
        @endforeach
        <tr class="total-row">
            <td><strong>TOTAL</strong></td>
            <td class="text-center"><strong>{{ number_format($totalHours, 1) }} hrs</strong></td>
            <td class="text-right"><strong>${{ number_format($totalLaborCost, 2) }}</strong></td>
            <td class="text-right"><strong>${{ number_format($totalLaborRevenue, 2) }}</strong></td>
            <td class="text-right"><strong>${{ $totalHours > 0 ? number_format($totalLaborRevenue / $totalHours, 2) : '0.00' }}/hr</strong></td>
        </tr>
    </tbody>
</table>

<!-- Detailed Breakdown by Area -->
<h2 style="margin-top: 30px;">Detailed Labor Breakdown</h2>
@foreach($laborByArea as $areaData)
    <div class="labor-category">
        <div style="overflow: hidden; margin-bottom: 8px;">
            <h3 style="float: left; margin: 0; font-size: 16px; color: #1f2937;">{{ $areaData['name'] }}</h3>
            <span style="float: right; font-size: 14px; color: #f59e0b; font-weight: 700;">{{ number_format($areaData['hours'], 1) }} hours</span>
        </div>
        
        <table style="width: 100%; margin: 0; font-size: 12px;">
            <thead>
                <tr style="background: #f3f4f6;">
                    <th style="padding: 6px 8px; text-align: left; font-size: 11px;">Labor Type</th>
                    <th style="padding: 6px 8px; text-align: center; font-size: 11px;">Hours</th>
                    <th style="padding: 6px 8px; text-align: right; font-size: 11px;">Cost Rate</th>
                    <th style="padding: 6px 8px; text-align: right; font-size: 11px;">Bill Rate</th>
                    <th style="padding: 6px 8px; text-align: right; font-size: 11px;">Total Cost</th>
                    <th style="padding: 6px 8px; text-align: right; font-size: 11px;">Total Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($areaData['items'] as $item)
                    <tr>
                        <td style="padding: 6px 8px; border-bottom: 1px solid #e5e7eb;">
                            {{ $item->name }}
                            @if($item->description)
                                <br><small style="color: #6b7280;">{{ $item->description }}</small>
                            @endif
                        </td>
                        <td style="padding: 6px 8px; border-bottom: 1px solid #e5e7eb; text-align: center;">{{ number_format($item->quantity, 2) }}</td>
                        <td style="padding: 6px 8px; border-bottom: 1px solid #e5e7eb; text-align: right;">${{ number_format($item->unit_cost ?? 0, 2) }}/hr</td>
                        <td style="padding: 6px 8px; border-bottom: 1px solid #e5e7eb; text-align: right;">${{ number_format($item->unit_price, 2) }}/hr</td>
                        <td style="padding: 6px 8px; border-bottom: 1px solid #e5e7eb; text-align: right;">${{ number_format($item->quantity * ($item->unit_cost ?? 0), 2) }}</td>
                        <td style="padding: 6px 8px; border-bottom: 1px solid #e5e7eb; text-align: right;">${{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endforeach

<!-- Labor Analysis -->
<h2 style="margin-top: 30px;">Labor Analysis</h2>
<div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 15px; margin-bottom: 20px;">
    <h3 style="margin: 0 0 10px 0; color: #1e40af; font-size: 14px;">Key Insights</h3>
    <ul style="margin: 0; padding-left: 20px; color: #1e40af; font-size: 13px; line-height: 1.6;">
        <li><strong>Total Labor Hours:</strong> {{ number_format($totalHours, 1) }} hours estimated for completion</li>
        <li><strong>Average Cost Rate:</strong> ${{ $totalHours > 0 ? number_format($totalLaborCost / $totalHours, 2) : '0.00' }}/hour in labor costs</li>
        <li><strong>Average Bill Rate:</strong> ${{ $totalHours > 0 ? number_format($totalLaborRevenue / $totalHours, 2) : '0.00' }}/hour billed to client</li>
        <li><strong>Labor Profit Margin:</strong> {{ $totalLaborRevenue > 0 ? number_format((($totalLaborRevenue - $totalLaborCost) / $totalLaborRevenue) * 100, 1) : '0.0' }}%</li>
        @if($totalHours > 0)
            <li><strong>Estimated Duration:</strong> 
                @if($totalHours <= 8)
                    1 day ({{ number_format($totalHours, 1) }} hours)
                @elseif($totalHours <= 40)
                    {{ ceil($totalHours / 8) }} days ({{ number_format($totalHours, 1) }} hours)
                @else
                    {{ ceil($totalHours / 40) }} weeks ({{ number_format($totalHours, 1) }} hours)
                @endif
            </li>
        @endif
    </ul>
</div>

<!-- Crew Recommendations -->
<h2>Crew Recommendations</h2>
<div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px;">
    <h3 style="margin: 0 0 10px 0; color: #78350f; font-size: 14px;">Suggested Crew Size & Timeline</h3>
    <table style="width: 100%; font-size: 13px; margin: 0;">
        <thead>
            <tr style="background: rgba(120, 53, 15, 0.1);">
                <th style="padding: 8px; text-align: left; color: #78350f;">Crew Size</th>
                <th style="padding: 8px; text-align: center; color: #78350f;">Working Days</th>
                <th style="padding: 8px; text-align: center; color: #78350f;">Hours/Day</th>
                <th style="padding: 8px; text-align: right; color: #78350f;">Duration</th>
            </tr>
        </thead>
        <tbody>
            @php
                $crewSizes = [2, 3, 4, 5];
                $hoursPerDay = 8;
            @endphp
            @foreach($crewSizes as $crewSize)
                @php
                    $totalManDays = $totalHours / $hoursPerDay;
                    $workingDays = ceil($totalManDays / $crewSize);
                @endphp
                <tr>
                    <td style="padding: 6px 8px; border-bottom: 1px solid #fde68a; color: #78350f;">{{ $crewSize }} person crew</td>
                    <td style="padding: 6px 8px; border-bottom: 1px solid #fde68a; text-align: center; color: #78350f;">{{ $workingDays }} days</td>
                    <td style="padding: 6px 8px; border-bottom: 1px solid #fde68a; text-align: center; color: #78350f;">{{ $hoursPerDay }} hrs</td>
                    <td style="padding: 6px 8px; border-bottom: 1px solid #fde68a; text-align: right; color: #78350f;">
                        @if($workingDays <= 5)
                            {{ $workingDays }} days
                        @elseif($workingDays <= 10)
                            {{ ceil($workingDays / 5) }} weeks
                        @else
                            {{ ceil($workingDays / 20) }} months
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p style="margin: 10px 0 0 0; font-size: 11px; color: #78350f; font-style: italic;">
        * Estimates assume 8-hour work days. Actual duration may vary based on site conditions, weather, and crew efficiency.
    </p>
</div>

<!-- Footer -->
<div class="footer">
    <p><strong>Report Generated:</strong> {{ now()->format('F j, Y g:i A') }}</p>
    <p>This report is for planning and estimation purposes only.</p>
</div>

</div>
</body>
</html>
