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

        <div class="mb-6">
            @include('calculators.partials.section_heading', ['title' => 'Crew & Logistics'])
            @include('calculators.partials.overhead_inputs')
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
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

        <div class="mb-6 bg-white rounded border p-4">
            @include('calculators.partials.section_heading', ['title' => 'Synthetic Turf Selection'])
            @php
                $turfOptions = config('syn_turf.materials.turf_tiers', []);
                $turfGrade = old('turf_grade', $formData['turf_grade'] ?? 'better');
                $overrideChecked = old('materials_override_enabled', $formData['materials_override_enabled'] ?? false);
            @endphp
            <label class="block font-semibold mb-1">Turf Tier</label>
            <select name="turf_grade" class="form-select w-full">
                @foreach ($turfOptions as $key => $tier)
                    <option value="{{ $key }}"
                        {{ $turfGrade === $key ? 'selected' : '' }}>
                        {{ $tier['label'] ?? ucfirst($key) }} (${{ number_format($tier['unit_cost'] ?? 0, 2) }} / sq ft)
                    </option>
                @endforeach
            </select>

            @include('calculators.partials.material_override_inputs', [
                'overrideToggleName' => 'materials_override_enabled',
                'overrideToggleLabel' => 'Manually override material pricing / product name',
                'overrideChecked' => (bool) $overrideChecked,
                'fields' => [
                    [
                        'type' => 'text',
                        'name' => 'turf_custom_name',
                        'label' => 'Turf Product Name',
                        'placeholder' => 'e.g. Emerald Pro 90',
                        'value' => $formData['turf_custom_name'] ?? '',
                        'help' => 'Optional custom label shown in estimates/PDFs.',
                        'width' => 'full',
                    ],
                    [
                        'type' => 'number',
                        'name' => 'override_turf_price',
                        'label' => 'Turf Price ($ / sq ft)',
                        'placeholder' => 'Leave blank for tier default',
                        'value' => $formData['override_turf_price'] ?? '',
                        'step' => '0.01',
                        'min' => '0',
                        'width' => 'half',
                    ],
                    [
                        'type' => 'number',
                        'name' => 'override_infill_price',
                        'label' => 'Infill Price ($ / bag)',
                        'placeholder' => 'Default $25',
                        'value' => $formData['override_infill_price'] ?? '',
                        'step' => '0.01',
                        'min' => '0',
                        'width' => 'half',
                    ],
                    [
                        'type' => 'number',
                        'name' => 'override_edging_price',
                        'label' => "Edging Board Price ($ / 20')",
                        'placeholder' => 'Default $45',
                        'value' => $formData['override_edging_price'] ?? '',
                        'step' => '0.01',
                        'min' => '0',
                        'width' => 'half',
                    ],
                    [
                        'type' => 'number',
                        'name' => 'override_weed_barrier_price',
                        'label' => 'Weed Barrier Price ($ / roll)',
                        'placeholder' => 'Default $75',
                        'value' => $formData['override_weed_barrier_price'] ?? '',
                        'step' => '0.01',
                        'min' => '0',
                        'width' => 'half',
                    ],
                ],
            ])
        </div>

        <div class="mb-4">
            <label class="inline-flex items-center">
                <input type="checkbox" id="toggleAdvancedTasks" class="form-checkbox h-5 w-5 text-blue-600">
                <span class="ml-2 text-sm font-medium">Show Optional Crew/Detail Tasks</span>
            </label>
        </div>

        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-2">Synthetic Turf Tasks</h2>

            @php
                $savedTasks = $formData['tasks'] ?? [];
                $savedQuantities = [];

                foreach ($savedTasks as $taskRow) {
                    $key = str_replace(' ', '_', strtolower($taskRow['task']));
                    $savedQuantities[$key] = $taskRow['qty'] ?? null;
                }

                $rates = \App\Models\ProductionRate::where('calculator', 'syn_turf')
                    ->orderBy('task')
                    ->get();
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
                @foreach ($rates as $rate)
                    @php
                        $key = $rate->task;
                        $label = ucwords(str_replace('_', ' ', $key));
                        $value = old("tasks.$key.qty", $savedQuantities[$key] ?? '');
                        $isAdvanced = str_contains($key, 'detail');
                    @endphp

                    <div class="border p-4 rounded bg-gray-50 {{ $isAdvanced ? 'advanced-task hidden' : '' }} {{ in_array($key, ['excavation','excavation_skid_steer','excavation_mini_skid']) ? 'excavation-card' : '' }}" @if(in_array($key, ['excavation','excavation_skid_steer','excavation_mini_skid'])) data-excavation-task="1" data-excavation-key="{{ $key }}" @endif>
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
    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.getElementById('toggleAdvancedTasks');
        const advanced = document.querySelectorAll('.advanced-task');

        function updateVisibility() {
            advanced.forEach(el => el.classList.toggle('hidden', !toggle.checked));
        }

        if (toggle) {
            toggle.addEventListener('change', updateVisibility);
            updateVisibility();
        }

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
            methodRadios.forEach(r => r.addEventListener('change', applyExcavationVisibility));
            applyExcavationVisibility();
        }

        // Mirror edging linear feet to edging task qty (unless user overrides)
        const edgingLF = document.querySelector('input[name="edging_linear_ft"]');
        const edgingQty = document.querySelector('input[name="tasks[edging][qty]"]');
        if (edgingLF && edgingQty) {
            function syncEdging() {
                if (edgingQty.dataset.userEdited === 'true') return;
                edgingQty.value = edgingLF.value;
                edgingQty.dataset.synced = 'true';
            }
            edgingLF.addEventListener('input', syncEdging);
            edgingQty.addEventListener('input', () => { edgingQty.dataset.userEdited = 'true'; });
            // Initial sync if empty
            if (!edgingQty.value) syncEdging();
        }
    });
</script>
@endpush
