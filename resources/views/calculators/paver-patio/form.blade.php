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

    $defaultUnitCosts = [
        'paver_unit_cost' => 3.25,
        'base_unit_cost' => 45.00,
        'plastic_edge_unit_cost' => 5.00,
        'concrete_edge_unit_cost' => 12.00,
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
    ];

    $customMaterials = old('custom_materials', $formData['custom_materials'] ?? []);
@endphp

@section('content')
<div class="max-w-4xl mx-auto py-10 space-y-8">
    <div>
        <h1 class="text-3xl font-bold">
            {{ $editMode ? 'Edit Paver Patio Data' : 'Paver Patio Calculator' }}
        </h1>
        <p class="text-gray-600 mt-2">Estimate materials, labor, and logistics with the same grouped layout as the planting calculator.</p>
    </div>

    <form method="POST" action="{{ route('calculators.patio.calculate') }}" class="space-y-8">
        @csrf

        {{-- Edit Mode: Calculation ID --}}
        @if ($editMode && isset($calculation))
            <input type="hidden" name="calculation_id" value="{{ $calculation->id }}">
        @endif

        {{-- Required --}}
        <input type="hidden" name="site_visit_id" value="{{ $siteVisitId }}">

        {{-- Crew & Logistics --}}
        <div>
            <h2 class="text-xl font-semibold mb-2">Crew & Logistics</h2>
            @include('calculators.partials.overhead_inputs')
        </div>

        {{-- Core Inputs --}}
        <div>
            <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between mb-3">
                <h2 class="text-xl font-semibold">Patio Inputs</h2>
                <span
                    id="patioAreaBadge"
                    class="text-sm font-medium {{ $areaSqft ? 'text-gray-600' : 'text-gray-500' }}"
                    data-empty-message="Enter length + width to unlock quantities."
                    data-prefix="Area: "
                >
                    {{ $areaSqft ? 'Area: ' . number_format($areaSqft, 2) . ' sqft' : 'Enter length + width to unlock quantities.' }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border rounded-lg p-4 bg-white shadow-sm">
                    <label class="block font-semibold mb-2">Length (ft)</label>
                    <input type="number" step="0.1" name="length" class="form-input w-full"
                           value="{{ $lengthValue }}" required>
                    <p class="text-xs text-gray-500 mt-2">Paired with width to generate patio area.</p>
                </div>

                <div class="border rounded-lg p-4 bg-white shadow-sm">
                    <label class="block font-semibold mb-2">Width (ft)</label>
                    <input type="number" step="0.1" name="width" class="form-input w-full"
                           value="{{ $widthValue }}" required>
                    <p class="text-xs text-gray-500 mt-2">Used for material quantities and labor hours.</p>
                </div>

                <div class="border rounded-lg p-4 bg-white shadow-sm">
                    <label class="block font-semibold mb-2">Paver Type</label>
                    <select name="paver_type" class="form-select w-full" required>
                        <option value="">-- Select a Brand --</option>
                        <option value="belgard" {{ $paverTypeValue === 'belgard' ? 'selected' : '' }}>Belgard</option>
                        <option value="techo" {{ $paverTypeValue === 'techo' ? 'selected' : '' }}>Techo-Bloc</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-2">Switch brands to reflect catalog pricing.</p>
                </div>

                <div class="border rounded-lg p-4 bg-white shadow-sm">
                    <label class="block font-semibold mb-2">Edge Restraint Type</label>
                    <select name="edge_restraint" class="form-select w-full" required>
                        <option value="">-- Choose Edge Type --</option>
                        <option value="plastic" {{ $edgeSelection === 'plastic' ? 'selected' : '' }}>Plastic</option>
                        <option value="concrete" {{ $edgeSelection === 'concrete' ? 'selected' : '' }}>Concrete</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-2">Determines which default edge pricing is applied.</p>
                </div>
            </div>
        </div>

        {{-- Material Preview --}}
        <div>
            <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between mb-3">
                <div>
                    <h2 class="text-xl font-semibold">Materials & Pricing Preview</h2>
                    <p class="text-gray-500 text-sm">Mirrors the planting calculator grid so everything lines up.</p>
                </div>
                <span
                    id="materialPreviewHint"
                    class="text-sm {{ $areaSqft ? 'text-gray-600' : 'text-gray-500' }}"
                    data-empty-message="Enter dimensions above to unlock quantities."
                    data-filled-message="Quantities update automatically while you type."
                >
                    {{ $areaSqft ? 'Quantities update automatically while you type.' : 'Enter dimensions above to unlock quantities.' }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach ($materialCards as $card)
                    <div class="border rounded-lg p-4 bg-white shadow-sm flex flex-col">
                        <div class="flex items-center justify-between">
                            <p class="font-semibold">{{ $card['label'] }}</p>
                            <span class="text-sm text-gray-500">{{ $card['unit'] }}</span>
                        </div>

                        <div class="mt-4">
                            <p class="text-xs uppercase tracking-wide text-gray-500">Qty Estimate</p>
                            <p class="text-2xl font-bold" data-material-qty="{{ \Illuminate\Support\Str::slug($card['label'], '_') }}">
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

                        <div class="mt-4">
                            <p class="text-xs uppercase tracking-wide text-gray-500">Default Unit Cost</p>
                            <p class="text-lg font-semibold" data-material-cost="{{ \Illuminate\Support\Str::slug($card['label'], '_') }}">
                                @if(!is_null($card['unit_cost']))
                                    ${{ number_format($card['unit_cost'], 2) }}
                                @else
                                    Select edge type
                                @endif
                            </p>
                        </div>

                        <p class="text-xs text-gray-500 mt-4">{{ $card['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Additional Materials --}}
        <div>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between mb-3">
                <div>
                    <h2 class="text-xl font-semibold">Additional Materials</h2>
                    <p class="text-gray-500 text-sm">Add any other items (polymeric sand, lighting, etc.). They roll into material totals everywhere.</p>
                </div>
                <button type="button" id="addCustomMaterial" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium">
                    + Add Material
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

        {{-- Material Overrides --}}
        @include('calculators.partials.material_override_inputs', [
            'overrideToggleName' => 'materials_override_enabled',
            'overrideToggleLabel' => 'Show Material Cost Overrides',
            'overrideChecked' => (bool) $overrideChecked,
            'fields' => [
                [
                    'name' => 'override_paver_cost',
                    'label' => 'Paver Cost per sqft ($)',
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                    'value' => $formData['override_paver_cost'] ?? '',
                    'width' => 'half',
                ],
                [
                    'name' => 'override_base_cost',
                    'label' => 'Base Gravel Cost per Ton ($)',
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                    'value' => $formData['override_base_cost'] ?? '',
                    'width' => 'half',
                ],
                [
                    'name' => 'override_plastic_edge_cost',
                    'label' => 'Plastic Edge ($/20ft)',
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                    'value' => $formData['override_plastic_edge_cost'] ?? '',
                    'width' => 'half',
                ],
                [
                    'name' => 'override_concrete_edge_cost',
                    'label' => 'Concrete Edge ($/20ft)',
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                    'value' => $formData['override_concrete_edge_cost'] ?? '',
                    'width' => 'half',
                ],
            ],
        ])

        {{-- Submit --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-6">
            <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">
                {{ $editMode ? 'Recalculate Patio' : 'Calculate Patio Data' }}
            </button>
            <a href="{{ route('clients.show', $siteVisitId) }}"
               class="inline-flex items-center px-5 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-semibold">
                Back to Client
            </a>
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
        };

        const qtyEls = {
            pavers: document.querySelector('[data-material-qty="pavers"]'),
            base: document.querySelector('[data-material-qty="78_base_gravel"]'),
            edge: document.querySelector('[data-material-qty="edge_restraints"]'),
        };

        const costEls = {
            pavers: document.querySelector('[data-material-cost="pavers"]'),
            base: document.querySelector('[data-material-cost="78_base_gravel"]'),
            edge: document.querySelector('[data-material-cost="edge_restraints"]'),
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
            el.classList.toggle('text-gray-600', isFilled);
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
                const emptyMsg = materialHint.dataset.emptyMessage || 'Enter dimensions above to unlock quantities.';
                const filledMsg = materialHint.dataset.filledMessage || 'Quantities update automatically while you type.';
                setBadgeText(materialHint, area ? filledMsg : emptyMsg, Boolean(area));
            }

            const paverQty = area ? Math.ceil(area / defaults.paverCoverage) : null;
            const baseQty = area ? Math.ceil((area * defaults.baseDepthFeet) / defaults.baseTonsDivisor) : null;
            const edgeQty = area ? parseFloat((area / defaults.edgeLfDivisor).toFixed(2)) : null;

            if (qtyEls.pavers) qtyEls.pavers.textContent = formatQty(paverQty, true);
            if (qtyEls.base) qtyEls.base.textContent = formatQty(baseQty, true);
            if (qtyEls.edge) qtyEls.edge.textContent = formatQty(edgeQty);

            const paverCost = resolveCost(overrideInputs.paver, defaults.paverUnitCost);
            const baseCost = resolveCost(overrideInputs.base, defaults.baseUnitCost);

            if (costEls.pavers) costEls.pavers.textContent = formatCurrency(paverCost);
            if (costEls.base) costEls.base.textContent = formatCurrency(baseCost);

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

