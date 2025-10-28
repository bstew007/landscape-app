@extends('layouts.sidebar')

@php
    $hasOverrides = collect(old())->keys()->filter(fn($key) => str_contains($key, 'override_'))->isNotEmpty();
@endphp

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

        {{-- Fence Length --}}
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

        {{-- Gates --}}
        <div class="mb-4">
            <label for="gate_4ft" class="block font-semibold mb-1">Number of 4' Gates</label>
            <input type="number" name="gate_4ft" id="gate_4ft" value="{{ old('gate_4ft', 0) }}" min="0" class="form-input w-full">
        </div>
        <div class="mb-4">
            <label for="gate_5ft" class="block font-semibold mb-1">Number of 5' Gates</label>
            <input type="number" name="gate_5ft" id="gate_5ft" value="{{ old('gate_5ft', 0) }}" min="0" class="form-input w-full">
        </div>

        {{-- Wood Specific Options --}}
        <div id="wood-options" style="display: none;">
            <div class="mb-4">
                <label for="picket_spacing" class="block font-semibold mb-1">Picket Spacing (inches)</label>
                <input type="number" step="0.01" name="picket_spacing" id="picket_spacing" value="{{ old('picket_spacing', 0.25) }}" class="form-input w-full">
            </div>
            <div class="mb-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="shadow_box" value="1" {{ old('shadow_box') ? 'checked' : '' }} class="form-checkbox">
                    <span class="ml-2">Shadow Box Style (Double Pickets)</span>
                </label>
            </div>
        </div>

        {{-- Vinyl Specific Options --}}
        <div id="vinyl-options" style="display: none;">
            <div class="mb-4">
                <label for="vinyl_corner_posts" class="block font-semibold mb-1">Vinyl Corner Posts</label>
                <input type="number" name="vinyl_corner_posts" id="vinyl_corner_posts" value="{{ old('vinyl_corner_posts', 0) }}" class="form-input w-full">
            </div>
            <div class="mb-4">
                <label for="vinyl_end_posts" class="block font-semibold mb-1">Vinyl End Posts</label>
                <input type="number" name="vinyl_end_posts" id="vinyl_end_posts" value="{{ old('vinyl_end_posts', 0) }}" class="form-input w-full">
            </div>
        </div>


        {{-- Toggle Material Overrides --}}
        <div class="mb-4">
            <label class="inline-flex items-center">
              <input type="checkbox" id="showOverrides" class="form-checkbox" {{ $hasOverrides ? 'checked' : '' }}>


                <span class="ml-2">Show Material Cost Overrides</span>
            </label>
        </div>

        <div id="overrides-section" style="display: none;">
            @include('calculators.fence.partials.overrides')
        </div>

        {{-- Always Show Overhead Inputs --}}
<div class="mb-6">
    @include('calculators.partials.overhead_inputs')
</div>

        {{-- Site Visit --}}
        <input type="hidden" name="site_visit_id" value="{{ $siteVisitId }}">

        <button type="submit" class="bg-green-600 text-white px-6 py-3 rounded hover:bg-green-700">
            ➕ Calculate Fence Estimate
        </button>
    </form>
</div>

{{-- Conditional Display Logic --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fenceTypeSelect = document.getElementById('fence_type');
        const overridesCheckbox = document.getElementById('showOverrides');

        function toggleSections() {
            const type = fenceTypeSelect.value;

            document.getElementById('wood-options').style.display = type === 'wood' ? 'block' : 'none';
            document.getElementById('vinyl-options').style.display = type === 'vinyl' ? 'block' : 'none';
        }

        function toggleFenceSpecificOverrides() {
            const type = fenceTypeSelect.value;

            // Hide all
            document.querySelectorAll('.override-group').forEach(group => {
                group.style.display = 'none';
            });

            // Show wood or vinyl overrides
            if (type === 'wood') {
                document.querySelectorAll('.wood-material').forEach(el => el.style.display = 'block');
            } else if (type === 'vinyl') {
                document.querySelectorAll('.vinyl-material').forEach(el => el.style.display = 'block');
            }
        }

        function toggleOverrideSection() {
            const overridesSection = document.getElementById('overrides-section');
            const shouldShow = overridesCheckbox.checked;
            overridesSection.style.display = shouldShow ? 'block' : 'none';

            if (shouldShow) {
                toggleFenceSpecificOverrides();
            }
        }

        // Initial runs
        toggleSections();
        toggleOverrideSection();

        // Listeners
        fenceTypeSelect.addEventListener('change', function () {
            toggleSections();
            if (overridesCheckbox.checked) {
                toggleFenceSpecificOverrides();
            }
        });

        overridesCheckbox.addEventListener('change', toggleOverrideSection);
    });
</script>
@endpush


@endsection
