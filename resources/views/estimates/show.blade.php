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
            <button class="px-3 py-1.5 text-base hover:bg-gray-100 text-gray-700 border-l" :class="{ 'bg-gray-200 text-gray-900' : tab==='crew' }" @click="tab='crew'">Crew Notes</button>
            <button class="px-3 py-1.5 text-base rounded-r-md hover:bg-gray-100 text-gray-700 border-l" :class="{ 'bg-gray-200 text-gray-900' : tab==='files' }" @click="tab='files'">Files</button>
        </div>
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
            <div class="flex flex-col gap-2 border-b border-gray-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Work Areas</h2>
                    <p class="text-sm text-gray-500">Each area groups the labor, materials, and pricing for a portion of the estimate.</p>
                </div>
                <x-brand-button type="button" size="sm"
                    @click="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'add-work-area' }))">
                    + Add Work Area
                </x-brand-button>
            </div>
            <div id="workAreasEmpty" @class([
                'px-6 py-10 text-center text-gray-500 border-t border-gray-100',
                'hidden' => $hasWorkAreas,
            ])>
                <p class="font-medium text-gray-700">No work areas yet.</p>
                <p class="text-sm text-gray-500">Use “Add Work Area” to create the first area and begin adding items.</p>
            </div>
            <div id="workAreasListWrapper" @class([
                'bg-slate-50 px-4 py-4 space-y-6',
                'hidden' => ! $hasWorkAreas,
            ])>
                @include('estimates.partials.work-areas', ['estimate' => $estimate, 'costCodes' => $costCodes ?? []])
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

