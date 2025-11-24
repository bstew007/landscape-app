@php
    $activeBudgetName = $activeBudgetName ?? '—';
    $overheadRecoveryModel = $overheadRecoveryModel ?? '—';
@endphp

<div class="grid gap-4 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7">
                <div class="rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Operating Budget</p>
        <p class="mt-2 text-lg font-semibold text-gray-900" data-summary-card="operating-budget-name">{{ $activeBudgetName }}</p>
        <p class="text-[11px] text-gray-400">{{ $overheadRecoveryModel }}</p>
        <p class="text-[11px] text-brand-600 font-medium mt-1">OH: ${{ number_format($overheadRate ?? 0, 2) }}/hr</p>
        {{-- Debug: show where the value is coming from --}}
        @if(isset($budget))
            <p class="text-[9px] text-gray-400 mt-1">
                From outputs: {{ data_get($budget->outputs, 'labor.ohr', 'null') }}<br>
                From inputs: {{ data_get($budget->inputs, 'oh_recovery.labor_hour.markup_per_hour', 'null') }}
            </p>
        @endif
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Man Hours</p>
        <p class="mt-2 text-2xl font-semibold text-gray-900 tabular-nums" data-summary-card="man-hours">--</p>
        <p class="text-[11px] text-gray-400">Totals labor inputs</p>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Total Cost</p>
        <p class="mt-2 text-2xl font-semibold text-gray-900 tabular-nums" data-summary-card="total-cost">--</p>
        <p class="text-[11px] text-gray-400">Labor + materials</p>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Breakeven</p>
        <p class="mt-2 text-2xl font-semibold text-gray-900 tabular-nums" data-summary-card="breakeven">--</p>
        <p class="text-[11px] text-gray-400">From catalog breakeven</p>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Subtotal</p>
        <p class="mt-2 text-2xl font-semibold text-gray-900 tabular-nums" data-summary-card="subtotal">--</p>
        <p class="text-[11px] text-gray-400">Sum of area subtotals</p>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Total</p>
        <p class="mt-2 text-2xl font-semibold text-gray-900 tabular-nums" data-summary-card="grand-total">--</p>
        <p class="text-[11px] text-gray-400">Estimate grand total</p>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Net Profit</p>
        <div class="mt-2 flex items-baseline gap-2">
            <span class="text-2xl font-semibold text-gray-900 tabular-nums" data-summary-card="net-profit-amount">--</span>
            <span class="text-sm font-medium text-gray-500 tabular-nums" data-summary-card="net-profit-percent">0.0%</span>
        </div>
        <p class="text-[11px] text-gray-400">Dollars & percentage</p>
    </div>
</div>
