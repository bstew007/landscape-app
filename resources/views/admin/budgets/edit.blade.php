@extends('layouts.sidebar')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">{{ $budget->exists ? 'Edit Budget' : 'New Budget' }}</h1>
        <a href="{{ route('admin.budgets.index') }}" class="px-4 py-2 rounded border">Back</a>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-2 rounded">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $budget->exists ? route('admin.budgets.update', $budget) : route('admin.budgets.store') }}" class="space-y-6 bg-white rounded shadow p-6">
        @csrf
        @if ($budget->exists)
            @method('PUT')
        @endif

        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium">Name</label>
                <input type="text" name="name" class="form-input w-full mt-1" value="{{ old('name', $budget->name) }}" required>
            </div>
            <div>
                <label class="block text-sm font-medium">Year</label>
                <input type="number" name="year" class="form-input w-full mt-1" value="{{ old('year', $budget->year) }}" min="2000" max="2100">
            </div>
            <div>
                <label class="block text-sm font-medium">Effective From</label>
                <input type="date" name="effective_from" class="form-input w-full mt-1" value="{{ old('effective_from', optional($budget->effective_from)->format('Y-m-d')) }}">
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium">Desired Profit Margin (%)</label>
                <input type="number" step="0.1" min="0" max="99.9" name="desired_profit_margin_percent" class="form-input w-full mt-1" value="{{ old('desired_profit_margin_percent', number_format(($budget->desired_profit_margin ?? 0.2) * 100, 1)) }}">
                <input type="hidden" name="desired_profit_margin" value="{{ old('desired_profit_margin', $budget->desired_profit_margin ?? 0.2) }}" id="desired_profit_margin_hidden">
                <p class="text-xs text-gray-500 mt-1">Used to compute target charge-out rate.</p>
            </div>
            <div class="flex items-end">
                <label class="inline-flex items-center mt-6">
                    <input type="checkbox" name="is_active" value="1" class="mr-2" {{ old('is_active', $budget->is_active) ? 'checked' : '' }}>
                    Make Active
                </label>
            </div>
        </div>

        <div class="rounded border p-4">
            <h3 class="font-semibold mb-3">Labor Inputs</h3>
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium">Headcount</label>
                    <input type="number" step="0.1" min="0" name="inputs[labor][headcount]" class="form-input w-full mt-1" value="{{ old('inputs.labor.headcount', data_get($budget->inputs, 'labor.headcount', 5)) }}">
                </div>
                <div>
                    <label class="block text-sm font-medium">Wage ($/hr)</label>
                    <input type="number" step="0.01" min="0" name="inputs[labor][wage]" class="form-input w-full mt-1" value="{{ old('inputs.labor.wage', data_get($budget->inputs, 'labor.wage', 25)) }}">
                </div>
                <div>
                    <label class="block text-sm font-medium">Payroll Taxes (% of wage)</label>
                    <input type="number" step="0.1" min="0" name="inputs[labor][payroll_taxes]" class="form-input w-full mt-1" value="{{ old('inputs.labor.payroll_taxes', data_get($budget->inputs, 'labor.payroll_taxes', 9)) }}">
                </div>
                <div>
                    <label class="block text-sm font-medium">Benefits (% of wage)</label>
                    <input type="number" step="0.1" min="0" name="inputs[labor][benefits]" class="form-input w-full mt-1" value="{{ old('inputs.labor.benefits', data_get($budget->inputs, 'labor.benefits', 12)) }}">
                </div>
                <div>
                    <label class="block text-sm font-medium">Workers Comp (% of wage)</label>
                    <input type="number" step="0.1" min="0" name="inputs[labor][workers_comp]" class="form-input w-full mt-1" value="{{ old('inputs.labor.workers_comp', data_get($budget->inputs, 'labor.workers_comp', 3)) }}">
                </div>
                <div>
                    <label class="block text-sm font-medium">PTO Hours (per person)</label>
                    <input type="number" step="0.1" min="0" name="inputs[labor][pto_hours]" class="form-input w-full mt-1" value="{{ old('inputs.labor.pto_hours', data_get($budget->inputs, 'labor.pto_hours', 80)) }}">
                </div>
                <div>
                    <label class="block text-sm font-medium">Hours per Week</label>
                    <input type="number" step="0.1" min="0" name="inputs[labor][hours_per_week]" class="form-input w-full mt-1" value="{{ old('inputs.labor.hours_per_week', data_get($budget->inputs, 'labor.hours_per_week', 40)) }}">
                </div>
                <div>
                    <label class="block text-sm font-medium">Weeks per Year</label>
                    <input type="number" step="0.1" min="0" name="inputs[labor][weeks_per_year]" class="form-input w-full mt-1" value="{{ old('inputs.labor.weeks_per_year', data_get($budget->inputs, 'labor.weeks_per_year', 52)) }}">
                </div>
                <div>
                    <label class="block text-sm font-medium">Utilization (0-1)</label>
                    <input type="number" step="0.01" min="0" max="1" name="inputs[labor][utilization]" class="form-input w-full mt-1" value="{{ old('inputs.labor.utilization', data_get($budget->inputs, 'labor.utilization', 0.85)) }}">
                </div>
                <div>
                    <label class="block text-sm font-medium">Productivity (0-1)</label>
                    <input type="number" step="0.01" min="0" max="1" name="inputs[labor][productivity]" class="form-input w-full mt-1" value="{{ old('inputs.labor.productivity', data_get($budget->inputs, 'labor.productivity', 0.95)) }}">
                </div>
            </div>
        </div>

        <div class="rounded border p-4">
            <h3 class="font-semibold mb-3">Overhead</h3>
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium">Annual Overhead ($)</label>
                    <input type="number" step="0.01" min="0" name="inputs[overhead][total]" class="form-input w-full mt-1" value="{{ old('inputs.overhead.total', data_get($budget->inputs, 'overhead.total', 150000)) }}">
                </div>
            </div>
        </div>

        <div class="rounded border p-4">
            <h3 class="font-semibold mb-3">Outputs (computed)</h3>
            @php
                $outputs = $budget->outputs ?? [];
                $dlc = data_get($outputs, 'labor.dlc', 0);
                $ohr = data_get($outputs, 'labor.ohr', 0);
                $blc = data_get($outputs, 'labor.blc', 0);
                $plh = data_get($outputs, 'labor.plh', 0);
            @endphp
            <div class="grid md:grid-cols-4 gap-4 text-sm">
                <div>
                    <p class="text-gray-600">Direct Labor Cost</p>
                    <p class="font-semibold">${{ number_format($dlc, 2) }}/hr</p>
                </div>
                <div>
                    <p class="text-gray-600">Overhead / Prod. Hour</p>
                    <p class="font-semibold">${{ number_format($ohr, 2) }}/hr</p>
                </div>
                <div>
                    <p class="text-gray-600">Burdened Labor Cost</p>
                    <p class="font-semibold">${{ number_format($blc, 2) }}/hr</p>
                </div>
                <div>
                    <p class="text-gray-600">Productive Hours (annual)</p>
                    <p class="font-semibold">{{ number_format($plh, 0) }}</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save Budget</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const percent = document.querySelector('input[name="desired_profit_margin_percent"]');
        const hidden = document.getElementById('desired_profit_margin_hidden');
        if (percent && hidden) {
            percent.addEventListener('input', () => {
                const p = parseFloat(percent.value || '0');
                hidden.value = (isFinite(p) ? Math.min(Math.max(p, 0), 99.9) / 100 : 0).toFixed(4);
            });
        }
    });
</script>
@endpush
