@extends('layouts.sidebar')

@section('content')
<div class="max-w-4xl mx-auto py-10">
    @include('calculators.partials.form_header', [
        'title' => $editMode ? '✏️ Edit Synthetic Turf Estimate' : '🏟️ Synthetic Turf Calculator',
    ])

    @if(($mode ?? null) !== 'template' && $siteVisit)
        @include('calculators.partials.client_info', ['siteVisit' => $siteVisit])
    @else
        <div class="bg-white p-4 rounded border mb-6">
            <p class="text-sm text-gray-700">Template Mode — build a Synthetic Turf template without a site visit.</p>
            @if(!empty($estimateId))
                <p class="text-sm text-gray-500">Target Estimate: #{{ $estimateId }}</p>
            @endif
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

        <div class="mb-6 bg-white rounded border p-4">
            @include('calculators.partials.section_heading', ['title' => 'Crew & Logistics'])
            @include('calculators.partials.overhead_inputs')
        </div>

        <div class="mb-6 bg-white rounded border p-4">
            @include('calculators.partials.section_heading', ['title' => 'Project & Base Parameters'])

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block font-semibold mb-1">Project Square Footage</label>
                    <input type="number"
                           name="area_sqft"
                           min="1"
                           step="any"
                           class="form-input w-full"
                           value="{{ old('area_sqft', $formData['area_sqft'] ?? '') }}"
                           required>
                </div>
                <div>
                    <label class="block font-semibold mb-1">Perimeter / Edging (Linear Feet)</label>
                    <input type="number"
                           name="edging_linear_ft"
                           min="0"
                           step="any"
                           class="form-input w-full"
                           value="{{ old('edging_linear_ft', $formData['edging_linear_ft'] ?? '') }}"
                           required>
                    <p class="text-sm text-gray-500 mt-1">Used to calculate 20' edging boards.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block font-semibold mb-1">Excavation Depth (in)</label>
                    <input type="number" name="excavation_depth_in" min="0" step="0.25" class="form-input w-full" value="{{ old('excavation_depth_in', $formData['excavation_depth_in'] ?? 3) }}">
                    <p class="text-xs text-gray-500 mt-1">Used to auto-calc excavation volume (cubic yards) for equipment methods.</p>
                </div>
                <div>
                    <label class="block font-semibold mb-1">ABC Depth (in)</label>
                    <input type="number" name="abc_depth_in" min="0" step="0.25" class="form-input w-full" value="{{ old('abc_depth_in', $formData['abc_depth_in'] ?? '') }}">
                    <p class="text-xs text-gray-500 mt-1">Set depth to include ABC base material (in cubic yards).</p>
                </div>
                <div>
                    <label class="block font-semibold mb-1">Rock Dust Depth (in)</label>
                    <input type="number" name="rock_dust_depth_in" min="0" step="0.25" class="form-input w-full" value="{{ old('rock_dust_depth_in', $formData['rock_dust_depth_in'] ?? '') }}">
                    <p class="text-xs text-gray-500 mt-1">Set depth to include Rock Dust material (in cubic yards).</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-2">
                <div class="md:col-span-1">
                    <label class="block font-semibold mb-1">Tamper Rental</label>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="rent_tamper" id="rent_tamper" value="1" class="form-checkbox" {{ old('rent_tamper') ? 'checked' : '' }}>
                        <input type="number" name="tamper_days" min="1" step="1" class="form-input w-24" value="{{ old('tamper_days', 1) }}">
                        <span class="text-xs text-gray-500">days @ $125/day</span>
                    </div>
                </div>
                <div class="md:col-span-2 flex items-end">
                    <p id="excavationVolPreview" class="text-xs text-gray-500">&nbsp;</p>
                </div>
            </div>
        </div>

        <div class="mb-6 bg-white rounded border p-4">
            @include('calculators.partials.section_heading', ['title' => 'Synthetic Turf Selection'])
            @php
                $turfOptions = config('syn_turf.materials.turf_tiers', []);
                $turfGrade = old('turf_grade', $formData['turf_grade'] ?? 'better');
            @endphp
            <label class="block font-semibold mb-1">Turf Tier</label>
            <select name="turf_grade" class="form-select w-full">
                @foreach ($turfOptions as $key => $tier)
                    <option value="{{ $key }}" data-unit-cost="{{ $tier['unit_cost'] ?? 0 }}" data-label="{{ $tier['label'] ?? ucfirst($key) }}"
                        {{ $turfGrade === $key ? 'selected' : '' }}>
                        {{ $tier['label'] ?? ucfirst($key) }} (${{ number_format($tier['unit_cost'] ?? 0, 2) }} / sq ft)
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-6 bg-white rounded border p-4" id="materialsEditSection">
            @include('calculators.partials.section_heading', ['title' => 'Materials (Editable)'])
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
                    <div class="border rounded p-3 bg-gray-50" data-material-row data-key="{{ $key }}">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-semibold">{{ $row['label'] }}</div>
                                <div class="text-xs text-gray-500">Key: {{ $key }}</div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div>
                                    <label class="block text-xs text-gray-500">Qty</label>
                                    <input type="number" step="0.01" min="0" name="materials_edit[{{ $key }}][qty]" class="form-input w-28" value="{{ $qty }}" data-material-qty>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500">Unit Cost ($)</label>
                                    <input type="number" step="0.01" min="0" name="materials_edit[{{ $key }}][unit_cost]" class="form-input w-28" value="{{ $cost }}" data-material-cost>
                                </div>
                                <div class="w-36 text-right">
                                    <div class="text-xs text-gray-500">Line Total</div>
                                    <div class="font-semibold" data-material-line>{{ $line ? '$'.$line : '—' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-3 text-right">
                <span class="text-sm text-gray-600 mr-2">Materials Subtotal:</span>
                <span class="font-semibold" id="materialsEditSubtotal">—</span>
            </div>
        </div>

        <div class="mb-6 bg-white rounded border p-4">
            <h2 class="text-xl font-semibold mb-2">Synthetic Turf Tasks</h2>

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

            <div class="mb-4 p-4 bg-white rounded border">
                <label class="block font-semibold mb-2">Excavation Method</label>
                <div class="flex flex-col sm:flex-row sm:items-center gap-4 text-sm">
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" name="excavation_method" value="generic" class="form-radio" {{ $selectedExcavation === 'generic' ? 'checked' : '' }}>
                        <span>Generic</span>
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" name="excavation_method" value="skid" class="form-radio" {{ $selectedExcavation === 'skid' ? 'checked' : '' }}>
                        <span>Skid Steer</span>
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" name="excavation_method" value="mini" class="form-radio" {{ $selectedExcavation === 'mini' ? 'checked' : '' }}>
                        <span>Mini Skid</span>
                    </label>
                </div>
                <p class="text-xs text-gray-500 mt-2">Pick one excavation method. The form will enable only the matching excavation task input.</p>
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
                        <div class="border p-4 rounded bg-gray-50 {{ $isExcavation ? 'excavation-card' : '' }}" @if($isExcavation) data-excavation-task="1" data-excavation-key="{{ $key }}" @endif>
                            <label class="block font-semibold mb-1">{{ $label }} ({{ $rate->unit }})</label>
                            <input
                                type="number"
                                name="tasks[{{ $key }}][qty]"
                                step="any"
                                min="0"
                                class="form-input w-full"
                                placeholder="Enter quantity"
                                value="{{ $value }}"
                            >
                            <p class="text-sm text-gray-500">
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
                    <div class="border p-4 rounded bg-gray-50 {{ $isExcavation ? 'excavation-card' : '' }}" @if($isExcavation) data-excavation-task="1" data-excavation-key="{{ $key }}" @endif>
                        <label class="block font-semibold mb-1">{{ $label }} ({{ $rate->unit }})</label>
                        <input
                            type="number"
                            name="tasks[{{ $key }}][qty]"
                            step="any"
                            min="0"
                            class="form-input w-full"
                            placeholder="Enter quantity"
                            value="{{ $value }}"
                        >
                        <p class="text-sm text-gray-500">
                            Rate: {{ number_format($rate->rate, 4) }} hrs/{{ $rate->unit }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-6">
            @if(($mode ?? null) === 'template')
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 w-full">
                    <input type="text" name="template_name" class="form-input w-full sm:w-72" placeholder="Template name (e.g., Small backyard turf)" value="{{ old('template_name') }}">
                    <select name="template_scope" class="form-select w-full sm:w-48">
                        <option value="global" {{ old('template_scope')==='global' ? 'selected' : '' }}>Global</option>
                        <option value="client" {{ old('template_scope')==='client' ? 'selected' : '' }}>This Client</option>
                        <option value="property" {{ old('template_scope')==='property' ? 'selected' : '' }}>This Property</option>
                    </select>
                    <button type="submit" class="btn btn-secondary">💾 Save Template</button>
                </div>
            @else
                <button type="submit" class="btn btn-secondary">
                    {{ $editMode ? '🔄 Recalculate Synthetic Turf' : '🧮 Calculate Synthetic Turf' }}
                </button>
                <a href="{{ route('clients.show', $siteVisit->client->id ?? $siteVisitId) }}" class="btn btn-muted">
                    🔙 Back to Client
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
