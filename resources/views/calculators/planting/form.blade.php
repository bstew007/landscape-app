@extends('layouts.sidebar')

@php
    // Get saved values or old input
    $savedQty = $formData['task_inputs'] ?? [];
    $unitCosts = $formData['unit_costs'] ?? [];
    
    // Material catalog integration - support multiple plants
    $storedMaterials = $formData['materials'] ?? [];
    $selectedPlants = collect($storedMaterials);
@endphp

@section('content')
<div class="max-w-5xl mx-auto py-8 px-4">
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-3">
            <div class="bg-gradient-to-br from-green-600 to-green-800 p-3 rounded-xl shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                </svg>
            </div>
            <h1 class="text-4xl font-bold text-gray-900">
                {{ $editMode ? 'Edit Planting Data' : 'Planting Calculator' }}
            </h1>
        </div>
        <p class="text-gray-600">Calculate labor and select plants from your catalog for installation.</p>
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
                    <p class="text-sm text-blue-700">Build a planting estimate without a site visit.</p>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('calculators.planting.calculate') }}"
          x-data="plantingCalculator()"
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

        {{-- 2. Plant Selection --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-gray-800 to-gray-700 px-6 py-4 border-b border-gray-600">
                <h2 class="text-lg font-semibold text-white flex items-center">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-white/20 text-white font-bold mr-3">2</span>
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                    </svg>
                    Plant Selection
                </h2>
            </div>
            <div class="p-6 space-y-6">
                <p class="text-sm text-gray-600">Add plants from your material catalog. You can add multiple plants.</p>
                
                {{-- Material Catalog Picker --}}
                <div>
                    @include('components.material-catalog-picker')
                </div>
                
                {{-- Selected Plants Display --}}
                <div x-show="selectedPlants.length > 0" class="space-y-3">
                    <h3 class="font-semibold text-gray-900">Selected Plants</h3>
                    <template x-for="(plant, index) in selectedPlants" :key="index">
                        <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-500 rounded-xl p-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <p class="text-lg font-bold text-green-900" x-text="plant.name"></p>
                                    </div>
                                    <p class="text-sm font-semibold text-green-700 ml-7">
                                        $<span x-text="parseFloat(plant.unit_cost).toFixed(2)"></span> per <span x-text="plant.unit || 'ea'"></span>
                                    </p>
                                    <p class="text-xs text-green-600 mt-1 ml-7" x-show="plant.description" x-text="plant.description"></p>
                                    
                                    {{-- Quantity Input --}}
                                    <div class="mt-3 ml-7">
                                        <label class="block text-sm font-semibold text-green-900 mb-1">Quantity:</label>
                                        <input type="number" 
                                               :name="'plants[' + index + '][quantity]'"
                                               x-model="plant.quantity"
                                               min="1"
                                               step="1"
                                               class="w-32 px-3 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                               placeholder="Qty">
                                        {{-- Hidden fields for plant data --}}
                                        <input type="hidden" :name="'plants[' + index + '][catalog_id]'" :value="plant.id">
                                        <input type="hidden" :name="'plants[' + index + '][name]'" :value="plant.name">
                                        <input type="hidden" :name="'plants[' + index + '][unit_cost]'" :value="plant.unit_cost">
                                        <input type="hidden" :name="'plants[' + index + '][unit]'" :value="plant.unit || 'ea'">
                                    </div>
                                </div>
                                <button type="button"
                                        @click="removePlant(index)"
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

        {{-- 3. Labor Quantities --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-gray-800 to-gray-700 px-6 py-4 border-b border-gray-600">
                <h2 class="text-lg font-semibold text-white flex items-center">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-white/20 text-white font-bold mr-3">3</span>
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    Labor Quantities
                </h2>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-600 bg-gray-50 p-3 rounded-lg border border-gray-200 mb-6">
                    <strong>💡 Tip:</strong> Enter quantities for each plant type to calculate labor. Rates include facing and watering.
                </p>

                @php
                    $rates = \App\Models\ProductionRate::where('calculator', 'planting')
                        ->orderBy('task')
                        ->get();
                @endphp

                @if ($rates->isEmpty())
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-yellow-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <div>
                                <p class="font-semibold text-yellow-900">No production rates found</p>
                                <p class="text-sm text-yellow-700 mt-1">Please run: <code class="bg-yellow-100 px-2 py-1 rounded">php artisan db:seed --class=ProductionRateSeeder</code></p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($rates as $rate)
                        @php
                            $key = $rate->task;
                            $label = ucwords(str_replace('_', ' ', $key));
                            $value = old("tasks.$key.qty", $savedQty[$key] ?? '');
                        @endphp
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition">
                            <label class="block font-semibold text-gray-900 mb-2">{{ $label }}</label>
                            <input type="number"
                                   name="tasks[{{ $key }}][qty]"
                                   step="any"
                                   min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent transition"
                                   placeholder="Quantity"
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
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-white/20 text-white font-bold mr-3">4</span>
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Job Notes (Optional)
                </h2>
            </div>
            </div>
            <div class="p-6">
                <textarea name="job_notes" 
                          rows="4" 
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent transition"
                          placeholder="Any special notes or instructions for this job...">{{ old('job_notes', $formData['job_notes'] ?? '') }}</textarea>
            </div>
        </div>

        {{-- Actions --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            @if(($mode ?? null) === 'template')
                <div class="flex flex-col sm:flex-row gap-4 items-center">
                    <input type="text" 
                           name="template_name" 
                           class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent transition" 
                           placeholder="Template name (e.g., Spring Annuals)" 
                           value="{{ old('template_name') }}" 
                           required>
                    <select name="template_scope" class="w-full sm:w-56 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent transition">
                        <option value="global">Global Template</option>
                        <option value="client">Client Template</option>
                        <option value="property">Property Template</option>
                    </select>
                </div>
                <button type="submit" class="w-full sm:w-auto px-8 py-3 bg-brand-800 hover:bg-brand-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center mt-4">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                    </svg>
                    Save Template
                </button>
            @else
                <div class="flex flex-col sm:flex-row gap-4">
                    <button type="submit" class="flex-1 sm:flex-initial px-8 py-3 bg-brand-800 hover:bg-brand-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        {{ $editMode ? 'Recalculate' : 'Calculate Planting' }}
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
    </form>
</div>

@push('scripts')
<script>
function plantingCalculator() {
    return {
        selectedPlants: @json($formData['plants'] ?? []),
        
        init() {
            // Listen for material selection from catalog
            window.addEventListener('material-selected', (event) => {
                this.addPlant(event.detail);
            });
        },
        
        addPlant(material) {
            // Check if plant already exists
            const exists = this.selectedPlants.find(p => p.catalog_id === material.id);
            if (!exists) {
                this.selectedPlants.push({
                    catalog_id: material.id,
                    name: material.name,
                    unit_cost: parseFloat(material.unit_cost) || 0,
                    unit: material.unit || 'ea',
                    description: material.description || '',
                    quantity: 1
                });
            }
        },
        
        removePlant(index) {
            this.selectedPlants.splice(index, 1);
        }
    };
}
</script>
@endpush
@endsection
