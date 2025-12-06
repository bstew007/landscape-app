{{--
    ESTIMATE AREA COMPONENT
    
    This component displays a work area within an estimate with its line items.
    
    DATA FLOW:
    1. Area-level calculations (top summary):
       - laborHours: Sum of labor item quantities
       - cogs: Sum of cost_total for labor + materials
       - price: Sum of line_total for all items
       - profit: price - cogs
    
    2. Line item data (from database):
       - unit_cost: For catalog items, this IS the breakeven. For manual items, this is raw cost.
       - unit_price: The selling price (always from database, never calculated here)
       - quantity: Number of units
       - tax_rate: Only used for manual materials to calculate breakeven
    
    3. Line item calculations:
       - breakeven: For catalog items = unit_cost. For manual materials = unit_cost * (1 + tax_rate)
       - profitPercent: (unit_price - breakeven) / unit_price * 100
       - totalCost: unit_cost * quantity
       - totalPrice: unit_price * quantity
       - totalProfit: totalPrice - totalCost
    
    IMPORTANT: Unit prices are NEVER calculated in this view - they come directly from the database.
--}}
@php
    /** @var \App\Models\Estimate $estimate */
    /** @var \App\Models\EstimateArea $area */
    $allItems = $allItems ?? $estimate->items;
    $areaItems = $allItems->where('area_id', $area->id);
    $laborHours = $areaItems->where('item_type', 'labor')->sum('quantity');
    $cogs = $areaItems->filter(fn($i) => in_array($i->item_type, ['labor','material']))->sum('cost_total');
    $price = $areaItems->sum('line_total');
    $profit = $price - $cogs;
    $initiallyOpen = isset($initiallyOpen) ? (bool) $initiallyOpen : false;
