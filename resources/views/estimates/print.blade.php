<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Estimate #{{ $estimate->id }}</title>
    <style>
        body { font-family: system-ui, sans-serif; padding: 40px; color: #1f2937; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e5e7eb; padding-bottom: 20px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { padding: 8px; border: 1px solid #e5e7eb; }
        th { background: #f9fafb; font-size: 12px; text-transform: uppercase; letter-spacing: .05em; }
        .total { font-weight: 700; text-align: right; font-size: 1.25rem; }
        .print-btn { position: fixed; right: 20px; top: 20px; padding: 10px 15px; border: none; background: #2563eb; color: white; border-radius: 6px; cursor: pointer; }
    </style>
</head>
<body>
<button class="print-btn" onclick="window.print()">Print</button>
<div class="header">
    <div>
        <h1>Estimate #{{ $estimate->id }}</h1>
        <p>{{ $estimate->title }}</p>
    </div>
    <div class="text-right">
        <p><strong>Status:</strong> {{ ucfirst($estimate->status) }}</p>
        <p><strong>Created:</strong> {{ $estimate->created_at->format('M j, Y') }}</p>
        <p><strong>Expires:</strong> {{ optional($estimate->expires_at)->format('M j, Y') ?? 'N/A' }}</p>
    </div>
</div>

<p><strong>Client:</strong> {{ $estimate->client->name }}</p>
<p><strong>Property:</strong> {{ $estimate->property->name ?? 'N/A' }}</p>
<p><strong>Linked Visit:</strong> {{ optional($estimate->siteVisit)->visit_date?->format('M j, Y') ?? 'N/A' }}</p>

<section>
    <h2>Line Items</h2>
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($estimate->line_items ?? [] as $item)
            <tr>
                <td>{{ $item['label'] ?? 'Item' }}</td>
                <td>{{ $item['qty'] ?? 1 }}</td>
                <td>${{ number_format($item['price'] ?? 0, 2) }}</td>
                <td>${{ number_format(($item['qty'] ?? 1) * ($item['price'] ?? 0), 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-sm text-gray-500">No line items yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    <p class="total">
        Total: ${{ number_format($estimate->total ?? 0, 2) }}
    </p>
</section>

<section>
    <h2>Scope & Terms</h2>
    <p>{{ $estimate->notes }}</p>
    <p><strong>Terms:</strong> {{ $estimate->terms }}</p>
</section>
</body>
</html>
