@php
    $material = $material ?? null;
@endphp

@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-semibold mb-1">Name</label>
        <input type="text" name="name" class="form-input w-full"
               value="{{ old('name', $material->name ?? '') }}" required>
    </div>
    <div>
        <label class="block text-sm font-semibold mb-1">SKU</label>
        <input type="text" name="sku" class="form-input w-full"
               value="{{ old('sku', $material->sku ?? '') }}">
    </div>
    <div>
        <label class="block text-sm font-semibold mb-1">Category</label>
        <input type="text" name="category" class="form-input w-full"
               value="{{ old('category', $material->category ?? '') }}">
    </div>
    <div>
        <label class="block text-sm font-semibold mb-1">Unit</label>
        <input type="text" name="unit" class="form-input w-full"
               value="{{ old('unit', $material->unit ?? 'ea') }}" required>
    </div>
    <div>
        <label class="block text-sm font-semibold mb-1">Unit Cost ($)</label>
        <input type="number" step="0.01" min="0" name="unit_cost" class="form-input w-full"
               value="{{ old('unit_cost', $material->unit_cost ?? 0) }}" required>
    </div>
    <div>
        <label class="block text-sm font-semibold mb-1">Tax Rate</label>
        <input type="number" step="0.001" min="0" name="tax_rate" class="form-input w-full"
               value="{{ old('tax_rate', $material->tax_rate ?? 0) }}">
    </div>
    <div>
        <label class="block text-sm font-semibold mb-1">Vendor</label>
        <input type="text" name="vendor_name" class="form-input w-full"
               value="{{ old('vendor_name', $material->vendor_name ?? '') }}">
    </div>
    <div>
        <label class="block text-sm font-semibold mb-1">Vendor SKU</label>
        <input type="text" name="vendor_sku" class="form-input w-full"
               value="{{ old('vendor_sku', $material->vendor_sku ?? '') }}">
    </div>
</div>

<div>
    <label class="block text-sm font-semibold mb-1">Description</label>
    <textarea name="description" rows="3" class="form-textarea w-full">{{ old('description', $material->description ?? '') }}</textarea>
    <p class="text-xs text-gray-500 mt-1">Displayed when selecting materials for an estimate.</p>
</div>

<div class="flex items-center gap-6">
    <label class="inline-flex items-center">
        <input type="checkbox" name="is_taxable" value="1"
               {{ old('is_taxable', $material->is_taxable ?? true) ? 'checked' : '' }}
               class="form-checkbox">
        <span class="ml-2 text-sm">Taxable</span>
    </label>
    <label class="inline-flex items-center">
        <input type="checkbox" name="is_active" value="1"
               {{ old('is_active', $material->is_active ?? true) ? 'checked' : '' }}
               class="form-checkbox">
        <span class="ml-2 text-sm">Active</span>
    </label>
</div>

<div class="flex gap-3">
    <button class="px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700">
        {{ $material ? 'Update Material' : 'Create Material' }}
    </button>
    <a href="{{ route('materials.index') }}" class="px-5 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</a>
</div>
