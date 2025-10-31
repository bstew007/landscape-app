<div x-data="{ open: false }" class="mb-6 border border-gray-300 rounded-md p-4 bg-white shadow-sm">
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
            <!-- Hand Weeding -->
            <div class="border p-3 rounded bg-gray-50">
                <label class="block font-semibold mb-1">Hand Weeding – Light</label>
                <input type="number" name="weeding[hand_weeding_light][sqft]" class="form-input w-full" placeholder="Sq Ft" min="0">
            </div>

            <div class="border p-3 rounded bg-gray-50">
                <label class="block font-semibold mb-1">Hand Weeding – Heavy</label>
                <input type="number" name="weeding[hand_weeding_heavy][sqft]" class="form-input w-full" placeholder="Sq Ft" min="0">
            </div>

            <div class="border p-3 rounded bg-gray-50">
                <label class="block font-semibold mb-1">Hand Weeding – Natural Areas</label>
                <input type="number" name="weeding[hand_weeding_natural_areas][sqft]" class="form-input w-full" placeholder="Sq Ft" min="0">
            </div>

            <!-- Spraying -->
            <div class="border p-3 rounded bg-gray-50">
                <label class="block font-semibold mb-1">Spot Spraying – Ornamental Beds</label>
                <input type="number" name="weeding[spray_spot_beds][sqft]" class="form-input w-full" placeholder="Sq Ft" min="0">
            </div>

            <div class="border p-3 rounded bg-gray-50">
                <label class="block font-semibold mb-1">Broadcast Spraying – Ornamental Beds</label>
                <input type="number" name="weeding[spray_broadcast_beds][sqft]" class="form-input w-full" placeholder="Sq Ft" min="0">
            </div>

            <div class="border p-3 rounded bg-gray-50">
                <label class="block font-semibold mb-1">Spot Spraying – Natural Areas</label>
                <input type="number" name="weeding[spray_spot_natural_areas][sqft]" class="form-input w-full" placeholder="Sq Ft" min="0">
            </div>

            <!-- Trimming -->
            <div class="border p-3 rounded bg-gray-50">
                <label class="block font-semibold mb-1">Weed-Eating – Bed Edge Control</label>
                <input type="number" name="weeding[weed_eat_bed_edges][lf]" class="form-input w-full" placeholder="Linear Feet" min="0">
            </div>

            <div class="border p-3 rounded bg-gray-50">
                <label class="block font-semibold mb-1">Weed-Eating – Naturalized / Rough Areas</label>
                <input type="number" name="weeding[weed_eat_natural_areas][sqft]" class="form-input w-full" placeholder="Sq Ft" min="0">
            </div>
        </div>
    </div>
</div>
