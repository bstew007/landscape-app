<div x-data="{ open: false }" class="mb-6 border border-gray-300 rounded-md p-4 bg-white shadow-sm">
    <div class="flex items-center justify-between mb-2">
        <h2 class="text-lg font-bold">🍁 Pine Needles</h2>
        <button type="button"
                class="text-sm text-blue-600 hover:underline"
                @click="open = !open">
            <span x-show="!open">Show</span>
            <span x-show="open">Hide</span>
        </button>
    </div>

    <div x-show="open" x-transition>
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
                           placeholder="Bales">
                </div>
            @endforeach
        </div>
    </div>
</div>

