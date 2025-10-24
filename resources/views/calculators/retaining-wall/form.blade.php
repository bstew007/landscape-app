@extends('layouts.sidebar')

@section('content')

@if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

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

        {{-- Basic Inputs --}}
        <div class="mb-4">
            <label class="block font-semibold">Length (ft)</label>
            <input type="number" step="0.1" name="length" class="form-input w-full" value="{{ old('length', $formData['length'] ?? '') }}" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Height (ft)</label>
            <input type="number" step="0.1" name="height" class="form-input w-full" value="{{ old('height', $formData['height'] ?? '') }}" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Block System</label>
            <select name="block_system" id="block_system" class="form-select w-full" required>
                <option value="standard" {{ old('block_system', $formData['block_system'] ?? '') === 'standard' ? 'selected' : '' }}>Standard</option>
                <option value="allan_block" {{ old('block_system', $formData['block_system'] ?? '') === 'allan_block' ? 'selected' : '' }}>Allan Block</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Block Brand</label>
            <select name="block_brand" class="form-select w-full" required>
                <option value="">Choose brand</option>
                <option value="belgard" {{ old('block_brand', $formData['block_brand'] ?? '') === 'belgard' ? 'selected' : '' }}>Belgard</option>
                <option value="techo" {{ old('block_brand', $formData['block_brand'] ?? '') === 'techo' ? 'selected' : '' }}>Techo-Bloc</option>
                <option value="allan_block" {{ old('block_brand', $formData['block_brand'] ?? '') === 'allan_block' ? 'selected' : '' }}>Allan Block</option>
            </select>
        </div>

        <div id="allanBlockFields" class="{{ old('block_system', $formData['block_system'] ?? '') === 'allan_block' ? '' : 'hidden' }}">
            <h3 class="text-md font-semibold mt-6">Allan Block Details</h3>
            <div class="grid md:grid-cols-2 gap-4">
                <div><label>Straight Wall Length (ft)</label><input type="number" name="ab_straight_length" value="{{ old('ab_straight_length', $formData['ab_straight_length'] ?? '') }}" class="form-input w-full"></div>
                <div><label>Straight Wall Height (ft)</label><input type="number" name="ab_straight_height" value="{{ old('ab_straight_height', $formData['ab_straight_height'] ?? '') }}" class="form-input w-full"></div>
                <div><label>Curved Wall Length (ft)</label><input type="number" name="ab_curved_length" value="{{ old('ab_curved_length', $formData['ab_curved_length'] ?? '') }}" class="form-input w-full"></div>
                <div><label>Curved Wall Height (ft)</label><input type="number" name="ab_curved_height" value="{{ old('ab_curved_height', $formData['ab_curved_height'] ?? '') }}" class="form-input w-full"></div>
                <div><label>Step Count</label><input type="number" name="ab_step_count" value="{{ old('ab_step_count', $formData['ab_step_count'] ?? '') }}" class="form-input w-full"></div>
                <div><label>Column Count</label><input type="number" name="ab_column_count" value="{{ old('ab_column_count', $formData['ab_column_count'] ?? '') }}" class="form-input w-full"></div>
            </div>
        </div>

        <div class="mb-4 mt-4">
            <label class="block font-semibold">Equipment</label>
            <select name="equipment" class="form-select w-full" required>
                <option value="manual" {{ old('equipment', $formData['equipment'] ?? '') === 'manual' ? 'selected' : '' }}>Manual</option>
                <option value="skid_steer" {{ old('equipment', $formData['equipment'] ?? '') === 'skid steer' ? 'selected' : '' }}>Skid Steer</option>
                <option value="excavator" {{ old('equipment', $formData['equipment'] ?? '') === 'excavator' ? 'selected' : '' }}>Excavator</option>
            </select>
        </div>

        {{-- Overhead and logistics --}}
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div><label>Labor Rate ($/hr)</label><input type="number" step="0.01" name="labor_rate" class="form-input w-full" value="{{ old('labor_rate', $formData['labor_rate'] ?? '') }}" required></div>
            <div><label>Crew Size</label><input type="number" name="crew_size" class="form-input w-full" value="{{ old('crew_size', $formData['crew_size'] ?? '') }}" required></div>
            <div><label>Drive Distance (miles)</label><input type="number" step="0.1" name="drive_distance" class="form-input w-full" value="{{ old('drive_distance', $formData['drive_distance'] ?? '') }}" required></div>
            <div><label>Drive Speed (mph)</label><input type="number" step="1" name="drive_speed" class="form-input w-full" value="{{ old('drive_speed', $formData['drive_speed'] ?? '') }}" required></div>
            <div><label>Site Conditions Overhead (%)</label><input type="number" step="1" name="site_conditions" class="form-input w-full" value="{{ old('site_conditions', $formData['site_conditions'] ?? '') }}"></div>
            <div><label>Material Pickup Overhead (%)</label><input type="number" step="1" name="material_pickup" class="form-input w-full" value="{{ old('material_pickup', $formData['material_pickup'] ?? '') }}"></div>
            <div><label>Cleanup Overhead (%)</label><input type="number" step="1" name="cleanup" class="form-input w-full" value="{{ old('cleanup', $formData['cleanup'] ?? '') }}"></div>
            <div><label>Markup (%)</label><input type="number" step="0.1" name="markup" class="form-input w-full" value="{{ old('markup', $formData['markup'] ?? '') }}" required></div>
        </div>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold">
            {{ $editMode ? '🔄 Recalculate' : '🧮 Calculate Wall Estimate' }}
        </button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const blockSystemSelect = document.getElementById('block_system');
        const allanBlockFields = document.getElementById('allanBlockFields');

        function toggleAllanFields() {
            if (blockSystemSelect.value === 'allan_block') {
                allanBlockFields.classList.remove('hidden');
            } else {
                allanBlockFields.classList.add('hidden');
            }
        }

        blockSystemSelect.addEventListener('change', toggleAllanFields);
        toggleAllanFields(); // initial load
    });
</script>

@endsection