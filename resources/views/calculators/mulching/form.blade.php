@extends('layouts.sidebar')

@php
    // Get saved values or old input
    $areaValue = old('area_sqft', $formData['area_sqft'] ?? null);
    $depthValue = old('depth_inches', $formData['depth_inches'] ?? null);
    
    // Material catalog integration
    $storedMaterials = $formData['materials'] ?? [];
    $firstMaterial = collect($storedMaterials)->first();
    $selectedMaterialName = old('mulch_type', $firstMaterial['name'] ?? '');
    $selectedCatalogId = old('material_catalog_id', $firstMaterial['catalog_id'] ?? null);
    $selectedUnitCost = old('material_unit_cost', $firstMaterial['unit_cost'] ?? 35);
    
    // Calculate estimated quantity
    $mulchYardsPreview = ($areaValue && $depthValue) ? round(($areaValue * ($depthValue / 12)) / 27, 2) : null;
@endphp

@section('content')
<div class="max-w-5xl mx-auto py-8 px-4">
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-3">
            <div class="bg-gradient-to-br from-brand-700 to-brand-900 p-3 rounded-xl shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                </svg>
            </div>
            <h1 class="text-4xl font-bold text-gray-900">
                {{ $editMode ? 'Edit Mulching Data' : 'Mulching Calculator' }}
            </h1>
        </div>
        <p class="text-gray-600">Enter measurements, select mulch from catalog, calculate labor automatically.</p>
    </div>

    @if(($mode ?? null) !== 'template' && $siteVisit)
        @include('calculators.partials.client_info', ['siteVisit' => $siteVisit])
    @else
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-500 p-4 rounded-lg mb-6">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-blue-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <p class="font-semibold text-blue-900">Template Mode</p>
                    <p class="text-sm text-blue-700">Build a mulching estimate without a site visit.</p>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('calculators.mulching.calculate') }}" 
          x-data="mulchingCalculator({{ $selectedUnitCost }}, '{{ $selectedMaterialName }}', {{ $selectedCatalogId ?? 'null' }})"
          class="space-y-6">
        @csrf
        <input type="hidden" name="mode" value="{{ $mode ?? '' }}">
        @if(!empty($estimateId))
            <input type="hidden" name="estimate_id" value="{{ $estimateId }}">
        @endif
        @if ($editMode && isset($calculation))
            <input type="hidden" name="calculation_id" value="{{ $calculation->id }}">
        @endif
        <input type="hidden" name="site_visit_id" value="{{ $siteVisitId }}">

        {{-- 1. Crew & Logistics --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-gray-800 to-gray-700 px-6 py-4 border-b border-gray-600">
                <h2 class="text-lg font-semibold text-white flex items-center">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-white/20 text-white font-bold mr-3">1</span>
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Crew & Logistics
                </h2>
            </div>
            <div class="p-6">
                @include('calculators.partials.overhead_inputs')
            </div>
        </div>

        {{-- 2. Mulch Coverage --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-gray-800 to-gray-700 px-6 py-4 border-b border-gray-600">
                <h2 class="text-lg font-semibold text-white flex items-center">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-white/20 text-white font-bold mr-3">2</span>
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                    </svg>
                    Mulch Coverage
                </h2>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Square Footage <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               name="area_sqft"
                               x-model="area"
                               @input="calculateYards()"
                               step="any"
                               min="0"
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                               placeholder="e.g. 500"
                               value="{{ $areaValue }}">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Mulch Depth (inches) <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               name="depth_inches"
                               x-model="depth"
                               @input="calculateYards()"
                               step="any"
                               min="0"
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                               placeholder="e.g. 2"
                               value="{{ $depthValue }}">
                    </div>
                </div>
                
                {{-- Auto-calculated quantity preview --}}
                <div x-show="calculatedYards > 0" 
                     x-transition
                     class="bg-gradient-to-r from-blue-50 to-blue-100 border-l-4 border-blue-500 rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-blue-700 mb-1">Estimated Mulch Needed</p>
                            <p class="text-3xl font-bold text-blue-900" x-text="calculatedYards.toFixed(2) + ' cubic yards'"></p>
                        </div>
                        <div class="flex items-center justify-center w-20 h-20 rounded-full bg-blue-200/50">
                            <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Material Selection --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" @material-selected.window="handleMaterialSelected($event)">
            <div class="bg-gradient-to-r from-gray-800 to-gray-700 px-6 py-4 border-b border-gray-600">
                <h2 class="text-lg font-semibold text-white flex items-center">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-white/20 text-white font-bold mr-3">3</span>
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    Select Mulch Material
                </h2>
            </div>
            <div class="p-6 space-y-6">
                <p class="text-sm text-gray-600">Choose from your material catalog or enter a custom type.</p>
                
                {{-- Material Catalog Picker --}}
                <div>
                    @include('components.material-catalog-picker')
                </div>
                
                {{-- Selected Material Display --}}
                <div x-show="selectedMaterial" 
                     x-transition
                     class="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-500 rounded-xl p-6 shadow-md">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <svg class="w-6 h-6 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <p class="text-xl font-bold text-green-900" x-text="materialName"></p>
                            </div>
                            <p class="text-base font-semibold text-green-700 ml-8">
                                $<span x-text="unitCost.toFixed(2)"></span> per cubic yard
                            </p>
                            <p class="text-sm text-green-600 mt-2 ml-8" x-show="selectedMaterial?.description" x-text="selectedMaterial?.description"></p>
                        </div>
                        <button type="button"
                                @click="clearMaterial()"
                                class="ml-4 p-3 text-green-600 hover:text-green-800 hover:bg-green-100 rounded-lg transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                {{-- Manual Entry Fallback --}}
                <div x-show="!selectedMaterial" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Or Enter Custom Mulch Type:
                        </label>
                        <input type="text"
                               x-model="materialName"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                               placeholder="e.g., Forest Brown Mulch, Black Mulch">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Unit Cost (per cubic yard):
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-3 text-gray-500">$</span>
                            <input type="number"
                                   x-model="unitCost"
                                   step="0.01"
                                   min="0"
                                   class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                   placeholder="35.00">
                        </div>
                    </div>
                </div>
                
                {{-- Hidden Fields for Form Submission --}}
                <input type="hidden" name="mulch_type" :value="materialName">
                <input type="hidden" name="material_catalog_id" :value="catalogId">
                <input type="hidden" name="material_unit_cost" :value="unitCost">
            </div>
        </div>

        {{-- 4. Task Inputs (Auto-calculated from production rates) --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-gray-800 to-gray-700 px-6 py-4 border-b border-gray-600">
                <h2 class="text-lg font-semibold text-white flex items-center">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-white/20 text-white font-bold mr-3">4</span>
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                    Mulching Tasks
                    <span class="ml-3 text-xs font-normal text-gray-300">(Optional)</span>
                </h2>
            </div>
            </div>
            <div class="p-6 space-y-4">
                <p class="text-sm text-gray-600 bg-gray-50 p-3 rounded-lg border border-gray-200">
                    <strong>💡 Tip:</strong> Most jobs use "Standard Wheelbarrow" - only fill in if using special equipment.
                </p>

                @php
                    $rates = \App\Models\ProductionRate::where('calculator', 'mulching')
                        ->orderBy('task')
                        ->get();
                    $savedTasks = $formData['tasks'] ?? [];
                    $savedQuantities = [];
                    foreach ($savedTasks as $taskRow) {
                        $key = $taskRow['task_key'] ?? str_replace(' ', '_', strtolower($taskRow['task'] ?? ''));
                        $savedQuantities[$key] = $taskRow['qty'] ?? null;
                    }
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($rates as $rate)
                        @php
                            $key = $rate->task;
                            $label = ucwords(str_replace('_', ' ', $key));
                            $value = old("tasks.$key.qty", $savedQuantities[$key] ?? '');
                        @endphp
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition">
                            <label class="block font-semibold text-gray-900 mb-2">{{ $label }}</label>
                            <input type="number"
                                   name="tasks[{{ $key }}][qty]"
                                   step="any"
                                   min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                   placeholder="Cubic yards"
                                   value="{{ $value }}">
                            <p class="text-xs text-gray-500 mt-2">
                                ⏱ Production: {{ number_format($rate->rate, 4) }} hrs/{{ $rate->unit }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Job Notes --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-gray-800 to-gray-700 px-6 py-4 border-b border-gray-600">
                <h2 class="text-lg font-semibold text-white flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Job Notes
                    <span class="ml-3 text-xs font-normal text-gray-300">(Optional)</span>
                </h2>
            </div>
            <div class="p-6">
                <textarea name="job_notes" 
                          rows="4" 
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition resize-none"
                          placeholder="Any special instructions, site conditions, or notes...">{{ old('job_notes', $formData['job_notes'] ?? '') }}</textarea>
            </div>
        </div>

        {{-- Submit Buttons --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-6">
                @if(($mode ?? null) === 'template')
                    <div class="space-y-4">
                        <div class="flex flex-col sm:flex-row gap-4">
                            <input type="text" 
                                   name="template_name" 
                                   class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" 
                                   placeholder="Template name (e.g., Standard Mulch - 2 inch depth)" 
                                   value="{{ old('template_name') }}" 
                                   required>
                            <select name="template_scope" class="w-full sm:w-56 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                <option value="global">Global Template</option>
                                <option value="client">Client Template</option>
                                <option value="property">Property Template</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full sm:w-auto px-8 py-3 bg-brand-800 hover:bg-brand-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                            </svg>
                            Save Template
                        </button>
                    </div>
                @else
                    <div class="flex flex-col sm:flex-row gap-4">
                        <button type="submit" class="flex-1 sm:flex-initial px-8 py-3 bg-brand-800 hover:bg-brand-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            {{ $editMode ? 'Recalculate' : 'Calculate Mulching' }}
                        </button>
                        @if($siteVisit)
                            <a href="{{ route('clients.show', $siteVisit->client->id) }}" class="px-6 py-3 bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-all duration-200 flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Back to Client
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function mulchingCalculator(initialCost, initialName, initialCatalogId) {
    return {
        // Measurements
        area: {{ $areaValue ?? 0 }},
        depth: {{ $depthValue ?? 0 }},
        calculatedYards: {{ $mulchYardsPreview ?? 0 }},
        
        // Material selection
        selectedMaterial: null,
        materialName: initialName || '',
        catalogId: initialCatalogId,
        unitCost: parseFloat(initialCost) || 35,
        
        init() {
            this.calculateYards();
        },
        
        calculateYards() {
            if (this.area > 0 && this.depth > 0) {
                this.calculatedYards = (this.area * (this.depth / 12)) / 27;
            } else {
                this.calculatedYards = 0;
            }
        },
        
        handleMaterialSelected(event) {
            const material = event.detail;
            this.selectedMaterial = material;
            this.materialName = material.name;
            this.catalogId = material.id;
            this.unitCost = parseFloat(material.unit_cost) || 35;
        },
        
        clearMaterial() {
            this.selectedMaterial = null;
            this.catalogId = null;
            this.materialName = '';
            this.unitCost = 35;
        }
    };
}
</script>
@endpush
@endsection
