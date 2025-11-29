{{-- Overhead and logistics --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">
            Labor Rate ($/hr) <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <span class="absolute left-4 top-3 text-gray-500">$</span>
            <input type="number" 
                   step="0.01" 
                   name="labor_rate" 
                   class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" 
                   value="{{ old('labor_rate', $formData['labor_rate'] ?? 65) }}" 
                   required>
        </div>
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">
            Crew Size <span class="text-red-500">*</span>
        </label>
        <input type="number" 
               name="crew_size" 
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" 
               value="{{ old('crew_size', $formData['crew_size'] ?? 3) }}" 
               placeholder="Number of workers"
               required>
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">
            Drive Distance (miles) <span class="text-red-500">*</span>
        </label>
        <input type="number" 
               step="0.1" 
               name="drive_distance" 
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" 
               value="{{ old('drive_distance', $formData['drive_distance'] ?? 15) }}" 
               placeholder="e.g. 15.5"
               required>
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">
            Drive Speed (mph) <span class="text-red-500">*</span>
        </label>
        <input type="number" 
               step="1" 
               name="drive_speed" 
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" 
               value="{{ old('drive_speed', $formData['drive_speed'] ?? 45) }}" 
               placeholder="e.g. 45"
               required>
    </div>
</div>

<div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
    <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
        <svg class="w-4 h-4 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        Additional Overhead (Optional)
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-600 mb-2">Site Conditions (%)</label>
            <input type="number" 
                   step="1" 
                   name="site_conditions" 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" 
                   value="{{ old('site_conditions', $formData['site_conditions'] ?? 5) }}"
                   placeholder="5">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-600 mb-2">Material Pickup (%)</label>
            <input type="number" 
                   step="1" 
                   name="material_pickup" 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" 
                   value="{{ old('material_pickup', $formData['material_pickup'] ?? 5) }}"
                   placeholder="5">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-600 mb-2">Cleanup (%)</label>
            <input type="number" 
                   step="1" 
                   name="cleanup" 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" 
                   value="{{ old('cleanup', $formData['cleanup'] ?? 5) }}"
                   placeholder="5">
        </div>
    </div>
</div>

