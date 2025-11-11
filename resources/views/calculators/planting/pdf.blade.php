<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Planting Estimate</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        h1, h2 { margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; }
        th { background: #f3f4f6; }
        .summary { margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>Planting Estimate</h1>
    <p><strong>Client:</strong> {{ $siteVisit->client->name }}</p>
    <p><strong>Site Visit:</strong> {{ $siteVisit->visit_date?->format('M j, Y') ?? $siteVisit->created_at->format('M j, Y') }}</p>

    <div class="summary">
        <h2>Final Price</h2>
        <p><strong>${{ number_format($data['final_price'], 2) }}</strong></p>
    </div>

    @if (!empty($data['materials']))
        <h2>Materials</h2>
        <table>
            <thead>
                <tr>
                    <th>Material</th>
                    <th style="text-align:right;">Qty</th>
                    <th style="text-align:right;">Unit Cost</th>
                    <th style="text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['materials'] as $label => $item)
                    <tr>
                        <td>{{ $label }}</td>
                        <td style="text-align:right;">{{ $item['qty'] }}</td>
                        <td style="text-align:right;">${{ number_format($item['unit_cost'], 2) }}</td>
                        <td style="text-align:right;">${{ number_format($item['total'], 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="3" style="text-align:right;"><strong>Total Material Cost</strong></td>
                    <td style="text-align:right;"><strong>${{ number_format($data['material_total'], 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
    @endif

    <h2>Labor Breakdown</h2>
    <table>
        <thead>
            <tr>
                <th>Task</th>
                <th style="text-align:right;">Hours</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['labor_by_task'] as $task => $hours)
                <tr>
                    <td>{{ $task }}</td>
                    <td style="text-align:right;">{{ number_format($hours, 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td><strong>Total Labor Hours</strong></td>
                <td style="text-align:right;"><strong>{{ number_format($data['total_hours'], 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <h2>Pricing Summary</h2>
    <table>
        <tbody>
            <tr>
                <td>Labor Cost</td>
                <td style="text-align:right;">${{ number_format($data['labor_cost'], 2) }}</td>
            </tr>
            <tr>
                <td>Material Cost</td>
                <td style="text-align:right;">${{ number_format($data['material_total'], 2) }}</td>
            </tr>
            <tr>
                <td><strong>Final Price</strong></td>
                <td style="text-align:right;"><strong>${{ number_format($data['final_price'], 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    @if (!empty($data['job_notes']))
        <h2>Job Notes</h2>
        <p>{{ $data['job_notes'] }}</p>
    @endif
</body>
</html>
