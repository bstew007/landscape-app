<div class="bg-white rounded-2xl border border-brand-100 shadow-sm p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Financial Summary</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Estimated --}}
        <div>
            <h4 class="text-sm font-medium text-gray-500 mb-2">Estimated</h4>
            <dl class="space-y-1">
                <div class="flex justify-between text-sm">
                    <dt class="text-gray-600">Revenue</dt>
                    <dd class="font-medium text-gray-900">${{ number_format($job->estimated_revenue, 2) }}</dd>
                </div>
                <div class="flex justify-between text-sm">
                    <dt class="text-gray-600">Cost</dt>
                    <dd class="font-medium text-gray-900">${{ number_format($job->estimated_cost, 2) }}</dd>
                </div>
                <div class="flex justify-between text-sm pt-1 border-t border-gray-200">
                    <dt class="text-gray-600 font-medium">Profit</dt>
                    <dd class="font-semibold text-green-600">${{ number_format($job->estimated_profit, 2) }}</dd>
                </div>
            </dl>
        </div>

        {{-- Actual --}}
        <div>
            <h4 class="text-sm font-medium text-gray-500 mb-2">Actual</h4>
            <dl class="space-y-1">
                <div class="flex justify-between text-sm">
                    <dt class="text-gray-600">Labor Cost</dt>
                    <dd class="font-medium text-gray-900">${{ number_format($job->actual_labor_cost, 2) }}</dd>
                </div>
                <div class="flex justify-between text-sm">
                    <dt class="text-gray-600">Material Cost</dt>
                    <dd class="font-medium text-gray-900">${{ number_format($job->actual_material_cost, 2) }}</dd>
                </div>
                <div class="flex justify-between text-sm pt-1 border-t border-gray-200">
                    <dt class="text-gray-600 font-medium">Total Cost</dt>
                    <dd class="font-semibold text-gray-900">${{ number_format($job->actual_total_cost, 2) }}</dd>
                </div>
            </dl>
        </div>

        {{-- Variance --}}
        <div>
            <h4 class="text-sm font-medium text-gray-500 mb-2">Variance</h4>
            <dl class="space-y-1">
                <div class="flex justify-between text-sm">
                    <dt class="text-gray-600">Amount</dt>
                    <dd class="font-medium {{ $job->variance_total >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $job->variance_total >= 0 ? '+' : '' }}${{ number_format(abs($job->variance_total), 2) }}
                    </dd>
                </div>
                <div class="flex justify-between text-sm">
                    <dt class="text-gray-600">Percentage</dt>
                    <dd class="font-medium {{ $job->variance_percent >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $job->variance_percent >= 0 ? '+' : '' }}{{ number_format($job->variance_percent, 1) }}%
                    </dd>
                </div>
                <div class="flex justify-between text-sm pt-1 border-t border-gray-200">
                    <dt class="text-gray-600 font-medium">Actual Profit</dt>
                    <dd class="font-semibold {{ $job->actual_profit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        ${{ number_format($job->actual_profit, 2) }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</div>
