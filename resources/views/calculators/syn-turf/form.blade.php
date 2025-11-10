@extends('layouts.sidebar')

@section('content')
<div class="max-w-4xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">
        {{ $editMode ? '✏️ Edit Synthetic Turf Estimate' : '🏟️ Synthetic Turf Calculator' }}
    </h1>

    <form method="POST" action="{{ route('calculators.syn_turf.calculate') }}">
        @csrf

        @if ($editMode && isset($calculation))
            <input type="hidden" name="calculation_id" value="{{ $calculation->id }}">
        @endif

        <input type="hidden" name="site_visit_id" value="{{ $siteVisitId }}">

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
            <h2 class="text-xl font-semibold mb-3">Synthetic Turf Selection</h2>
            @php
                $turfGrade = old('turf_grade', $formData['turf_grade'] ?? 'better');
                $overrideChecked = old('materials_override_enabled', $formData['materials_override_enabled'] ?? false);
            @endphp
            <label class="block font-semibold mb-1">Turf Tier</label>
            <select name="turf_grade" class="form-select w-full">
                <option value="good" {{ $turfGrade === 'good' ? 'selected' : '' }}>Good ($2.00 / sq ft)</option>
                <option value="better" {{ $turfGrade === 'better' ? 'selected' : '' }}>Better ($3.00 / sq ft)</option>
                <option value="best" {{ $turfGrade === 'best' ? 'selected' : '' }}>Best ($4.00 / sq ft)</option>
            </select>

            <div class="mt-4">
                <label class="inline-flex items-center">
                    <input type="checkbox"
                           id="toggleOverride"
                           name="materials_override_enabled"
                           value="1"
                           class="form-checkbox h-5 w-5 text-blue-600"
                           {{ $overrideChecked ? 'checked' : '' }}>
                    <span class="ml-2 text-sm font-medium">Manually override material pricing / product name</span>
                </label>
            </div>

            <div id="overrideFields" class="mt-4 space-y-4 {{ $overrideChecked ? '' : 'hidden' }}">
                <div>
                    <label class="block text-sm font-semibold">Turf Product Name</label>
                    <input type="text"
                           name="turf_custom_name"
                           class="form-input w-full"
                           placeholder="e.g. Emerald Pro 90"
                           value="{{ old('turf_custom_name', $formData['turf_custom_name'] ?? '') }}">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold">Turf Price ($ / sq ft)</label>
                        <input type="number"
                               step="0.01"
                               min="0"
                               name="override_turf_price"
                               class="form-input w-full"
                               value="{{ old('override_turf_price', $formData['override_turf_price'] ?? '') }}"
                               placeholder="Leave blank for default tier price">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold">Infill Price ($ / bag)</label>
                        <input type="number"
                               step="0.01"
                               min="0"
                               name="override_infill_price"
                               class="form-input w-full"
                               value="{{ old('override_infill_price', $formData['override_infill_price'] ?? '') }}"
                               placeholder="Default $25">
                    </div>
                </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold">Edging Board Price ($ / 20')</label>
                        <input type="number"
                               step="0.01"
                               min="0"
                               name="override_edging_price"
                               class="form-input w-full"
                               value="{{ old('override_edging_price', $formData['override_edging_price'] ?? '') }}"
                               placeholder="Default $45">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold">Weed Barrier Price ($ / roll)</label>
                        <input type="number"
                               step="0.01"
                               min="0"
                               name="override_weed_barrier_price"
                               class="form-input w-full"
                               value="{{ old('override_weed_barrier_price', $formData['override_weed_barrier_price'] ?? '') }}"
                               placeholder="Default $75">
                    </div>
                </div>
            </div>
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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($rates as $rate)
                    @php
                        $key = $rate->task;
                        $label = ucwords(str_replace('_', ' ', $key));
                        $value = old("tasks.$key.qty", $savedQuantities[$key] ?? '');
                        $isAdvanced = str_contains($key, 'detail') || str_contains($key, 'edging');
                    @endphp

                    <div class="border p-4 rounded bg-gray-50 {{ $isAdvanced ? 'advanced-task hidden' : '' }}">
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

        <div class="mb-6">
            @include('calculators.partials.overhead_inputs')
        </div>

        <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">
            {{ $editMode ? '🔄 Recalculate Synthetic Turf' : '🧮 Calculate Synthetic Turf' }}
        </button>

        <div class="mt-6">
            <a href="{{ route('clients.show', $siteVisit->client->id ?? $siteVisitId) }}"
               class="bg-gray-600 hover:bg-gray-700 text-white px-5 py-3 rounded-lg font-semibold">
                🔙 Back to Client
            </a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.getElementById('toggleAdvancedTasks');
        const advanced = document.querySelectorAll('.advanced-task');
        const overrideToggle = document.getElementById('toggleOverride');
        const overrideFields = document.getElementById('overrideFields');

        function updateVisibility() {
            advanced.forEach(el => el.classList.toggle('hidden', !toggle.checked));
        }

        toggle.addEventListener('change', updateVisibility);
        updateVisibility();

        if (overrideToggle && overrideFields) {
            const updateOverrideVisibility = () => {
                overrideFields.classList.toggle('hidden', !overrideToggle.checked);
            };

            overrideToggle.addEventListener('change', updateOverrideVisibility);
            updateOverrideVisibility();
        }
    });
</script>
@endpush
