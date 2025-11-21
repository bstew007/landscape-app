@extends('layouts.sidebar')

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
@endphp

@section('content')

@push('styles')
<style>
@keyframes estimatePulseHighlight {
    0% { background-color: rgba(253, 230, 138, 0.8); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
    100% { background-color: transparent; box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
}
.estimate-highlight {
    animation: estimatePulseHighlight 1.8s ease-out;
}
</style>
@endpush
@php
    $calcRoutes = [
        'mulching' => Route::has('calculators.mulching.form') ? route('calculators.mulching.form') : null,
        'weeding' => Route::has('calculators.weeding.form') ? route('calculators.weeding.form') : null,
        'planting' => Route::has('calculators.planting.form') ? route('calculators.planting.form') : null,
        'turf_mowing' => Route::has('calculators.turf_mowing.form') ? route('calculators.turf_mowing.form') : null,
        'retaining_wall' => Route::has('calculators.wall.form') ? route('calculators.wall.form') : null,
        'paver_patio' => Route::has('calculators.patio.form') ? route('calculators.patio.form') : null,
        'fence' => Route::has('calculators.fence.form') ? route('calculators.fence.form') : null,
        'syn_turf' => Route::has('calculators.syn_turf.form') ? route('calculators.syn_turf.form') : null,
        'pruning' => Route::has('calculators.pruning.form') ? route('calculators.pruning.form') : null,
    ];
    $templatesRoute = Route::has('estimates.calculator.templates') ? route('estimates.calculator.templates', $estimate) : null;
    $importRoute = Route::has('estimates.calculator.import') ? route('estimates.calculator.import', $estimate) : null;
    $galleryRoute = Route::has('calculator.templates.gallery') ? route('calculator.templates.gallery') : '#';
    $previewEmailRoute = Route::has('estimates.preview-email') ? route('estimates.preview-email', $estimate) : null;
    $printRoute = Route::has('estimates.print') ? route('estimates.print', $estimate) : null;
@endphp

<script>
    // Provide minimal globals for the JS module
    window.__calcRoutes = @json($calcRoutes);
    window.__estimateTemplatesUrl = @json($templatesRoute);
    window.__estimateImportUrl = @json($importRoute);
    window.__estimateItemsBaseUrl = "{{ url('estimates/'.$estimate->id.'/items') }}";
    window.__galleryUrl = @json($galleryRoute);
    window.__estimateAreaReorderUrl = "{{ url('estimates/'.$estimate->id.'/areas/reorder') }}";
    window.__estimateItemsReorderUrl = "{{ url('estimates/'.$estimate->id.'/items/reorder') }}";
    window.__estimateItemsUpdateBaseUrl = "{{ url('estimates/'.$estimate->id.'/items') }}/";
    window.__estimateRemoveCalcBaseUrl = "{{ url('estimates/'.$estimate->id.'/remove-calculation') }}/";
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

@php
    $reopenAddItems = session('reopen_add_items', false);
    $addItemsTabSeed = session('add_items_tab', 'materials');
    $initialState = [
        'tab' => 'work',
        'activeArea' => 'all',
        'showAddItems' => (bool) $reopenAddItems,
        'addItemsTab' => $addItemsTabSeed,
    ];
@endphp

<script>
    window.estimateShowComponent = window.estimateShowComponent || function(el) {
        let initial = {};
        try {
            initial = el?.dataset?.estimateShowInitial ? JSON.parse(el.dataset.estimateShowInitial) : {};
        } catch (_) {
            initial = {};
        }
        return {
            tab: initial.tab || 'work',
            activeArea: initial.activeArea || 'all',
            showAddItems: Boolean(initial.showAddItems),
            addItemsTab: initial.addItemsTab || 'materials',
            openAddItems(tab = 'labor') {
                this.addItemsTab = tab;
                this.showAddItems = true;
            },
            closeAddItems() {
                this.showAddItems = false;
            },
        };
    };
</script>

<div class="space-y-6"
     data-estimate-show-root
     data-estimate-show-initial='@json($initialState)'
     data-highlight-item="{{ session('recent_item_id') }}"
     x-data="estimateShowComponent($el)">
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
            @if($previewEmailRoute)
                <x-brand-button href="{{ $previewEmailRoute }}" variant="outline">Preview Email</x-brand-button>
            @endif
            <form action="{{ route('estimates.invoice', $estimate) }}" method="POST">
                @csrf
                <x-brand-button type="submit" variant="outline">Create Invoice</x-brand-button>
            </form>
            @if($printRoute)
                <x-brand-button href="{{ $printRoute }}" target="_blank" variant="outline">Print</x-brand-button>
            @endif
            <x-brand-button type="button" id="openCalcDrawerBtn" class="ml-2">+ Add via Calculator</x-brand-button>
        </x-slot:actions>
    </x-page-header>

    <!-- Add via Calculator Slide-over (controlled by JS module) -->
    <div id="calcDrawer" class="fixed inset-0 z-40" style="display:none;" x-data="{ itemsTab: 'labor' }" x-on:set-calc-tab.window="itemsTab = $event.detail || 'labor'">
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
                            @php $rate = $labor->average_wage ?? $labor->base_rate; @endphp
                            <div class="px-3 py-2 text-sm flex items-center justify-between gap-4">
                                <div>
                                    <div class="font-medium text-gray-900">{{ $labor->name }}</div>
                                    <div class="text-xs text-gray-500">{{ ucfirst($labor->type) }} ? {{ $labor->unit }}</div>
                                </div>
                                <div class="flex flex-col items-end text-right gap-1">
                                    <div class="text-xs text-gray-600">Avg Wage: ${{ number_format($rate, 2) }}</div>
                                    <button type="button"
                                            class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-brand-600 text-white hover:bg-brand-700 transition"
                                            data-action="drawer-add"
                                            data-item-type="labor"
                                            data-catalog-id="{{ $labor->id }}"
                                            data-catalog-name="{{ $labor->name }}"
                                            data-catalog-unit="{{ $labor->unit }}"
                                            data-catalog-cost="{{ number_format($rate, 2, '.', '') }}">
                                        Add
                                    </button>
                                </div>
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
            <div x-data="{ open: true, tab: 'pricing', menuOpen: false }"
                 x-on:force-open-area.window="if (Number($event.detail?.areaId) === {{ $area->id }}) open = true"
                 class="mb-6 border rounded-lg bg-white work-area overflow-visible"
                 data-area-id="{{ $area->id }}"
                 data-sort-order="{{ $area->sort_order ?? $loop->iteration }}">
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
                                        <tr class="border-t"
                                            data-item-id="{{ $item->id }}"
                                            data-item-type="{{ $item->item_type }}"
                                            data-area-id="{{ $area->id }}"
                                            data-quantity="{{ $item->quantity }}"
                                            id="estimate-item-{{ $item->id }}">
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
                        <input type="hidden" name="stay_in_add_items" value="1">
                        <input type="hidden" name="add_items_tab" value="equipment">
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
                        <input type="hidden" name="stay_in_add_items" value="1">
                        <input type="hidden" name="add_items_tab" value="subs">
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
                <input type="hidden" name="stay_in_add_items" value="1">
                <input type="hidden" name="add_items_tab" value="materials">
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
                <input type="hidden" name="stay_in_add_items" value="1">
                <input type="hidden" name="add_items_tab" value="labor">
                <div>
                    <label class="block text-sm font-semibold mb-1">Labor</label>
                    <input type="text" class="form-input w-full mb-2 text-sm border-brand-300 focus:ring-brand-500 focus:border-brand-500" placeholder="Search labor..." data-role="filter">
                    <select name="catalog_id" class="form-select w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" data-role="labor-select">
                        <option value="">Select labor</option>
                        @foreach ($laborCatalog as $labor)
                            @php $rate = $labor->average_wage ?? $labor->base_rate; @endphp
                            <option value="{{ $labor->id }}"
                                    data-unit="{{ $labor->unit }}"
                                    data-cost="{{ $rate }}">
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
                <input type="hidden" name="stay_in_add_items" value="1">
                <input type="hidden" name="add_items_tab" value="other">
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
            <input type="hidden" name="return_to" value="{{ request()->fullUrl() }}">
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


