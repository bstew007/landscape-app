<!-- Add Items Slide-over Panel (Labor, Materials only) -->
<div x-show="showAddItems" class="fixed inset-0 z-40" style="display: none;">
    <div class="absolute inset-0 bg-black/30" @click="showAddItems = false"></div>
    <div class="absolute right-0 top-0 h-full w-full sm:max-w-xl bg-white shadow-xl flex flex-col">
        <div class="flex items-center justify-between px-4 py-3 border-b">
            <h3 class="text-lg font-semibold">Add Items</h3>
            <button class="text-gray-500 hover:text-gray-700" @click="showAddItems = false">Close</button>
        </div>
        <div class="p-4 overflow-y-auto space-y-6">
            <div class="mb-3">
                <div class="flex flex-wrap gap-2">
                    <button type="button" class="px-3 py-1 text-sm rounded border border-transparent hover:bg-brand-50" :class="{ 'bg-brand-600 text-white border-brand-600': addItemsTab==='labor' }" @click="addItemsTab='labor'">Labor</button>
                    <button type="button" class="px-3 py-1 text-sm rounded border border-transparent hover:bg-brand-50" :class="{ 'bg-brand-600 text-white border-brand-600': addItemsTab==='materials' }" @click="addItemsTab='materials'">Materials</button>
                </div>
            </div>

            <div x-show="addItemsTab==='materials'" class="bg-white rounded-lg border p-4 space-y-4">
                <h4 class="text-md font-semibold">Add Material from Catalog</h4>
                <form method="POST" action="{{ route('estimates.items.store', $estimate) }}" class="space-y-3" id="materialCatalogForm" data-form-type="material">
                    @csrf
                    <input type="hidden" name="item_type" value="material">
                    <input type="hidden" name="catalog_type" value="material">
                    <input type="hidden" name="stay_in_add_items" value="1">
                    <input type="hidden" name="add_items_tab" value="materials">
                    <input type="hidden" name="area_id" data-role="add-items-area-id">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Material</label>
                        <input type="text" class="form-input w-full mb-2 text-sm border-brand-300 focus:ring-brand-500 focus:border-brand-500" placeholder="Search materials..." data-role="filter">
                        <select name="catalog_id" class="form-select w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" data-role="material-select">
                            <option value="">Select material</option>
                            @foreach ($materials as $material)
                                <option value="{{ $material->id }}"
                                        data-unit="{{ $material->unit }}"
                                        data-cost="{{ $material->unit_cost }}"
                                        data-tax="{{ $material->is_taxable ? $material->tax_rate : 0 }}">
                                    {{ $material->name }} ({{ $material->unit }} @ ${{ number_format($material->unit_cost, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold mb-1">Quantity</label>
                            <input type="number" step="0.01" min="0" name="quantity" class="form-input w-full border-brand-300 focus:ring-brand-500 focus-border-brand-500" value="1" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1">Unit Cost ($)</label>
                            <input type="number" step="0.01" min="0" name="unit_cost" class="form-input w-full border-brand-300 focus:ring-brand-500 focus-border-brand-500" value="0" required data-role="material-cost">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold mb-1">Margin %</label>
                            <input type="number" step="0.1" min="-99" class="form-input w-full border-brand-300 focus:ring-brand-500 focus-border-brand-500" value="{{ number_format($defaultMarginPercent ?? 20, 1) }}" data-role="margin-percent">
                            <input type="hidden" name="margin_rate" value="{{ number_format($defaultMarginRate ?? 0.2, 4) }}" data-role="margin-rate">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1">Unit Price ($)</label>
                            <input type="number" step="0.01" min="0" name="unit_price" class="form-input w-full border-brand-300 focus:ring-brand-500 focus-border-brand-500" value="0" data-role="unit-price">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold mb-1">Unit Label</label>
                            <input type="text" name="unit" class="form-input w-full border-brand-300 focus:ring-brand-500 focus-border-brand-500" value="" data-role="material-unit">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1">Tax Rate</label>
                            <input type="number" step="0.001" min="0" name="tax_rate" class="form-input w-full border-brand-300 focus:ring-brand-500 focus-border-brand-500" value="0" data-role="material-tax">
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <x-brand-button type="submit" disabled>Add Material</x-brand-button>
                        <span class="text-xs text-gray-500" data-role="preview-total">Line total: $0.00</span>
                    </div>
                    @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    @error('unit_cost')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
                </form>
            </div>

            <div x-show="addItemsTab==='labor'" class="bg-white rounded-lg border p-4 space-y-4">
                <h4 class="text-md font-semibold">Add Labor from Catalog</h4>
                <form method="POST" action="{{ route('estimates.items.store', $estimate) }}" class="space-y-3" id="laborCatalogForm" data-form-type="labor">
                    @csrf
                    <input type="hidden" name="item_type" value="labor">
                    <input type="hidden" name="catalog_type" value="labor">
                    <input type="hidden" name="stay_in_add_items" value="1">
                    <input type="hidden" name="add_items_tab" value="labor">
                    <input type="hidden" name="area_id" data-role="add-items-area-id">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Labor</label>
                        <input type="text" class="form-input w-full mb-2 text-sm border-brand-300 focus:ring-brand-500 focus-border-brand-500" placeholder="Search labor..." data-role="filter">
                        <select name="catalog_id" class="form-select w-full border-brand-300 focus:ring-brand-500 focus-border-brand-500" data-role="labor-select">
                            <option value="">Select labor</option>
                            @foreach ($laborCatalog as $labor)
                                @php $rate = $labor->average_wage ?? $labor->base_rate; @endphp
                                <option value="{{ $labor->id }}"
                                        data-unit="{{ $labor->unit }}"
                                        data-cost="{{ $rate }}">
                                    {{ $labor->name }} ({{ ucfirst($labor->type) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold mb-1">Quantity</label>
                            <input type="number" step="0.01" min="0" name="quantity" class="form-input w-full border-brand-300 focus:ring-brand-500 focus-border-brand-500" value="1" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1">Unit Cost ($)</label>
                            <input type="number" step="0.01" min="0" name="unit_cost" class="form-input w-full border-brand-300 focus:ring-brand-500 focus-border-brand-500" value="0" required data-role="labor-cost">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold mb-1">Margin %</label>
                            <input type="number" step="0.1" min="-99" class="form-input w-full border-brand-300 focus:ring-brand-500 focus-border-brand-500" value="{{ number_format($defaultMarginPercent ?? 20, 1) }}" data-role="margin-percent">
                            <input type="hidden" name="margin_rate" value="{{ number_format($defaultMarginRate ?? 0.2, 4) }}" data-role="margin-rate">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1">Unit Price ($)</label>
                            <input type="number" step="0.01" min="0" name="unit_price" class="form-input w-full border-brand-300 focus:ring-brand-500 focus-border-brand-500" value="0" data-role="unit-price">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold mb-1">Unit Label</label>
                            <input type="text" name="unit" class="form-input w-full border-brand-300 focus:ring-brand-500 focus-border-brand-500" value="" data-role="labor-unit">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1">Tax Rate</label>
                            <input type="number" step="0.001" min="0" name="tax_rate" class="form-input w-full border-brand-300 focus:ring-brand-500 focus-border-brand-500" value="0">
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <x-brand-button type="submit" disabled>Add Labor</x-brand-button>
                        <span class="text-xs text-gray-500" data-role="preview-total">Line total: $0.00</span>
                    </div>
                    @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    @error('unit_cost')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
                </form>
            </div>
            </div>



        </div>
    </div>
</div>
