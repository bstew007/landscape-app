@extends('layouts.sidebar')

@php
    $customMaterials = old('custom_materials', $formData['custom_materials'] ?? []);
    $areaValue = old('area_sqft', $formData['area_sqft'] ?? null);
    $depthValue = old('depth_inches', $formData['depth_inches'] ?? null);
    $mulchTypes = [
        'Forest Brown', 'Black', 'Red', 'Triple Shredded Hardwood',
        'Playground', 'Pine Fines', 'Mini Nuggets', 'Big Nuggets'
    ];
    $selectedMulchType = old('mulch_type', $formData['mulch_type'] ?? '');
    $storedMaterials = $formData['materials'] ?? [];
    $primaryMaterialName = $selectedMulchType ?: (collect($storedMaterials)->keys()->first() ?? 'Mulch');
    $primaryMaterial = $storedMaterials[$primaryMaterialName] ?? null;
    $defaultMulchCost = 35;
    $prefillCost = data_get($primaryMaterial, 'unit_cost', $defaultMulchCost);
    $prefillQty = data_get($primaryMaterial, 'qty');
    $mulchYardsPreview = ($areaValue && $depthValue) ? round(($areaValue * ($depthValue / 12)) / 27, 2) : null;
    $materialCards = [
        [
            'key' => 'mulch_material',
            'label' => $primaryMaterialName ?: 'Mulch',
            'unit' => 'cu yd',
            'qty' => $prefillQty ?? $mulchYardsPreview,
            'qty_is_int' => false,
            'unit_cost' => $prefillCost,
            'description' => 'Area × depth ÷ 27 = cubic yards.',
        ],
    ];
@endphp

