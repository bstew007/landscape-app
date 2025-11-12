@php
    $rowIndex = $rowIndex ?? '__INDEX__';
    $material = $material ?? [];
    $qtyValue = $material['qty'] ?? null;
    $unitCostValue = $material['unit_cost'] ?? null;
    $lineTotal = (is_numeric($qtyValue) && is_numeric($unitCostValue))
        ? '$' . number_format((float) $qtyValue * (float) $unitCostValue, 2)
        : '--';
@endphp

<div class="border rounded-lg p-4 bg-white shadow-sm" data-custom-row data-custom-index="{{ $rowIndex }}">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="md:col-span-2">
            <label class="block text-sm font-semibold mb-1">Material Name</label>
            <input
                type="text"
                name="custom_materials[{{ $rowIndex }}][name]"
                class="form-input w-full"
                value="{{ $material['name'] ?? '' }}"
                placeholder="e.g., Lighting Kit"
            >
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Qty</label>
            <input
                type="number"
                min="0"
                step="0.01"
                name="custom_materials[{{ $rowIndex }}][qty]"
                class="form-input w-full"
                value="{{ $qtyValue ?? '' }}"
                data-custom-qty
            >
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Unit Cost ($)</label>
            <input
                type="number"
                min="0"
                step="0.01"
                name="custom_materials[{{ $rowIndex }}][unit_cost]"
                class="form-input w-full"
                value="{{ $unitCostValue ?? '' }}"
                data-custom-cost
            >
        </div>
    </div>
    <div class="mt-3 flex items-center justify-between text-sm text-gray-600">
        <p>Line Total: <span class="font-semibold" data-custom-total>{{ $lineTotal }}</span></p>
        <button type="button" class="text-red-600 hover:text-red-700" data-action="remove-custom-material">Remove</button>
    </div>
</div>
