<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fence Estimate PDF</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 14px; line-height: 1.5; }
        h1, h2 { margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background-color: #eee; }
        .summary { font-weight: bold; }
        .section { margin-bottom: 30px; }
    </style>
</head>
<body>

    <h1>📊 Fence Estimate Summary</h1>
    <p><strong>Company:</strong> Cape Fear Landscaping</p>

    <div class="section">
        <h2>Client Information</h2>
        <p><strong>Name:</strong> {{ $siteVisit->client->full_name }}</p>
        <p><strong>Address:</strong> {{ $siteVisit->client->address ?? '—' }}</p>
        <p><strong>Site Visit Date:</strong> {{ $siteVisit->created_at->format('F j, Y') }}</p>
    </div>

    <div class="section">
        <h2>Final Price</h2>
        <p style="font-size: 18px;"><strong>Total:</strong> ${{ number_format($data['final_price'], 2) }}</p>
    </div>

    <div class="section">
        <h2>Materials Summary</h2>
        <table>
            <thead>
                <tr>
                    <th>Material</th>
                    <th>Qty</th>
                    <th>Unit Cost</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['materials'] as $label => $item)
                    @if(is_array($item) && isset($item['qty'], $item['unit_cost'], $item['total']))
                        <tr>
                            <td>{{ $label }}</td>
                            <td style="text-align: right;">{{ $item['qty'] }}</td>
                            <td style="text-align: right;">${{ number_format($item['unit_cost'], 2) }}</td>
                            <td style="text-align: right;">${{ number_format($item['total'], 2) }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
        <p class="summary">Total Material Cost: ${{ number_format($data['material_total'], 2) }}</p>
    </div>

   <div class="section">
    <h2>👷 Labor Breakdown</h2>
    <table>
        <thead>
            <tr>
                <th>Task</th>
                <th style="text-align: right;">Hours</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($labor_breakdown as $task => $hours)
                <tr>
                    <td>{{ ucwords(str_replace('_', ' ', $task)) }}</td>
                    <td style="text-align: right;">{{ number_format($hours, 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td><strong>Overhead + Travel</strong></td>
                <td style="text-align: right;">{{ number_format($data['overhead_hours'], 2) }}</td>
            </tr>
            <tr>
                <td><strong>Total Labor Hours</strong></td>
                <td style="text-align: right;">{{ number_format($data['total_hours'], 2) }}</td>
            </tr>
            <tr>
                <td><strong>Labor Cost</strong></td>
                <td style="text-align: right;">${{ number_format($data['labor_cost'], 2) }}</td>
            </tr>
        </tbody>
    </table>
</div>


    <div class="section">
        {{-- 💰 Pricing Breakdown --}}
<h2 class="text-2xl font-semibold mb-4 mt-10">💰 Pricing Breakdown</h2>
<table class="w-full text-sm border border-gray-300">
    <tbody>
        <tr>
            <td class="p-2 border-b font-medium">Labor Cost</td>
            <td class="p-2 border-b text-right">${{ number_format($data['labor_cost'], 2) }}</td>
        </tr>
        <tr>
            <td class="p-2 border-b font-medium">Material Cost</td>
            <td class="p-2 border-b text-right">${{ number_format($data['material_total'], 2) }}</td>
        </tr>
        <tr class="font-semibold">
            <td class="p-2 border-b">Total Cost (Before Margin)</td>
            <td class="p-2 border-b text-right">${{ number_format($data['labor_cost'] + $data['material_total'], 2) }}</td>
        </tr>
        <tr>
            <td class="p-2 border-b">Target Margin</td>
            <td class="p-2 border-b text-right">{{ $data['markup'] }}%</td>
        </tr>
        <tr>
            <td class="p-2 border-b">Markup (Dollar Amount)</td>
            <td class="p-2 border-b text-right">${{ number_format($data['markup_amount'], 2) }}</td>
        </tr>
        <tr class="font-bold text-lg">
            <td class="p-2 border-t">Final Price (With Margin)</td>
            <td class="p-2 border-t text-right">${{ number_format($data['final_price'], 2) }}</td>
        </tr>
    </tbody>
</table>


    @if (!empty($data['job_notes']))
    <div class="section">
        <h2>Job Notes</h2>
        <p>{{ $data['job_notes'] }}</p>
    </div>
    @endif

</body>
</html>
