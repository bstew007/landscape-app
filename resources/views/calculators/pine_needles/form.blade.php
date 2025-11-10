@extends('layouts.sidebar')

@section('content')
<div class="max-w-4xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">
        {{ $editMode ? '✏️ Edit Pine Needle Data' : '🌿 Pine Needle Calculator' }}
    </h1>

    <form method="POST" action="{{ route('calculators.pine_needles.calculate') }}">
        @csrf

        {{-- Edit Mode: Calculation ID --}}
        @if ($editMode && isset($calculation))
            <input type="hidden" name="calculation_id" value="{{ $calculation->id }}">
        @endif

        {{-- Required --}}
        <input type="hidden" name="site_visit_id" value="{{ $siteVisitId }}">

        {{-- Crew & Logistics --}}
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-2">Crew & Logistics</h2>
            @include('calculators.partials.overhead_inputs')
        </div>

        {{-- Straw Area --}}
<div class="mb-6">
    <h2 class="text-xl font-semibold mb-2">Straw Coverage</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Square Footage --}}
        <div>
            <label class="block font-semibold mb-1">Square Footage</label>
            <input type="number"
                   name="area_sqft"
                   step="any"
                   min="0"
                   class="form-input w-full"
                   placeholder="Enter total area (sqft)"
                   value="{{ old('area_sqft', $formData['area_sqft'] ?? '') }}">
        </div>

</div>

<div id="mulchEstimate" class="mb-6 bg-green-50 border border-green-200 rounded p-4 text-green-800 font-semibold hidden">
    🧮 <strong>Estimated Straw Needed:</strong> <span id="mulchYards"></span> bales
</div>

{{-- Straw Type Dropdown --}}
<div class="mb-6">
    <label class="block font-semibold mb-1">Straw Type</label>
    <select name="mulch_type" class="form-select w-full">
        <option value="" disabled {{ !isset($formData['mulch_type']) ? 'selected' : '' }}>Select a straw type</option>
        @php
            $types = [
                'Pine Needles', 'Wheat Straw'
            ];
            $selectedType = old('mulch_type', $formData['mulch_type'] ?? '');
        @endphp
        @foreach ($types as $type)
            <option value="{{ $type }}" {{ $selectedType === $type ? 'selected' : '' }}>
                {{ $type }}
            </option>
        @endforeach
    </select>
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

        

        {{-- Submit --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-6">
            <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">
                {{ $editMode ? '🔄 Recalculate Pine Needle Data' : '🧮 Calculate Pine Needle Data' }}
            </button>

            <a href="{{ route('clients.show', $siteVisitId) }}"
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
    function calculateMulchYards() {
        const areaInput = document.querySelector('input[name="area_sqft"]');
        const outputDiv = document.getElementById('mulchEstimate');
        const outputValue = document.getElementById('mulchYards');

        const area = parseFloat(areaInput.value);

        if (!isNaN(area) && area > 0) {
           const mulchYards = Math.ceil(area /50);
            outputValue.textContent = mulchYards;
            outputDiv.classList.remove('hidden');
        } else {
            outputDiv.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const areaInput = document.querySelector('input[name="area_sqft"]');

        areaInput.addEventListener('input', calculateMulchYards);

        // Recalculate on load if values are already present
        calculateMulchYards();
    });
</script>
@endpush
