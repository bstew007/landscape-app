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

    @if ($siteVisit->photos->count())
        <div class="section">
            <h2>Photos</h2>
            <div class="photo-grid">
                @foreach ($siteVisit->photos as $photo)
                    <div>
                        <img src="{{ public_path('storage/'.$photo->path) }}" alt="{{ $photo->caption ?? 'Site photo' }}">
                        <p style="font-size: 11px;">{{ $photo->caption ?? '—' }}</p>
                    </div>
                @endforeach
            </div>
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
</body>
</html>
