@extends('layouts.sidebar')

@section('content')
<div class="max-w-3xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">
        {{ $editMode ? '✏️ Edit Retaining Wall Calculation' : '🧱 Retaining Wall Calculator' }}
    </h1>

    <form method="POST" action="{{ route('calculators.wall.calculate') }}">
        @csrf

        @if ($editMode && isset($calculation))
            <input type="hidden" name="calculation_id" value="{{ $calculation->id }}">
        @endif

        <input type="hidden" name="site_visit_id" value="{{ $siteVisitId }}">

        {{-- Inputs --}}
        <div class="mb-4">
            <label class="block font-semibold">Length (ft)</label>
            <input type="number" step="0.1" name="length" class="form-input w-full"
                   value="{{ old('length', $formData['length'] ?? '') }}" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Height (ft)</label>
            <input type="number" step="0.1" name="height" class="form-input w-full"
                   value="{{ old('height', $formData['height'] ?? '') }}" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Block Brand</label>
            <select name="block_brand" class="form-select w-full" required>
                <option value="">Choose brand</option>
                <option value="belgard" {{ old('block_brand', $formData['block_brand'] ?? '') === 'belgard' ? 'selected' : '' }}>Belgard</option>
                <option value="techo" {{ old('block_brand', $formData['block_brand'] ?? '') === 'techo' ? 'selected' : '' }}>Techo-Bloc</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="inline-flex items-center">
                <input type="checkbox" name="include_capstones" class="form-checkbox"
                       {{ old('include_capstones', $formData['include_capstones'] ?? false) ? 'checked' : '' }}>
                <span class="ml-2">Include Capstones</span>
            </label>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Equipment</label>
            <select name="equipment" class="form-select w-full" required>
                <option value="excavator" {{ old('equipment', $formData['equipment'] ?? '') === 'excavator' ? 'selected' : '' }}>Excavator</option>
                <option value="manual" {{ old('equipment', $formData['equipment'] ?? '') === 'manual' ? 'selected' : '' }}>Manual</option>
            </select>
        </div>

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
                    <label>Block Cost ($/block)</label>
                    <input type="number" step="0.01" name="block_cost" class="form-input w-full"
                           value="{{ old('block_cost', $formData['block_cost'] ?? '') }}">
                </div>
                <div>
                    <label>Capstone Cost ($/cap)</label>
                    <input type="number" step="0.01" name="cap_cost" class="form-input w-full"
                           value="{{ old('cap_cost', $formData['cap_cost'] ?? '') }}">
                </div>
                <div>
                    <label>Adhesive Cost ($/tube)</label>
                    <input type="number" step="0.01" name="adhesive_cost" class="form-input w-full"
                           value="{{ old('adhesive_cost', $formData['adhesive_cost'] ?? '') }}">
                </div>
                <div>
                    <label>Drain Pipe Cost ($/ft)</label>
                    <input type="number" step="0.01" name="pipe_cost" class="form-input w-full"
                           value="{{ old('pipe_cost', $formData['pipe_cost'] ?? '') }}">
                </div>
                <div>
                    <label>Gravel Cost ($/ton)</label>
                    <input type="number" step="0.01" name="gravel_cost" class="form-input w-full"
                           value="{{ old('gravel_cost', $formData['gravel_cost'] ?? '') }}">
                </div>
                <div>
                    <label>Topsoil Cost ($/yard)</label>
                    <input type="number" step="0.01" name="topsoil_cost" class="form-input w-full"
                           value="{{ old('topsoil_cost', $formData['topsoil_cost'] ?? '') }}">
                </div>
                <div>
                    <label>Fabric Cost ($/sqft)</label>
                    <input type="number" step="0.01" name="fabric_cost" class="form-input w-full"
                           value="{{ old('fabric_cost', $formData['fabric_cost'] ?? '') }}">
                </div>
                <div>
                    <label>Geogrid Cost ($/sqft)</label>
                    <input type="number" step="0.01" name="geogrid_cost" class="form-input w-full"
                           value="{{ old('geogrid_cost', $formData['geogrid_cost'] ?? '') }}">
                </div>
            </div>
        </div>

        {{-- Labor Overrides --}}
        <div id="laborSection" class="hidden border p-4 rounded mb-6 bg-gray-50">
            <h3 class="font-semibold mb-3">Labor Production Rates (hours/unit)</h3>
            <div class="grid grid-cols-2 gap-4">
                <div><label>Excavation</label><input type="number" step="0.001" name="rate_excavation" class="form-input w-full" value="{{ old('rate_excavation', $formData['rate_excavation'] ?? '') }}"></div>
                <div><label>Base Install</label><input type="number" step="0.001" name="rate_base_install" class="form-input w-full" value="{{ old('rate_base_install', $formData['rate_base_install'] ?? '') }}"></div>
                <div><label>Block Laying</label><input type="number" step="0.001" name="rate_block_laying" class="form-input w-full" value="{{ old('rate_block_laying', $formData['rate_block_laying'] ?? '') }}"></div>
                <div><label>Pipe Install</label><input type="number" step="0.001" name="rate_pipe_install" class="form-input w-full" value="{{ old('rate_pipe_install', $formData['rate_pipe_install'] ?? '') }}"></div>
                <div><label>Gravel Backfill</label><input type="number" step="0.001" name="rate_gravel_backfill" class="form-input w-full" value="{{ old('rate_gravel_backfill', $formData['rate_gravel_backfill'] ?? '') }}"></div>
                <div><label>Topsoil Backfill</label><input type="number" step="0.001" name="rate_topsoil_backfill" class="form-input w-full" value="{{ old('rate_topsoil_backfill', $formData['rate_topsoil_backfill'] ?? '') }}"></div>
                <div><label>Underlayment</label><input type="number" step="0.001" name="rate_underlayment" class="form-input w-full" value="{{ old('rate_underlayment', $formData['rate_underlayment'] ?? '') }}"></div>
                <div><label>Geogrid</label><input type="number" step="0.001" name="rate_geogrid" class="form-input w-full" value="{{ old('rate_geogrid', $formData['rate_geogrid'] ?? '') }}"></div>
                <div><label>Capstone</label><input type="number" step="0.001" name="rate_capstone" class="form-input w-full" value="{{ old('rate_capstone', $formData['rate_capstone'] ?? '') }}"></div>
            </div>
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold">
            {{ $editMode ? '🔄 Recalculate' : '🧮 Calculate Wall Estimate' }}
        </button>

        <div class="mt-6">
            <a href="{{ route('clients.show', $clientId) }}"
               class="bg-gray-600 hover:bg-gray-700 text-white px-5 py-3 rounded-lg font-semibold">
                🔙 Back to Client
            </a>
        </div>
    </form>
</div>

<script>
    function toggleSection(id) {
        const el = document.getElementById(id);
        el.classList.toggle('hidden');
    }
</script>
@endsection


