@extends('layouts.sidebar')

@section('content')
<div class="max-w-3xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">
        {{ $editMode ? '✏️ Edit Paver Patio Calculation' : '🧱 Paver Patio Calculator' }}
    </h1>

    <form method="POST" action="{{ route('calculators.patio.calculate') }}">
        @csrf

        @if ($editMode && isset($calculation))
            <input type="hidden" name="calculation_id" value="{{ $calculation->id }}">
        @endif

        <input type="hidden" name="site_visit_id" value="{{ $siteVisit->id }}">

        {{-- Basic Inputs --}}
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

        <div class="mb-4">
            <label class="block font-semibold">Paver Type</label>
            <select name="paver_type" class="form-select w-full" required>
                <option value="">Select a brand</option>
                <option value="belgard" {{ old('paver_type', $formData['paver_type'] ?? '') === 'belgard' ? 'selected' : '' }}>Belgard</option>
                <option value="techo" {{ old('paver_type', $formData['paver_type'] ?? '') === 'techo' ? 'selected' : '' }}>Techo-Bloc</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Edge Restraint Type</label>
            <select name="edge_restraint" class="form-select w-full" required>
                <option value="">Choose edge type</option>
                <option value="plastic" {{ old('edge_restraint', $formData['edge_restraint'] ?? '') === 'plastic' ? 'selected' : '' }}>Plastic</option>
                <option value="concrete" {{ old('edge_restraint', $formData['edge_restraint'] ?? '') === 'concrete' ? 'selected' : '' }}>Concrete</option>
            </select>
        </div>

        {{-- General Labor --}}
        <div class="mb-4">
            <label class="block font-semibold">Labor Rate ($/hr)</label>
            <input type="number" step="0.01" name="labor_rate" class="form-input w-full"
                   value="{{ old('labor_rate', $formData['labor_rate'] ?? '') }}" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Crew Size</label>
            <input type="number" name="crew_size" class="form-input w-full"
                   value="{{ old('crew_size', $formData['crew_size'] ?? '') }}" required>
        </div>

        {{-- Drive and Overhead --}}
        <div class="mb-4">
            <label class="block font-semibold">Drive Distance (miles)</label>
            <input type="number" step="0.1" name="drive_distance" class="form-input w-full"
                   value="{{ old('drive_distance', $formData['drive_distance'] ?? '') }}" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Drive Speed (mph)</label>
            <input type="number" step="1" name="drive_speed" class="form-input w-full"
                   value="{{ old('drive_speed', $formData['drive_speed'] ?? '') }}" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Site Conditions Overhead (%)</label>
            <input type="number" step="1" name="site_conditions" class="form-input w-full"
                   value="{{ old('site_conditions', $formData['site_conditions'] ?? '') }}">
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Material Pickup Overhead (%)</label>
            <input type="number" step="1" name="material_pickup" class="form-input w-full"
                   value="{{ old('material_pickup', $formData['material_pickup'] ?? '') }}">
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Cleanup Overhead (%)</label>
            <input type="number" step="1" name="cleanup" class="form-input w-full"
                   value="{{ old('cleanup', $formData['cleanup'] ?? '') }}">
        </div>

        <div class="mb-6">
            <label class="block font-semibold">Markup (%)</label>
            <input type="number" step="0.1" name="markup" class="form-input w-full"
                   value="{{ old('markup', $formData['markup'] ?? '') }}" required>
        </div>

        {{-- Toggle Buttons --}}
        <div class="mb-6">
            <button type="button" onclick="toggleSection('materialsSection')"
                    class="text-blue-600 underline mb-2">🧱 Adjust Material Costs</button><br>
            <button type="button" onclick="toggleSection('laborSection')"
                    class="text-blue-600 underline">👷‍♂️ Adjust Labor Production Rates</button>
        </div>

        {{-- Material Overrides --}}
        <div id="materialsSection" class="hidden border p-4 rounded mb-6 bg-gray-50">
            <h3 class="font-semibold mb-3">Material Unit Costs</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label>Paver Cost per Unit ($)</label>
                    <input type="number" step="0.01" name="paver_cost" class="form-input w-full"
                           value="{{ old('paver_cost', $formData['paver_cost'] ?? '') }}">
                </div>
                <div>
                    <label>Base Gravel Cost per Ton ($)</label>
                    <input type="number" step="0.01" name="base_cost" class="form-input w-full"
                           value="{{ old('base_cost', $formData['base_cost'] ?? '') }}">
                </div>
                <div>
                    <label>Plastic Edge Restraint ($/20ft)</label>
                    <input type="number" step="0.01" name="plastic_edge_cost" class="form-input w-full"
                           value="{{ old('plastic_edge_cost', $formData['plastic_edge_cost'] ?? '') }}">
                </div>
                <div>
                    <label>Concrete Edge Restraint ($/20ft)</label>
                    <input type="number" step="0.01" name="concrete_edge_cost" class="form-input w-full"
                           value="{{ old('concrete_edge_cost', $formData['concrete_edge_cost'] ?? '') }}">
                </div>
            </div>
        </div>

        {{-- Labor Overrides --}}
        <div id="laborSection" class="hidden border p-4 rounded mb-6 bg-gray-50">
            <h3 class="font-semibold mb-3">Labor Production Rates (hours/sqft)</h3>
            <div class="grid grid-cols-2 gap-4">
                <div><label>Excavation</label>
                    <input type="number" step="0.001" name="rate_excavation" class="form-input w-full"
                           value="{{ old('rate_excavation', $formData['rate_excavation'] ?? '') }}"></div>
                <div><label>Base Compaction</label>
                    <input type="number" step="0.001" name="rate_base_compaction" class="form-input w-full"
                           value="{{ old('rate_base_compaction', $formData['rate_base_compaction'] ?? '') }}"></div>
                <div><label>Laying Pavers</label>
                    <input type="number" step="0.001" name="rate_laying_pavers" class="form-input w-full"
                           value="{{ old('rate_laying_pavers', $formData['rate_laying_pavers'] ?? '') }}"></div>
                <div><label>Cutting Borders</label>
                    <input type="number" step="0.001" name="rate_cutting_borders" class="form-input w-full"
                           value="{{ old('rate_cutting_borders', $formData['rate_cutting_borders'] ?? '') }}"></div>
                <div><label>Install Edging</label>
                    <input type="number" step="0.001" name="rate_install_edging" class="form-input w-full"
                           value="{{ old('rate_install_edging', $formData['rate_install_edging'] ?? '') }}"></div>
                <div><label>Cleanup</label>
                    <input type="number" step="0.001" name="rate_cleanup" class="form-input w-full"
                           value="{{ old('rate_cleanup', $formData['rate_cleanup'] ?? '') }}"></div>
            </div>
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold">
            {{ $editMode ? '🔄 Recalculate' : '🧮 Calculate Patio Estimate' }}
        </button>

        <div class="mt-6">
            <a href="{{ route('clients.show', $siteVisit->client_id) }}"
               class="bg-gray-600 hover:bg-gray-700 text-white px-5 py-3 rounded-lg font-semibold">
                🔙 Back to Client
            </a>
        </div>
    </form>
</div>

{{-- JS TOGGLE --}}
<script>
    function toggleSection(id) {
        const section = document.getElementById(id);
        section.classList.toggle('hidden');
    }
</script>
@endsection
