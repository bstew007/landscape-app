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

        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-2">Crew & Logistics</h2>
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
            <h2 class="text-xl font-semibold mb-3">Synthetic Turf Selection</h2>
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

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-6">
            <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">
                {{ $editMode ? '🔄 Recalculate Synthetic Turf' : '🧮 Calculate Synthetic Turf' }}
            </button>

            <a href="{{ route('clients.show', $siteVisit->client->id ?? $siteVisitId) }}"
               class="inline-flex items-center px-5 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-semibold">
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

        function updateVisibility() {
            advanced.forEach(el => el.classList.toggle('hidden', !toggle.checked));
        }

        toggle.addEventListener('change', updateVisibility);
        updateVisibility();
    });
</script>
@endpush
