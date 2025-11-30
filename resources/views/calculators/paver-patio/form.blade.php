@extends('layouts.sidebar')

@php
    $hasOverrides = collect(old())->keys()->filter(fn($key) => str_starts_with($key, 'override_'))->isNotEmpty();
    $overrideChecked = old('materials_override_enabled', $formData['materials_override_enabled'] ?? $hasOverrides);

    $lengthValue = old('length', $formData['length'] ?? null);
    $widthValue = old('width', $formData['width'] ?? null);
    $paverTypeValue = old('paver_type', $formData['paver_type'] ?? '');
    $edgeSelection = old('edge_restraint', $formData['edge_restraint'] ?? '');
    $edgeLfValue = old('edging_linear_feet', $formData['edging_linear_feet'] ?? null);

    // Get materials from catalog if available - support multiple materials
    $storedMaterials = $formData['materials'] ?? [];
    $selectedMaterials = collect($storedMaterials);

    $areaSqft = ($lengthValue && $widthValue) ? round($lengthValue * $widthValue, 2) : null;
    $paverCoverage = 0.94;
    $paverCountEstimate = $areaSqft ? (int) ceil($areaSqft / $paverCoverage) : null;
    $baseDepthFeet = 2.5 / 12;
    $baseTonsEstimate = $areaSqft ? (int) ceil(($areaSqft * $baseDepthFeet) / 21.6) : null;
    $edgeLfEstimate = $edgeLfValue ?? ($areaSqft ? round($areaSqft / 20, 2) : null);
    $polymericCoverageSqft = 60;
    $polymericBagsEstimate = $areaSqft ? (int) ceil($areaSqft / $polymericCoverageSqft) : null;

    $defaultUnitCosts = [
        'base_unit_cost' => 45.00,
        'plastic_edge_unit_cost' => 5.00,
        'concrete_edge_unit_cost' => 12.00,
        'polymeric_sand_unit_cost' => 28.00,
    ];

    $edgeCostLookup = [
        'plastic' => $defaultUnitCosts['plastic_edge_unit_cost'],
        'concrete' => $defaultUnitCosts['concrete_edge_unit_cost'],
    ];

    $customMaterials = old('custom_materials', $formData['custom_materials'] ?? []);
@endphp

