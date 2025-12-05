{{--
    Excavation Method Selector
    
    Props needed:
    - $color: 'blue', 'amber', 'green', etc. (for border hover color)
    - $formData: form data array for old values
    - $alpineModel: Alpine.js x-model binding (default: 'excavationMethod')
    
    Example usage:
    @include('calculators.partials.excavation_method_selector', [
        'color' => 'green',
        'formData' => $formData,
        'alpineModel' => 'excavationMethod'
    ])
--}}

@php
    $color = $color ?? 'green';
    $alpineModel = $alpineModel ?? 'excavationMethod';
    $hoverColors = [
        'blue' => 'hover:border-blue-500',
        'amber' => 'hover:border-amber-500',
        'green' => 'hover:border-green-500',
    ];
    $textColors = [
        'blue' => 'text-blue-600',
        'amber' => 'text-amber-600',
        'green' => 'text-green-600',
    ];
    $hoverClass = $hoverColors[$color] ?? $hoverColors['green'];
    $textClass = $textColors[$color] ?? $textColors['green'];
@endphp

<div>
    <label class="block text-sm font-semibold text-gray-700 mb-2">Excavation Method</label>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <label class="flex items-center p-3 border-2 border-gray-300 rounded-lg cursor-pointer {{ $hoverClass }} transition">
            <input type="radio" 
                   name="excavation_method" 
                   value="manual" 
                   x-model="{{ $alpineModel }}"
                   class="w-4 h-4 {{ $textClass }}"
                   {{ old('excavation_method', $formData['excavation_method'] ?? 'manual') === 'manual' ? 'checked' : '' }}>
            <span class="ml-3 text-sm font-medium text-gray-900">Manual (Shovels)</span>
        </label>
        <label class="flex items-center p-3 border-2 border-gray-300 rounded-lg cursor-pointer {{ $hoverClass }} transition">
            <input type="radio" 
                   name="excavation_method" 
                   value="mini_skid" 
                   x-model="{{ $alpineModel }}"
                   class="w-4 h-4 {{ $textClass }}"
                   {{ old('excavation_method', $formData['excavation_method'] ?? '') === 'mini_skid' ? 'checked' : '' }}>
            <span class="ml-3 text-sm font-medium text-gray-900">Mini Skid Steer</span>
        </label>
        <label class="flex items-center p-3 border-2 border-gray-300 rounded-lg cursor-pointer {{ $hoverClass }} transition">
            <input type="radio" 
                   name="excavation_method" 
                   value="skid_steer" 
                   x-model="{{ $alpineModel }}"
                   class="w-4 h-4 {{ $textClass }}"
                   {{ old('excavation_method', $formData['excavation_method'] ?? '') === 'skid_steer' ? 'checked' : '' }}>
            <span class="ml-3 text-sm font-medium text-gray-900">Skid Steer</span>
        </label>
    </div>
</div>
