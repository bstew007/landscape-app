@extends('layouts.sidebar')

@php
    $hasOverrides = collect(old())->keys()->filter(fn($key) => str_starts_with($key, 'override_'))->isNotEmpty();
@endphp

@section('content')
<div class="max-w-4xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">
        {{ $editMode ? '✏️ Edit Paver Patio Estimate' : '🧱 Paver Patio Calculator' }}
    </h1>

    <form method="POST" action="{{ route('calculators.patio.calculate') }}">
        @csrf

        {{-- Edit Mode: Calculation ID --}}
        @if ($editMode && isset($calculation))
            <input type="hidden" name="calculation_id" value="{{ $calculation->id }}">
        @endif

        {{-- Required --}}
        <input type="hidden" name="site_visit_id" value="{{ $siteVisitId }}">

        {{-- Dimensions --}}
        <div class="mb-4">
            <label class="block font-semibold">Length (ft)</label>
            <input type="number" step="0.1" name="length" class="form-input w-full"
                   value="{{ old('length', $formData['length'] ?? '') }}" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Width (ft)</label>
            <input type="number" step="0.1" name="width" class="form-input w-full"
                   value="{{ old('width', $formData['width'] ?? '') }}" required>
        </div>

        {{-- Paver Type --}}
        <div class="mb-4">
            <label class="block font-semibold">Paver Type</label>
            <select name="paver_type" class="form-select w-full" required>
                <option value="">-- Select a Brand --</option>
                <option value="belgard" {{ old('paver_type', $formData['paver_type'] ?? '') === 'belgard' ? 'selected' : '' }}>Belgard</option>
                <option value="techo" {{ old('paver_type', $formData['paver_type'] ?? '') === 'techo' ? 'selected' : '' }}>Techo-Bloc</option>
            </select>
        </div>

        {{-- Edge Restraint --}}
        <div class="mb-4">
            <label class="block font-semibold">Edge Restraint Type</label>
            <select name="edge_restraint" class="form-select w-full" required>
                <option value="">-- Choose Edge Type --</option>
                <option value="plastic" {{ old('edge_restraint', $formData['edge_restraint'] ?? '') === 'plastic' ? 'selected' : '' }}>Plastic</option>
                <option value="concrete" {{ old('edge_restraint', $formData['edge_restraint'] ?? '') === 'concrete' ? 'selected' : '' }}>Concrete</option>
            </select>
        </div>

        {{-- Overhead (Use Partial) --}}
        <div class="mb-6">
            @include('calculators.partials.overhead_inputs')
        </div>

        {{-- Material Overrides --}}
        <div class="mb-6">
            <label class="inline-flex items-center">
                <input type="checkbox" id="showOverrides" class="form-checkbox" {{ $hasOverrides ? 'checked' : '' }}>
                <span class="ml-2">Show Material Cost Overrides</span>
            </label>
        </div>

        <div id="overrides-section" style="display: none;">
            <div class="border p-4 rounded bg-gray-50 mb-6">
                <h3 class="font-semibold mb-3">Material Unit Costs</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label>Paver Cost per sqft ($)</label>
                        <input type="number" step="0.01" name="override_paver_cost" class="form-input w-full"
                               value="{{ old('override_paver_cost', $formData['override_paver_cost'] ?? '') }}">
                    </div>
                    <div>
                        <label>Base Gravel Cost per Ton ($)</label>
                        <input type="number" step="0.01" name="override_base_cost" class="form-input w-full"
                               value="{{ old('override_base_cost', $formData['override_base_cost'] ?? '') }}">
                    </div>
                    <div>
                        <label>Plastic Edge ($/20ft)</label>
                        <input type="number" step="0.01" name="override_plastic_edge_cost" class="form-input w-full"
                               value="{{ old('override_plastic_edge_cost', $formData['override_plastic_edge_cost'] ?? '') }}">
                    </div>
                    <div>
                        <label>Concrete Edge ($/20ft)</label>
                        <input type="number" step="0.01" name="override_concrete_edge_cost" class="form-input w-full"
                               value="{{ old('override_concrete_edge_cost', $formData['override_concrete_edge_cost'] ?? '') }}">
                    </div>
                </div>
            </div>
        </div>

        

        {{-- Submit --}}
        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold">
            {{ $editMode ? '🔄 Recalculate' : '🧮 Calculate Patio Estimate' }}
        </button>

        <div class="mt-6">
            <a href="{{ route('clients.show', $siteVisitId) }}"
               class="bg-gray-600 hover:bg-gray-700 text-white px-5 py-3 rounded-lg font-semibold">
                🔙 Back to Client
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const overridesCheckbox = document.getElementById('showOverrides');
        const section = document.getElementById('overrides-section');

        function toggleOverrides() {
            section.style.display = overridesCheckbox.checked ? 'block' : 'none';
        }

        overridesCheckbox.addEventListener('change', toggleOverrides);
        toggleOverrides(); // Initial state
    });
</script>
@endpush

@endsection

