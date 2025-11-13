@extends('layouts.sidebar')

@php
    $customMaterials = old('custom_materials', $formData['custom_materials'] ?? []);
    $areaValue = old('area_sqft', $formData['area_sqft'] ?? null);
    $strawTypes = ['Pine Needles', 'Wheat Straw'];
    $selectedStrawType = old('mulch_type', $formData['mulch_type'] ?? '');
    $storedMaterials = $formData['materials'] ?? [];
    $primaryMaterialName = $selectedStrawType ?: (collect($storedMaterials)->keys()->first() ?? 'Pine Needles');
    $primaryMaterial = $storedMaterials[$primaryMaterialName] ?? null;
    $defaultStrawCost = 7;
    $prefillCost = data_get($primaryMaterial, 'unit_cost', $defaultStrawCost);
    $prefillQty = data_get($primaryMaterial, 'qty');
    $strawBalesPreview = $areaValue ? round($areaValue / 50, 0) : null;
    $materialCards = [
        [
            'key' => 'straw_material',
            'label' => $primaryMaterialName ?: 'Pine Needles',
            'unit' => 'bales',
            'qty' => $prefillQty ?? $strawBalesPreview,
            'qty_is_int' => true,
            'unit_cost' => $prefillCost,
            'description' => 'Approx. 1 bale per 50 sqft.',
        ],
    ];
@endphp

@section('content')
<div class="max-w-4xl mx-auto py-10">
    @include('calculators.partials.form_header', [
        'title' => $editMode ? '✏️ Edit Pine Needle Data' : '🌿 Pine Needle Calculator',
    ])

    @if(($mode ?? null) !== 'template' && ($siteVisit ?? null))
        @include('calculators.partials.client_info', ['siteVisit' => $siteVisit])
    @else
        <div class="bg-white p-4 rounded border mb-6">
            <p class="text-sm text-gray-700">Template Mode — build a Pine Needles template without a site visit.</p>
            @if(!empty($estimateId))
                <p class="text-sm text-gray-500">Target Estimate: #{{ $estimateId }}</p>
            @endif
        </div>
    @endif

    <form method="POST" action="{{ route('calculators.pine_needles.calculate') }}">
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

        {{-- Crew & Logistics --}}
        <div class="mb-6">
            @include('calculators.partials.section_heading', ['title' => 'Crew & Logistics'])
            @include('calculators.partials.overhead_inputs')
        </div>

        {{-- Straw Area --}}
        <div class="mb-6">
            @include('calculators.partials.section_heading', ['title' => 'Straw Coverage'])

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
            </div>
        </div>

        {{-- Straw Type Dropdown --}}
        <div class="mb-6">
            <label class="block font-semibold mb-1">Straw Type</label>
            <select name="mulch_type" class="form-select w-full">
                <option value="" disabled {{ empty($selectedStrawType) ? 'selected' : '' }}>Select a straw type</option>
                @foreach ($strawTypes as $type)
                    <option value="{{ $type }}" {{ $selectedStrawType === $type ? 'selected' : '' }}>
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
                    <p class="text-gray-500 text-sm">Aligns with the other calculators—live bale counts + pricing.</p>
                </div>
                <span
                    id="materialPreviewHint"
                    class="text-sm {{ $areaValue ? 'text-gray-600' : 'text-gray-500' }}"
                    data-empty-message="Enter square footage to unlock quantities."
                    data-filled-message="Quantities update automatically while you type."
                >
                    {{ $areaValue ? 'Quantities update automatically while you type.' : 'Enter square footage to unlock quantities.' }}
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
                                    {{ number_format($card['qty']) }}
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
            <h2 class="text-xl font-semibold mb-2">Pine Needle Tasks</h2>

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
                    <input type="text" name="template_name" class="form-input w-full sm:w-72" placeholder="Template name (e.g., Front beds straw)" value="{{ old('template_name') }}">
                    <select name="template_scope" class="form-select w-full sm:w-48">
                        <option value="global" {{ old('template_scope')==='global' ? 'selected' : '' }}>Global</option>
                        <option value="client" {{ old('template_scope')==='client' ? 'selected' : '' }}>This Client</option>
                        <option value="property" {{ old('template_scope')==='property' ? 'selected' : '' }}>This Property</option>
                    </select>
                    <button type="submit" class="btn btn-secondary">💾 Save Template</button>
                </div>
            @else
                <button type="submit" class="btn btn-secondary">
                    {{ $editMode ? '🔄 Recalculate Pine Needle Data' : '🧮 Calculate Pine Needle Data' }}
                </button>

                <a href="{{ route('clients.show', $siteVisitId) }}" class="btn btn-muted">
                    🔙 Back to Client
                </a>
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
        const strawTypeSelect = document.querySelector('select[name="mulch_type"]');
        const qtyEl = document.querySelector('[data-material-qty="straw_material"]');
        const costEl = document.querySelector('[data-material-cost="straw_material"]');
        const labelEl = document.querySelector('[data-material-label="straw_material"]');
        const hintEl = document.getElementById('materialPreviewHint');
        const defaultCost = {{ $defaultStrawCost }};

        const parseNumber = (value) => {
            const num = parseFloat(value);
            return Number.isFinite(num) ? num : null;
        };

        const formatQty = (value) => {
            if (value === null || Number.isNaN(value)) {
                return '—';
            }
            return Math.max(0, Math.round(value)).toLocaleString();
        };

        const setHint = (hasValue) => {
            if (!hintEl) return;
            const emptyText = hintEl.dataset.emptyMessage || '';
            const filledText = hintEl.dataset.filledMessage || '';
            hintEl.textContent = hasValue ? filledText : emptyText;
            hintEl.classList.toggle('text-gray-600', hasValue);
            hintEl.classList.toggle('text-gray-500', !hasValue);
        };

        const recalc = () => {
            const area = parseNumber(areaInput ? areaInput.value : null);
            const hasValue = area !== null && area > 0;
            setHint(Boolean(hasValue));

            const bales = hasValue ? Math.ceil(area / 50) : null;
            if (qtyEl) {
                qtyEl.textContent = formatQty(bales);
            }
            if (costEl) {
                costEl.textContent = `$${defaultCost.toFixed(2)}`;
            }
            if (labelEl && strawTypeSelect) {
                const selected = strawTypeSelect.value || labelEl.dataset.originalLabel || labelEl.textContent;
                labelEl.textContent = selected || 'Pine Needles';
            }
        };

        if (labelEl) {
            labelEl.dataset.originalLabel = labelEl.textContent;
        }

        if (areaInput) {
            areaInput.addEventListener('input', recalc);
        }

        if (strawTypeSelect) {
            strawTypeSelect.addEventListener('change', recalc);
        }

        recalc();
    });
</script>
@endpush
