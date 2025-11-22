@php
    /** @var \App\Models\Estimate $estimate */
    /** @var \App\Models\EstimateArea $area */
    $allItems = $allItems ?? $estimate->items;
    $areaItems = $allItems->where('area_id', $area->id);
    $laborHours = $areaItems->where('item_type', 'labor')->sum('quantity');
    $cogs = $areaItems->filter(fn($i) => in_array($i->item_type, ['labor','material']))->sum('cost_total');
    $price = $areaItems->sum('line_total');
    $profit = $price - $cogs;
@endphp
<div x-data="{ open: true, tab: 'pricing', menuOpen: false }"
     x-on:force-open-area.window="if (Number($event.detail?.areaId) === {{ $area->id }}) open = true"
     class="mb-6 border rounded-lg bg-white work-area overflow-visible"
     data-area-id="{{ $area->id }}"
     data-sort-order="{{ $area->sort_order ?? 0 }}">
    <div class="px-4 py-3 border-b border-slate-200 bg-slate-100">
        <form method="POST" action="{{ route('estimates.areas.update', [$estimate, $area]) }}" class="flex flex-wrap items-start gap-3">
            @csrf
            @method('PATCH')
            <div class="relative inline-block text-left shrink-0">
                <button type="button" class="text-xs px-2 py-1 rounded border" @click.stop="menuOpen = !menuOpen" @keydown.escape.window="menuOpen = false">Options</button>
                <div x-cloak x-show="menuOpen" x-transition @click.away="menuOpen = false"
                     class="absolute z-20 mt-1 min-w-[9rem] left-0 bg-white border rounded-md shadow-lg text-sm py-1 ring-1 ring-black/5">
                    <button type="button" class="block w-full text-left px-3 py-1 hover:bg-gray-100" @click="open = true; menuOpen = false">Edit</button>
                    <button type="button" class="block w-full text-left px-3 py-1 hover:bg-gray-100" @click="open = false; menuOpen = false">Close</button>
                    <div class="my-1 border-t border-slate-200"></div>
                    <button type="button" class="block w-full text-left px-3 py-1 text-red-600 hover:bg-red-50" @click.prevent="$refs.deleteForm.submit()">Delete</button>
                </div>
            </div>
            <div class="w-16">
                <label class="block text-xs font-medium text-gray-600">Order</label>
                <input type="number" name="sort_order" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="{{ $area->sort_order ?? 0 }}">
            </div>
            <div class="w-full sm:w-80">
                <label class="block text-xs font-medium text-gray-600">Name</label>
                <input type="text" name="name" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="{{ $area->name }}">
            </div>
            <div class="w-28">
                <label class="block text-xs font-medium text-gray-600">Id</label>
                <input type="text" name="identifier" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="{{ $area->identifier }}">
            </div>
            <div class="w-64">
                <label class="block text-xs font-medium text-gray-600">Cost Code</label>
                <select name="cost_code_id" class="form-select w-full border-brand-300 focus:ring-brand-500 focus-border-brand-500">
                    <option value="">—</option>
                    @foreach (($costCodes ?? []) as $cc)
                        <option value="{{ $cc->id }}" @selected($area->cost_code_id === $cc->id)>{{ $cc->code }} - {{ $cc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-wrap items-center gap-6 w-auto pt-1">
                <div class="flex items-baseline gap-2">
                    <span class="text-xs uppercase tracking-wide text-gray-500">Hrs</span>
                    <span class="text-base font-semibold text-gray-900">{{ number_format($laborHours, 2) }}</span>
                </div>
                <span class="text-gray-300">•</span>
                <div class="flex items-baseline gap-2">
                    <span class="text-xs uppercase tracking-wide text-gray-500">COGS</span>
                    <span class="text-base font-semibold text-gray-900">${{ number_format($cogs, 2) }}</span>
                </div>
                <span class="text-gray-300">•</span>
                <div class="flex items-baseline gap-2">
                    <span class="text-xs uppercase tracking-wide text-gray-500">Price</span>
                    <span class="text-base font-semibold text-gray-900">${{ number_format($price, 2) }}</span>
                </div>
                <span class="text-gray-300">•</span>
                <div class="flex items-baseline gap-2">
                    <span class="text-xs uppercase tracking-wide text-gray-500">Profit</span>
                    <span class="text-base font-semibold text-gray-900">${{ number_format($profit, 2) }}</span>
                </div>
            </div>
            <div class="flex items-center pt-1 ml-auto">
                <x-brand-button
                    type="button"
                    size="sm"
                    @click="$dispatch('estimate-open-add-items', { areaId: {{ $area->id }}, tab: 'labor' })">
                    + Add Items
                </x-brand-button>
            </div>
        </form>
        <form x-ref="deleteForm" method="POST" action="{{ route('estimates.areas.destroy', [$estimate, $area]) }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
    <div x-show="open" class="px-4 pt-3">
        <div class="flex items-center gap-2 mb-3">
            <div class="inline-flex rounded-md border">
                <button type="button" class="px-3 py-1.5 text-sm rounded-l-md hover:bg-gray-100 text-gray-700" :class="{ 'bg-gray-200 text-gray-900': tab==='pricing' }" @click="tab='pricing'">
                    <svg class="inline h-4 w-4 mr-1 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit Pricing
                </button>
                <button type="button" class="px-3 py-1.5 text-sm rounded-r-md hover:bg-gray-100 text-gray-700 border-l" :class="{ 'bg-gray-200 text-gray-900': tab==='notes' }" @click="tab='notes'">
                    <svg class="inline h-4 w-4 mr-1 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h9l7 7v9a2 2 0 0 1-2 2z"/><path d="M17 21v-8h-6"/></svg>
                    Edit Notes
                </button>
            </div>
        </div>
        <div x-show="tab==='pricing'" class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="text-left px-3 py-2">Name</th>
                        <th class="text-center px-3 py-2">Qty</th>
                        <th class="text-center px-3 py-2">Units</th>
                        <th class="text-center px-3 py-2">Unit Cost</th>
                        <th class="text-center px-3 py-2">Unit Price</th>
                        <th class="text-center px-3 py-2">Profit</th>
                        <th class="text-center px-3 py-2">Total Cost</th>
                        <th class="text-right px-3 py-2">Total Price</th>
                        <th class="px-3 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($areaItems as $item)
                        @php $rowProfit = $item->margin_total; @endphp
                        <tr class="border-t"
                            data-item-id="{{ $item->id }}"
                            data-item-type="{{ $item->item_type }}"
                            data-area-id="{{ $area->id }}"
                            data-quantity="{{ $item->quantity }}"
                            id="estimate-item-{{ $item->id }}">
                            <td class="px-3 py-2">
                                <form method="POST" action="{{ route('estimates.items.update', [$estimate, $item]) }}" class="contents">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="area_id" value="{{ $area->id }}">
                                    <input type="text" name="name" class="form-input w-full border-brand-300 focus:ring-brand-500 focus-border-brand-500" value="{{ $item->name }}">
                            </td>
                            <td class="px-3 py-2 text-center">
                                    <input type="number" step="0.01" min="0" name="quantity" class="form-input w-24 mx-auto border-brand-300 focus:ring-brand-500 focus-border-brand-500" value="{{ $item->quantity }}">
                            </td>
                            <td class="px-3 py-2 text-center">
                                    <input type="text" name="unit" class="form-input w-24 mx-auto border-brand-300 focus:ring-brand-500 focus-border-brand-500" value="{{ $item->unit }}">
                            </td>
                            <td class="px-3 py-2 text-center">
                                    <input type="number" step="0.01" min="0" name="unit_cost" class="form-input w-28 mx-auto border-brand-300 focus:ring-brand-500 focus-border-brand-500" value="{{ $item->unit_cost }}">
                            </td>
                            <td class="px-3 py-2 text-center">
                                    <input type="number" step="0.01" min="0" name="unit_price" class="form-input w-28 mx-auto border-brand-300 focus:ring-brand-500 focus-border-brand-500" value="{{ $item->unit_price }}">
                            </td>
                            <td class="px-3 py-2 text-center text-gray-700">
                                ${{ number_format($rowProfit, 2) }}
                            </td>
                            <td class="px-3 py-2 text-center text-gray-700">
                                ${{ number_format($item->cost_total, 2) }}
                            </td>
                            <td class="px-3 py-2 text-right font-semibold text-gray-900" data-col="line_total">
                                ${{ number_format($item->line_total, 2) }}
                            </td>
                            <td class="px-3 py-2 text-right space-x-2">
                                    <x-brand-button type="submit" size="sm" variant="outline">Save</x-brand-button>
                                </form>
                                <form action="{{ route('estimates.items.destroy', [$estimate, $item]) }}" method="POST" class="inline" onsubmit="return confirm('Remove this line item?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-danger-button size="sm" type="submit">Delete</x-danger-button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-3 py-4 text-sm text-gray-500">No items in this work area yet.</td>
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
