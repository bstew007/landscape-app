<div x-data="{ open: true }" class="mb-6 border border-gray-300 rounded-md p-4 bg-white shadow-sm">
    <div class="flex items-center justify-between mb-2">
        <h2 class="text-lg font-bold">🌱 Weeding</h2>
        <button type="button"
                class="text-sm text-blue-600 hover:underline"
                @click="open = !open">
            <span x-show="!open">Show</span>
            <span x-show="open">Hide</span>
        </button>
    </div>

    <div x-show="open" x-transition>
        <div class="mb-4 grid grid-cols-1 lg:grid-cols-3 gap-4">
            @foreach([
                ['hand_weeding_light', 'Hand Weeding – Light', 'sqft'],
                ['hand_weeding_heavy', 'Hand Weeding – Heavy', 'sqft'],
                ['hand_weeding_natural_areas', 'Hand Weeding – Natural Areas', 'sqft'],

                ['spray_spot_beds', 'Spot Spraying – Ornamental Beds', 'sqft'],
                ['spray_broadcast_beds', 'Broadcast Spraying – Ornamental Beds', 'sqft'],
                ['spray_spot_natural_areas', 'Spot Spraying – Natural Areas', 'sqft'],

                ['weed_eat_bed_edges', 'Weed-Eating – Bed Edge Control', 'lf'],
                ['weed_eat_natural_areas', 'Weed-Eating – Naturalized / Rough Areas', 'sqft'],
            ] as [$key, $label, $unit])
                <div class="border p-3 rounded bg-gray-50">
                    <label class="block font-semibold mb-1">{{ $label }}</label>
                    <input 
                        type="number" 
                        name="weeding[{{ $key }}][{{ $unit }}]" 
                        class="form-input w-full" 
                        placeholder="{{ ucfirst($unit) }}" 
                        min="0"
                        value="{{ old("weeding.$key.$unit", $formData['weeding'][$key][$unit] ?? '') }}">
                </div>
            @endforeach
        </div>
    </div>
</div>

