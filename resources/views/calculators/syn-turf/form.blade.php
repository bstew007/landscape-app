@extends('layouts.sidebar')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="synTurfCalculator()">
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-3">
            <div class="flex-shrink-0">
                <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-green-600 to-green-800 flex items-center justify-center shadow-lg">
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 13v6m4-6v6m4-6v6m4-6v6" stroke="currentColor" opacity="0.5"/>
                    </svg>
                </div>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    {{ $editMode ? 'Edit Synthetic Turf Calculation' : 'Synthetic Turf Calculator' }}
                </h1>
                <p class="text-gray-600 mt-1">Artificial turf installation estimator</p>
            </div>
        </div>
    </div>

    @if(($mode ?? null) !== 'template' && $siteVisit)
        @include('calculators.partials.client_info', ['siteVisit' => $siteVisit])
    @else
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-blue-900">Template Mode Active</p>
                    <p class="text-sm text-blue-700 mt-1">Building synthetic turf template without site visit</p>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('calculators.syn_turf.calculate') }}" class="space-y-6">
        @csrf
        <input type="hidden" name="mode" value="{{ $mode ?? '' }}">
        @if(!empty($estimateId))
            <input type="hidden" name="estimate_id" value="{{ $estimateId }}">
        @endif
        @if ($editMode && isset($calculation))
            <input type="hidden" name="calculation_id" value="{{ $calculation->id }}">
        @endif
        @if(($mode ?? null) !== 'template')
            <input type="hidden" name="site_visit_id" value="{{ $siteVisitId }}">
        @endif

        {{-- 1️⃣ Crew & Logistics --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <div class="flex items-center">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-700 text-white font-bold mr-3">1</span>
                    <h2 class="text-xl font-bold text-gray-900">Crew & Logistics</h2>
                </div>
            </div>
            <div class="p-6">
                @include('calculators.partials.overhead_inputs')
            </div>
        </div>

        {{-- 2️⃣ Project Configuration --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <div class="flex items-center">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-700 text-white font-bold mr-3">2</span>
                    <h2 class="text-xl font-bold text-gray-900">Project Configuration</h2>
                </div>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Project Area (sq ft) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               name="area_sqft" 
                               x-model="area"
                               @input="calculateQuantities()"
                               min="1" 
                               step="any" 
                               required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               value="{{ old('area_sqft', $formData['area_sqft'] ?? '') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Perimeter / Edging (linear ft) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               name="perimeter_lf" 
                               x-model="perimeter"
                               @input="calculateQuantities()"
                               min="0" 
                               step="any" 
                               required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               value="{{ old('perimeter_lf', $formData['perimeter_lf'] ?? '') }}">
                        <p class="text-xs text-gray-500 mt-1">For edging board calculation</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Excavation Depth (inches)</label>
                        <input type="number" 
                               name="excavation_depth" 
                               x-model="excavationDepth"
                               @input="calculateQuantities()"
                               min="0" 
                               step="0.25"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               value="{{ old('excavation_depth', $formData['excavation_depth'] ?? 3) }}">
                        <p class="text-xs text-gray-500 mt-1">Typical: 3-4 inches</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">ABC Base Depth (inches)</label>
                        <input type="number" 
                               name="abc_depth" 
                               x-model="abcDepth"
                               @input="calculateQuantities()"
                               min="0" 
                               step="0.25"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               value="{{ old('abc_depth', $formData['abc_depth'] ?? 2) }}">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Rock Dust Depth (inches)</label>
                        <input type="number" 
                               name="rock_dust_depth" 
                               x-model="rockDustDepth"
                               @input="calculateQuantities()"
                               min="0" 
                               step="0.25"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               value="{{ old('rock_dust_depth', $formData['rock_dust_depth'] ?? 1) }}">
                    </div>
                </div>

                @include('calculators.partials.calculated_quantities_box', [
                    'color' => 'blue',
                    'quantities' => [
                        ['label' => 'Excavation', 'value' => "excavationCY.toFixed(2) + ' cy'", 'alpine' => true],
                        ['label' => 'ABC Base', 'value' => "abcCY.toFixed(2) + ' cy'", 'alpine' => true],
                        ['label' => 'Rock Dust', 'value' => "rockDustCY.toFixed(2) + ' cy'", 'alpine' => true],
                        ['label' => 'Infill Bags', 'value' => "infillBags + ' bags'", 'alpine' => true],
                        ['label' => 'Edging Boards', 'value' => "edgingBoards + ' boards'", 'alpine' => true],
                        ['label' => 'Weed Barrier', 'value' => "weedBarrierRolls + ' rolls'", 'alpine' => true],
                        ['label' => 'Turf Area', 'value' => "(area || 0) + ' sqft'", 'alpine' => true],
                    ]
                ])

                @include('calculators.partials.excavation_method_selector', [
                    'color' => 'green',
                    'formData' => $formData
                ])
            </div>
        </div>

        {{-- 3️⃣ Materials --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" @material-selected.window="handleMaterialSelected($event)">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <div class="flex items-center">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-700 text-white font-bold mr-3">3</span>
                    <h2 class="text-xl font-bold text-gray-900">Materials</h2>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <p class="text-sm text-gray-600">Select materials from your catalog. Quantities are calculated automatically based on project configuration.</p>
                
                @include('components.material-catalog-picker')
                
                {{-- Selected Materials Display --}}
                <div x-show="selectedMaterials.length > 0" class="mt-6 space-y-3">
                    <h3 class="font-semibold text-gray-900">Selected Materials</h3>
                    <template x-for="(material, index) in selectedMaterials" :key="index">
                        <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-500 rounded-xl p-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <p class="text-lg font-bold text-green-900" x-text="material.name"></p>
                                    </div>
                                    <div class="ml-7 space-y-1">
                                        <p class="text-sm font-semibold text-green-700">
                                            $<span x-text="parseFloat(material.unit_cost).toFixed(2)"></span> per <span x-text="material.unit || 'unit'"></span>
                                        </p>
                                        <div class="flex items-center gap-3">
                                            <label class="text-sm text-green-800">Quantity:</label>
                                            <input type="number" 
                                                   :name="'materials[' + index + '][quantity]'"
                                                   x-model="material.quantity"
                                                   min="0"
                                                   step="any"
                                                   class="w-32 px-3 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm">
                                            <span class="text-sm text-green-700">
                                                Total: $<span x-text="(material.quantity * material.unit_cost).toFixed(2)"></span>
                                            </span>
                                        </div>
                                    </div>
                                    <input type="hidden" :name="'materials[' + index + '][catalog_id]'" :value="material.catalog_id">
                                    <input type="hidden" :name="'materials[' + index + '][name]'" :value="material.name">
                                    <input type="hidden" :name="'materials[' + index + '][unit_cost]'" :value="material.unit_cost">
                                    <input type="hidden" :name="'materials[' + index + '][unit]'" :value="material.unit">
                                </div>
                                <button type="button"
                                        @click="removeMaterial(index)"
                                        class="ml-4 p-3 text-green-600 hover:text-green-800 hover:bg-green-100 rounded-lg transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- 4️⃣ Labor Tasks --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <div class="flex items-center">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-700 text-white font-bold mr-3">4</span>
                    <h2 class="text-xl font-bold text-gray-900">Labor Tasks</h2>
                </div>
            </div>
            <div class="p-6">
                @include('calculators.partials.labor_tasks_section', [
                    'calculator' => 'syn_turf',
                    'formData' => $formData,
                    'color' => 'green',
                    'includeExcavation' => false
                ])
            </div>
        </div>

        {{-- 5️⃣ Job Notes --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <div class="flex items-center">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-700 text-white font-bold mr-3">5</span>
                    <h2 class="text-xl font-bold text-gray-900">Job Notes</h2>
                </div>
            </div>
            <div class="p-6">
                <textarea name="job_notes" 
                          rows="4" 
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition resize-none"
                          placeholder="Any special instructions, site conditions, or notes...">{{ old('job_notes', $formData['job_notes'] ?? '') }}</textarea>
            </div>
        </div>

        {{-- Submit Buttons --}}
        <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
            @if(($mode ?? null) === 'template')
                <div class="w-full flex flex-col lg:flex-row gap-4">
                    <input type="text" name="template_name" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" placeholder="Template name (e.g., Standard 500 sqft turf)" value="{{ old('template_name') }}">
                    <select name="template_scope" class="px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="global" {{ old('template_scope')==='global' ? 'selected' : '' }}>Global</option>
                        <option value="client" {{ old('template_scope')==='client' ? 'selected' : '' }}>This Client</option>
                        <option value="property" {{ old('template_scope')==='property' ? 'selected' : '' }}>This Property</option>
                    </select>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-8 py-3 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                        </svg>
                        Save Template
                    </button>
                </div>
            @else
                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    {{ $editMode ? 'Recalculate' : 'Calculate Synthetic Turf' }}
                </button>
                @if($siteVisit)
                    <a href="{{ route('clients.show', $siteVisit->client->id) }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Client
                    </a>
                @endif
            @endif
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function synTurfCalculator() {
    return {
        // Dimensions
        area: {{ old('area_sqft', $formData['area_sqft'] ?? 0) }},
        perimeter: {{ old('perimeter_lf', $formData['perimeter_lf'] ?? 0) }},
        excavationDepth: {{ old('excavation_depth', $formData['excavation_depth'] ?? 3) }},
        abcDepth: {{ old('abc_depth', $formData['abc_depth'] ?? 2) }},
        rockDustDepth: {{ old('rock_dust_depth', $formData['rock_dust_depth'] ?? 1) }},
        excavationMethod: '{{ old('excavation_method', $formData['excavation_method'] ?? 'manual') }}',
        
        // Calculated quantities
        excavationCY: 0,
        abcCY: 0,
        rockDustCY: 0,
        infillBags: 0,
        edgingBoards: 0,
        weedBarrierRolls: 0,
        
        // Labor quantities - will be auto-populated
        laborQuantities: {
            excavation_manual: 0,
            excavation_mini_skid: 0,
            excavation_skid_steer: 0,
            base_install: 0,
            turf_install: 0,
            edging_install: 0,
            infill_application: 0
        },
        
        // Materials
        selectedMaterials: @json($formData['materials'] ?? []),
        
        init() {
            this.calculateQuantities();
            this.updateLaborQuantities();
            
            // Watch for changes
            this.$watch('area', () => {
                this.calculateQuantities();
                this.updateLaborQuantities();
            });
            this.$watch('perimeter', () => {
                this.calculateQuantities();
                this.updateLaborQuantities();
            });
            this.$watch('excavationDepth', () => {
                this.calculateQuantities();
                this.updateLaborQuantities();
            });
            this.$watch('abcDepth', () => {
                this.calculateQuantities();
                this.updateLaborQuantities();
            });
            this.$watch('rockDustDepth', () => {
                this.calculateQuantities();
                this.updateLaborQuantities();
            });
            this.$watch('excavationMethod', () => {
                this.updateLaborQuantities();
            });
        },
        
        calculateQuantities() {
            const area = parseFloat(this.area) || 0;
            const perimeter = parseFloat(this.perimeter) || 0;
            const excDepth = parseFloat(this.excavationDepth) || 0;
            const abcDepth = parseFloat(this.abcDepth) || 0;
            const rockDepth = parseFloat(this.rockDustDepth) || 0;
            
            // Calculate cubic yards from sqft and depth in inches
            // Formula: (sqft * (depth_in / 12)) / 27
            this.excavationCY = area > 0 && excDepth > 0 ? (area * (excDepth / 12)) / 27 : 0;
            this.abcCY = area > 0 && abcDepth > 0 ? (area * (abcDepth / 12)) / 27 : 0;
            this.rockDustCY = area > 0 && rockDepth > 0 ? (area * (rockDepth / 12)) / 27 : 0;
            
            // Material quantities
            this.infillBags = area > 0 ? Math.ceil(area / 50) : 0; // 50 sqft per bag
            this.edgingBoards = perimeter > 0 ? Math.ceil(perimeter / 20) : 0; // 20 ft boards
            this.weedBarrierRolls = area > 0 ? Math.ceil(area / 1800) : 0; // 1800 sqft per roll
        },
        
        updateLaborQuantities() {
            const area = parseFloat(this.area) || 0;
            const perimeter = parseFloat(this.perimeter) || 0;
            
            // Reset all excavation methods to 0
            this.laborQuantities.excavation_manual = 0;
            this.laborQuantities.excavation_mini_skid = 0;
            this.laborQuantities.excavation_skid_steer = 0;
            
            // Set the appropriate excavation method quantity based on selection
            // Round to 2 decimals
            if (this.excavationMethod === 'manual') {
                this.laborQuantities.excavation_manual = Math.round(this.excavationCY * 100) / 100;
            } else if (this.excavationMethod === 'mini_skid') {
                this.laborQuantities.excavation_mini_skid = Math.round(this.excavationCY * 100) / 100;
            } else if (this.excavationMethod === 'skid_steer') {
                this.laborQuantities.excavation_skid_steer = Math.round(this.excavationCY * 100) / 100;
            }
            
            // Base install = ABC + Rock Dust (both in cubic yards)
            // Round to 2 decimals
            this.laborQuantities.base_install = Math.round((this.abcCY + this.rockDustCY) * 100) / 100;
            
            // Turf install = project area (sqft)
            // Round to 2 decimals
            this.laborQuantities.turf_install = Math.round(area * 100) / 100;
            
            // Edging install = perimeter (linear feet)
            // Round to 2 decimals
            this.laborQuantities.edging_install = Math.round(perimeter * 100) / 100;
            
            // Infill application = project area (sqft)
            // Round to 2 decimals
            this.laborQuantities.infill_application = Math.round(area * 100) / 100;
        },
        
        handleMaterialSelected(event) {
            this.addMaterial(event.detail);
        },
        
        addMaterial(material) {
            const exists = this.selectedMaterials.find(m => m.catalog_id === material.id);
            if (!exists) {
                this.selectedMaterials.push({
                    catalog_id: material.id,
                    name: material.name,
                    unit_cost: parseFloat(material.unit_cost) || 0,
                    unit: material.unit || 'unit',
                    description: material.description || '',
                    quantity: 1
                });
            }
        },
        
        removeMaterial(index) {
            this.selectedMaterials.splice(index, 1);
        }
    };
}
</script>
@endpush
