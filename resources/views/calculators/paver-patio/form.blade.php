@extends('layouts.sidebar')

@php
    $hasOverrides = collect(old())->keys()->filter(fn($key) => str_starts_with($key, 'override_'))->isNotEmpty();
    $overrideChecked = old('materials_override_enabled', $formData['materials_override_enabled'] ?? $hasOverrides);

    $lengthValue = old('length', $formData['length'] ?? null);
    $widthValue = old('width', $formData['width'] ?? null);
    $paverTypeValue = old('paver_type', $formData['paver_type'] ?? '');
    $edgeSelection = old('edge_restraint', $formData['edge_restraint'] ?? '');

    $areaSqft = ($lengthValue && $widthValue) ? round($lengthValue * $widthValue, 2) : null;
    $paverCoverage = 0.94;
    $paverCountEstimate = $areaSqft ? (int) ceil($areaSqft / $paverCoverage) : null;
    $baseDepthFeet = 2.5 / 12;
    $baseTonsEstimate = $areaSqft ? (int) ceil(($areaSqft * $baseDepthFeet) / 21.6) : null;
    $edgeLfEstimate = $areaSqft ? round($areaSqft / 20, 2) : null;

    $polymericCoverageSqft = 60;

    $defaultUnitCosts = [
        'paver_unit_cost' => 3.25,
        'base_unit_cost' => 45.00,
        'plastic_edge_unit_cost' => 5.00,
        'concrete_edge_unit_cost' => 12.00,
        'polymeric_sand_unit_cost' => 28.00,
    ];

    $edgeCostLookup = [
        'plastic' => old('override_plastic_edge_cost', $formData['override_plastic_edge_cost'] ?? null) ?: $defaultUnitCosts['plastic_edge_unit_cost'],
        'concrete' => old('override_concrete_edge_cost', $formData['override_concrete_edge_cost'] ?? null) ?: $defaultUnitCosts['concrete_edge_unit_cost'],
    ];

    $materialCards = [
        [
            'label' => 'Pavers',
            'unit' => 'stones',
            'qty' => $paverCountEstimate,
            'qty_is_int' => true,
            'unit_cost' => old('override_paver_cost', $formData['override_paver_cost'] ?? null) ?: $defaultUnitCosts['paver_unit_cost'],
            'description' => '0.94 sqft coverage per stone',
        ],
        [
            'label' => '#78 Base Gravel',
            'unit' => 'tons',
            'qty' => $baseTonsEstimate,
            'qty_is_int' => true,
            'unit_cost' => old('override_base_cost', $formData['override_base_cost'] ?? null) ?: $defaultUnitCosts['base_unit_cost'],
            'description' => '2.5" compacted depth assumption',
        ],
        [
            'label' => 'Edge Restraints',
            'unit' => 'LF (est.)',
            'qty' => $edgeLfEstimate,
            'qty_is_int' => false,
            'unit_cost' => $edgeSelection ? $edgeCostLookup[$edgeSelection] : null,
            'description' => sprintf('Plastic $%s /20ft | Concrete $%s /20ft', number_format($defaultUnitCosts['plastic_edge_unit_cost'], 2), number_format($defaultUnitCosts['concrete_edge_unit_cost'], 2)),
        ],
        [
            'label' => 'Polymeric Sand',
            'unit' => 'bags',
            'qty' => data_get($formData, 'materials.Polymeric Sand.qty') ?? ($areaSqft ? (int) ceil($areaSqft / $polymericCoverageSqft) : null),
            'qty_is_int' => true,
            'unit_cost' => old('override_polymeric_sand_cost', $formData['override_polymeric_sand_cost'] ?? null) ?: $defaultUnitCosts['polymeric_sand_unit_cost'],
            'description' => 'Avg. coverage ~60 sqft per bag.',
        ],
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

    <form method="POST" action="{{ route('calculators.patio.calculate') }}">
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
                    <label class="block font-semibold text-gray-900 mb-2">Paver Type</label>
                    <select name="paver_type" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent" required>
                        <option value="">-- Select a Brand --</option>
                        <option value="belgard" {{ $paverTypeValue === 'belgard' ? 'selected' : '' }}>Belgard</option>
                        <option value="techo" {{ $paverTypeValue === 'techo' ? 'selected' : '' }}>Techo-Bloc</option>
                    </select>
                    <p class="text-xs text-gray-600 mt-2">Brand selection for catalog pricing</p>
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
            </div>
        </div>

        {{-- 3️⃣ Materials Preview --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 mb-6">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-gradient-to-br from-gray-800 to-gray-700 flex items-center justify-center">
                        <span class="text-white font-bold text-sm">3</span>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Materials & Pricing Preview</h2>
                        <p class="text-sm text-gray-600">Auto-calculated quantities based on area</p>
                    </div>
                </div>
                <span id="materialPreviewHint" class="text-sm font-medium {{ $areaSqft ? 'text-green-700' : 'text-gray-500' }}" data-empty-message="Enter dimensions to unlock quantities" data-filled-message="Updating automatically">{{ $areaSqft ? 'Updating automatically' : 'Enter dimensions to unlock quantities' }}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($materialCards as $card)
                    <div class="bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <p class="font-bold text-gray-900">{{ $card['label'] }}</p>
                            <span class="text-xs font-medium text-gray-600 bg-white px-2 py-1 rounded">{{ $card['unit'] }}</span>
                        </div>

                        <div class="mb-3">
                            <p class="text-xs uppercase tracking-wide text-gray-600 mb-1">Qty Estimate</p>
                            <p class="text-2xl font-bold text-gray-900" data-material-qty="{{ \Illuminate\Support\Str::slug($card['label'], '_') }}">
                                @if(!is_null($card['qty']))
                                    @if(!empty($card['qty_is_int']))
                                        {{ number_format($card['qty']) }}
                                    @else
                                        {{ number_format($card['qty'], 2) }}
                                    @endif
                                @else
                                    &mdash;
                                @endif
                            </p>
                        </div>

                        <div class="mb-3">
                            <p class="text-xs uppercase tracking-wide text-gray-600 mb-1">Unit Cost</p>
                            <p class="text-lg font-semibold text-amber-800" data-material-cost="{{ \Illuminate\Support\Str::slug($card['label'], '_') }}">
                                @if(!is_null($card['unit_cost']))
                                    ${{ number_format($card['unit_cost'], 2) }}
                                @else
                                    Select edge type
                                @endif
                            </p>
                        </div>

                        <p class="text-xs text-gray-600 border-t border-amber-200 pt-2">{{ $card['description'] }}</p>
                    </div>
                @endforeach
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
    document.addEventListener('DOMContentLoaded', function () {
        const lengthInput = document.querySelector('input[name="length"]');
        const widthInput = document.querySelector('input[name="width"]');
        const edgeSelect = document.querySelector('select[name="edge_restraint"]');

        const overrideInputs = {
            paver: document.querySelector('input[name="override_paver_cost"]'),
            base: document.querySelector('input[name="override_base_cost"]'),
            plasticEdge: document.querySelector('input[name="override_plastic_edge_cost"]'),
            concreteEdge: document.querySelector('input[name="override_concrete_edge_cost"]'),
            polymericSand: document.querySelector('input[name="override_polymeric_sand_cost"]'),
        };

        const qtyEls = {
            pavers: document.querySelector('[data-material-qty="pavers"]'),
            base: document.querySelector('[data-material-qty="78_base_gravel"]'),
            edge: document.querySelector('[data-material-qty="edge_restraints"]'),
            polymeric_sand: document.querySelector('[data-material-qty="polymeric_sand"]'),
        };

        const costEls = {
            pavers: document.querySelector('[data-material-cost="pavers"]'),
            base: document.querySelector('[data-material-cost="78_base_gravel"]'),
            edge: document.querySelector('[data-material-cost="edge_restraints"]'),
            polymeric_sand: document.querySelector('[data-material-cost="polymeric_sand"]'),
        };

        const areaBadge = document.getElementById('patioAreaBadge');
        const materialHint = document.getElementById('materialPreviewHint');
        const customRowsContainer = document.getElementById('customMaterialRows');
        const customTemplate = document.getElementById('customMaterialTemplate');
        const addCustomMaterialButton = document.getElementById('addCustomMaterial');

        const defaults = {
            paverUnitCost: 3.25,
            baseUnitCost: 45.0,
            plasticEdgeUnitCost: 5.0,
            concreteEdgeUnitCost: 12.0,
            paverCoverage: 0.94,
            baseDepthFeet: 2.5 / 12,
            baseTonsDivisor: 21.6,
            edgeLfDivisor: 20,
            polymericSandCoverage: 60,
            polymericSandUnitCost: 28.0,
        };

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

        const setBadgeText = (el, text, isFilled) => {
            if (!el) return;
            el.textContent = text;
            if (isFilled) {
                el.classList.remove('bg-gray-100', 'text-gray-600');
                el.classList.add('bg-amber-100', 'text-amber-800');
            } else {
                el.classList.remove('bg-amber-100', 'text-amber-800');
                el.classList.add('bg-gray-100', 'text-gray-600');
            }
        };

        const setHintText = (el, text, isFilled) => {
            if (!el) return;
            el.textContent = text;
            el.classList.toggle('text-green-700', isFilled);
            el.classList.toggle('text-gray-500', !isFilled);
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

        const recalc = () => {
            const length = lengthInput ? parseNumber(lengthInput.value) : null;
            const width = widthInput ? parseNumber(widthInput.value) : null;
            const area = length && width ? length * width : null;

            if (areaBadge) {
                const emptyMessage = areaBadge.dataset.emptyMessage || 'Enter length + width to unlock quantities.';
                const prefix = areaBadge.dataset.prefix || 'Area: ';
                setBadgeText(
                    areaBadge,
                    area ? `${prefix}${area.toFixed(2)} sqft` : emptyMessage,
                    Boolean(area)
                );
            }

            if (materialHint) {
                const emptyMsg = materialHint.dataset.emptyMessage || 'Enter dimensions to unlock quantities';
                const filledMsg = materialHint.dataset.filledMessage || 'Updating automatically';
                setHintText(materialHint, area ? filledMsg : emptyMsg, Boolean(area));
            }

            const paverQty = area ? Math.ceil(area / defaults.paverCoverage) : null;
            const baseQty = area ? Math.ceil((area * defaults.baseDepthFeet) / defaults.baseTonsDivisor) : null;
            const edgeQty = area ? parseFloat((area / defaults.edgeLfDivisor).toFixed(2)) : null;
            const polymericQty = area ? Math.ceil(area / defaults.polymericSandCoverage) : null;

            if (qtyEls.pavers) qtyEls.pavers.textContent = formatQty(paverQty, true);
            if (qtyEls.base) qtyEls.base.textContent = formatQty(baseQty, true);
            if (qtyEls.edge) qtyEls.edge.textContent = formatQty(edgeQty);
            if (qtyEls.polymeric_sand) qtyEls.polymeric_sand.textContent = formatQty(polymericQty, true);

            const paverCost = resolveCost(overrideInputs.paver, defaults.paverUnitCost);
            const baseCost = resolveCost(overrideInputs.base, defaults.baseUnitCost);
            const polymericCost = resolveCost(overrideInputs.polymericSand, defaults.polymericSandUnitCost);

            if (costEls.pavers) costEls.pavers.textContent = formatCurrency(paverCost);
            if (costEls.base) costEls.base.textContent = formatCurrency(baseCost);
            if (costEls.polymeric_sand) costEls.polymeric_sand.textContent = formatCurrency(polymericCost);

            if (costEls.edge) {
                const selection = edgeSelect ? edgeSelect.value : '';
                if (!selection) {
                    costEls.edge.textContent = 'Select edge type';
                } else {
                    const edgeCost = selection === 'plastic'
                        ? resolveCost(overrideInputs.plasticEdge, defaults.plasticEdgeUnitCost)
                        : resolveCost(overrideInputs.concreteEdge, defaults.concreteEdgeUnitCost);
                    costEls.edge.textContent = formatCurrency(edgeCost);
                }
            }
        };

        recalc();

        [lengthInput, widthInput].forEach((input) => {
            if (!input) return;
            input.addEventListener('input', recalc);
        });

        if (edgeSelect) {
            edgeSelect.addEventListener('change', recalc);
        }

        Object.values(overrideInputs).forEach((input) => {
            if (!input) return;
            input.addEventListener('input', recalc);
        });
    });
</script>
@endpush
