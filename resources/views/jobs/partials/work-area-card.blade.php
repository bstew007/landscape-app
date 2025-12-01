<div class="bg-brand-50 rounded-lg p-4 border border-brand-200">
    <div class="flex justify-between items-start mb-3">
        <div class="flex-1">
            <h4 class="text-sm font-semibold text-gray-900">{{ $area->name }}</h4>
            @if($area->estimateArea)
                <p class="text-xs text-gray-500 mt-1">{{ $area->estimateArea->identifier }}</p>
            @endif
        </div>
        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
            {{ $area->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
            {{ $area->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
            {{ $area->status === 'not_started' ? 'bg-gray-100 text-gray-800' : '' }}">
            {{ ucwords(str_replace('_', ' ', $area->status)) }}
        </span>
    </div>

    <div class="grid grid-cols-3 gap-4 text-sm">
        <div>
            <p class="text-gray-500 text-xs">Estimated Labor</p>
            <p class="font-medium text-gray-900">{{ number_format($area->estimated_labor_hours, 1) }} hrs</p>
            <p class="text-gray-600">${{ number_format($area->estimated_labor_cost, 2) }}</p>
        </div>
        <div>
            <p class="text-gray-500 text-xs">Actual Labor</p>
            <p class="font-medium text-gray-900">{{ number_format($area->actual_labor_hours, 1) }} hrs</p>
            <p class="text-gray-600">${{ number_format($area->actual_labor_cost, 2) }}</p>
        </div>
        <div>
            <p class="text-gray-500 text-xs">Materials</p>
            <p class="text-gray-600">Est: ${{ number_format($area->estimated_material_cost, 2) }}</p>
            <p class="text-gray-600">Act: ${{ number_format($area->actual_material_cost, 2) }}</p>
        </div>
    </div>

    @if($area->variance_total != 0)
        <div class="mt-3 pt-3 border-t border-brand-200">
            <div class="flex justify-between text-xs">
                <span class="text-gray-500">Variance:</span>
                <span class="font-medium {{ $area->variance_total >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $area->variance_total >= 0 ? '+' : '' }}${{ number_format(abs($area->variance_total), 2) }}
                    ({{ number_format($area->variance_percent, 1) }}%)
                </span>
            </div>
        </div>
    @endif
</div>
