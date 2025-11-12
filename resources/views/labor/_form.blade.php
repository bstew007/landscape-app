@php
    $labor = $labor ?? null;
@endphp

@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-semibold mb-1">Name</label>
        <input type="text" name="name" class="form-input w-full"
               value="{{ old('name', $labor->name ?? '') }}" required>
    </div>
    <div>
        <label class="block text-sm font-semibold mb-1">Type</label>
        <input type="text" name="type" class="form-input w-full"
               value="{{ old('type', $labor->type ?? 'crew') }}" required>
    </div>
    <div>
        <label class="block text-sm font-semibold mb-1">Unit</label>
        <input type="text" name="unit" class="form-input w-full"
               value="{{ old('unit', $labor->unit ?? 'hr') }}" required>
    </div>
    <div>
        <label class="block text-sm font-semibold mb-1">Base Rate ($)</label>
        <input type="number" step="0.01" min="0" name="base_rate" class="form-input w-full"
               value="{{ old('base_rate', $labor->base_rate ?? 0) }}" required>
    </div>
    <div>
        <label class="block text-sm font-semibold mb-1">Overtime Rate ($)</label>
        <input type="number" step="0.01" min="0" name="overtime_rate" class="form-input w-full"
               value="{{ old('overtime_rate', $labor->overtime_rate ?? null) }}">
    </div>
    <div>
        <label class="block text-sm font-semibold mb-1">Burden %</label>
        <input type="number" step="0.1" min="0" name="burden_percentage" class="form-input w-full"
               value="{{ old('burden_percentage', $labor->burden_percentage ?? 0) }}">
    </div>
</div>

<div>
    <label class="block text-sm font-semibold mb-1">Notes</label>
    <textarea name="notes" rows="3" class="form-textarea w-full">{{ old('notes', $labor->notes ?? '') }}</textarea>
</div>

<div class="flex items-center gap-6">
    <label class="inline-flex items-center">
        <input type="checkbox" name="is_billable" value="1"
               {{ old('is_billable', $labor->is_billable ?? true) ? 'checked' : '' }} class="form-checkbox">
        <span class="ml-2 text-sm">Billable</span>
    </label>
    <label class="inline-flex items-center">
        <input type="checkbox" name="is_active" value="1"
               {{ old('is_active', $labor->is_active ?? true) ? 'checked' : '' }} class="form-checkbox">
        <span class="ml-2 text-sm">Active</span>
    </label>
</div>

<div class="flex gap-3">
    <button class="px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700">
        {{ $labor ? 'Update Labor Entry' : 'Create Labor Entry' }}
    </button>
    <a href="{{ route('labor.index') }}" class="px-5 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</a>
</div>
