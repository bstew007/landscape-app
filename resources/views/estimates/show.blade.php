@extends('layouts.sidebar')

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
@endphp

@section('content')
<script>
    // Provide minimal globals for the JS module
    window.__calcRoutes = {
        mulching: '{{ route('calculators.mulching.form') }}',
        weeding: '{{ route('calculators.weeding.form') }}',
        planting: '{{ route('calculators.planting.form') }}',
        turf_mowing: '{{ route('calculators.turf_mowing.form') }}',
        retaining_wall: '{{ route('calculators.wall.form') }}',
        paver_patio: '{{ route('calculators.patio.form') }}',
        fence: '{{ route('calculators.fence.form') }}',
        syn_turf: '{{ route('calculators.syn_turf.form') }}',
        pruning: '{{ route('calculators.pruning.form') }}',
    };
    window.__estimateTemplatesUrl = "{{ route('estimates.calculator.templates', $estimate) }}";
    window.__estimateImportUrl = "{{ route('estimates.calculator.import', $estimate) }}";
    window.__estimateItemsBaseUrl = "{{ url('estimates/'.$estimate->id.'/items') }}";
    window.__estimateSetup = {
        estimateId: {{ (int) $estimate->id }},
        areas: @json($estimate->areas->map(fn($a)=>['id'=>$a->id,'name'=>$a->name]))
    };
</script>
<!-- Page loading overlay -->
<div id="pageLoadingOverlay" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-white/70 backdrop-blur-sm"></div>
    <div class="absolute inset-0 flex items-center justify-center">
        <div class="h-10 w-10 animate-spin rounded-full border-4 border-emerald-600 border-t-transparent"></div>
    </div>
</div>

