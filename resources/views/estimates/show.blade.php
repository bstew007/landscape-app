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
        'tab' => request('tab', 'work'),
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
     x-on:estimate-open-add-items.window="handleAddItemsOpen($event)"
     x-init="
         window.estimateUnsavedChanges = { hasChanges: false };
         
         // Keyboard shortcuts
         window.addEventListener('keydown', (e) => {
             // Ctrl/Cmd + S: Save all
             if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                 e.preventDefault();
                 document.getElementById('saveAllBtn')?.click();
             }
             
             // Ctrl/Cmd + K: Open calculator drawer
             if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                 e.preventDefault();
                 const drawer = document.getElementById('calcDrawer');
                 if (drawer && drawer.style.display === 'none') {
                     drawer.style.display = 'block';
                 }
             }
             
             // Escape: Close any open modals/drawers
             if (e.key === 'Escape') {
                 const drawer = document.getElementById('calcDrawer');
                 if (drawer && drawer.style.display !== 'none') {
                     drawer.style.display = 'none';
                 }
             }
         });
     ">
    
    <!-- Modern Header -->
    <section class="rounded-[32px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-6 sm:p-8 shadow-2xl border border-brand-800/40 relative overflow-hidden">
        <!-- Action Buttons Row -->
        <div class="flex flex-wrap gap-2 mb-4">
                <button type="button" id="estimateRefreshBtn"
                        class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg border text-sm bg-white/10 text-white border-white/40 hover:bg-white/20 transition">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/>
                    </svg>
                    Refresh
                </button>
                <button type="button" id="saveAllBtn"
                        class="inline-flex items-center gap-1.5 h-9 px-4 rounded-lg bg-white text-brand-900 text-sm font-semibold hover:bg-brand-50 transition shadow-sm">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/>
                        <polyline points="7 3 7 8 15 8"/>
                    </svg>
                    Save All
                </button>
                <a href="{{ route('estimates.edit', $estimate) }}"
                   class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg border text-sm bg-white/10 text-white border-white/40 hover:bg-white/20 transition">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    Edit
                </a>
                @if($previewEmailRoute)
                    <a href="{{ $previewEmailRoute }}"
                       class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg border text-sm bg-white/10 text-white border-white/40 hover:bg-white/20 transition">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                        Email
                    </a>
                @endif
                @if($printRoute)
                    <a href="{{ $printRoute }}" target="_blank"
                       class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg border text-sm bg-white/10 text-white border-white/40 hover:bg-white/20 transition">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 6 2 18 2 18 9"/>
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                            <rect x="6" y="14" width="12" height="8"/>
                        </svg>
                        Print
                    </a>
                @endif
                
                <!-- Convert to Job Button -->
                @include('estimates.partials.create-job-button', ['estimate' => $estimate])
                
                <!-- Keyboard Shortcuts Help -->
                <div x-data="{ showShortcuts: false }" class="relative inline-block">
                    <button type="button" 
                            @click="showShortcuts = !showShortcuts"
                            class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg border text-sm bg-white/10 text-white border-white/40 hover:bg-white/20 transition">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M7 2h10M7 22h10M14 2h7a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2h-7M3 2h7a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H3" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="hidden sm:inline">Shortcuts</span>
                    </button>
                    <div x-show="showShortcuts" 
                         x-transition
                         @click.outside="showShortcuts = false"
                         class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl border border-gray-200 p-3 text-gray-900 text-sm z-50"
                         style="top: 100%;">
                        <div class="font-semibold mb-2">Keyboard Shortcuts</div>
                        <div class="space-y-1.5 text-xs">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Save all</span>
                                <kbd class="px-2 py-0.5 bg-gray-100 border border-gray-300 rounded font-mono">⌘/Ctrl+S</kbd>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Open calculator</span>
                                <kbd class="px-2 py-0.5 bg-gray-100 border border-gray-300 rounded font-mono">⌘/Ctrl+K</kbd>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Close modals</span>
                                <kbd class="px-2 py-0.5 bg-gray-100 border border-gray-300 rounded font-mono">Esc</kbd>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
        
        <!-- Title Row -->
        <div class="flex items-center gap-4">
            <div class="h-16 w-16 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center flex-shrink-0">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-8 w-8 text-white">
                    <path d="M7 2h7l5 5v13a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/>
                    <path d="M14 2v5h5"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0 space-y-1">
                <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Estimate #{{ $estimate->id }}</p>
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl sm:text-3xl font-semibold text-white">{{ $estimate->title }}</h1>
                    <div x-data="{ hasChanges: false }" 
                         x-init="
                             $watch('hasChanges', value => window.estimateUnsavedChanges.hasChanges = value);
                             window.addEventListener('form-changed', () => hasChanges = true);
                             window.addEventListener('form-saved', () => hasChanges = false);
                         "
                         x-show="hasChanges" 
                         x-transition
                         class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-500/20 text-amber-100 border border-amber-400/30 animate-pulse">
                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor">
                            <circle cx="12" cy="12" r="10"/>
                        </svg>
                        Unsaved changes
                    </div>
                </div>
                <p class="text-sm text-brand-100/85">{{ $estimate->client->name }} · {{ $estimate->property->name ?? 'No property' }}</p>
            </div>
        </div>
    </section>

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
            <div id="calcTemplatesPane" class="p-4 overflow-y-auto space-y-4" x-show="itemsTab==='templates'" x-data="{ recentTemplates: [] }" x-init="
                if (sessionStorage.getItem('recentTemplates')) {
                    recentTemplates = JSON.parse(sessionStorage.getItem('recentTemplates'));
                }
                $watch('recentTemplates', value => {
                    sessionStorage.setItem('recentTemplates', JSON.stringify(value));
                });
                window.addEventListener('template-used', (e) => {
                    const template = e.detail;
                    recentTemplates = [template, ...recentTemplates.filter(t => t.id !== template.id)].slice(0, 5);
                });
            ">
                <!-- Recently Used Templates -->
                <div x-show="recentTemplates.length > 0" class="bg-brand-50 border border-brand-200 rounded-lg p-3">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="h-4 w-4 text-brand-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm font-semibold text-brand-900">Recently Used</span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="(template, index) in recentTemplates.slice(0, 5)" :key="template.id">
                            <button type="button" 
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-brand-700 bg-white border border-brand-300 rounded-md hover:bg-brand-100 hover:border-brand-400 transition-colors"
                                    @click="window.estimateCalculator?.importTemplate(template.id, false)">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span x-text="template.name"></span>
                                <span class="text-brand-500" x-text="'(' + template.type.replace('_', ' ') + ')'"></span>
                            </button>
                        </template>
                    </div>
                </div>
                
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
            <div id="calcItemsPane" class="p-4 overflow-y-auto space-y-4" x-show="itemsTab!=='templates'" x-data="{ laborSearch: '', materialSearch: '', equipmentSearch: '' }">
                <!-- Labor List -->
                <div x-show="itemsTab==='labor'" class="space-y-2">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-semibold">Labor Catalog</h4>
                        <x-brand-button type="button" size="sm" @click="$dispatch('open-modal','new-labor')">New</x-brand-button>
                    </div>
                    <!-- Search Input -->
                    <div class="relative">
                        <input type="text" 
                               x-model="laborSearch" 
                               placeholder="Search labor items..."
                               class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                        <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <div class="max-h-60 overflow-y-auto border rounded bg-white divide-y">
                        @foreach ($laborCatalog as $labor)
                            @php 
                                // Use stored values from catalog database
                                $breakeven = (float) ($labor->breakeven ?? 0);
                                $price = (float) ($labor->base_rate ?? 0);
                                $profitMargin = (float) ($labor->profit_percent ?? 0);
                                
                                // For backward compatibility with wage calculations
                                $wage = (float) ($labor->average_wage ?? 0);
                                $otFactorPct = (float) ($labor->overtime_factor ?? 0);
                                $burdenPct = max(0, (float) ($labor->labor_burden_percentage ?? 0));
                                $effectiveWage = $wage * (1 + ($otFactorPct / 100));
                                $costPerHour = $effectiveWage * (1 + ($burdenPct / 100));
                            @endphp
                            <div class="px-3 py-2 text-sm flex items-center justify-between gap-4"
                                 x-show="laborSearch === '' || '{{ strtolower($labor->name) }} {{ strtolower($labor->type) }}'.includes(laborSearch.toLowerCase())">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">{{ $labor->name }}</div>
                                    <div class="text-xs text-gray-500">{{ ucfirst($labor->type) }} · {{ $labor->unit }}</div>
                                </div>
                                <div class="flex flex-col items-end text-right gap-1 min-w-[140px]">
                                    <div class="grid grid-cols-2 gap-x-3 gap-y-0.5 text-xs w-full">
                                        <div class="text-gray-500">Breakeven:</div>
                                        <div class="font-medium text-gray-700">${{ number_format($breakeven, 2) }}</div>
                                        
                                        <div class="text-gray-500">Profit:</div>
                                        <div class="font-medium text-gray-700">{{ number_format($profitMargin, 1) }}%</div>
                                        
                                        <div class="text-gray-500">Price:</div>
                                        <div class="font-semibold text-brand-700">${{ number_format($price, 2) }}</div>
                                    </div>
                                    <button type="button"
                                            class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-brand-600 text-white hover:bg-brand-700 transition mt-1"
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
                            <div class="px-3 py-8 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <p class="text-sm font-medium text-gray-600">No labor items in catalog</p>
                                <p class="text-xs text-gray-500 mt-1">Click "New" above to add your first labor item</p>
                            </div>
                        @endif
                    </div>
                </div>
                <!-- Materials List -->
                <div x-show="itemsTab==='materials'" class="space-y-2">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-semibold">Materials Catalog</h4>
                        <x-brand-button type="button" size="sm" @click="$dispatch('open-modal','new-material')">New</x-brand-button>
                    </div>
                    <!-- Search Input -->
                    <div class="relative">
                        <input type="text" 
                               x-model="materialSearch" 
                               placeholder="Search materials..."
                               class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                        <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <div class="max-h-60 overflow-y-auto border rounded bg-white divide-y">
                        @foreach ($materials as $material)
                            <div class="px-3 py-2 text-sm flex items-center justify-between gap-4"
                                 x-show="materialSearch === '' || '{{ strtolower($material->name) }} {{ strtolower($material->unit) }}'.includes(materialSearch.toLowerCase())">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">{{ $material->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $material->unit }}</div>
                                </div>
                                <div class="flex flex-col items-end text-right gap-1 min-w-[140px]">
                                    <div class="text-xs text-gray-600">Cost: ${{ number_format($material->unit_cost, 2) }}</div>
                                    <button type="button"
                                            class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-brand-600 text-white hover:bg-brand-700 transition"
                                            data-action="drawer-add"
                                            data-item-type="material"
                                            data-catalog-id="{{ $material->id }}"
                                            data-catalog-name="{{ $material->name }}"
                                            data-catalog-unit="{{ $material->unit }}"
                                            data-catalog-cost="{{ number_format($material->unit_cost, 2, '.', '') }}">
                                        Add
                                    </button>
                                </div>
                            </div>
                        @endforeach
                        @if($materials->isEmpty())
                            <div class="px-3 py-8 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                <p class="text-sm font-medium text-gray-600">No materials in catalog</p>
                                <p class="text-xs text-gray-500 mt-1">Click "New" above to add your first material</p>
                            </div>
                        @endif
                    </div>
                </div>
                <!-- Equipment List -->
                <div x-show="itemsTab==='equipment'" class="space-y-2">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-semibold">Equipment Catalog</h4>
                        <x-brand-button type="button" size="sm" onclick="window.location.href='{{ route('equipment.create') }}'">New</x-brand-button>
                    </div>
                    <!-- Search Input -->
                    <div class="relative">
                        <input type="text" 
                               x-model="equipmentSearch" 
                               placeholder="Search equipment..."
                               class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                        <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <div class="max-h-60 overflow-y-auto border rounded bg-white divide-y">
                        @foreach ($equipmentCatalog as $equipment)
                            @php
                                $rate = $equipment->unit === 'hr' ? $equipment->hourly_rate : $equipment->daily_rate;
                                $cost = $equipment->unit === 'hr' ? $equipment->hourly_cost : $equipment->daily_cost;
                                $ownershipBadge = $equipment->ownership_type === 'company' ? '🏢' : '🔑';
                                $ownershipLabel = $equipment->ownership_type === 'company' ? 'Company' : 'Rental';
                            @endphp
                            <div class="px-3 py-2 text-sm flex items-center justify-between gap-4"
                                 x-show="equipmentSearch === '' || '{{ strtolower($equipment->name) }} {{ strtolower($equipment->category ?? '') }} {{ strtolower($equipment->model ?? '') }}'.includes(equipmentSearch.toLowerCase())">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">{{ $ownershipBadge }} {{ $equipment->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $ownershipLabel }} · {{ $equipment->unit }}</div>
                                </div>
                                <div class="flex flex-col items-end text-right gap-1 min-w-[140px]">
                                    <div class="grid grid-cols-2 gap-x-3 gap-y-0.5 text-xs w-full">
                                        <div class="text-gray-500">Cost:</div>
                                        <div class="font-medium text-gray-700">${{ number_format($cost, 2) }}</div>
                                        
                                        <div class="text-gray-500">Rate:</div>
                                        <div class="font-semibold text-brand-700">${{ number_format($rate, 2) }}</div>
                                    </div>
                                    <button type="button"
                                            class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-brand-600 text-white hover:bg-brand-700 transition mt-1"
                                            data-action="drawer-add"
                                            data-item-type="equipment"
                                            data-catalog-id="{{ $equipment->id }}"
                                            data-catalog-name="{{ $equipment->name }}"
                                            data-catalog-unit="{{ $equipment->unit }}"
                                            data-catalog-cost="{{ number_format($cost, 2, '.', '') }}">
                                        Add
                                    </button>
                                </div>
                            </div>
                        @endforeach
                        @if($equipmentCatalog->isEmpty())
                            <div class="px-3 py-8 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                </svg>
                                <p class="text-sm font-medium text-gray-600">No equipment in catalog</p>
                                <p class="text-xs text-gray-500 mt-1">Click "New" above to add your first equipment</p>
                            </div>
                        @endif
                    </div>
                </div>
                <!-- Placeholders -->
                <div x-show="itemsTab==='subs'" class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <p class="text-sm font-medium text-gray-600">Subcontractor catalog coming soon</p>
                    <p class="text-xs text-gray-500 mt-1">Manage subcontractor services and pricing</p>
                </div>
                <div x-show="itemsTab==='other'" class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    <p class="text-sm font-medium text-gray-600">Other items catalog coming soon</p>
                    <p class="text-xs text-gray-500 mt-1">Add fees, permits, and miscellaneous costs</p>
                </div>
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
            :class="{ 'border-brand-500 text-brand-700 font-semibold' : tab==='client-notes', 'border-transparent hover:text-gray-900 hover:border-gray-300' : tab!=='client-notes' }"
            @click="tab='client-notes'">
            Client Notes
        </button>
        <button class="px-4 py-2 -mb-px border-b-2 transition-colors"
            :class="{ 'border-brand-500 text-brand-700 font-semibold' : tab==='crew', 'border-transparent hover:text-gray-900 hover:border-gray-300' : tab!=='crew' }"
            @click="tab='crew'">
            Crew Notes
        </button>
        <button class="px-4 py-2 -mb-px border-b-2 transition-colors"
            :class="{ 'border-brand-500 text-brand-700 font-semibold' : tab==='print', 'border-transparent hover:text-gray-900 hover:border-gray-300' : tab!=='print' }"
            @click="tab='print'">
            Print Documents
        </button>
        <button class="px-4 py-2 -mb-px border-b-2 transition-colors"
            :class="{ 'border-brand-500 text-brand-700 font-semibold' : tab==='files', 'border-transparent hover:text-gray-900 hover:border-gray-300' : tab!=='files' }"
            @click="tab='files'">
            Files
        </button>
    </nav>
    
    <!-- Sticky Totals Bar (visible on Work & Pricing tab) -->
    @php
        $totalItems = $estimate->items->count();
        $totalCost = $estimate->cost_total ?? 0;
        $totalRevenue = $estimate->grand_total ?? 0;
        $totalProfit = $totalRevenue - $totalCost;
        $profitMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;
        
        $marginClass = match(true) {
            $profitMargin < 10 => 'text-red-700 bg-red-50 border-red-200',
            $profitMargin < 15 => 'text-amber-700 bg-amber-50 border-amber-200',
            default => 'text-emerald-700 bg-emerald-50 border-emerald-200',
        };
    @endphp
    <div x-show="tab==='work'" 
         class="sticky top-0 z-10 bg-white border-b border-gray-200 shadow-sm">
        <div class="px-4 py-2.5 flex flex-wrap items-center gap-3 text-sm">
            <div class="flex items-center gap-2">
                <span class="text-gray-500 font-medium">Total:</span>
                <span class="font-bold text-gray-900 text-lg tabular-nums">${{ number_format($totalRevenue, 0) }}</span>
            </div>
            <div class="h-4 w-px bg-gray-300"></div>
            <div class="flex items-center gap-2">
                <span class="text-gray-500 font-medium">Cost:</span>
                <span class="font-semibold text-gray-700 tabular-nums">${{ number_format($totalCost, 0) }}</span>
            </div>
            <div class="h-4 w-px bg-gray-300"></div>
            <div class="flex items-center gap-2">
                <span class="text-gray-500 font-medium">Profit:</span>
                <span class="font-semibold text-gray-700 tabular-nums">${{ number_format($totalProfit, 0) }}</span>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold border {{ $marginClass }}">
                    @if($profitMargin < 10)
                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    @endif
                    {{ number_format($profitMargin, 1) }}%
                </span>
            </div>
            <div class="h-4 w-px bg-gray-300"></div>
            <div class="flex items-center gap-2">
                <span class="text-gray-500 font-medium">Items:</span>
                <span class="font-semibold text-gray-700">{{ $totalItems }}</span>
            </div>
            @if($profitMargin < 15)
                <div class="ml-auto">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-semibold {{ $profitMargin < 10 ? 'bg-red-100 text-red-800 border border-red-300' : 'bg-amber-100 text-amber-800 border border-amber-300' }}">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        {{ $profitMargin < 10 ? 'Below Minimum Margin' : 'Low Margin Warning' }}
                    </div>
                </div>
            @endif
        </div>
    </div>
    
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

    <!-- Client Notes Tab Content -->
    <div x-show="tab==='client-notes'">
        @include('estimates.partials.notes', ['estimate' => $estimate])
    </div>

    <!-- Crew Notes Tab Content -->
    <div x-show="tab==='crew'">
        @include('estimates.partials.crew-notes', ['estimate' => $estimate])
    </div>

    <!-- Print Documents Tab Content -->
    <div x-show="tab==='print'">
        @include('estimates.partials.print-documents', ['estimate' => $estimate])
    </div>

    <!-- Files Tab Content -->
    <section class="bg-white rounded-lg shadow p-6 space-y-4" x-show="tab==='files'">
        <div class="text-center py-12">
            <svg class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 3v4a2 2 0 002 2h4"/>
            </svg>
            <h3 class="mt-2 text-lg font-semibold text-gray-700">No files yet</h3>
            <p class="mt-1 text-sm text-gray-500 max-w-md mx-auto">File attachments and document management will be available soon.</p>
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
                <div class="py-8">
                    <svg class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="font-semibold text-gray-700 text-lg mb-1">No work areas yet</p>
                    <p class="text-sm text-gray-500 max-w-md mx-auto">Click "Add Work Area" above to create your first area and start building your estimate with line items.</p>
                </div>
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

    @include('estimates.partials.add-items-panel', ['estimate' => $estimate, 'materials' => $materials, 'laborCatalog' => $laborCatalog, 'equipmentCatalog' => $equipmentCatalog, 'defaultMarginPercent' => $defaultMarginPercent ?? 20, 'defaultMarginRate' => $defaultMarginRate ?? 0.2])

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
                <x-brand-button type="submit" class="bg-brand-800 hover:bg-brand-900 focus:ring-brand-700 border border-brand-800">Save</x-brand-button>
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
                <x-brand-button type="submit" class="bg-brand-800 hover:bg-brand-900 focus:ring-brand-700 border border-brand-800">Save</x-brand-button>
            </div>
        </form>
    </div>
</x-modal>

{{-- Custom Pricing Modal --}}
@include('estimates.partials.modals._custom-pricing')

</div>
@endsection
