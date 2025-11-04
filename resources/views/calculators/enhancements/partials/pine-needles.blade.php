<div 
    x-data="{
        open: true,
        pineSqft: {{ old('pine_needles.sqft', $formData['pine_needles']['sqft'] ?? 0) }},
        depthInches: {{ old('pine_needles.depth_in_inches', $formData['pine_needles']['depth_in_inches'] ?? 2) }},
        get estimatedBales() {
            const sqftPerBaleAt2Inches = 45;
            return (this.pineSqft / sqftPerBaleAt2Inches) * (this.depthInches / 2);
        },
        applyToTask() {
            const input = document.querySelector('input[name=\'pine_needles[pine_needles_open_area][bales]\']');
            if (input) input.value = Math.round(this.estimatedBales);
        }
    }"
    class="mb-6 border border-gray-300 rounded-md p-4 bg-white shadow-sm"
>
    <div class="flex items-center justify-between mb-2">
        <h2 class="text-lg font-bold">🍁 Pine Needles</h2>
        <button type="button"
                class="text-sm text-blue-600 hover:underline"
                @click="open = !open">
            <span x-show="!open" x-cloak>Show</span>
            <span x-show="open" x-cloak>Hide</span>
        </button>
    </div>

    <div x-show="open" x-transition>
        {{-- Estimator Inputs --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block font-semibold mb-1">Approximate Area (sq ft)</label>
                <input type="number"
                       name="pine_needles[sqft]"
                       x-model.number="pineSqft"
                       value="{{ old('pine_needles.sqft', $formData['pine_needles']['sqft'] ?? '') }}"
                       class="form-input w-full"
                       min="0"
                       step="1"
                       placeholder="e.g. 500">
            </div>
            <div>
                <label class="block font-semibold mb-1">Depth (inches)</label>
                <input type="number"
                       name="pine_needles[depth_in_inches]"
                       x-model.number="depthInches"
                       value="{{ old('pine_needles.depth_in_inches', $formData['pine_needles']['depth_in_inches'] ?? 2) }}"
                       class="form-input w-full"
                       step="0.1"
                       min="0"
                       placeholder="e.g. 2">
            </div>
            <div class="flex items-end">
                <button type="button"
                        @click="applyToTask"
                        class="bg-green-600 text-white px-4 py-2 rounded shadow hover:bg-green-700 w-full">
                    📥 Apply to Open Area Task
                </button>
            </div>
        </div>

        {{-- Live Output Display --}}
        <div class="text-sm text-gray-700 mb-4" x-show="pineSqft > 0">
            🧮 Estimated Bales Needed:
            <span class="font-semibold text-green-700" x-text="estimatedBales.toFixed(1)"></span>
            (from <span x-text="pineSqft"></span> sqft @ <span x-text="depthInches"></span>" depth)
        </div>

        {{-- Labor Task Inputs --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            @foreach([
                'pine_needles_open_area' => 'Open Areas',
                'pine_needles_around_plants' => 'Around Plants / Beds',
                'pine_needles_heavy_prep' => 'Heavy Prep / Re-Edge',
                'pine_needles_refresh_light' => 'Refresh / Touch-Up',
                'pine_needles_delivery_stage' => 'Delivery & Staging',
                'pine_needles_cleanup_final' => 'Final Cleanup / Detailing',
            ] as $key => $label)
                <div class="border p-3 rounded bg-gray-50">
                    <label class="block font-semibold mb-1">Pine Needles – {{ $label }}</label>
                    <input type="number"
                           name="pine_needles[{{ $key }}][bales]"
                           class="form-input w-full"
                           min="0"
                           step="1"
                           placeholder="Bales"
                           value="{{ old('pine_needles.' . $key . '.bales', $formData['pine_needles'][$key]['bales'] ?? '') }}">
                </div>
            @endforeach
        </div>

        {{-- Material Cost Override --}}
        <div class="mt-4 border p-3 rounded bg-gray-50">
            <label class="block font-semibold mb-1">Override Material Cost per Bale (optional)</label>
            <input type="number"
                   name="pine_needles[cost_per_bale]"
                   class="form-input w-full"
                   step="0.01"
                   placeholder="e.g. 6.00"
                   value="{{ old('pine_needles.cost_per_bale', $formData['pine_needles']['cost_per_bale'] ?? '') }}">
        </div>
    </div>
</div>

