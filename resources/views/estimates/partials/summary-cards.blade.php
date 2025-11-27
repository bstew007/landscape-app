@php
    $activeBudgetName = $activeBudgetName ?? '—';
    $overheadRecoveryModel = $overheadRecoveryModel ?? '—';
    
    // Use the already-calculated database values (recalculated when items are added/removed)
    // This ensures consistency and avoids double-counting or stale relationship data
    $manHours = $estimate->items->where('item_type', 'labor')->sum('quantity');
    $initialBreakeven = $estimate->cost_total ?? 0;  // Use the database column, not recalculating
    $initialSubtotal = $estimate->revenue_total ?? 0;
    $initialGrandTotal = $estimate->grand_total ?? 0;
    
    // Net Profit = Grand Total - Breakeven
    $initialNetProfit = $initialGrandTotal - $initialBreakeven;
    $initialNetMargin = $initialGrandTotal > 0 ? ($initialNetProfit / $initialGrandTotal) * 100 : 0;
@endphp

<div class="grid gap-4 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7">
    <div class="rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Operating Budget</p>
        <p class="mt-2 text-lg font-semibold text-gray-900" data-summary-card="operating-budget-name">{{ $activeBudgetName }}</p>
        <p class="text-[11px] text-gray-400">{{ $overheadRecoveryModel }}</p>
        <p class="text-[11px] text-brand-600 font-medium mt-1">OH: ${{ number_format($overheadRate ?? 0, 2) }}/hr</p>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Man Hours</p>
        <p class="mt-2 text-2xl font-semibold text-gray-900 tabular-nums" id="man-hours" data-summary-card="man-hours">{{ number_format($manHours, 2) }}</p>
        <p class="text-[11px] text-gray-400">Total labor hours</p>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Breakeven</p>
        <p class="mt-2 text-2xl font-semibold text-gray-900 tabular-nums" data-summary-card="total-cost">
            ${{ number_format($initialBreakeven, 2) }}
        </p>
        <p class="text-[11px] text-gray-400">Sum of line item costs</p>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Subtotal</p>
        <p class="mt-2 text-2xl font-semibold text-gray-900 tabular-nums" data-summary-card="subtotal">${{ number_format($initialSubtotal, 2) }}</p>
        <p class="text-[11px] text-gray-400">Sum of line item revenue</p>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Total</p>
        <p class="mt-2 text-2xl font-semibold text-gray-900 tabular-nums" data-summary-card="grand-total">${{ number_format($initialGrandTotal, 2) }}</p>
        <p class="text-[11px] text-gray-400">Estimate grand total</p>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Net Profit</p>
        <p class="mt-2 text-2xl font-semibold text-gray-900 tabular-nums" data-summary-card="net-profit-amount">${{ number_format($initialNetProfit, 2) }}</p>
        <p class="text-[11px] text-gray-400">
            <span data-summary-card="net-profit-percent">{{ number_format($initialNetMargin, 1) }}%</span> margin
        </p>
    </div>
</div>
