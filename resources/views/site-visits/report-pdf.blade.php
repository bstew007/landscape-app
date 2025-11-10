<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Site Visit Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; line-height: 1.4; padding: 30px; color: #333; }
        h1, h2 { margin-bottom: 10px; }
        .section { margin-top: 25px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 8px 10px; text-align: left; }
        th { background-color: #f5f5f5; }
        .header { border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .photo-grid { display: flex; flex-wrap: wrap; gap: 10px; }
        .photo-grid img { width: 32%; height: auto; object-fit: cover; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Site Visit Report</h1>
        <p><strong>Visit Date:</strong> {{ optional($siteVisit->visit_date ?? $siteVisit->created_at)->format('F j, Y') }}</p>
    </div>

    <div class="section">
        <h2>Client Information</h2>
        <p><strong>Name:</strong> {{ $siteVisit->client->name }}</p>
        <p><strong>Email:</strong> {{ $siteVisit->client->email ?? '—' }}</p>
        <p><strong>Phone:</strong> {{ $siteVisit->client->phone ?? '—' }}</p>
        <p><strong>Address:</strong> {{ $siteVisit->client->address ?? '—' }}</p>
        <p><strong>Site Notes:</strong> {{ $siteVisit->notes ?? '—' }}</p>
    </div>

    @if (!empty($photoSources) && count($photoSources))
        <div class="section">
            <h2>Photos</h2>
            <table>
                <tbody>
                    @foreach (array_chunk($photoSources->toArray(), 2) as $chunk)
                        <tr>
                            @foreach ($chunk as $photo)
                                <td style="width:50%; text-align:center;">
                                    @php
                                        $extension = pathinfo($photo['path'], PATHINFO_EXTENSION);
                                        $dataUri = null;
                                        if (is_readable($photo['path'])) {
                                            $dataUri = 'data:image/'.$extension.';base64,'.base64_encode(file_get_contents($photo['path']));
                                        }
                                    @endphp
                                    @if ($dataUri)
                                        <img src="{{ $dataUri }}" alt="{{ $photo['caption'] ?? 'Site photo' }}" style="width:95%; height:auto; object-fit:cover;">
                                    @else
                                        <span>Image unavailable</span>
                                    @endif
                                    <div style="font-size:11px; margin-top:4px;">{{ $photo['caption'] ?? '—' }}</div>
                                </td>
                            @endforeach
                            @if (count($chunk) === 1)
                                <td style="width:50%;"></td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @foreach ($calculationsByType as $type => $calculations)
        @php
            $calculation = $calculations->first();
            $viewPath = "calculators.reports.pdf.$type";
        @endphp
        @if (View::exists($viewPath))
            @include($viewPath, ['calculation' => $calculation, 'siteVisit' => $siteVisit])
        @else
            <div class="section">
                <h2>{{ ucwords(str_replace('_', ' ', $type)) }}</h2>
                <p>No report view defined for this calculator yet.</p>
            </div>
        @endif
    @endforeach

    @if (!empty($reportSummary) && count($reportSummary))
        <div class="section">
            <h2>Calculator Summary</h2>
            <table>
                <thead>
                    <tr>
                        <th>Calculator</th>
                        <th style="text-align:right;">Labor Cost</th>
                        <th style="text-align:right;">Material Cost</th>
                        <th style="text-align:right;">Total Cost</th>
                        <th style="text-align:right;">Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reportSummary as $row)
                        <tr>
                            <td>{{ $row['label'] }}</td>
                            <td style="text-align:right;">${{ number_format($row['labor'], 2) }}</td>
                            <td style="text-align:right;">${{ number_format($row['materials'], 2) }}</td>
                            <td style="text-align:right;">${{ number_format($row['cost'], 2) }}</td>
                            <td style="text-align:right;">${{ number_format($row['price'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr style="font-weight:bold; background-color:#f5f5f5;">
                        <td style="text-align:right;">Totals:</td>
                        <td style="text-align:right;">${{ number_format($reportTotals['labor'], 2) }}</td>
                        <td style="text-align:right;">${{ number_format($reportTotals['materials'], 2) }}</td>
                        <td style="text-align:right;">${{ number_format($reportTotals['cost'], 2) }}</td>
                        <td style="text-align:right;">${{ number_format($reportTotals['price'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif
</body>
</html>