@endphp
<div x-data="areaComponent({{ $initiallyOpen ? 'true' : 'false' }}, '{{ route('estimates.areas.clearCustomPricing', [$estimate, $area]) }}', {{ $area->id }})"
     class="border rounded-lg bg-white work-area overflow-visible"
     data-area-id="{{ $area->id }}"
     data-sort-order="{{ $area->sort_order ?? 0 }}">

    <div class="px-3 py-1.5 border-b border-slate-200 bg-slate-100 cursor-pointer"
         @click.self="toggleOpen()">
        <div class="flex flex-wrap items-start gap-1.5">
            {{-- Options Dropdown - Outside of form to avoid scope issues --}}
            <div class="relative inline-block text-left shrink-0" @keydown.escape.stop="menuOpen = false">
                <button type="button" 
                        class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-white text-gray-700 border border-gray-300 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-1" 
                        @click.stop="menuOpen = !menuOpen" 
                        :aria-expanded="menuOpen">
                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="1"/>
                        <circle cx="12" cy="5" r="1"/>
                        <circle cx="12" cy="19" r="1"/>
                    </svg>
                    Options
                    <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
                <div x-cloak x-show="menuOpen" x-transition @click.outside="menuOpen = false"
                     class="absolute z-20 mt-1 min-w-[10rem] left-0 bg-white border border-gray-200 rounded-lg shadow-lg text-sm py-1 ring-1 ring-black/5">
                    <button type="button" 
                            class="flex items-center gap-2 w-full text-left px-3 py-2 text-gray-700 hover:bg-gray-50" 
                            @click="toggleOpen(); menuOpen = false;">
                        <svg class="h-4 w-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 11 12 14 22 4"/>
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                        </svg>
                        <span x-text="open ? 'Close' : 'Open'"></span>
                    </button>
                    <button type="button" 
                            class="flex items-center gap-2 w-full text-left px-3 py-2 text-gray-700 hover:bg-gray-50" 
                            @click="open = true; menuOpen = false;">
                        <svg class="h-4 w-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                        Edit
                    </button>
                    <button type="button" 
                            class="flex items-center gap-2 w-full text-left px-3 py-2 text-gray-700 hover:bg-gray-50" 
                            @click="menuOpen = false; $refs.duplicateForm.submit()">
                        <svg class="h-4 w-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                        </svg>
                        Duplicate
                    </button>
                    <div class="my-1 border-t border-gray-200"></div>
                    
                    {{-- Custom Pricing Options --}}
                    <button type="button" 
                            class="flex items-center gap-2 w-full text-left px-3 py-2 text-brand-700 hover:bg-brand-50" 
                            @click="menuOpen = false; $dispatch('open-custom-pricing', { mode: 'price', areaId: {{ $area->id }}, areaName: '{{ addslashes($area->name) }}', estimateId: {{ $estimate->id }}, currentTotal: {{ $price }}, currentCost: {{ $cogs }} })">
                        <svg class="h-4 w-4 text-brand-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                        Custom Total Price
                    </button>
                    <button type="button" 
                            class="flex items-center gap-2 w-full text-left px-3 py-2 text-brand-700 hover:bg-brand-50" 
                            @click="menuOpen = false; $dispatch('open-custom-pricing', { mode: 'profit', areaId: {{ $area->id }}, areaName: '{{ addslashes($area->name) }}', estimateId: {{ $estimate->id }}, currentTotal: {{ $price }}, currentCost: {{ $cogs }} })">
                        <svg class="h-4 w-4 text-brand-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 3v18h18M18 17V9M13 17V5M8 17v-3"/>
                        </svg>
                        Custom Profit %
                    </button>
                    
                    @if($area->hasCustomPricing())
                        <button type="button" 
                                class="flex items-center gap-2 w-full text-left px-3 py-2 text-amber-700 hover:bg-amber-50" 
                                @click="menuOpen = false; clearCustomPricing()">
                            <svg class="h-4 w-4 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2M10 11v6M14 11v6"/>
                            </svg>
                            Clear Custom Pricing
                        </button>
                    @endif
                    
                    <div class="my-1 border-t border-gray-200"></div>
                    
                    {{-- Export Options --}}
                    <button type="button" 
                            class="flex items-center gap-2 w-full text-left px-3 py-2 text-gray-700 hover:bg-gray-50" 
                            @click="menuOpen = false; exportAreaToCSV()">
                        <svg class="h-4 w-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>
                        </svg>
                        Export to Excel
                    </button>
                    
                    <div class="my-1 border-t border-gray-200"></div>
                    
                    <button type="button" 
                            class="flex items-center gap-2 w-full text-left px-3 py-2 text-red-600 hover:bg-red-50" 
                            @click.prevent="$refs.deleteForm.submit()">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                        </svg>
                        Delete Area
                    </button>
                </div>
            </div>
            
            {{-- Area Update Form --}}
            <form method="POST" action="{{ route('estimates.areas.update', [$estimate, $area]) }}" class="flex flex-wrap items-start gap-1.5 flex-1">
                @csrf
                @method('PATCH')
            <div class="w-16">
                <label class="block text-xs font-medium text-gray-600">Order</label>
                <input type="number" name="sort_order" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="{{ $area->sort_order ?? 0 }}">
            </div>
            <div class="w-full sm:w-72">
                <label class="block text-xs font-medium text-gray-600">Name</label>
                <input type="text" name="name" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="{{ $area->name }}">
            </div>
            <div class="w-28">
                <label class="block text-xs font-medium text-gray-600">Id</label>
                <input type="text" name="identifier" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="{{ $area->identifier }}">
            </div>
            <div class="w-60">
                <label class="block text-xs font-medium text-gray-600">Cost Code</label>
                <select name="cost_code_id" class="form-select w-full border-brand-300 focus:ring-brand-500 focus-border-brand-500">
                    <option value="">—</option>
                    @foreach (($costCodes ?? []) as $cc)
                        <option value="{{ $cc->id }}" @selected($area->cost_code_id === $cc->id)>{{ $cc->code }} - {{ $cc->name }}</option>
                    @endforeach
                </select>
            </div>
        </form>
        <form x-ref="deleteForm" method="POST" action="{{ route('estimates.areas.destroy', [$estimate, $area]) }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
        
        <form x-ref="duplicateForm" method="POST" action="{{ route('estimates.areas.duplicate', [$estimate, $area]) }}" class="hidden">
            @csrf
        </form>
    </div>
    <div x-show="open" class="px-3 py-2">
        <div class="flex flex-wrap items-center gap-1.5 mb-1.5">
            <div class="inline-flex rounded-md border">
                <button type="button" class="px-2.5 py-1.5 text-sm rounded-l-md hover:bg-gray-100 text-gray-700" :class="{ 'bg-gray-200 text-gray-900': tab==='pricing' }" @click="tab='pricing'">
                    <svg class="inline h-4 w-4 mr-1 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit Pricing
                </button>
                <button type="button" class="px-2.5 py-1.5 text-sm rounded-r-md hover:bg-gray-100 text-gray-700 border-l" :class="{ 'bg-gray-200 text-gray-900': tab==='notes' }" @click="tab='notes'">
                    <svg class="inline h-4 w-4 mr-1 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h9l7 7v9a2 2 0 0 1-2 2z"/><path d="M17 21v-8h-6"/></svg>
                    Edit Notes
                </button>
            </div>
        </div>
        <div x-show="tab==='pricing'" class="overflow-x-auto" x-data="{
            selectedItems: [],
            selectAll: false,
            toggleSelectAll() {
                if (this.selectAll) {
                    this.selectedItems = [];
                    this.selectAll = false;
                } else {
                    this.selectedItems = Array.from(document.querySelectorAll('[data-item-id]')).map(el => parseInt(el.dataset.itemId));
                    this.selectAll = true;
                }
            },
            async bulkDelete() {
                if (!this.selectedItems.length) return;
                if (!confirm(`Delete ${this.selectedItems.length} selected item(s)?`)) return;
                
                for (const itemId of this.selectedItems) {
                    const form = document.querySelector(`form[action*='items/${itemId}'][method='POST']`);
                    if (form) {
                        const formData = new FormData(form);
                        await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                    }
                }
                
                if (window.showToast) window.showToast('Items deleted', 'success');
                window.location.reload();
            }
        }">
            <div x-show="selectedItems.length > 0" x-transition class="bg-brand-50 border-b border-brand-200 px-3 py-2 flex items-center justify-between">
                <span class="text-sm font-medium text-brand-900">
                    <span x-text="selectedItems.length"></span> item(s) selected
                </span>
                <div class="flex gap-2">
                    <button type="button" 
                            @click="bulkDelete()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-red-700 bg-white border border-red-300 rounded-md hover:bg-red-50">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Delete Selected
                    </button>
                    <button type="button" 
                            @click="selectedItems = []; selectAll = false"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Clear
                    </button>
                </div>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="text-center px-2.5 py-1.5 w-10">
                            <input type="checkbox" 
                                   :checked="selectAll"
                                   @change="toggleSelectAll()"
                                   class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        </th>
                        <th class="text-left px-2.5 py-1.5">Name</th>
                        <th class="text-center px-2.5 py-1.5">Qty</th>
                        <th class="text-center px-2.5 py-1.5">Units</th>
                        <th class="text-center px-2.5 py-1.5">Unit Cost</th>
                        <th class="text-center px-2.5 py-1.5">Unit Price</th>
                        <th class="text-center px-2.5 py-1.5">Profit %</th>
                        <th class="text-center px-2.5 py-1.5">Total Cost</th>
                        <th class="text-right px-2.5 py-1.5">Total Price</th>
                        <th class="px-2.5 py-1.5"></th>
                    </tr>
                </thead>
                <tbody data-area-items="{{ $area->id }}">
                    @forelse ($areaItems as $item)
                        @php 
                            // ============================================
                            // VALUES FROM DATABASE (NO CALCULATIONS)
                            // ============================================
                            // $item->unit_cost    = Breakeven from catalog OR raw cost for manual items
                            // $item->unit_price   = Selling price (from catalog or manually set)
                            // $item->quantity     = Quantity of units
                            // $item->tax_rate     = Tax rate for material (only for manual materials)
                            // $item->catalog_type = 'material', 'labor', or null for manual items
                            
                            $overheadRate = $overheadRate ?? 0.0;
                            $isMaterial = $item->item_type === 'material';
                            
                            // ============================================
                            // BREAKEVEN CALCULATION
                            // ============================================
                            // For catalog items: unit_cost already IS the breakeven (stored from catalog)
                            // For manual materials with tax: apply tax to unit_cost to get breakeven
                            // For all others: breakeven = unit_cost
                            
                            if ($item->catalog_type === 'material' || $item->catalog_type === 'labor') {
                                $breakeven = $item->unit_cost;  // Already is breakeven from catalog
                            } elseif ($isMaterial && $item->tax_rate > 0) {
                                $breakeven = $item->unit_cost * (1 + $item->tax_rate);  // Manual material with tax
                            } else {
                                $breakeven = $item->unit_cost;  // Everything else
                            }
                            
                            // ============================================
                            // PROFIT CALCULATIONS
                            // ============================================
                            // Profit % = (Price - Breakeven) / Price * 100
                            $profitPercent = $item->unit_price > 0 
                                ? round((($item->unit_price - $breakeven) / $item->unit_price) * 100, 1)
                                : 0.0;
                            
                            // ============================================
                            // TOTALS (Qty × Unit Values)
                            // ============================================
                            $totalCost = $item->unit_cost * $item->quantity;
                            $totalPrice = $item->unit_price * $item->quantity;
                            $totalProfit = $totalPrice - $totalCost;
                        @endphp
                        <tr class="border-t"
                            data-item-id="{{ $item->id }}"
                            data-item-type="{{ $item->item_type }}"
                            data-area-id="{{ $area->id }}"
                            data-quantity="{{ $item->quantity }}"
                            data-unit-cost="{{ $item->unit_cost }}"
                            data-overhead-rate="{{ $overheadRate }}"
                            data-tax-rate="{{ $item->tax_rate }}"
                            data-catalog-type="{{ $item->catalog_type ?? '' }}"
                            data-catalog-id="{{ $item->catalog_id ?? '' }}"
                            id="estimate-item-{{ $item->id }}"
                            x-data="lineItemCalculator({
                                itemType: '{{ $item->item_type }}',
                                unitCost: {{ $item->unit_cost }},
                                unitPrice: {{ $item->unit_price }},
                                quantity: {{ $item->quantity }},
                                overheadRate: {{ $overheadRate }},
                                taxRate: {{ $item->tax_rate }},
                                breakeven: {{ $breakeven }}
                            })"
