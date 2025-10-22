<div x-data="{ showMulch: true }" class="border p-4 rounded mb-6">
    <h2 @click="showMulch = !showMulch" class="text-lg font-bold cursor-pointer">
        🌾 Mulching
    </h2>

    <div x-show="showMulch" class="space-y-4 mt-4">

        {{-- Area and Depth --}}
        <div class="grid grid-cols-3 gap-4">

            {{-- Square Footage --}}
            <div>
                <label for="mulching_sqft" class="block text-sm font-medium text-gray-700">Square Footage</label>
                <input type="number" name="mulching[sqft]" id="mulching_sqft" step="1"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-opacity-50" />
            </div>

            {{-- Mulch Depth --}}
            <div>
                <label for="mulching_depth" class="block text-sm font-medium text-gray-700">Mulch Depth (inches)</label>
                <select name="mulching[depth_in_inches]" id="mulching_depth"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-opacity-50">
                    @for ($i = 1; $i <= 6; $i++)
                        <option value="{{ $i }}">{{ $i }}"</option>
                    @endfor
                </select>
            </div>

            {{-- Mulch Type --}}
            <div>
                <label for="mulching_type" class="block text-sm font-medium text-gray-700">Mulch Type</label>
                <select name="mulching[mulch_type]" id="mulching_type"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-opacity-50">
                    <option value="Triple Shredded Hardwood">Triple Shredded Hardwood</option>
                    <option value="Forest Brown">Forest Brown</option>
                    <option value="Red">Red</option>
                    <option value="Pine Fines">Pine Fines</option>
                    <option value="Big Nuggets">Big Nuggets</option>
                    <option value="Mini Nuggets">Mini Nuggets</option>
                </select>
            </div>
        </div>

        {{-- Labor and Method --}}
        <div class="grid grid-cols-3 gap-4">

            {{-- Delivery Method --}}
            <div>
                <label for="mulching_delivery" class="block text-sm font-medium text-gray-700">Delivery Method</label>
                <select name="mulching[delivery_method]" id="mulching_delivery"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-opacity-50">
                    <option value="wheelbarrow_dump">Wheelbarrow + Dump</option>
                    <option value="wheelbarrow_hand">Wheelbarrow + Hand Spread</option>
                    <option value="tractor_rake">Tractor + Rake Out</option>
                </select>
            </div>

            {{-- Labor Rate --}}
            <div>
                <label for="mulching_labor_rate" class="block text-sm font-medium text-gray-700">Labor Rate ($/hr)</label>
                <input type="number" name="mulching[labor_rate]" id="mulching_labor_rate" step="0.01" value="45"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-opacity-50" />
            </div>

            {{-- Crew Size --}}
            <div>
                <label for="mulching_crew_size" class="block text-sm font-medium text-gray-700">Crew Size</label>
                <input type="number" name="mulching[crew_size]" id="mulching_crew_size" step="1" value="2"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-opacity-50" />
            </div>
        </div>

    </div>
</div>
