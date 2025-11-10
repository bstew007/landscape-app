@extends('layouts.sidebar')

@php
    $hasOverrides = collect(old())->keys()->filter(fn($key) => str_starts_with($key, 'override_'))->isNotEmpty();
    $overrideChecked = old('materials_override_enabled', $formData['materials_override_enabled'] ?? $hasOverrides);
@endphp

@section('content')
<div class="max-w-4xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">
        {{ $editMode ? '✏️ Edit Paver Patio Data' : '🧱 Paver Patio Calculator' }}
    </h1>

    <form method="POST" action="{{ route('calculators.patio.calculate') }}">
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

        {{-- Material Overrides --}}
        @include('calculators.partials.material_override_inputs', [
            'overrideToggleName' => 'materials_override_enabled',
            'overrideToggleLabel' => 'Show Material Cost Overrides',
            'overrideChecked' => (bool) $overrideChecked,
            'fields' => [
                [
                    'name' => 'override_paver_cost',
                    'label' => 'Paver Cost per sqft ($)',
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                    'value' => $formData['override_paver_cost'] ?? '',
                    'width' => 'half',
                ],
                [
                    'name' => 'override_base_cost',
                    'label' => 'Base Gravel Cost per Ton ($)',
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                    'value' => $formData['override_base_cost'] ?? '',
                    'width' => 'half',
                ],
                [
                    'name' => 'override_plastic_edge_cost',
                    'label' => 'Plastic Edge ($/20ft)',
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                    'value' => $formData['override_plastic_edge_cost'] ?? '',
                    'width' => 'half',
                ],
                [
                    'name' => 'override_concrete_edge_cost',
                    'label' => 'Concrete Edge ($/20ft)',
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                    'value' => $formData['override_concrete_edge_cost'] ?? '',
                    'width' => 'half',
                ],
            ],
        ])

        

        {{-- Submit --}}
        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold">
            {{ $editMode ? '🔄 Recalculate' : '🧮 Calculate Patio Data' }}
        </button>

        <div class="mt-6">
            <a href="{{ route('clients.show', $siteVisitId) }}"
               class="bg-gray-600 hover:bg-gray-700 text-white px-5 py-3 rounded-lg font-semibold">
                🔙 Back to Client
            </a>
        </div>
    </form>
</div>

@endsection
