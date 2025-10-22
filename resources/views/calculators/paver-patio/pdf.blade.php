<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Paver Patio Estimate</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; margin: 20px; }
        h1, h2, h3 { margin: 0 0 8px 0; }
        .section { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>

    {{-- Header --}}
    <h1>Paver Patio Estimate</h1>

    <div class="section">
        <h3>Client: {{ $siteVisit->client->first_name }} {{ $siteVisit->client->last_name }}</h3>
        <p>Address: {{ $siteVisit->client->address ?? '—' }}</p>
        <p>Phone: {{ $siteVisit->client->phone ?? '—' }}</p>
        <p>Email: {{ $siteVisit->client->email ?? '—' }}</p>
        <p>Site Visit Date: {{ $siteVisit->created_at->format('F j, Y') }}</p>
        <p>Site Visit ID: {{ $siteVisit->id }}</p>
    </div>

    {{-- Materials --}}
    <div class="section">
        <h2>🧱 Materials</h2>
        <table>
            <thead>
                <tr><th>Item</th><th>Cost</th></tr>
            </thead>
            <tbody>
                @foreach($data['materials'] as $item => $cost)
                    <tr>
                        <td>{{ $item }}</td>
                        <td>${{ number_format($cost, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p class="bold">Total Materials: ${{ number_format($data['material_total'], 2) }}</p>
    </div>

    {{-- Labor --}}
<div class="section">
    <h2>👷 Labor Summary</h2>
    <table>
        <thead>
            <tr><th>Task</th><th>Hours</th></tr>
        </thead>
        <tbody>
            @foreach ($data['labor_by_task'] as $task => $hours)
                <tr>
                    <td>{{ ucwords(str_replace('_', ' ', $task)) }}</td>
                    <td>{{ number_format($hours, 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td><strong>Base Labor Total</strong></td>
                <td><strong>{{ number_format($data['labor_hours'], 2) }} hrs</strong></td>
            </tr>
            <tr>
                <td>Overhead + Drive Time</td>
                <td>{{ number_format($data['overhead_hours'], 2) }} hrs</td>
            </tr>
            <tr>
                <td><strong>Total Labor Hours</strong></td>
                <td><strong>{{ number_format($data['total_hours'], 2) }} hrs</strong></td>
            </tr>
        </tbody>
    </table>

    <p class="bold">Labor Cost: ${{ number_format($data['labor_cost'], 2) }}</p>
</div>


    {{-- Project Totals --}}
    <div class="section">
        <h2>📊 Project Totals</h2>
        <table>
            <tr>
                <td class="bold">Total Hours:</td>
                <td>{{ number_format($data['total_hours'], 2) }} hrs</td>
            </tr>
            <tr>
                <td class="bold">Materials Total:</td>
                <td>${{ number_format($data['material_total'], 2) }}</td>
            </tr>
            <tr>
                <td class="bold">Labor Cost:</td>
                <td>${{ number_format($data['labor_cost'], 2) }}</td>
            </tr>
            <tr>
                <td class="bold">Markup:</td>
                <td>${{ number_format($data['markup_amount'], 2) }}</td>
            </tr>
            <tr>
                <td class="bold">Final Price:</td>
                <td><strong>${{ number_format($data['final_price'], 2) }}</strong></td>
            </tr>
        </table>
    </div>

</body>
</html>
