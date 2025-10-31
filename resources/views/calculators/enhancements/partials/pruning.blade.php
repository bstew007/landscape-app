<div x-data="{ open: false, palm: false }" class="mb-6 border border-gray-300 rounded-md p-4 bg-white shadow-sm">
    <div class="flex items-center justify-between mb-2">
        <h2 class="text-lg font-bold">🌿 Pruning</h2>
        <button type="button"
                class="text-sm text-blue-600 hover:underline"
                @click="open = !open">
            <span x-show="!open" x-cloak>Show</span>
            <span x-show="open" x-cloak>Hide</span>
        </button>
    </div>

    <div x-show="open" x-transition>
        <div class="mb-4 grid grid-cols-1 lg:grid-cols-3 gap-4">
            @foreach([
                'shearing' => 'Shearing Shrubs',
                'hand_pruning' => 'Hand Pruning Shrubs',
                'ladder_pruning' => 'Ladder Pruning',
                'tree_pruning' => 'Tree Pruning',
                'deadheading' => 'Deadheading Perennials (Sq Ft)',
                'cut_back_grasses' => 'Cut Back Grasses',
                'cut_back_annuals' => 'Cut Back Annuals',
                'hedge_shearing' => 'Shearing Hedges (Face Area Sq Ft)',
            ] as $key => $label)
                <div class="border p-3 rounded bg-gray-50">
                    <label class="block font-semibold mb-1">{{ $label }}</label>
                    <input type="number"
                           name="pruning[{{ $key }}][{{ in_array($key, ['deadheading', 'hedge_shearing']) ? 'sqft' : (in_array($key, ['cut_back_annuals']) ? 'plants' : 'count') }}]"
                           class="form-input w-full mb-2"
                           min="0"
                           placeholder="Qty">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="pruning[{{ $key }}][overgrown]" value="1" class="form-checkbox">
                        <span class="ml-2 text-sm">Overgrown?</span>
                    </label>
                </div>
            @endforeach
        </div>

        {{-- Palm Pruning Toggle --}}
        <div class="mb-4">
            <label class="inline-flex items-center">
                <input type="checkbox" x-model="palm" class="form-checkbox h-5 w-5 text-green-600">
                <span class="ml-2 text-sm font-semibold text-gray-800">Include Palm Pruning?</span>
            </label>
        </div>

        {{-- Palm Pruning Inputs --}}
        <div x-show="palm" x-transition>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                @foreach([
                    'palm_prune_short' => 'Short (Under 8 ft)',
                    'palm_prune_medium' => 'Medium (8–12 ft)',
                    'palm_prune_tall' => 'Tall (12–20 ft)',
                    'palm_prune_extra_tall' => 'Extra Tall (20+ ft or Climbing)',
                    'palm_seed_removal' => 'Seed / Inflorescence Removal',
                    'palm_cleanup_heavy' => 'Debris Cleanup / Haul Prep',
                ] as $key => $label)
                    <div class="border p-3 rounded bg-yellow-50">
                        <label class="block font-semibold mb-1">🌴 {{ $label }}</label>
                        <input type="number"
                               name="pruning[{{ $key }}][palms]"
                               class="form-input w-full"
                               min="0"
                               step="1"
                               placeholder="Palms">
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Cleanup Options --}}
        <div class="mt-6">
            <label class="block font-semibold mb-2">Include Cleanup?</label>
            <select name="pruning[cleanup][method]" class="form-select w-full">
                <option value="auto">Auto (% of pruning time)</option>
                <option value="manual">Manual Hours</option>
            </select>
        </div>

        <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
            <div>
                <label class="block font-semibold">Cleanup % Time (if Auto)</label>
                <input type="number" name="pruning[cleanup][percent_additional_time]" class="form-input w-full" step="1" placeholder="e.g. 15">
            </div>
            <div>
                <label class="block font-semibold">Cleanup Manual Hours (if Manual)</label>
                <input type="number" name="pruning[cleanup][manual_hours]" class="form-input w-full" step="0.1" placeholder="e.g. 2.5">
            </div>
        </div>
    </div>
</div>
