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
        <label class="block text-sm font-semibold mb-1">Units</label>
        <input type="text" name="unit" class="form-input w-full"
               value="{{ old('unit', $labor->unit ?? 'hr') }}" required>
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold mb-1">Description</label>
        <textarea name="description" rows="2" class="form-textarea w-full" placeholder="Client-facing description">{{ old('description', $labor->description ?? '') }}</textarea>
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold mb-1">Internal Notes</label>
        <textarea name="internal_notes" rows="2" class="form-textarea w-full" placeholder="Internal only">{{ old('internal_notes', $labor->internal_notes ?? '') }}</textarea>
    </div>
    <div>
        <label class="block text-sm font-semibold mb-1">Average Wage ($)</label>
        <input type="number" step="0.01" min="0" name="average_wage" class="form-input w-full"
               value="{{ old('average_wage', $labor->average_wage ?? '') }}">
    </div>
    <div>
        <label class="block text-sm font-semibold mb-1">Overtime Factor</label>
        <input type="number" step="0.01" min="0" name="overtime_factor" class="form-input w-full"
               value="{{ old('overtime_factor', $labor->overtime_factor ?? '') }}" placeholder="e.g., 1.5">
    </div>
    <div>
        <label class="block text-sm font-semibold mb-1">Unbillable %</label>
        <input type="number" step="0.1" min="0" name="unbillable_percentage" class="form-input w-full"
               value="{{ old('unbillable_percentage', $labor->unbillable_percentage ?? 0) }}">
    </div>
    <div>
        <label class="block text-sm font-semibold mb-1">Labor Burden %</label>
        <input type="number" step="0.1" min="0" name="labor_burden_percentage" class="form-input w-full"
               value="{{ old('labor_burden_percentage', $labor->labor_burden_percentage ?? 0) }}">
    </div>

    <div>
        <label class="block text-sm font-semibold mb-1">Type</label>
        <input type="text" name="type" class="form-input w-full"
               value="{{ old('type', $labor->type ?? 'crew') }}" required>
    </div>
    <div>
        <label class="block text-sm font-semibold mb-1">Cost Code</label>
        <select name="cost_code_id" class="form-select w-full">
            <option value="">—</option>
            @foreach(\App\Models\CostCode::orderBy('code')->get() as $cc)
                <option value="{{ $cc->id }}" @selected(old('cost_code_id', $labor->cost_code_id ?? null) == $cc->id)>{{ $cc->code }} — {{ $cc->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-semibold mb-1">Average Base Rate ($)</label>
        <input type="number" step="0.01" min="0" name="base_rate" class="form-input w-full"
               value="{{ old('base_rate', $labor->base_rate ?? 0) }}" required>
    </div>
    <div>
        <label class="block text-sm font-semibold mb-1">Overtime Rate ($)</label>
        <input type="number" step="0.01" min="0" name="overtime_rate" class="form-input w-full"
               value="{{ old('overtime_rate', $labor->overtime_rate ?? null) }}">
    </div>
</div>

<div class="flex items-center gap-6 mt-2">
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

<div class="flex gap-3 mt-3">
    <button class="px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700">
        {{ $labor ? 'Update Labor Entry' : 'Create Labor Entry' }}
    </button>
    <a href="{{ route('labor.index') }}" class="px-5 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</a>
</div>
