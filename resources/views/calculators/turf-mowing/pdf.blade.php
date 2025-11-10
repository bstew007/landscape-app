<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Turf Maintenance Estimate</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; line-height: 1.4; padding: 30px; color: #333; }
        h1, h2 { margin-bottom: 10px; }
        .section { margin-top: 25px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 8px 10px; text-align: left; }
        th { background-color: #f5f5f5; }
        .header { border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Turf Maintenance Estimate</h1>
        <p><strong>Site Visit Date:</strong> {{ $siteVisit->created_at->format('F j, Y') }}</p>
    </div>

    <div class="section">
        <h2>Client Information</h2>
        <p><strong>Name:</strong> {{ $siteVisit->client->first_name }} {{ $siteVisit->client->last_name }}</p>
        <p><strong>Address:</strong> {{ $siteVisit->client->address ?? '—' }}</p>
        <p><strong>Phone:</strong> {{ $siteVisit->client->phone ?? '—' }}</p>
        <p><strong>Email:</strong> {{ $siteVisit->client->email ?? '—' }}</p>
    </div>

    <div class="section">
        <h2>Final Price</h2>
        <p style="font-size: 18px;"><strong>Total:</strong> ${{ number_format($data['final_price'], 2) }}</p>
    </div>

    <div class="section">
        <h2>Labor Breakdown</h2>
        <table>
            <thead>
                <tr>
                    <th>Task</th>
                    <th style="text-align: right;">Hours</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['labor_by_task'] as $task => $hours)
                    <tr>
                        <td>{{ ucwords(str_replace('_', ' ', $task)) }}</td>
                        <td style="text-align: right;">{{ number_format($hours, 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td>Overhead</td>
                    <td style="text-align: right;">{{ number_format($data['overhead_hours'] ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td>Drive Time</td>
                    <td style="text-align: right;">{{ number_format($data['drive_time_hours'] ?? 0, 2) }}</td>
                </tr>
                <tr style="font-weight: bold; background-color: #f3f4f6;">
                    <td>Total Labor Hours</td>
                    <td style="text-align: right;">{{ number_format($data['total_hours'] ?? 0, 2) }}</td>
                </tr>
                <tr style="font-weight: bold; background-color: #f3f4f6;">
                    <td>Total Labor Cost</td>
                    <td style="text-align: right;">${{ number_format($data['labor_cost'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Pricing Summary</h2>
        <table>
            <tbody>
                <tr>
                    <td>Labor Cost</td>
                    <td style="text-align: right;">${{ number_format($data['labor_cost'], 2) }}</td>
                </tr>
                <tr>
                    <td>Markup</td>
                    <td style="text-align: right;">${{ number_format($data['markup_amount'] ?? 0, 2) }}</td>
                </tr>
                <tr style="font-weight: bold; background-color: #f3f4f6;">
                    <td>Final Price</td>
                    <td style="text-align: right;">${{ number_format($data['final_price'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    @if (!empty($data['job_notes']))
        <div class="section">
            <h2>Job Notes</h2>
            <p>{{ $data['job_notes'] }}</p>
        </div>
    @endif
</body>
</html>
