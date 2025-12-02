{{-- Job Costing Tab --}}
<div class="space-y-6">
    {{-- Financial Summary --}}
    @include('jobs.partials.financial-summary', ['job' => $job])

    {{-- Work Areas Breakdown --}}
    <div class="bg-white rounded-2xl border border-brand-100 shadow-sm">
        <div class="px-6 py-4 border-b border-brand-200">
            <h3 class="text-lg font-semibold text-gray-900">Work Areas Breakdown</h3>
        </div>
        <div class="p-6 space-y-4">
            @forelse($job->workAreas as $area)
                @include('jobs.partials.work-area-card', ['area' => $area])
            @empty
                <p class="text-gray-500 text-center py-4">No work areas defined</p>
            @endforelse
        </div>
    </div>

    {{-- Budget Variance Analysis --}}
    <div class="bg-white rounded-2xl border border-brand-100 shadow-sm">
        <div class="px-6 py-4 border-b border-brand-200">
            <h3 class="text-lg font-semibold text-gray-900">Budget Variance</h3>
        </div>
        <div class="p-6">
            @php
                // Budget (Breakeven) is the estimated cost from work areas
                $budgetTotal = $job->estimated_cost ?? 0;
                
                // Actual is what's been spent
                $actualTotal = $job->actual_total_cost ?? 0;
                
                // Variance is Budget - Actual (positive = under budget, negative = over budget)
                $variance = $budgetTotal - $actualTotal;
                $variancePercent = $budgetTotal > 0 ? ($variance / $budgetTotal) * 100 : 0;
                
                // Calculate labor and material breakdowns from work areas
                $budgetLabor = $job->workAreas->sum('estimated_labor_cost');
                $budgetMaterial = $job->workAreas->sum('estimated_material_cost');
                $actualLabor = $job->actual_labor_cost ?? 0;
                $actualMaterial = $job->actual_material_cost ?? 0;
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <p class="text-sm text-gray-500 uppercase tracking-wider mb-2">Budget (Breakeven)</p>
                    <p class="text-2xl font-bold text-gray-900">${{ number_format($budgetTotal, 2) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Cost to complete work</p>
                </div>
                
                <div class="text-center">
                    <p class="text-sm text-gray-500 uppercase tracking-wider mb-2">Actual Spent</p>
                    <p class="text-2xl font-bold text-gray-900">${{ number_format($actualTotal, 2) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Current spending</p>
                </div>
                
                <div class="text-center">
                    <p class="text-sm text-gray-500 uppercase tracking-wider mb-2">Variance</p>
                    <p class="text-2xl font-bold {{ $variance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $variance >= 0 ? '+' : '' }}${{ number_format($variance, 2) }}
                    </p>
                    <p class="text-sm {{ $variance >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1">
                        {{ $variance >= 0 ? '+' : '' }}{{ number_format($variancePercent, 1) }}%
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $variance >= 0 ? 'Under budget' : 'Over budget' }}
                    </p>
                </div>
            </div>

            {{-- Labor vs Materials Breakdown --}}
            <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Labor --}}
                <div class="border border-brand-200 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Labor Costs</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Budget (Breakeven):</span>
                            <span class="font-medium text-gray-900">${{ number_format($budgetLabor, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Actual:</span>
                            <span class="font-medium text-gray-900">${{ number_format($actualLabor, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm pt-2 border-t border-gray-200">
                            <span class="text-gray-600">Variance:</span>
                            @php
                                $laborVariance = $budgetLabor - $actualLabor;
                            @endphp
                            <span class="font-semibold {{ $laborVariance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $laborVariance >= 0 ? '+' : '' }}${{ number_format($laborVariance, 2) }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Materials --}}
                <div class="border border-brand-200 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Material Costs</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Budget (Breakeven):</span>
                            <span class="font-medium text-gray-900">${{ number_format($budgetMaterial, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Actual:</span>
                            <span class="font-medium text-gray-900">${{ number_format($actualMaterial, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm pt-2 border-t border-gray-200">
                            <span class="text-gray-600">Variance:</span>
                            @php
                                $materialVariance = $budgetMaterial - $actualMaterial;
                            @endphp
                            <span class="font-semibold {{ $materialVariance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $materialVariance >= 0 ? '+' : '' }}${{ number_format($materialVariance, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
