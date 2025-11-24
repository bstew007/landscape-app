@php
    $activeBudgetName = $activeBudgetName ?? '—';
    $overheadRecoveryModel = $overheadRecoveryModel ?? '—';
    // Calculate initial man hours from estimate items
    $manHours = $estimate->items->where('item_type', 'labor')->sum('quantity');
    
    // Calculate initial breakeven based on overhead recovery method
    $initialCost = $estimate->items->sum('cost_total');
    $initialBreakeven = $initialCost;
    
    // Get OH recovery settings from budget
    $budget = $budget ?? app(\App\Services\BudgetService::class)->active();
    $ohRecovery = $budget ? ($budget->inputs['oh_recovery'] ?? []) : [];
    
    if (isset($ohRecovery['labor_hour']) && ($ohRecovery['labor_hour']['activated'] ?? false)) {
        // Labor Hours method: Breakeven = Cost + (Hours × OH Rate)
        $ohRate = (float) ($ohRecovery['labor_hour']['markup_per_hour'] ?? 0);
        $initialBreakeven = $initialCost + ($manHours * $ohRate);
    } elseif (isset($ohRecovery['revenue']) && ($ohRecovery['revenue']['activated'] ?? false)) {
        // Revenue (SORS) method: Breakeven = Cost / (1 - OH%)
        $ohPercent = (float) ($ohRecovery['revenue']['target_overhead_percent'] ?? 0) / 100;
        $initialBreakeven = $ohPercent >= 0.999 ? $initialCost : ($initialCost / (1 - $ohPercent));
    } elseif (isset($ohRecovery['dual']) && ($ohRecovery['dual']['activated'] ?? false)) {
        // Dual-Base: Split between labor hours and revenue methods
        $laborPortion = (float) ($ohRecovery['dual']['labor_portion'] ?? 50) / 100;
        $revenuePortion = 1 - $laborPortion;
        
        $ohRate = (float) ($ohRecovery['dual']['labor_markup_per_hour'] ?? 0);
        $ohPercent = (float) ($ohRecovery['dual']['revenue_overhead_percent'] ?? 0) / 100;
        
        $laborBreakeven = $initialCost + ($manHours * $ohRate * $laborPortion);
        $revenueBreakeven = $ohPercent >= 0.999 ? $initialCost : ($initialCost / (1 - $ohPercent * $revenuePortion));
        
        $initialBreakeven = max($laborBreakeven, $revenueBreakeven);
    }
    
    // Calculate initial totals for the cards
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
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Total Cost</p>
        <p class="mt-2 text-2xl font-semibold text-gray-900 tabular-nums" data-summary-card="total-cost">
            ${{ number_format($initialCost, 2) }}
        </p>
        <p class="text-[11px] text-gray-400">Labor + materials</p>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Breakeven</p>
        <p class="mt-2 text-2xl font-semibold text-gray-900 tabular-nums" data-summary-card="breakeven">
            ${{ number_format($initialBreakeven, 2) }}
        </p>
        <p class="text-[11px] text-gray-400">
            @if($overheadRecoveryModel === 'Labor Hours')
                Cost + OH recovery
            @elseif($overheadRecoveryModel === 'Revenue (SORS)')
                Cost / (1 - OH%)
            @elseif($overheadRecoveryModel === 'Dual-Base')
                Dual method applied
            @else
                Total cost baseline
            @endif
        </p>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Subtotal</p>
        <p class="mt-2 text-2xl font-semibold text-gray-900 tabular-nums" data-summary-card="subtotal">${{ number_format($initialSubtotal, 2) }}</p>
        <p class="text-[11px] text-gray-400">Sum of area subtotals</p>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Total</p>
        <p class="mt-2 text-2xl font-semibold text-gray-900 tabular-nums" data-summary-card="grand-total">${{ number_format($initialGrandTotal, 2) }}</p>
        <p class="text-[11px] text-gray-400">Estimate grand total</p>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Net Profit</p>
        <div class="mt-2 flex items-baseline gap-2">
            <span class="text-2xl font-semibold text-gray-900 tabular-nums" data-summary-card="net-profit-amount">${{ number_format($initialNetProfit, 2) }}</span>
            <span class="text-sm font-medium text-gray-500 tabular-nums" data-summary-card="net-profit-percent">{{ number_format($initialNetMargin, 1) }}%</span>
        </div>
        <p class="text-[11px] text-gray-400">Total - Breakeven</p>
    </div>
</div>
