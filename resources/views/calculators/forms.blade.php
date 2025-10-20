@extends('layouts.sidebar')

@section('content')
<div class="max-w-4xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">🧱 Retaining Wall Calculator</h1>

    <form action="{{ route('calculators.wall.calculate') }}" method="POST" class="bg-white p-6 rounded-lg shadow space-y-6">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="length" class="block font-semibold">Wall Length (ft)</label>
                <input type="number" name="length" id="length" class="w-full border rounded px-3 py-2" step="0.1" required>
            </div>

            <div>
                <label for="height" class="block font-semibold">Wall Height (ft)</label>
                <input type="number" name="height" id="height" class="w-full border rounded px-3 py-2" step="0.1" required>
            </div>

            <div>
                <label for="equipment" class="block font-semibold">Build Method</label>
                <select name="equipment" id="equipment" class="w-full border rounded px-3 py-2" required>
                    <option value="hand">By Hand</option>
                    <option value="excavator">Excavator</option>
                </select>
            </div>

            <div>
                <label for="block_type" class="block font-semibold">Wall System</label>
                <select name="block_type" id="block_type" class="w-full border rounded px-3 py-2" required>
                    <option value="diamond_pro">Diamond Pro</option>
                    <option value="nicolock">Nicolock</option>
                    <option value="techoblock">Techo-Bloc</option>
                </select>
            </div>

            <div>
                <label for="crew_size" class="block font-semibold">Crew Size</label>
                <input type="number" name="crew_size" id="crew_size" class="w-full border rounded px-3 py-2" value="3" min="1" required>
            </div>

            <div>
                <label for="drive_distance" class="block font-semibold">Drive Distance (miles)</label>
                <input type="number" name="drive_distance" id="drive_distance" class="w-full border rounded px-3 py-2" step="0.1" value="10" required>
            </div>

            <div>
                <label for="drive_speed" class="block font-semibold">Avg Drive Speed (mph)</label>
                <input type="number" name="drive_speed" id="drive_speed" class="w-full border rounded px-3 py-2" step="1" value="35" required>
            </div>

            <div>
                <label for="labor_rate" class="block font-semibold">Labor Rate ($/hr)</label>
                <input type="number" name="labor_rate" id="labor_rate" class="w-full border rounded px-3 py-2" value="74" step="0.01" required>
            </div>

            <div>
                <label for="markup" class="block font-semibold">Markup (%)</label>
                <input type="number" name="markup" id="markup" class="w-full border rounded px-3 py-2" value="15" step="1" required>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mt-4">
            <div>
                <label for="site_conditions" class="block font-semibold">Site Conditions (+%)</label>
                <input type="number" name="site_conditions" id="site_conditions" class="w-full border rounded px-3 py-2" value="0" step="1">
            </div>

            <div>
                <label for="material_pickup" class="block font-semibold">Material Pickup (+%)</label>
                <input type="number" name="material_pickup" id="material_pickup" class="w-full border rounded px-3 py-2" value="0" step="1">
            </div>

            <div>
                <label for="cleanup" class="block font-semibold">Cleanup (+%)</label>
                <input type="number" name="cleanup" id="cleanup" class="w-full border rounded px-3 py-2" value="0" step="1">
            </div>
        </div>

        <div class="pt-6">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded">
                🧮 Calculate Estimate
            </button>
        </div>
    </form>
</div>
@endsection
