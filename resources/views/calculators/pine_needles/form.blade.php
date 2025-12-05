@extends('layouts.sidebar')

@php
    // Get saved values or old input
    $areaValue = old('area_sqft', $formData['area_sqft'] ?? null);
    
    // Material catalog integration
    $storedMaterials = $formData['materials'] ?? [];
    $firstMaterial = collect($storedMaterials)->first();
    $selectedMaterialName = old('mulch_type', $firstMaterial['name'] ?? '');
    $selectedCatalogId = old('material_catalog_id', $firstMaterial['catalog_id'] ?? null);
    $selectedUnitCost = old('material_unit_cost', $firstMaterial['unit_cost'] ?? 7);
    
    // Calculate estimated quantity
    $strawBalesPreview = $areaValue ? round($areaValue / 50, 0) : null;
@endphp

@section('content')
<div class="max-w-5xl mx-auto py-8 px-4">
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-3">
            <div class="bg-gradient-to-br from-amber-700 to-amber-900 p-3 rounded-xl shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                </svg>
            </div>
            <h1 class="text-4xl font-bold text-gray-900">
                {{ $editMode ? 'Edit Pine Needles Data' : 'Pine Needles Calculator' }}
            </h1>
        </div>
        <p class="text-gray-600">Calculate materials and labor for pine needle or straw installations.</p>
    </div>

    @if(($mode ?? null) !== 'template' && ($siteVisit ?? null))
        @include('calculators.partials.client_info', ['siteVisit' => $siteVisit])
    @else
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-500 p-4 rounded-lg mb-6">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-blue-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <p class="font-semibold text-blue-900">Template Mode</p>
                    <p class="text-sm text-blue-700">Build a pine needles estimate without a site visit.</p>
                    @if(!empty($estimateId))
                        <p class="text-sm text-blue-600 mt-1">Target Estimate: #{{ $estimateId }}</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('calculators.pine_needles.calculate') }}" 
          x-data="pineNeedlesCalculator({{ $selectedUnitCost }}, '{{ $selectedMaterialName }}', {{ $selectedCatalogId ?? 'null' }})"
          class="space-y-6">
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

        {{-- 2. Coverage Area --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-gray-800 to-gray-700 px-6 py-4 border-b border-gray-600">
                <h2 class="text-lg font-semibold text-white flex items-center">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-white/20 text-white font-bold mr-3">2</span>
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                    </svg>
                    Project Configuration
                </h2>
            </div>
            <div class="p-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Square Footage <span class="text-red-500">*</span>
                    </label>
                    <input type="number"
                           name="area_sqft"
                           x-model="area"
                           @input="calculateBales()"
                           step="any"
                           min="0"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                           placeholder="e.g. 500"
                           value="{{ $areaValue }}">
                </div>
                
                {{-- Auto-calculated quantity preview --}}
                <div x-show="calculatedBales > 0" 
                     x-transition
                     class="bg-gradient-to-r from-amber-50 to-amber-100 border-l-4 border-amber-500 rounded-lg p-6 mt-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-amber-700 mb-1">Estimated Bales Needed</p>
                            <p class="text-3xl font-bold text-amber-900" x-text="calculatedBales + ' bales'"></p>
                            <p class="text-xs text-amber-600 mt-1">Approx. 1 bale per 50 sqft</p>
                        </div>
                        <div class="flex items-center justify-center w-20 h-20 rounded-full bg-amber-200/50">
                            <svg class="w-12 h-12 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
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
                    Materials
                </h2>
            </div>
            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Selected Material</label>
                    <div class="flex gap-3">
                        <input type="text" 
                               x-model="materialName" 
                               name="mulch_type"
                               class="flex-1 px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" 
                               placeholder="Select material from catalog"
                               readonly>
                        <button type="button" 
                                @click="$dispatch('open-material-picker')"
                                class="px-6 py-3 bg-brand-800 hover:bg-brand-700 text-white font-semibold rounded-lg shadow-md transition duration-200 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Browse Materials
                        </button>
                    </div>
                    <input type="hidden" name="material_catalog_id" x-model="catalogId">
                    <input type="hidden" name="material_unit_cost" x-model="unitCost">
                </div>

                <div x-show="materialName" x-transition class="bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-500 p-4 rounded-lg">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-semibold text-blue-700 uppercase tracking-wide">Unit Cost</p>
                            <p class="text-xl font-bold text-blue-900" x-text="'$' + parseFloat(unitCost || 0).toFixed(2)"></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-blue-700 uppercase tracking-wide">Estimated Total</p>
                            <p class="text-xl font-bold text-blue-900" x-text="'$' + (calculatedBales * parseFloat(unitCost || 0)).toFixed(2)"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. Pine Needle Tasks --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-gray-800 to-gray-700 px-6 py-4 border-b border-gray-600">
                <h2 class="text-lg font-semibold text-white flex items-center">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-white/20 text-white font-bold mr-3">4</span>
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                    Labor Tasks
                </h2>
            </div>
            <div class="p-6">
                @php
                    $savedTasks = $formData['tasks'] ?? [];
                    $savedQuantities = [];

                    foreach ($savedTasks as $taskRow) {
                        $key = str_replace(' ', '_', strtolower($taskRow['task']));
                        $savedQuantities[$key] = $taskRow['qty'] ?? null;
                    }

                    $rates = \App\Models\ProductionRate::where('calculator', 'pine_needles')
                        ->orderBy('task')
                        ->get();
                @endphp

                @if ($rates->isEmpty())
                    <div class="bg-gradient-to-r from-yellow-50 to-amber-50 border-l-4 border-yellow-400 p-4 rounded-lg mb-4">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-yellow-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <div>
                                <p class="font-semibold text-yellow-900">No pine needles production rates found</p>
                                <p class="text-sm text-yellow-700 mt-1">
                                    Please add pine needles rates in 
                                    <a href="{{ route('production-rates.index', ['calculator' => 'pine_needles']) }}" class="underline font-medium hover:text-yellow-900">Production Rates</a>
                                    or run <code class="bg-yellow-200 px-1 rounded">php artisan db:seed --class=ProductionRateSeeder</code>.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($rates as $rate)
                        @php
                            $key = $rate->task;
                            $label = ucwords(str_replace('_', ' ', $key));
                            $value = old("tasks.$key.qty", $savedQuantities[$key] ?? '');
                            $isAdvanced = str_contains($key, 'overgrown') || str_contains($key, 'palm');
                        @endphp

                        <div class="border border-gray-200 p-5 rounded-lg bg-gradient-to-br from-white to-gray-50 hover:shadow-md transition {{ $isAdvanced ? 'advanced-task hidden' : '' }}">
                            <label class="block font-semibold text-gray-900 mb-2">{{ $label }}</label>
                            <input type="number"
                                   name="tasks[{{ $key }}][qty]"
                                   step="any"
                                   min="0"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                   placeholder="Enter {{ $rate->unit }}"
                                   value="{{ $value }}">
                            <p class="text-sm text-gray-500 mt-2 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Rate: {{ number_format($rate->rate, 4) }} hrs/{{ $rate->unit }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- 5. Job Notes --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-gray-800 to-gray-700 px-6 py-4 border-b border-gray-600">
                <h2 class="text-lg font-semibold text-white flex items-center">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-white/20 text-white font-bold mr-3">5</span>
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Job Notes
                </h2>
            </div>
            <div class="p-6">
                <textarea name="job_notes"
                          rows="4"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                          placeholder="Enter any special instructions, site conditions, or notes...">{{ old('job_notes', $formData['job_notes'] ?? '') }}</textarea>
            </div>
        </div>

        {{-- Submit Buttons --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-6">
                @if(($mode ?? null) === 'template')
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 w-full">
                        <input type="text" 
                               name="template_name" 
                               class="w-full sm:flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" 
                               placeholder="Template name (e.g., Front beds straw)" 
                               value="{{ old('template_name') }}">
                        <select name="template_scope" class="w-full sm:w-48 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <option value="global" {{ old('template_scope')==='global' ? 'selected' : '' }}>Global</option>
                            <option value="client" {{ old('template_scope')==='client' ? 'selected' : '' }}>This Client</option>
                            <option value="property" {{ old('template_scope')==='property' ? 'selected' : '' }}>This Property</option>
                        </select>
                        <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-brand-800 hover:bg-brand-700 text-white font-semibold rounded-lg shadow-md transition duration-200 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                            </svg>
                            Save Template
                        </button>
                    </div>
                @else
                    <button type="submit" class="w-full sm:w-auto px-8 py-3 bg-brand-800 hover:bg-brand-700 text-white font-semibold rounded-lg shadow-md transition duration-200 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        {{ $editMode ? 'Recalculate Pine Needles' : 'Calculate Pine Needles' }}
                    </button>

                    @if($siteVisit && $siteVisit->client)
                        <a href="{{ route('clients.show', $siteVisit->client->id) }}" class="w-full sm:w-auto px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg shadow-md transition duration-200 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to Client
                        </a>
                    @endif
                @endif
            </div>
        </div>

    </form>
</div>

{{-- Material Catalog Picker Component --}}
@include('components.material-catalog-picker')

@endsection

@push('scripts')
<script>
    function pineNeedlesCalculator(initialCost, initialName, initialCatalogId) {
        return {
            area: {{ $areaValue ?? 0 }},
            calculatedBales: {{ $strawBalesPreview ?? 0 }},
            materialName: initialName || '',
            unitCost: initialCost || 7,
            catalogId: initialCatalogId || null,
            
            calculateBales() {
                if (this.area > 0) {
                    this.calculatedBales = Math.ceil(this.area / 50);
                } else {
                    this.calculatedBales = 0;
                }
            },
            
            handleMaterialSelected(event) {
                const material = event.detail;
                this.materialName = material.name;
                this.unitCost = material.unit_cost;
                this.catalogId = material.id;
            }
        }
    }
</script>
@endpush