@section('content')
<div class="max-w-5xl mx-auto py-8 px-4">
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-6">
            <div class="flex-shrink-0 w-16 h-16 rounded-xl bg-gradient-to-br from-amber-700 to-orange-800 flex items-center justify-center shadow-lg">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    {{ $editMode ? 'Edit Paver Patio' : 'Paver Patio Calculator' }}
                </h1>
                <p class="text-gray-600 mt-1">Estimate materials, labor, and project costs</p>
            </div>
        </div>

        @if(($mode ?? null) !== 'template' && $siteVisit)
            @include('calculators.partials.client_info', ['siteVisit' => $siteVisit])
        @else
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-l-4 border-blue-400 rounded-lg shadow-sm p-4 mb-6">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-blue-400 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">Template Mode</p>
                        <p class="text-sm text-gray-700 mt-1">Building a paver patio template without a site visit</p>
                        @if(!empty($estimateId))
                            <p class="text-sm text-gray-600 mt-1">Target Estimate: #{{ $estimateId }}</p>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    <form method="POST" action="{{ route('calculators.patio.calculate') }}"
          x-data="paverPatioCalculator()"
          @material-selected.window="handleMaterialSelected($event)">
        @csrf
        <input type="hidden" name="mode" value="{{ $mode ?? '' }}">
        @if(!empty($estimateId))
            <input type="hidden" name="estimate_id" value="{{ $estimateId }}">
        @endif

        {{-- Edit Mode: Calculation ID --}}
        @if ($editMode && isset($calculation))
            <input type="hidden" name="calculation_id" value="{{ $calculation->id }}">
        @endif

        {{-- Required --}}
        @if(($mode ?? null) !== 'template')
            <input type="hidden" name="site_visit_id" value="{{ $siteVisitId }}">
        @endif

        {{-- 1️⃣ Crew & Logistics --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-gradient-to-br from-gray-800 to-gray-700 flex items-center justify-center">
                    <span class="text-white font-bold text-sm">1</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Crew & Logistics</h2>
                    <p class="text-sm text-gray-600">Labor and overhead settings</p>
                </div>
            </div>
            @include('calculators.partials.overhead_inputs')
        </div>

        {{-- 2️⃣ Patio Dimensions --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-gradient-to-br from-gray-800 to-gray-700 flex items-center justify-center">
                        <span class="text-white font-bold text-sm">2</span>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Patio Dimensions</h2>
                        <p class="text-sm text-gray-600">Length, width, and configuration</p>
                    </div>
                </div>
                <span id="patioAreaBadge" class="text-sm font-semibold px-3 py-1 rounded-full {{ $areaSqft ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600' }}" data-empty-message="Enter dimensions" data-prefix="Area: ">{{ $areaSqft ? 'Area: '.number_format($areaSqft, 2).' sqft' : 'Enter dimensions' }}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-200 rounded-lg p-4">
                    <label class="block font-semibold text-gray-900 mb-2">Length (ft)</label>
                    <input type="number" step="0.1" name="length" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                           value="{{ $lengthValue }}" required>
                    <p class="text-xs text-gray-600 mt-2">Paired with width to calculate patio area</p>
                </div>

                <div class="bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-200 rounded-lg p-4">
                    <label class="block font-semibold text-gray-900 mb-2">Width (ft)</label>
                    <input type="number" step="0.1" name="width" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                           value="{{ $widthValue }}" required>
                    <p class="text-xs text-gray-600 mt-2">Used for material quantities and labor</p>
                </div>

                <div class="bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-200 rounded-lg p-4">
                    <label class="block font-semibold text-gray-900 mb-2">Edge Restraint Type</label>
                    <select name="edge_restraint" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent" required>
                        <option value="">-- Choose Edge Type --</option>
                        <option value="plastic" {{ $edgeSelection === 'plastic' ? 'selected' : '' }}>Plastic</option>
                        <option value="concrete" {{ $edgeSelection === 'concrete' ? 'selected' : '' }}>Concrete</option>
                    </select>
                    <p class="text-xs text-gray-600 mt-2">Plastic or concrete edge restraint</p>
                </div>

                <div class="bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-200 rounded-lg p-4">
                    <label class="block font-semibold text-gray-900 mb-2">Edging Linear Feet</label>
                    <input type="number" step="0.1" name="edging_linear_feet" 
                           x-ref="edgingLfInput"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                           value="{{ $edgeLfValue }}" 
                           placeholder="Enter linear feet">
                    <p class="text-xs text-gray-600 mt-2">Specify exact LF for edge restraints</p>
                </div>
            </div>
            
            {{-- Auto-calculated quantities preview --}}
            <div x-show="length > 0 && width > 0" 
                 x-transition
                 class="bg-gradient-to-r from-amber-50 to-orange-100 border-l-4 border-amber-500 rounded-lg p-6 mt-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-xs font-semibold text-amber-700 mb-1 uppercase tracking-wide">Area</p>
                        <p class="text-2xl font-bold text-amber-900" x-text="area.toFixed(2) + ' sqft'"></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-amber-700 mb-1 uppercase tracking-wide">Pavers Est.</p>
                        <p class="text-2xl font-bold text-amber-900" x-text="paverCount.toLocaleString() + ' stones'"></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-amber-700 mb-1 uppercase tracking-wide">Base Gravel</p>
                        <p class="text-2xl font-bold text-amber-900" x-text="baseTons.toLocaleString() + ' tons'"></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-amber-700 mb-1 uppercase tracking-wide">Polymeric Sand</p>
                        <p class="text-2xl font-bold text-amber-900" x-text="polymericBags.toLocaleString() + ' bags'"></p>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3️⃣ Material Selection --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-gradient-to-br from-gray-800 to-gray-700 flex items-center justify-center">
                    <span class="text-white font-bold text-sm">3</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Material Selection</h2>
                    <p class="text-sm text-gray-600">Add materials from your catalog. You can add multiple materials.</p>
                </div>
            </div>

            <div class="space-y-6">
                {{-- Material Catalog Picker --}}
                <div>
                    @include('components.material-catalog-picker')
                </div>
                
                {{-- Selected Materials Display --}}
                <div x-show="selectedMaterials.length > 0" class="space-y-3">
                    <h3 class="font-semibold text-gray-900">Selected Materials</h3>
                    <template x-for="(material, index) in selectedMaterials" :key="index">
                        <div class="bg-gradient-to-r from-amber-50 to-orange-50 border-2 border-amber-500 rounded-xl p-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <svg class="w-5 h-5 text-amber-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <p class="text-lg font-bold text-amber-900" x-text="material.name"></p>
                                    </div>
                                    <p class="text-sm font-semibold text-amber-700 ml-7">
                                        $<span x-text="parseFloat(material.unit_cost).toFixed(2)"></span> per <span x-text="material.unit || 'ea'"></span>
                                    </p>
                                    <p class="text-xs text-amber-600 mt-1 ml-7" x-show="material.description" x-text="material.description"></p>
                                    
                                    {{-- Quantity Input --}}
                                    <div class="mt-3 ml-7">
                                        <label class="block text-sm font-semibold text-amber-900 mb-1">Quantity:</label>
                                        <input type="number" 
                                               :name="'materials[' + index + '][quantity]'"
                                               x-model="material.quantity"
                                               min="0"
                                               step="any"
                                               class="w-32 px-3 py-2 border border-amber-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                               placeholder="Qty">
                                        {{-- Hidden fields for material data --}}
                                        <input type="hidden" :name="'materials[' + index + '][catalog_id]'" :value="material.catalog_id">
                                        <input type="hidden" :name="'materials[' + index + '][name]'" :value="material.name">
                                        <input type="hidden" :name="'materials[' + index + '][unit_cost]'" :value="material.unit_cost">
                                        <input type="hidden" :name="'materials[' + index + '][unit]'" :value="material.unit || 'ea'">
                                    </div>
                                </div>
                                <button type="button"
                                        @click="removeMaterial(index)"
                                        class="ml-4 p-3 text-amber-600 hover:text-amber-800 hover:bg-amber-100 rounded-lg transition-colors">
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

        {{-- 4️⃣ Additional Materials --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-gradient-to-br from-gray-800 to-gray-700 flex items-center justify-center">
                        <span class="text-white font-bold text-sm">4</span>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Additional Materials</h2>
                        <p class="text-sm text-gray-600">Optional items not auto-calculated</p>
                    </div>
                </div>
                <button type="button" id="addCustomMaterial" class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-800 hover:bg-brand-700 text-white font-semibold rounded-lg shadow-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Material
                </button>
            </div>

            <div id="customMaterialRows" class="space-y-4">
                @if (!empty($customMaterials))
                    @foreach ($customMaterials as $index => $customMaterial)
                        @include('calculators.partials.custom-material-row', [
                            'rowIndex' => $index,
                            'material' => $customMaterial,
                        ])
                    @endforeach
                @else
                    @include('calculators.partials.custom-material-row', [
                        'rowIndex' => 0,
                        'material' => [],
                    ])
                @endif
            </div>

            <template id="customMaterialTemplate">
                @include('calculators.partials.custom-material-row', [
                    'rowIndex' => '__INDEX__',
                    'material' => [],
                ])
            </template>
        </div>

        {{-- Submit Buttons --}}
        <div class="flex flex-col sm:flex-row gap-4 items-center justify-between mt-8">
            @if(($mode ?? null) === 'template')
                <div class="w-full flex flex-col lg:flex-row gap-4">
                    <input type="text" name="template_name" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent" placeholder="Template name (e.g., 12x20 patio)" value="{{ old('template_name') }}">
                    <select name="template_scope" class="px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                        <option value="global" {{ old('template_scope')==='global' ? 'selected' : '' }}>Global</option>
                        <option value="client" {{ old('template_scope')==='client' ? 'selected' : '' }}>This Client</option>
                        <option value="property" {{ old('template_scope')==='property' ? 'selected' : '' }}>This Property</option>
                    </select>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-8 py-3 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                        </svg>
                        Save Template
                    </button>
                </div>
            @else
                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    {{ $editMode ? 'Recalculate Paver Patio' : 'Calculate Paver Patio' }}
                </button>
                <a href="{{ route('clients.show', $siteVisit->client->id) }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Client
                </a>
            @endif
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Alpine.js component for paver patio calculator
    function paverPatioCalculator() {
        return {
            // Dimensions
            length: {{ $lengthValue ?? 0 }},
            width: {{ $widthValue ?? 0 }},
            area: 0,
            paverCount: 0,
            baseTons: 0,
            polymericBags: 0,
            
            // Material selection - support multiple materials
            selectedMaterials: @json($formData['materials'] ?? []),
            
            init() {
                this.calculateQuantities();
                
                // Watch for dimension changes
                this.$watch('length', () => this.calculateQuantities());
                this.$watch('width', () => this.calculateQuantities());
            },
            
            calculateQuantities() {
                if (this.length > 0 && this.width > 0) {
                    this.area = this.length * this.width;
                    this.paverCount = Math.ceil(this.area / 0.94);
                    this.baseTons = Math.ceil((this.area * (2.5 / 12)) / 21.6);
                    this.polymericBags = Math.ceil(this.area / 60);
                } else {
                    this.area = 0;
                    this.paverCount = 0;
                    this.baseTons = 0;
                    this.polymericBags = 0;
                }
            },
            
            handleMaterialSelected(event) {
                this.addMaterial(event.detail);
            },
            
            addMaterial(material) {
                // Check if material already exists
                const exists = this.selectedMaterials.find(m => m.catalog_id === material.id);
                if (!exists) {
                    this.selectedMaterials.push({
                        catalog_id: material.id,
                        name: material.name,
                        unit_cost: parseFloat(material.unit_cost) || 0,
                        unit: material.unit || 'ea',
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

    document.addEventListener('DOMContentLoaded', function () {
        const lengthInput = document.querySelector('input[name="length"]');
        const widthInput = document.querySelector('input[name="width"]');
        const customRowsContainer = document.getElementById('customMaterialRows');
        const customTemplate = document.getElementById('customMaterialTemplate');
        const addCustomMaterialButton = document.getElementById('addCustomMaterial');



        const parseNumber = (value) => {
            const num = parseFloat(value);
            return Number.isFinite(num) ? num : null;
        };

        const formatQty = (value, asInt = false) => {
            if (value === null) {
                return '--';
            }
            if (asInt) {
                return Number(value).toLocaleString();
            }
            return Number(value).toFixed(2);
        };

        const formatCurrency = (value) => {
            return `$${Number(value).toFixed(2)}`;
        };

        const resolveCost = (input, fallback) => {
            const parsed = input ? parseNumber(input.value) : null;
            return parsed ?? fallback;
        };



        const recalcCustomMaterials = () => {
            if (!customRowsContainer) return;
            customRowsContainer.querySelectorAll('[data-custom-row]').forEach((row) => {
                const qtyInput = row.querySelector('[data-custom-qty]');
                const costInput = row.querySelector('[data-custom-cost]');
                const totalEl = row.querySelector('[data-custom-total]');
                if (!totalEl) return;

                const qty = qtyInput ? parseNumber(qtyInput.value) : null;
                const cost = costInput ? parseNumber(costInput.value) : null;

                if (qty === null || cost === null) {
                    totalEl.textContent = '--';
                } else {
                    totalEl.textContent = formatCurrency(qty * cost);
                }
            });
        };

        const registerCustomRow = (row) => {
            if (!row) return;
            const qtyInput = row.querySelector('[data-custom-qty]');
            const costInput = row.querySelector('[data-custom-cost]');
            const removeBtn = row.querySelector('[data-action="remove-custom-material"]');

            [qtyInput, costInput].forEach((input) => {
                if (!input) return;
                input.addEventListener('input', recalcCustomMaterials);
            });

            if (removeBtn) {
                removeBtn.addEventListener('click', () => {
                    row.remove();
                    recalcCustomMaterials();
                });
            }
        };

        const getNextCustomIndex = () => {
            if (!customRowsContainer) return 0;
            const indexes = Array.from(customRowsContainer.querySelectorAll('[data-custom-row]'))
                .map((row) => parseInt(row.dataset.customIndex ?? '', 10))
                .filter((value) => Number.isFinite(value));
            return indexes.length ? Math.max(...indexes) + 1 : 1;
        };

        let customIndex = getNextCustomIndex();

        const addCustomRow = () => {
            if (!customTemplate || !customRowsContainer) return;
            const html = customTemplate.innerHTML.replace(/__INDEX__/g, customIndex++);
            const wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();
            const newRow = wrapper.firstElementChild;
            if (!newRow) return;
            customRowsContainer.appendChild(newRow);
            registerCustomRow(newRow);
            recalcCustomMaterials();
        };

        if (customRowsContainer) {
            customRowsContainer.querySelectorAll('[data-custom-row]').forEach(registerCustomRow);
            recalcCustomMaterials();
        }

        if (addCustomMaterialButton) {
            addCustomMaterialButton.addEventListener('click', addCustomRow);
        }

        // Sync dimension inputs with Alpine.js
        if (lengthInput && widthInput) {
            const alpineData = Alpine.$data(document.querySelector('form[x-data]'));
            
            lengthInput.addEventListener('input', (e) => {
                if (alpineData) {
                    alpineData.length = parseFloat(e.target.value) || 0;
                }
            });
            
            widthInput.addEventListener('input', (e) => {
                if (alpineData) {
                    alpineData.width = parseFloat(e.target.value) || 0;
                }
            });
        }
    });
</script>
@endpush
