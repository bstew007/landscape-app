@extends('layouts.sidebar')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Modern Header with Icon --}}
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
                    {{ $editMode ? 'Edit Synthetic Turf Estimate' : 'Synthetic Turf Calculator' }}
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
                    @if(!empty($estimateId))
                        <p class="text-sm text-blue-600 mt-1">Target Estimate: #{{ $estimateId }}</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('calculators.syn_turf.calculate') }}">
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

        {{-- 2️⃣ Project & Base Parameters --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-gradient-to-br from-gray-800 to-gray-700 flex items-center justify-center">
                    <span class="text-white font-bold text-sm">2</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Project & Base Parameters</h2>
                    <p class="text-sm text-gray-600">Area, edging, and base layer specifications</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Project Square Footage</label>
                    <input type="number" name="area_sqft" min="1" step="any" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                           value="{{ old('area_sqft', $formData['area_sqft'] ?? '') }}">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Perimeter / Edging (Linear Feet)</label>
                    <input type="number" name="edging_linear_ft" min="0" step="any" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                           value="{{ old('edging_linear_ft', $formData['edging_linear_ft'] ?? '') }}">
                    <p class="text-xs text-gray-500 mt-1">Used to calculate 20' edging boards</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Excavation Depth (in)</label>
                    <input type="number" name="excavation_depth_in" min="0" step="0.25"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                           value="{{ old('excavation_depth_in', $formData['excavation_depth_in'] ?? 3) }}">
                    <p class="text-xs text-gray-500 mt-1">Auto-calc excavation volume (cubic yards)</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">ABC Depth (in)</label>
                    <input type="number" name="abc_depth_in" min="0" step="0.25"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                           value="{{ old('abc_depth_in', $formData['abc_depth_in'] ?? '') }}">
                    <p class="text-xs text-gray-500 mt-1">ABC base material depth</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Rock Dust Depth (in)</label>
                    <input type="number" name="rock_dust_depth_in" min="0" step="0.25"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                           value="{{ old('rock_dust_depth_in', $formData['rock_dust_depth_in'] ?? '') }}">
                    <p class="text-xs text-gray-500 mt-1">Rock dust material depth</p>
                </div>
            </div>

            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="rent_tamper" id="rent_tamper" value="1" 
                               class="w-4 h-4 text-brand-600 border-gray-300 rounded focus:ring-brand-500"
                               {{ old('rent_tamper') ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-gray-700">Tamper Rental</span>
                    </label>
                    <input type="number" name="tamper_days" min="1" step="1"
                           class="w-20 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                           value="{{ old('tamper_days', 1) }}">
                    <span class="text-sm text-gray-600">days @ $125/day</span>
                </div>
            </div>
        </div>

        {{-- 3️⃣ Synthetic Turf Selection --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-gradient-to-br from-gray-800 to-gray-700 flex items-center justify-center">
                    <span class="text-white font-bold text-sm">3</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Synthetic Turf Selection</h2>
                    <p class="text-sm text-gray-600">Choose turf tier and quality</p>
                </div>
            </div>
            @php
                $turfOptions = config('syn_turf.materials.turf_tiers', []);
                $turfGrade = old('turf_grade', $formData['turf_grade'] ?? 'better');
            @endphp
            <label class="block text-sm font-semibold text-gray-700 mb-2">Turf Tier</label>
            <select name="turf_grade" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                @foreach ($turfOptions as $key => $tier)
                    <option value="{{ $key }}" data-unit-cost="{{ $tier['unit_cost'] ?? 0 }}" data-label="{{ $tier['label'] ?? ucfirst($key) }}"
                        {{ $turfGrade === $key ? 'selected' : '' }}>
                        {{ $tier['label'] ?? ucfirst($key) }} (${{ number_format($tier['unit_cost'] ?? 0, 2) }} / sq ft)
                    </option>
                @endforeach
            </select>
        </div>

        {{-- 4️⃣ Materials (Editable) --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6" id="materialsEditSection">
            <div class="flex items-center gap-3 mb-6">
                <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-gradient-to-br from-gray-800 to-gray-700 flex items-center justify-center">
                    <span class="text-white font-bold text-sm">4</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Materials (Editable)</h2>
                    <p class="text-sm text-gray-600">Adjust quantities and unit costs</p>
                </div>
            </div>
            @php
                $m = $formData['materials'] ?? [];
                $areaVal = old('area_sqft', $formData['area_sqft'] ?? 0);
                $turfLabel = $formData['turf_name'] ?? (($turfOptions[$turfGrade]['label'] ?? ucfirst($turfGrade)).' Synthetic Turf');
                $defaults = [
                    'turf' => [
                        'label' => $turfLabel,
                        'qty' => $areaVal,
                        'unit_cost' => $formData['turf_unit_cost'] ?? ($turfOptions[$turfGrade]['unit_cost'] ?? 0),
                    ],
                    'infill_bags' => [
                        'label' => 'Infill Bags',
                        'qty' => $m['Infill Bags']['qty'] ?? null,
                        'unit_cost' => $m['Infill Bags']['unit_cost'] ?? 25,
                    ],
                    'edging_boards' => [
                        'label' => 'Composite Edging Boards',
                        'qty' => $m['Composite Edging Boards']['qty'] ?? null,
                        'unit_cost' => $m['Composite Edging Boards']['unit_cost'] ?? 45,
                    ],
                    'weed_barrier_rolls' => [
                        'label' => 'Weed Barrier Rolls',
                        'qty' => $m['Weed Barrier Rolls']['qty'] ?? null,
                        'unit_cost' => $m['Weed Barrier Rolls']['unit_cost'] ?? 75,
                    ],
                    'abc_cy' => [
                        'label' => 'ABC Base (cy)',
                        'qty' => $m['ABC Base (cy)']['qty'] ?? null,
                        'unit_cost' => 38,
                    ],
                    'rock_dust_cy' => [
                        'label' => 'Rock Dust (cy)',
                        'qty' => $m['Rock Dust (cy)']['qty'] ?? null,
                        'unit_cost' => 42,
                    ],
                ];
            @endphp
            <div class="grid grid-cols-1 gap-3">
                @foreach ($defaults as $key => $row)
                    @php
                        $qty = old("materials_edit.$key.qty", $row['qty']);
                        $cost = old("materials_edit.$key.unit_cost", $row['unit_cost']);
                        $line = (is_numeric($qty) && is_numeric($cost)) ? number_format((float)$qty * (float)$cost, 2) : null;
                    @endphp
                    <div class="border border-gray-200 rounded-lg p-4 bg-gradient-to-br from-gray-50 to-gray-100" data-material-row data-key="{{ $key }}">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="flex-1">
                                <div class="font-semibold text-gray-900">{{ $row['label'] }}</div>
                                <div class="text-xs text-gray-500">Key: {{ $key }}</div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Qty</label>
                                    <input type="number" step="0.01" min="0" name="materials_edit[{{ $key }}][qty]" 
                                           class="w-28 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent" 
                                           value="{{ $qty }}" data-material-qty>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Unit Cost ($)</label>
                                    <input type="number" step="0.01" min="0" name="materials_edit[{{ $key }}][unit_cost]" 
                                           class="w-28 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent" 
                                           value="{{ $cost }}" data-material-cost>
                                </div>
                                <div class="w-32 text-right">
                                    <div class="text-xs text-gray-600 mb-1">Line Total</div>
                                    <div class="font-semibold text-gray-900" data-material-line>{{ $line ? '$'.$line : '—' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 pt-4 border-t border-gray-200 text-right">
                <span class="text-sm text-gray-600 mr-2">Materials Subtotal:</span>
                <span class="text-lg font-bold text-gray-900" id="materialsEditSubtotal">—</span>
            </div>
        </div>

        {{-- 5️⃣ Synthetic Turf Tasks --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-gradient-to-br from-gray-800 to-gray-700 flex items-center justify-center">
                    <span class="text-white font-bold text-sm">5</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Labor Tasks</h2>
                    <p class="text-sm text-gray-600">Installation tasks with production rates</p>
                </div>
            </div>

            @php
                $savedTasks = $formData['tasks'] ?? [];
                $savedQuantities = [];

                foreach ($savedTasks as $taskRow) {
                    $key = str_replace(' ', '_', strtolower($taskRow['task']));
                    $savedQuantities[$key] = $taskRow['qty'] ?? null;
                }

                $rates = \App\Models\ProductionRate::where('calculator', 'syn_turf')->get();
                $rateMap = $rates->keyBy('task');
                $preferredOrder = ['excavation','excavation_skid_steer','excavation_mini_skid','base_install','edging','syn_turf_install','infill'];
            @endphp

            @php
                $excavationTasks = ['excavation','excavation_skid_steer','excavation_mini_skid'];
                $methodFromOld = old('excavation_method');
                $selectedExcavation = $methodFromOld;
                if (!$selectedExcavation) {
                    if (!empty($savedQuantities['excavation_skid_steer'])) {
                        $selectedExcavation = 'skid';
                    } elseif (!empty($savedQuantities['excavation_mini_skid'])) {
                        $selectedExcavation = 'mini';
                    } else {
                        $selectedExcavation = 'generic';
                    }
                }
            @endphp

            {{-- Excavation Method Selection --}}
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4 mb-6">
                <label class="block font-semibold text-gray-900 mb-3">Excavation Method</label>
                <div class="flex flex-col sm:flex-row gap-3">
                    <label class="flex items-center gap-2 px-4 py-2 bg-white border-2 border-gray-200 rounded-lg cursor-pointer hover:border-brand-500 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50 transition-colors">
                        <input type="radio" name="excavation_method" value="generic" class="w-4 h-4 text-brand-600 border-gray-300 focus:ring-brand-500" {{ $selectedExcavation === 'generic' ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-gray-700">Generic</span>
                    </label>
                    <label class="flex items-center gap-2 px-4 py-2 bg-white border-2 border-gray-200 rounded-lg cursor-pointer hover:border-brand-500 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50 transition-colors">
                        <input type="radio" name="excavation_method" value="skid" class="w-4 h-4 text-brand-600 border-gray-300 focus:ring-brand-500" {{ $selectedExcavation === 'skid' ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-gray-700">Skid Steer</span>
                    </label>
                    <label class="flex items-center gap-2 px-4 py-2 bg-white border-2 border-gray-200 rounded-lg cursor-pointer hover:border-brand-500 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50 transition-colors">
                        <input type="radio" name="excavation_method" value="mini" class="w-4 h-4 text-brand-600 border-gray-300 focus:ring-brand-500" {{ $selectedExcavation === 'mini' ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-gray-700">Mini Skid</span>
                    </label>
                </div>
                <p class="text-xs text-gray-600 mt-2">Select one excavation method - the corresponding task input will be enabled</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @php $rendered = []; @endphp
                @foreach ($preferredOrder as $key)
                    @if ($rateMap->has($key))
                        @php
                            $rate = $rateMap->get($key);
                            $label = ucwords(str_replace('_', ' ', $key));
                            $value = old("tasks.$key.qty", $savedQuantities[$key] ?? '');
                            $isExcavation = in_array($key, ['excavation','excavation_skid_steer','excavation_mini_skid']);
                            $rendered[$key] = true;
                        @endphp
                        <div class="border border-gray-200 rounded-lg p-4 bg-gradient-to-br from-gray-50 to-gray-100 {{ $isExcavation ? 'excavation-card' : '' }}" @if($isExcavation) data-excavation-task="1" data-excavation-key="{{ $key }}" @endif>
                            <label class="block font-semibold text-gray-900 mb-2">
                                {{ $label }}
                                <span class="text-sm text-gray-600 font-normal">({{ $rate->unit }})</span>
                            </label>
                            <input
                                type="number"
                                name="tasks[{{ $key }}][qty]"
                                step="any"
                                min="0"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                                placeholder="Enter quantity"
                                value="{{ $value }}"
                            >
                            <p class="text-xs text-gray-600 mt-2">
                                Rate: {{ number_format($rate->rate, 4) }} hrs/{{ $rate->unit }}
                            </p>
                        </div>
                    @endif
                @endforeach

                @foreach ($rateMap as $key => $rate)
                    @continue(isset($rendered[$key]))
                    @php
                        if ($key === 'base') continue; // legacy hidden
                        $label = ucwords(str_replace('_', ' ', $key));
                        $value = old("tasks.$key.qty", $savedQuantities[$key] ?? '');
                        $isExcavation = in_array($key, ['excavation','excavation_skid_steer','excavation_mini_skid']);
                    @endphp
                    <div class="border border-gray-200 rounded-lg p-4 bg-gradient-to-br from-gray-50 to-gray-100 {{ $isExcavation ? 'excavation-card' : '' }}" @if($isExcavation) data-excavation-task="1" data-excavation-key="{{ $key }}" @endif>
                        <label class="block font-semibold text-gray-900 mb-2">
                            {{ $label }}
                            <span class="text-sm text-gray-600 font-normal">({{ $rate->unit }})</span>
                        </label>
                        <input
                            type="number"
                            name="tasks[{{ $key }}][qty]"
                            step="any"
                            min="0"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                            placeholder="Enter quantity"
                            value="{{ $value }}"
                        >
                        <p class="text-xs text-gray-600 mt-2">
                            Rate: {{ number_format($rate->rate, 4) }} hrs/{{ $rate->unit }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Submit Buttons --}}
        <div class="flex flex-col sm:flex-row gap-4 items-center justify-between mt-8">
            @if(($mode ?? null) === 'template')
                <div class="w-full flex flex-col lg:flex-row gap-4">
                    <input type="text" name="template_name" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent" placeholder="Template name (e.g., Small backyard turf)" value="{{ old('template_name') }}">
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
                    {{ $editMode ? 'Recalculate Synthetic Turf' : 'Calculate Synthetic Turf' }}
                </button>
                <a href="{{ route('clients.show', $siteVisit->client->id ?? $siteVisitId) }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors duration-200">
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
(function(){
    function initSynTurfCalc(){

        const toggle = document.getElementById('toggleAdvancedTasks');
        const advanced = document.querySelectorAll('.advanced-task');

        function updateVisibility() {
            advanced.forEach(el => el.classList.toggle('hidden', !toggle.checked));
        }

        // Helper getters
        const areaInput = document.querySelector('input[name="area_sqft"]');
        const edgingLF = document.querySelector('input[name="edging_linear_ft"]');
        const abcDepthInput = document.querySelector('input[name="abc_depth_in"]');
        const rockDepthInput = document.querySelector('input[name="rock_dust_depth_in"]');
        const excDepthInput = document.querySelector('input[name="excavation_depth_in"]');
        const excavationPreview = document.getElementById('excavationVolPreview');

        // Materials edit inputs
        const mat = {
            turf: {
                qty: document.querySelector('input[name="materials_edit[turf][qty]"]'),
                cost: document.querySelector('input[name="materials_edit[turf][unit_cost]"]'),
            },
            infill_bags: {
                qty: document.querySelector('input[name="materials_edit[infill_bags][qty]"]'),
                cost: document.querySelector('input[name="materials_edit[infill_bags][unit_cost]"]'),
            },
            edging_boards: {
                qty: document.querySelector('input[name="materials_edit[edging_boards][qty]"]'),
                cost: document.querySelector('input[name="materials_edit[edging_boards][unit_cost]"]'),
            },
            weed_barrier_rolls: {
                qty: document.querySelector('input[name="materials_edit[weed_barrier_rolls][qty]"]'),
                cost: document.querySelector('input[name="materials_edit[weed_barrier_rolls][unit_cost]"]'),
            },
            abc_cy: {
                qty: document.querySelector('input[name="materials_edit[abc_cy][qty]"]'),
                cost: document.querySelector('input[name="materials_edit[abc_cy][unit_cost]"]'),
            },
            rock_dust_cy: {
                qty: document.querySelector('input[name="materials_edit[rock_dust_cy][qty]"]'),
                cost: document.querySelector('input[name="materials_edit[rock_dust_cy][unit_cost]"]'),
            },
        };

        // Excavation method toggle: only one of excavation, excavation_skid_steer, excavation_mini_skid
        const methodRadios = document.querySelectorAll('input[name="excavation_method"]');
        const excavationCards = document.querySelectorAll('.excavation-card');
        const methodToKey = { generic: 'excavation', skid: 'excavation_skid_steer', mini: 'excavation_mini_skid' };

        function getSelectedMethod() {
            const checked = Array.from(methodRadios).find(r => r.checked);
            return checked ? checked.value : 'generic';
        }

        function applyExcavationVisibility() {
            const method = getSelectedMethod();
            const allowedKey = methodToKey[method] || 'excavation';
            excavationCards.forEach(card => {
                const key = card.dataset.excavationKey || '';
                const show = key === allowedKey;
                card.style.display = show ? '' : 'none';
                const qtyInput = card.querySelector('input[type="number"][name^="tasks["][name$="][qty]"]');
                if (qtyInput) {
                    qtyInput.disabled = !show;
                    if (!show) {
                        qtyInput.value = '';
                    }
                }
            });
        }

        if (methodRadios.length) {
            methodRadios.forEach(r => r.addEventListener('change', () => { applyExcavationVisibility(); autoFillTasks(); }));
            applyExcavationVisibility();
        } else {
            // Default method if none rendered (fallback)
            autoFillTasks();
        }

        // Mirror edging linear feet to edging task qty (unless user overrides)
        const edgingQty = document.querySelector('input[name="tasks[edging][qty]"]');
        if (edgingLF && edgingQty) {
            function syncEdging() {
                if (edgingQty.dataset.userEdited === 'true') return;
                edgingQty.value = edgingLF.value;
                edgingQty.dataset.synced = 'true';
            }
            edgingLF.addEventListener('input', () => { syncEdging(); autoFillTasks(); });
            edgingQty.addEventListener('input', () => { edgingQty.dataset.userEdited = 'true'; });
            if (!edgingQty.value) syncEdging();
        }

        // Auto-fill task quantities from area / depths
        function num(val, d = 0) { const n = parseFloat(val); return isFinite(n) ? n : d; }
        function cyFrom(areaSqft, depthIn) { const cf = num(areaSqft) * (num(depthIn)/12); return +(cf/27).toFixed(2); }
        function setInputValue(input, value){ if(!input) return; input.value = value; input.dispatchEvent(new Event('input', { bubbles: true })); }

        function setIfNotUserEdited(selector, value) {
            const el = document.querySelector(selector);
            if (!el) return;
            if (el.dataset.userEdited === 'true') return;
            if (!el.value || el.dataset.synced === 'true') {
                el.value = value;
            }
        }
        function setMatIfNotUserEdited(node, value){ if(!node) return; if(node.dataset.userEdited==='true') return; if(!node.value || node.dataset.synced==='true'){ node.value = value; node.dataset.synced='true'; } }

        function autoFillTasks() {
            const area = num(areaInput?.value, 0);
            const method = getSelectedMethod();
            const excDepth = num(excDepthInput?.value, 3);
            const abcDepth = num(abcDepthInput?.value, 0);
            const rockDepth = num(rockDepthInput?.value, 0);
            const excCY = cyFrom(area, excDepth);
            const abcCY_fromDepth = cyFrom(area, abcDepth);
            const rockCY_fromDepth = cyFrom(area, rockDepth);
            const baseCY = +(abcCY_fromDepth + rockCY_fromDepth).toFixed(2);

            // ALWAYS set area-dependent tasks from Project Square Footage
            const infillTask = document.querySelector('input[name="tasks[infill][qty]"]');
            const turfTask = document.querySelector('input[name="tasks[syn_turf_install][qty]"]');
            if (infillTask) setInputValue(infillTask, area);
            if (turfTask) setInputValue(turfTask, area);

            // Materials (area-dependent)
            const bags = Math.max(0, Math.ceil(area / 50));
            if (mat.turf.qty) { mat.turf.qty.value = area; mat.turf.qty.dataset.synced='true'; mat.turf.qty.dispatchEvent(new Event('input', { bubbles:true })); }
            if (mat.infill_bags.qty) { mat.infill_bags.qty.value = bags; mat.infill_bags.qty.dataset.synced='true'; mat.infill_bags.qty.dispatchEvent(new Event('input', { bubbles:true })); }

            // ABC / Rock dust -> materials and base_install task
            if (mat.abc_cy.qty) { mat.abc_cy.qty.value = abcCY_fromDepth; mat.abc_cy.qty.dataset.synced='true'; mat.abc_cy.qty.dispatchEvent(new Event('input', { bubbles:true })); }
            if (mat.rock_dust_cy.qty) { mat.rock_dust_cy.qty.value = rockCY_fromDepth; mat.rock_dust_cy.qty.dataset.synced='true'; mat.rock_dust_cy.qty.dispatchEvent(new Event('input', { bubbles:true })); }
            const baseTask = document.querySelector('input[name="tasks[base_install][qty]"]');
            if (baseTask) setInputValue(baseTask, baseCY);

            // Edging boards from LF (20' boards)
            const lf = num(edgingLF?.value, 0);
            const boards = Math.max(0, Math.ceil(lf / 20));
            if (mat.edging_boards.qty) { mat.edging_boards.qty.value = boards; mat.edging_boards.qty.dataset.synced='true'; mat.edging_boards.qty.dispatchEvent(new Event('input', { bubbles:true })); }

            // Weed barrier from area (1800 sqft/roll default)
            if (mat.weed_barrier_rolls.qty) { const rolls = Math.max(0, Math.ceil(area / 1800)); mat.weed_barrier_rolls.qty.value = rolls; mat.weed_barrier_rolls.qty.dataset.synced='true'; mat.weed_barrier_rolls.qty.dispatchEvent(new Event('input', { bubbles:true })); }

            // excavation auto-fill based on method
            if (method === 'generic') {
                const gen = document.querySelector('input[name="tasks[excavation][qty]"]');
                if (gen) setInputValue(gen, area);
            } else {
                const gen = document.querySelector('input[name="tasks[excavation][qty]"]');
                if (gen && gen.dataset.userEdited !== 'true') gen.value = '';
            }
            if (method === 'skid') {
                const el = document.querySelector('input[name="tasks[excavation_skid_steer][qty]"]');
                if (el) setInputValue(el, excCY);
            } else {
                const el = document.querySelector('input[name="tasks[excavation_skid_steer][qty]"]');
                if (el && el.dataset.userEdited !== 'true') el.value = '';
            }
            if (method === 'mini') {
                const el = document.querySelector('input[name="tasks[excavation_mini_skid][qty]"]');
                if (el) setInputValue(el, excCY);
            } else {
                const el = document.querySelector('input[name="tasks[excavation_mini_skid][qty]"]');
                if (el && el.dataset.userEdited !== 'true') el.value = '';
            }

            if (excavationPreview) {
                excavationPreview.textContent = `Excavation volume: ${excCY} cy`;
            }
            // Update materials totals after autofill
            recalcMaterialsSubtotal();
        }

        // Mark user edits
        document.querySelectorAll('input[name^="tasks["][name$="][qty]"]').forEach(input => {
            input.addEventListener('input', () => { input.dataset.userEdited = 'true'; });
        });

        [areaInput, excDepthInput, abcDepthInput, rockDepthInput].forEach(el => {
            if (!el) return;
            ['input','change','blur'].forEach(evt => el.addEventListener(evt, autoFillTasks));
        });

        function fmtMoney(n){ const v = parseFloat(n); if(!isFinite(v)) return '—'; return '$' + v.toFixed(2); }
        function recalcMaterialsSubtotal(){
            let sum = 0;
            document.querySelectorAll('[data-material-row]').forEach(row => {
                const qty = parseFloat(row.querySelector('[data-material-qty]')?.value || '');
                const cost = parseFloat(row.querySelector('[data-material-cost]')?.value || '');
                const lineEl = row.querySelector('[data-material-line]');
                if (isFinite(qty) && isFinite(cost)) {
                    const line = qty * cost;
                    sum += line;
                    if (lineEl) lineEl.textContent = fmtMoney(line);
                } else {
                    if (lineEl) lineEl.textContent = '—';
                }
            });
            const sub = document.getElementById('materialsEditSubtotal');
            if (sub) sub.textContent = fmtMoney(sum);
        }

        document.querySelectorAll('[data-material-qty],[data-material-cost]').forEach(input => {
            input.addEventListener('input', () => { input.dataset.userEdited='true'; recalcMaterialsSubtotal(); });
        });

        // Turf grade change handler -> update turf label and unit cost
        const turfSelect = document.querySelector('select[name="turf_grade"]');
        if (turfSelect) {
            turfSelect.addEventListener('change', () => {
                const opt = turfSelect.options[turfSelect.selectedIndex];
                const unitCost = parseFloat(opt?.dataset?.unitCost || '0');
                const label = (opt?.dataset?.label || 'Turf') + ' Synthetic Turf';
                const turfRow = document.querySelector('[data-material-row][data-key="turf"]');
                if (turfRow) {
                    const labelEl = turfRow.querySelector('.font-semibold');
                    if (labelEl) labelEl.textContent = label;
                }
                if (mat.turf.cost && mat.turf.cost.dataset.userEdited !== 'true') {
                    mat.turf.cost.value = unitCost.toFixed(2);
                }
                // Also re-run auto-fill so Turf qty and related derived values refresh
                autoFillTasks();
                recalcMaterialsSubtotal();
            });
        }

        // Initial fill (ensure UI populated)
        autoFillTasks();
        recalcMaterialsSubtotal();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSynTurfCalc);
    } else {
        initSynTurfCalc();
    }
})();
</script>
@endpush
