@extends('layouts.sidebar')

@php
    $fenceTypeValue = old('fence_type', $formData['fence_type'] ?? '');
    $heightValue = old('height', $formData['height'] ?? '');
    
    // Get materials from catalog if available
    $storedMaterials = $formData['materials'] ?? [];
    $selectedMaterials = collect($storedMaterials);
@endphp

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Modern Header with Icon --}}
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-3">
            <div class="flex-shrink-0">
                <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-gray-700 to-gray-900 flex items-center justify-center shadow-lg">
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <circle cx="12" cy="6" r="1.5" fill="currentColor"/>
                        <circle cx="12" cy="12" r="1.5" fill="currentColor"/>
                        <circle cx="12" cy="18" r="1.5" fill="currentColor"/>
                    </svg>
                </div>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    {{ $editMode ? 'Edit Fence Estimate' : 'Fence Calculator' }}
                </h1>
                <p class="text-gray-600 mt-1">Wood or vinyl fence installation estimator</p>
            </div>
        </div>
    </div>

    @if(($mode ?? null) === 'template')
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-blue-900">Template Mode Active</p>
                    <p class="text-sm text-blue-700 mt-1">Building fence template without site visit</p>
                    @if(!empty($estimateId))
                        <p class="text-sm text-blue-600 mt-1">Target Estimate: #{{ $estimateId }}</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('calculators.fence.calculate') }}">
        @csrf
        <input type="hidden" name="mode" value="{{ $mode ?? '' }}">
        @if(!empty($estimateId))
            <input type="hidden" name="estimate_id" value="{{ $estimateId }}">
        @endif

        {{-- When editing, include hidden calculation ID --}}
        @if ($editMode && isset($existingCalculation))
            <input type="hidden" name="calculation_id" value="{{ $existingCalculation->id }}">
        @endif

        {{-- Site Visit --}}
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
                    <p class="text-sm text-gray-600">Labor rates, crew size, and travel details</p>
                </div>
            </div>
            @include('calculators.partials.overhead_inputs')
        </div>

        {{-- 2️⃣ Fence Configuration --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-gradient-to-br from-gray-800 to-gray-700 flex items-center justify-center">
                    <span class="text-white font-bold text-sm">2</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Fence Configuration</h2>
                    <p class="text-sm text-gray-600">Type, dimensions, and installation method</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Fence Type --}}
                <div>
                    <label for="fence_type" class="block text-sm font-semibold text-gray-700 mb-2">Fence Type</label>
                    <select name="fence_type" id="fence_type" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                        <option value="">-- Select Type --</option>
                        <option value="wood" {{ old('fence_type', $formData['fence_type'] ?? '') == 'wood' ? 'selected' : '' }}>Wood</option>
                        <option value="vinyl" {{ old('fence_type', $formData['fence_type'] ?? '') == 'vinyl' ? 'selected' : '' }}>Vinyl</option>
                    </select>
                </div>

                {{-- Fence Height --}}
                <div>
                    <label for="height" class="block text-sm font-semibold text-gray-700 mb-2">Fence Height</label>
                    <select name="height" id="height" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                        <option value="4" {{ old('height', $formData['height'] ?? '') == '4' ? 'selected' : '' }}>4 feet</option>
                        <option value="6" {{ old('height', $formData['height'] ?? '') == '6' ? 'selected' : '' }}>6 feet</option>
                    </select>
                </div>

                {{-- Fence Length --}}
                <div>
                    <label for="length" class="block text-sm font-semibold text-gray-700 mb-2">Total Fence Length (ft)</label>
                    <input type="number" name="length" id="length" value="{{ old('length', $formData['length'] ?? '') }}" required 
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                </div>

                {{-- Dig Method --}}
                <div>
                    <label for="dig_method" class="block text-sm font-semibold text-gray-700 mb-2">Post Digging Method</label>
                    <select name="dig_method" id="dig_method" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                        <option value="">-- Select Method --</option>
                        <option value="hand" {{ old('dig_method', $formData['dig_method'] ?? '') == 'hand' ? 'selected' : '' }}>Hand Dig</option>
                        <option value="auger" {{ old('dig_method', $formData['dig_method'] ?? '') == 'auger' ? 'selected' : '' }}>Auger</option>
                    </select>
                </div>

                {{-- 4' Gates --}}
                <div>
                    <label for="gate_4ft" class="block text-sm font-semibold text-gray-700 mb-2">Number of 4' Gates</label>
                    <input type="number" name="gate_4ft" id="gate_4ft" value="{{ old('gate_4ft', $formData['gate_4ft'] ?? 0) }}" min="0" 
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                </div>

                {{-- 5' Gates --}}
                <div>
                    <label for="gate_5ft" class="block text-sm font-semibold text-gray-700 mb-2">Number of 5' Gates</label>
                    <input type="number" name="gate_5ft" id="gate_5ft" value="{{ old('gate_5ft', $formData['gate_5ft'] ?? 0) }}" min="0" 
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                </div>
            </div>

            {{-- Wood Specific Options --}}
            <div id="wood-options" style="display: {{ $fenceTypeValue === 'wood' ? 'block' : 'none' }};" class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Wood Fence Options</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="picket_spacing" class="block text-sm font-semibold text-gray-700 mb-2">Picket Spacing (inches)</label>
                        <input type="number" step="0.01" name="picket_spacing" id="picket_spacing" value="{{ old('picket_spacing', $formData['picket_spacing'] ?? 0.25) }}" 
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                    </div>
                    <div class="flex items-center">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="shadow_box" value="1"
                                   {{ old('shadow_box', $formData['shadow_box'] ?? false) ? 'checked' : '' }} 
                                   class="w-4 h-4 text-brand-600 border-gray-300 rounded focus:ring-brand-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">Shadow Box Style (Double Pickets)</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Vinyl Specific Options --}}
            <div id="vinyl-options" style="display: {{ $fenceTypeValue === 'vinyl' ? 'block' : 'none' }};" class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Vinyl Fence Options</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="vinyl_corner_posts" class="block text-sm font-semibold text-gray-700 mb-2">Vinyl Corner Posts</label>
                        <input type="number" name="vinyl_corner_posts" id="vinyl_corner_posts" value="{{ old('vinyl_corner_posts', $formData['vinyl_corner_posts'] ?? 0) }}" 
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="vinyl_end_posts" class="block text-sm font-semibold text-gray-700 mb-2">Vinyl End Posts</label>
                        <input type="number" name="vinyl_end_posts" id="vinyl_end_posts" value="{{ old('vinyl_end_posts', $formData['vinyl_end_posts'] ?? 0) }}" 
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                    </div>
                </div>
            </div>
        </div>

        {{-- 3️⃣ Materials from Catalog --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-gradient-to-br from-gray-800 to-gray-700 flex items-center justify-center">
                    <span class="text-white font-bold text-sm">3</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Materials</h2>
                    <p class="text-sm text-gray-600">Select materials from catalog</p>
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
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 border-2 border-gray-500 rounded-xl p-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <p class="text-lg font-bold text-gray-900" x-text="material.name"></p>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-700 ml-7">
                                        $<span x-text="parseFloat(material.unit_cost).toFixed(2)"></span> per <span x-text="material.unit || 'ea'"></span>
                                    </p>
                                    <p class="text-xs text-gray-600 mt-1 ml-7" x-show="material.description" x-text="material.description"></p>
                                    
                                    {{-- Quantity Input --}}
                                    <div class="mt-3 ml-7">
                                        <label class="block text-sm font-semibold text-gray-900 mb-1">Quantity:</label>
                                        <input type="number" 
                                               :name="'materials[' + index + '][quantity]'"
                                               x-model="material.quantity"
                                               min="0"
                                               step="any"
                                               class="w-32 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
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
                                        class="ml-4 p-3 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
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

        {{-- 4️⃣ Job Notes --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-gradient-to-br from-gray-800 to-gray-700 flex items-center justify-center">
                    <span class="text-white font-bold text-sm">4</span>
                </div>
                <div class="flex-1">
                    <h2 class="text-xl font-bold text-gray-900">Job Notes</h2>
                    <p class="text-sm text-gray-600">Special considerations, access notes, or custom requirements</p>
                </div>
            </div>

            <div>
                <textarea name="job_notes" id="job_notes" rows="3" 
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent" 
                          placeholder="Special considerations, access notes, or custom requirements...">{{ old('job_notes', $formData['job_notes'] ?? '') }}</textarea>
            </div>
        </div>

        {{-- Submit Button --}}
        <div class="flex flex-col sm:flex-row gap-4">
            @if(($mode ?? null) === 'template')
                <input type="text" name="template_name" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent" 
                       placeholder="Template name (e.g., 6' vinyl with 2 gates)" value="{{ old('template_name') }}">
                <select name="template_scope" class="px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                    <option value="global" {{ old('template_scope')==='global' ? 'selected' : '' }}>Global</option>
                    <option value="client" {{ old('template_scope')==='client' ? 'selected' : '' }}>This Client</option>
                    <option value="property" {{ old('template_scope')==='property' ? 'selected' : '' }}>This Property</option>
                </select>
                <button type="submit" class="px-6 py-2.5 bg-brand-800 hover:bg-brand-700 text-white font-medium rounded-lg transition-colors shadow-sm">
                    💾 Save Template
                </button>
            @else
                <button type="submit" class="px-8 py-3 bg-brand-800 hover:bg-brand-700 text-white font-medium rounded-lg transition-colors shadow-sm">
                    {{ $editMode ? '🔄 Recalculate Estimate' : '➡️ Calculate Fence Estimate' }}
                </button>
                <a href="{{ route('clients.show', $clientId ?? $siteVisitId) }}" class="px-8 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors text-center">
                    ← Back to Client
                </a>
            @endif
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fenceTypeSelect = document.getElementById('fence_type');
        
        function toggleSections() {
            const type = fenceTypeSelect.value;
            document.getElementById('wood-options').style.display = type === 'wood' ? 'block' : 'none';
            document.getElementById('vinyl-options').style.display = type === 'vinyl' ? 'block' : 'none';
        }

        if (fenceTypeSelect) {
            fenceTypeSelect.addEventListener('change', toggleSections);
            toggleSections(); // Initialize on load
        }
    });
</script>
@endpush
