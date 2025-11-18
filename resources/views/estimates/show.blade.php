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
        pruning: '{{ route('calculators.pruning.form') }}'
    };
    window.__estimateTemplatesUrl = "{{ route('estimates.calculator.templates', $estimate) }}";
    window.__estimateImportUrl = "{{ route('estimates.calculator.import', $estimate) }}";
    window.__estimateItemsBaseUrl = "{{ url('estimates/'.$estimate->id.'/items') }}";
    window.__galleryUrl = "{{ route('calculator.templates.gallery') }}";
    window.__estimateSetup = {
        estimateId: {{ (int) $estimate->id }},
        areas: @json($estimate->areas->map(fn($a)=>['id'=>$a->id,'name'=>$a->name]))
    };
</script>
<!-- Page loading overlay -->
<div id="pageLoadingOverlay" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-white/70 backdrop-blur-sm"></div>
    <div class="absolute inset-0 flex items-center justify-center">
        <div class="h-10 w-10 animate-spin rounded-full border-4 border-brand-600 border-t-transparent"></div>
    </div>
</div>

<div class="space-y-6" x-data="{ tab: 'work', activeArea: 'all', showAddItems: false, addItemsTab: 'materials' }">
    <x-page-header title="{{ $estimate->title }}" eyebrow="Estimate" subtitle="{{ $estimate->client->name }} · {{ $estimate->property->name ?? 'No property' }}" variant="compact">
        <x-slot:leading>
            <div class="h-12 w-12 rounded-full bg-brand-600 text-white flex items-center justify-center text-lg font-semibold shadow-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6"><path d="M7 2h7l5 5v13a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/><path d="M14 2v5h5"/></svg>
            </div>
        </x-slot:leading>
        <x-slot:actions>
            <x-brand-button type="button" id="estimateRefreshBtn" variant="outline">Refresh</x-brand-button>
            <x-brand-button type="button" id="saveAllBtn" class="ml-2">Save All</x-brand-button>
            <x-brand-button href="{{ route('estimates.edit', $estimate) }}" variant="outline">Edit</x-brand-button>
            <form action="{{ route('estimates.destroy', $estimate) }}" method="POST" onsubmit="return confirm('Delete this estimate?');">
                @csrf
                @method('DELETE')
                <x-brand-button type="submit" variant="outline" class="border-red-300 text-red-700 hover:bg-red-50">Delete</x-brand-button>
            </form>
            <x-brand-button href="{{ route('estimates.preview-email', $estimate) }}" variant="outline">Preview Email</x-brand-button>
            <form action="{{ route('estimates.invoice', $estimate) }}" method="POST">
                @csrf
                <x-brand-button type="submit" variant="outline">Create Invoice</x-brand-button>
            </form>
            <x-brand-button href="{{ route('estimates.print', $estimate) }}" target="_blank" variant="outline">Print</x-brand-button>
            <x-brand-button type="button" id="openCalcDrawerBtn" class="ml-2">+ Add via Calculator</x-brand-button>
        </x-slot:actions>
    </x-page-header>

    <!-- Add via Calculator Slide-over (controlled by JS module) -->
    <div id="calcDrawer" class="fixed inset-0 z-40" style="display:none;" x-data="{ itemsTab: 'labor' }">
        <div id="calcDrawerOverlay" class="absolute inset-0 bg-black/30"></div>
        <div class="absolute right-0 top-0 h-full w-full sm:max-w-2xl bg-white shadow-xl flex flex-col">
            <div class="flex items-center justify-between px-4 py-3 border-b">
                <h3 class="text-lg font-semibold"></h3>
                <x-close-button id="calcDrawerCloseBtn" size="md" />
            </div>
            <div class="px-4 pt-3 border-b">
                <div class="inline-flex rounded-md border overflow-x-auto">
                    <button type="button" class="px-3 py-1.5 text-sm rounded-l-md hover:bg-gray-100 text-gray-700" :class="{ 'bg-gray-200 text-gray-900': itemsTab==='labor' }" @click="itemsTab='labor'">
                        <svg class="inline h-4 w-4 mr-1 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Z"/><path d="M6 20v-2a4 4 0 0 1 4-4h4"/></svg>
                        Labor
                    </button>
                    <button type="button" class="px-3 py-1.5 text-sm hover:bg-gray-100 text-gray-700 border-l" :class="{ 'bg-gray-200 text-gray-900': itemsTab==='equipment' }" @click="itemsTab='equipment'">
                        <svg class="inline h-4 w-4 mr-1 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10h4l3-3 4 6 3-3 4 0"/><path d="M14 19H6a3 3 0 0 1-3-3V7"/></svg>
                        Equipment
                    </button>
                    <button type="button" class="px-3 py-1.5 text-sm hover:bg-gray-100 text-gray-700 border-l" :class="{ 'bg-gray-200 text-gray-900': itemsTab==='materials' }" @click="itemsTab='materials'">
                        <svg class="inline h-4 w-4 mr-1 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/></svg>
                        Materials
                    </button>
                    <button type="button" class="px-3 py-1.5 text-sm hover:bg-gray-100 text-gray-700 border-l" :class="{ 'bg-gray-200 text-gray-900': itemsTab==='subs' }" @click="itemsTab='subs'">
                        <svg class="inline h-4 w-4 mr-1 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Subs
                    </button>
                    <button type="button" class="px-3 py-1.5 text-sm hover:bg-gray-100 text-gray-700 border-l" :class="{ 'bg-gray-200 text-gray-900': itemsTab==='other' }" @click="itemsTab='other'">
                        <svg class="inline h-4 w-4 mr-1 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v18"/><path d="M3 12h18"/></svg>
                        Other
                    </button>
                    <button type="button" class="px-3 py-1.5 text-sm rounded-r-md hover:bg-gray-100 text-gray-700 border-l" :class="{ 'bg-gray-200 text-gray-900': itemsTab==='templates' }" @click="itemsTab='templates'">
                        <svg class="inline h-4 w-4 mr-1 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18"/><path d="M3 9h18"/></svg>
                        Templates
                    </button>
                </div>
            </div>

            <!-- Templates Pane -->
            <div id="calcTemplatesPane" class="p-4 overflow-y-auto space-y-4" x-show="itemsTab==='templates'">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <label class="text-sm">Type:</label>
                        <select id="calcTypeSelectTpl" class="form-select w-48 border-brand-300 focus:ring-brand-500 focus:border-brand-500">
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
                    <div class="flex items-center gap-3">
                        <x-brand-button id="calcTplOpenGallery" href="#" target="_blank" variant="outline" size="sm">Go to Gallery</x-brand-button>
                        <x-secondary-button id="calcTplRefresh" size="sm">Refresh</x-secondary-button>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-medium">Calculator</label>
                    <select id="calcTypeSelect" class="form-select w-full sm:w-64 border-brand-300 focus:ring-brand-500 focus:border-brand-500">
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
                <div class="flex items-center gap-2">
                    <x-brand-button href="#" id="openTemplateModeLink">Open in Template Mode</x-brand-button>
                    <x-secondary-button as="a" href="#" id="openCreateLink" size="sm">Create</x-secondary-button>
                </div>
                <div id="calcTplLoading" class="text-sm text-gray-500" style="display:none;">Loading templates...</div>
                <div id="calcTplList" class="space-y-2"></div>
            </div>
            <!-- Items Pane (Labor, Equipment, Materials, Subs, Other) -->
            <div id="calcItemsPane" class="p-4 overflow-y-auto space-y-4" x-show="itemsTab!=='templates'">
                <!-- Labor List -->
                <div x-show="itemsTab==='labor'" class="space-y-2">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-semibold">Labor Catalog</h4>
                        <x-brand-button type="button" size="sm" @click="$dispatch('open-modal','new-labor')">New</x-brand-button>
                    </div>
                    <div class="max-h-60 overflow-y-auto border rounded bg-white divide-y">
                        @foreach ($laborCatalog as $labor)
                            <div class="px-3 py-2 text-sm flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-gray-900">{{ $labor->name }}</div>
                                    <div class="text-xs text-gray-500">{{ ucfirst($labor->type) }} • {{ $labor->unit }}</div>
                                </div>
                                <div class="text-xs text-gray-600">Base: ${{ number_format($labor->base_rate, 2) }}</div>
                            </div>
                        @endforeach
                        @if($laborCatalog->isEmpty())
                            <div class="px-3 py-3 text-sm text-gray-500">No labor items yet.</div>
                        @endif
                    </div>
                </div>
                <!-- Materials List -->
                <div x-show="itemsTab==='materials'" class="space-y-2">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-semibold">Materials Catalog</h4>
                        <x-brand-button type="button" size="sm" @click="$dispatch('open-modal','new-material')">New</x-brand-button>
                    </div>
                    <div class="max-h-60 overflow-y-auto border rounded bg-white divide-y">
                        @foreach ($materials as $material)
                            <div class="px-3 py-2 text-sm flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-gray-900">{{ $material->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $material->unit }}</div>
                                </div>
                                <div class="text-xs text-gray-600">Cost: ${{ number_format($material->unit_cost, 2) }}</div>
                            </div>
                        @endforeach
                        @if($materials->isEmpty())
                            <div class="px-3 py-3 text-sm text-gray-500">No materials yet.</div>
                        @endif
                    </div>
                </div>
                <!-- Placeholders -->
                <div x-show="itemsTab==='equipment'" class="text-sm text-gray-600">Equipment list coming soon.</div>
                <div x-show="itemsTab==='subs'" class="text-sm text-gray-600">Subcontractors list coming soon.</div>
                <div x-show="itemsTab==='other'" class="text-sm text-gray-600">Other items coming soon.</div>
            </div>
        </div>
    </div>

    <!-- Tabs Bar -->
    <div class="bg-white rounded shadow p-3">
        <div class="inline-flex rounded-md border bg-gray-50 overflow-x-auto">
            <button class="px-3 py-1.5 text-base rounded-l-md hover:bg-gray-100 text-gray-700" :class="{ 'bg-gray-200 text-gray-900' : tab==='overview' }" @click="tab='overview'">Customer Info</button>
            <button class="px-3 py-1.5 text-base hover:bg-gray-100 text-gray-700 border-l" :class="{ 'bg-gray-200 text-gray-900' : tab==='work' }" @click="tab='work'">Work & Pricing</button>
            <button class="px-3 py-1.5 text-base hover:bg-gray-100 text-gray-700 border-l" :class="{ 'bg-gray-200 text-gray-900' : tab==='notes' }" @click="tab='notes'">Client Notes</button>
            <button class="px-3 py-1.5 text-base hover:bg-gray-100 text-gray-700 border-l" :class="{ 'bg-gray-200 text-gray-900' : tab==='crew' }" @click="tab='crew'">Crew Notes</button>
            <button class="px-3 py-1.5 text-base hover:bg-gray-100 text-gray-700 border-l" :class="{ 'bg-gray-200 text-gray-900' : tab==='analysis' }" @click="tab='analysis'">Analysis</button>
            <button class="px-3 py-1.5 text-base rounded-r-md hover:bg-gray-100 text-gray-700 border-l" :class="{ 'bg-gray-200 text-gray-900' : tab==='files' }" @click="tab='files'">Files</button>
        </div>
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
                              'bg-brand-100 text-brand-700' => $estimate->status === 'sent',
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
                        <a href="{{ route('clients.site-visits.show', [$estimate->client, $estimate->siteVisit]) }}" class="inline-block mt-2 text-xs text-brand-700 hover:text-brand-900">Open Visit</a>
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
                            <input type="text" name="title" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="{{ old('title', $estimate->title) }}" required>
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
                                <select name="status" class="form-select w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500">
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status }}" @selected(old('status', $estimate->status ?? 'draft') === $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Expires On</label>
                                <input type="date" name="expires_at" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="{{ old('expires_at', optional($estimate->expires_at ?? null)->format('Y-m-d')) }}">
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <x-brand-button type="submit">Save</x-brand-button>
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

    <section class="bg-white rounded-lg shadow p-6 space-y-4" x-show="tab==='notes'">
        <form method="POST" action="{{ route('estimates.update', $estimate) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700">Client Notes</label>
                <textarea name="notes" rows="6" class="form-textarea w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500">{{ old('notes', $estimate->notes) }}</textarea>
                @error('notes')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Terms & Conditions</label>
                <textarea name="terms" rows="6" class="form-textarea w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500">{{ old('terms', $estimate->terms) }}</textarea>
                @error('terms')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex justify-end">
                <x-brand-button type="submit">Save</x-brand-button>
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



        <x-modal name="add-work-area" maxWidth="lg">
            <div class="border-b px-4 py-3">
                <h3 class="text-lg font-semibold">Add Work Area</h3>
            </div>
            <div class="p-4">
                <form method="POST" action="{{ route('estimates.areas.store', $estimate) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Work Area Name</label>
                        <input type="text" name="name" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Work Area Identifier (optional)</label>
                        <input type="text" name="identifier" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" placeholder="e.g., A1, Zone 3">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Cost Code</label>
                        <select name="cost_code_id" class="form-select w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500">
                            <option value="">—</option>
                            @foreach (($costCodes ?? []) as $cc)
                                <option value="{{ $cc->id }}">{{ $cc->code }} - {{ $cc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="description" rows="3" class="form-textarea w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" placeholder="Details or special instructions for this area"></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'add-work-area')">Cancel</x-secondary-button>
                        <x-brand-button type="submit">Save Area</x-brand-button>
                    </div>
                </form>
            </div>
        </x-modal>

        @php $allItems = $estimate->items; @endphp
        <div class="rounded-lg border border-gray-200 bg-slate-300 shadow-sm overflow-hidden">
                                <div class="px-4 py-2 border-b border-slate-200 bg-slate-100">
                        <x-brand-button type="button" size="sm"
                            @click="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'add-work-area' }))">
                            + Add Work Area
                        </x-brand-button>
                    </div>
            <div id="areasContainer" class="p-2 space-y-6">
        @forelse ($estimate->areas as $area)
            @php
                $areaItems = $allItems->where('area_id', $area->id);
                $laborHours = $areaItems->where('item_type', 'labor')->sum('quantity');
                $cogs = $areaItems->filter(fn($i) => in_array($i->item_type, ['labor','material']))->sum('cost_total');
                $price = $areaItems->sum('line_total');
                $profit = $price - $cogs;
            @endphp
            <div x-data="{ open: true, tab: 'pricing', menuOpen: false }" class="mb-6 border rounded-lg bg-white work-area overflow-visible" data-area-id="{{ $area->id }}" data-sort-order="{{ $area->sort_order ?? $loop->iteration }}">
                    <div class="px-4 py-3 border-b border-slate-200 bg-slate-100">
                        <form method="POST" action="{{ route('estimates.areas.update', [$estimate, $area]) }}" class="flex flex-wrap items-start gap-3">
                            @csrf
                            @method('PATCH')
                            <div class="relative inline-block text-left shrink-0">
                                <button type="button" class="text-xs px-2 py-1 rounded border" @click.stop="menuOpen = !menuOpen" @keydown.escape.window="menuOpen = false">
                                    Options
                                </button>
                                <div x-cloak x-show="menuOpen" x-transition @click.away="menuOpen = false"
                                     class="absolute z-20 mt-1 min-w-[9rem] left-0 bg-white border rounded-md shadow-lg text-sm py-1 ring-1 ring-black/5">
                                    <button type="button" class="block w-full text-left px-3 py-1 hover:bg-gray-100" @click="open = true; menuOpen = false">Edit</button>
                                    <button type="button" class="block w-full text-left px-3 py-1 hover:bg-gray-100" @click="open = false; menuOpen = false">Close</button>
                                    <div class="my-1 border-t border-slate-200"></div>
                                    <button type="button" class="block w-full text-left px-3 py-1 text-red-600 hover:bg-red-50" @click.prevent="$refs.deleteForm.submit()">Delete</button>
                                </div>
                            </div>
                            <div class="w-16">
                                <label class="block text-xs font-medium text-gray-600">Order</label>
                                <input type="number" name="sort_order" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="{{ $area->sort_order ?? $loop->iteration }}">
                            </div>
                            <div class="w-full sm:w-80">
                                <label class="block text-xs font-medium text-gray-600">Name</label>
                                <input type="text" name="name" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="{{ $area->name }}">
                            </div>
                            <div class="w-28">
                                <label class="block text-xs font-medium text-gray-600">Id</label>
                                <input type="text" name="identifier" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="{{ $area->identifier }}">
                            </div>
                            <div class="w-64">
                                <label class="block text-xs font-medium text-gray-600">Cost Code</label>
                                <select name="cost_code_id" class="form-select w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500">
                                    <option value="">—</option>
                                    @foreach (($costCodes ?? []) as $cc)
                                        <option value="{{ $cc->id }}" @selected($area->cost_code_id === $cc->id)>{{ $cc->code }} - {{ $cc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex flex-wrap items-center gap-6 w-auto pt-1">
                                <div class="flex items-baseline gap-2">
                                    <span class="text-xs uppercase tracking-wide text-gray-500">Hrs</span>
                                    <span class="text-base font-semibold text-gray-900">{{ number_format($laborHours, 2) }}</span>
                                </div>
                                <span class="text-gray-300">•</span>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-xs uppercase tracking-wide text-gray-500">COGS</span>
                                    <span class="text-base font-semibold text-gray-900">${{ number_format($cogs, 2) }}</span>
                                </div>
                                <span class="text-gray-300">•</span>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-xs uppercase tracking-wide text-gray-500">Price</span>
                                    <span class="text-base font-semibold text-gray-900">${{ number_format($price, 2) }}</span>
                                </div>
                                <span class="text-gray-300">•</span>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-xs uppercase tracking-wide text-gray-500">Profit</span>
                                    <span class="text-base font-semibold text-gray-900">${{ number_format($profit, 2) }}</span>
                                </div>
                            </div>
                            <div class="flex items-center pt-1 ml-auto">
                                <x-brand-button type="button" size="sm" @click="document.getElementById('openCalcDrawerBtn') && document.getElementById('openCalcDrawerBtn').click()">Add Items + Templates</x-brand-button>
                            </div>
                        </form>
                        <form x-ref="deleteForm" method="POST" action="{{ route('estimates.areas.destroy', [$estimate, $area]) }}" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                    <div x-show="open" class="px-4 pt-3">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="inline-flex rounded-md border">
                                <button type="button" class="px-3 py-1.5 text-sm rounded-l-md hover:bg-gray-100 text-gray-700" :class="{ 'bg-gray-200 text-gray-900': tab==='pricing' }" @click="tab='pricing'">
                                    <svg class="inline h-4 w-4 mr-1 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    Edit Pricing
                                </button>
                                <button type="button" class="px-3 py-1.5 text-sm rounded-r-md hover:bg-gray-100 text-gray-700 border-l" :class="{ 'bg-gray-200 text-gray-900': tab==='notes' }" @click="tab='notes'">
                                    <svg class="inline h-4 w-4 mr-1 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h9l7 7v9a2 2 0 0 1-2 2z"/><path d="M17 21v-8h-6"/></svg>
                                    Edit Notes
                                </button>
                            </div>
                        </div>
                        <div x-show="tab==='pricing'" class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                                    <tr>
                                        <th class="text-left px-3 py-2">Name</th>
                                        <th class="text-center px-3 py-2">Qty</th>
                                        <th class="text-center px-3 py-2">Units</th>
                                        <th class="text-center px-3 py-2">Unit Cost</th>
                                        <th class="text-center px-3 py-2">Unit Price</th>
                                        <th class="text-center px-3 py-2">Profit</th>
                                        <th class="text-center px-3 py-2">Total Cost</th>
                                        <th class="text-right px-3 py-2">Total Price</th>
                                        <th class="px-3 py-2"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($areaItems as $item)
                                        @php $rowProfit = $item->margin_total; @endphp
                                        <tr class="border-t">
                                            <td class="px-3 py-2">
                                                <form method="POST" action="{{ route('estimates.items.update', [$estimate, $item]) }}" class="contents">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="area_id" value="{{ $area->id }}">
                                                    <input type="text" name="name" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="{{ $item->name }}">
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                    <input type="number" step="0.01" min="0" name="quantity" class="form-input w-24 mx-auto border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="{{ $item->quantity }}">
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                    <input type="text" name="unit" class="form-input w-24 mx-auto border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="{{ $item->unit }}">
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                    <input type="number" step="0.01" min="0" name="unit_cost" class="form-input w-28 mx-auto border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="{{ $item->unit_cost }}">
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                    <input type="number" step="0.01" min="0" name="unit_price" class="form-input w-28 mx-auto border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="{{ $item->unit_price }}">
                                            </td>
                                            <td class="px-3 py-2 text-center text-gray-700">
                                                ${{ number_format($rowProfit, 2) }}
                                            </td>
                                            <td class="px-3 py-2 text-center text-gray-700">
                                                ${{ number_format($item->cost_total, 2) }}
                                            </td>
                                            <td class="px-3 py-2 text-right font-semibold text-gray-900">
                                                ${{ number_format($item->line_total, 2) }}
                                            </td>
                                            <td class="px-3 py-2 text-right space-x-2">
                                                    <x-brand-button type="submit" size="sm" variant="outline">Save</x-brand-button>
                                                </form>
                                                <form action="{{ route('estimates.items.destroy', [$estimate, $item]) }}" method="POST" class="inline" onsubmit="return confirm('Remove this line item?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-danger-button size="sm" type="submit">Delete</x-danger-button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="px-3 py-4 text-sm text-gray-500">No items in this work area yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div x-show="tab==='notes'" class="pb-4">
                            <form method="POST" action="{{ route('estimates.areas.update', [$estimate, $area]) }}" class="space-y-2">
                                @csrf
                                @method('PATCH')
                                <label class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea name="description" rows="5" class="form-textarea w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500">{{ old('description', $area->description) }}</textarea>
                                <p class="text-xs text-gray-500">Use “Save All” at the top to save changes.</p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-500 p-4">No work areas yet. Use “Add Work Area” to create one.</p>
        @endforelse
            </div>
        </div>
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
                <!-- Add Items Tabs -->
                <div class="mb-3">
                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="px-3 py-1 text-sm rounded border border-transparent hover:bg-brand-50" :class="{ 'bg-brand-600 text-white border-brand-600': addItemsTab==='labor' }" @click="addItemsTab='labor'">Labor</button>
                        <button type="button" class="px-3 py-1 text-sm rounded border border-transparent hover:bg-brand-50" :class="{ 'bg-brand-600 text-white border-brand-600': addItemsTab==='equipment' }" @click="addItemsTab='equipment'">Equipment</button>
                        <button type="button" class="px-3 py-1 text-sm rounded border border-transparent hover:bg-brand-50" :class="{ 'bg-brand-600 text-white border-brand-600': addItemsTab==='materials' }" @click="addItemsTab='materials'">Materials</button>
                        <button type="button" class="px-3 py-1 text-sm rounded border border-transparent hover:bg-brand-50" :class="{ 'bg-brand-600 text-white border-brand-600': addItemsTab==='subs' }" @click="addItemsTab='subs'">Subs</button>
                        <button type="button" class="px-3 py-1 text-sm rounded border border-transparent hover:bg-brand-50" :class="{ 'bg-brand-600 text-white border-brand-600': addItemsTab==='other' }" @click="addItemsTab='other'">Other</button>
                        <button type="button" class="px-3 py-1 text-sm rounded border border-transparent hover:bg-brand-50" :class="{ 'bg-brand-600 text-white border-brand-600': addItemsTab==='templates' }" @click="addItemsTab='templates'">Templates</button>
                    </div>
                </div>

                <!-- Equipment tab -->
                <div x-show="addItemsTab==='equipment'" class="bg-white rounded-lg border p-4 space-y-4">
                    <h4 class="text-md font-semibold">Add Equipment</h4>
                    <form method="POST" action="{{ route('estimates.items.store', $estimate) }}" class="space-y-3" id="equipmentCatalogForm" data-form-type="custom">
                        @csrf
                        <input type="hidden" name="item_type" value="fee">
                        <input type="hidden" name="catalog_type" value="equipment">
                        <div>
                            <label class="block text-sm font-semibold mb-1">Equipment</label>
                            <input type="text" class="form-input w-full mb-2 text-sm border-brand-300 focus:ring-brand-500 focus:border-brand-500" placeholder="Search equipment..." data-role="filter">
                            <select name="catalog_id" class="form-select w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" data-role="equipment-select">
                                <option value="">Select equipment</option>
                                @foreach ($equipment ?? \App\Models\Asset::orderBy('name')->get() as $equip)
                                    <option value="{{ $equip->id }}" data-unit="day" data-cost="0">{{ $equip->name }} ({{ $equip->type }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-semibold mb-1">Days</label>
                                <input type="number" step="0.1" min="0" name="quantity" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="1" required>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1">Daily Cost ($)</label>
                                <input type="number" step="0.01" min="0" name="unit_cost" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="0" required>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-semibold mb-1">Margin %</label>
                                <input type="number" step="0.1" min="-99" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="{{ number_format($defaultMarginPercent ?? 20, 1) }}" data-role="margin-percent">
                                <input type="hidden" name="margin_rate" value="{{ number_format($defaultMarginRate ?? 0.2, 4) }}" data-role="margin-rate">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1">Daily Price ($)</label>
                                <input type="number" step="0.01" min="0" name="unit_price" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="0" data-role="unit-price">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-semibold mb-1">Unit Label</label>
                                <input type="text" name="unit" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="day">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1">Tax Rate</label>
                                <input type="number" step="0.001" min="0" name="tax_rate" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="0">
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <x-brand-button type="submit" disabled>Add Equipment</x-brand-button>
                            <span class="text-xs text-gray-500" data-role="preview-total">Line total: $0.00</span>
                        </div>
                    </form>
                </div>

                <!-- Subs tab -->
                <div x-show="addItemsTab==='subs'" class="bg-white rounded-lg border p-4 space-y-4">
                    <h4 class="text-md font-semibold">Add Subcontractor</h4>
                    <form method="POST" action="{{ route('estimates.items.store', $estimate) }}" class="space-y-3" id="subsCatalogForm" data-form-type="custom">
                        @csrf
                        <input type="hidden" name="item_type" value="fee">
                        <input type="hidden" name="catalog_type" value="subcontractor">
                        <div>
                            <label class="block text-sm font-semibold mb-1">Vendor</label>
                            <input type="text" class="form-input w-full mb-2 text-sm border-brand-300 focus:ring-brand-500 focus:border-brand-500" placeholder="Search vendors..." data-role="filter">
                            <select name="catalog_id" class="form-select w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" data-role="subs-select">
                                <option value="">Select vendor</option>
                                @foreach (($vendors ?? collect()) as $vendor)
                                    <option value="{{ $vendor->id }}" data-unit="job" data-cost="0">{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1">Description (optional)</label>
                            <input type="text" name="name" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" placeholder="e.g., Hauling and disposal">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-semibold mb-1">Quantity</label>
                                <input type="number" step="0.01" min="0" name="quantity" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="1" required>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1">Unit Cost ($)</label>
                                <input type="number" step="0.01" min="0" name="unit_cost" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="0" required>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-semibold mb-1">Margin %</label>
                                <input type="number" step="0.1" min="-99" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="{{ number_format($defaultMarginPercent ?? 20, 1) }}" data-role="margin-percent">
                                <input type="hidden" name="margin_rate" value="{{ number_format($defaultMarginRate ?? 0.2, 4) }}" data-role="margin-rate">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1">Unit Price ($)</label>
                                <input type="number" step="0.01" min="0" name="unit_price" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="0" data-role="unit-price">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-semibold mb-1">Unit Label</label>
                                <input type="text" name="unit" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="job">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1">Tax Rate</label>
                                <input type="number" step="0.001" min="0" name="tax_rate" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="0">
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <x-brand-button type="submit" disabled>Add Subcontractor Fee</x-brand-button>
                            <span class="text-xs text-gray-500" data-role="preview-total">Line total: $0.00</span>
                        </div>
                    </form>
                </div>

                <!-- Templates redirect -->
                <div x-show="addItemsTab==='templates'" class="bg-white rounded-lg border p-4 space-y-3">
                    <h4 class="text-md font-semibold">Templates</h4>
                    <p class="text-sm text-gray-600">Open the Templates drawer to import saved calculator templates into this estimate.</p>
                    <x-brand-button type="button" size="sm" @click="showAddItems=false; document.getElementById('openCalcDrawerBtn')?.click();">Open Templates Drawer</x-brand-button>
                </div>
                <div x-show="addItemsTab==='materials'" class="bg-white rounded-lg border p-4 space-y-4">
                    <h4 class="text-md font-semibold">Add Material from Catalog</h4>
            <h3 class="text-lg font-semibold">Add Material from Catalog</h3>
            <form method="POST" action="{{ route('estimates.items.store', $estimate) }}" class="space-y-3" id="materialCatalogForm" data-form-type="material">
                @csrf
                <input type="hidden" name="item_type" value="material">
                <input type="hidden" name="catalog_type" value="material">
                <div>
                    <label class="block text-sm font-semibold mb-1">Material</label>
                    <input type="text" class="form-input w-full mb-2 text-sm border-brand-300 focus:ring-brand-500 focus:border-brand-500" placeholder="Search materials..." data-role="filter">
                    <select name="catalog_id" class="form-select w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" data-role="material-select">
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
                        <input type="number" step="0.01" min="0" name="quantity" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="1" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Unit Cost ($)</label>
                        <input type="number" step="0.01" min="0" name="unit_cost" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="0" required data-role="material-cost">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Margin %</label>
                        <input type="number" step="0.1" min="-99" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="{{ number_format($defaultMarginPercent ?? 20, 1) }}" data-role="margin-percent">
                        <input type="hidden" name="margin_rate" value="{{ number_format($defaultMarginRate ?? 0.2, 4) }}" data-role="margin-rate">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Unit Price ($)</label>
                        <input type="number" step="0.01" min="0" name="unit_price" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="0" data-role="unit-price">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Unit Label</label>
                        <input type="text" name="unit" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="" data-role="material-unit">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Tax Rate</label>
                        <input type="number" step="0.001" min="0" name="tax_rate" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="0" data-role="material-tax">
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <x-brand-button type="submit" disabled>Add Material</x-brand-button>
                    <span class="text-xs text-gray-500" data-role="preview-total">Line total: $0.00</span>
                </div>
                @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                @error('unit_cost')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
            </form>
        </div>

                <div x-show="addItemsTab==='labor'" class="bg-white rounded-lg border p-4 space-y-4">
                    <h4 class="text-md font-semibold">Add Labor from Catalog</h4>
            <form method="POST" action="{{ route('estimates.items.store', $estimate) }}" class="space-y-3" id="laborCatalogForm" data-form-type="labor">
                @csrf
                <input type="hidden" name="item_type" value="labor">
                <input type="hidden" name="catalog_type" value="labor">
                <div>
                    <label class="block text-sm font-semibold mb-1">Labor</label>
                    <input type="text" class="form-input w-full mb-2 text-sm border-brand-300 focus:ring-brand-500 focus:border-brand-500" placeholder="Search labor..." data-role="filter">
                    <select name="catalog_id" class="form-select w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" data-role="labor-select">
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
                        <input type="number" step="0.01" min="0" name="quantity" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="1" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Unit Cost ($)</label>
                        <input type="number" step="0.01" min="0" name="unit_cost" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="0" required data-role="labor-cost">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Margin %</label>
                        <input type="number" step="0.1" min="-99" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="{{ number_format($defaultMarginPercent ?? 20, 1) }}" data-role="margin-percent">
                        <input type="hidden" name="margin_rate" value="{{ number_format($defaultMarginRate ?? 0.2, 4) }}" data-role="margin-rate">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Unit Price ($)</label>
                        <input type="number" step="0.01" min="0" name="unit_price" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="0" data-role="unit-price">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Unit Label</label>
                        <input type="text" name="unit" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="" data-role="labor-unit">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Tax Rate</label>
                        <input type="number" step="0.001" min="0" name="tax_rate" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="0">
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <x-brand-button type="submit" disabled>Add Labor</x-brand-button>
                    <span class="text-xs text-gray-500" data-role="preview-total">Line total: $0.00</span>
                </div>
                @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                @error('unit_cost')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
            </form>
        </div>

                <div x-show="addItemsTab==='other'" class="bg-white rounded-lg border p-4 space-y-4">
                    <h4 class="text-md font-semibold">Add Custom Line Item</h4>
            <form method="POST" action="{{ route('estimates.items.store', $estimate) }}" class="space-y-3" id="customItemForm" data-form-type="custom">
                @csrf
                <div>
                    <label class="block text-sm font-semibold mb-1">Type</label>
                    <select name="item_type" class="form-select w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500">
                        <option value="material">Material</option>
                        <option value="labor">Labor</option>
                        <option value="fee">Fee</option>
                        <option value="discount">Discount</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Name</label>
                    <input type="text" name="name" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Description</label>
                    <textarea name="description" rows="2" class="form-textarea w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Quantity</label>
                        <input type="number" step="0.01" min="0" name="quantity" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="1" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Unit Cost ($)</label>
                        <input type="number" step="0.01" min="0" name="unit_cost" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="0" required>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Margin %</label>
                        <input type="number" step="0.1" min="-99" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="{{ number_format($defaultMarginPercent ?? 20, 1) }}" data-role="margin-percent">
                        <input type="hidden" name="margin_rate" value="{{ number_format($defaultMarginRate ?? 0.2, 4) }}" data-role="margin-rate">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Unit Price ($)</label>
                        <input type="number" step="0.01" min="0" name="unit_price" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="0" data-role="unit-price">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Unit Label</label>
                        <input type="text" name="unit" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Tax Rate</label>
                        <input type="number" step="0.001" min="0" name="tax_rate" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="0">
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <x-brand-button type="submit" disabled>Add Custom Item</x-brand-button>
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
                <a href="{{ Storage::disk('public')->url($estimate->invoice->pdf_path) }}" class="text-brand-700 hover:text-brand-900 text-sm">Download Invoice</a>
            @endif
        @else
            <p class="text-sm text-gray-500">No invoice generated yet. Use the button above to create one.</p>
        @endif
    </section>

<!-- New Labor Modal -->
<x-modal name="new-labor" maxWidth="xl">
    <div class="border-b px-4 py-3 flex items-center justify-between">
        <h3 class="text-lg font-semibold">New Labor Item</h3>
        <x-close-button @click="$dispatch('close-modal','new-labor')" />
    </div>
    <div class="p-4">
        <form method="POST" action="{{ route('labor.store') }}" class="space-y-4">
            @csrf
            @include('labor._form')
            <div class="flex items-center justify-end gap-2">
                <x-secondary-button type="button" @click="$dispatch('close-modal','new-labor')">Cancel</x-secondary-button>
                <x-brand-button type="submit">Save</x-brand-button>
            </div>
        </form>
    </div>
</x-modal>

<!-- New Material Modal -->
<x-modal name="new-material" maxWidth="xl">
    <div class="border-b px-4 py-3 flex items-center justify-between">
        <h3 class="text-lg font-semibold">New Material</h3>
        <x-close-button @click="$dispatch('close-modal','new-material')" />
    </div>
    <div class="p-4">
        <form method="POST" action="{{ route('materials.store') }}" class="space-y-4">
            @csrf
            @include('materials._form')
            <div class="flex items-center justify-end gap-2">
                <x-secondary-button type="button" @click="$dispatch('close-modal','new-material')">Cancel</x-secondary-button>
                <x-brand-button type="submit">Save</x-brand-button>
            </div>
        </form>
    </div>
</x-modal>

</div>
@endsection

@push('scripts')
<script>
// Minimal wiring to open/close the Calculator drawer and switch tabs (no network calls)
(function(){
  function init(){
    var drawer = document.getElementById('calcDrawer');
    if (!drawer) return;
    var overlay = document.getElementById('calcDrawerOverlay');
    var openBtn = document.getElementById('openCalcDrawerBtn');
    var closeBtn = document.getElementById('calcDrawerCloseBtn');
    var createPane = document.getElementById('calcCreatePane');
    var templatesPane = document.getElementById('calcTemplatesPane');
    var tabCreate = document.getElementById('calcTabCreateBtn');
    var tabTemplates = document.getElementById('calcTabTemplatesBtn');

    function setTab(which){
      var createActive = which === 'create';
      var tplActive = which === 'templates';
      if (createPane) createPane.style.display = createActive ? '' : 'none';
      if (templatesPane) templatesPane.style.display = tplActive ? '' : 'none';
      if (tabCreate) tabCreate.classList.toggle('bg-gray-100', createActive);
      if (tabTemplates) tabTemplates.classList.toggle('bg-gray-100', tplActive);
    }
    function onKey(e){ if (e.key === 'Escape') closeDrawer(); }
    function openDrawer(defaultTab){ if (!drawer) return; drawer.style.display='block'; setTab(defaultTab||'templates'); document.addEventListener('keydown', onKey); }
    function closeDrawer(){ if (!drawer) return; drawer.style.display='none'; document.removeEventListener('keydown', onKey); }

    if (openBtn) openBtn.addEventListener('click', function(){ openDrawer('templates'); });
    if (overlay) overlay.addEventListener('click', closeDrawer);
    if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
    if (tabCreate) tabCreate.addEventListener('click', function(){ setTab('create'); });
    if (tabTemplates) tabTemplates.addEventListener('click', function(){ setTab('templates'); });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
</script>
@endpush

@push('scripts')
<script>
// Minimal init only; disable heavy inline JS to restore rendering
window.estimatePage = function(){ return { tab: 'work', activeArea: 'all', showAddItems: false }; };

/* Disabled interactive script for stability
// document.addEventListener('DOMContentLoaded', () => {
        // Spinner + auto-refresh helpers
        const overlay = document.getElementById('pageLoadingOverlay');
        function showPageSpinner(){ if (overlay) overlay.classList.remove('hidden'); }
        function hidePageSpinner(){ if (overlay) overlay.classList.add('hidden'); }
        function autoRefresh(delay = 150){ showPageSpinner(); setTimeout(() => window.location.reload(), delay); }

        // ===============
        // Add via Calculator Drawer wiring
        // ===============
        (function initCalcDrawer(){
            const drawer = document.getElementById('calcDrawer');
            const overlayEl = document.getElementById('calcDrawerOverlay');
            const openBtn = document.getElementById('openCalcDrawerBtn');
            const closeBtn = document.getElementById('calcDrawerCloseBtn');
            const createPane = document.getElementById('calcCreatePane');
            const templatesPane = document.getElementById('calcTemplatesPane');
            const tabCreate = document.getElementById('calcTabCreateBtn');
            const tabTemplates = document.getElementById('calcTabTemplatesBtn');
            const typeSelectCreate = document.getElementById('calcTypeSelect');
            const typeSelectTpl = document.getElementById('calcTypeSelectTpl');
            const openTemplateModeLink = document.getElementById('openTemplateModeLink');
            const listEl = document.getElementById('calcTplList');
            const loadingEl = document.getElementById('calcTplLoading');
            const refreshBtnTpl = document.getElementById('calcTplRefresh');
            const openGalleryLink = document.getElementById('calcTplOpenGallery');

            const estimateId = window.__estimateSetup?.estimateId;
            const routes = window.__calcRoutes || {};

            function setTab(which){
                if (!drawer) return;
                const activeCreate = which === 'create';
                const activeTemplates = which === 'templates';
                if (createPane) createPane.style.display = activeCreate ? '' : 'none';
                if (templatesPane) templatesPane.style.display = activeTemplates ? '' : 'none';
                if (tabCreate) tabCreate.classList.toggle('bg-gray-100', activeCreate);
                if (tabTemplates) tabTemplates.classList.toggle('bg-gray-100', activeTemplates);
                if (activeTemplates) {
                    updateGalleryLink();
                    loadTemplates();
                }
            }

            function openDrawer(defaultTab = 'templates'){
                if (!drawer) return;
                drawer.style.display = 'block';
                setTab(defaultTab);
                updateOpenTemplateLink();
                document.addEventListener('keydown', onKeydown);
            }

            function closeDrawer(){
                if (!drawer) return;
                drawer.style.display = 'none';
                document.removeEventListener('keydown', onKeydown);
            }

            function onKeydown(e){ if (e.key === 'Escape') closeDrawer(); }

            function updateOpenTemplateLink(){
                const type = (typeSelectCreate?.value || 'mulching');
                const base = routes[type];
                if (openTemplateModeLink) {
                    if (base) {
                        const sep = base.includes('?') ? '&' : '?';
                        openTemplateModeLink.href = `${base}${sep}mode=template&estimate_id=${encodeURIComponent(estimateId)}`;
                        openTemplateModeLink.classList.remove('opacity-50','pointer-events-none');
                        openTemplateModeLink.setAttribute('aria-disabled','false');
                    } else {
                        openTemplateModeLink.href = '#';
                        openTemplateModeLink.classList.add('opacity-50','pointer-events-none');
                        openTemplateModeLink.setAttribute('aria-disabled','true');
                    }
                }
            }

            function updateGalleryLink(){
                const type = (typeSelectTpl?.value || '').trim();
                if (openGalleryLink) {
                    const base = window.__galleryUrl || '#';
                    openGalleryLink.href = type ? `${base}?type=${encodeURIComponent(type)}` : base;
                }
            }

            async function loadTemplates(){
                if (!listEl || !loadingEl) return;
                const type = (typeSelectTpl?.value || 'mulching');
                listEl.innerHTML = '';
                loadingEl.style.display = '';
                updateGalleryLink();
                try {
                    const url = `${window.__estimateTemplatesUrl}?type=${encodeURIComponent(type)}`;
                    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) throw new Error('Failed to load templates');
                    const json = await res.json();
                    const templates = json.templates || [];
                    if (!templates.length) {
                        listEl.innerHTML = '<p class="text-sm text-gray-500">No templates yet for this type.</p>';
                    } else {
                        templates.forEach(t => listEl.appendChild(renderTemplateRow(t)));
                    }
                } catch (e) {
                    listEl.innerHTML = '<p class="text-sm text-red-600">Error loading templates.</p>';
                } finally {
                    loadingEl.style.display = 'none';
                }
            }

            function escapeHtml(str){
                const div = document.createElement('div');
                div.textContent = String(str ?? '');
                return div.innerHTML;
            }
            function renderTemplateRow(t){
                const wrap = document.createElement('div');
                wrap.className = 'flex items-center justify-between border rounded p-2';
                const dt = t.created_at ? new Date(t.created_at) : null;
                const dateTxt = dt ? dt.toLocaleString() : '';
                wrap.innerHTML = `
                    <div>
                        <div class="font-medium">${escapeHtml(t.template_name || '(Untitled)')}</div>
                        <div class="text-xs text-gray-500">${dateTxt}</div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button class="px-2 py-1 text-xs rounded border hover:bg-gray-50" data-role="tpl-append" data-id="${t.id}">Import (Append)</button>
                        <button class="px-2 py-1 text-xs rounded border hover:bg-gray-50" data-role="tpl-replace" data-id="${t.id}">Import (Replace)</button>
                    </div>
                `;
                wrap.querySelector('[data-role="tpl-append"]').addEventListener('click', () => importTemplate(t.id, false));
                wrap.querySelector('[data-role="tpl-replace"]').addEventListener('click', () => importTemplate(t.id, true));
                return wrap;
            }

            async function importTemplate(templateId, replaceFlag){
                try {
                    showPageSpinner();
                    const res = await fetch(window.__estimateImportUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ template_id: templateId, replace: !!replaceFlag })
                    });
                    if (!res.ok) throw await res.json().catch(()=>({ message: 'Import failed' }));
                    const data = await res.json();
                    if (data?.totals) window.updateSummary?.(data.totals);
                    showToast('Template imported', 'success');
                    closeDrawer();
                    autoRefresh(200);
                } catch(err){
                    showToast('Failed to import template', 'error');
                    hidePageSpinner();
                }
            }

            if (openBtn) openBtn.addEventListener('click', () => openDrawer('templates'));
            if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
            if (overlayEl) overlayEl.addEventListener('click', closeDrawer);
            if (tabCreate) tabCreate.addEventListener('click', () => setTab('create'));
            if (tabTemplates) tabTemplates.addEventListener('click', () => setTab('templates'));
            if (typeSelectTpl) typeSelectTpl.addEventListener('change', () => { updateGalleryLink(); loadTemplates(); });
            if (refreshBtnTpl) refreshBtnTpl.addEventListener('click', () => loadTemplates());
            if (typeSelectCreate) typeSelectCreate.addEventListener('change', updateOpenTemplateLink);

            // Initialize default state
            updateOpenTemplateLink();
        })();

        // Refresh button to reload the page and pick up any changes
        const refreshBtn = document.getElementById('estimateRefreshBtn');

        // === Wire Work Area manual ordering via Order input ===
        (function wireAreaOrdering(){
            const container = document.getElementById('areasContainer');
            if (!container) return;
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const baseUrl = "{{ url('estimates/'.$estimate->id.'/areas/reorder') }}";

            function readRows() {
                return Array.from(container.querySelectorAll('.work-area'));
            }
            function getOrderFromRow(row){
                const input = row.querySelector('input[name="sort_order"]');
                const v = input ? parseInt(input.value, 10) : NaN;
                return Number.isFinite(v) ? v : parseInt(row.getAttribute('data-sort-order') || '0', 10);
            }
            function applyDomOrder(){
                const rows = readRows();
                rows.sort((a,b) => getOrderFromRow(a) - getOrderFromRow(b));
                rows.forEach(r => container.appendChild(r));
            }
            function payload(){
                return readRows().map(r => ({ id: r.getAttribute('data-area-id'), sort_order: getOrderFromRow(r) }));
            }
            async function persist(){
                try {
                    await fetch(baseUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        body: JSON.stringify({ areas: payload() }),
                    });
                } catch(e) {/* non-blocking */}
            }
            container.addEventListener('change', (e) => {
                const t = e.target;
                if (t && t.name === 'sort_order') {
                    applyDomOrder();
                    persist();
                }
            });
        })();
        if (refreshBtn) refreshBtn.addEventListener('click', () => autoRefresh());

        // Save All: submits all area + item update forms
        const saveAllBtn = document.getElementById('saveAllBtn');
        if (saveAllBtn) {
            saveAllBtn.addEventListener('click', async () => {
                try {
                    showPageSpinner();
                    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const scope = document.querySelector('[x-show="tab===\'work\'"]') || document;
                    // Area update forms (PATCH /areas/{id})
                    const areaForms = Array.from(document.querySelectorAll('form[action*="/areas/"] input[name="_method"][value="PATCH"]')).map(i => i.closest('form'));
                    // Item update forms (PATCH /items/{id})
                    const itemForms = Array.from(document.querySelectorAll('form[action*="/items/"] input[name="_method"][value="PATCH"]')).map(i => i.closest('form'));

                    const forms = [...new Set([...areaForms, ...itemForms])];

                    for (const form of forms) {
                        const action = form.getAttribute('action');
                        const fd = new FormData(form);
                        const res = await fetch(action, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                            body: fd,
                        });
                        // If a single update fails, continue saving others, then refresh
                    }
                    showToast('All changes saved', 'success');
                    autoRefresh(200);
                } catch (e) {
                    hidePageSpinner();
                    showToast('Save failed', 'error');
                }
            });
        }
        // Build collapsible headers per area with subtotals
        function buildAreaHeaders() { return; // legacy table area headers removed
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

            breakdowns.forEach((entry) => {
                const key = entry.key;
                const revenue = entry.revenue;
                const cost = entry.cost;
                const profit = entry.profit;
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

*/
</script>
@endpush
