@php
    $areaId = $areaId ?? $item->area_id;
    $defaultMarginPercent = $defaultMarginPercent ?? 20.0;
    $overheadRate = $overheadRate ?? 0.0;
    $isLabor = $item->item_type === 'labor';
    $isMaterial = $item->item_type === 'material';
    
    // NO CALCULATIONS - just use the stored values
    // For items from catalog: unit_cost = breakeven (stored in database)
    // For manual items: may need to add tax for materials
    if ($item->catalog_type === 'material' || $item->catalog_type === 'labor') {
        // From catalog - unit_cost IS the breakeven
        $breakeven = $item->unit_cost;
    } elseif ($isMaterial && $item->tax_rate > 0) {
        // Manual material with tax
        $breakeven = $item->unit_cost * (1 + $item->tax_rate);
    } else {
        // Manual items or non-taxable
        $breakeven = $item->unit_cost;
    }
    
    // Calculate profit % from breakeven and price
    $profitPercent = $item->unit_price > 0 
        ? round((($item->unit_price - $breakeven) / $item->unit_price) * 100, 1)
        : 0.0;
    
    $totalProfit = ($item->unit_price - $breakeven) * $item->quantity;
@endphp
<tr class="border-t"
    data-item-id="{{ $item->id }}"
    data-item-type="{{ $item->item_type }}"
    data-area-id="{{ $areaId }}"
    data-quantity="{{ $item->quantity }}"
    id="estimate-item-{{ $item->id }}">
    <td class="px-3 py-2">
        <form method="POST" action="{{ route('estimates.items.update', [$estimate, $item]) }}" class="contents">
            @csrf
            @method('PATCH')
            <input type="hidden" name="area_id" value="{{ $areaId }}">
            <input type="text" name="name" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="{{ $item->name }}">
    </td>
    <td class="px-3 py-2 text-center">
            <input type="number" step="0.01" min="0" name="quantity" class="form-input w-24 mx-auto border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="{{ $item->quantity }}">
    </td>
    <td class="px-3 py-2 text-center">
            <input type="text" name="unit" class="form-input w-24 mx-auto border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="{{ $item->unit }}">
    </td>
    <td class="px-3 py-2 text-center">
            <input type="number" step="0.01" min="0" name="unit_cost" class="form-input w-28 mx-auto border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="{{ $item->unit_cost }}">
    </td>
    <td class="px-3 py-2 text-center">
        <div class="flex flex-col items-center gap-0.5">
            <span class="text-gray-700 font-medium">${{ number_format($breakeven, 2) }}</span>
            @if($isLabor && $overheadRate > 0)
                <div class="text-[10px] text-gray-500" title="Includes ${{ number_format($overheadRate, 2) }}/hr overhead">
                    +${{ number_format($overheadRate, 2) }} OH
                </div>
            @elseif($isMaterial && $item->tax_rate > 0)
                <div class="text-[10px] text-gray-500" title="Includes {{ number_format($item->tax_rate * 100, 1) }}% tax">
                    +{{ number_format($item->tax_rate * 100, 1) }}% tax
                </div>
            @endif
        </div>
    </td>
    <td class="px-3 py-2 text-center">
            <input type="number" step="0.01" min="0" name="unit_price" class="form-input w-28 mx-auto border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="{{ $item->unit_price }}">
    </td>
    <td class="px-3 py-2 text-center">
        <div class="flex flex-col items-center gap-0.5">
            @php
                $marginColorClass = match(true) {
                    $profitPercent < 0 => 'text-red-700 bg-red-50',
                    $profitPercent < 10 => 'text-red-600 bg-red-50',
                    $profitPercent < 15 => 'text-amber-600 bg-amber-50',
                    default => 'text-emerald-700 bg-emerald-50',
                };
            @endphp
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-bold {{ $marginColorClass }}">
                @if($profitPercent < 10)
                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                @endif
                {{ number_format($profitPercent, 1) }}%
            </span>
            <div class="text-[10px] text-gray-500 mt-0.5">
                ${{ number_format($totalProfit, 2) }}
            </div>
        </div>
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
