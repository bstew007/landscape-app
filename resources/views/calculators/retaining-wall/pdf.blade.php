<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Retaining Wall Estimate</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 13px;
            line-height: 1.5;
            padding: 30px;
            color: #333;
        }

        h1, h2, h3, h4 {
            margin-bottom: 5px;
        }

        .section {
            margin-top: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 8px 10px;
            text-align: left;
        }

        th {
            background-color: #f5f5f5;
        }

        .totals-table td {
            border: 1px solid #ccc;
            padding: 8px 10px;
        }

        .totals-table th {
            background-color: #f5f5f5;
            border: 1px solid #ccc;
            padding: 8px 10px;
        }

        .header {
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .info-table td {
            padding: 2px 5px;
            vertical-align: top;
        }

        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo img {
            height: 80px;
        }
    </style>
</head>
<body>

    {{-- Logo --}}
    <div class="logo">
        <img src="{{ public_path('images/logo.png') }}" alt="Company Logo">
    </div>

    <div class="header">
        <h1>Retaining Wall Estimate</h1>
        <p><strong>Estimate Date:</strong> {{ $siteVisit->created_at->format('F j, Y') }}</p>
    </div>

    {{-- Client Info --}}
    <div class="section">
        <h3>Client Information</h3>
        <table class="info-table">
            <tr>
                <td><strong>Name:</strong></td>
                <td>{{ $siteVisit->client->first_name }} {{ $siteVisit->client->last_name }}</td>
            </tr>
            <tr>
                <td><strong>Address:</strong></td>
                <td>{{ $siteVisit->client->address ?? '—' }}</td>
            </tr>
            <tr>
                <td><strong>Phone:</strong></td>
                <td>{{ $siteVisit->client->phone ?? '—' }}</td>
            </tr>
            <tr>
                <td><strong>Email:</strong></td>
                <td>{{ $siteVisit->client->email ?? '—' }}</td>
            </tr>
            <tr>
                <td><strong>Site Visit ID:</strong></td>
                <td>{{ $siteVisit->id }}</td>
            </tr>
        </table>
    </div>
@if($data['include_geogrid'])
    <p>Geogrid was included in this estimate.</p>
@endif

    {{-- Materials --}}
    <div class="section">
        <h3>Materials</h3>
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Cost</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['materials'] as $item => $cost)
                    <tr>
                        <td>{{ $item }}</td>
                        <td>${{ number_format($cost, 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td><strong>Total Material Cost</strong></td>
                    <td><strong>${{ number_format($data['material_total'], 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Labor --}}
    <div class="section">
        <h3>Labor Summary</h3>
        <table>
            <thead>
                <tr>
                    <th>Task</th>
                    <th>Hours</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['labor_by_task'] as $task => $hours)
                    <tr>
                        <td>{{ ucwords(str_replace('_', ' ', $task)) }}</td>
                        <td>{{ number_format($hours, 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td><strong>Total Labor Hours</strong></td>
                    <td><strong>{{ number_format($data['total_hours'], 2) }}</strong></td>
                </tr>
                <tr>
                    <td><strong>Total Labor Cost</strong></td>
                    <td><strong>${{ number_format($data['labor_cost'], 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Totals --}}
    <div class="section">
        <h3>Project Totals</h3>
        <table class="totals-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Materials Total</td>
                    <td>${{ number_format($data['material_total'], 2) }}</td>
                </tr>
                <tr>
                    <td>Labor Cost</td>
                    <td>${{ number_format($data['labor_cost'], 2) }}</td>
                </tr>
                <tr>
                    <td>Markup</td>
                    <td>${{ number_format($data['markup_amount'], 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Final Price</strong></td>
                    <td><strong>${{ number_format($data['final_price'], 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

</body>
</html>
