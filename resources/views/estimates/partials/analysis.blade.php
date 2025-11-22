@php
    $revenueSnapshot = $financialSummary['revenue'] ?? 0;
    $costSnapshot = $financialSummary['costs'] ?? 0;
    $grossSnapshot = $financialSummary['gross_profit'] ?? 0;
    $netSnapshot = $financialSummary['net_profit'] ?? 0;
    $costSnapshotPercent = $revenueSnapshot > 0 ? min(100, max(0, round(($costSnapshot / $revenueSnapshot) * 100, 1))) : 0;
    $grossSnapshotPercent = $revenueSnapshot > 0 ? min(100, max(0, round(($grossSnapshot / $revenueSnapshot) * 100, 1))) : 0;
    $netSnapshotPercent = $revenueSnapshot > 0 ? min(100, max(0, round(($netSnapshot / $revenueSnapshot) * 100, 1))) : 0;
@endphp
<section class="bg-white rounded-lg shadow p-6 space-y-6">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Financial Snapshot</h2>
            <p class="text-sm text-gray-500">Compare revenue, direct costs, and profits in real-time.</p>
        </div>
        <div class="text-sm text-gray-500">
            <span class="font-semibold text-gray-900">Gross Margin:</span>
            <span id="snapshot-gross-margin">{{ number_format($financialSummary['profit_margin'], 2) }}%</span>
            <span class="mx-2 text-gray-300">•</span>
            <span class="font-semibold text-gray-900">Net Margin:</span>
            <span id="snapshot-net-margin">{{ number_format($financialSummary['net_margin'], 2) }}%</span>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-lg border border-gray-100 p-4">
            <p class="text-xs uppercase tracking-wide text-gray-500">Revenue</p>
            <p class="text-2xl font-semibold text-gray-900" id="snapshot-revenue">${{ number_format($revenueSnapshot, 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">Before taxes and adjustments</p>
        </div>
        <div class="rounded-lg border border-gray-100 p-4">
            <p class="text-xs uppercase tracking-wide text-gray-500">Direct Costs</p>
            <p class="text-2xl font-semibold text-gray-900" id="snapshot-costs">${{ number_format($costSnapshot, 2) }}</p>
            <p class="text-xs text-gray-500 mt-1"><span id="snapshot-cost-percent">{{ $costSnapshotPercent }}</span>% of revenue</p>
        </div>
        <div class="rounded-lg border border-gray-100 p-4">
            <p class="text-xs uppercase tracking-wide text-gray-500">Gross Profit</p>
            <p class="text-2xl font-semibold text-gray-900" id="snapshot-gross-profit">${{ number_format($grossSnapshot, 2) }}</p>
            <p class="text-xs text-gray-500 mt-1" id="snapshot-gross-percent">{{ number_format($financialSummary['profit_margin'], 2) }}% margin</p>
        </div>
        <div class="rounded-lg border border-gray-100 p-4">
            <p class="text-xs uppercase tracking-wide text-gray-500">Net Profit</p>
            <p class="text-2xl font-semibold text-gray-900" id="snapshot-net-profit">${{ number_format($netSnapshot, 2) }}</p>
            <p class="text-xs text-gray-500 mt-1" id="snapshot-net-percent">{{ number_format($financialSummary['net_margin'], 2) }}% margin</p>
        </div>
    </div>

    <div class="space-y-4">
        <div>
            <div class="flex items-center justify-between text-xs font-medium text-gray-600">
                <span>Cost vs Revenue</span>
                <span><span id="snapshot-cost-percent-inline">{{ $costSnapshotPercent }}</span>% costs</span>
            </div>
            <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                <div class="h-full bg-amber-400 transition-all duration-500" style="width: {{ $costSnapshotPercent }}%" id="snapshot-cost-bar"></div>
            </div>
        </div>
        <div>
            <div class="flex items-center justify-between text-xs font-medium text-gray-600">
                <span>Profit Retained</span>
                <span>
                    <span id="snapshot-gross-percent-inline">{{ $grossSnapshotPercent }}</span>% gross ·
                    <span id="snapshot-net-percent-inline">{{ $netSnapshotPercent }}</span>% net
                </span>
            </div>
            <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                <div class="h-full bg-brand-500 transition-all duration-500" style="width: {{ $grossSnapshotPercent }}%" id="snapshot-gross-bar"></div>
            </div>
            <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-brand-200">
                <div class="h-full bg-brand-600 transition-all duration-500" style="width: {{ $netSnapshotPercent }}%" id="snapshot-net-bar"></div>
            </div>
        </div>
    </div>

    <div class="grid gap-3 md:grid-cols-2">
        @foreach ($typeBreakdown as $key => $metrics)
            @php
                $typeRevenue = $metrics['revenue'];
                $typeCost = $metrics['cost'];
                $typeProfit = $metrics['profit'];
                $typeMargin = $typeRevenue > 0 ? ($typeProfit / max($typeRevenue, 1)) * 100 : 0;
            @endphp
            <div class="rounded-lg border border-gray-100 p-4">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-gray-900">{{ $metrics['label'] }}</p>
                    <p class="text-xs text-gray-500"><span id="breakdown-{{ $key }}-margin">{{ number_format($typeMargin, 1) }}</span>% margin</p>
                </div>
                <div class="mt-2 grid grid-cols-3 gap-2 text-xs text-gray-600">
                    <div>
                        <p class="uppercase tracking-wide text-[10px]">Revenue</p>
                        <p class="font-semibold text-gray-900" id="breakdown-{{ $key }}-revenue">${{ number_format($typeRevenue, 2) }}</p>
                    </div>
                    <div>
                        <p class="uppercase tracking-wide text-[10px]">Cost</p>
                        <p class="font-semibold text-gray-900" id="breakdown-{{ $key }}-cost">${{ number_format($typeCost, 2) }}</p>
                    </div>
                    <div>
                        <p class="uppercase tracking-wide text-[10px]">Profit</p>
                        <p class="font-semibold text-gray-900" id="breakdown-{{ $key }}-profit">${{ number_format($typeProfit, 2) }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>
