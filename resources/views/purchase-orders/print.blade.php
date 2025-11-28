<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order {{ $purchaseOrder->po_number }}</title>
    @php
        $company = \App\Models\CompanySetting::getSettings();
    @endphp
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: #1f2937;
            padding: 40px;
            background: white;
        }
        
        .header {
            padding-bottom: 30px;
            border-bottom: 3px solid #10b981;
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .company-info {
            float: left;
            width: 60%;
        }
        
        .po-info {
            float: right;
            width: 38%;
        }
        
        .company-info h1 {
            font-size: 28px;
            font-weight: 700;
            color: #047857;
            margin-bottom: 8px;
        }
        
        .company-info p {
            font-size: 13px;
            color: #6b7280;
            line-height: 1.6;
        }
        
        .po-info {
            text-align: right;
        }
        
        .po-number {
            font-size: 24px;
            font-weight: 700;
            color: #047857;
            margin-bottom: 4px;
        }
        
        .po-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-draft { background: #f3f4f6; color: #374151; }
        .status-sent { background: #dbeafe; color: #1e40af; }
        .status-received { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        
        .po-date {
            font-size: 12px;
            color: #6b7280;
            margin-top: 8px;
        }
        
        .addresses {
            width: 100%;
            margin-bottom: 30px;
        }
        
        .addresses::after {
            content: "";
            display: table;
            clear: both;
        }
        
        .address-block {
            float: left;
            width: 48%;
        }
        
        .address-block:last-child {
            float: right;
        }
        
        .address-block h3 {
            font-size: 12px;
            font-weight: 600;
            color: #047857;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        
        .address-block p {
            font-size: 13px;
            color: #374151;
            line-height: 1.6;
        }
        
        .address-block strong {
            display: block;
            font-size: 15px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 4px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        thead {
            background: #f9fafb;
            border-top: 2px solid #e5e7eb;
            border-bottom: 2px solid #e5e7eb;
        }
        
        thead th {
            padding: 12px 8px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
        }
        
        thead th.text-left { text-align: left; }
        thead th.text-center { text-align: center; }
        thead th.text-right { text-align: right; }
        
        tbody td {
            padding: 12px 8px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 13px;
            color: #374151;
        }
        
        tbody tr:hover {
            background: #fafafa;
        }
        
        tbody td.text-left { text-align: left; }
        tbody td.text-center { text-align: center; }
        tbody td.text-right { text-align: right; }
        
        tbody td.font-medium {
            font-weight: 500;
            color: #1f2937;
        }
        
        .totals {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }
        
        .totals-table {
            width: 300px;
        }
        
        .totals-table tr {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }
        
        .totals-table tr.total {
            border-top: 2px solid #10b981;
            padding-top: 12px;
            margin-top: 8px;
            font-weight: 700;
            font-size: 16px;
            color: #047857;
        }
        
        .totals-table td:first-child {
            font-weight: 500;
            color: #6b7280;
        }
        
        .totals-table td:last-child {
            font-weight: 600;
            text-align: right;
            color: #1f2937;
        }
        
        .notes {
            margin-top: 30px;
            padding: 20px;
            background: #f9fafb;
            border-left: 4px solid #10b981;
            border-radius: 4px;
        }
        
        .notes h3 {
            font-size: 13px;
            font-weight: 600;
            color: #047857;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        
        .notes p {
            font-size: 13px;
            color: #374151;
            line-height: 1.6;
            white-space: pre-wrap;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 11px;
            color: #9ca3af;
            text-align: center;
        }
        
        @media print {
            body {
                padding: 20px;
            }
            
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    
    <!-- Header -->
    <div class="header">
        <div class="company-info">
            <h1>{{ $company->company_name ?: config('app.name', 'Your Company') }}</h1>
            <p>
                @if($company->address)
                    {{ $company->address }}<br>
                @endif
                @if($company->city || $company->state || $company->postal_code)
                    {{ collect([$company->city, $company->state])->filter()->join(', ') }} {{ $company->postal_code }}<br>
                @endif
                @if($company->phone)
                    Phone: {{ $company->phone }}<br>
                @endif
                @if($company->email)
                    Email: {{ $company->email }}
                @endif
            </p>
        </div>
        <div class="po-info">
            <div class="po-number">{{ $purchaseOrder->po_number }}</div>
            <div>
                <span class="po-status status-{{ $purchaseOrder->status }}">
                    {{ ucfirst($purchaseOrder->status) }}
                </span>
            </div>
            <div class="po-date">
                Date: {{ $purchaseOrder->created_at->format('M d, Y') }}
            </div>
        </div>
    </div>
    
    <!-- Addresses -->
    <div class="addresses">
        <div class="address-block">
            <h3>Vendor</h3>
            @if($purchaseOrder->supplier)
                <strong>{{ $purchaseOrder->supplier->company_name ?: ($purchaseOrder->supplier->first_name . ' ' . $purchaseOrder->supplier->last_name) }}</strong>
                @if($purchaseOrder->supplier->address)
                    <p>{{ $purchaseOrder->supplier->address }}</p>
                @endif
                @if($purchaseOrder->supplier->city || $purchaseOrder->supplier->state || $purchaseOrder->supplier->postal_code)
                    <p>
                        {{ collect([$purchaseOrder->supplier->city, $purchaseOrder->supplier->state])->filter()->join(', ') }} {{ $purchaseOrder->supplier->postal_code }}
                    </p>
                @endif
                @if($purchaseOrder->supplier->phone)
                    <p>Phone: {{ $purchaseOrder->supplier->phone }}</p>
                @endif
                @if($purchaseOrder->supplier->mobile)
                    <p>Mobile: {{ $purchaseOrder->supplier->mobile }}</p>
                @endif
                @if($purchaseOrder->supplier->email)
                    <p>Email: {{ $purchaseOrder->supplier->email }}</p>
                @endif
            @else
                <strong>No Supplier Assigned</strong>
                <p style="color: #ef4444; font-size: 12px;">Please assign a supplier to this purchase order</p>
            @endif
        </div>
        
        <div class="address-block">
            <h3>Ship To / Project</h3>
            @if($purchaseOrder->estimate->property)
                <strong>{{ $purchaseOrder->estimate->client->name }}</strong>
                <p>{{ $purchaseOrder->estimate->property->name }}</p>
                @if($purchaseOrder->estimate->property->address)
                    <p>{{ $purchaseOrder->estimate->property->address }}</p>
                @endif
                @if($purchaseOrder->estimate->property->city || $purchaseOrder->estimate->property->state || $purchaseOrder->estimate->property->zip)
                    <p>
                        {{ $purchaseOrder->estimate->property->city }}{{ $purchaseOrder->estimate->property->state ? ', ' . $purchaseOrder->estimate->property->state : '' }} {{ $purchaseOrder->estimate->property->zip }}
                    </p>
                @endif
            @else
                <strong>{{ $purchaseOrder->estimate->client->name }}</strong>
                <p>Estimate #{{ $purchaseOrder->estimate->id }} - {{ $purchaseOrder->estimate->title }}</p>
            @endif
        </div>
    </div>
    
    <!-- Line Items Table -->
    <table>
        <thead>
            <tr>
                <th class="text-left">Item</th>
                <th class="text-left">Description</th>
                <th class="text-center">Qty</th>
                <th class="text-center">Unit</th>
                <th class="text-right">Unit Cost</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->items as $item)
                <tr>
                    <td class="font-medium">{{ $item->material_name }}</td>
                    <td>{{ $item->estimateItem?->description ?? '' }}</td>
                    <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                    <td class="text-center">{{ $item->unit ?? 'ea' }}</td>
                    <td class="text-right">${{ number_format($item->unit_cost, 2) }}</td>
                    <td class="text-right font-medium">${{ number_format($item->total_cost, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Totals -->
    <div class="totals">
        <div class="totals-table">
            <div style="padding: 8px 0; overflow: hidden;">
                <span style="font-weight: 500; color: #6b7280; float: left;">Subtotal</span>
                <span style="font-weight: 600; color: #1f2937; float: right;">${{ number_format($purchaseOrder->total_amount, 2) }}</span>
            </div>
            <div style="padding-top: 12px; margin-top: 8px; border-top: 2px solid #10b981; font-weight: 700; font-size: 16px; color: #047857; overflow: hidden;">
                <span style="float: left;">Total</span>
                <span style="float: right;">${{ number_format($purchaseOrder->total_amount, 2) }}</span>
            </div>
        </div>
    </div>
    
    <!-- Notes -->
    @if($purchaseOrder->notes)
        <div class="notes">
            <h3>Notes</h3>
            <p>{{ $purchaseOrder->notes }}</p>
        </div>
    @endif
    
    <!-- Footer -->
    <div class="footer">
        <p>Thank you for your business!</p>
        <p style="margin-top: 8px;">
            Purchase Order {{ $purchaseOrder->po_number }} • 
            Estimate #{{ $purchaseOrder->estimate->id }} • 
            Generated {{ now()->format('M d, Y') }}
        </p>
    </div>
    
</body>
</html>