@section('content')
<div class="max-w-4xl mx-auto py-10">
    @include('calculators.partials.form_header', [
        'title' => $editMode ? '✏️ Edit Mulching Data' : '🌿 Mulching Calculator',
        'subtitle' => null,
    ])

    @if(($mode ?? null) !== 'template' && $siteVisit)
        @include('calculators.partials.client_info', ['siteVisit' => $siteVisit])
    @else
        <div class="bg-white p-4 rounded border mb-6">
            <p class="text-sm text-gray-700">Template Mode — build a mulching estimate without a site visit.</p>
            @if(!empty($estimateId))
                <p class="text-sm text-gray-500">Target Estimate: #{{ $estimateId }}</p>
            @endif
        </div>
    @endif

    <form method="POST" action="{{ route('calculators.mulching.calculate') }}">
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
        <input type="hidden" name="site_visit_id" value="{{ $siteVisitId }}">

        {{-- Crew & Logistics --}}
        <div class="mb-6">
            @include('calculators.partials.section_heading', ['title' => 'Crew & Logistics'])
            @include('calculators.partials.overhead_inputs')
        </div>

        {{-- Mulch Area & Depth --}}
        <div class="mb-6">
            @include('calculators.partials.section_heading', ['title' => 'Mulch Coverage'])

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold mb-1">Square Footage</label>
                    <input type="number"
                           name="area_sqft"
                           step="any"
                           min="0"
                           class="form-input w-full"
                           placeholder="Enter total area (sqft)"
                           value="{{ $areaValue }}">
                </div>

                <div>
                    <label class="block font-semibold mb-1">Desired Mulch Depth (inches)</label>
                    <input type="number"
                           name="depth_inches"
                           step="any"
                           min="0"
                           class="form-input w-full"
                           placeholder="e.g. 2"
                           value="{{ $depthValue }}">
                </div>
            </div>
        </div>

        {{-- Mulch Type Dropdown --}}
        <div class="mb-6">
            <label class="block font-semibold mb-1">Mulch Type</label>
            <select name="mulch_type" class="form-select w-full">
                <option value="" disabled {{ empty($selectedMulchType) ? 'selected' : '' }}>Select a mulch type</option>
                @foreach ($mulchTypes as $type)
                    <option value="{{ $type }}" {{ $selectedMulchType === $type ? 'selected' : '' }}>
                        {{ $type }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Materials Preview --}}
        <div class="mb-6">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between mb-3">
                <div>
                    <h2 class="text-xl font-semibold">Materials & Pricing Preview</h2>
                    <p class="text-gray-500 text-sm">Same cards as the other calculators—see mulch yards + cost instantly.</p>
                </div>
                <span
                    id="materialPreviewHint"
                    class="text-sm {{ ($areaValue && $depthValue) ? 'text-gray-600' : 'text-gray-500' }}"
                    data-empty-message="Enter area + depth to unlock quantities."
                    data-filled-message="Quantities update automatically while you type."
                >
                    {{ ($areaValue && $depthValue) ? 'Quantities update automatically while you type.' : 'Enter area + depth to unlock quantities.' }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($materialCards as $card)
                    <div class="border rounded-lg p-4 bg-white shadow-sm flex flex-col">
                        <div class="flex items-center justify-between">
                            <p class="font-semibold" data-material-label="{{ $card['key'] }}">{{ $card['label'] }}</p>
                            <span class="text-sm text-gray-500">{{ $card['unit'] }}</span>
                        </div>

                        <div class="mt-4">
                            <p class="text-xs uppercase tracking-wide text-gray-500">Qty Estimate</p>
                            <p class="text-2xl font-bold" data-material-qty="{{ $card['key'] }}">
                                @if(!is_null($card['qty']))
                                    {{ number_format($card['qty'], 2) }}
                                @else
                                    &mdash;
                                @endif
                            </p>
                        </div>

                        <div class="mt-4">
                            <p class="text-xs uppercase tracking-wide text-gray-500">Default Unit Cost</p>
                            <p class="text-lg font-semibold" data-material-cost="{{ $card['key'] }}">
                                ${{ number_format($card['unit_cost'], 2) }}
                            </p>
                        </div>

                        <p class="text-xs text-gray-500 mt-4">{{ $card['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
     
        {{-- Task Inputs from DB --}}
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-2">Mulching Tasks</h2>

            @php
                $savedTasks = $formData['tasks'] ?? [];
                $savedQuantities = [];

                foreach ($savedTasks as $taskRow) {
                    $key = str_replace(' ', '_', strtolower($taskRow['task']));
                    $savedQuantities[$key] = $taskRow['qty'] ?? null;
                }

                $rates = \App\Models\ProductionRate::where('calculator', 'mulching')
                    ->orderBy('task')
                    ->get();
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($rates as $rate)
                    @php
                        $key = $rate->task;
                        $label = ucwords(str_replace('_', ' ', $key));
                        $value = old("tasks.$key.qty", $savedQuantities[$key] ?? '');
                        $isAdvanced = str_contains($key, 'overgrown') || str_contains($key, 'palm');
                    @endphp

                    <div class="border p-4 rounded bg-gray-50 {{ $isAdvanced ? 'advanced-task hidden' : '' }}">
                        <label class="block font-semibold mb-1">{{ $label }} ({{ $rate->unit }})</label>
                        <input type="number"
                               name="tasks[{{ $key }}][qty]"
                               step="any"
                               min="0"
                               class="form-input w-full"
                               placeholder="Enter quantity"
                               value="{{ $value }}">
                        <p class="text-sm text-gray-500">Rate: {{ number_format($rate->rate, 4) }} hrs/{{ $rate->unit }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        
        {{-- Additional Materials --}}
        <div class="mb-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between mb-3">
                <div>
                    <h2 class="text-xl font-semibold">Additional Materials</h2>
                    <p class="text-gray-500 text-sm">Log materials not auto-calculated (delivery fees, edging, etc.).</p>
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

        {{-- Submit --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-6">
            @if(($mode ?? null) === 'template')
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 w-full">
                    <input type="text" name="template_name" class="form-input w-full sm:w-72" placeholder="Template name (e.g., Small front bed)" value="{{ old('template_name') }}">
                    <select name="template_scope" class="form-select w-full sm:w-48">
                        <option value="global" {{ old('template_scope')==='global' ? 'selected' : '' }}>Global</option>
                        <option value="client" {{ old('template_scope')==='client' ? 'selected' : '' }}>This Client</option>
                        <option value="property" {{ old('template_scope')==='property' ? 'selected' : '' }}>This Property</option>
                    </select>
                    <button type="submit" name="import_now" value="0" class="btn btn-secondary">💾 Save Template</button>
                </div>
            @else
                <button type="submit" class="btn btn-secondary">
                    {{ $editMode ? '🔄 Recalculate Mulching' : '🧮 Calculate Mulching Data' }}
                </button>
                <a href="{{ route('clients.show', $siteVisitId) }}" class="btn btn-muted">🔙 Back to Client</a>
            @endif
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.getElementById('toggleAdvancedTasks');
        const advanced = document.querySelectorAll('.advanced-task');

        if (!toggle) {
            return;
        }

        function updateVisibility() {
            advanced.forEach(el => {
                el.classList.toggle('hidden', !toggle.checked);
            });
        }

        toggle.addEventListener('change', updateVisibility);
        updateVisibility(); // on load
    });
</script>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const customRowsContainer = document.getElementById('customMaterialRows');
        const customTemplate = document.getElementById('customMaterialTemplate');
        const addCustomMaterialButton = document.getElementById('addCustomMaterial');

        const parseNumber = (value) => {
            const num = parseFloat(value);
            return Number.isFinite(num) ? num : null;
        };

        const formatCurrency = (value) => `$${Number(value).toFixed(2)}`;

        const recalcCustomMaterials = () => {
            if (!customRowsContainer) return;
            customRowsContainer.querySelectorAll('[data-custom-row]').forEach((row) => {
                const qtyInput = row.querySelector('[data-custom-qty]');
                const costInput = row.querySelector('[data-custom-cost]');
                const totalEl = row.querySelector('[data-custom-total]');
                if (!totalEl) return;

                const qty = qtyInput ? parseNumber(qtyInput.value) : null;
                const cost = costInput ? parseNumber(costInput.value) : null;

                totalEl.textContent = (qty === null || cost === null) ? '--' : formatCurrency(qty * cost);
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
    });
</script>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const areaInput = document.querySelector('input[name="area_sqft"]');
        const depthInput = document.querySelector('input[name="depth_inches"]');
        const mulchTypeSelect = document.querySelector('select[name="mulch_type"]');
        const qtyEl = document.querySelector('[data-material-qty="mulch_material"]');
        const costEl = document.querySelector('[data-material-cost="mulch_material"]');
        const labelEl = document.querySelector('[data-material-label="mulch_material"]');
        const hintEl = document.getElementById('materialPreviewHint');
        const defaultCost = {{ $defaultMulchCost }};

        const parseNumber = (value) => {
            const num = parseFloat(value);
            return Number.isFinite(num) ? num : null;
        };

        const formatQty = (value) => {
            if (value === null || Number.isNaN(value)) {
                return '—';
            }
            return Number(value).toFixed(2);
        };

        const setHint = (hasValues) => {
            if (!hintEl) {
                return;
            }
            const emptyText = hintEl.dataset.emptyMessage || '';
            const filledText = hintEl.dataset.filledMessage || '';
            hintEl.textContent = hasValues ? filledText : emptyText;
            hintEl.classList.toggle('text-gray-600', hasValues);
            hintEl.classList.toggle('text-gray-500', !hasValues);
        };

        const recalc = () => {
            const area = parseNumber(areaInput ? areaInput.value : null);
            const depth = parseNumber(depthInput ? depthInput.value : null);
            const hasValues = area !== null && area > 0 && depth !== null && depth > 0;
            setHint(Boolean(hasValues));

            const yards = hasValues ? (area * (depth / 12)) / 27 : null;

            if (qtyEl) {
                qtyEl.textContent = formatQty(yards);
            }

            if (costEl) {
                costEl.textContent = `$${defaultCost.toFixed(2)}`;
            }

            if (labelEl && mulchTypeSelect) {
                const selected = mulchTypeSelect.value || labelEl.dataset.originalLabel || labelEl.textContent;
                labelEl.textContent = selected || 'Mulch';
            }
        };

        if (labelEl) {
            labelEl.dataset.originalLabel = labelEl.textContent;
        }

        [areaInput, depthInput].forEach((input) => {
            if (!input) return;
            input.addEventListener('input', recalc);
        });

        if (mulchTypeSelect) {
            mulchTypeSelect.addEventListener('change', recalc);
        }

        recalc();
    });
</script>
@endpush
@push('scripts')
<script>
    function calculateMulchYards() {
        const areaInput = document.querySelector('input[name="area_sqft"]');
        const depthInput = document.querySelector('input[name="depth_inches"]');
        const outputDiv = document.getElementById('mulchEstimate');
        const outputValue = document.getElementById('mulchYards');

        const area = parseFloat(areaInput.value);
        const depth = parseFloat(depthInput.value);

        if (!isNaN(area) && area > 0 && !isNaN(depth) && depth > 0) {
           const mulchYards = Math.ceil((area * (depth / 12)) / 27);
            outputValue.textContent = mulchYards;
            outputDiv.classList.remove('hidden');
        } else {
            outputDiv.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const areaInput = document.querySelector('input[name="area_sqft"]');
        const depthInput = document.querySelector('input[name="depth_inches"]');

        areaInput.addEventListener('input', calculateMulchYards);
        depthInput.addEventListener('input', calculateMulchYards);

        // Recalculate on load if values are already present
        calculateMulchYards();
    });
</script>
@endpush
