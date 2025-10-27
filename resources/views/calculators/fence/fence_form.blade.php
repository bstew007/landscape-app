
@extends('layouts.sidebar')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <h1 class="text-3xl font-bold mb-6">Fence Calculator Form</h1>

    <form method="POST" action="{{ route('fence.calculate') }}">
        @csrf

        {{-- Fence Type --}}
        <div class="mb-4">
            <label for="fence_type" class="block font-semibold mb-1">Fence Type</label>
            <select name="fence_type" id="fence_type" required class="form-select w-full">
                <option value="">-- Select --</option>
                <option value="wood" {{ old('fence_type') == 'wood' ? 'selected' : '' }}>Wood</option>
                <option value="vinyl" {{ old('fence_type') == 'vinyl' ? 'selected' : '' }}>Vinyl</option>
            </select>
        </div>

        {{-- Fence Height --}}
        <div class="mb-4">
            <label for="height" class="block font-semibold mb-1">Fence Height</label>
            <select name="height" id="height" required class="form-select w-full">
                <option value="4" {{ old('height') == '4' ? 'selected' : '' }}>4'</option>
                <option value="6" {{ old('height') == '6' ? 'selected' : '' }}>6'</option>
            </select>
        </div>

        {{-- Total Length --}}
        <div class="mb-4">
            <label for="length" class="block font-semibold mb-1">Total Fence Length (ft)</label>
            <input type="number" name="length" id="length" value="{{ old('length') }}" required class="form-input w-full">
        </div>

        {{-- Dig Method --}}
        <div class="mb-4">
            <label for="dig_method" class="block font-semibold mb-1">Post Digging Method</label>
            <select name="dig_method" id="dig_method" required class="form-select w-full">
                <option value="">-- Select Method --</option>
                <option value="hand" {{ old('dig_method') == 'hand' ? 'selected' : '' }}>Hand Dig</option>
                <option value="auger" {{ old('dig_method') == 'auger' ? 'selected' : '' }}>Auger</option>
            </select>
        </div>

        {{-- Gate Count --}}
        <div class="mb-4">
            <label for="gate_4ft" class="block font-semibold mb-1">Number of 4' Gates</label>
            <input type="number" name="gate_4ft" id="gate_4ft" value="{{ old('gate_4ft') ?? 0 }}" min="0" class="form-input w-full">
        </div>

        <div class="mb-4">
            <label for="gate_5ft" class="block font-semibold mb-1">Number of 5' Gates</label>
            <input type="number" name="gate_5ft" id="gate_5ft" value="{{ old('gate_5ft') ?? 0 }}" min="0" class="form-input w-full">
        </div>

        {{-- Vinyl Specific --}}
        <div class="mb-4">
            <label for="vinyl_corner_posts" class="block font-semibold mb-1">Vinyl Corner Posts</label>
            <input type="number" name="vinyl_corner_posts" id="vinyl_corner_posts" value="{{ old('vinyl_corner_posts') ?? 0 }}" min="0" class="form-input w-full">
        </div>

        <div class="mb-4">
            <label for="vinyl_end_posts" class="block font-semibold mb-1">Vinyl End Posts</label>
            <input type="number" name="vinyl_end_posts" id="vinyl_end_posts" value="{{ old('vinyl_end_posts') ?? 0 }}" min="0" class="form-input w-full">
        </div>

        {{-- Wood Options --}}
        <div class="mb-4">
            <label for="picket_spacing" class="block font-semibold mb-1">Picket Spacing (in inches)</label>
            <input type="number" step="0.01" name="picket_spacing" id="picket_spacing" value="{{ old('picket_spacing', 0.25) }}" class="form-input w-full">
        </div>

        <div class="mb-4">
            <label class="inline-flex items-center">
                <input type="checkbox" name="shadow_box" value="1" {{ old('shadow_box') ? 'checked' : '' }} class="form-checkbox">
                <span class="ml-2">Shadow Box Style (Double Pickets)</span>
            </label>
        </div>

        {{-- Override Material Costs (examples shown, you can add more as needed) --}}
        <div class="mb-4">
            <label class="block font-semibold mb-1">Override Wood 4x4 Post Cost</label>
            <input type="number" step="0.01" name="override_wood_post_4x4_cost" class="form-input w-full" value="{{ old('override_wood_post_4x4_cost') }}">
        </div>

        {{-- Labor Inputs --}}
        <h2 class="text-lg font-semibold mt-6 mb-2">Labor Inputs</h2>
        <div class="mb-4">
            <label for="labor_rate" class="block font-semibold mb-1">Labor Rate ($/hr)</label>
            <input type="number" name="labor_rate" id="labor_rate" value="{{ old('labor_rate', 45) }}" step="0.01" class="form-input w-full">
        </div>

        <div class="mb-4">
            <label for="crew_size" class="block font-semibold mb-1">Crew Size</label>
            <input type="number" name="crew_size" id="crew_size" value="{{ old('crew_size', 2) }}" min="1" class="form-input w-full">
        </div>

       

        {{-- Markup --}}
        <div class="mb-4">
            <label for="markup" class="block font-semibold mb-1">Markup Percentage</label>
            <input type="number" name="markup" id="markup" value="{{ old('markup', 20) }}" step="1" class="form-input w-full">
        </div>

        {{-- Site Visit Reference --}}
        <input type="hidden" name="site_visit_id" value="{{ $siteVisitId }}">

        {{-- Submit --}}
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">
            ➕ Calculate Fence Estimate
        </button>
    </form>
</div>
@endsection
