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
    $recentAreaId = session('recent_area_id');
    $addItemsTabSeed = session('add_items_tab', 'labor');
    $initialState = [
        'tab' => 'work',
        'activeArea' => 'all',
        'showAddItems' => (bool) $reopenAddItems,
        'addItemsTab' => $addItemsTabSeed,
    ];
@endphp



<div class="space-y-6"
     data-estimate-show-root
     data-estimate-show-initial='@json($initialState)'
     data-highlight-item="{{ session('recent_item_id') }}"
     x-data="estimateShowComponent($el)"
     x-on:estimate-open-add-items.window="handleAddItemsOpen($event)">
    @include('estimates.partials.header', ['estimate' => $estimate, 'previewEmailRoute' => $previewEmailRoute ?? null, 'printRoute' => $printRoute ?? null])

    {{-- Header included above; remove duplicate block below --}}


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
                            @php 
                                $wage = (float) ($labor->average_wage ?? 0);
                                $otMult = max(1, (float) ($labor->overtime_factor ?? 1));
                                $burdenPct = max(0, (float) ($labor->labor_burden_percentage ?? 0));
                                $unbillPct = min(99.9, max(0, (float) ($labor->unbillable_percentage ?? 0)));
                                $effectiveWage = $wage * $otMult;
                                $costPerHour = $effectiveWage * (1 + ($burdenPct / 100));
                                $billableFraction = max(0.01, 1 - ($unbillPct / 100));
                                $laborOverheadRate = $overheadRate ?? 0; // Use the overhead rate from controller
                                $breakeven = ($costPerHour / $billableFraction) + $laborOverheadRate;
                            @endphp
                            <div class="px-3 py-2 text-sm flex items-center justify-between gap-4">
                                <div>
                                    <div class="font-medium text-gray-900">{{ $labor->name }}</div>
                                    <div class="text-xs text-gray-500">{{ ucfirst($labor->type) }} · {{ $labor->unit }}</div>
                                </div>
                                <div class="flex flex-col items-end text-right gap-1">
                                    <div class="text-xs text-gray-600">Cost/Hr: ${{ number_format($costPerHour, 2) }}</div>
                                    <button type="button"
                                            class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-brand-600 text-white hover:bg-brand-700 transition"
                                            data-action="drawer-add"
                                            data-item-type="labor"
                                            data-catalog-id="{{ $labor->id }}"
                                            data-catalog-name="{{ $labor->name }}"
                                            data-catalog-unit="{{ $labor->unit }}"
                                            data-catalog-cost="{{ number_format($costPerHour, 2, '.', '') }}">
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
    <nav class="flex flex-wrap border-b border-gray-200 text-sm font-medium text-gray-600">
        <button class="px-4 py-2 -mb-px border-b-2 transition-colors"
            :class="{ 'border-brand-500 text-brand-700 font-semibold' : tab==='overview', 'border-transparent hover:text-gray-900 hover:border-gray-300' : tab!=='overview' }"
            @click="tab='overview'">
            Customer Info
        </button>
        <button class="px-4 py-2 -mb-px border-b-2 transition-colors"
            :class="{ 'border-brand-500 text-brand-700 font-semibold' : tab==='work', 'border-transparent hover:text-gray-900 hover:border-gray-300' : tab!=='work' }"
            @click="tab='work'">
            Work & Pricing
        </button>
        <button class="px-4 py-2 -mb-px border-b-2 transition-colors"
            :class="{ 'border-brand-500 text-brand-700 font-semibold' : tab==='crew', 'border-transparent hover:text-gray-900 hover:border-gray-300' : tab!=='crew' }"
            @click="tab='crew'">
            Crew Notes
        </button>
        <button class="px-4 py-2 -mb-px border-b-2 transition-colors"
            :class="{ 'border-brand-500 text-brand-700 font-semibold' : tab==='files', 'border-transparent hover:text-gray-900 hover:border-gray-300' : tab!=='files' }"
            @click="tab='files'">
            Files
        </button>
    </nav>
    <div class="mt-4">
        @include('estimates.partials.summary-cards', ['estimate' => $estimate])
    </div>

    <section class="bg-white rounded-lg shadow p-6 space-y-4" x-show="tab==='overview'">
        @include('estimates.partials.overview', ['estimate' => $estimate])
        <div class="grid md:grid-cols-2 gap-6">
            @include('estimates.partials.project-info', ['estimate' => $estimate, 'statuses' => $statuses])
            @include('estimates.partials.client-info', ['estimate' => $estimate])
        </div>
    </section>

    @php $hasWorkAreas = $estimate->areas->isNotEmpty(); @endphp
    <section class="space-y-4" x-show="tab==='work'">
        <div class="bg-white rounded-lg shadow overflow-hidden" id="workAreasCard">
            @php
                $stubButtonClasses = 'inline-flex items-center gap-1.5 px-3 py-2 rounded-md text-xs font-medium bg-white text-gray-700 border border-gray-300 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-1';
                $stubPrimaryClasses = 'inline-flex items-center gap-1.5 px-3 py-2 rounded-md text-xs font-semibold bg-brand-600 text-white border border-brand-600 shadow-sm hover:bg-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-1';
            @endphp
            <div class="flex flex-wrap items-center gap-3 border-b border-gray-200 bg-gray-200 px-4 py-4">
                <button type="button" class="{{ $stubPrimaryClasses }}"
                    @click="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'add-work-area' }))">
                    <svg class="h-4 w-4 text-white" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M4 10h12M10 4v12" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Add Work Area
                </button>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="{{ $stubButtonClasses }}">
                        <svg class="h-4 w-4 text-gray-500" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M4 10h12M10 4v12" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Auto ID
                    </button>
                    <button type="button" class="{{ $stubButtonClasses }}">
                        <svg class="h-4 w-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M12 3v18m0 0l-5-5m5 5l5-5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Import
                    </button>
                    <button type="button" class="{{ $stubButtonClasses }}">
                        <svg class="h-4 w-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M4 12h16" stroke-linecap="round" />
                            <path d="M8 16h8" stroke-linecap="round" />
                            <path d="M6 8h12" stroke-linecap="round" />
                        </svg>
                        Set Profit
                    </button>
                    <button type="button" class="{{ $stubButtonClasses }}">
                        <svg class="h-4 w-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M4 4h7v7H4zM13 13h7v7h-7z" stroke-linejoin="round" />
                            <path d="M4 15l3 3 4-4M13 9l4-4 3 3" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Refresh Pricing
                    </button>
                    <button type="button" class="{{ $stubButtonClasses }}">
                        <svg class="h-4 w-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M3 11l9-7 9 7-9 7-9-7z" stroke-linejoin="round" />
                            <path d="M5 13.5l7 5 7-5" stroke-linejoin="round" />
                        </svg>
                        Measure Site
                    </button>
                </div>
            </div>
            <div id="workAreasEmpty" @class([
                'px-3 py-4 text-center text-gray-500 border-t border-gray-100',
                'hidden' => $hasWorkAreas,
            ])>
                <p class="font-medium text-gray-700">No work areas yet.</p>
                <p class="text-sm text-gray-500">Use “Add Work Area” to create the first area and begin adding items.</p>
            </div>
            <div id="workAreasListWrapper" @class([
                'bg-gray-200 px-2.5 pt-2 pb-2.5',
                'hidden' => ! $hasWorkAreas,
            ])>
                @include('estimates.partials.work-areas', [
                    'estimate' => $estimate,
                    'costCodes' => $costCodes ?? [],
                    'recentAreaId' => $recentAreaId ?? null,
                    'defaultMarginPercent' => $defaultMarginPercent ?? 20.0,
                    'overheadRate' => $overheadRate ?? 0.0,
                ])
            </div>
        </div>

        @include('estimates.partials.work-area-modal', ['estimate' => $estimate, 'costCodes' => $costCodes ?? []])
    </section>

    @include('estimates.partials.add-items-panel', ['estimate' => $estimate, 'materials' => $materials, 'laborCatalog' => $laborCatalog, 'defaultMarginPercent' => $defaultMarginPercent ?? 20, 'defaultMarginRate' => $defaultMarginRate ?? 0.2])

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
