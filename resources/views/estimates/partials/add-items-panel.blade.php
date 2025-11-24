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

                        <div x-show="addItemsTab==='labor'" class="bg-white rounded-lg border p-4 space-y-4" x-data="{ showLaborForm: false }">
                <div class="flex items-center justify-between">
                    <h4 class="text-md font-semibold">Labor Catalog</h4>
                    <x-brand-button type="button" size="sm" @click="showLaborForm = true">New Labor</x-brand-button>
                </div>
                
                                                <!-- Labor Create Form Overlay -->
                <div x-show="showLaborForm" 
                     class="fixed inset-0 z-50 flex items-center justify-center p-2" 
                     style="display: none;"
                     @keydown.escape.window="showLaborForm = false">
                    <div class="absolute inset-0 bg-black/50" @click="showLaborForm = false"></div>
                    <div class="relative bg-white rounded-lg shadow-2xl w-full max-w-7xl h-[98vh] flex flex-col">
                        <div class="flex items-center justify-between px-4 py-3 border-b bg-gray-50">
                            <h3 class="text-lg font-semibold">New Labor Item</h3>
                            <button type="button" class="text-gray-500 hover:text-gray-700" @click="showLaborForm = false">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <div class="flex-1 overflow-hidden">
                            <iframe 
                                x-show="showLaborForm"
                                :src="showLaborForm ? '{{ route('labor.create') }}?modal=1' : ''"
                                class="w-full h-full border-0"
                                @load="$event.target.contentWindow.postMessage({type: 'labor:modal-mode'}, '*')"
                            ></iframe>
                        </div>
                    </div>
                </div>
                <div class="max-h-96 overflow-y-auto border rounded bg-white divide-y">
                    @foreach ($laborCatalog as $labor)
                        @php 
                            $wage = (float) ($labor->average_wage ?? 0);
                            $otMult = max(1, (float) ($labor->overtime_factor ?? 1));
                            $burdenPct = max(0, (float) ($labor->labor_burden_percentage ?? 0));
                            $unbillPct = min(99.9, max(0, (float) ($labor->unbillable_percentage ?? 0)));
                            $effectiveWage = $wage * $otMult;
                            $costPerHour = $effectiveWage * (1 + ($burdenPct / 100));
                            $billableFraction = max(0.01, 1 - ($unbillPct / 100));
                            $overheadRate = 0; // You may want to pass this from controller
                            $breakeven = ($costPerHour / $billableFraction) + $overheadRate;
                            $rate = $labor->base_rate ?? $breakeven;
                        @endphp
                        <div class="px-3 py-2 text-sm flex items-center justify-between gap-4">
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">{{ $labor->name }}</div>
                                <div class="text-xs text-gray-500">{{ ucfirst($labor->type) }} · {{ $labor->unit }}</div>
                            </div>
                            <div class="flex flex-col items-end text-right gap-1">
                                <div class="text-xs text-gray-600">Cost/Hr: ${{ number_format($costPerHour, 2) }}</div>
                                <button type="button"
                                        class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-brand-600 text-white hover:bg-brand-700 transition"
                                        data-action="drawer-add"
                                        data-item-type="labor"
                                        data-catalog-id="{{ $labor->id }}"
                                        data-catalog-name="{{ $labor->name }}"
                                        data-catalog-unit="{{ $labor->unit }}"
                                        data-catalog-cost="{{ number_format($costPerHour, 2, '.', '') }}">
                                    + Add
                                </button>
                            </div>
                        </div>
                    @endforeach
                    @if($laborCatalog->isEmpty())
                        <div class="px-3 py-3 text-sm text-gray-500">No labor items yet.</div>
                    @endif
                </div>
                
                <!-- Form removed - using list-based add with + Add buttons -->
                <form method="POST" action="{{ route('estimates.items.store', $estimate) }}" class="space-y-3" id="laborCatalogForm" data-form-type="labor" style="display:none;">
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
