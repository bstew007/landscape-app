@extends('layouts.sidebar')

@section('content')
<div class="max-w-4xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">
        {{ $editMode ?? false ? '✏️ Edit Retaining Wall Estimate' : '🧱 Retaining Wall Calculator' }}
    </h1>

    <form method="POST" action="{{ route('calculators.wall.calculate') }}" class="space-y-6 bg-white p-6 rounded shadow">
        @csrf

        {{-- Wall Dimensions --}}
        <div>
            <label class="block font-semibold mb-1">Wall Length (ft)</label>
            <input type="number" name="length" step="0.1" required
                   value="{{ old('length', $formData['length'] ?? '') }}"
                   class="form-input w-full" />
        </div>

        <div>
            <label class="block font-semibold mb-1">Wall Height (ft)</label>
            <input type="number" name="height" step="0.1" required
                   value="{{ old('height', $formData['height'] ?? '') }}"
                   class="form-input w-full" />
        </div>

        {{-- Equipment --}}
        <div>
            <label class="block font-semibold mb-1">Equipment Used</label>
            <select name="equipment" required class="form-select w-full">
                <option value="excavator" {{ (old('equipment', $formData['equipment'] ?? '') == 'excavator') ? 'selected' : '' }}>Excavator</option>
                <option value="hand" {{ (old('equipment', $formData['equipment'] ?? '') == 'hand') ? 'selected' : '' }}>Hand Tools</option>
            </select>
        </div>

        {{-- Block Brand --}}
        <div>
            <label class="block font-semibold mb-1">Block Brand</label>
            <select name="block_brand" required class="form-select w-full">
                <option value="belgard" {{ (old('block_brand', $formData['block_brand'] ?? '') == 'belgard') ? 'selected' : '' }}>Belgard Diamond Pro</option>
                <option value="techo" {{ (old('block_brand', $formData['block_brand'] ?? '') == 'techo') ? 'selected' : '' }}>Techo-Bloc</option>
            </select>
        </div>

        {{-- Capstones --}}
        <div class="flex items-center">
            <input type="checkbox" name="include_capstones" id="include_capstones" value="1"
                   {{ old('include_capstones', $formData['include_capstones'] ?? false) ? 'checked' : '' }}
                   class="mr-2">
            <label for="include_capstones" class="font-semibold">Include Capstones</label>
        </div>

        {{-- Site Visit ID --}}
        <input type="hidden" name="site_visit_id" value="{{ $siteVisitId }}">

        {{-- Crew & Labor --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold mb-1">Crew Size</label>
                <input type="number" name="crew_size" min="1" required
                       value="{{ old('crew_size', $formData['crew_size'] ?? '') }}"
                       class="form-input w-full" />
            </div>
            <div>
                <label class="block font-semibold mb-1">Labor Rate ($/hr)</label>
                <input type="number" name="labor_rate" min="1" required
                       value="{{ old('labor_rate', $formData['labor_rate'] ?? '') }}"
                       class="form-input w-full" />
            </div>
        </div>

        {{-- Travel --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold mb-1">Drive Distance (miles)</label>
                <input type="number" name="drive_distance" step="0.1" required
                       value="{{ old('drive_distance', $formData['drive_distance'] ?? '') }}"
                       class="form-input w-full" />
            </div>
            <div>
                <label class="block font-semibold mb-1">Drive Speed (mph)</label>
                <input type="number" name="drive_speed" step="1" required
                       value="{{ old('drive_speed', $formData['drive_speed'] ?? '') }}"
                       class="form-input w-full" />
            </div>
        </div>

        {{-- Overhead Fields --}}
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block font-semibold mb-1">Site Conditions (%)</label>
                <input type="number" name="site_conditions" step="1"
                       value="{{ old('site_conditions', $formData['site_conditions'] ?? '') }}"
                       class="form-input w-full" />
            </div>
            <div>
                <label class="block font-semibold mb-1">Material Pickup (%)</label>
                <input type="number" name="material_pickup" step="1"
                       value="{{ old('material_pickup', $formData['material_pickup'] ?? '') }}"
                       class="form-input w-full" />
            </div>
            <div>
                <label class="block font-semibold mb-1">Cleanup (%)</label>
                <input type="number" name="cleanup" step="1"
                       value="{{ old('cleanup', $formData['cleanup'] ?? '') }}"
                       class="form-input w-full" />
            </div>
        </div>

        {{-- Markup --}}
        <div>
            <label class="block font-semibold mb-1">Markup (%)</label>
            <input type="number" name="markup" step="1" required
                   value="{{ old('markup', $formData['markup'] ?? '') }}"
                   class="form-input w-full" />
        </div>

        {{-- Submit --}}
        <div class="pt-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold">
                📊 {{ $editMode ?? false ? 'Recalculate Estimate' : 'Calculate Estimate' }}
            </button>
        </div>
    </form>
</div>
@endsection
