<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Paver Patio Data</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; margin: 20px; }
        h1, h2, h3 { margin-bottom: 10px; }
        .section { margin-bottom: 25px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ccc; padding: 6px; }
        th { background-color: #f3f4f6; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>

    {{-- Header --}}
    <h1>Paver Patio Data</h1>

    <div class="section">
        <h3>Client: {{ $siteVisit->client->first_name }} {{ $siteVisit->client->last_name }}</h3>
        <p>Address: {{ $siteVisit->client->address ?? '—' }}</p>
        <p>Phone: {{ $siteVisit->client->phone ?? '—' }}</p>
        <p>Email: {{ $siteVisit->client->email ?? '—' }}</p>
        <p>Site Visit Date: {{ $siteVisit->created_at->format('F j, Y') }}</p>
    </div>

    {{-- Materials Summary --}}
    <div class="section">
        <h2>Materials Summary</h2>
        <table>
            <thead>
                <tr>
                    <th>Material</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Unit Cost</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Pavers</td>
                    <td class="text-right">{{ $data['paver_count'] }}</td>
                    <td class="text-right">${{ number_format($data['paver_unit_cost'], 2) }}</td>
                    <td class="text-right">${{ number_format($data['materials']['Pavers']['total'], 2) }}</td>
                </tr>
                <tr>
                    <td>#78 Base Gravel</td>
                    <td class="text-right">{{ $data['base_tons'] }} tons</td>
                    <td class="text-right">${{ number_format($data['base_unit_cost'], 2) }}</td>
                    <td class="text-right">${{ number_format($data['materials']['#78 Base Gravel']['total'], 2) }}</td>
                </tr>
                <tr>
                    <td>Edge Restraints</td>
                    <td class="text-right">{{ $data['edge_lf'] }} lf</td>
                    <td class="text-right">${{ number_format($data['edge_unit_cost'], 2) }} / 20ft</td>
                    <td class="text-right">${{ number_format($data['materials']['Edge Restraints']['total'], 2) }}</td>
                </tr>
                <tr style="font-weight: bold; background-color: #f3f4f6;">
                    <td colspan="3" class="text-right">Total Material Cost:</td>
                    <td class="text-right">${{ number_format($data['material_total'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Labor Breakdown --}}
    <div class="section">
        <h2>Labor Breakdown</h2>
        <table>
            <thead>
                <tr>
                    <th>Task</th>
                    <th class="text-right">Hours</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['labor_by_task'] as $task => $hours)
                    <tr>
                        <td>{{ ucwords(str_replace('_', ' ', $task)) }}</td>
                        <td class="text-right">{{ number_format($hours, 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td><strong>Base Labor Total</strong></td>
                    <td class="text-right"><strong>{{ number_format($data['labor_hours'], 2) }}</strong></td>
                </tr>
                <tr>
                    <td>Overhead + Drive Time</td>
                    <td class="text-right">{{ number_format($data['overhead_hours'], 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Total Labor Hours</strong></td>
                    <td class="text-right"><strong>{{ number_format($data['total_hours'], 2) }}</strong></td>
                </tr>
                <tr style="font-weight: bold; background-color: #f3f4f6;">
                    <td>Labor Cost:</td>
                    <td class="text-right">${{ number_format($data['labor_cost'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Totals --}}
    <div class="section">
        <h2>Pricing Breakdown</h2>
        <table>
            <tbody>
                <tr>
                    <td>Total Labor Hours</td>
                    <td class="text-right">{{ number_format($data['total_hours'], 2) }} hrs</td>
                </tr>
                <tr>
                    <td>Material Cost</td>
                    <td class="text-right">${{ number_format($data['material_total'], 2) }}</td>
                </tr>
                <tr>
                    <td>Labor Cost</td>
                    <td class="text-right">${{ number_format($data['labor_cost'], 2) }}</td>
                </tr>
                <tr>
                    <td>Markup</td>
                    <td class="text-right">${{ number_format($data['markup_amount'], 2) }}</td>
                </tr>
                <tr style="font-weight: bold; background-color: #f3f4f6;">
                    <td>Final Price</td>
                    <td class="text-right">${{ number_format($data['final_price'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

</body>
</html>