>
                            <td class="px-2.5 py-1.5 text-center">
                                <input type="checkbox" 
                                       :checked="selectedItems.includes({{ $item->id }})"
                                       @change="selectedItems.includes({{ $item->id }}) ? selectedItems = selectedItems.filter(id => id !== {{ $item->id }}) : selectedItems.push({{ $item->id }})"
                                       data-item-id="{{ $item->id }}"
                                       class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            </td>
                            <td class="px-2.5 py-1.5">
                                <form method="POST" 
                                      action="{{ route('estimates.items.update', [$estimate, $item]) }}" 
                                      class="contents"
                                      @submit.prevent="
                                          const formData = new FormData($el);
                                          fetch($el.action, {
                                              method: 'POST',
                                              body: formData,
                                              headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                          }).then(res => res.json()).then(data => {
                                              if (window.updateSummary && data.totals) window.updateSummary(data.totals);
                                              if (window.showToast) window.showToast('Item updated', 'success');
                                              window.dispatchEvent(new CustomEvent('form-saved'));
                                          }).catch(err => {
                                              if (window.showToast) window.showToast('Update failed', 'error');
                                          });
                                      ">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="area_id" value="{{ $area->id }}">
                                    <input type="text" name="name" class="form-input w-full border-brand-300 focus:ring-brand-500 focus-border-brand-500" value="{{ $item->name }}">
                            </td>
                            <td class="px-2.5 py-1.5 text-center">
                                    <div x-data="{ saving: false }" class="relative">
                                        <input type="number" step="0.01" min="0" name="quantity" 
                                               class="form-input w-24 mx-auto border-brand-300 focus:ring-brand-500 focus-border-brand-500" 
                                               value="{{ $item->quantity }}"
                                               @input="window.dispatchEvent(new CustomEvent('form-changed'))"
                                               @blur="
                                                   if ($el.value !== '{{ $item->quantity }}') {
                                                       saving = true;
                                                       $el.closest('form').requestSubmit();
                                                       setTimeout(() => saving = false, 1000);
                                                   }
                                               ">
                                        <div x-show="saving" x-transition class="absolute -right-6 top-1/2 -translate-y-1/2">
                                            <svg class="h-4 w-4 text-green-600 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </div>
                                    </div>
                            </td>
                            <td class="px-2.5 py-1.5 text-center">
                                    <input type="text" name="unit" class="form-input w-24 mx-auto border-brand-300 focus:ring-brand-500 focus-border-brand-500" value="{{ $item->unit }}">
                            </td>
                            <td class="px-2.5 py-1.5 text-center">
                                <input type="number" step="0.01" min="0" name="unit_cost" 
                                       class="form-input w-28 mx-auto border-brand-300 focus:ring-brand-500 focus-border-brand-500" 
                                       value="{{ $item->unit_cost }}"
                                       x-model.number="unitCost"
                                       @input="recalculateFromCost()">
                            </td>
                            <td class="px-2.5 py-1.5 text-center">
                                <div x-data="{ editing: false, unitPrice: {{ $item->unit_price }}, saving: false }" class="flex items-center justify-center gap-1">
                                    <template x-if="!editing">
                                        <div class="flex items-center gap-1">
                                            <span class="text-sm font-medium" x-text="'$' + unitPrice.toFixed(2)"></span>
                                            <button type="button" 
                                                    @click="editing = true; $nextTick(() => $refs.priceInput.focus())"
                                                    class="text-blue-600 hover:text-blue-800 transition">
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="editing">
                                        <div class="flex items-center gap-1">
                                            <div class="relative">
                                                <span class="absolute inset-y-0 left-2 flex items-center text-xs text-gray-500">$</span>
                                                <input type="number" 
                                                       step="0.01" 
                                                       x-model.number="unitPrice"
                                                       @input="window.dispatchEvent(new CustomEvent('form-changed'))"
                                                       @blur="
                                                           editing = false;
                                                           if (unitPrice !== {{ $item->unit_price }}) {
                                                               saving = true;
                                                               $el.closest('form').requestSubmit();
                                                               setTimeout(() => saving = false, 1000);
                                                           }
                                                       "
                                                       @keydown.enter="$el.blur()"
                                                       x-ref="priceInput"
                                                       class="form-input w-24 text-sm pl-5 pr-2 py-1 border-brand-300 focus:ring-brand-500 focus:border-brand-500">
                                            </div>
                                        </div>
                                    </template>
                                    <div x-show="saving" x-transition class="ml-1">
                                        <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <input type="hidden" name="unit_price" x-model="unitPrice">
                                </div>
                            </td>
                            <td class="px-2.5 py-1.5 text-center">
                                <div x-data="{ editing: false, profitPercent: {{ $profitPercent }} }" class="flex flex-col items-center gap-1">
                                    <template x-if="!editing">
                                        <div class="flex items-center gap-1">
                                            <span class="text-sm font-medium" x-text="profitPercent.toFixed(1) + '%'"></span>
                                            <button type="button" 
                                                    @click="editing = true"
                                                    class="text-blue-600 hover:text-blue-800 transition">
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="editing">
                                        <div class="flex items-center gap-1">
                                            <div class="relative">
                                                <input type="number" 
                                                       step="0.1" 
                                                       x-model.number="profitPercent"
                                                       @input="profitPercent = Math.round(profitPercent * 10) / 10"
                                                       @blur="editing = false"
                                                       x-ref="profitInput"
                                                       class="form-input w-20 text-sm pr-6 py-1 border-brand-300 focus:ring-brand-500 focus:border-brand-500">
                                                <span class="absolute inset-y-0 right-2 flex items-center text-xs text-gray-500">%</span>
                                            </div>
                                            <button type="button" 
                                                    @click="editing = false"
                                                    class="text-green-600 hover:text-green-800 transition">
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polyline points="20 6 9 17 4 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                    <div class="flex items-center gap-1 mt-1">
                                        <div class="text-[10px] font-medium"
                                             @class([
                                                 'text-green-600' => $profitPercent >= 10,
                                                 'text-yellow-600' => $profitPercent >= 0 && $profitPercent < 10,
                                                 'text-red-600' => $profitPercent < 0
                                             ])>
                                            ${{ number_format($totalProfit, 2) }}
                                        </div>
                                    </div>
                                    <input type="hidden" name="profit_percent" x-model="profitPercent">
                                </div>
                            </td>
                            <td class="px-2.5 py-1.5 text-center text-gray-700">
                                ${{ number_format($totalCost, 2) }}
                            </td>
                            <td class="px-2.5 py-1.5 text-right font-semibold text-gray-900" data-col="line_total">
                                ${{ number_format($totalPrice, 2) }}
                            </td>
                            <td class="px-2.5 py-1.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                </form>
                                <form action="{{ route('estimates.items.destroy', [$estimate, $item]) }}" method="POST" class="inline" onsubmit="return confirm('Remove this line item?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-white text-red-700 border border-red-300 shadow-sm hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1">
                                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                        </svg>
                                        Delete
                                    </button>
                                </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-3 py-3 text-sm text-gray-500">No items in this work area yet.</td>
                        </tr>
                    @endforelse
                    
                    {{-- Quick Add Row --}}
                    <tr class="bg-brand-50/30 border-t-2 border-brand-200">
                        <td colspan="10" class="px-2.5 py-2">
                            <button type="button" 
                                    @click="window.openCalculatorDrawer ? window.openCalculatorDrawer({{ $area->id }}) : (document.getElementById('calcDrawer').style.display = 'block')"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-brand-700 hover:text-brand-800 hover:bg-brand-100 rounded-md transition-colors">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 5v14M5 12h14" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Add Items from Catalog
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div x-show="tab==='notes'" class="pb-4">
            <form method="POST" action="{{ route('estimates.areas.update', [$estimate, $area]) }}" class="space-y-2">
                @csrf
                @method('PATCH')
                <input type="hidden" name="name" value="{{ $area->name }}">
                <input type="hidden" name="identifier" value="{{ $area->identifier }}">
                <input type="hidden" name="cost_code_id" value="{{ $area->cost_code_id }}">
                <input type="hidden" name="sort_order" value="{{ $area->sort_order ?? 0 }}">
                <label class="block text-sm font-medium text-gray-700">Notes</label>
                <textarea name="description" rows="5" class="form-textarea w-full border-brand-300 focus:ring-brand-500 focus-border-brand-500">{{ old('description', $area->description) }}</textarea>
                <p class="text-xs text-gray-500">Use "Save All" at the top to save changes.</p>
            </form>
        </div>
    </div>
</div>
