<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Material Requirements - Estimate #{{ $estimate->id }}</title>
    @include('estimates.print-templates._styles')
    <style>
        .material-summary-box {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border: 2px solid #3b82f6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 12px;
        }
        .supplier-group {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-left: 4px solid #3b82f6;
            padding: 12px;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        .material-item {
            border-bottom: 1px solid #e5e7eb;
            padding: 8px 0;
        }
        .material-item:last-child {
            border-bottom: none;
        }
        .category-header {
            background: #1e40af;
            color: white;
            padding: 10px;
            margin: 20px 0 10px 0;
            border-radius: 6px;
            font-weight: 700;
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="page">

@include('estimates.print-templates._header')

<!-- Report Title -->
<div style="text-align: center; margin-bottom: 20px; padding: 20px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; border-radius: 8px;">
    <h1 style="margin: 0; font-size: 28px; font-weight: 700;">Material Requirements</h1>
    <p style="margin: 8px 0 0 0; font-size: 14px; opacity: 0.9;">Complete materials list with quantities, costs, and supplier information</p>
</div>

<!-- Summary Metrics -->
<h2>Materials Summary</h2>
<div style="display: table; width: 100%; table-layout: fixed; margin-bottom: 20px;">
    <div style="display: table-cell; width: 24%; padding-right: 1%;">
        <div class="material-summary-box">
            <div style="font-size: 12px; color: #1e40af; font-weight: 600;">Total Items</div>
            <div style="font-size: 24px; color: #1e40af; font-weight: 700; margin-top: 4px;">{{ $totalItems }}</div>
        </div>
    </div>
    <div style="display: table-cell; width: 24%; padding: 0 1%;">
        <div class="material-summary-box">
            <div style="font-size: 12px; color: #1e40af; font-weight: 600;">Total Cost</div>
            <div style="font-size: 24px; color: #1e40af; font-weight: 700; margin-top: 4px;">${{ number_format($totalCost, 2) }}</div>
        </div>
    </div>
    <div style="display: table-cell; width: 24%; padding: 0 1%;">
        <div class="material-summary-box">
            <div style="font-size: 12px; color: #1e40af; font-weight: 600;">Total Revenue</div>
            <div style="font-size: 24px; color: #1e40af; font-weight: 700; margin-top: 4px;">${{ number_format($totalRevenue, 2) }}</div>
        </div>
    </div>
    <div style="display: table-cell; width: 24%; padding-left: 1%;">
        <div class="material-summary-box">
            <div style="font-size: 12px; color: #1e40af; font-weight: 600;">Avg Markup</div>
            <div style="font-size: 24px; color: #1e40af; font-weight: 700; margin-top: 4px;">{{ $totalCost > 0 ? number_format((($totalRevenue - $totalCost) / $totalCost) * 100, 0) : '0' }}%</div>
        </div>
    </div>
</div>

<!-- Consolidated Materials List -->
<h2>Consolidated Materials List</h2>
<table>
    <thead>
        <tr>
            <th style="width: 35%;">Material</th>
            <th class="text-center" style="width: 10%;">Unit</th>
            <th class="text-right" style="width: 10%;">Quantity</th>
            <th class="text-right" style="width: 15%;">Unit Cost</th>
            <th class="text-right" style="width: 15%;">Total Cost</th>
            <th class="text-right" style="width: 15%;">Total Price</th>
        </tr>
    </thead>
    <tbody>
        @foreach($consolidatedMaterials as $material)
            <tr>
                <td>
                    <strong>{{ $material['name'] }}</strong>
                    @if(!empty($material['description']))
                        <br><small style="color: #6b7280;">{{ $material['description'] }}</small>
                    @endif
                </td>
                <td class="text-center">{{ $material['unit'] }}</td>
                <td class="text-right">{{ number_format($material['quantity'], 2) }}</td>
                <td class="text-right">${{ number_format($material['unit_cost'], 2) }}</td>
                <td class="text-right">${{ number_format($material['total_cost'], 2) }}</td>
                <td class="text-right">${{ number_format($material['total_price'], 2) }}</td>
            </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="4"><strong>TOTAL MATERIALS</strong></td>
            <td class="text-right"><strong>${{ number_format($totalCost, 2) }}</strong></td>
            <td class="text-right"><strong>${{ number_format($totalRevenue, 2) }}</strong></td>
        </tr>
    </tbody>
</table>

<!-- Materials by Supplier -->
<h2 style="margin-top: 30px;">Materials Grouped by Supplier</h2>
<p style="margin-bottom: 15px; color: #6b7280; font-size: 13px; font-style: italic;">
    Use this section for ordering and procurement. Materials are organized by supplier for easy ordering.
</p>

@foreach($materialsBySupplier as $supplierName => $supplierData)
    <div class="supplier-group">
        <div style="overflow: hidden; margin-bottom: 10px;">
            <h3 style="float: left; margin: 0; font-size: 16px; color: #1f2937;">
                {{ $supplierName ?: 'Unassigned Supplier' }}
            </h3>
            <span style="float: right; font-size: 14px; color: #3b82f6; font-weight: 700;">
                {{ count($supplierData['items']) }} items · ${{ number_format($supplierData['total'], 2) }}
            </span>
        </div>
        
        @if(!empty($supplierData['contact']))
            <div style="background: #eff6ff; padding: 8px; border-radius: 4px; margin-bottom: 10px; font-size: 12px; color: #1e40af;">
                <strong>Contact:</strong> {{ $supplierData['contact'] }}
            </div>
        @endif
        
        <table style="width: 100%; margin: 0; font-size: 12px;">
            <thead>
                <tr style="background: #f3f4f6;">
                    <th style="padding: 6px 8px; text-align: left; font-size: 11px;">Material</th>
                    <th style="padding: 6px 8px; text-align: center; font-size: 11px;">Unit</th>
                    <th style="padding: 6px 8px; text-align: right; font-size: 11px;">Qty Needed</th>
                    <th style="padding: 6px 8px; text-align: right; font-size: 11px;">Unit Cost</th>
                    <th style="padding: 6px 8px; text-align: right; font-size: 11px;">Total Cost</th>
                </tr>
            </thead>
            <tbody>
                @foreach($supplierData['items'] as $item)
                    <tr>
                        <td style="padding: 6px 8px; border-bottom: 1px solid #e5e7eb;">
                            {{ $item['name'] }}
                            @if(!empty($item['sku']))
                                <br><small style="color: #9ca3af;">SKU: {{ $item['sku'] }}</small>
                            @endif
                        </td>
                        <td style="padding: 6px 8px; border-bottom: 1px solid #e5e7eb; text-align: center;">{{ $item['unit'] }}</td>
                        <td style="padding: 6px 8px; border-bottom: 1px solid #e5e7eb; text-align: right;">{{ number_format($item['quantity'], 2) }}</td>
                        <td style="padding: 6px 8px; border-bottom: 1px solid #e5e7eb; text-align: right;">${{ number_format($item['unit_cost'], 2) }}</td>
                        <td style="padding: 6px 8px; border-bottom: 1px solid #e5e7eb; text-align: right;">${{ number_format($item['total_cost'], 2) }}</td>
                    </tr>
                @endforeach
                <tr style="background: #f3f4f6; font-weight: 700;">
                    <td colspan="4" style="padding: 6px 8px; text-align: right;">Supplier Total:</td>
                    <td style="padding: 6px 8px; text-align: right;">${{ number_format($supplierData['total'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
@endforeach

<!-- Materials by Work Area -->
<h2 style="margin-top: 30px;">Materials by Work Area</h2>
<p style="margin-bottom: 15px; color: #6b7280; font-size: 13px; font-style: italic;">
    This breakdown shows which materials are needed for each work area of the project.
</p>

@foreach($materialsByArea as $areaData)
    <div class="category-header">
        {{ $areaData['name'] }} — ${{ number_format($areaData['total_cost'], 2) }} cost / ${{ number_format($areaData['total_price'], 2) }} price
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 40%;">Material</th>
                <th class="text-center" style="width: 10%;">Unit</th>
                <th class="text-right" style="width: 12%;">Quantity</th>
                <th class="text-right" style="width: 13%;">Unit Cost</th>
                <th class="text-right" style="width: 12%;">Total Cost</th>
                <th class="text-right" style="width: 13%;">Total Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($areaData['items'] as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td class="text-center">{{ $item->unit ?? 'ea' }}</td>
                    <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                    <td class="text-right">${{ number_format($item->unit_cost ?? 0, 2) }}</td>
                    <td class="text-right">${{ number_format($item->quantity * ($item->unit_cost ?? 0), 2) }}</td>
                    <td class="text-right">${{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endforeach

<!-- Procurement Notes -->
<h2 style="margin-top: 30px;">Procurement Notes</h2>
<div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px;">
    <h3 style="margin: 0 0 10px 0; color: #78350f; font-size: 14px;">Ordering Recommendations</h3>
    <ul style="margin: 0; padding-left: 20px; color: #78350f; font-size: 13px; line-height: 1.6;">
        <li>Review quantities and add 10-15% buffer for waste and overage</li>
        <li>Confirm availability and lead times with suppliers before ordering</li>
        <li>Verify that material specifications match project requirements</li>
        <li>Check for volume discounts or bulk pricing opportunities</li>
        <li>Coordinate delivery schedules with project timeline</li>
        <li>Confirm delivery location and any special handling requirements</li>
    </ul>
</div>

<!-- Storage & Handling -->
<div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 15px; margin-top: 15px;">
    <h3 style="margin: 0 0 10px 0; color: #1e40af; font-size: 14px;">Storage & Handling</h3>
    <ul style="margin: 0; padding-left: 20px; color: #1e40af; font-size: 13px; line-height: 1.6;">
        <li>Ensure adequate storage space is available before delivery</li>
        <li>Protect materials from weather and theft</li>
        <li>Store materials in sequence needed for installation</li>
        <li>Keep fragile items separate and clearly marked</li>
        <li>Maintain MSDS sheets for all chemical products</li>
    </ul>
</div>

<!-- Footer -->
<div class="footer">
    <p><strong>Report Generated:</strong> {{ now()->format('F j, Y g:i A') }}</p>
    <p>This materials list is based on estimate quantities. Actual requirements may vary.</p>
</div>

</div>
</body>
</html>
