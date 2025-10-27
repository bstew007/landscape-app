{{-- Overhead and logistics --}}
<div class="grid grid-cols-2 gap-4 mb-4">
    <div>
        <label>Labor Rate ($/hr)</label>
        <input type="number" step="0.01" name="labor_rate" class="form-input w-full" value="{{ old('labor_rate', $formData['labor_rate'] ?? '') }}" required>
    </div>
    <div>
        <label>Crew Size</label>
        <input type="number" name="crew_size" class="form-input w-full" value="{{ old('crew_size', $formData['crew_size'] ?? '') }}" required>
    </div>
    <div>
        <label>Drive Distance (miles)</label>
        <input type="number" step="0.1" name="drive_distance" class="form-input w-full" value="{{ old('drive_distance', $formData['drive_distance'] ?? '') }}" required>
    </div>
    <div>
        <label>Drive Speed (mph)</label>
        <input type="number" step="1" name="drive_speed" class="form-input w-full" value="{{ old('drive_speed', $formData['drive_speed'] ?? '') }}" required>
    </div>
    <div>
        <label>Site Conditions Overhead (%)</label>
        <input type="number" step="1" name="site_conditions" class="form-input w-full" value="{{ old('site_conditions', $formData['site_conditions'] ?? '') }}">
    </div>
    <div>
        <label>Material Pickup Overhead (%)</label>
        <input type="number" step="1" name="material_pickup" class="form-input w-full" value="{{ old('material_pickup', $formData['material_pickup'] ?? '') }}">
    </div>
    <div>
        <label>Cleanup Overhead (%)</label>
        <input type="number" step="1" name="cleanup" class="form-input w-full" value="{{ old('cleanup', $formData['cleanup'] ?? '') }}">
    </div>
    <div>
        <label>Markup (%)</label>
        <input type="number" step="0.1" name="markup" class="form-input w-full" value="{{ old('markup', $formData['markup'] ?? '') }}" required>
    </div>
</div>

{{-- Job Notes --}}
<div class="mb-6">
    <label class="block font-semibold" for="job_notes">Job Notes (optional)</label>
    <textarea name="job_notes" id="job_notes" rows="4"
              class="form-textarea w-full"
              placeholder="Add any special site conditions, client instructions, exclusions, etc.">{{ old('job_notes', $formData['job_notes'] ?? '') }}</textarea>
</div>

