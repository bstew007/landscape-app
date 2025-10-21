@extends('layouts.sidebar')

@section('content')
<div class="max-w-4xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">
        {{ $editMode ? '✏️ Edit Retaining Wall Estimate' : '🧮 New Retaining Wall Estimate' }}
    </h1>

    <form method="POST" action="{{ route('calculators.wall.calculate') }}">
        @csrf

        {{-- Hidden fields --}}
        <input type="hidden" name="site_visit_id" value="{{ $siteVisitId }}">
        @if ($editMode && isset($calculation))
            <input type="hidden" name="calculation_id" value="{{ $calculation->id }}">
        @endif

        {{-- Length --}}
        <div class="mb-4">
            <label class="block font-semibold mb-1">Wall Length (ft):</label>
            <input type="number" step="0.1" name="length" required
                   value="{{ old('length', $formData['length'] ?? '') }}"
                   class="w-full border border-gray-300 rounded px-4 py-2">
        </div>

        {{-- Height --}}
        <div class="mb-4">
            <label class="block font-semibold mb-1">Wall Height (ft):</label>
            <input type="number" step="0.1" name="height" required
                   value="{{ old('height', $formData['height'] ?? '') }}"
                   class="w-full border border-gray-300 rounded px-4 py-2">
        </div>

        {{-- Equipment --}}
        <div class="mb-4">
            <label class="block font-semibold mb-1">Equipment:</label>
            <select name="equipment" class="w-full border border-gray-300 rounded px-4 py-2">
                <option value="excavator" {{ old('equipment', $formData['equipment'] ?? '') === 'excavator' ? 'selected' : '' }}>Excavator</option>
                <option value="hand_tools" {{ old('equipment', $formData['equipment'] ?? '') === 'hand_tools' ? 'selected' : '' }}>Hand Tools</option>
            </select>
        </div>

        {{-- Crew Size --}}
        <div class="mb-4">
            <label class="block font-semibold mb-1">Crew Size:</label>
            <input type="number" name="crew_size" required
                   value="{{ old('crew_size', $formData['crew_size'] ?? '') }}"
                   class="w-full border border-gray-300 rounded px-4 py-2">
        </div>

        {{-- Drive Distance + Speed --}}
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block font-semibold mb-1">Drive Distance (mi):</label>
                <input type="number" step="0.1" name="drive_distance"
                       value="{{ old('drive_distance', $formData['drive_distance'] ?? '') }}"
                       class="w-full border border-gray-300 rounded px-4 py-2">
            </div>
            <div>
                <label class="block font-semibold mb-1">Drive Speed (mph):</label>
                <input type="number" name="drive_speed"
                       value="{{ old('drive_speed', $formData['drive_speed'] ?? '') }}"
                       class="w-full border border-gray-300 rounded px-4 py-2">
            </div>
        </div>

        {{-- Labor Rate --}}
        <div class="mb-4">
            <label class="block font-semibold mb-1">Labor Rate ($/hr):</label>
            <input type="number" step="0.01" name="labor_rate"
                   value="{{ old('labor_rate', $formData['labor_rate'] ?? '') }}"
                   class="w-full border border-gray-300 rounded px-4 py-2">
        </div>

        {{-- Markup --}}
        <div class="mb-4">
            <label class="block font-semibold mb-1">Markup (%):</label>
            <input type="number" step="0.1" name="markup"
                   value="{{ old('markup', $formData['markup'] ?? '') }}"
                   class="w-full border border-gray-300 rounded px-4 py-2">
        </div>

        {{-- Overhead --}}
        <div class="grid grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block font-semibold mb-1">Site Conditions (%):</label>
                <input type="number" step="0.1" name="site_conditions"
                       value="{{ old('site_conditions', $formData['site_conditions'] ?? '') }}"
                       class="w-full border border-gray-300 rounded px-4 py-2">
            </div>
            <div>
                <label class="block font-semibold mb-1">Material Pickup (%):</label>
                <input type="number" step="0.1" name="material_pickup"
                       value="{{ old('material_pickup', $formData['material_pickup'] ?? '') }}"
                       class="w-full border border-gray-300 rounded px-4 py-2">
            </div>
            <div>
                <label class="block font-semibold mb-1">Cleanup (%):</label>
                <input type="number" step="0.1" name="cleanup"
                       value="{{ old('cleanup', $formData['cleanup'] ?? '') }}"
                       class="w-full border border-gray-300 rounded px-4 py-2">
            </div>
        </div>

        {{-- Block Brand --}}
        <div class="mb-4">
            <label class="block font-semibold mb-1">Block Brand:</label>
            <select name="block_brand" class="w-full border border-gray-300 rounded px-4 py-2">
                <option value="belgard" {{ old('block_brand', $formData['block_brand'] ?? '') === 'belgard' ? 'selected' : '' }}>Belgard Diamond Pro</option>
                <option value="techo" {{ old('block_brand', $formData['block_brand'] ?? '') === 'techo' ? 'selected' : '' }}>Techo-Bloc</option>
            </select>
        </div>

        {{-- Include Capstones --}}
        <div class="mb-6">
            <label class="inline-flex items-center">
                <input type="checkbox" name="include_capstones" value="1"
                       {{ old('include_capstones', $formData['include_capstones'] ?? false) ? 'checked' : '' }}
                       class="mr-2">
                <span class="font-semibold">Include Capstones</span>
            </label>
        </div>

        {{-- Submit --}}
        <div>
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded font-semibold">
                {{ $editMode ? '🔁 Recalculate & Save' : '➡️ Calculate Estimate' }}
            </button>
        </div>
    </form>

    {{-- Back Link --}}
    <div class="mt-6">
        <a href="{{ route('clients.show', $clientId) }}"
   class="text-gray-600 hover:text-gray-900 underline">
   🔙 Back to Client
</a>

    </div>
</div>
@endsection

