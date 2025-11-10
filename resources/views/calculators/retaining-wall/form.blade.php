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

@php
    $overrideChecked = (bool) old('materials_override_enabled', $formData['materials_override_enabled'] ?? false);
@endphp

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

        {{-- Crew & Logistics --}}
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-2">Crew & Logistics</h2>
            @include('calculators.partials.overhead_inputs')
        </div>

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
           <div class="mb-4">
             <label class="inline-flex items-center">
                 <input type="checkbox" name="use_capstones" value="1" {{ old('use_capstones', $formData['use_capstones'] ?? false) ? 'checked' : '' }}>
             <span class="ml-2">Include Capstones</span>
                    </label>
            </div>


        <div id="allanBlockFields" class="{{ old('block_system', $formData['block_system'] ?? '') === 'allan_block' ? '' : 'hidden' }}">
    <h3 class="text-md font-semibold mt-6">Allan Block Details</h3>
    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label>Straight Wall Length (ft)</label>
            <input type="number" name="ab_straight_length" value="{{ old('ab_straight_length', $formData['ab_straight_length'] ?? '') }}" class="form-input w-full">
        </div>
        <div>
            <label>Straight Wall Height (ft)</label>
            <input type="number" name="ab_straight_height" value="{{ old('ab_straight_height', $formData['ab_straight_height'] ?? '') }}" class="form-input w-full">
        </div>
        <div>
            <label>Curved Wall Length (ft)</label>
            <input type="number" name="ab_curved_length" value="{{ old('ab_curved_length', $formData['ab_curved_length'] ?? '') }}" class="form-input w-full">
        </div>
        <div>
            <label>Curved Wall Height (ft)</label>
            <input type="number" name="ab_curved_height" value="{{ old('ab_curved_height', $formData['ab_curved_height'] ?? '') }}" class="form-input w-full">
        </div>
        <div>
            <label>Step Count</label>
            <input type="number" name="ab_step_count" value="{{ old('ab_step_count', $formData['ab_step_count'] ?? '') }}" class="form-input w-full">
        </div>
        <div>
            <label>Column Count</label>
            <input type="number" name="ab_column_count" value="{{ old('ab_column_count', $formData['ab_column_count'] ?? '') }}" class="form-input w-full">
        </div>
    </div>
</div>


        {{-- Equipment Selection --}}
        <div class="mb-4 mt-4">
            <label class="block font-semibold">Equipment</label>
            <select name="equipment" class="form-select w-full" required>
                <option value="manual" {{ old('equipment', $formData['equipment'] ?? '') === 'manual' ? 'selected' : '' }}>Manual</option>
                <option value="skid_steer" {{ old('equipment', $formData['equipment'] ?? '') === 'skid steer' ? 'selected' : '' }}>Skid Steer</option>
                <option value="excavator" {{ old('equipment', $formData['equipment'] ?? '') === 'excavator' ? 'selected' : '' }}>Excavator</option>
            </select>
        </div>

        <div class="mt-6 border-t pt-4">
            @include('calculators.partials.material_override_inputs', [
                'overrideToggleName' => 'materials_override_enabled',
                'overrideToggleLabel' => 'Override Material Prices',
                'overrideChecked' => $overrideChecked,
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
                        'label' => 'Topsoil ($/yd³)',
                        'type' => 'number',
                        'step' => '0.01',
                        'min' => '0',
                        'value' => $formData['override_topsoil_cost'] ?? '',
                        'width' => 'half',
                    ],
                    [
                        'name' => 'override_fabric_cost',
                        'label' => 'Underlayment Fabric ($/ft²)',
                        'type' => 'number',
                        'step' => '0.01',
                        'min' => '0',
                        'value' => $formData['override_fabric_cost'] ?? '',
                        'width' => 'half',
                    ],
                    [
                        'name' => 'override_geogrid_cost',
                        'label' => 'Geogrid ($/ft²)',
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
        </div>


        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-6">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">
                {{ $editMode ? '🔄 Recalculate' : '🧮 Calculate Wall Estimate' }}
            </button>

            <a href="{{ route('clients.show', $siteVisit->client_id ?? $siteVisitId) }}"
               class="inline-flex items-center px-5 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                🔙 Back to Client
            </a>
        </div>
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
