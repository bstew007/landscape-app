<style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background: #f3f4f6;
            padding: 24px;
            margin: 0;
            color: #1f2937;
        }
        .email-wrapper {
            max-width: 640px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.1);
        }
        .email-header {
            background: #fefce8;
            padding: 28px 32px;
            color: #1f2937;
            border-bottom: 1px solid #fcd34d;
        }
        .email-header h1 {
            margin: 0;
            font-size: 26px;
            color: #1f2937;
        }
        .email-header p {
            margin: 6px 0 0;
            font-size: 15px;
            color: #92400e;
        }
        .email-body {
            padding: 32px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .summary-card {
            background: #f9fafb;
            border-radius: 12px;
            padding: 16px;
            border: 1px solid #e5e7eb;
        }
        .summary-card span {
            display: block;
            font-size: 12px;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #6b7280;
        }
        .summary-card strong {
            margin-top: 6px;
            font-size: 20px;
            color: #111827;
        }
        .info-block {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
        }
        .info-block h2 {
            margin: 0 0 12px;
            font-size: 16px;
            color: #111827;
        }
        .info-block p {
            margin: 4px 0;
            color: #374151;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        th, td {
            padding: 10px;
            font-size: 13px;
            text-align: left;
        }
        th {
            background: #f3f4f6;
            text-transform: uppercase;
            letter-spacing: .05em;
            font-weight: 600;
            color: #6b7280;
        }
        tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        td {
            border-bottom: 1px solid #e5e7eb;
            color: #1f2937;
        }
        .total-row td {
            font-size: 18px;
            font-weight: 700;
            color: #14532d;
        }
        .notes {
            margin-top: 24px;
        }
        .notes h3 {
            font-size: 16px;
            margin-bottom: 8px;
            color: #111827;
        }
        .notes p {
            font-size: 14px;
            color: #374151;
            line-height: 1.5;
        }
        .email-footer {
            margin-top: 32px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
}
</style>
@php
    $lineItems = $estimate->line_items ?? [];
    $notes = trim($estimate->notes ?? '');
    $terms = trim($estimate->terms ?? '');
    $lineItemsTotal = collect($lineItems)->sum(function ($item) {
        $qty = $item['qty'] ?? 1;
        $rate = $item['price'] ?? $item['rate'] ?? 0;
        return $item['total'] ?? ($qty * $rate);
    });
    $displayTotal = $estimate->total ?? $lineItemsTotal;
@endphp
<div class="email-wrapper">
    <div class="email-header">
        <h1>{{ $estimate->title }}</h1>
        <p>Prepared for {{ $estimate->client->name }}</p>
    </div>
    <div class="email-body">
        <div class="summary-grid">
            <div class="summary-card">
                <span>Total</span>
                <strong>${{ number_format($displayTotal, 2) }}</strong>
            </div>
            <div class="summary-card">
                <span>Status</span>
                <strong>{{ ucfirst($estimate->status) }}</strong>
            </div>
            <div class="summary-card">
                <span>Expires</span>
                <strong>{{ optional($estimate->expires_at)->format('M j, Y') ?? 'N/A' }}</strong>
            </div>
        </div>

        <div class="info-block">
            <h2>Project & Contact</h2>
            <p><strong>Client:</strong> {{ $estimate->client->name }}</p>
            <p><strong>Email:</strong> {{ $estimate->client->email ?? 'Not provided' }}</p>
            <p><strong>Property:</strong> {{ $estimate->property->name ?? 'No property selected' }}</p>
            <p><strong>Site Visit:</strong> {{ optional($estimate->siteVisit)->visit_date?->format('M j, Y') ?? 'N/A' }}</p>
        </div>

        @if (!empty($lineItems))
            <div class="info-block">
                <h2>Line Items</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Scope</th>
                            <th style="text-align:center;">Qty</th>
                            <th style="text-align:right;">Rate</th>
                            <th style="text-align:right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lineItems as $item)
                            @php
                                $qty = $item['qty'] ?? 1;
                                $rate = $item['price'] ?? $item['rate'] ?? 0;
                                $rowTotal = $item['total'] ?? ($qty * $rate);
                            @endphp
                            <tr>
                                <td>{{ $item['label'] ?? 'Line Item' }}</td>
                                <td style="text-align:center;">{{ $qty }}</td>
                                <td style="text-align:right;">${{ number_format($rate, 2) }}</td>
                                <td style="text-align:right;">${{ number_format($rowTotal, 2) }}</td>
                            </tr>
                        @endforeach
                        <tr class="total-row">
                            <td colspan="3">Project Total</td>
                            <td style="text-align:right;">${{ number_format($displayTotal, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif

        @if ($notes !== '')
            <div class="notes">
                <h3>Scope & Notes</h3>
                <p>{!! nl2br(e($notes)) !!}</p>
            </div>
        @endif

        @if ($terms !== '')
            <div class="notes">
                <h3>Terms & Conditions</h3>
                <p>{!! nl2br(e($terms)) !!}</p>
            </div>
        @endif

        <div class="notes">
            <p>We've also attached a PDF copy of this estimate if you'd like to download or print it for your records.</p>
        </div>

        <div class="email-footer">
            © {{ date('Y') }} {{ config('app.name') }} &middot; Prepared by Cape Fear Landscaping
        </div>
    </div>
</div>
