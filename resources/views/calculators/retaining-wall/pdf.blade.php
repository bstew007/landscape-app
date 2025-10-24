<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Retaining Wall Estimate</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
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
@if (($data['block_system'] ?? 'standard') === 'allan_block')
    <h3 style="margin-top: 20px; font-weight: bold;">🧱 Allan Block Components</h3>

    <table width="100%" cellpadding="6" cellspacing="0" border="1" style="border-collapse: collapse; margin-top: 10px;">
        <thead style="background: #f3f3f3;">
            <tr>
                <th align="left">Component</th>
                <th align="right">Quantity</th>
                <th align="right">Labor Hours</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Straight Wall Area</td>
                <td align="right">{{ number_format($data['ab_straight_sqft'] ?? 0, 2) }} sqft</td>
                <td align="right">{{ number_format($data['labor_by_task']['ab_straight_wall'] ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td>Curved Wall Area</td>
                <td align="right">{{ number_format($data['ab_curved_sqft'] ?? 0, 2) }} sqft</td>
                <td align="right">{{ number_format($data['labor_by_task']['ab_curved_wall'] ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td>Stairs</td>
                <td align="right">{{ $data['ab_step_count'] ?? 0 }} steps</td>
                <td align="right">{{ number_format($data['labor_by_task']['ab_stairs'] ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td>Columns</td>
                <td align="right">{{ $data['ab_column_count'] ?? 0 }} columns</td>
                <td align="right">{{ number_format($data['labor_by_task']['ab_columns'] ?? 0, 2) }}</td>
            </tr>
        </tbody>
    </table>
@endif

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
            <td>Overhead (Site Conditions, Pickup, Cleanup)</td>
            <td>{{ number_format($data['overhead_hours'] ?? 0, 2) }} hrs</td>
        </tr>
        <tr>
            <td>Drive Time</td>
            <td>{{ number_format($data['drive_time'] ?? 0, 2) }} hrs</td>
        </tr>
        <tr style="border-top: 2px solid #000;">
            <td><strong>Total Labor Hours</strong></td>
            <td><strong>{{ number_format($data['total_hours'] ?? 0, 2) }} hrs</strong></td>
        </tr>
        <tr>
            <td><strong>Total Labor Cost</strong></td>
            <td><strong>${{ number_format($data['labor_cost'] ?? 0, 2) }}</strong></td>
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




    @if (!empty($data['job_notes']))
    <div style="margin-top: 30px;">
        <h3 style="font-size: 16px; font-weight: bold; margin-bottom: 5px;">📌 Job Notes</h3>
        <p style="font-size: 13px; line-height: 1.5;">{{ $data['job_notes'] }}</p>
    </div>
@endif


</body>
</html>
