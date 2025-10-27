@extends('layouts.sidebar')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">
    <h1 class="text-3xl font-bold mb-6">🛠️ Fence Calculator</h1>

    <form method="POST" action="{{ route('calculators.fence.calculate') }}">

        @csrf

        <input type="hidden" name="site_visit_id" value="{{ $siteVisitId }}">

        {{-- Fence Type --}}
        <div class="mb-4">
            <label for="fence_type" class="block font-semibold">Fence Type</label>
            <select name="fence_type" id="fence_type" class="form-select mt-1 block w-full" required>
                <option value="wood">Wood (Stick Built)</option>
                <option value="vinyl">Vinyl (Panel)</option>
            </select>
        </div>

        {{-- Height --}}
        <div class="mb-4">
            <label for="height" class="block font-semibold">Fence Height (ft)</label>
            <select name="height" id="height" class="form-select mt-1 block w-full" required>
                <option value="4">4'</option>
                <option value="6">6'</option>
            </select>
        </div>

        {{-- Total Length --}}
        <div class="mb-4">
            <label for="length" class="block font-semibold">Total Fence Length (ft)</label>
            <input type="number" name="length" id="length" class="form-input mt-1 block w-full" step="0.1" required>
        </div>

        {{-- Gate Inputs --}}
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label for="gate_4ft" class="block font-semibold">4' Gates</label>
                <input type="number" name="gate_4ft" id="gate_4ft" class="form-input mt-1 block w-full" min="0" value="0">
            </div>
            <div>
                <label for="gate_5ft" class="block font-semibold">5' Gates</label>
                <input type="number" name="gate_5ft" id="gate_5ft" class="form-input mt-1 block w-full" min="0" value="0">
            </div>
        </div>

        {{-- Vinyl Specific Inputs --}}
        <div class="mb-4" id="vinyl-options">
            <label class="block font-semibold">For Vinyl Fences</label>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label for="vinyl_corner_posts" class="block text-sm">Corner Posts</label>
                    <input type="number" name="vinyl_corner_posts" id="vinyl_corner_posts" class="form-input mt-1 block w-full" value="0">
                </div>
                <div>
                    <label for="vinyl_end_posts" class="block text-sm">End Posts</label>
                    <input type="number" name="vinyl_end_posts" id="vinyl_end_posts" class="form-input mt-1 block w-full" value="0">
                </div>
            </div>
        </div>

        {{-- Wood Specific Inputs --}}
        <div class="mb-4" id="wood-options">
            <label class="block font-semibold">For Wood Fences</label>
            <div class="mb-2">
                <label for="picket_spacing" class="block text-sm">Picket Spacing (inches)</label>
                <input type="number" name="picket_spacing" id="picket_spacing" class="form-input mt-1 block w-full" value="0.25" step="0.01">
            </div>
            <div class="flex items-center space-x-2">
                <input type="checkbox" name="shadow_box" id="shadow_box" class="form-checkbox">
                <label for="shadow_box" class="text-sm">Shadow Box (Pickets on both sides)</label>
            </div>
        </div>

        {{-- Submit --}}
        <div class="mt-6">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">
                🚀 Calculate Estimate
            </button>
        </div>
    </form>
</div>

{{-- Optional JavaScript to toggle wood/vinyl sections --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fenceType = document.getElementById('fence_type');
        const woodOptions = document.getElementById('wood-options');
        const vinylOptions = document.getElementById('vinyl-options');

        function toggleFenceOptions() {
            const isWood = fenceType.value === 'wood';
            woodOptions.style.display = isWood ? 'block' : 'none';
            vinylOptions.style.display = isWood ? 'none' : 'block';
        }

        fenceType.addEventListener('change', toggleFenceOptions);
        toggleFenceOptions(); // Initialize on page load
    });
</script>
@endsection
