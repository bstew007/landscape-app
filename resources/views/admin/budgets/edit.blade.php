@extends('layouts.sidebar')

@section('content')
@php($initialSalesRows = old('inputs.sales.rows', data_get($budget->inputs, 'sales.rows', [])))
@php($initialHourlyRows = old('inputs.labor.hourly.rows', data_get($budget->inputs, 'labor.hourly.rows', [])))
@php($initialSalaryRows = old('inputs.labor.salary.rows', data_get($budget->inputs, 'labor.salary.rows', [])))
@php($initialLaborBurdenPct = old('inputs.labor.burden_pct', data_get($budget->inputs, 'labor.burden_pct', 0)))
@php($initialOtMultiplier = old('inputs.labor.ot_multiplier', data_get($budget->inputs, 'labor.ot_multiplier', 1.5)))
@php($initialIndustryAvgRatio = old('inputs.labor.industry_avg_ratio', data_get($budget->inputs, 'labor.industry_avg_ratio', 26.6)))
<div class="max-w-7xl mx-auto py-6 text-sm" data-theme="compact" x-data="budgetEditor()">
    <x-page-header title="{{ $budget->exists ? 'Budget' : 'New Budget' }}" eyebrow="Admin" variant="compact">
        <x-slot:leading>
            <div class="h-10 w-10 rounded-full bg-brand-600 text-white flex items-center justify-center shadow-sm">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="14" rx="2"></rect>
                    <path d="M8 10h8M8 14h5"></path>
                    <path d="M12 2v2M7 2v2M17 2v2"></path>
                </svg>
            </div>
        </x-slot:leading>
        <x-slot:actions>
            <x-brand-button href="{{ route('admin.budgets.index') }}">Back</x-brand-button>
            <x-brand-button type="submit" form="companyBudgetForm">Save</x-brand-button>
        </x-slot:actions>
    </x-page-header>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-2 rounded mb-4">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="companyBudgetForm" method="POST" action="{{ $budget->exists ? route('admin.budgets.update', $budget) : route('admin.budgets.store') }}" class="bg-white rounded shadow overflow-hidden text-sm">
        @csrf
        @if ($budget->exists)
            @method('PUT')
        @endif
        <div class="flex">
            <!-- Left Nav -->
            <aside class="w-56 md:w-64 border-r bg-gray-50">
                <nav class="p-2">
                    @foreach (['Budget Info','Sales Budget','Field Labor','Equipment','Materials','Subcontracting','Overhead','Profit/Loss','OH Recovery','Analysis'] as $s)
                        <button type="button"
                                @click="section='{{ $s }}'"
                                :class="{'bg-white text-brand-700 border-brand-300': section==='{{ $s }}'}"
                                class="w-full text-left px-3 py-2 text-sm rounded border hover:bg-white mb-1">
                            {{ $s }}
                        </button>
                    @endforeach
                    <div class="mt-3 pt-3 border-t">
                        <label class="inline-flex items-center text-sm">
                            <input type="checkbox" name="is_active" value="1" class="mr-2" {{ old('is_active', $budget->is_active) ? 'checked' : '' }}>
                            Active Budget
                        </label>
                    </div>
                </nav>
            </aside>

            <!-- Main Panel -->
            <div class="flex-1 p-4 space-y-4 text-sm">
                <!-- BUDGET INFO -->
                <section x-show="section==='Budget Info'" x-cloak>
                    <h2 class="text-lg font-semibold mb-3">Budget Info</h2>
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
                    <div class="grid md:grid-cols-3 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium">Desired Profit Margin (%)</label>
                            <input type="number" step="0.1" min="0" max="99.9" name="desired_profit_margin_percent" class="form-input w-full mt-1" value="{{ old('desired_profit_margin_percent', number_format(($budget->desired_profit_margin ?? 0.2) * 100, 1)) }}">
                            <input type="hidden" name="desired_profit_margin" value="{{ old('desired_profit_margin', $budget->desired_profit_margin ?? 0.2) }}" id="desired_profit_margin_hidden">
                            <p class="text-xs text-gray-500 mt-1">Target company profit margin used in pricing.</p>
                        </div>
                        <div class="md:col-span-2">
                            <div class="rounded border p-3 bg-gray-50 text-sm text-gray-700">
                                Define revenue goals, pricing strategy, and global assumptions here.
                                We’ll expand this section with forecast and sales mix inputs.
                            </div>
                        </div>
                    </div>
                </section>

                <!-- SALES BUDGET -->
                <section x-show="section==='Sales Budget'" x-cloak>
                    <h2 class="text-lg font-semibold mb-3 flex items-center gap-2">
                        <svg class="h-5 w-5 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><rect x="7" y="13" width="3" height="5"/><rect x="12" y="9" width="3" height="9"/><rect x="17" y="5" width="3" height="13"/></svg>
                        <span>SALES BUDGET</span>
                    </h2>
                    <div class="rounded border p-4">
                        <!-- Graphics Row -->
                        <div class="grid md:grid-cols-3 gap-4 mb-4">
                            <!-- Pie: Divisional Sales -->
                            <div class="rounded border p-3">
                                <div class="text-xs uppercase tracking-wide text-gray-500 mb-2">Divisional Sales</div>
                                <div class="flex items-center gap-4">
                                    <div class="relative h-28 w-28 rounded-full"
                                         :style="{ backgroundImage: pieGradient() }">
                                        <div class="absolute inset-3 bg-white rounded-full"></div>
                                    </div>
                                    <div class="flex-1 text-xs">
                                        <template x-for="seg in divisionSegments()" :key="seg.label">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="inline-block w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: seg.color }"></span>
                                                <span x-text="seg.percent.toFixed(0) + '%'" class="tabular-nums"></span>
                                            </div>
                                        </template>
                                        <div x-show="divisionSegments().length === 0" class="text-gray-500">No data</div>
                                    </div>
                                </div>
                            </div>
                            <!-- Prev vs Forecast -->
                            <div class="rounded border p-3">
                                <div class="text-xs uppercase tracking-wide text-gray-500 mb-2">Prev vs Forecast</div>
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between text-xs">
                                        <span>Previous</span>
                                        <span x-text="formatMoney(prevTotal())"></span>
                                    </div>
                                    <div class="h-2 rounded bg-gray-200 overflow-hidden">
                                        <div class="h-2 bg-gray-500" :style="{ width: barWidth(prevTotal()) }"></div>
                                    </div>
                                    <div class="flex items-center justify-between text-xs">
                                        <span>Forecast</span>
                                        <span x-text="formatMoney(forecastTotal())"></span>
                                    </div>
                                    <div class="h-2 rounded bg-brand-200 overflow-hidden">
                                        <div class="h-2 bg-brand-600" :style="{ width: barWidth(forecastTotal()) }"></div>
                                    </div>
                                </div>
                            </div>
                            <!-- Change over Previous -->
                            <div class="rounded border p-3">
                                <div class="text-xs uppercase tracking-wide text-gray-500 mb-2">Change over Previous</div>
                                <div class="flex items-center gap-4">
                                    <div class="relative h-28 w-28 rounded-full" :style="{ backgroundImage: changeRing() }">
                                        <div class="absolute inset-4 bg-white rounded-full flex items-center justify-center text-lg font-semibold">
                                            <span x-text="(changePercent() >= 0 ? '+' : '') + changePercent().toFixed(1) + '%' "></span>
                                        </div>
                                    </div>
                                    <div class="text-xs text-gray-700">
                                        <div class="font-semibold" x-text="(changePercent() >= 0 ? 'Increase' : 'Decrease')"></div>
                                        <div>Total Prev: <span class="font-semibold" x-text="formatMoney(prevTotal())"></span></div>
                                        <div>Total Forecast: <span class="font-semibold" x-text="formatMoney(forecastTotal())"></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Header Row -->
                        <div class="hidden md:grid grid-cols-12 gap-3 text-xs font-medium text-gray-600 border-b pb-2">
                            <div class="col-span-2">Acct. ID</div>
                            <div class="col-span-2">Division</div>
                            <div class="col-span-2">Previous $</div>
                            <div class="col-span-2">Forecast $</div>
                            <div class="col-span-1">% Diff</div>
                            <div class="col-span-2">Comments</div>
                            <div class="col-span-1 text-right">Actions</div>
                        </div>

                        <!-- Rows -->
                        <template x-for="(row, idx) in salesRows" :key="idx">
                            <div class="grid grid-cols-12 gap-3 items-center py-2 border-b">
                                <!-- Acct. ID -->
                                <div class="col-span-12 md:col-span-2">
                                    <label class="md:hidden block text-xs text-gray-500">Acct. ID</label>
                                    <input type="text" class="form-input w-full" x-model="row.account_id" :name="'inputs[sales][rows]['+idx+'][account_id]'" placeholder="e.g., 4001">
                                </div>
                                <!-- Division -->
                                <div class="col-span-12 md:col-span-2">
                                    <label class="md:hidden block text-xs text-gray-500">Division</label>
                                    <input type="text" class="form-input w-full" x-model="row.division" :name="'inputs[sales][rows]['+idx+'][division]'" placeholder="e.g., Maintenance">
                                </div>
                                <!-- Previous $ -->
                                <div class="col-span-6 md:col-span-2">
                                    <label class="md:hidden block text-xs text-gray-500">Previous $</label>
                                    <input type="number" step="0.01" min="0" inputmode="decimal" class="form-input w-full" x-model="row.previous" :name="'inputs[sales][rows]['+idx+'][previous]'" placeholder="0.00">
                                </div>
                                <!-- Forecast $ -->
                                <div class="col-span-6 md:col-span-2">
                                    <label class="md:hidden block text-xs text-gray-500">Forecast $</label>
                                    <input type="number" step="0.01" min="0" inputmode="decimal" class="form-input w-full" x-model="row.forecast" :name="'inputs[sales][rows]['+idx+'][forecast]'" placeholder="0.00">
                                </div>
                                <!-- % Diff -->
                                <div class="col-span-6 md:col-span-1">
                                    <label class="md:hidden block text-xs text-gray-500">% Diff</label>
                                    <input type="text" class="form-input w-full bg-gray-50" :value="computeDiff(row)" readonly tabindex="-1">
                                </div>
                                <!-- Comments -->
                                <div class="col-span-6 md:col-span-2">
                                    <label class="md:hidden block text-xs text-gray-500">Comments</label>
                                    <input type="text" class="form-input w-full" x-model="row.comments" :name="'inputs[sales][rows]['+idx+'][comments]'" placeholder="Notes">
                                </div>
                                <!-- Actions -->
                                <div class="col-span-12 md:col-span-1 flex md:justify-end">
                                    <x-danger-button size="sm" type="button" @click="removeSalesRow(idx)">Delete</x-danger-button>
                                </div>
                            </div>
                        </template>

                        <!-- Add New Row -->
                        <div class="pt-3">
                            <x-brand-button type="button" size="sm" variant="ghost" @click="addSalesRow()">+ New</x-brand-button>
                        </div>
                    </div>
                </section>

                <!-- FIELD LABOR -->
                <section x-show="section==='Field Labor'" x-cloak>
                    <h2 class="text-lg font-semibold mb-3">Field Labor</h2>
                    <div class="rounded border p-4">
                        <!-- Boxes Row -->
                        <div class="grid md:grid-cols-3 gap-4 mb-4">
                            <!-- Key Factors -->
                            <div class="rounded border p-3">
                                <div class="text-xs uppercase tracking-wide text-gray-500 mb-2">Key Factors</div>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Labor Burden (%)</label>
                                        <input type="number" step="0.1" min="0" class="form-input w-full" x-model.number="burdenPct" name="inputs[labor][burden_pct]" placeholder="0.0">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Overtime Multiplier</label>
                                        <select class="form-select w-full" x-model.number="otMultiplier" name="inputs[labor][ot_multiplier]">
                                            <template x-for="opt in overtimeOptions()" :key="opt">
                                                <option :value="opt" x-text="opt.toFixed(2) + 'x'"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- Field Labor Summary -->
                            <div class="rounded border p-3">
                                <div class="text-xs uppercase tracking-wide text-gray-500 mb-2">Field Labor Summary</div>
                                <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-sm">
                                    <div class="text-gray-600">Total Hrs</div>
                                    <div class="text-right font-semibold" x-text="(totalHours()).toLocaleString()"></div>
                                    <div class="text-gray-600">Total Wages</div>
                                    <div class="text-right font-semibold" x-text="formatMoney(totalWages())"></div>
                                    <div class="text-gray-600">Total Burden</div>
                                    <div class="text-right font-semibold" x-text="formatMoney(totalBurden())"></div>
                                    <div class="text-gray-600">Field Payroll</div>
                                    <div class="text-right font-semibold" x-text="formatMoney(fieldPayroll())"></div>
                                </div>
                            </div>
                            <!-- Field Labor Ratio -->
                            <div class="rounded border p-3">
                                <div class="text-xs uppercase tracking-wide text-gray-500 mb-2">Field Labor Ratio</div>
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm text-gray-600">Your Ratio</div>
                                        <div class="text-base font-semibold" x-text="laborRatio().toFixed(1) + '%'" ></div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Industry Avg (%)</label>
                                        <input type="number" step="0.1" min="0" class="form-input w-full" x-model.number="industryAvgRatio" name="inputs[labor][industry_avg_ratio]" placeholder="26.6">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Tabs -->
                        <div class="inline-flex rounded-md border overflow-hidden mb-4">
                            <button type="button" class="px-3 py-1.5 text-sm" :class="{ 'bg-gray-200 text-gray-900' : laborTab==='hourly' }" @click="laborTab='hourly'">Hourly Field Staff</button>
                            <button type="button" class="px-3 py-1.5 text-sm border-l" :class="{ 'bg-gray-200 text-gray-900' : laborTab==='salary' }" @click="laborTab='salary'">Salary Field Staff</button>
                        </div>

                        <!-- Hourly Table -->
                        <div x-show="laborTab==='hourly'" class="space-y-2">
                            <div class="hidden md:grid grid-cols-12 gap-2 text-xs font-medium text-gray-600 border-b pb-2">
                                <div class="col-span-2">Employee Type</div>
                                <div class="col-span-1"># Staff</div>
                                <div class="col-span-2">Hrs/Yr (Ea)</div>
                                <div class="col-span-2">OT Hrs (Ea)</div>
                                <div class="col-span-2">Avg Wage</div>
                                <div class="col-span-2">Bonus</div>
                                <div class="col-span-1 text-right">Wages/Yr</div>
                            </div>
                            <template x-for="(row, idx) in hourlyRows" :key="'h'+idx">
                                <div class="grid grid-cols-12 gap-2 items-center py-2 border-b">
                                    <div class="col-span-12 md:col-span-2">
                                        <label class="md:hidden block text-xs text-gray-500">Employee Type</label>
                                        <input type="text" class="form-input w-full" x-model="row.type" :name="'inputs[labor][hourly][rows]['+idx+'][type]'" placeholder="e.g., Crew Lead">
                                    </div>
                                    <div class="col-span-6 md:col-span-1">
                                        <label class="md:hidden block text-xs text-gray-500"># Staff</label>
                                        <input type="number" step="1" min="0" class="form-input w-full" x-model="row.staff" :name="'inputs[labor][hourly][rows]['+idx+'][staff]'" placeholder="0">
                                    </div>
                                    <div class="col-span-6 md:col-span-2">
                                        <label class="md:hidden block text-xs text-gray-500">Hrs/Yr (Ea)</label>
                                        <input type="number" step="1" min="0" class="form-input w-full" x-model="row.hrs" :name="'inputs[labor][hourly][rows]['+idx+'][hrs]'" placeholder="2080">
                                    </div>
                                    <div class="col-span-6 md:col-span-2">
                                        <label class="md:hidden block text-xs text-gray-500">OT Hrs (Ea)</label>
                                        <input type="number" step="1" min="0" class="form-input w-full" x-model="row.ot_hrs" :name="'inputs[labor][hourly][rows]['+idx+'][ot_hrs]'" placeholder="0">
                                    </div>
                                    <div class="col-span-6 md:col-span-2">
                                        <label class="md:hidden block text-xs text-gray-500">Avg Wage</label>
                                        <input type="number" step="0.01" min="0" class="form-input w-full" x-model="row.avg_wage" :name="'inputs[labor][hourly][rows]['+idx+'][avg_wage]'" placeholder="0.00">
                                    </div>
                                    <div class="col-span-6 md:col-span-2">
                                        <label class="md:hidden block text-xs text-gray-500">Bonus</label>
                                        <input type="number" step="0.01" min="0" class="form-input w-full" x-model="row.bonus" :name="'inputs[labor][hourly][rows]['+idx+'][bonus]'" placeholder="0.00">
                                    </div>
                                    <div class="col-span-10 md:col-span-1 text-right font-semibold">
                                        <span x-text="formatMoney(wagesHourlyRow(row))"></span>
                                    </div>
                                    <div class="col-span-2 md:col-span-12 md:text-right">
                                        <x-danger-button size="sm" type="button" @click="removeHourlyRow(idx)">Delete</x-danger-button>
                                    </div>
                                </div>
                            </template>
                            <div class="pt-3">
                                <x-brand-button type="button" size="sm" variant="ghost" @click="addHourlyRow()">+ New</x-brand-button>
                            </div>
                        </div>

                        <!-- Salary Table -->
                        <div x-show="laborTab==='salary'" class="space-y-2">
                            <div class="hidden md:grid grid-cols-12 gap-2 text-xs font-medium text-gray-600 border-b pb-2">
                                <div class="col-span-3">Employee Type</div>
                                <div class="col-span-1"># Staff</div>
                                <div class="col-span-2">Ann Hrs (Ea)</div>
                                <div class="col-span-2">Ann Salary (Ea)</div>
                                <div class="col-span-2">Bonus</div>
                                <div class="col-span-2 text-right">Ann. Wages</div>
                            </div>
                            <template x-for="(row, idx) in salaryRows" :key="'s'+idx">
                                <div class="grid grid-cols-12 gap-2 items-center py-2 border-b">
                                    <div class="col-span-12 md:col-span-3">
                                        <label class="md:hidden block text-xs text-gray-500">Employee Type</label>
                                        <input type="text" class="form-input w-full" x-model="row.type" :name="'inputs[labor][salary][rows]['+idx+'][type]'" placeholder="e.g., Supervisor">
                                    </div>
                                    <div class="col-span-6 md:col-span-1">
                                        <label class="md:hidden block text-xs text-gray-500"># Staff</label>
                                        <input type="number" step="1" min="0" class="form-input w-full" x-model="row.staff" :name="'inputs[labor][salary][rows]['+idx+'][staff]'" placeholder="0">
                                    </div>
                                    <div class="col-span-6 md:col-span-2">
                                        <label class="md:hidden block text-xs text-gray-500">Ann Hrs (Ea)</label>
                                        <input type="number" step="1" min="0" class="form-input w-full" x-model="row.ann_hrs" :name="'inputs[labor][salary][rows]['+idx+'][ann_hrs]'" placeholder="2080">
                                    </div>
                                    <div class="col-span-6 md:col-span-2">
                                        <label class="md:hidden block text-xs text-gray-500">Ann Salary (Ea)</label>
                                        <input type="number" step="0.01" min="0" class="form-input w-full" x-model="row.ann_salary" :name="'inputs[labor][salary][rows]['+idx+'][ann_salary]'" placeholder="0.00">
                                    </div>
                                    <div class="col-span-6 md:col-span-2">
                                        <label class="md:hidden block text-xs text-gray-500">Bonus</label>
                                        <input type="number" step="0.01" min="0" class="form-input w-full" x-model="row.bonus" :name="'inputs[labor][salary][rows]['+idx+'][bonus]'" placeholder="0.00">
                                    </div>
                                    <div class="col-span-10 md:col-span-2 text-right font-semibold">
                                        <span x-text="formatMoney(wagesSalaryRow(row))"></span>
                                    </div>
                                    <div class="col-span-2 md:col-span-12 md:text-right">
                                        <x-danger-button size="sm" type="button" @click="removeSalaryRow(idx)">Delete</x-danger-button>
                                    </div>
                                </div>
                            </template>
                            <div class="pt-3">
                                <x-brand-button type="button" size="sm" variant="ghost" @click="addSalaryRow()">+ New</x-brand-button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- EQUIPMENT -->
                <section x-show="section==='Equipment'" x-cloak>
                    <h2 class="text-lg font-semibold mb-3">Equipment</h2>
                    <div class="rounded border p-4 bg-gray-50 text-sm text-gray-700">
                        Define owned/leased equipment cost structure, rates, and utilization. (Coming soon)
                    </div>
                </section>

                <!-- MATERIALS -->
                <section x-show="section==='Materials'" x-cloak>
                    <h2 class="text-lg font-semibold mb-3">Materials</h2>
                    <div class="rounded border p-4 bg-gray-50 text-sm text-gray-700">
                        Configure material markups, waste factors, and category-specific assumptions. (Coming soon)
                    </div>
                </section>

                <!-- SUBCONTRACTING -->
                <section x-show="section==='Subcontracting'" x-cloak>
                    <h2 class="text-lg font-semibold mb-3">Subcontracting</h2>
                    <div class="rounded border p-4 bg-gray-50 text-sm text-gray-700">
                        Define subcontractor fees, markups, and usage assumptions. (Coming soon)
                    </div>
                </section>

                <!-- OVERHEAD -->
                <section x-show="section==='Overhead'" x-cloak>
                    <h2 class="text-lg font-semibold mb-3">Overhead</h2>
                    <div class="rounded border p-4">
                        <div class="grid md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium">Annual Overhead ($)</label>
                                <input type="number" step="0.01" min="0" name="inputs[overhead][total]" class="form-input w-full mt-1" value="{{ old('inputs.overhead.total', data_get($budget->inputs, 'overhead.total', 150000)) }}">
                            </div>
                        </div>
                    </div>
                </section>

                <!-- PROFIT / LOSS -->
                <section x-show="section==='Profit/Loss'" x-cloak>
                    <h2 class="text-lg font-semibold mb-3">Profit / Loss</h2>
                    <div class="rounded border p-4 bg-gray-50 text-sm text-gray-700">
                        High-level P&L view and targets will appear here. (Coming soon)
                    </div>
                </section>

                <!-- OH RECOVERY -->
                <section x-show="section==='OH Recovery'" x-cloak>
                    <h2 class="text-lg font-semibold mb-3">Overhead Recovery</h2>
                    <div class="rounded border p-4 bg-gray-50 text-sm text-gray-700">
                        Configure recovery method (e.g., labor-based, revenue-based) and allocations. (Coming soon)
                    </div>
                </section>

                <!-- ANALYSIS -->
                <section x-show="section==='Analysis'" x-cloak>
                    <h2 class="text-lg font-semibold mb-3">Analysis</h2>
                    <div class="grid md:grid-cols-4 gap-4 text-sm">
                        <div class="rounded border p-3">
                            <p class="text-gray-600">Direct Labor Cost</p>
                            <p class="font-semibold">${{ number_format(data_get($budget->outputs ?? [], 'labor.dlc', 0), 2) }}/hr</p>
                        </div>
                        <div class="rounded border p-3">
                            <p class="text-gray-600">Overhead / Prod. Hour</p>
                            <p class="font-semibold">${{ number_format(data_get($budget->outputs ?? [], 'labor.ohr', 0), 2) }}/hr</p>
                        </div>
                        <div class="rounded border p-3">
                            <p class="text-gray-600">Burdened Labor Cost</p>
                            <p class="font-semibold">${{ number_format(data_get($budget->outputs ?? [], 'labor.blc', 0), 2) }}/hr</p>
                        </div>
                        <div class="rounded border p-3">
                            <p class="text-gray-600">Productive Hours (annual)</p>
                            <p class="font-semibold">{{ number_format(data_get($budget->outputs ?? [], 'labor.plh', 0), 0) }}</p>
                        </div>
                    </div>
                </section>

                <div class="flex justify-end">
                    <x-brand-button type="submit">Save Budget</x-brand-button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Seed initial Sales rows from server
    window.__initialSalesRows = @json($initialSalesRows);
    window.__initialHourlyRows = @json($initialHourlyRows);
    window.__initialSalaryRows = @json($initialSalaryRows);
    window.__initialLaborBurdenPct = @json($initialLaborBurdenPct);
    window.__initialOtMultiplier = @json($initialOtMultiplier);
    window.__initialIndustryAvgRatio = @json($initialIndustryAvgRatio);

    // Alpine data for the budget editor
    window.budgetEditor = function(){
        return {
            section: 'Budget Info',
            salesRows: Array.isArray(window.__initialSalesRows) ? window.__initialSalesRows : [],
            laborTab: 'hourly',
            hourlyRows: Array.isArray(window.__initialHourlyRows) ? window.__initialHourlyRows : [],
            salaryRows: Array.isArray(window.__initialSalaryRows) ? window.__initialSalaryRows : [],
            burdenPct: parseFloat(window.__initialLaborBurdenPct) || 0,
            otMultiplier: parseFloat(window.__initialOtMultiplier) || 1.5,
            industryAvgRatio: parseFloat(window.__initialIndustryAvgRatio) || 26.6,
            addSalesRow() { this.salesRows.push({ account_id: '', division: '', previous: '', forecast: '', comments: '' }); },
            removeSalesRow(i) { this.salesRows.splice(i, 1); },
            addHourlyRow(){ this.hourlyRows.push({ type:'', staff:'', hrs:'', ot_hrs:'', avg_wage:'', bonus:'' }); },
            removeHourlyRow(i){ this.hourlyRows.splice(i,1); },
            addSalaryRow(){ this.salaryRows.push({ type:'', staff:'', ann_hrs:'', ann_salary:'', bonus:'' }); },
            removeSalaryRow(i){ this.salaryRows.splice(i,1); },
            computeDiff(row) {
                const p = parseFloat(row.previous) || 0;
                const f = parseFloat(row.forecast) || 0;
                if (!p) return f === 0 ? '0%' : '—';
                const pct = ((f - p) / Math.abs(p)) * 100;
                return pct.toFixed(1) + '%';
            },
            // Formatting helpers
            formatMoney(n){ const v = parseFloat(n) || 0; return '$' + v.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },
            // Totals
            prevTotal(){ return this.salesRows.reduce((s, r) => s + (parseFloat(r.previous) || 0), 0); },
            forecastTotal(){ return this.salesRows.reduce((s, r) => s + (parseFloat(r.forecast) || 0), 0); },
            barWidth(val){ const max = Math.max(this.prevTotal(), this.forecastTotal(), 1); return Math.round((Math.max(0, val) / max) * 100) + '%'; },
            // Hourly wages computation
            wagesHourlyPerEmp(row){
                const hrs = parseFloat(row.hrs) || 0;
                const ot = parseFloat(row.ot_hrs) || 0;
                const wage = parseFloat(row.avg_wage) || 0;
                const bonus = parseFloat(row.bonus) || 0;
                const mult = parseFloat(this.otMultiplier) || 1.5;
                return (hrs * wage) + (ot * wage * mult) + bonus;
            },
            wagesHourlyRow(row){
                const staff = parseFloat(row.staff) || 0;
                return staff * this.wagesHourlyPerEmp(row);
            },
            // Salary wages computation
            wagesSalaryPerEmp(row){
                const sal = parseFloat(row.ann_salary) || 0;
                const bonus = parseFloat(row.bonus) || 0;
                return sal + bonus;
            },
            wagesSalaryRow(row){
                const staff = parseFloat(row.staff) || 0;
                return staff * this.wagesSalaryPerEmp(row);
            },
            // Totals and ratios
            totalHours(){
                const h = this.hourlyRows.reduce((s, r) => s + ( (parseFloat(r.staff)||0) * ( (parseFloat(r.hrs)||0) + (parseFloat(r.ot_hrs)||0) ) , 0), 0);
                const s = this.salaryRows.reduce((t, r) => t + ( (parseFloat(r.staff)||0) * (parseFloat(r.ann_hrs)||0) ), 0);
                return Math.round(h + s);
            },
            totalWages(){
                const h = this.hourlyRows.reduce((s, r) => s + this.wagesHourlyRow(r), 0);
                const s = this.salaryRows.reduce((t, r) => t + this.wagesSalaryRow(r), 0);
                return h + s;
            },
            totalBurden(){
                const pct = (parseFloat(this.burdenPct) || 0) / 100;
                return this.totalWages() * pct;
            },
            fieldPayroll(){
                return this.totalWages() + this.totalBurden();
            },
            laborRatio(){
                const sales = this.forecastTotal();
                if (!sales) return 0;
                return (this.fieldPayroll() / Math.abs(sales)) * 100;
            },
            overtimeOptions(){
                const out = [];
                for (let v = 1.25; v <= 3.0001; v += 0.25) out.push(Number(v.toFixed(2)));
                return out;
            },
            // Division segments for pie
            divisionSegments(){
                const map = new Map();
                this.salesRows.forEach(r => {
                    const key = (r.division || '').trim() || 'Unassigned';
                    const v = parseFloat(r.forecast) || 0;
                    map.set(key, (map.get(key) || 0) + v);
                });
                const total = Array.from(map.values()).reduce((a,b)=>a+b,0);
                const palette = ['#2563eb','#16a34a','#f59e0b','#dc2626','#7c3aed','#0ea5e9','#ea580c','#22c55e','#e11d48'];
                let i = 0;
                return Array.from(map.entries()).map(([label, value]) => ({
                    label,
                    value,
                    percent: total > 0 ? (value / total) * 100 : 0,
                    color: palette[i++ % palette.length],
                }));
            },
            pieGradient(){
                const segs = this.divisionSegments();
                if (!segs.length) return 'conic-gradient(#e5e7eb 0 360deg)';
                let acc = 0;
                const parts = segs.map(seg => {
                    const start = acc;
                    const sweep = (seg.percent / 100) * 360;
                    const end = start + sweep;
                    acc = end;
                    return `${seg.color} ${start}deg ${end}deg`;
                });
                if (acc < 360) parts.push(`#e5e7eb ${acc}deg 360deg`);
                return `conic-gradient(${parts.join(',')})`;
            },
            // Change ring
            changePercent(){
                const p = this.prevTotal();
                const f = this.forecastTotal();
                if (!p) return f === 0 ? 0 : 100; // if no previous, treat as 100% change when forecast > 0
                return ((f - p) / Math.abs(p)) * 100;
            },
            changeRing(){
                const c = this.changePercent();
                const pct = Math.max(0, Math.min(100, Math.abs(c)));
                const color = c >= 0 ? '#16a34a' : '#dc2626';
                return `conic-gradient(${color} 0 ${pct}%, #e5e7eb ${pct}%)`;
            }
        };
    };

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
