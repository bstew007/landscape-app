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
<div x-data="{
        open: {{ $initiallyOpen ? 'true' : 'false' }},
        tab: 'pricing',
        menuOpen: false,
        toggleOpen() {
            this.open = !this.open;
        }
    }"
     x-on:force-open-area.window="if (Number($event.detail?.areaId) === {{ $area->id }} && !open) { toggleOpen(); }"
     class="border rounded-lg bg-white work-area overflow-visible"
     data-area-id="{{ $area->id }}"
     data-sort-order="{{ $area->sort_order ?? 0 }}">

    <div class="px-3 py-1.5 border-b border-slate-200 bg-slate-100 cursor-pointer"
         @click.self="toggleOpen()">
        <form method="POST" action="{{ route('estimates.areas.update', [$estimate, $area]) }}" class="flex flex-wrap items-start gap-1.5">
            @csrf
            @method('PATCH')
            <div class="relative inline-block text-left shrink-0">
                <button type="button" 
                        class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-white text-gray-700 border border-gray-300 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-1" 
                        @click.stop="menuOpen = !menuOpen" 
                        @keydown.escape.window="menuOpen = false"
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
                <div x-cloak x-show="menuOpen" x-transition @click.away="menuOpen = false"
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
            <div class="flex flex-wrap items-center gap-3 w-auto pt-0.5 text-sm">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-xs uppercase tracking-wide text-gray-500">Hrs</span>
                    <span class="text-base font-semibold text-gray-900">{{ number_format($laborHours, 2) }}</span>
                </div>
                <span class="text-gray-300">•</span>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-xs uppercase tracking-wide text-gray-500">COGS</span>
                    <span class="text-base font-semibold text-gray-900">${{ number_format($cogs, 2) }}</span>
                </div>
                <span class="text-gray-300">•</span>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-xs uppercase tracking-wide text-gray-500">Price</span>
                    <span class="text-base font-semibold text-gray-900">${{ number_format($price, 2) }}</span>
                </div>
                <span class="text-gray-300">•</span>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-xs uppercase tracking-wide text-gray-500">Profit</span>
                    <span class="text-base font-semibold text-gray-900">${{ number_format($profit, 2) }}</span>
                </div>
            </div>
            <div class="flex items-center gap-2 pt-1 ml-auto">
                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-xs font-medium bg-white text-gray-700 border border-gray-300 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-1"
                    @click="$dispatch('estimate-open-add-items', { areaId: {{ $area->id }}, tab: 'labor' })">
                    <svg class="h-3.5 w-3.5 text-gray-500" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 10h12M10 4v12" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Add Items
                </button>
            </div>
        </form>
        <form x-ref="deleteForm" method="POST" action="{{ route('estimates.areas.destroy', [$estimate, $area]) }}" class="hidden">
            @csrf
            @method('DELETE')
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
        <div x-show="tab==='pricing'" class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="text-left px-2.5 py-1.5">Name</th>
                        <th class="text-center px-2.5 py-1.5">Qty</th>
                        <th class="text-center px-2.5 py-1.5">Units</th>
                        <th class="text-center px-2.5 py-1.5">Unit Cost</th>
                        <th class="text-center px-2.5 py-1.5">Breakeven</th>
                        <th class="text-center px-2.5 py-1.5">Unit Price</th>
                        <th class="text-center px-2.5 py-1.5">Profit %</th>
                        <th class="text-center px-2.5 py-1.5">Total Cost</th>
                        <th class="text-right px-2.5 py-1.5">Total Price</th>
                        <th class="px-2.5 py-1.5"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($areaItems as $item)
                        @php 
                            $defaultMarginPercent = $defaultMarginPercent ?? 20.0;
                            $overheadRate = $overheadRate ?? 0.0;
                            $isLabor = $item->item_type === 'labor';
                            $isMaterial = $item->item_type === 'material';
                            
                            // DEBUG
                            if ($isLabor && $item->unit_cost == 22) {
                                \Log::info('Area Item Debug', [
                                    'item_name' => $item->name,
                                    'overhead_rate_received' => $overheadRate,
                                    'unit_cost' => $item->unit_cost,
                                ]);
                            }
                            
                            // Calculate true breakeven based on item type
                            if ($isLabor) {
                                // For labor: breakeven includes overhead
                                $breakeven = $item->unit_cost + $overheadRate;
                            } elseif ($isMaterial && $item->tax_rate > 0) {
                                // For materials: breakeven includes tax if taxable
                                $breakeven = $item->unit_cost * (1 + $item->tax_rate);
                            } else {
                                // For fees, discounts, and non-taxable materials
                                $breakeven = $item->unit_cost;
                            }
                            
                            // Calculate actual profit % based on breakeven and price
                            // Profit % = (Price - Breakeven) / Price × 100
                            $profitPercent = $item->unit_price > 0 
                                ? round((($item->unit_price - $breakeven) / $item->unit_price) * 100, 1)
                                : 0.0;
                            
                            // Calculate total profit in dollars
                            $totalProfit = ($item->unit_price - $breakeven) * $item->quantity;
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
                            <td class="px-2.5 py-1.5">
                                <form method="POST" action="{{ route('estimates.items.update', [$estimate, $item]) }}" class="contents">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="area_id" value="{{ $area->id }}">
                                    <input type="text" name="name" class="form-input w-full border-brand-300 focus:ring-brand-500 focus-border-brand-500" value="{{ $item->name }}">
                            </td>
                            <td class="px-2.5 py-1.5 text-center">
                                    <input type="number" step="0.01" min="0" name="quantity" class="form-input w-24 mx-auto border-brand-300 focus:ring-brand-500 focus-border-brand-500" value="{{ $item->quantity }}">
                            </td>
                            <td class="px-2.5 py-1.5 text-center">
                                    <input type="text" name="unit" class="form-input w-24 mx-auto border-brand-300 focus:ring-brand-500 focus-border-brand-500" value="{{ $item->unit }}">
                            </td>
                            <td class="px-2.5 py-1.5 text-center">
                                <input type="number" step="0.01" min="0" name="unit_cost" 
                                       class="form-input w-28 mx-auto border-brand-300 focus:ring-brand-500 focus-border-brand-500" 
                                       x-model.number="unitCost"
                                       @input="recalculateFromCost()">
                            </td>
                            <td class="px-2.5 py-1.5 text-center">
                                <div class="flex flex-col items-center gap-0.5">
                                    <span class="text-gray-700 font-medium" x-text="'$' + breakeven.toFixed(2)"></span>
                                    @if($isLabor && $overheadRate > 0)
                                        <div class="text-[10px] text-gray-500" x-bind:title="'Includes $' + overheadRate.toFixed(2) + '/hr overhead'">
                                            <span x-text="'+$' + overheadRate.toFixed(2) + ' OH'"></span>
                                        </div>
                                    @elseif($isMaterial && $item->tax_rate > 0)
                                        <div class="text-[10px] text-gray-500" x-bind:title="'Includes ' + (taxRate * 100).toFixed(1) + '% tax'">
                                            <span x-text="'+' + (taxRate * 100).toFixed(1) + '% tax'"></span>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-2.5 py-1.5 text-center">
                                <div x-data="{ editing: false }" class="flex items-center justify-center gap-1">
                                    <template x-if="!editing">
                                        <div class="flex items-center gap-1">
                                            <span class="text-sm font-medium" x-text="'$' + unitPrice.toFixed(2)"></span>
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
                                                <span class="absolute inset-y-0 left-2 flex items-center text-xs text-gray-500">$</span>
                                                <input type="number" 
                                                       step="0.01" 
                                                       x-model.number="unitPrice"
                                                       @input="recalculateFromPrice()"
                                                       @blur="editing = false"
                                                       x-ref="priceInput"
                                                       class="form-input w-24 text-sm pl-5 pr-2 py-1 border-brand-300 focus:ring-brand-500 focus:border-brand-500">
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
                                </div>
                                <input type="hidden" name="unit_price" :value="unitPrice.toFixed(2)">
                            </td>
                            <td class="px-2.5 py-1.5 text-center">
                                <div x-data="{ editing: false }" class="flex flex-col items-center gap-1">
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
                                                       @input="profitPercent = Math.round(profitPercent * 10) / 10; recalculateFromProfit()"
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
                                             :class="{ 'text-green-600': profitPercent >= 10, 'text-yellow-600': profitPercent >= 0 && profitPercent < 10, 'text-red-600': profitPercent < 0 }"
                                             x-text="'$' + totalProfit.toFixed(2)"></div>
                                        <button type="button"
                                                @click="resetToCatalogDefaults()"
                                                title="Reset to catalog defaults"
                                                class="text-gray-400 hover:text-blue-600 transition">
                                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <input type="hidden" name="profit_percent" :value="profitPercent.toFixed(1)">
                            </td>
                            <td class="px-2.5 py-1.5 text-center text-gray-700" x-text="'$' + (unitCost * quantity).toFixed(2)">
                            </td>
                            <td class="px-2.5 py-1.5 text-right font-semibold text-gray-900" data-col="line_total" x-text="'$' + (unitPrice * quantity).toFixed(2)">
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
                </tbody>
            </table>
        </div>
        <div x-show="tab==='notes'" class="pb-4">
            <form method="POST" action="{{ route('estimates.areas.update', [$estimate, $area]) }}" class="space-y-2">
                @csrf
                @method('PATCH')
                <label class="block text-sm font-medium text-gray-700">Notes</label>
                <textarea name="description" rows="5" class="form-textarea w-full border-brand-300 focus:ring-brand-500 focus-border-brand-500">{{ old('description', $area->description) }}</textarea>
                <p class="text-xs text-gray-500">Use “Save All” at the top to save changes.</p>
            </form>
        </div>
    </div>
</div>