<div class="space-y-6" x-data="{ tab: 'work', activeArea: 'all', showAddItems: false }">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm text-gray-500 uppercase tracking-wide">Estimate</p>
            <h1 class="text-3xl font-bold">{{ $estimate->title }}</h1>
            <p class="text-gray-600">{{ $estimate->client->name }} · {{ $estimate->property->name ?? 'No property' }}</p>
        </div>
            <div class="flex flex-wrap gap-2">
            <button type="button" id="estimateRefreshBtn" class="rounded border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">Refresh</button>
            <a href="{{ route('estimates.edit', $estimate) }}" class="rounded border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">Edit</a>
            <form action="{{ route('estimates.destroy', $estimate) }}" method="POST" onsubmit="return confirm('Delete this estimate?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded border border-red-300 px-4 py-2 text-sm text-red-600 hover:bg-red-50">Delete</button>
            </form>
            <a href="{{ route('estimates.preview-email', $estimate) }}" class="rounded border border-emerald-300 px-4 py-2 text-sm text-emerald-700 hover:bg-emerald-50">Preview Email</a>
            <form action="{{ route('estimates.invoice', $estimate) }}" method="POST">
                @csrf
                <button type="submit" class="rounded border border-emerald-300 px-4 py-2 text-sm text-emerald-700 hover:bg-emerald-50">Create Invoice</button>
            </form>
            <a href="{{ route('estimates.print', $estimate) }}" target="_blank" class="rounded border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Print</a>
            <button type="button" id="openCalcDrawerBtn" class="rounded bg-brand-700 text-white px-4 py-2 text-sm hover:bg-brand-800">+ Add via Calculator</button>
        </div>
    </div>

    <!-- Add via Calculator Slide-over (controlled by JS module) -->
    <div id="calcDrawer" class="fixed inset-0 z-40" style="display:none;">
        <div id="calcDrawerOverlay" class="absolute inset-0 bg-black/30"></div>
        <div class="absolute right-0 top-0 h-full w-full sm:max-w-2xl bg-white shadow-xl flex flex-col">
            <div class="flex items-center justify-between px-4 py-3 border-b">
                <h3 class="text-lg font-semibold">Add via Calculator</h3>
                <button id="calcDrawerCloseBtn" class="text-gray-500 hover:text-gray-700">Close</button>
            </div>
            <div class="px-4 pt-3 border-b">
                <div class="inline-flex rounded border">
                    <button id="calcTabCreateBtn" class="px-3 py-1 text-sm">Create with Calculator</button>
                    <button id="calcTabTemplatesBtn" class="px-3 py-1 text-sm">Templates</button>
                </div>
            </div>
            <div id="calcCreatePane" class="p-4 overflow-y-auto space-y-4">
                <div class="space-y-2">
                    <label class="block text-sm font-medium">Calculator</label>
                    <select id="calcTypeSelect" class="form-select w-full sm:w-64">
                        <option value="mulching">Mulching</option>
                        <option value="weeding">Weeding</option>
                        <option value="planting">Planting</option>
                        <option value="turf_mowing">Turf Mowing</option>
                        <option value="retaining_wall">Retaining Wall</option>
                        <option value="paver_patio">Paver Patio</option>
                        <option value="fence">Fence</option>
                        <option value="syn_turf">Synthetic Turf</option>
                        <option value="pruning">Pruning</option>
                    </select>
                </div>
                <div>
                    <a id="openTemplateModeLink" href="#" class="inline-flex items-center px-4 py-2 bg-brand-700 text-white rounded hover:bg-brand-800">Open in Template Mode</a>
                    <p class="text-xs text-gray-500 mt-1">Opens the selected calculator with template fields. Save as template and optionally import into this estimate.</p>
                </div>
            </div>
            <div id="calcTemplatesPane" class="p-4 overflow-y-auto space-y-4" style="display:none;">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <label class="text-sm">Type:</label>
                        <select id="calcTypeSelectTpl" class="form-select w-48">
                            <option value="mulching">Mulching</option>
                            <option value="weeding">Weeding</option>
                            <option value="planting">Planting</option>
                            <option value="turf_mowing">Turf Mowing</option>
                            <option value="retaining_wall">Retaining Wall</option>
                            <option value="paver_patio">Paver Patio</option>
                            <option value="fence">Fence</option>
                            <option value="syn_turf">Synthetic Turf</option>
                            <option value="pruning">Pruning</option>
                        </select>
                    </div>
                    <button id="calcTplRefresh" class="text-sm text-gray-600 hover:text-gray-800">Refresh</button>
                </div>
                <div id="calcTplLoading" class="text-sm text-gray-500" style="display:none;">Loading templates...</div>
                <div id="calcTplList" class="space-y-2"></div>
            </div>
        </div>
    </div>

    <!-- Tabs Bar -->
    <div class="bg-white rounded shadow p-2 flex flex-wrap gap-2">
        <button class="px-3 py-1 text-sm rounded border" :class="{ 'bg-blue-600 text-white' : tab==='overview' }" @click="tab='overview'">Customer Info</button>
        <button class="px-3 py-1 text-sm rounded border" :class="{ 'bg-blue-600 text-white' : tab==='work' }" @click="tab='work'">Work & Pricing</button>
        <button class="px-3 py-1 text-sm rounded border" :class="{ 'bg-blue-600 text-white' : tab==='notes' }" @click="tab='notes'">Client Notes</button>
        <button class="px-3 py-1 text-sm rounded border" :class="{ 'bg-blue-600 text-white' : tab==='crew' }" @click="tab='crew'">Crew Notes</button>
        <button class="px-3 py-1 text-sm rounded border" :class="{ 'bg-blue-600 text-white' : tab==='analysis' }" @click="tab='analysis'">Analysis</button>
        <button class="px-3 py-1 text-sm rounded border" :class="{ 'bg-blue-600 text-white' : tab==='files' }" @click="tab='files'">Files</button>
    </div>

    <section class="bg-white rounded-lg shadow p-6 space-y-4" x-show="tab==='overview'">
        @php
            $displayTotal = $estimate->grand_total ?? $estimate->total ?? 0;
            $siteVisitDate = optional(optional($estimate->siteVisit)->visit_date)->format('M j, Y');
        @endphp
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border border-gray-100 p-4">
                <p class="text-xs uppercase tracking-wide text-gray-500">Estimate Total</p>
                <p class="text-2xl font-semibold text-gray-900">${{ number_format($displayTotal, 2) }}</p>
                <p class="text-xs text-gray-500 mt-1">Includes taxes/fees if applicable</p>
            </div>
            <div class="rounded-lg border border-gray-100 p-4">
                <p class="text-xs uppercase tracking-wide text-gray-500">Status</p>
                <div class="mt-1">
                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold"
                          @class([
                              'bg-gray-100 text-gray-700' => $estimate->status === 'draft',
                              'bg-amber-100 text-amber-700' => $estimate->status === 'pending',
                              'bg-blue-100 text-blue-700' => $estimate->status === 'sent',
                              'bg-green-100 text-green-700' => $estimate->status === 'approved',
                              'bg-red-100 text-red-700' => $estimate->status === 'rejected',
                          ])>
                        {{ ucfirst($estimate->status) }}
                    </span>
                </div>
                @if ($estimate->email_last_sent_at)
                    <p class="text-[11px] text-gray-500 mt-2">Last emailed {{ $estimate->email_last_sent_at->format('M j, Y') }} ({{ $estimate->email_send_count }} {{ \Illuminate\Support\Str::plural('time', $estimate->email_send_count) }})</p>
                @endif
            </div>
            <div class="rounded-lg border border-gray-100 p-4">
                <p class="text-xs uppercase tracking-wide text-gray-500">Expires</p>
                <p class="text-lg font-semibold text-gray-900">{{ optional($estimate->expires_at)->format('M j, Y') ?? 'Not set' }}</p>
                <p class="text-[11px] text-gray-500 mt-1">Created {{ optional($estimate->created_at)->format('M j, Y') }}</p>
            </div>
            <div class="rounded-lg border border-gray-100 p-4">
                <p class="text-xs uppercase tracking-wide text-gray-500">Linked Site Visit</p>
                <p class="text-lg font-semibold text-gray-900">{{ $siteVisitDate ?? 'None' }}</p>
                @if (!empty($siteVisitDate))
                    <p class="text-[11px] text-gray-500 mt-1">Visit date</p>
                    @if ($estimate->siteVisit)
                        <a href="{{ route('clients.site-visits.show', [$estimate->client, $estimate->siteVisit]) }}" class="inline-block mt-2 text-xs text-blue-600 hover:text-blue-800">Open Visit</a>
                    @endif
                @else
                    <p class="text-[11px] text-gray-500 mt-1">No site visit linked</p>
                @endif
            </div>
        </div>
        <div class="grid md:grid-cols-2 gap-6">
            <div class="rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-gray-100 px-4 py-3">
                    <h2 class="text-base font-semibold text-gray-900">Project Information</h2>
                </div>
                <div class="px-4 py-4">
                    <form method="POST" action="{{ route('estimates.update', $estimate) }}" class="space-y-3">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Project Name</label>
                            <input type="text" name="title" class="form-input w-full" value="{{ old('title', $estimate->title) }}" required>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Estimate ID</label>
                                <input type="text" class="form-input w-full bg-gray-50" value="{{ $estimate->id }}" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Estimate Date</label>
                                <input type="date" class="form-input w-full bg-gray-50" value="{{ $estimate->created_at->format('Y-m-d') }}" readonly>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Estimate Status</label>
                                <select name="status" class="form-select w-full">
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status }}" @selected(old('status', $estimate->status ?? 'draft') === $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Expires On</label>
                                <input type="date" name="expires_at" class="form-input w-full" value="{{ old('expires_at', optional($estimate->expires_at ?? null)->format('Y-m-d')) }}">
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button class="px-4 py-2 bg-emerald-700 text-white rounded hover:bg-emerald-800">Save</button>
                        </div>
                </form>
                </div>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-gray-100 px-4 py-3">
                    <h2 class="text-base font-semibold text-gray-900">Client Information</h2>
                </div>
                <div class="px-4 py-4 text-sm text-gray-700 space-y-2">
                    <div>
                        <span class="font-medium">Client:</span> {{ $estimate->client->name ?? '—' }}
                    </div>
                    <div>
                        <span class="font-medium">Billing Address:</span>
                        @php
                            $billing = trim(implode(' ', array_filter([
                                $estimate->client->address ?? null,
                                $estimate->client->city ?? null,
                                $estimate->client->state ?? null,
                                $estimate->client->postal_code ?? null,
                            ])));
                        @endphp
                        {{ $billing !== '' ? $billing : '—' }}
                    </div>
                    <div>
                        <span class="font-medium">Contact:</span>
                        {{ trim(($estimate->client->first_name ?? '') . ' ' . ($estimate->client->last_name ?? '')) ?: ($estimate->client->company_name ?? '—') }}
                    </div>
                    <div>
                        <span class="font-medium">Phone:</span> {{ $estimate->client->phone ?? '—' }}
                    </div>
                    <div>
                        <span class="font-medium">Email:</span> {{ $estimate->client->email ?? '—' }}
                    </div>
                    <div>
                        <span class="font-medium">Property:</span> {{ $estimate->property->name ?? '—' }}
                    </div>
                    <div>
                        <span class="font-medium">Property Address:</span>
                        @php
                            $paddr = trim(implode(' ', array_filter([
                                optional($estimate->property)->address_line1,
                                optional($estimate->property)->city,
                                optional($estimate->property)->state,
                                optional($estimate->property)->postal_code,
                            ])));
                        @endphp
                        {{ $paddr !== '' ? $paddr : '—' }}
                    </div>
                </div>
            </div>
        </div>
    </section>


    @php
        $revenueSnapshot = $financialSummary['revenue'] ?? 0;
        $costSnapshot = $financialSummary['costs'] ?? 0;
        $grossSnapshot = $financialSummary['gross_profit'] ?? 0;
        $netSnapshot = $financialSummary['net_profit'] ?? 0;
        $costSnapshotPercent = $revenueSnapshot > 0 ? min(100, max(0, round(($costSnapshot / $revenueSnapshot) * 100, 1))) : 0;
        $grossSnapshotPercent = $revenueSnapshot > 0 ? min(100, max(0, round(($grossSnapshot / $revenueSnapshot) * 100, 1))) : 0;
        $netSnapshotPercent = $revenueSnapshot > 0 ? min(100, max(0, round(($netSnapshot / $revenueSnapshot) * 100, 1))) : 0;
    @endphp

    <section class="bg-white rounded-lg shadow p-6 space-y-6" x-show="tab==='analysis'">
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
                    <div class="h-full bg-emerald-500 transition-all duration-500" style="width: {{ $grossSnapshotPercent }}%" id="snapshot-gross-bar"></div>
                </div>
                <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-emerald-200">
                    <div class="h-full bg-emerald-600 transition-all duration-500" style="width: {{ $netSnapshotPercent }}%" id="snapshot-net-bar"></div>
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

    <section class="bg-white rounded-lg shadow p-6 space-y-4" x-show="tab==='notes'">
        <form method="POST" action="{{ route('estimates.update', $estimate) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700">Client Notes</label>
                <textarea name="notes" rows="6" class="form-textarea w-full">{{ old('notes', $estimate->notes) }}</textarea>
                @error('notes')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Terms & Conditions</label>
                <textarea name="terms" rows="6" class="form-textarea w-full">{{ old('terms', $estimate->terms) }}</textarea>
                @error('terms')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex justify-end">
                <button class="px-4 py-2 bg-emerald-700 text-white rounded hover:bg-emerald-800">Save</button>
            </div>
        </form>
    </section>

    <section class="bg-white rounded-lg shadow p-6 space-y-4" x-show="tab==='work'">
        @php
            $manHours = $estimate->items->where('item_type', 'labor')->sum('quantity');
            $totalCost = $estimate->cost_total ?? 0;
            $subtotal = $estimate->revenue_total ?? 0;
            $totalPrice = $estimate->grand_total ?? 0;
            $grossProfitVal = $estimate->profit_total ?? 0;
            $grossMarginPct = $estimate->profit_margin ?? 0;
            $netProfit = $estimate->net_profit_total ?? 0;
            $netMarginPct = $estimate->net_margin ?? 0;
            $breakeven = max(0, $totalPrice - $netProfit);
        @endphp
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-7">
            <div class="rounded-lg border border-gray-100 p-4">
                <p class="text-xs uppercase tracking-wide text-gray-500">Man Hours</p>
                <p class="text-2xl font-semibold text-gray-900"><span id="work-man-hours">{{ number_format($manHours, 2) }}</span></p>
            </div>
            <div class="rounded-lg border border-gray-100 p-4">
                <p class="text-xs uppercase tracking-wide text-gray-500">Total Cost</p>
                <p class="text-2xl font-semibold text-gray-900" id="work-total-cost">${{ number_format($totalCost, 2) }}</p>
            </div>
            <div class="rounded-lg border border-gray-100 p-4">
                <p class="text-xs uppercase tracking-wide text-gray-500">Breakeven</p>
                <p class="text-2xl font-semibold text-gray-900" id="work-breakeven">${{ number_format($breakeven, 2) }}</p>
            </div>
            <div class="rounded-lg border border-gray-100 p-4">
                <p class="text-xs uppercase tracking-wide text-gray-500">Subtotal</p>
                <p class="text-2xl font-semibold text-gray-900" id="work-subtotal">${{ number_format($subtotal, 2) }}</p>
            </div>
            <div class="rounded-lg border border-gray-100 p-4">
                <p class="text-xs uppercase tracking-wide text-gray-500">Total Price</p>
                <p class="text-2xl font-semibold text-gray-900" id="work-total-price">${{ number_format($totalPrice, 2) }}</p>
            </div>
            <div class="rounded-lg border border-gray-100 p-4">
                <p class="text-xs uppercase tracking-wide text-gray-500">Gross Profit</p>
                <p class="text-2xl font-semibold text-gray-900"><span id="work-gross-profit">${{ number_format($grossProfitVal, 2) }}</span>
                    <span class="text-sm text-gray-500">(<span id="work-gross-margin">{{ number_format($grossMarginPct, 2) }}</span>%)</span>
                </p>
            </div>
            <div class="rounded-lg border border-gray-100 p-4">
                <p class="text-xs uppercase tracking-wide text-gray-500">Net Profit</p>
                <p class="text-2xl font-semibold text-gray-900"><span id="work-net-profit">${{ number_format($netProfit, 2) }}</span>
                    <span class="text-sm text-gray-500">(<span id="work-net-margin">{{ number_format($netMarginPct, 2) }}</span>%)</span>
                </p>
            </div>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-gray-900">Line Items</h2>
            <p class="text-sm text-gray-500">Includes calculator imports + manual catalog additions.</p>
            <form method="POST" action="{{ route('estimates.areas.store', $estimate) }}" class="mt-2 flex flex-wrap items-center gap-2" id="createAreaForm">
                @csrf
                <input type="text" name="name" placeholder="New work area name" class="form-input text-sm" required>
                <button class="px-3 py-1 text-sm rounded bg-emerald-700 text-white hover:bg-emerald-800">Add Work Area</button>
            </form>
        </div>

        <div class="mb-4 bg-white border rounded p-4">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-semibold text-gray-700">Work Areas:</span>
                <button type="button" class="px-2 py-1 text-sm rounded border hover:bg-gray-50" data-area-chip="all">All</button>
                @foreach ($estimate->areas as $area)
                    <div class="inline-flex items-center gap-1">
                        <button type="button" class="px-2 py-1 text-sm rounded border hover:bg-gray-50" data-area-chip="{{ $area->id }}">{{ $area->name }}</button>
                        <form action="{{ route('estimates.areas.destroy', [$estimate, $area]) }}" method="POST" class="inline"
                              onsubmit="return confirm('Delete this work area? Any items in this area will be unassigned.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 text-xs hover:underline" title="Delete area">Delete</button>
                        </form>
                    </div>
                @endforeach
                <button type="button" class="ml-auto px-3 py-1 text-sm rounded bg-emerald-700 text-white hover:bg-emerald-800" @click="showAddItems = true">+ Add Items</button>
            </div>
        </div>

        @if ($estimate->items->isNotEmpty())
            <div class="overflow-x-auto border rounded">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="text-left px-3 py-2">Type</th>
                            <th class="text-left px-3 py-2">Description</th>
                            <th class="text-center px-3 py-2">Qty</th>
                            <th class="text-center px-3 py-2">Unit Cost</th>
                            <th class="text-center px-3 py-2">Unit Price</th>
                            <th class="text-center px-3 py-2">Margin</th>
                            <th class="text-center px-3 py-2">Tax</th>
                            <th class="text-right px-3 py-2">Line Total</th>
                            <th class="px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $grouped = $estimate->items->groupBy('calculation_id');
                        @endphp
                        @foreach ($grouped as $calcId => $items)
                            @php
                                $calc = $calcId ? $items->first()->calculation : null;
                                $groupLabel = $calc ? (\Illuminate\Support\Str::headline($calc->calculation_type) . ' Calculation') : 'Manual Items';
                                $groupSubtotal = $items->sum('line_total');
                            @endphp
                            <tr class="bg-gray-50" @if ($calc) data-calculation-id="{{ $calc->id }}" @endif>
                                <td colspan="7" class="px-3 py-2 text-gray-700 font-semibold">{{ $groupLabel }}</td>
                                <td class="px-3 py-2 text-right font-semibold text-gray-900" data-role="group-subtotal">${{ number_format($groupSubtotal, 2) }}</td>
                                <td class="px-3 py-2 text-right space-x-2">
                                    @if ($calc)
                                        <button type="button" class="text-red-600 hover:underline text-sm" data-action="remove-group" data-calculation-id="{{ $calc->id }}">Remove Items</button>
                                    @endif
                                </td>
                            </tr>
                            @foreach ($items as $item)
                                @php
                                    $marginPercent = $item->margin_rate !== null ? $item->margin_rate * 100 : 0;
                                @endphp
                                <tr
                                    class="border-t"
                                    data-item-id="{{ $item->id }}"
                                    @if ($calcId) data-calculation-id="{{ $calcId }}" @endif
                                    draggable="true"
                                    data-name="{{ e($item->name) }}"
                                    data-quantity="{{ $item->quantity }}"
                                    data-unit="{{ $item->unit }}"
                                    data-unit-cost="{{ $item->unit_cost }}"
                                    data-unit-price="{{ $item->unit_price }}"
                                    data-margin-rate="{{ $item->margin_rate }}"
                                    data-tax-rate="{{ $item->tax_rate }}"
                                    data-cost-total="{{ $item->cost_total }}"
                                    data-margin-total="{{ $item->margin_total }}"
                                    data-area-id="{{ $item->area_id ?? 0 }}"
                                    data-item-type="{{ $item->item_type }}"
                                >
                                    <td class="px-3 py-2 text-gray-600 capitalize">{{ $item->item_type }}</td>
                                    <td class="px-3 py-2">
                                        <div class="font-semibold text-gray-900">{{ $item->name }}</div>
                                        @if ($item->description)
                                            <p class="text-xs text-gray-500">{{ $item->description }}</p>
                                        @endif
                                        <p class="mt-1 text-xs text-gray-500">Cost total: <span data-col="cost_total">${{ number_format($item->cost_total, 2) }}</span></p>
                                    </td>
                                    <td class="px-3 py-2 text-center text-gray-700" data-col="quantity">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }} {{ $item->unit }}</td>
                                    <td class="px-3 py-2 text-center text-gray-700" data-col="unit_cost">${{ number_format($item->unit_cost, 2) }}</td>
                                    <td class="px-3 py-2 text-center text-gray-700" data-col="unit_price">${{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-3 py-2 text-center text-gray-700" data-col="margin">
                                        <div class="font-semibold text-gray-900" data-col="margin_percent">{{ number_format($marginPercent, 2) }}%</div>
                                        <div class="text-xs text-gray-500" data-col="margin_total">${{ number_format($item->margin_total, 2) }}</div>
                                    </td>
                                    <td class="px-3 py-2 text-center text-gray-700" data-col="tax_rate">
                                        {{ $item->tax_rate > 0 ? number_format($item->tax_rate * 100, 2) . '%' : '—' }}
                                    </td>
                                    <td class="px-3 py-2 text-right font-semibold text-gray-900" data-col="line_total">${{ number_format($item->line_total, 2) }}</td>
                                    <td class="px-3 py-2 text-right space-x-3" data-col="actions">
                                        <label class="text-xs text-gray-500 mr-1">Area</label>
                                        <select class="form-select text-xs" data-action="set-area" data-item-id="{{ $item->id }}">
                                            <option value="">Unassigned</option>
                                            @foreach ($estimate->areas as $area)
                                                <option value="{{ $area->id }}" @selected($item->area_id === $area->id)>{{ $area->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="text-blue-600 hover:underline text-sm ml-2" data-action="edit-item" data-item-id="{{ $item->id }}">Edit</button>
                                        <form action="{{ route('estimates.items.destroy', [$estimate, $item]) }}" method="POST"
                                              onsubmit="return confirm('Remove this line item?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-red-600 hover:underline text-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-gray-500">No structured line items yet. Import from calculators or add a catalog item below.</p>
        @endif
    </section>

    <!-- Add Items Slide-over Panel -->
    <div x-show="showAddItems" class="fixed inset-0 z-40" style="display: none;">
        <div class="absolute inset-0 bg-black/30" @click="showAddItems = false"></div>
        <div class="absolute right-0 top-0 h-full w-full sm:max-w-xl bg-white shadow-xl flex flex-col">
            <div class="flex items-center justify-between px-4 py-3 border-b">
                <h3 class="text-lg font-semibold">Add Items</h3>
                <button class="text-gray-500 hover:text-gray-700" @click="showAddItems = false">Close</button>
            </div>
            <div class="p-4 overflow-y-auto space-y-6">
                <div class="bg-white rounded-lg border p-4 space-y-4">
                    <h4 class="text-md font-semibold">Add Material from Catalog</h4>
            <h3 class="text-lg font-semibold">Add Material from Catalog</h3>
            <form method="POST" action="{{ route('estimates.items.store', $estimate) }}" class="space-y-3" id="materialCatalogForm" data-form-type="material">
                @csrf
                <input type="hidden" name="item_type" value="material">
                <input type="hidden" name="catalog_type" value="material">
                <div>
                    <label class="block text-sm font-semibold mb-1">Material</label>
                    <input type="text" class="form-input w-full mb-2 text-sm" placeholder="Search materials..." data-role="filter">
                    <select name="catalog_id" class="form-select w-full" data-role="material-select">
                        <option value="">Select material</option>
                        @foreach ($materials as $material)
                            <option value="{{ $material->id }}"
                                    data-unit="{{ $material->unit }}"
                                    data-cost="{{ $material->unit_cost }}"
                                    data-tax="{{ $material->is_taxable ? $material->tax_rate : 0 }}">
                                {{ $material->name }} ({{ $material->unit }} @ ${{ number_format($material->unit_cost, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Quantity</label>
                        <input type="number" step="0.01" min="0" name="quantity" class="form-input w-full" value="1" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Unit Cost ($)</label>
                        <input type="number" step="0.01" min="0" name="unit_cost" class="form-input w-full" value="0" required data-role="material-cost">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Margin %</label>
                        <input type="number" step="0.1" min="-99" class="form-input w-full" value="{{ number_format($defaultMarginPercent ?? 20, 1) }}" data-role="margin-percent">
                        <input type="hidden" name="margin_rate" value="{{ number_format($defaultMarginRate ?? 0.2, 4) }}" data-role="margin-rate">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Unit Price ($)</label>
                        <input type="number" step="0.01" min="0" name="unit_price" class="form-input w-full" value="0" data-role="unit-price">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Unit Label</label>
                        <input type="text" name="unit" class="form-input w-full" value="" data-role="material-unit">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Tax Rate</label>
                        <input type="number" step="0.001" min="0" name="tax_rate" class="form-input w-full" value="0" data-role="material-tax">
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <button class="inline-flex justify-center px-4 py-2 bg-emerald-700 text-white rounded hover:bg-emerald-800 disabled:opacity-50 disabled:cursor-not-allowed" type="submit" disabled>
                        Add Material
                    </button>
                    <span class="text-xs text-gray-500" data-role="preview-total">Line total: $0.00</span>
                </div>
                @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                @error('unit_cost')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
            </form>
        </div>

                <div class="bg-white rounded-lg border p-4 space-y-4">
                    <h4 class="text-md font-semibold">Add Labor from Catalog</h4>
            <form method="POST" action="{{ route('estimates.items.store', $estimate) }}" class="space-y-3" id="laborCatalogForm" data-form-type="labor">
                @csrf
                <input type="hidden" name="item_type" value="labor">
                <input type="hidden" name="catalog_type" value="labor">
                <div>
                    <label class="block text-sm font-semibold mb-1">Labor</label>
                    <input type="text" class="form-input w-full mb-2 text-sm" placeholder="Search labor..." data-role="filter">
                    <select name="catalog_id" class="form-select w-full" data-role="labor-select">
                        <option value="">Select labor</option>
                        @foreach ($laborCatalog as $labor)
                            <option value="{{ $labor->id }}"
                                    data-unit="{{ $labor->unit }}"
                                    data-cost="{{ $labor->base_rate }}">
                                {{ $labor->name }} ({{ ucfirst($labor->type) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Quantity</label>
                        <input type="number" step="0.01" min="0" name="quantity" class="form-input w-full" value="1" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Unit Cost ($)</label>
                        <input type="number" step="0.01" min="0" name="unit_cost" class="form-input w-full" value="0" required data-role="labor-cost">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Margin %</label>
                        <input type="number" step="0.1" min="-99" class="form-input w-full" value="{{ number_format($defaultMarginPercent ?? 20, 1) }}" data-role="margin-percent">
                        <input type="hidden" name="margin_rate" value="{{ number_format($defaultMarginRate ?? 0.2, 4) }}" data-role="margin-rate">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Unit Price ($)</label>
                        <input type="number" step="0.01" min="0" name="unit_price" class="form-input w-full" value="0" data-role="unit-price">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Unit Label</label>
                        <input type="text" name="unit" class="form-input w-full" value="" data-role="labor-unit">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Tax Rate</label>
                        <input type="number" step="0.001" min="0" name="tax_rate" class="form-input w-full" value="0">
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <button class="inline-flex justify-center px-4 py-2 bg-emerald-700 text-white rounded hover:bg-emerald-800 disabled:opacity-50 disabled:cursor-not-allowed" type="submit" disabled>
                        Add Labor
                    </button>
                    <span class="text-xs text-gray-500" data-role="preview-total">Line total: $0.00</span>
                </div>
                @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                @error('unit_cost')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
            </form>
        </div>

                <div class="bg-white rounded-lg border p-4 space-y-4">
                    <h4 class="text-md font-semibold">Add Custom Line Item</h4>
            <form method="POST" action="{{ route('estimates.items.store', $estimate) }}" class="space-y-3" id="customItemForm" data-form-type="custom">
                @csrf
                <div>
                    <label class="block text-sm font-semibold mb-1">Type</label>
                    <select name="item_type" class="form-select w-full">
                        <option value="material">Material</option>
                        <option value="labor">Labor</option>
                        <option value="fee">Fee</option>
                        <option value="discount">Discount</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Name</label>
                    <input type="text" name="name" class="form-input w-full" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Description</label>
                    <textarea name="description" rows="2" class="form-textarea w-full"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Quantity</label>
                        <input type="number" step="0.01" min="0" name="quantity" class="form-input w-full" value="1" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Unit Cost ($)</label>
                        <input type="number" step="0.01" min="0" name="unit_cost" class="form-input w-full" value="0" required>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Margin %</label>
                        <input type="number" step="0.1" min="-99" class="form-input w-full" value="{{ number_format($defaultMarginPercent ?? 20, 1) }}" data-role="margin-percent">
                        <input type="hidden" name="margin_rate" value="{{ number_format($defaultMarginRate ?? 0.2, 4) }}" data-role="margin-rate">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Unit Price ($)</label>
                        <input type="number" step="0.01" min="0" name="unit_price" class="form-input w-full" value="0" data-role="unit-price">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Unit Label</label>
                        <input type="text" name="unit" class="form-input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Tax Rate</label>
                        <input type="number" step="0.001" min="0" name="tax_rate" class="form-input w-full" value="0">
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <button class="inline-flex justify-center px-4 py-2 bg-emerald-700 text-white rounded hover:bg-emerald-800 disabled:opacity-50 disabled:cursor-not-allowed" type="submit" disabled>
                        Add Custom Item
                    </button>
                    <span class="text-xs text-gray-500" data-role="preview-total">Line total: $0.00</span>
                </div>
                @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                @error('unit_cost')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
            </form>
                </div>
            </div>
        </div>
    </div>

    <section class="bg-white rounded-lg shadow p-6" x-show="tab==='overview'">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Invoice</h2>
            <p class="text-sm text-gray-500">Auto-generated from estimate</p>
        </div>
        @if ($estimate->invoice)
            <p class="text-sm text-gray-700"><strong>Status:</strong> {{ ucfirst($estimate->invoice->status) }}</p>
            <p class="text-sm text-gray-700"><strong>Amount:</strong> ${{ number_format($estimate->invoice->amount ?? 0, 2) }}</p>
            <p class="text-sm text-gray-700"><strong>Due:</strong> {{ optional($estimate->invoice->due_date)->format('M j, Y') ?? 'N/A' }}</p>
            @if ($estimate->invoice->pdf_path)
                <a href="{{ Storage::disk('public')->url($estimate->invoice->pdf_path) }}" class="text-blue-600 hover:text-blue-800 text-sm">Download Invoice</a>
            @endif
        @else
            <p class="text-sm text-gray-500">No invoice generated yet. Use the button above to create one.</p>
        @endif
    </section>

</div>
@endsection

@push('scripts')
<script>
    // Alpine state factory to avoid huge x-data inline JS
    window.estimatePage = function(){ return { tab: 'work', activeArea: 'all', showAddItems: false }; };

document.addEventListener('DOMContentLoaded', () => {
        // Spinner + auto-refresh helpers
        const overlay = document.getElementById('pageLoadingOverlay');
        function showPageSpinner(){ if (overlay) overlay.classList.remove('hidden'); }
        function hidePageSpinner(){ if (overlay) overlay.classList.add('hidden'); }
        function autoRefresh(delay = 150){ showPageSpinner(); setTimeout(() => window.location.reload(), delay); }

        // Refresh button to reload the page and pick up any changes
        const refreshBtn = document.getElementById('estimateRefreshBtn');
        if (refreshBtn) refreshBtn.addEventListener('click', () => autoRefresh());
        // Build collapsible headers per area with subtotals
        function buildAreaHeaders() {
            const tbody = document.querySelector('table tbody');
            if (!tbody) return;
            // Remove existing
            tbody.querySelectorAll('tr[data-role="area-header"]').forEach(el => el.remove());
            const rows = Array.from(tbody.querySelectorAll('tr[data-item-id]'));
            const groups = new Map();
            rows.forEach(r => {
                const aid = r.getAttribute('data-area-id') || '0';
                if (!groups.has(aid)) groups.set(aid, []);
                groups.get(aid).push(r);
            });
            // Build area id -> name map from bootstrap data
            const areaMap = new Map((window.__estimateSetup?.areas || []).map(a => [String(a.id), a.name]));
            groups.forEach((list, aid) => {
                if (!list || !list.length) return;
                let subtotal = 0;
                list.forEach(row => {
                    const cell = row.querySelector('[data-col="line_total"]');
                    if (cell) subtotal += parseFloat((cell.textContent || '').replace(/[^0-9.\-]/g,'')) || 0;
                });
                const label = (aid === '0') ? 'Unassigned' : (areaMap.get(String(aid)) || `Area ${aid}`);
                const header = document.createElement('tr');
                header.className = 'bg-gray-100';
                header.setAttribute('data-role','area-header');
                header.setAttribute('data-area-id', aid);
                header.innerHTML = `
                    <td colspan="7" class="px-3 py-2 text-gray-700 font-semibold">
                        <button data-action="toggle-area" data-area-id="${aid}" class="mr-2 text-xs px-2 py-0.5 rounded border">Toggle</button>
                        ${label}
                    </td>
                    <td class="px-3 py-2 text-right font-semibold text-gray-900" data-role="area-subtotal">$${subtotal.toFixed(2)}</td>
                    <td class="px-3 py-2 text-right text-sm">
                        <button class="text-gray-600 hover:underline text-xs" data-action="collapse-all">Collapse All</button>
                        <button class="text-gray-600 hover:underline text-xs ml-2" data-action="expand-all">Expand All</button>
                    </td>`;
                tbody.insertBefore(header, list[0]);
            });
        }
        buildAreaHeaders();

        document.addEventListener('click', (e) => {
            const t = e.target;
            const toggle = t.closest('[data-action="toggle-area"]');
            if (toggle) {
                const aid = toggle.getAttribute('data-area-id');
                const tbody = document.querySelector('table tbody');
                tbody.querySelectorAll(`tr[data-item-id][data-area-id="${aid}"]`).forEach(r => {
                    r.style.display = (r.style.display === 'none') ? '' : 'none';
                });
                return;
            }
            if (t.closest('[data-action="collapse-all"]')) {
                document.querySelectorAll('tr[data-item-id]').forEach(r => r.style.display = 'none');
                return;
            }
            if (t.closest('[data-action="expand-all"]')) {
                document.querySelectorAll('tr[data-item-id]').forEach(r => r.style.display = '');
                return;
            }
        });
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const reorderUrl = "{{ url('estimates/'.$estimate->id.'/items/reorder') }}";
        const updateBaseUrl = "{{ url('estimates/'.$estimate->id.'/items') }}/";
        const removeCalcBaseUrl = "{{ url('estimates/'.$estimate->id.'/remove-calculation') }}/";

        const parseNumber = (value, fallback = 0) => {
            if (value === null || value === undefined) return fallback;
            if (typeof value === 'number') return Number.isFinite(value) ? value : fallback;
            const cleaned = String(value).replace(/[^0-9.\-]/g, '');
            const num = parseFloat(cleaned);
            return Number.isFinite(num) ? num : fallback;
        };

        const clamp = (val, min, max) => Math.min(Math.max(val, min), max);

        const formatMoney = (val) => {
            const num = parseNumber(val, 0);
            return `$${num.toFixed(2)}`;
        };

        const formatPercent = (val, decimals = 2) => {
            const num = parseNumber(val, 0);
            return `${num.toFixed(decimals)}%`;
        };

        const setText = (target, value) => {
            const el = typeof target === 'string' ? document.getElementById(target) : target;
            if (el) el.textContent = value;
        };

        const setBarWidth = (id, value) => {
            const el = document.getElementById(id);
            if (el) el.style.width = `${clamp(value, 0, 100)}%`;
        };

        function updateSummary(totals) {
            if (!totals) return;

            const materialRevenue = parseNumber(totals.material_subtotal);
            const materialCost = parseNumber(totals.material_cost_total);
            const materialProfit = parseNumber(totals.material_profit_total);

            const laborRevenue = parseNumber(totals.labor_subtotal);
            const laborCost = parseNumber(totals.labor_cost_total);
            const laborProfit = parseNumber(totals.labor_profit_total);

            const feeRevenue = parseNumber(totals.fee_total);
            const feeCost = parseNumber(totals.fee_cost_total);
            const feeProfit = parseNumber(totals.fee_profit_total);

            const discountRevenue = parseNumber(totals.discount_total);
            const discountCost = parseNumber(totals.discount_cost_total);
            const discountProfit = parseNumber(totals.discount_profit_total);

            const revenue = parseNumber(totals.revenue_total);
            const costs = parseNumber(totals.cost_total);
            const grossProfit = parseNumber(totals.profit_total);
            const netProfit = parseNumber(totals.net_profit_total);
            const grossMargin = parseNumber(totals.profit_margin);
            const netMargin = parseNumber(totals.net_margin);
            const taxTotal = parseNumber(totals.tax_total);
            const grandTotal = parseNumber(totals.grand_total);

            setText('summary-material', formatMoney(materialRevenue));
            setText('summary-material-cost', formatMoney(materialCost));
            setText('summary-labor', formatMoney(laborRevenue));
            setText('summary-labor-cost', formatMoney(laborCost));
            setText('summary-fees', formatMoney(feeRevenue - discountRevenue));
            setText('summary-tax', formatMoney(taxTotal));
            setText('summary-revenue', formatMoney(revenue));
            setText('summary-cost', formatMoney(costs));
            setText('summary-profit', formatMoney(grossProfit));
            setText('summary-net', formatMoney(netProfit));
            setText('summary-profit-margin', grossMargin.toFixed(2));
            setText('summary-net-margin', netMargin.toFixed(2));
            setText('summary-grand', formatMoney(grandTotal));

            // Work & Pricing top cards
            setText('work-total-cost', formatMoney(costs));
            setText('work-subtotal', formatMoney(revenue));
            setText('work-total-price', formatMoney(grandTotal));
            setText('work-net-profit', formatMoney(netProfit));
            setText('work-net-margin', netMargin.toFixed(2));
            // Also set gross profit
            setText('work-gross-profit', formatMoney(grossProfit));
            setText('work-gross-margin', grossMargin.toFixed(2));
            const breakeven = Math.max(0, grandTotal - netProfit);
            setText('work-breakeven', formatMoney(breakeven));
            // Man hours computed from DOM rows
            computeManHours();

            setText('snapshot-revenue', formatMoney(revenue));
            setText('snapshot-costs', formatMoney(costs));
            const costPercent = revenue > 0 ? clamp((costs / revenue) * 100, 0, 100) : 0;
            const grossPercent = revenue > 0 ? clamp((grossProfit / revenue) * 100, 0, 100) : 0;
            const netPercent = revenue > 0 ? clamp((netProfit / revenue) * 100, 0, 100) : 0;
            setText('snapshot-cost-percent', costPercent.toFixed(1));
            setText('snapshot-cost-percent-inline', costPercent.toFixed(1));
            setText('snapshot-gross-profit', formatMoney(grossProfit));
            setText('snapshot-net-profit', formatMoney(netProfit));
            setText('snapshot-gross-percent', `${grossMargin.toFixed(2)}% margin`);
            setText('snapshot-net-percent', `${netMargin.toFixed(2)}% margin`);
            setText('snapshot-gross-margin', `${grossMargin.toFixed(2)}%`);
            setText('snapshot-net-margin', `${netMargin.toFixed(2)}%`);
            setText('snapshot-gross-percent-inline', grossPercent.toFixed(1));
            setText('snapshot-net-percent-inline', netPercent.toFixed(1));
            setBarWidth('snapshot-cost-bar', costPercent);
            setBarWidth('snapshot-gross-bar', grossPercent);
            setBarWidth('snapshot-net-bar', netPercent);

            const breakdowns = [
                { key: 'material', revenue: materialRevenue, cost: materialCost, profit: materialProfit },
                { key: 'labor', revenue: laborRevenue, cost: laborCost, profit: laborProfit },
                { key: 'fee', revenue: feeRevenue, cost: feeCost, profit: feeProfit },
                { key: 'discount', revenue: discountRevenue, cost: discountCost, profit: discountProfit },
            ];

            breakdowns.forEach(({ key, revenue, cost, profit }) => {
                setText(`breakdown-${key}-revenue`, formatMoney(revenue));
                setText(`breakdown-${key}-cost`, formatMoney(cost));
                setText(`breakdown-${key}-profit`, formatMoney(profit));
                const margin = revenue !== 0 ? ((profit / Math.abs(revenue)) * 100) : 0;
                setText(`breakdown-${key}-margin`, margin.toFixed(1));
            });
        }
        // Expose for Alpine handlers
        window.updateSummary = updateSummary;

        function computeManHours() {
            const rows = document.querySelectorAll('tr[data-item-id]');
            let hours = 0;
            rows.forEach(r => {
                const type = (r.dataset.itemType || '').toLowerCase();
                if (type === 'labor') {
                    hours += parseNumber(r.dataset.quantity, 0);
                }
            });
            setText('work-man-hours', (hours || 0).toFixed(2));
        }

        function wireCatalogForm(formSelector, selectSelector, unitSelector, costSelector, taxSelector) {
            const form = document.querySelector(formSelector);
            if (!form) return;
            const select = form.querySelector(selectSelector);
            const unitInput = unitSelector ? form.querySelector(unitSelector) : null;
            const costInput = costSelector ? form.querySelector(costSelector) : null;
            const taxInput = taxSelector ? form.querySelector(taxSelector) : null;

            if (select) {
                select.addEventListener('change', () => {
                    const option = select.options[select.selectedIndex];
                    if (!option) return;
                    if (unitInput) unitInput.value = option.dataset.unit || '';
                    if (costInput) {
                        costInput.value = option.dataset.cost || 0;
                        const unitPriceInput = form.querySelector('[data-role="unit-price"]');
                        if (unitPriceInput && unitPriceInput.dataset.manualOverride !== '1') {
                            unitPriceInput.value = option.dataset.cost || 0;
                        }
                    }
                    if (taxInput) taxInput.value = option.dataset.tax || 0;
                    updateFormState(form);
                });
            }

            const filterInput = form.querySelector('[data-role="filter"]');
            if (filterInput && select) {
                filterInput.addEventListener('input', () => {
                    const query = filterInput.value.toLowerCase().trim();
                    Array.from(select.options).forEach((opt, idx) => {
                        if (idx === 0) return;
                        const match = (opt.textContent || '').toLowerCase().includes(query);
                        opt.hidden = query ? !match : false;
                    });
                });
            }
        }

        wireCatalogForm('#materialCatalogForm', '[data-role="material-select"]', '[data-role="material-unit"]', '[data-role="material-cost"]', '[data-role="material-tax"]');
        wireCatalogForm('#laborCatalogForm', '[data-role="labor-select"]', '[data-role="labor-unit"]', '[data-role="labor-cost"]');


        const forms = ['#materialCatalogForm', '#laborCatalogForm', '#customItemForm'].map(sel => document.querySelector(sel)).filter(Boolean);
        forms.forEach(bindForm);

        function bindForm(form) {
            setInitialFinancialState(form);
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(el => el.addEventListener('input', () => handleFormChange(form, el)));
            inputs.forEach(el => el.addEventListener('change', () => handleFormChange(form, el)));
            form.addEventListener('submit', (event) => handleFormSubmit(event, form));
            updateFormState(form);
        }

        function setInitialFinancialState(form) {
            const unitPriceInput = form.querySelector('[data-role="unit-price"]');
            if (unitPriceInput && !unitPriceInput.dataset.manualOverride) {
                unitPriceInput.dataset.manualOverride = '0';
            }
        }

        function handleFormChange(form, el) {
            if (el.matches('[data-role="unit-price"]')) {
                el.dataset.manualOverride = '1';
            }
            if (el.matches('[data-role="margin-percent"]')) {
                const priceInput = form.querySelector('[data-role="unit-price"]');
                if (priceInput) priceInput.dataset.manualOverride = '0';
            }
            updateFormState(form);
        }

        function updateFormState(form) {
            const quantityInput = form.querySelector('input[name="quantity"]');
            const costInput = form.querySelector('input[name="unit_cost"]');
            const unitPriceInput = form.querySelector('[data-role="unit-price"]');
            const marginDisplay = form.querySelector('[data-role="margin-percent"]');
            const marginHidden = form.querySelector('input[name="margin_rate"]');
            const submitBtn = form.querySelector('button[type="submit"]');
            const preview = form.querySelector('[data-role="preview-total"]');
            const select = form.querySelector('select[name="catalog_id"]');
            const nameInput = form.querySelector('input[name="name"]');

            const qty = parseNumber(quantityInput?.value, 0);
            const unitCost = parseNumber(costInput?.value, 0);
            let unitPrice = parseNumber(unitPriceInput?.value, unitCost);
            let marginRate = parseNumber(marginHidden?.value, 0);
            const manualOverride = unitPriceInput?.dataset.manualOverride === '1';

            if (marginDisplay && !manualOverride) {
                marginRate = clamp(parseNumber(marginDisplay.value, 0) / 100, -0.99, 10);
                if (marginHidden) marginHidden.value = marginRate.toFixed(4);
                unitPrice = unitCost * (1 + marginRate);
                if (unitPriceInput) unitPriceInput.value = unitPrice.toFixed(2);
            } else if (unitPriceInput) {
                unitPrice = parseNumber(unitPriceInput.value, unitCost);
                marginRate = unitCost !== 0 ? clamp((unitPrice - unitCost) / unitCost, -0.99, 10) : 0;
                if (marginHidden) marginHidden.value = marginRate.toFixed(4);
                if (marginDisplay) marginDisplay.value = (marginRate * 100).toFixed(2);
            }

            const lineTotal = qty * unitPrice;
            const costTotal = qty * unitCost;
            const marginTotal = lineTotal - costTotal;

            if (preview) {
                preview.textContent = `Line total: ${formatMoney(lineTotal)} · Profit: ${formatMoney(marginTotal)}`;
            }

            let canSubmit = true;
            const type = form.dataset.formType;
            if (type === 'material' || type === 'labor') {
                canSubmit = Boolean(select && select.value);
            } else {
                canSubmit = Boolean(nameInput && nameInput.value.trim().length);
            }
            if (!Number.isFinite(qty) || qty < 0) canSubmit = false;
            if (!Number.isFinite(unitCost) || unitCost < 0) canSubmit = false;
            if (!Number.isFinite(unitPrice) || unitPrice < 0) canSubmit = false;

            if (submitBtn) submitBtn.disabled = !canSubmit;
        }

        function clearFormErrors(form) {
            form.querySelectorAll('[data-error]').forEach(el => el.remove());
            form.querySelectorAll('.border-red-300').forEach(el => el.classList.remove('border-red-300'));
        }

        function renderFormErrors(form, errors) {
            Object.entries(errors || {}).forEach(([field, messages]) => {
                const input = form.querySelector(`[name="${field}"]`);
                const message = Array.isArray(messages) ? messages[0] : String(messages);
                if (input) {
                    input.classList.add('border-red-300');
                    const errorEl = document.createElement('p');
                    errorEl.className = 'text-red-600 text-xs mt-1';
                    errorEl.setAttribute('data-error', field);
                    errorEl.textContent = message;
                    input.insertAdjacentElement('afterend', errorEl);
                }
            });
        }

        function resetForm(form) {
            const formType = form.dataset.formType;
            if (formType === 'material' || formType === 'labor') {
                const select = form.querySelector('select[name="catalog_id"]');
                if (select) select.value = '';
            } else {
                const name = form.querySelector('input[name="name"]');
                if (name) name.value = '';
                const description = form.querySelector('textarea[name="description"]');
                if (description) description.value = '';
            }

            const quantityInput = form.querySelector('input[name="quantity"]');
            if (quantityInput) quantityInput.value = '1';

            const costInput = form.querySelector('input[name="unit_cost"]');
            if (costInput) costInput.value = '0';

            const unitInput = form.querySelector('input[name="unit"]');
            if (unitInput) unitInput.value = '';

            const unitPriceInput = form.querySelector('[data-role="unit-price"]');
            if (unitPriceInput) {
                unitPriceInput.value = costInput ? costInput.value : '0';
                unitPriceInput.dataset.manualOverride = '0';
            }

            const marginDisplay = form.querySelector('[data-role="margin-percent"]');
            if (marginDisplay) marginDisplay.value = '0';

            const marginHidden = form.querySelector('input[name="margin_rate"]');
            if (marginHidden) marginHidden.value = '0';

            const taxInput = form.querySelector('input[name="tax_rate"]');
            if (taxInput) taxInput.value = taxInput.getAttribute('data-default') ?? taxInput.value ?? '0';

            updateFormState(form);
        }

        async function handleFormSubmit(event, form) {
            event.preventDefault();
            clearFormErrors(form);
            const action = form.getAttribute('action');
            const payload = new FormData(form);

            try {
                const response = await fetch(action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: payload,
                });
                if (!response.ok) {
                    throw await response.json().catch(() => ({}));
                }
                const data = await response.json();
                if (data.totals) updateSummary(data.totals);
                showToast('Line item added', 'success');
                return autoRefresh();
            } catch (error) {
                if (error && error.errors) {
                    renderFormErrors(form, error.errors);
                }
                showToast('Could not add item', 'error');
            }
        }

        function ensureManualHeader(tbody) {
            let manualHeader = tbody.querySelector('tr.bg-gray-50:not([data-calculation-id])');
            if (!manualHeader) {
                manualHeader = document.createElement('tr');
                manualHeader.className = 'bg-gray-50';
                manualHeader.innerHTML = `
                    <td colspan="7" class="px-3 py-2 text-gray-700 font-semibold">Manual Items</td>
                    <td class="px-3 py-2 text-right font-semibold text-gray-900" data-role="group-subtotal">$0.00</td>
                    <td class="px-3 py-2 text-right space-x-2"></td>
                `;
                tbody.appendChild(manualHeader);
            }
            return manualHeader;
        }

        function insertOrUpdateRow(item) {
            const table = document.querySelector('table');
            const tbody = table ? table.querySelector('tbody') : null;
            if (!tbody) {
                return window.location.reload();
            }

            if (item.calculation_id) {
                return window.location.reload();
            }

            const existing = tbody.querySelector(`tr[data-item-id="${item.id}"]`);
            if (existing) {
                existing.replaceWith(renderItemRow(item));
                return;
            }

            const manualHeader = ensureManualHeader(tbody);
            const newRow = renderItemRow(item);
            let insertAfter = manualHeader;
            let cursor = manualHeader.nextElementSibling;
            while (cursor && !cursor.classList.contains('bg-gray-50')) {
                insertAfter = cursor;
                cursor = cursor.nextElementSibling;
            }

            insertAfter.insertAdjacentElement('afterend', newRow);
            newRow.classList.add('bg-green-50');
            setTimeout(() => newRow.classList.remove('bg-green-50'), 1200);
            updateGroupSubtotal(manualHeader);
        }

        function updateGroupSubtotal(headerRow) {
            if (!headerRow) return;
            let subtotal = 0;
            let cursor = headerRow.nextElementSibling;
            while (cursor && !cursor.classList.contains('bg-gray-50')) {
                const amountCell = cursor.querySelector('[data-col="line_total"]');
                if (amountCell) {
                    subtotal += parseNumber(amountCell.textContent, 0);
                }
                cursor = cursor.nextElementSibling;
            }
            const subtotalCell = headerRow.querySelector('[data-role="group-subtotal"]');
            if (subtotalCell) subtotalCell.textContent = formatMoney(subtotal);
        }

        function renderItemRow(item) {
            const row = document.createElement('tr');
            row.className = 'border-t';
            row.setAttribute('data-item-id', item.id);
            if (item.calculation_id) {
                row.setAttribute('data-calculation-id', item.calculation_id);
            }
            row.setAttribute('draggable', 'true');
            row.dataset.name = item.name || 'Line Item';
            row.dataset.itemType = (item.item_type || 'item');
            row.dataset.quantity = item.quantity ?? 0;
            row.dataset.unit = item.unit || '';
            row.dataset.unitCost = item.unit_cost ?? 0;
            row.dataset.unitPrice = item.unit_price ?? item.unit_cost ?? 0;
            row.dataset.marginRate = item.margin_rate ?? 0;
            row.dataset.taxRate = item.tax_rate ?? 0;
            row.dataset.costTotal = item.cost_total ?? 0;
            row.dataset.marginTotal = item.margin_total ?? 0;

            const marginPercent = parseNumber(item.margin_rate ?? 0) * 100;
            const quantityText = `${parseNumber(item.quantity, 0).toFixed(2).replace(/\.00$/, '')} ${item.unit || ''}`.trim();
            const taxDisplay = parseNumber(item.tax_rate, 0) > 0 ? `${(parseNumber(item.tax_rate, 0) * 100).toFixed(2)}%` : '—';
            const description = item.description ? `<p class="text-xs text-gray-500">${escapeHtml(item.description)}</p>` : '';

            row.innerHTML = `
                <td class="px-3 py-2 text-gray-600 capitalize">${escapeHtml(item.item_type || 'item')}</td>
                <td class="px-3 py-2">
                    <div class="font-semibold text-gray-900">${escapeHtml(item.name || 'Line Item')}</div>
                    ${description}
                    <p class="mt-1 text-xs text-gray-500">Cost total: <span data-col="cost_total">${formatMoney(item.cost_total ?? 0)}</span></p>
                </td>
                <td class="px-3 py-2 text-center text-gray-700" data-col="quantity">${escapeHtml(quantityText)}</td>
                <td class="px-3 py-2 text-center text-gray-700" data-col="unit_cost">${formatMoney(item.unit_cost ?? 0)}</td>
                <td class="px-3 py-2 text-center text-gray-700" data-col="unit_price">${formatMoney(item.unit_price ?? item.unit_cost ?? 0)}</td>
                <td class="px-3 py-2 text-center text-gray-700" data-col="margin">
                    <div class="font-semibold text-gray-900" data-col="margin_percent">${formatPercent(marginPercent, 2)}</div>
                    <div class="text-xs text-gray-500" data-col="margin_total">${formatMoney(item.margin_total ?? 0)}</div>
                </td>
                <td class="px-3 py-2 text-center text-gray-700" data-col="tax_rate">${taxDisplay}</td>
                <td class="px-3 py-2 text-right font-semibold text-gray-900" data-col="line_total">${formatMoney(item.line_total ?? 0)}</td>
                <td class="px-3 py-2 text-right space-x-3" data-col="actions">
                    <button type="button" class="text-blue-600 hover:underline text-sm" data-action="edit-item" data-item-id="${item.id}">Edit</button>
                    <form action="{{ url('estimates/'.$estimate->id.'/items') }}/${item.id}" method="POST" class="inline" onsubmit="return confirm('Remove this line item?')">
                        <input type="hidden" name="_token" value="${csrfToken}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button class="text-red-600 hover:underline text-sm">Delete</button>
                    </form>
                </td>
            `;
            return row;
        }

        function escapeHtml(value) {
            return (value ?? '').toString()
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        }

        const tbody = document.querySelector('table tbody');
        if (tbody) {
            let dragSrc = null;
            tbody.addEventListener('dragstart', (event) => {
                const row = event.target.closest('tr[data-item-id]');
                if (!row) return;
                dragSrc = row;
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', row.dataset.itemId);
                row.classList.add('opacity-50');
            });

            tbody.addEventListener('dragover', (event) => {
                event.preventDefault();
                event.dataTransfer.dropEffect = 'move';
            });

            tbody.addEventListener('drop', (event) => {
                event.preventDefault();
                const targetRow = event.target.closest('tr[data-item-id]');
                if (!dragSrc || !targetRow || dragSrc === targetRow) return;
                const rect = targetRow.getBoundingClientRect();
                const before = (event.clientY - rect.top) < rect.height / 2;
                if (before) {
                    targetRow.parentNode.insertBefore(dragSrc, targetRow);
                } else {
                    targetRow.parentNode.insertBefore(dragSrc, targetRow.nextSibling);
                }
                dragSrc.classList.remove('opacity-50');
                dragSrc = null;

                const ids = Array.from(tbody.querySelectorAll('tr[data-item-id]')).map(tr => parseInt(tr.dataset.itemId, 10));
                fetch(reorderUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ order: ids }),
                }).then(async (response) => {
                    if (!response.ok) throw await response.json().catch(() => ({}));
                    const json = await response.json();
                    if (json.totals) updateSummary(json.totals);
                    showToast('Item order updated', 'success');
                    autoRefresh();
                }).catch(() => window.location.reload());
            });

            tbody.addEventListener('dragend', (event) => {
                const row = event.target.closest('tr[data-item-id]');
                if (row) row.classList.remove('opacity-50');
                dragSrc = null;
            });
        }

        document.addEventListener('click', async (event) => {
            const editBtn = event.target.closest('[data-action="edit-item"]');
            const saveBtn = event.target.closest('[data-action="save-item"]');
            const cancelBtn = event.target.closest('[data-action="cancel-edit"]');
            const removeGroupBtn = event.target.closest('[data-action="remove-group"]');

            if (editBtn) {
                const id = editBtn.dataset.itemId;
                const row = document.querySelector(`tr[data-item-id="${id}"]`);
                if (row) enterEditMode(row);
                return;
            }

            if (cancelBtn) {
                const id = cancelBtn.dataset.itemId;
                const row = document.querySelector(`tr[data-item-id="${id}"]`);
                if (row && row.dataset.originalHtml) {
                    row.innerHTML = row.dataset.originalHtml;
                    row.dataset.editing = '0';
                }
                return;
            }

            if (saveBtn) {
                const id = saveBtn.dataset.itemId;
                const row = document.querySelector(`tr[data-item-id="${id}"]`);
                if (!row) return;

                const payload = collectRowPayload(row);
                if (!payload) return;

                try {
                    const response = await fetch(`${updateBaseUrl}${id}`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    });
                    if (!response.ok) throw await response.json().catch(() => ({}));
                    const data = await response.json();
                    row.dataset.editing = '0';
                    row.dataset.originalHtml = '';
                    const updatedRow = renderItemRow(data.item);
                    row.replaceWith(updatedRow);
                    if (data.totals) updateSummary(data.totals);
                    showToast('Item updated', 'success');
                    autoRefresh();
                } catch (error) {
                    showToast('Failed to update item', 'error');
                }
                return;
            }

            if (removeGroupBtn) {
                const calcId = removeGroupBtn.dataset.calculationId;
                if (!calcId) return;
                try {
                    const response = await fetch(`${removeCalcBaseUrl}${calcId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ _method: 'DELETE' }),
                    });
                    if (!response.ok) throw await response.json().catch(() => ({}));
                    const data = await response.json();
                    document.querySelectorAll(`tr[data-calculation-id="${calcId}"]`).forEach(row => row.remove());
                    if (data.totals) updateSummary(data.totals);
                    showToast('Removed items for calculation', 'success');
                    autoRefresh();
                } catch (error) {
                    window.location.reload();
                }
            }
        });

        function collectRowPayload(row) {
            const name = row.querySelector('[data-edit-name]')?.value ?? '';
            const quantity = parseNumber(row.querySelector('[data-edit-qty]')?.value, NaN);
            const unit = row.querySelector('[data-edit-unit]')?.value ?? '';
            const unitCost = parseNumber(row.querySelector('[data-edit-unit-cost]')?.value, NaN);
            const unitPrice = parseNumber(row.querySelector('[data-edit-unit-price]')?.value, NaN);
            const marginPercent = parseNumber(row.querySelector('[data-edit-margin]')?.value, NaN);
            const taxRate = parseNumber(row.querySelector('[data-edit-tax]')?.value, NaN);

            if (!name.trim().length) {
                alert('Item name is required.');
                return null;
            }
            if (!Number.isFinite(quantity) || quantity < 0) {
                alert('Invalid quantity value.');
                return null;
            }
            if (!Number.isFinite(unitCost) || unitCost < 0) {
                alert('Invalid unit cost value.');
                return null;
            }
            if (!Number.isFinite(unitPrice) || unitPrice < 0) {
                alert('Invalid unit price value.');
                return null;
            }
            if (!Number.isFinite(marginPercent)) {
                alert('Invalid margin percentage.');
                return null;
            }
            if (!Number.isFinite(taxRate) || taxRate < 0) {
                alert('Invalid tax rate.');
                return null;
            }

            return {
                name,
                unit,
                quantity,
                unit_cost: unitCost,
                unit_price: unitPrice,
                margin_rate: clamp(marginPercent / 100, -0.99, 10),
                tax_rate: taxRate,
            };
        }

        function enterEditMode(row) {
            if (row.dataset.editing === '1') return;
            row.dataset.editing = '1';
            row.dataset.originalHtml = row.innerHTML;

            const name = row.dataset.name || '';
            const quantity = parseNumber(row.dataset.quantity, 0);
            const unit = row.dataset.unit || '';
            const unitCost = parseNumber(row.dataset.unitCost, 0);
            const unitPrice = parseNumber(row.dataset.unitPrice, unitCost);
            const marginRate = parseNumber(row.dataset.marginRate, 0);
            const marginPercent = marginRate * 100;
            const taxRate = parseNumber(row.dataset.taxRate, 0);

            const cells = row.children;
            cells[1].innerHTML = `
                <input type="text" class="form-input w-full text-sm" data-edit-name value="${escapeHtml(name)}">
                <p class="text-xs text-gray-500 mt-1">Update name, pricing, and margins</p>
            `;
            cells[2].innerHTML = `
                <div class="flex items-center justify-center gap-2">
                    <input type="number" step="0.01" min="0" class="form-input w-24 text-sm text-center" data-edit-qty value="${quantity}">
                    <input type="text" class="form-input w-16 text-sm text-center" data-edit-unit value="${escapeHtml(unit)}">
                </div>
            `;
            cells[3].innerHTML = `<input type="number" step="0.01" min="0" class="form-input w-24 text-sm text-center" data-edit-unit-cost value="${unitCost.toFixed(2)}">`;
            cells[4].innerHTML = `<input type="number" step="0.01" min="0" class="form-input w-24 text-sm text-center" data-edit-unit-price value="${unitPrice.toFixed(2)}">`;
            cells[5].innerHTML = `
                <div class="flex flex-col items-center gap-1">
                    <input type="number" step="0.1" min="-99" class="form-input w-24 text-sm text-center" data-edit-margin value="${marginPercent.toFixed(2)}">
                    <span class="text-xs text-gray-500" data-edit-margin-preview>${formatMoney((unitPrice - unitCost) * quantity)} total</span>
                </div>
            `;
            cells[6].innerHTML = `<input type="number" step="0.001" min="0" class="form-input w-20 text-sm text-center" data-edit-tax value="${taxRate.toFixed(3)}">`;
            cells[7].innerHTML = `<span class="text-sm text-gray-500">Will update on save</span>`;
            cells[8].innerHTML = `
                <button class="text-green-700 hover:underline text-sm" data-action="save-item" data-item-id="${row.dataset.itemId}">Save</button>
                <button class="text-gray-600 hover:underline text-sm ml-2" data-action="cancel-edit" data-item-id="${row.dataset.itemId}">Cancel</button>
            `;

            bindRowFinancialInputs(row);
        }

        function bindRowFinancialInputs(row) {
            const qtyInput = row.querySelector('[data-edit-qty]');
            const costInput = row.querySelector('[data-edit-unit-cost]');
            const priceInput = row.querySelector('[data-edit-unit-price]');
            const marginInput = row.querySelector('[data-edit-margin]');
            const preview = row.querySelector('[data-edit-margin-preview]');

            if (priceInput && !priceInput.dataset.manualOverride) {
                priceInput.dataset.manualOverride = '0';
            }

            const sync = () => {
                const qty = parseNumber(qtyInput?.value, 0);
                const unitCost = parseNumber(costInput?.value, 0);
                let unitPrice = parseNumber(priceInput?.value, unitCost);
                let marginPercent = parseNumber(marginInput?.value, 0);
                const manualOverride = priceInput?.dataset.manualOverride === '1';

                if (!manualOverride) {
                    marginPercent = clamp(parseNumber(marginInput.value, 0), -99, 1000);
                    unitPrice = unitCost * (1 + (marginPercent / 100));
                    if (priceInput) priceInput.value = unitPrice.toFixed(2);
                } else {
                    unitPrice = parseNumber(priceInput.value, unitCost);
                    marginPercent = unitCost !== 0 ? clamp(((unitPrice - unitCost) / unitCost) * 100, -99, 1000) : 0;
                    if (marginInput) marginInput.value = marginPercent.toFixed(2);
                }

                if (preview) {
                    const marginTotal = qty * (unitPrice - unitCost);
                    preview.textContent = `${formatMoney(marginTotal)} total`;
                }
            };

            [qtyInput, costInput, marginInput].forEach(input => {
                if (!input) return;
                input.addEventListener('input', () => {
                    if (input === marginInput) {
                        if (priceInput) priceInput.dataset.manualOverride = '0';
                    }
                    sync();
                });
            });

            if (priceInput) {
                priceInput.addEventListener('input', () => {
                    priceInput.dataset.manualOverride = '1';
                    sync();
                });
            }

            sync();
        }

        // Area filtering chips
        (function initAreaChips(){
            const chips = document.querySelectorAll('[data-area-chip]');
            const tbody = document.querySelector('table tbody');
            function applyAreaFilter(val){
                chips.forEach(c=>c.classList.toggle('bg-blue-100', c.getAttribute('data-area-chip')===val));
                const rows = tbody ? tbody.querySelectorAll('tr[data-item-id]') : [];
                rows.forEach(row => {
                    const areaId = String(row.getAttribute('data-area-id') || '0');
                    const show = (val === 'all') || (areaId === val);
                    row.style.display = show ? '' : 'none';
                });
            }
            chips.forEach(chip => chip.addEventListener('click', () => applyAreaFilter(chip.getAttribute('data-area-chip'))));
            applyAreaFilter('all');
        })();

        // Set area via select
        document.addEventListener('change', async (event) => {
            const select = event.target.closest('select[data-action="set-area"]');
            if (!select) return;
            const id = select.getAttribute('data-item-id');
            const areaId = select.value || null;
            try {
                const response = await fetch("{{ url('estimates/'.$estimate->id.'/items') }}/" + id, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ area_id: areaId }),
                });
                if (!response.ok) throw await response.json().catch(()=>({}));
                const data = await response.json();
                showToast('Area updated', 'success');
                autoRefresh();
            } catch (e) {
                showToast('Failed to set area', 'error');
            }
        });


        window.showToast = function(message, type = 'info') {
            const colors = { success: 'bg-green-600', error: 'bg-red-600', info: 'bg-gray-800' };
            const el = document.createElement('div');
            el.className = `${colors[type] || colors.info} text-white px-4 py-2 rounded shadow fixed top-4 right-4 z-50 opacity-0 transition-opacity duration-300`;
            el.textContent = message;
            document.body.appendChild(el);
            requestAnimationFrame(() => el.classList.remove('opacity-0'));
            setTimeout(() => {
                el.classList.add('opacity-0');
                setTimeout(() => el.remove(), 300);
            }, 2500);
        }
    });
</script>
@endpush
