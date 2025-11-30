
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

    $materialCards = [
        [
            'key' => 'wall_blocks',
            'label' => 'Wall Blocks',
            'unit' => 'blocks',
            'qty' => $blockCountEstimate,
            'qty_is_int' => true,
            'unit_cost' => $blockUnitCostDefault,
            'description' => 'Coverage ~' . number_format($blockCoverage, 2) . ' sqft/block',
        ],
        [
            'key' => 'capstones',
            'label' => 'Capstones',
            'unit' => 'caps',
            'qty' => $capCountEstimate,
            'qty_is_int' => true,
            'unit_cost' => $capUnitCostDefault,
            'description' => 'Qty mirrors wall length',
        ],
        [
            'key' => 'drain_pipe',
            'label' => 'Drain Pipe',
            'unit' => 'lf',
            'qty' => $lengthValue,
            'qty_is_int' => false,
            'unit_cost' => $pipeUnitCostDefault,
            'description' => 'Full wall length for pipe runs',
        ],
        [
            'key' => 'gravel',
            'label' => '#57 Gravel',
            'unit' => 'tons',
            'qty' => $gravelTonsEstimate,
            'qty_is_int' => true,
            'unit_cost' => $gravelUnitCostDefault,
            'description' => 'Backfill 1.5ft thick minus the first 0.5ft',
        ],
        [
            'key' => 'topsoil',
            'label' => 'Topsoil',
            'unit' => 'cu yd',
            'qty' => $topsoilYardsEstimate,
            'qty_is_int' => true,
            'unit_cost' => $topsoilUnitCostDefault,
            'description' => '0.5ft cap at 1.5ft depth',
        ],
        [
            'key' => 'fabric',
            'label' => 'Underlayment Fabric',
            'unit' => 'sqft',
            'qty' => $fabricAreaEstimate,
            'qty_is_int' => false,
            'unit_cost' => $fabricUnitCostDefault,
            'description' => 'Covers both faces of the wall',
        ],
        [
            'key' => 'geogrid',
            'label' => 'Geogrid',
            'unit' => 'sqft',
            'qty' => $geogridAreaEstimate,
            'qty_is_int' => false,
            'unit_cost' => $geogridUnitCostDefault,
            'description' => 'Only when geogrid is enabled',
        ],
        [
            'key' => 'adhesive',
            'label' => 'Adhesive Tubes',
            'unit' => 'tubes',
            'qty' => $adhesiveTubeEstimate,
            'qty_is_int' => true,
            'unit_cost' => $adhesiveUnitCostDefault,
            'description' => '1 tube per 20 capstones',
        ],
    ];

    $customMaterials = old('custom_materials', $formData['custom_materials'] ?? []);
@endphp

