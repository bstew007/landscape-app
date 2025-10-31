<div x-data="{ open: false }" class="mb-6 border border-gray-300 rounded-md p-4 bg-white shadow-sm">
    <div class="flex items-center justify-between mb-2">
        <h2 class="text-lg font-bold">🍂 Mulching</h2>
        <button type="button"
                class="text-sm text-blue-600 hover:underline"
                @click="open = !open">
            <span x-show="!open">Show</span>
            <span x-show="open">Hide</span>
        </button>
    </div>

    <div x-show="open" x-transition>
        <div class="mb-4 grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="border p-3 rounded bg-gray-50">
                <label class="block font-semibold mb-1">Square Footage</label>
                <input type="number" name="mulching[sqft]" class="form-input w-full" min="0">
            </div>

            <div class="border p-3 rounded bg-gray-50">
                <label class="block font-semibold mb-1">Depth (in inches)</label>
                <input type="number" name="mulching[depth_in_inches]" class="form-input w-full" step="0.5" min="0">
            </div>

            <div class="border p-3 rounded bg-gray-50">
                <label class="block font-semibold mb-1">Mulch Type</label>
                <select name="mulching[mulch_type]" class="form-select w-full">
                    <option>Triple Shredded Hardwood</option>
                    <option>Forest Brown</option>
                    <option>Red</option>
                    <option>Pine Fines</option>
                    <option>Big Nuggets</option>
                    <option>Mini Nuggets</option>
                </select>
            </div>

            <div class="border p-3 rounded bg-gray-50">
                <label class="block font-semibold mb-1">Delivery Method</label>
                <select name="mulching[delivery_method]" class="form-select w-full">
                    <option value="wheelbarrow">Wheelbarrow</option>
                    <option value="tractor">Tractor / Loader</option>
                </select>
            </div>

            <div class="border p-3 rounded bg-gray-50">
                <label class="block font-semibold mb-1">Install Type</label>
                <select name="mulching[install_type]" class="form-select w-full">
                    <option value="standard">Standard</option>
                    <option value="heavy">Heavy</option>
                    <option value="refresh">Refresh / Topdress</option>
                </select>
            </div>

            <div class="border p-3 rounded bg-gray-50">
                <label class="block font-semibold mb-1">Override Material Cost (optional)</label>
                <input type="number" name="mulching[override_material_cost_per_cy]" class="form-input w-full" step="0.01">
            </div>
        </div>

        <!-- Bed Edging Options -->
        <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="border p-3 rounded bg-gray-50">
                <label class="block font-semibold mb-1">Include Bed Edging?</label>
                <input type="checkbox" name="mulching[include_bed_edging]" value="1" class="form-checkbox">
            </div>

            <div class="border p-3 rounded bg-gray-50">
                <label class="block font-semibold mb-1">Edging Method</label>
                <select name="mulching[bed_edging_method]" class="form-select w-full">
                    <option value="manual">Manual</option>
                    <option value="mechanical">Mechanical</option>
                </select>
            </div>

            <div class="border p-3 rounded bg-gray-50">
                <label class="block font-semibold mb-1">Edging Length (Linear Feet)</label>
                <input type="number" name="mulching[bed_edging_length_lf]" class="form-input w-full" step="1">
            </div>
        </div>

        <!-- Final Cleanup -->
        <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="border p-3 rounded bg-gray-50">
                <label class="block font-semibold mb-1">Include Final Cleanup?</label>
                <input type="checkbox" name="mulching[include_final_cleanup]" value="1" class="form-checkbox">
            </div>
        </div>
    </div>
</div>

