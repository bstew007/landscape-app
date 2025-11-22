@php
    $rowProfit = $item->margin_total;
    $areaId = $areaId ?? $item->area_id;
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
            <input type="number" step="0.01" min="0" name="unit_price" class="form-input w-28 mx-auto border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="{{ $item->unit_price }}">
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