@section('content')
@if ($errors->any())
    <div class="max-w-4xl mx-auto mb-6">
        <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded">
            <ul class="list-disc pl-5 space-y-1 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="max-w-4xl mx-auto py-10 space-y-8">
    @include('calculators.partials.form_header', [
        'title' => $editMode ? 'Edit Retaining Wall Calculation' : 'Retaining Wall Calculator',
        'subtitle' => 'Matches the planting/paver workflow: enter inputs, preview materials instantly, add overrides, then calculate.',
    ])

    @if(($mode ?? null) !== 'template' && $siteVisit)
        @include('calculators.partials.client_info', ['siteVisit' => $siteVisit])
    @else
        <div class="bg-white p-4 rounded border mb-6">
            <p class="text-sm text-gray-700">Template Mode — build a Retaining Wall template without a site visit.</p>
            @if(!empty($estimateId))
                <p class="text-sm text-gray-500">Target Estimate: #{{ $estimateId }}</p>
            @endif
        </div>
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

        {{-- Crew & Logistics --}}
        <div>
            <h2 class="text-xl font-semibold mb-2">Crew & Logistics</h2>
            @include('calculators.partials.overhead_inputs')
        </div>

        {{-- Wall Inputs --}}
        <div>
            @php($wallBadge = new \Illuminate\Support\HtmlString('<span id="wallAreaBadge" class="text-sm font-medium '.($areaSqft ? 'text-gray-700' : 'text-gray-500').'" data-empty-message="Enter length + height to unlock wall area." data-prefix="Wall Area: ">'.($areaSqft ? 'Wall Area: '.number_format($areaSqft, 2).' sqft' : 'Enter length + height to unlock wall area.').'</span>'))
            @include('calculators.partials.section_heading', [
                'title' => 'Wall Inputs',
                'right' => $wallBadge,
            ])

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border rounded-lg p-4 bg-white shadow-sm">
                    <label class="block font-semibold mb-2">Length (ft)</label>
                    <input type="number" step="0.1" name="length" class="form-input w-full" value="{{ $lengthValue }}" required>
                    <p class="text-xs text-gray-500 mt-2">Used for block, pipe, gravel, and topsoil calculations.</p>
                </div>

                <div class="border rounded-lg p-4 bg-white shadow-sm">
                    <label class="block font-semibold mb-2">Height (ft)</label>
                    <input type="number" step="0.1" name="height" class="form-input w-full" value="{{ $heightValue }}" required>
                    <p class="text-xs text-gray-500 mt-2">Impacts block count, fabric, geogrid, and gravel.</p>
                </div>

                <div class="border rounded-lg p-4 bg-white shadow-sm">
                    <label class="block font-semibold mb-2">Block System</label>
                    <select name="block_system" id="block_system" class="form-select w-full" required>
                        <option value="standard" {{ $blockSystemValue === 'standard' ? 'selected' : '' }}>Standard</option>
                        <option value="allan_block" {{ $blockSystemValue === 'allan_block' ? 'selected' : '' }}>Allan Block</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-2">Choosing Allan Block reveals additional component inputs.</p>
                </div>

                <div class="border rounded-lg p-4 bg-white shadow-sm">
                    <label class="block font-semibold mb-2">Block Brand</label>
                    <select name="block_brand" class="form-select w-full" required>
                        <option value="">Choose brand</option>
                        <option value="belgard" {{ $blockBrandValue === 'belgard' ? 'selected' : '' }}>Belgard</option>
                        <option value="techo" {{ $blockBrandValue === 'techo' ? 'selected' : '' }}>Techo-Bloc</option>
                        <option value="allan_block" {{ $blockBrandValue === 'allan_block' ? 'selected' : '' }}>Allan Block</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-2">Brand toggles coverage + pricing assumptions.</p>
                </div>

                <div class="border rounded-lg p-4 bg-white shadow-sm">
                    <label class="block font-semibold mb-2">Equipment</label>
                    <select name="equipment" class="form-select w-full" required>
                        <option value="manual" {{ $equipmentValue === 'manual' ? 'selected' : '' }}>Manual</option>
                        <option value="skid_steer" {{ $equipmentValue === 'skid_steer' ? 'selected' : '' }}>Skid Steer</option>
                        <option value="excavator" {{ $equipmentValue === 'excavator' ? 'selected' : '' }}>Excavator</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-2">Impacts labor production rates.</p>
                </div>

                <div class="border rounded-lg p-4 bg-white shadow-sm space-y-3">
                    <label class="block font-semibold">Accessories</label>
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="use_capstones" value="1" class="form-checkbox" {{ $includeCaps ? 'checked' : '' }}>
                        <span>Include capstones</span>
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="include_geogrid" value="1" class="form-checkbox" {{ $includeGeogrid ? 'checked' : '' }}>
                        <span>Include geogrid (auto at =4 ft)</span>
                    </label>
                </div>
            </div>

            <div id="allanBlockFields" class="mt-6 border rounded-lg p-4 bg-white shadow-sm {{ $blockSystemValue === 'allan_block' ? '' : 'hidden' }}">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">Allan Block Details</h3>
                    <span class="text-sm text-gray-500">Optional but boosts accuracy</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Straight Wall Length (ft)</label>
                        <input type="number" step="0.1" name="ab_straight_length" value="{{ old('ab_straight_length', $formData['ab_straight_length'] ?? '') }}" class="form-input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Straight Wall Height (ft)</label>
                        <input type="number" step="0.1" name="ab_straight_height" value="{{ old('ab_straight_height', $formData['ab_straight_height'] ?? '') }}" class="form-input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Curved Wall Length (ft)</label>
                        <input type="number" step="0.1" name="ab_curved_length" value="{{ old('ab_curved_length', $formData['ab_curved_length'] ?? '') }}" class="form-input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Curved Wall Height (ft)</label>
                        <input type="number" step="0.1" name="ab_curved_height" value="{{ old('ab_curved_height', $formData['ab_curved_height'] ?? '') }}" class="form-input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Step Count</label>
                        <input type="number" name="ab_step_count" value="{{ old('ab_step_count', $formData['ab_step_count'] ?? '') }}" class="form-input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Column Count</label>
                        <input type="number" name="ab_column_count" value="{{ old('ab_column_count', $formData['ab_column_count'] ?? '') }}" class="form-input w-full">
                    </div>
                </div>
            </div>
        </div>
        {{-- Materials Preview --}}
        <div>
            <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between mb-3">
                <div>
                    <h2 class="text-xl font-semibold">Materials & Pricing Preview</h2>
                    <p class="text-gray-500 text-sm">Identical card grid-see quantities + default unit cost immediately.</p>
                </div>
                <span
                    id="materialPreviewHint"
                    class="text-sm {{ $areaSqft ? 'text-gray-700' : 'text-gray-500' }}"
                    data-empty-message="Enter dimensions above to unlock quantities."
                    data-filled-message="Quantities update automatically while you type."
                >
                    {{ $areaSqft ? 'Quantities update automatically while you type.' : 'Enter dimensions above to unlock quantities.' }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach ($materialCards as $card)
                    <div class="border rounded-lg p-4 bg-white shadow-sm flex flex-col">
                        <div class="flex items-center justify-between">
                            <p class="font-semibold">{{ $card['label'] }}</p>
                            <span class="text-sm text-gray-500">{{ $card['unit'] }}</span>
                        </div>

                        <div class="mt-4">
                            <p class="text-xs uppercase tracking-wide text-gray-500">Qty Estimate</p>
                            <p class="text-2xl font-bold" data-material-qty="{{ \Illuminate\Support\Str::slug($card['key'], '_') }}">
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
                            <p class="text-lg font-semibold" data-material-cost="{{ \Illuminate\Support\Str::slug($card['key'], '_') }}">
                                @if(!is_null($card['unit_cost']))
                                    ${{ number_format($card['unit_cost'], 2) }}
                                @else
                                    --
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
                    <p class="text-gray-500 text-sm">Log materials not auto-calculated (lighting, specialty drain items, etc.).</p>
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
                    'name' => 'override_block_cost',
                    'label' => 'Wall Block ($/block)',
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                    'value' => $formData['override_block_cost'] ?? '',
                    'width' => 'half',
                ],
                [
                    'name' => 'override_capstone_cost',
                    'label' => 'Capstone ($/cap)',
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                    'value' => $formData['override_capstone_cost'] ?? '',
                    'width' => 'half',
                ],
                [
                    'name' => 'override_pipe_cost',
                    'label' => 'Drain Pipe ($/ft)',
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                    'value' => $formData['override_pipe_cost'] ?? '',
                    'width' => 'half',
                ],
                [
                    'name' => 'override_gravel_cost',
                    'label' => '#57 Gravel ($/ton)',
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                    'value' => $formData['override_gravel_cost'] ?? '',
                    'width' => 'half',
                ],
                [
                    'name' => 'override_topsoil_cost',
                    'label' => 'Topsoil ($/yd^3)',
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                    'value' => $formData['override_topsoil_cost'] ?? '',
                    'width' => 'half',
                ],
                [
                    'name' => 'override_fabric_cost',
                    'label' => 'Underlayment Fabric ($/ft^2)',
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                    'value' => $formData['override_fabric_cost'] ?? '',
                    'width' => 'half',
                ],
                [
                    'name' => 'override_geogrid_cost',
                    'label' => 'Geogrid ($/ft^2)',
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                    'value' => $formData['override_geogrid_cost'] ?? '',
                    'width' => 'half',
                ],
                [
                    'name' => 'override_adhesive_cost',
                    'label' => 'Adhesive ($/tube)',
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                    'value' => $formData['override_adhesive_cost'] ?? '',
                    'width' => 'half',
                ],
            ],
        ])

        {{-- Job Notes --}}
        <div>
            <label class="block text-sm font-semibold mb-2">Job Notes</label>
            <textarea name="job_notes" rows="3" class="form-textarea w-full" placeholder="Anything unique about this wall or site?">{{ old('job_notes', $formData['job_notes'] ?? '') }}</textarea>
        </div>

        {{-- Submit --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-6">
            @if(($mode ?? null) === 'template')
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 w-full">
                    <input type="text" name="template_name" class="form-input w-full sm:w-72" placeholder="Template name (e.g., 3ft retaining wall)" value="{{ old('template_name') }}">
                    <select name="template_scope" class="form-select w-full sm:w-48">
                        <option value="global" {{ old('template_scope')==='global' ? 'selected' : '' }}>Global</option>
                        <option value="client" {{ old('template_scope')==='client' ? 'selected' : '' }}>This Client</option>
                        <option value="property" {{ old('template_scope')==='property' ? 'selected' : '' }}>This Property</option>
                    </select>
                    <button type="submit" class="btn btn-secondary">💾 Save Template</button>
                </div>
            @else
                <button type="submit" class="btn btn-secondary">
                    {{ $editMode ? 'Recalculate Wall' : 'Calculate Wall Estimate' }}
                </button>
                <a href="{{ route('clients.show', $siteVisit->client_id) }}" class="btn btn-muted">
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
        const heightInput = document.querySelector('input[name="height"]');
        const blockBrandSelect = document.querySelector('select[name="block_brand"]');
        const capstoneCheckbox = document.querySelector('input[name="use_capstones"]');
        const geogridCheckbox = document.querySelector('input[name="include_geogrid"]');
        const blockSystemSelect = document.getElementById('block_system');
        const allanBlockFields = document.getElementById('allanBlockFields');

        const overrideInputs = {
            block: document.querySelector('input[name="override_block_cost"]'),
            cap: document.querySelector('input[name="override_capstone_cost"]'),
            pipe: document.querySelector('input[name="override_pipe_cost"]'),
            gravel: document.querySelector('input[name="override_gravel_cost"]'),
            topsoil: document.querySelector('input[name="override_topsoil_cost"]'),
            fabric: document.querySelector('input[name="override_fabric_cost"]'),
            geogrid: document.querySelector('input[name="override_geogrid_cost"]'),
            adhesive: document.querySelector('input[name="override_adhesive_cost"]'),
        };

        const qtyEls = {
            wall_blocks: document.querySelector('[data-material-qty="wall_blocks"]'),
            capstones: document.querySelector('[data-material-qty="capstones"]'),
            drain_pipe: document.querySelector('[data-material-qty="drain_pipe"]'),
            gravel: document.querySelector('[data-material-qty="gravel"]'),
            topsoil: document.querySelector('[data-material-qty="topsoil"]'),
            fabric: document.querySelector('[data-material-qty="fabric"]'),
            geogrid: document.querySelector('[data-material-qty="geogrid"]'),
            adhesive: document.querySelector('[data-material-qty="adhesive"]'),
        };

        const costEls = {
            wall_blocks: document.querySelector('[data-material-cost="wall_blocks"]'),
            capstones: document.querySelector('[data-material-cost="capstones"]'),
            drain_pipe: document.querySelector('[data-material-cost="drain_pipe"]'),
            gravel: document.querySelector('[data-material-cost="gravel"]'),
            topsoil: document.querySelector('[data-material-cost="topsoil"]'),
            fabric: document.querySelector('[data-material-cost="fabric"]'),
            geogrid: document.querySelector('[data-material-cost="geogrid"]'),
            adhesive: document.querySelector('[data-material-cost="adhesive"]'),
        };

        const areaBadge = document.getElementById('wallAreaBadge');
        const materialHint = document.getElementById('materialPreviewHint');
        const customRowsContainer = document.getElementById('customMaterialRows');
        const customTemplate = document.getElementById('customMaterialTemplate');
        const addCustomMaterialButton = document.getElementById('addCustomMaterial');

        const defaults = {
            blockUnitCost: 11,
            capUnitCost: 18,
            pipeUnitCost: 2,
            gravelUnitCost: 85,
            topsoilUnitCost: 5,
            fabricUnitCost: 0.3,
            geogridUnitCost: 1.5,
            adhesiveUnitCost: 8,
        };

        const getBlockCoverage = (brand) => (brand === 'belgard' ? 0.67 : 0.65);

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

        const formatCurrency = (value) => `$${Number(value).toFixed(2)}`;

        const resolveCost = (input, fallback) => {
            const parsed = input ? parseNumber(input.value) : null;
            return parsed ?? fallback;
        };

        const setBadgeText = (el, text, isFilled) => {
            if (!el) return;
            el.textContent = text;
            el.classList.toggle('text-gray-700', isFilled);
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

        const toggleAllanFields = () => {
            if (!blockSystemSelect || !allanBlockFields) return;
            allanBlockFields.classList.toggle('hidden', blockSystemSelect.value !== 'allan_block');
        };

        if (blockSystemSelect) {
            blockSystemSelect.addEventListener('change', toggleAllanFields);
            toggleAllanFields();
        }

        const recalc = () => {
            const length = lengthInput ? parseNumber(lengthInput.value) : null;
            const height = heightInput ? parseNumber(heightInput.value) : null;
            const area = length && height ? length * height : null;

            if (areaBadge) {
                const emptyMessage = areaBadge.dataset.emptyMessage || 'Enter length + height to unlock wall area.';
                const prefix = areaBadge.dataset.prefix || 'Wall Area: ';
                setBadgeText(areaBadge, area ? `${prefix}${area.toFixed(2)} sqft` : emptyMessage, Boolean(area));
            }

        if (materialHint) {
            const emptyMsg = materialHint.dataset.emptyMessage || 'Enter dimensions above to unlock quantities.';
            const filledMsg = materialHint.dataset.filledMessage || 'Quantities update automatically while you type.';
            setBadgeText(materialHint, area ? filledMsg : emptyMsg, Boolean(area));
        }

            const coverage = blockBrandSelect ? getBlockCoverage(blockBrandSelect.value) : getBlockCoverage('');
            const blocks = area ? Math.ceil(area / coverage) : null;
            const capsIncluded = capstoneCheckbox ? capstoneCheckbox.checked : false;
            const caps = capsIncluded && length ? Math.ceil(length) : 0;
            const pipe = length ?? null;
            const gravelVolumeCF = length && height ? length * Math.max(height - 0.5, 0) * 1.5 : null;
            const gravelTons = gravelVolumeCF ? Math.ceil(gravelVolumeCF / 21.6) : null;
            const topsoilVolume = length ? length * 0.5 * 1.5 : null;
            const topsoilYards = topsoilVolume ? Math.ceil(topsoilVolume / 27) : null;
            const fabricArea = length && height ? (length * height * 2) : null;
            const geogridEnabled = geogridCheckbox ? geogridCheckbox.checked && height && height >= 4 : false;
            const geogridLayers = geogridEnabled ? Math.floor(height / 2) : 0;
            const geogridLF = geogridLayers && length ? length * geogridLayers : 0;
            const geogridArea = geogridLF && height ? geogridLF * height : 0;
            const adhesiveTubes = caps > 0 ? Math.ceil(caps / 20) : 0;

            if (qtyEls.wall_blocks) qtyEls.wall_blocks.textContent = formatQty(blocks, true);
            if (qtyEls.capstones) qtyEls.capstones.textContent = formatQty(caps, true);
            if (qtyEls.drain_pipe) qtyEls.drain_pipe.textContent = formatQty(pipe);
            if (qtyEls.gravel) qtyEls.gravel.textContent = formatQty(gravelTons, true);
            if (qtyEls.topsoil) qtyEls.topsoil.textContent = formatQty(topsoilYards, true);
            if (qtyEls.fabric) qtyEls.fabric.textContent = formatQty(fabricArea);
            if (qtyEls.geogrid) qtyEls.geogrid.textContent = formatQty(geogridArea);
            if (qtyEls.adhesive) qtyEls.adhesive.textContent = formatQty(adhesiveTubes, true);

            if (costEls.wall_blocks) costEls.wall_blocks.textContent = formatCurrency(resolveCost(overrideInputs.block, defaults.blockUnitCost));
            if (costEls.capstones) costEls.capstones.textContent = formatCurrency(resolveCost(overrideInputs.cap, defaults.capUnitCost));
            if (costEls.drain_pipe) costEls.drain_pipe.textContent = formatCurrency(resolveCost(overrideInputs.pipe, defaults.pipeUnitCost));
            if (costEls.gravel) costEls.gravel.textContent = formatCurrency(resolveCost(overrideInputs.gravel, defaults.gravelUnitCost));
            if (costEls.topsoil) costEls.topsoil.textContent = formatCurrency(resolveCost(overrideInputs.topsoil, defaults.topsoilUnitCost));
            if (costEls.fabric) costEls.fabric.textContent = formatCurrency(resolveCost(overrideInputs.fabric, defaults.fabricUnitCost));
            if (costEls.geogrid) {
                costEls.geogrid.textContent = geogridEnabled
                    ? formatCurrency(resolveCost(overrideInputs.geogrid, defaults.geogridUnitCost))
                    : '--';
            }
            if (costEls.adhesive) costEls.adhesive.textContent = formatCurrency(resolveCost(overrideInputs.adhesive, defaults.adhesiveUnitCost));
        };

        recalc();

        [lengthInput, heightInput].forEach((input) => {
            if (!input) return;
            input.addEventListener('input', recalc);
        });

        if (blockBrandSelect) {
            blockBrandSelect.addEventListener('change', recalc);
        }

        if (capstoneCheckbox) {
            capstoneCheckbox.addEventListener('change', recalc);
        }

        if (geogridCheckbox) {
            geogridCheckbox.addEventListener('change', recalc);
        }

        Object.values(overrideInputs).forEach((input) => {
            if (!input) return;
            input.addEventListener('input', recalc);
        });
    });
</script>
@endpush


