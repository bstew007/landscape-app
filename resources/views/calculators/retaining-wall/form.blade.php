@extends('layouts.sidebar')

@php
    $lengthValue = old('length', $formData['length'] ?? null);
    $heightValue = old('height', $formData['height'] ?? null);
    $blockSystemValue = old('block_system', $formData['block_system'] ?? 'standard');
    $blockBrandValue = old('block_brand', $formData['block_brand'] ?? '');
    $equipmentValue = old('equipment', $formData['equipment'] ?? 'manual');
    $includeCaps = (bool) old('use_capstones', $formData['use_capstones'] ?? false);
    $includeGeogrid = (bool) old('include_geogrid', $formData['include_geogrid'] ?? false);
@endphp

@section('content')
@if ($errors->any())
    <div class="max-w-6xl mx-auto mb-6">
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Please fix the following errors:</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="max-w-6xl mx-auto px-4 py-8 space-y-8">
    {{-- Header --}}
    <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-2xl p-8">
        <div class="flex items-center gap-6">
            <div class="flex-shrink-0">
                <div class="bg-gradient-to-br from-gray-600 to-gray-800 p-4 rounded-xl shadow-lg">
                    <svg class="w-16 h-16 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z"/>
                        <path d="M7 7h10v2H7zm0 4h10v2H7zm0 4h10v2H7z"/>
                    </svg>
                </div>
            </div>
            <div class="flex-1">
                <h1 class="text-4xl font-extrabold text-white mb-2">Retaining Wall Calculator</h1>
                <p class="text-gray-300 text-lg">Calculate labor costs for retaining wall installations</p>
            </div>
        </div>
    </div>

    <form 
        action="{{ route('calculators.retaining-wall.calculate') }}" 
        method="POST"
        class="space-y-8"
    >
        @csrf
        <input type="hidden" name="site_visit_id" value="{{ $siteVisitId ?? '' }}">
        <input type="hidden" name="calculation_id" value="{{ $calculation->id ?? '' }}">
        <input type="hidden" name="estimate_id" value="{{ $estimateId ?? '' }}">
        <input type="hidden" name="mode" value="{{ $mode ?? '' }}">

        {{-- SECTION 1: Wall Dimensions --}}
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
            <div class="bg-gradient-to-r from-gray-700 to-gray-800 px-6 py-4 flex items-center gap-3">
                <div class="bg-white text-gray-800 w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm">
                    1
                </div>
                <h2 class="text-xl font-bold text-white">Wall Dimensions</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Length (feet)</label>
                        <input 
                            type="number" 
                            step="0.1" 
                            name="length" 
                            required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors" 
                            value="{{ $lengthValue }}"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Height (feet)</label>
                        <input 
                            type="number" 
                            step="0.1" 
                            name="height" 
                            required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors" 
                            value="{{ $heightValue }}"
                        >
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Block Brand</label>
                        <select 
                            name="block_brand" 
                            required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors"
                        >
                            <option value="">Select brand...</option>
                            <option value="belgard" {{ $blockBrandValue === 'belgard' ? 'selected' : '' }}>Belgard</option>
                            <option value="techo_bloc" {{ $blockBrandValue === 'techo_bloc' ? 'selected' : '' }}>Techo-Bloc</option>
                            <option value="versa_lok" {{ $blockBrandValue === 'versa_lok' ? 'selected' : '' }}>Versa-Lok</option>
                            <option value="allan_block" {{ $blockBrandValue === 'allan_block' ? 'selected' : '' }}>Allan Block</option>
                            <option value="keystone" {{ $blockBrandValue === 'keystone' ? 'selected' : '' }}>Keystone</option>
                            <option value="other" {{ $blockBrandValue === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Equipment</label>
                        <select 
                            name="equipment" 
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors"
                        >
                            <option value="manual" {{ $equipmentValue === 'manual' ? 'selected' : '' }}>Manual</option>
                            <option value="mini_skid" {{ $equipmentValue === 'mini_skid' ? 'selected' : '' }}>Mini Skid Steer</option>
                            <option value="skid_steer" {{ $equipmentValue === 'skid_steer' ? 'selected' : '' }}>Skid Steer</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 space-y-4">
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="use_capstones" 
                            name="use_capstones" 
                            value="1"
                            {{ $includeCaps ? 'checked' : '' }}
                            class="w-4 h-4 text-gray-600 border-gray-300 rounded focus:ring-gray-500"
                        >
                        <label for="use_capstones" class="ml-2 text-sm font-medium text-gray-700">
                            Include Capstones
                        </label>
                    </div>
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="include_geogrid" 
                            name="include_geogrid" 
                            value="1"
                            {{ $includeGeogrid ? 'checked' : '' }}
                            class="w-4 h-4 text-gray-600 border-gray-300 rounded focus:ring-gray-500"
                        >
                        <label for="include_geogrid" class="ml-2 text-sm font-medium text-gray-700">
                            Include Geogrid (for walls 4ft+ tall)
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 2: Block System --}}
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
            <div class="bg-gradient-to-r from-gray-700 to-gray-800 px-6 py-4 flex items-center gap-3">
                <div class="bg-white text-gray-800 w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm">
                    2
                </div>
                <h2 class="text-xl font-bold text-white">Block System</h2>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg hover:border-gray-500 cursor-pointer transition-colors">
                        <input 
                            type="radio" 
                            name="block_system" 
                            value="standard" 
                            {{ $blockSystemValue === 'standard' ? 'checked' : '' }}
                            class="w-4 h-4 text-gray-600 focus:ring-gray-500"
                        >
                        <div class="ml-3">
                            <span class="text-sm font-semibold text-gray-900">Standard Wall</span>
                            <p class="text-xs text-gray-500">Typical retaining wall installation</p>
                        </div>
                    </label>
                    <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg hover:border-gray-500 cursor-pointer transition-colors">
                        <input 
                            type="radio" 
                            name="block_system" 
                            value="allan_block" 
                            {{ $blockSystemValue === 'allan_block' ? 'checked' : '' }}
                            class="w-4 h-4 text-gray-600 focus:ring-gray-500"
                        >
                        <div class="ml-3">
                            <span class="text-sm font-semibold text-gray-900">Allan Block System</span>
                            <p class="text-xs text-gray-500">Complex curved/stepped walls, columns</p>
                        </div>
                    </label>
                </div>

                {{-- Allan Block Additional Fields --}}
                <div id="allanBlockFields" class="{{ $blockSystemValue === 'allan_block' ? '' : 'hidden' }} mt-6 p-6 bg-gray-50 rounded-lg border border-gray-200 space-y-6">
                    <h3 class="text-sm font-bold text-gray-700 mb-4">Allan Block Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Straight Wall Length (ft)</label>
                            <input 
                                type="number" 
                                step="0.1" 
                                name="ab_straight_length" 
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors" 
                                value="{{ old('ab_straight_length', $formData['ab_straight_length'] ?? '') }}"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Straight Wall Height (ft)</label>
                            <input 
                                type="number" 
                                step="0.1" 
                                name="ab_straight_height" 
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors" 
                                value="{{ old('ab_straight_height', $formData['ab_straight_height'] ?? '') }}"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Curved Wall Length (ft)</label>
                            <input 
                                type="number" 
                                step="0.1" 
                                name="ab_curved_length" 
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors" 
                                value="{{ old('ab_curved_length', $formData['ab_curved_length'] ?? '') }}"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Curved Wall Height (ft)</label>
                            <input 
                                type="number" 
                                step="0.1" 
                                name="ab_curved_height" 
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors" 
                                value="{{ old('ab_curved_height', $formData['ab_curved_height'] ?? '') }}"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Number of Steps</label>
                            <input 
                                type="number" 
                                step="1" 
                                name="ab_step_count" 
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors" 
                                value="{{ old('ab_step_count', $formData['ab_step_count'] ?? '') }}"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Number of Columns</label>
                            <input 
                                type="number" 
                                step="1" 
                                name="ab_column_count" 
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors" 
                                value="{{ old('ab_column_count', $formData['ab_column_count'] ?? '') }}"
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 3: Labor Inputs --}}
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
            <div class="bg-gradient-to-r from-gray-700 to-gray-800 px-6 py-4 flex items-center gap-3">
                <div class="bg-white text-gray-800 w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm">
                    3
                </div>
                <h2 class="text-xl font-bold text-white">Labor & Overhead</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Labor Rate ($/hour)</label>
                        <input 
                            type="number" 
                            step="0.01" 
                            name="labor_rate" 
                            required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors" 
                            value="{{ old('labor_rate', $formData['labor_rate'] ?? $defaultLaborRate ?? 85) }}"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Crew Size</label>
                        <input 
                            type="number" 
                            step="1" 
                            name="crew_size" 
                            required
                            min="1"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors" 
                            value="{{ old('crew_size', $formData['crew_size'] ?? 2) }}"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Drive Distance (miles)</label>
                        <input 
                            type="number" 
                            step="0.1" 
                            name="drive_distance" 
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors" 
                            value="{{ old('drive_distance', $formData['drive_distance'] ?? 0) }}"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Average Speed (mph)</label>
                        <input 
                            type="number" 
                            step="1" 
                            name="drive_speed" 
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors" 
                            value="{{ old('drive_speed', $formData['drive_speed'] ?? 35) }}"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Site Conditions (hours)</label>
                        <input 
                            type="number" 
                            step="0.1" 
                            name="site_conditions" 
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors" 
                            value="{{ old('site_conditions', $formData['site_conditions'] ?? 0) }}"
                        >
                        <p class="text-xs text-gray-500 mt-1">Extra time for difficult access, slopes, etc.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Cleanup Time (hours)</label>
                        <input 
                            type="number" 
                            step="0.1" 
                            name="cleanup" 
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors" 
                            value="{{ old('cleanup', $formData['cleanup'] ?? 0) }}"
                        >
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 4: Notes --}}
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
            <div class="bg-gradient-to-r from-gray-700 to-gray-800 px-6 py-4 flex items-center gap-3">
                <div class="bg-white text-gray-800 w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm">
                    4
                </div>
                <h2 class="text-xl font-bold text-white">Job Notes</h2>
            </div>
            <div class="p-6">
                <textarea 
                    name="job_notes" 
                    rows="4" 
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors" 
                    placeholder="Add any special notes or considerations..."
                >{{ old('job_notes', $formData['job_notes'] ?? '') }}</textarea>
            </div>
        </div>

        @if(($mode ?? null) === 'template')
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Template Name</label>
            <input 
                type="text" 
                name="template_name" 
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" 
                value="{{ old('template_name', $formData['template_name'] ?? '') }}"
                placeholder="e.g., Standard 4ft Belgard Wall"
            >
            <p class="text-xs text-gray-600 mt-2">Give this template a descriptive name for easy identification</p>
        </div>
        @endif

        {{-- Submit Buttons --}}
        <div class="flex items-center justify-between gap-4 pt-6 border-t border-gray-200">
            <a 
                href="{{ ($mode ?? null) === 'template' && !empty($estimateId) ? route('estimates.show', $estimateId) : route('site-visits.show', $siteVisitId) }}" 
                class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors font-medium"
            >
                Cancel
            </a>
            <button 
                type="submit" 
                class="px-8 py-3 bg-gradient-to-r from-gray-700 to-gray-800 hover:from-gray-800 hover:to-gray-900 text-white rounded-lg shadow-lg transition-all transform hover:scale-105 font-bold text-lg"
            >
                {{ $editMode ?? false ? 'Update Calculation' : 'Calculate Wall Estimate' }}
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Block System toggle for Allan Block fields
    const blockSystemRadios = document.querySelectorAll('input[name="block_system"]');
    const allanBlockFields = document.getElementById('allanBlockFields');
    
    blockSystemRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'allan_block') {
                allanBlockFields.classList.remove('hidden');
            } else {
                allanBlockFields.classList.add('hidden');
            }
        });
    });
});
</script>

@endsection
