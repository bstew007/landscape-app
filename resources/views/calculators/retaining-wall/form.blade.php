@extends('layouts.sidebar')

@section('content')
<div class="max-w-4xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">🧱 Retaining Wall Calculator</h1>

    {{-- 🔥 Show Validation Errors --}}
    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-4 rounded mb-4">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('calculators.wall.calculate') }}" class="space-y-6">
        @csrf

        {{-- 🚀 Hidden Site Visit ID --}}
        <input type="hidden" name="site_visit_id" value="{{ request('site_visit_id') }}">

        {{-- Length --}}
        <div>
            <label class="block font-semibold mb-1">Wall Length (ft):</label>
            <input type="number" step="0.1" name="length" value="{{ old('length') }}"
                   class="w-full border rounded px-4 py-2">
        </div>

        {{-- Height --}}
        <div>
            <label class="block font-semibold mb-1">Wall Height (ft):</label>
            <input type="number" step="0.1" name="height" value="{{ old('height') }}"
                   class="w-full border rounded px-4 py-2">
        </div>

        {{-- Crew Size --}}
        <div>
            <label class="block font-semibold mb-1">Crew Size:</label>
            <input type="number" name="crew_size" value="{{ old('crew_size', 3) }}"
                   class="w-full border rounded px-4 py-2">
        </div>

        {{-- Equipment --}}
        <div>
            <label class="block font-semibold mb-1">Equipment Used:</label>
            <select name="equipment" class="w-full border rounded px-4 py-2">
                <option value="excavator" {{ old('equipment') == 'excavator' ? 'selected' : '' }}>Excavator</option>
                <option value="manual" {{ old('equipment') == 'manual' ? 'selected' : '' }}>Manual</option>
            </select>
        </div>

        {{-- Block Type --}}
        <div>
            <label class="block font-semibold mb-1">Wall Block Type:</label>
            <select name="block_type" class="w-full border rounded px-4 py-2">
                <option value="versa_lok" {{ old('block_type') == 'versa_lok' ? 'selected' : '' }}>Versa-Lok</option>
                <option value="anchor" {{ old('block_type') == 'anchor' ? 'selected' : '' }}>Anchor</option>
                <option value="keystone" {{ old('block_type') == 'keystone' ? 'selected' : '' }}>Keystone</option>
            </select>
        </div>

        {{-- Drive Distance --}}
        <div>
            <label class="block font-semibold mb-1">Drive Distance (miles):</label>
            <input type="number" step="0.1" name="drive_distance" value="{{ old('drive_distance', 0) }}"
                   class="w-full border rounded px-4 py-2">
        </div>

        {{-- Drive Speed --}}
        <div>
            <label class="block font-semibold mb-1">Average Drive Speed (mph):</label>
            <input type="number" step="1" name="drive_speed" value="{{ old('drive_speed', 35) }}"
                   class="w-full border rounded px-4 py-2">
        </div>

        {{-- Labor Rate --}}
        <div>
            <label class="block font-semibold mb-1">Labor Rate ($/hr):</label>
            <input type="number" step="0.01" name="labor_rate" value="{{ old('labor_rate', 45) }}"
                   class="w-full border rounded px-4 py-2">
        </div>

        {{-- Markup --}}
        <div>
            <label class="block font-semibold mb-1">Markup (%):</label>
            <input type="number" step="1" name="markup" value="{{ old('markup', 20) }}"
                   class="w-full border rounded px-4 py-2">
        </div>

        {{-- Optional Overhead Inputs --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block font-semibold mb-1">Site Conditions (%):</label>
                <input type="number" step="1" name="site_conditions" value="{{ old('site_conditions', 0) }}"
                       class="w-full border rounded px-4 py-2">
            </div>

            <div>
                <label class="block font-semibold mb-1">Material Pickup (%):</label>
                <input type="number" step="1" name="material_pickup" value="{{ old('material_pickup', 0) }}"
                       class="w-full border rounded px-4 py-2">
            </div>

            <div>
                <label class="block font-semibold mb-1">Cleanup (%):</label>
                <input type="number" step="1" name="cleanup" value="{{ old('cleanup', 0) }}"
                       class="w-full border rounded px-4 py-2">
            </div>
        </div>

        {{-- Submit --}}
        <div class="mt-6">
            <button type="submit"
                    class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold shadow">
                🧮 Calculate Estimate
            </button>
        </div>
    </form>
</div>
@endsection
