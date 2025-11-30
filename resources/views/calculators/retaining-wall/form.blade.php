@extends('layouts.sidebar')

@php
    $hasOverrides = collect(old())->keys()->filter(fn($key) => str_starts_with($key, 'override_'))->isNotEmpty();
    $overrideChecked = old('materials_override_enabled', $formData['materials_override_enabled'] ?? $hasOverrides);

    $lengthValue = old('length', $formData['length'] ?? null);
    $heightValue = old('height', $formData['height'] ?? null);
    $blockSystemValue = old('block_system', $formData['block_system'] ?? 'standard');
    $blockBrandValue = old('block_brand', $formData['block_brand'] ?? '');
    $equipmentValue = old('equipment', $formData['equipment'] ?? 'manual');
    $includeCaps = (bool) old('use_capstones', $formData['use_capstones'] ?? false);
    $includeGeogrid = (bool) old('include_geogrid', $formData['include_geogrid'] ?? false);

    $resolveCost = function (string $field, float $default) use ($formData) {
        $value = old($field, $formData[$field] ?? null);
        return $value === null || $value === '' ? $default : (float) $value;
    };

    $blockUnitCostDefault = $resolveCost('override_block_cost', 11.00);
    $capUnitCostDefault = $resolveCost('override_capstone_cost', 18.00);
    $pipeUnitCostDefault = $resolveCost('override_pipe_cost', 2.00);
    $gravelUnitCostDefault = $resolveCost('override_gravel_cost', 85.00);
    $topsoilUnitCostDefault = $resolveCost('override_topsoil_cost', 17.00);
    $fabricUnitCostDefault = $resolveCost('override_fabric_cost', 0.30);
    $geogridUnitCostDefault = $resolveCost('override_geogrid_cost', 1.50);
    $adhesiveUnitCostDefault = $resolveCost('override_adhesive_cost', 8.00);

    $areaSqft = ($lengthValue && $heightValue) ? round($lengthValue * $heightValue, 2) : null;
    $blockCoverage = $blockBrandValue === 'belgard' ? 0.67 : 0.65;
    $blockCountEstimate = $areaSqft ? (int) ceil($areaSqft / $blockCoverage) : null;

    $capCountEstimate = $includeCaps && $lengthValue ? (int) ceil($lengthValue) : 0;
    $adhesiveTubeEstimate = $capCountEstimate > 0 ? (int) ceil($capCountEstimate / 20) : 0;

    $gravelVolumeCF = ($lengthValue && $heightValue)
        ? $lengthValue * max($heightValue - 0.5, 0) * 1.5
        : null;
    $gravelTonsEstimate = $gravelVolumeCF ? ceil($gravelVolumeCF / 21.6) : null;

    $topsoilVolumeCF = $lengthValue ? ($lengthValue * 0.5 * 1.5) : null;
    $topsoilYardsEstimate = $topsoilVolumeCF ? ceil($topsoilVolumeCF / 27) : null;

    $fabricAreaEstimate = ($lengthValue && $heightValue) ? round($lengthValue * $heightValue * 2, 2) : null;
    $geogridLayersEstimate = ($includeGeogrid && $heightValue && $heightValue >= 4)
        ? (int) floor($heightValue / 2)
        : 0;
    $geogridLfEstimate = ($lengthValue && $geogridLayersEstimate)
        ? $lengthValue * $geogridLayersEstimate
        : 0;
    $geogridAreaEstimate = $geogridLfEstimate && $heightValue ? round($geogridLfEstimate * $heightValue, 2) : 0;

    $customMaterials = old('custom_materials', $formData['custom_materials'] ?? []);
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
    {{-- Header with Wall Icon --}}
    <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-2xl p-8">
        <div class="flex items-center gap-6">
            {{-- Gray Gradient Brick Wall Icon (16x16 blocks) --}}
            <div class="flex-shrink-0">
                <div class="bg-gradient-to-br from-gray-600 to-gray-800 p-4 rounded-xl shadow-lg">
                    <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 64 64">
                        <g stroke-width="1.5">
                            {{-- Row 1 --}}
                            <rect x="2" y="2" width="12" height="6" fill="currentColor" opacity="0.9"/>
                            <rect x="16" y="2" width="12" height="6" fill="currentColor" opacity="0.8"/>
                            <rect x="30" y="2" width="12" height="6" fill="currentColor" opacity="0.9"/>
                            <rect x="44" y="2" width="12" height="6" fill="currentColor" opacity="0.8"/>
                            {{-- Row 2 (offset) --}}
                            <rect x="8" y="10" width="12" height="6" fill="currentColor" opacity="0.85"/>
                            <rect x="22" y="10" width="12" height="6" fill="currentColor" opacity="0.9"/>
                            <rect x="36" y="10" width="12" height="6" fill="currentColor" opacity="0.85"/>
                            <rect x="50" y="10" width="12" height="6" fill="currentColor" opacity="0.9"/>
                            {{-- Row 3 --}}
                            <rect x="2" y="18" width="12" height="6" fill="currentColor" opacity="0.8"/>
                            <rect x="16" y="18" width="12" height="6" fill="currentColor" opacity="0.9"/>
                            <rect x="30" y="18" width="12" height="6" fill="currentColor" opacity="0.8"/>
                            <rect x="44" y="18" width="12" height="6" fill="currentColor" opacity="0.9"/>
                            {{-- Row 4 (offset) --}}
                            <rect x="8" y="26" width="12" height="6" fill="currentColor" opacity="0.9"/>
                            <rect x="22" y="26" width="12" height="6" fill="currentColor" opacity="0.85"/>
                            <rect x="36" y="26" width="12" height="6" fill="currentColor" opacity="0.9"/>
                            <rect x="50" y="26" width="12" height="6" fill="currentColor" opacity="0.85"/>
                            {{-- Row 5 --}}
                            <rect x="2" y="34" width="12" height="6" fill="currentColor" opacity="0.9"/>
                            <rect x="16" y="34" width="12" height="6" fill="currentColor" opacity="0.8"/>
                            <rect x="30" y="34" width="12" height="6" fill="currentColor" opacity="0.9"/>
                            <rect x="44" y="34" width="12" height="6" fill="currentColor" opacity="0.8"/>
                            {{-- Row 6 (offset) --}}
                            <rect x="8" y="42" width="12" height="6" fill="currentColor" opacity="0.85"/>
                            <rect x="22" y="42" width="12" height="6" fill="currentColor" opacity="0.9"/>
                            <rect x="36" y="42" width="12" height="6" fill="currentColor" opacity="0.85"/>
                            <rect x="50" y="42" width="12" height="6" fill="currentColor" opacity="0.9"/>
                            {{-- Row 7 --}}
                            <rect x="2" y="50" width="12" height="6" fill="currentColor" opacity="0.8"/>
                            <rect x="16" y="50" width="12" height="6" fill="currentColor" opacity="0.9"/>
                            <rect x="30" y="50" width="12" height="6" fill="currentColor" opacity="0.8"/>
                            <rect x="44" y="50" width="12" height="6" fill="currentColor" opacity="0.9"/>
                            {{-- Row 8 (offset) --}}
                            <rect x="8" y="58" width="12" height="4" fill="currentColor" opacity="0.9"/>
                            <rect x="22" y="58" width="12" height="4" fill="currentColor" opacity="0.85"/>
                            <rect x="36" y="58" width="12" height="4" fill="currentColor" opacity="0.9"/>
                            <rect x="50" y="58" width="12" height="4" fill="currentColor" opacity="0.85"/>
                        </g>
                    </svg>
                </div>
            </div>
            <div class="flex-1">
                <h1 class="text-4xl font-bold text-white mb-2">
                    {{ $editMode ? 'Edit Retaining Wall Calculation' : 'Retaining Wall Calculator' }}
                </h1>
                <p class="text-gray-300 text-lg">
                    Configure wall dimensions, select block system (Allan Block or Standard), preview materials, and calculate labor costs.
                </p>
            </div>
        </div>
        
        @if(($mode ?? null) === 'template')
            <div class="mt-6 bg-blue-500/20 border border-blue-400/30 rounded-lg p-4">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-blue-300" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="text-blue-200 font-medium">Template Mode Active</p>
                        <p class="text-blue-300 text-sm">Building a Retaining Wall template without a site visit
                            @if(!empty($estimateId)) - Target Estimate: #{{ $estimateId }}@endif
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if(($mode ?? null) !== 'template' && $siteVisit)
        @include('calculators.partials.client_info', ['siteVisit' => $siteVisit])
    @endif

    <form method="POST" action="{{ route('calculators.wall.calculate') }}" class="space-y-8">
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

        {{-- SECTION 1: Crew & Logistics --}}
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
            <div class="bg-gradient-to-r from-gray-700 to-gray-800 px-6 py-4 flex items-center gap-3">
                <div class="bg-white text-gray-800 w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm">
                    1
                </div>
                <h2 class="text-xl font-bold text-white">Crew & Logistics</h2>
            </div>
            <div class="p-6">
                @include('calculators.partials.overhead_inputs')
            </div>
        </div>

        {{-- SECTION 2: Wall Configuration --}}
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
            <div class="bg-gradient-to-r from-gray-700 to-gray-800 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="bg-white text-gray-800 w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm">
                            2
                        </div>
                        <h2 class="text-xl font-bold text-white">Wall Configuration</h2>
                    </div>
                    <div id="wallAreaBadge" class="px-4 py-1.5 rounded-full text-sm font-semibold {{ $areaSqft ? 'bg-gray-100 text-gray-800' : 'bg-gray-600/50 text-gray-300' }}">
                        {{ $areaSqft ? 'Wall Area: ' . number_format($areaSqft, 2) . ' sqft' : 'Enter dimensions to calculate area' }}
                    </div>
                </div>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Wall Length (ft) <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            step="0.1" 
                            name="length" 
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors" 
                            value="{{ $lengthValue }}" 
                            required
                        >
                        <p class="text-xs text-gray-500 mt-1.5">Base for material calculations</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Wall Height (ft) <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            step="0.1" 
                            name="height" 
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors" 
                            value="{{ $heightValue }}" 
                            required
                        >
                        <p class="text-xs text-gray-500 mt-1.5">Impacts blocks, backfill, geogrid</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Equipment <span class="text-red-500">*</span>
                        </label>
                        <select 
                            name="equipment" 
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors" 
                            required
                        >
                            <option value="manual" {{ $equipmentValue === 'manual' ? 'selected' : '' }}>Manual Labor</option>
                            <option value="skid_steer" {{ $equipmentValue === 'skid_steer' ? 'selected' : '' }}>Skid Steer</option>
                            <option value="excavator" {{ $equipmentValue === 'excavator' ? 'selected' : '' }}>Excavator</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1.5">Affects labor production rates</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Block System <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="relative flex items-center justify-center px-4 py-3 border-2 rounded-lg cursor-pointer transition-all {{ $blockSystemValue === 'standard' ? 'border-gray-600 bg-gray-50' : 'border-gray-300 bg-white hover:border-gray-400' }}">
                                <input 
                                    type="radio" 
                                    name="block_system" 
                                    value="standard" 
                                    class="sr-only" 
                                    {{ $blockSystemValue === 'standard' ? 'checked' : '' }}
                                    required
                                >
                                <div class="text-center">
                                    <div class="font-semibold text-gray-900">Standard</div>
                                    <div class="text-xs text-gray-500">Generic blocks</div>
                                </div>
                            </label>
                            <label class="relative flex items-center justify-center px-4 py-3 border-2 rounded-lg cursor-pointer transition-all {{ $blockSystemValue === 'allan_block' ? 'border-gray-600 bg-gray-50' : 'border-gray-300 bg-white hover:border-gray-400' }}">
                                <input 
                                    type="radio" 
                                    name="block_system" 
                                    value="allan_block" 
                                    class="sr-only" 
                                    {{ $blockSystemValue === 'allan_block' ? 'checked' : '' }}
                                    required
                                >
                                <div class="text-center">
                                    <div class="font-semibold text-gray-900">Allan Block</div>
                                    <div class="text-xs text-gray-500">AB System</div>
                                </div>
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 mt-1.5">Allan Block enables additional component options</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Block Brand <span class="text-red-500">*</span>
                        </label>
                        <select 
                            name="block_brand" 
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors" 
                            required
                        >
                            <option value="">Choose brand...</option>
                            <option value="belgard" {{ $blockBrandValue === 'belgard' ? 'selected' : '' }}>Belgard (0.67 sqft/block)</option>
                            <option value="techo" {{ $blockBrandValue === 'techo' ? 'selected' : '' }}>Techo-Bloc (0.65 sqft/block)</option>
                            <option value="allan_block" {{ $blockBrandValue === 'allan_block' ? 'selected' : '' }}>Allan Block</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1.5">Brand affects coverage calculations</p>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Optional Features</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative">
                                <input 
                                    type="checkbox" 
                                    name="use_capstones" 
                                    value="1" 
                                    class="w-5 h-5 text-gray-600 border-gray-300 rounded focus:ring-2 focus:ring-gray-500 transition-colors" 
                                    {{ $includeCaps ? 'checked' : '' }}
                                >
                            </div>
                            <div>
                                <div class="font-medium text-gray-900 group-hover:text-gray-700">Include Capstones</div>
                                <div class="text-xs text-gray-500">Finishing cap for wall top</div>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative">
                                <input 
                                    type="checkbox" 
                                    name="include_geogrid" 
                                    value="1" 
                                    class="w-5 h-5 text-gray-600 border-gray-300 rounded focus:ring-2 focus:ring-gray-500 transition-colors" 
                                    {{ $includeGeogrid ? 'checked' : '' }}
                                >
                            </div>
                            <div>
                                <div class="font-medium text-gray-900 group-hover:text-gray-700">Include Geogrid</div>
                                <div class="text-xs text-gray-500">Auto-calculated when height ≥ 4 ft</div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Allan Block Additional Fields (conditionally shown) --}}
                <div id="allanBlockFields" class="{{ $blockSystemValue === 'allan_block' ? '' : 'hidden' }} bg-blue-50 border-2 border-blue-200 rounded-lg p-6 space-y-4">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-blue-900">Allan Block Components</h3>
                        <span class="ml-auto text-sm text-blue-600">Optional for enhanced accuracy</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Straight Wall Length (ft)</label>
                            <input 
                                type="number" 
                                step="0.1" 
                                name="ab_straight_length" 
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" 
                                value="{{ old('ab_straight_length', $formData['ab_straight_length'] ?? '') }}"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Straight Wall Height (ft)</label>
                            <input 
                                type="number" 
                                step="0.1" 
                                name="ab_straight_height" 
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" 
                                value="{{ old('ab_straight_height', $formData['ab_straight_height'] ?? '') }}"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Curved Wall Length (ft)</label>
                            <input 
                                type="number" 
                                step="0.1" 
                                name="ab_curved_length" 
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" 
                                value="{{ old('ab_curved_length', $formData['ab_curved_length'] ?? '') }}"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Curved Wall Height (ft)</label>
                            <input 
                                type="number" 
                                step="0.1" 
                                name="ab_curved_height" 
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" 
                                value="{{ old('ab_curved_height', $formData['ab_curved_height'] ?? '') }}"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Number of Steps</label>
                            <input 
                                type="number" 
                                step="1" 
                                name="ab_step_count" 
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" 
                                value="{{ old('ab_step_count', $formData['ab_step_count'] ?? '') }}"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Number of Columns</label>
                            <input 
                                type="number" 
                                step="1" 
                                name="ab_column_count" 
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" 
                                value="{{ old('ab_column_count', $formData['ab_column_count'] ?? '') }}"
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 3: Materials Preview --}}
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
            <div class="bg-gradient-to-r from-gray-700 to-gray-800 px-6 py-4 flex items-center gap-3">
                <div class="bg-white text-gray-800 w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm">
                    3
                </div>
                <h2 class="text-xl font-bold text-white">Materials Preview</h2>
                <span class="ml-auto text-sm text-gray-300">Auto-calculated • Update costs as needed</span>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    {{-- Wall Blocks --}}
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 border-2 border-gray-300 rounded-xl p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-gray-600 to-gray-800 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5h16M4 12h16M4 19h16"/>
                                </svg>
                            </div>
                            <div>
                                <div class="font-bold text-gray-900">Wall Blocks</div>
                                <div class="text-xs text-gray-500">Coverage ~{{ number_format($blockCoverage, 2) }} sqft/block</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Quantity:</span>
                                <span class="font-bold text-gray-900">{{ $blockCountEstimate ? number_format($blockCountEstimate) : '—' }} blocks</span>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Unit Cost ($)</label>
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    name="override_block_cost" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-500 focus:border-gray-500" 
                                    value="{{ $blockUnitCostDefault }}"
                                    placeholder="11.00"
                                >
                            </div>
                            @if($blockCountEstimate)
                                <div class="pt-2 border-t border-gray-300">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-600">Total:</span>
                                        <span class="font-bold text-gray-900">${{ number_format($blockCountEstimate * $blockUnitCostDefault, 2) }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Capstones --}}
                    @if($includeCaps)
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 border-2 border-gray-300 rounded-xl p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-gray-500 to-gray-700 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"/>
                                </svg>
                            </div>
                            <div>
                                <div class="font-bold text-gray-900">Capstones</div>
                                <div class="text-xs text-gray-500">Mirrors wall length</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Quantity:</span>
                                <span class="font-bold text-gray-900">{{ $capCountEstimate ? number_format($capCountEstimate) : '—' }} caps</span>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Unit Cost ($)</label>
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    name="override_capstone_cost" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-500 focus:border-gray-500" 
                                    value="{{ $capUnitCostDefault }}"
                                    placeholder="18.00"
                                >
                            </div>
                            @if($capCountEstimate)
                                <div class="pt-2 border-t border-gray-300">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-600">Total:</span>
                                        <span class="font-bold text-gray-900">${{ number_format($capCountEstimate * $capUnitCostDefault, 2) }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Drain Pipe --}}
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 border-2 border-gray-300 rounded-xl p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-blue-800 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8M8 12h8m-8 5h8"/>
                                </svg>
                            </div>
                            <div>
                                <div class="font-bold text-gray-900">Drain Pipe</div>
                                <div class="text-xs text-gray-500">Full wall length</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Quantity:</span>
                                <span class="font-bold text-gray-900">{{ $lengthValue ? number_format($lengthValue, 1) : '—' }} lf</span>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Unit Cost ($/ft)</label>
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    name="override_pipe_cost" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-500 focus:border-gray-500" 
                                    value="{{ $pipeUnitCostDefault }}"
                                    placeholder="2.00"
                                >
                            </div>
                            @if($lengthValue)
                                <div class="pt-2 border-t border-gray-300">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-600">Total:</span>
                                        <span class="font-bold text-gray-900">${{ number_format($lengthValue * $pipeUnitCostDefault, 2) }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- #57 Gravel --}}
                    @if($gravelTonsEstimate)
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 border-2 border-gray-300 rounded-xl p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-stone-600 to-stone-800 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <circle cx="12" cy="8" r="1.5"/><circle cx="8" cy="12" r="1.5"/><circle cx="16" cy="12" r="1.5"/><circle cx="12" cy="16" r="1.5"/>
                                </svg>
                            </div>
                            <div>
                                <div class="font-bold text-gray-900">#57 Gravel</div>
                                <div class="text-xs text-gray-500">1.5ft backfill depth</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Quantity:</span>
                                <span class="font-bold text-gray-900">{{ number_format($gravelTonsEstimate) }} tons</span>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Unit Cost ($/ton)</label>
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    name="override_gravel_cost" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-500 focus:border-gray-500" 
                                    value="{{ $gravelUnitCostDefault }}"
                                    placeholder="85.00"
                                >
                            </div>
                            <div class="pt-2 border-t border-gray-300">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-600">Total:</span>
                                    <span class="font-bold text-gray-900">${{ number_format($gravelTonsEstimate * $gravelUnitCostDefault, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Topsoil --}}
                    @if($topsoilYardsEstimate)
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 border-2 border-gray-300 rounded-xl p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-amber-700 to-amber-900 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2L2 12h3v8h14v-8h3L12 2z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="font-bold text-gray-900">Topsoil</div>
                                <div class="text-xs text-gray-500">0.5ft cap layer</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Quantity:</span>
                                <span class="font-bold text-gray-900">{{ number_format($topsoilYardsEstimate) }} cu yd</span>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Unit Cost ($/yd³)</label>
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    name="override_topsoil_cost" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-500 focus:border-gray-500" 
                                    value="{{ $topsoilUnitCostDefault }}"
                                    placeholder="17.00"
                                >
                            </div>
                            <div class="pt-2 border-t border-gray-300">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-600">Total:</span>
                                    <span class="font-bold text-gray-900">${{ number_format($topsoilYardsEstimate * $topsoilUnitCostDefault, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Underlayment Fabric --}}
                    @if($fabricAreaEstimate)
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 border-2 border-gray-300 rounded-xl p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-gray-400 to-gray-600 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                            </div>
                            <div>
                                <div class="font-bold text-gray-900">Fabric</div>
                                <div class="text-xs text-gray-500">Both wall faces</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Quantity:</span>
                                <span class="font-bold text-gray-900">{{ number_format($fabricAreaEstimate, 1) }} sqft</span>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Unit Cost ($/sqft)</label>
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    name="override_fabric_cost" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-500 focus:border-gray-500" 
                                    value="{{ $fabricUnitCostDefault }}"
                                    placeholder="0.30"
                                >
                            </div>
                            <div class="pt-2 border-t border-gray-300">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-600">Total:</span>
                                    <span class="font-bold text-gray-900">${{ number_format($fabricAreaEstimate * $fabricUnitCostDefault, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Geogrid --}}
                    @if($includeGeogrid && $geogridAreaEstimate)
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 border-2 border-gray-300 rounded-xl p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-purple-600 to-purple-800 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14"/>
                                </svg>
                            </div>
                            <div>
                                <div class="font-bold text-gray-900">Geogrid</div>
                                <div class="text-xs text-gray-500">{{ $geogridLayersEstimate }} layers</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Quantity:</span>
                                <span class="font-bold text-gray-900">{{ number_format($geogridAreaEstimate, 1) }} sqft</span>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Unit Cost ($/sqft)</label>
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    name="override_geogrid_cost" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-500 focus:border-gray-500" 
                                    value="{{ $geogridUnitCostDefault }}"
                                    placeholder="1.50"
                                >
                            </div>
                            <div class="pt-2 border-t border-gray-300">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-600">Total:</span>
                                    <span class="font-bold text-gray-900">${{ number_format($geogridAreaEstimate * $geogridUnitCostDefault, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Adhesive for Capstones --}}
                    @if($includeCaps && $adhesiveTubeEstimate)
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 border-2 border-gray-300 rounded-xl p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-red-600 to-red-800 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="font-bold text-gray-900">Adhesive</div>
                                <div class="text-xs text-gray-500">1 tube per 20 caps</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Quantity:</span>
                                <span class="font-bold text-gray-900">{{ number_format($adhesiveTubeEstimate) }} tubes</span>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Unit Cost ($/tube)</label>
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    name="override_adhesive_cost" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-500 focus:border-gray-500" 
                                    value="{{ $adhesiveUnitCostDefault }}"
                                    placeholder="8.00"
                                >
                            </div>
                            <div class="pt-2 border-t border-gray-300">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-600">Total:</span>
                                    <span class="font-bold text-gray-900">${{ number_format($adhesiveTubeEstimate * $adhesiveUnitCostDefault, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- SECTION 4: Additional Materials (Custom) --}}
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
            <div class="bg-gradient-to-r from-gray-700 to-gray-800 px-6 py-4 flex items-center gap-3">
                <div class="bg-white text-gray-800 w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm">
                    4
                </div>
                <h2 class="text-xl font-bold text-white">Additional Materials</h2>
                <span class="ml-auto text-sm text-gray-300">Optional custom items</span>
            </div>
            <div class="p-6">
                <div id="customMaterialsWrapper">
                    @foreach($customMaterials as $index => $material)
                        @include('calculators.partials.custom-material-row', [
                            'index' => $index,
                            'name' => $material['name'] ?? '',
                            'qty' => $material['qty'] ?? '',
                            'unitCost' => $material['unit_cost'] ?? ''
                        ])
                    @endforeach
                    @if(empty($customMaterials))
                        @include('calculators.partials.custom-material-row', ['index' => 0, 'name' => '', 'qty' => '', 'unitCost' => ''])
                    @endif
                </div>
                <button type="button" onclick="addCustomMaterialRow()" class="mt-4 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors text-sm font-medium">
                    + Add Another Material
                </button>
            </div>
        </div>

        {{-- Job Notes --}}
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
            <div class="p-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Job Notes (Optional)</label>
                <textarea 
                    name="job_notes" 
                    rows="4" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors resize-none" 
                    placeholder="Add any special notes, site conditions, or additional requirements..."
                >{{ old('job_notes', $formData['job_notes'] ?? '') }}</textarea>
                <p class="text-xs text-gray-500 mt-2">These notes will appear on the calculation results and PDF export.</p>
            </div>
        </div>

        {{-- Template Name (if template mode) --}}
        @if(($mode ?? null) === 'template')
        <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Template Name (Optional)</label>
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
                {{ $editMode ? 'Update Calculation' : 'Calculate Wall Estimate' }}
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

let materialRowIndex = {{ count($customMaterials) > 0 ? count($customMaterials) : 1 }};
function addCustomMaterialRow() {
    const wrapper = document.getElementById('customMaterialsWrapper');
    const newRow = document.createElement('div');
    newRow.innerHTML = `@include('calculators.partials.custom-material-row', ['index' => '${materialRowIndex}', 'name' => '', 'qty' => '', 'unitCost' => ''])`.replace(/\$\{materialRowIndex\}/g, materialRowIndex);
    wrapper.insertAdjacentHTML('beforeend', newRow.innerHTML);
    materialRowIndex++;
}
</script>

@endsection
