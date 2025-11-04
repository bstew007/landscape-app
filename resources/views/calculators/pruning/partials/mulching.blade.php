<div 
    x-data="{
        open: true,
        sqft: {{ old('mulching.sqft', $formData['mulching']['sqft'] ?? 0) }},
        depth: {{ old('mulching.depth_in_inches', $formData['mulching']['depth_in_inches'] ?? 2) }},
        estimatedCY: null,
        get cubicYards() {
            return (this.sqft * (this.depth / 12)) / 27;
        },
        applyEstimate() {
            this.estimatedCY = this.cubicYards.toFixed(2);
        }
    }" 
    class="mb-6 border border-gray-300 rounded-md p-4 bg-white shadow-sm"
>
    <div class="flex items-center justify-between mb-2">
        <h2 class="text-lg font-bold">🍂 Mulching</h2>
        <button type="button"
                class="text-sm text-blue-600 hover:underline"
                @click="open = !open">
            <span x-show="!open" x-cloak>Show</span>
            <span x-show="open" x-cloak>Hide</span>
        </button>
    </div>

    <div x-show="open" x-transition>
        {{-- Estimator Section --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
            <div class="border p-3 rounded bg-green-50">
                <label class="block font-semibold mb-1">Square Footage</label>
                <input type="number"
                       name="mulching[sqft]"
                       x-model.number="sqft"
                       value="{{ old('mulching.sqft', $formData['mulching']['sqft'] ?? '') }}"
                       class="form-input w-full"
                       min="0">
            </div>

            <div class="border p-3 rounded bg-green-50">
                <label class="block font-semibold mb-1">Depth (in inches)</label>
                <input type="number"
                       name="mulching[depth_in_inches]"
                       x-model.number="depth"
                       value="{{ old('mulching.depth_in_inches', $formData['mulching']['depth_in_inches'] ?? 2) }}"
                       class="form-input w-full"
                       step="0.5"
                       min="0">
            </div>

            <div class="flex items-end">
                <div class="bg-green-100 text-green-800 p-3 rounded w-full">
                    🧮 Estimated: <span x-text="cubicYards.toFixed(2)"></span> CY
                </div>
            </div>
        </div>

        {{-- Use Estimate Button --}}
        <div class="mb-4">
            <button type="button"
                    @click="applyEstimate"
                    class="bg-blue-100 text-blue-800 px-4 py-2 rounded font-semibold hover:bg-blue-200">
                📥 Use This Estimate
            </button>

            <template x-if="estimatedCY !== null">
                <p class="text-sm mt-2 text-green-700">
                    Applied: <strong x-text="estimatedCY"></strong> CY
                </p>
            </template>

            <input type="hidden" name="mulching[calculated_cy]" :value="estimatedCY">
        </div>

        {{-- Existing Fields --}}
        <div class="mb-4 grid grid-cols-1 lg:grid-cols-3 gap-4">
            {{-- Mulch Type --}}
            <div class="border p-3 rounded bg-gray-50">
                <label class="block font-semibold mb-1">Mulch Type</label>
                <select name="mulching[mulch_type]" class="form-select w-full">
                    @foreach (['Triple Shredded Hardwood', 'Forest Brown', 'Red', 'Pine Fines', 'Big Nuggets', 'Mini Nuggets'] as $type)
                        <option value="{{ $type }}"
                            {{ old('mulching.mulch_type', $formData['mulching']['mulch_type'] ?? '') === $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Delivery Method --}}
            <div class="border p-3 rounded bg-gray-50">
                <label class="block font-semibold mb-1">Delivery Method</label>
                <select name="mulching[delivery_method]" class="form-select w-full">
                    <option value="wheelbarrow" {{ old('mulching.delivery_method', $formData['mulching']['delivery_method'] ?? '') === 'wheelbarrow' ? 'selected' : '' }}>Wheelbarrow</option>
                    <option value="tractor" {{ old('mulching.delivery_method', $formData['mulching']['delivery_method'] ?? '') === 'tractor' ? 'selected' : '' }}>Tractor / Loader</option>
                </select>
            </div>

            {{-- Install Type --}}
            <div class="border p-3 rounded bg-gray-50">
                <label class="block font-semibold mb-1">Install Type</label>
                <select name="mulching[install_type]" class="form-select w-full">
                    <option value="standard" {{ old('mulching.install_type', $formData['mulching']['install_type'] ?? '') === 'standard' ? 'selected' : '' }}>Standard</option>
                    <option value="heavy" {{ old('mulching.install_type', $formData['mulching']['install_type'] ?? '') === 'heavy' ? 'selected' : '' }}>Heavy</option>
                    <option value="refresh" {{ old('mulching.install_type', $formData['mulching']['install_type'] ?? '') === 'refresh' ? 'selected' : '' }}>Refresh / Topdress</option>
                </select>
            </div>

            {{-- Override Material Cost --}}
            <div class="border p-3 rounded bg-gray-50">
                <label class="block font-semibold mb-1">Override Material Cost (optional)</label>
                <input type="number"
                       name="mulching[override_material_cost_per_cy]"
                       value="{{ old('mulching.override_material_cost_per_cy', $formData['mulching']['override_material_cost_per_cy'] ?? '') }}"
                       class="form-input w-full"
                       step="0.01">
            </div>
        </div>

        {{-- Bed Edging --}}
        <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="border p-3 rounded bg-gray-50">
                <label class="block font-semibold mb-1">Include Bed Edging?</label>
                <input type="checkbox"
                       name="mulching[include_bed_edging]"
                       value="1"
                       class="form-checkbox"
                       {{ old('mulching.include_bed_edging', $formData['mulching']['include_bed_edging'] ?? false) ? 'checked' : '' }}>
            </div>

            <div class="border p-3 rounded bg-gray-50">
                <label class="block font-semibold mb-1">Edging Method</label>
                <select name="mulching[bed_edging_method]" class="form-select w-full">
                    <option value="manual" {{ old('mulching.bed_edging_method', $formData['mulching']['bed_edging_method'] ?? '') === 'manual' ? 'selected' : '' }}>Manual</option>
                    <option value="mechanical" {{ old('mulching.bed_edging_method', $formData['mulching']['bed_edging_method'] ?? '') === 'mechanical' ? 'selected' : '' }}>Mechanical</option>
                </select>
            </div>

            <div class="border p-3 rounded bg-gray-50">
                <label class="block font-semibold mb-1">Edging Length (Linear Feet)</label>
                <input type="number"
                       name="mulching[bed_edging_length_lf]"
                       value="{{ old('mulching.bed_edging_length_lf', $formData['mulching']['bed_edging_length_lf'] ?? '') }}"
                       class="form-input w-full"
                       step="1">
            </div>
        </div>

        {{-- Final Cleanup --}}
        <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="border p-3 rounded bg-gray-50">
                <label class="block font-semibold mb-1">Include Final Cleanup?</label>
                <input type="checkbox"
                       name="mulching[include_final_cleanup]"
                       value="1"
                       class="form-checkbox"
                       {{ old('mulching.include_final_cleanup', $formData['mulching']['include_final_cleanup'] ?? false) ? 'checked' : '' }}>
            </div>
        </div>
    </div>
</div>




