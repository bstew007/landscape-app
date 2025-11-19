@extends('layouts.sidebar')

@section('content')
@php($initialSalesRows = old('inputs.sales.rows', data_get($budget->inputs, 'sales.rows', [])))
@php($initialHourlyRows = old('inputs.labor.hourly.rows', data_get($budget->inputs, 'labor.hourly.rows', [])))
@php($initialSalaryRows = old('inputs.labor.salary.rows', data_get($budget->inputs, 'labor.salary.rows', [])))
@php($initialLaborBurdenPct = old('inputs.labor.burden_pct', data_get($budget->inputs, 'labor.burden_pct', 0)))
@php($initialOtMultiplier = old('inputs.labor.ot_multiplier', data_get($budget->inputs, 'labor.ot_multiplier', 1.5)))
@php($initialIndustryAvgRatio = old('inputs.labor.industry_avg_ratio', data_get($budget->inputs, 'labor.industry_avg_ratio', 26.6)))
@php($initialEquipmentRows = old('inputs.equipment.rows', data_get($budget->inputs, 'equipment.rows', [])))
@php($initialEquipmentGeneral = old('inputs.equipment.general', data_get($budget->inputs, 'equipment.general', ['fuel'=>0,'repairs'=>0,'insurance_misc'=>0])))
@php($initialEquipmentRentals = old('inputs.equipment.rentals', data_get($budget->inputs, 'equipment.rentals', 0)))
@php($initialEquipmentIndustryAvg = old('inputs.equipment.industry_avg_ratio', data_get($budget->inputs, 'equipment.industry_avg_ratio', 13.7)))
@php($initialMaterialsRows = old('inputs.materials.rows', data_get($budget->inputs, 'materials.rows', [])))
@php($initialMaterialsTaxPct = old('inputs.materials.tax_pct', data_get($budget->inputs, 'materials.tax_pct', 0)))
@php($initialMaterialsIndustryAvg = old('inputs.materials.industry_avg_ratio', data_get($budget->inputs, 'materials.industry_avg_ratio', 22.3)))
@php($initialOverheadExpensesRows = old('inputs.overhead.expenses.rows', data_get($budget->inputs, 'overhead.expenses.rows', [])))
@php($initialOverheadWagesRows = old('inputs.overhead.wages.rows', data_get($budget->inputs, 'overhead.wages.rows', [])))
@php($initialOverheadEquipmentRows = old('inputs.overhead.equipment.rows', data_get($budget->inputs, 'overhead.equipment.rows', [])))
@php($initialOverheadEquipmentGeneral = old('inputs.overhead.equipment.general', data_get($budget->inputs, 'overhead.equipment.general', ['fuel'=>0,'repairs'=>0,'insurance_misc'=>0])))
@php($initialOverheadEquipmentRentals = old('inputs.overhead.equipment.rentals', data_get($budget->inputs, 'overhead.equipment.rentals', 0)))
@php($initialOverheadIndustryAvg = old('inputs.overhead.industry_avg_ratio', data_get($budget->inputs, 'overhead.industry_avg_ratio', 24.8)))
@php($initialOverheadLaborBurden = old('inputs.overhead.labor_burden_pct', data_get($budget->inputs, 'overhead.labor_burden_pct', 0)))
@php($initialSubcontractingRows = old('inputs.subcontracting.rows', data_get($budget->inputs, 'subcontracting.rows', [])))
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
        <input type="hidden" name="section" :value="section">
        @if ($budget->exists)
            @method('PUT')
        @endif
        <input type="hidden" name="section" :value="section">
        <div class="flex">
            <!-- Left Nav -->
            <aside class="w-56 md:w-64 border-r bg-gray-50">
                <nav class="p-2">
                    @foreach (['Budget Info','Sales Budget','Field Labor','Equipment','Materials','Subcontracting','Overhead','Profit/Loss','OH Recovery','Analysis'] as $s)
                        <button type="button"
                                @click="section='{{ $s }}'"
                                :class="{'bg-white text-brand-700 border-brand-300': section==='{{ $s }}'}"
                                class="w-full px-3 py-2 text-sm rounded border hover:bg-white mb-1 flex items-center justify-between">
                            @if ($s === 'Sales Budget')
                                <span>{{ $s }}</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-brand-100 text-brand-800" x-text="formatMoney(forecastTotal())"></span>
                            @elseif ($s === 'Field Labor')
                                <span>{{ $s }}</span>
                                <span class="inline-flex items-center gap-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-brand-100 text-brand-800" x-text="formatMoney(fieldPayroll())"></span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" :class="laborPillClass()" x-text="laborRatio().toFixed(1) + '%'" title="Field Labor Ratio"></span>
                                </span>
                            @elseif ($s === 'Equipment')
                                <span>{{ $s }}</span>
                                <span class="inline-flex items-center gap-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-brand-100 text-brand-800" x-text="formatMoney(equipmentExpensesTotal())"></span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" :class="equipmentPillClass()" x-text="equipmentRatio().toFixed(1) + '%'" title="Equipment Ratio"></span>
                                </span>
                            @elseif ($s === 'Materials')
                                <span>{{ $s }}</span>
                                <span class="inline-flex items-center gap-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-brand-100 text-brand-800" x-text="formatMoney(materialsCurrentTotal())"></span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" :class="materialsPillClass()" x-text="materialsRatio().toFixed(1) + '%' "></span>
                                </span>
                            @elseif ($s === 'Subcontracting')
                                <span>{{ $s }}</span>
                                <span class="inline-flex items-center gap-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-brand-100 text-brand-800" x-text="formatMoney(subcCurrentTotal())"></span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800" x-text="subcRatio().toFixed(1) + '%'"></span>
                                </span>
                            @elseif ($s === 'Overhead')
                                <span>{{ $s }}</span>
                                <span class="inline-flex items-center gap-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-brand-100 text-brand-800" x-text="formatMoney(overheadCurrentTotal())"></span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" :class="overheadPillClass()" x-text="overheadRatio().toFixed(1) + '%'"></span>
                                </span>
                            @else
                                <span>{{ $s }}</span>
                            @endif
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
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-brand-100 text-brand-800" x-text="formatMoney(forecastTotal())"></span>
                    </h2>
                    <div class="rounded border p-4">
                        <!-- Graphics Row -->
                        <div class="grid md:grid-cols-3 gap-4 mb-4">
                            <!-- Pie: Divisional Sales -->
                            <div class="rounded border p-3 relative">
                                <div class="text-xs uppercase tracking-wide text-gray-500 mb-2">Divisional Sales</div>
                                <div class="absolute top-2 right-2 text-gray-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10a8.1 8.1 0 0 1-.9 3.8L12 12V3a9 9 0 1 1 9 9z"/></svg></div>
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
                            <div class="rounded border p-3 relative">
                                <div class="text-xs uppercase tracking-wide text-gray-500 mb-2">Prev vs Forecast</div>
                                <div class="absolute top-2 right-2 text-gray-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 13h3v5H7zM12 9h3v9h-3zM17 5h3v13h-3z"/></svg></div>
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
                            <div class="rounded border p-3 relative">
                                <div class="text-xs uppercase tracking-wide text-gray-500 mb-2">Change over Previous</div>
                                <div class="absolute top-2 right-2 text-gray-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 17l4-4 3 3 5-5"/></svg></div>
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
                    <h2 class="text-lg font-semibold mb-3 flex items-center gap-2">Field Labor
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-brand-100 text-brand-800" x-text="formatMoney(fieldPayroll())"></span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" :class="laborPillClass()" x-text="laborRatio().toFixed(1) + '%'" title="Field Labor Ratio"></span>
                    </h2>
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
                                                <option :value="opt" x-text="opt.toFixed(2) + 'x'" :selected="Number(opt) === Number(otMultiplier)"></option>
                                            </template>
                                        </select>
                                        <input type="hidden" name="section" :value="section">
                                    </div>
                                </div>
                            </div>
                            <!-- Field Labor Summary -->
                            <div class="rounded border p-3 relative">
                                <div class="text-xs uppercase tracking-wide text-gray-500 mb-2">Field Labor Summary</div>
                                <div class="absolute top-2 right-2 text-gray-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h18v4H3z"/><path d="M8 7v13"/><path d="M16 7v13"/></svg></div>
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
                            <div class="rounded border p-3 relative">
                                <div class="text-xs uppercase tracking-wide text-gray-500 mb-2">Field Labor Ratio</div>
                                <div class="absolute top-2 right-2 text-gray-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19h16"/><path d="M8 15h8"/><path d="M10 11h4"/><path d="M12 7h0"/></svg></div>
                                <div class="space-y-2">
                                    <div class="flex items-start justify-between gap-3 mb-2">
                                        <div class="flex-1">
                                            <div class="text-xs uppercase text-gray-500">Your Ratio</div>
                                                                                        <div class="text-3xl font-bold"><span class="px-2 py-0.5 rounded-full" :class="laborPillClass()" x-text="laborRatio().toFixed(1) + '%'"></span></div>
                                        </div>
                                        <div class="flex-1 text-right">
                                            <div class="text-xs uppercase text-gray-500">Industry Avg</div>
                                            <div class="text-3xl font-bold"><span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-800" x-text="(industryAvgRatio||0).toFixed(1) + '%'"></span></div>
                                        </div>
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
                    <h2 class="text-lg font-semibold mb-3 flex items-center gap-2">Equipment
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-brand-100 text-brand-800" x-text="formatMoney(equipmentExpensesTotal())"></span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" :class="equipmentPillClass()" x-text="equipmentRatio().toFixed(1) + '%'" title="Equipment Ratio"></span>
                    </h2>
                    <div class="rounded border p-4">
                        <!-- Graphics Row -->
                        <div class="grid md:grid-cols-3 gap-4 mb-4">
                            <!-- General Expenses -->
                            <div class="rounded border p-3 relative">
                                <div class="text-xs uppercase tracking-wide text-gray-500 mb-2">General Expenses</div>
                                <div class="absolute top-2 right-2 text-gray-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2C8 6 6 9 6 12a6 6 0 0 0 12 0c0-3-2-6-6-10z"/></svg></div>
                                <div class="space-y-1.5">
                                    <div class="flex items-center justify-between py-1.5 border-t border-gray-300 first:border-t-0">
                                        <label class="text-sm font-medium text-gray-800 pr-3">Forecast Fuel</label>
                                        <input type="number" step="0.01" min="0" class="form-input w-24 md:w-28 text-sm text-right" x-model.number="equipmentGeneral.fuel" name="inputs[equipment][general][fuel]" placeholder="0.00">
                                    </div>
                                    <div class="flex items-center justify-between py-1.5 border-t border-gray-300 first:border-t-0">
                                        <label class="text-sm font-medium text-gray-800 pr-3">Forecast Repairs</label>
                                        <input type="number" step="0.01" min="0" class="form-input w-24 md:w-28 text-sm text-right" x-model.number="equipmentGeneral.repairs" name="inputs[equipment][general][repairs]" placeholder="0.00">
                                    </div>
                                    <div class="flex items-center justify-between py-1.5 border-t border-gray-300 first:border-t-0">
                                        <label class="text-sm font-medium text-gray-800 pr-3">Insurance + Misc</label>
                                        <input type="number" step="0.01" min="0" class="form-input w-24 md:w-28 text-sm text-right" x-model.number="equipmentGeneral.insurance_misc" name="inputs[equipment][general][insurance_misc]" placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                            <!-- Equip Summary -->
                            <div class="rounded border p-3 relative">
                                <div class="text-xs uppercase tracking-wide text-gray-500 mb-2">Equip Summary</div>
                                <div class="absolute top-2 right-2 text-gray-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M7 7h10M7 11h4M13 11h4M7 15h4M13 15h4"/></svg></div>
                                <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-sm">
                                    <div class="text-gray-600">Equipment Expenses (list total)</div>
                                    <div class="text-right font-semibold" x-text="formatMoney(equipmentDisplayedListTotal())"></div>
                                    <div class="text-gray-600">Other (General Expenses total)</div>
                                    <div class="text-right font-semibold" x-text="formatMoney(generalExpensesTotal())"></div>
                                    <div class="text-gray-600">Total Equip Expenses</div>
                                    <div class="text-right font-semibold" x-text="formatMoney(equipmentExpensesTotal())"></div>
                                    <div class="text-gray-600">Plus Equip Rentals</div>
                                    <div>
                                        <input type="number" step="0.01" min="0" class="form-input w-full text-right" x-model.number="equipmentRentals" name="inputs[equipment][rentals]" placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                            <!-- Equipment Ratio -->
                            <div class="rounded border p-3 relative">
                                <div class="text-xs uppercase tracking-wide text-gray-500 mb-2">Equipment Ratio</div>
                                <div class="absolute top-2 right-2 text-gray-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg></div>
                                <div class="space-y-2">
                                    <div class="flex items-start justify-between gap-3 mb-2">
                                        <div class="flex-1">
                                            <div class="text-xs uppercase text-gray-500">Your Ratio</div>
                                                                                        <div class="text-3xl font-bold"><span class="px-2 py-0.5 rounded-full" :class="equipmentPillClass()" x-text="equipmentRatio().toFixed(1) + '%'"></span></div>
                                        </div>
                                        <div class="flex-1 text-right">
                                            <div class="text-xs uppercase text-gray-500">Industry Avg</div>
                                            <div class="text-3xl font-bold"><span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-800" x-text="(equipmentIndustryAvgRatio||0).toFixed(1) + '%'"></span></div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Industry Avg (%)</label>
                                        <input type="number" step="0.1" min="0" class="form-input w-full" x-model.number="equipmentIndustryAvgRatio" name="inputs[equipment][industry_avg_ratio]" placeholder="13.7">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="hidden md:grid grid-cols-12 gap-2 text-xs font-medium text-gray-600 border-b pb-2">
                            <div class="col-span-2">Equipment Type</div>
                            <div class="col-span-1">Qty</div>
                            <div class="col-span-2">Class</div>
                            <div class="col-span-4">Description</div>
                            <div class="col-span-2">Cost/Yr/Ea</div>
                            <div class="col-span-1 text-right">Cost/Yr/Ea</div>
                        </div>
                        <template x-for="(row, idx) in equipmentRows" :key="'eq'+idx">
                            <div class="grid grid-cols-12 gap-2 items-center py-2 border-b">
                                <div class="col-span-12 md:col-span-2">
                                    <label class="md:hidden block text-xs text-gray-500">Equipment Type</label>
                                    <input type="text" class="form-input w-full" x-model="row.type" :name="'inputs[equipment][rows]['+idx+'][type]'" placeholder="e.g., Truck">
                                </div>
                                <div class="col-span-6 md:col-span-1">
                                    <label class="md:hidden block text-xs text-gray-500">Qty</label>
                                    <input type="number" step="1" min="0" class="form-input w-full" x-model="row.qty" :name="'inputs[equipment][rows]['+idx+'][qty]'" placeholder="0">
                                </div>
                                <div class="col-span-6 md:col-span-2">
                                    <label class="md:hidden block text-xs text-gray-500">Class</label>
                                    <select class="form-select w-full" x-model="row.class" :name="'inputs[equipment][rows]['+idx+'][class]'" @change="if(row.class==='Owned' && !row.owned){row.owned={ replacement_value:'', fees:'', years:'', salvage_value:'', months_per_year:'', division_months:'', interest_rate_pct:'' }}; if(row.class==='Leased' && !row.leased){row.leased={ monthly_payment:'', payments_per_year:'', months_per_year:'', division_months:'' }}; if(row.class==='Group' && !row.group){row.group={ items: [] }}" >
                                        <option>Custom</option>
                                        <option>Owned</option>
                                        <option>Leased</option>
                                        <option>Group</option>
                                    </select>
                                    <!-- Always submit months fields so values persist regardless of panel state -->
                                    <template x-if="row.class==='Owned'">
                                        <div class="hidden">
                                            <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][owned][months_per_year]'" :value="row.owned.months_per_year">
                                            <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][owned][division_months]'" :value="row.owned.division_months">
                                        </div>
                                    </template>
                                    <template x-if="row.class==='Leased'">
                                        <div class="hidden">
                                            <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][leased][payments_per_year]'" :value="row.leased.payments_per_year">
                                            <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][leased][months_per_year]'" :value="row.leased.months_per_year">
                                            <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][leased][division_months]'" :value="row.leased.division_months">
                                        </div>
                                    </template>
                                </div>
                                <div class="col-span-12 md:col-span-4">
                                    <label class="md:hidden block text-xs text-gray-500">Description</label>
                                    <input type="text" class="form-input w-full" x-model="row.description" :name="'inputs[equipment][rows]['+idx+'][description]'" placeholder="Notes">
                                </div>
                                <div class="col-span-6 md:col-span-2">
                                    <label class="md:hidden block text-xs text-gray-500">Cost/Yr/Ea</label>
                                    <template x-if="row.class==='Owned'">
                                        <div class="relative">
                                            <input type="text" class="form-input w-full bg-green-50 pr-10" :value="(computeOwnedAnnual(row) || 0).toFixed(2)" readonly tabindex="-1" placeholder="0.00">
                                            <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][cost_per_year]'" :value="computeOwnedAnnual(row)">
                                            <!-- Ensure owned fields persist even when collapsed -->
                                            <template x-if="!row._ownedOpen">
                                                <div>
                                                    <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][owned][replacement_value]'" :value="row.owned.replacement_value">
                                                    <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][owned][fees]'" :value="row.owned.fees">
                                                    <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][owned][years]'" :value="row.owned.years">
                                                    <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][owned][salvage_value]'" :value="row.owned.salvage_value">
                                                    <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][owned][months_per_year]'" :value="row.owned.months_per_year">
                                                    <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][owned][division_months]'" :value="row.owned.division_months">
                                                    <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][owned][interest_rate_pct]'" :value="row.owned.interest_rate_pct">
                                                </div>
                                            </template>
                                            <button type="button" class="absolute inset-y-0 right-1 my-auto h-7 w-7 rounded border bg-white/80 hover:bg-white flex items-center justify-center shadow-sm"
                                                    @click.prevent="row._ownedOpen = !row._ownedOpen"
                                                    :aria-expanded="row._ownedOpen ? 'true' : 'false'"
                                                    title="Toggle calculator">
                                                <svg viewBox="0 0 24 24" class="h-4 w-4 text-gray-700" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                                                    <path d="M7 7h10M7 11h4M13 11h4M7 15h4M13 15h4"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="row.class==='Leased'">
                                        <div class="relative">
                                            <input type="text" class="form-input w-full bg-green-50 pr-10" :value="(computeLeasedAnnual(row) || 0).toFixed(2)" readonly tabindex="-1" placeholder="0.00">
                                            <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][cost_per_year]'" :value="computeLeasedAnnual(row)">
                                            <!-- Ensure leased fields persist even when collapsed -->
                                            <template x-if="!row._ownedOpen">
                                                <div>
                                                    <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][leased][monthly_payment]'" :value="row.leased.monthly_payment">
                                                    <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][leased][payments_per_year]'" :value="row.leased.payments_per_year">
                                                    <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][leased][months_per_year]'" :value="row.leased.months_per_year">
                                                    <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][leased][division_months]'" :value="row.leased.division_months">
                                                </div>
                                            </template>
                                            <button type="button" class="absolute inset-y-0 right-1 my-auto h-7 w-7 rounded border bg-white/80 hover:bg-white flex items-center justify-center shadow-sm"
                                                    @click.prevent="row._ownedOpen = !row._ownedOpen"
                                                    :aria-expanded="row._ownedOpen ? 'true' : 'false'"
                                                    title="Toggle calculator">
                                                <svg viewBox="0 0 24 24" class="h-4 w-4 text-gray-700" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                                                    <path d="M7 7h10M7 11h4M13 11h4M7 15h4M13 15h4"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="row.class==='Group'">
                                        <div class="relative">
                                            <input type="text" class="form-input w-full bg-green-50 pr-10" :value="(computeGroupAnnual(row) || 0).toFixed(2)" readonly tabindex="-1" placeholder="0.00">
                                            <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][cost_per_year]'" :value="computeGroupAnnual(row)">
                                            <!-- Ensure group items persist even when collapsed -->
                                            <template x-if="!row._ownedOpen">
                                                <div>
                                                    <template x-for="(gi, gidx) in (row.group?.items || [])" :key="'g'+gidx">
                                                        <div>
                                                            <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][group][items]['+gidx+'][name]'" :value="row.group.items[gidx].name">
                                                            <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][group][items]['+gidx+'][qty]'" :value="row.group.items[gidx].qty">
                                                            <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][group][items]['+gidx+'][purchase_price]'" :value="row.group.items[gidx].purchase_price">
                                                            <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][group][items]['+gidx+'][resale_value]'" :value="row.group.items[gidx].resale_value">
                                                            <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][group][items]['+gidx+'][years]'" :value="row.group.items[gidx].years">
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                            <button type="button" class="absolute inset-y-0 right-1 my-auto h-7 w-7 rounded border bg-white/80 hover:bg-white flex items-center justify-center shadow-sm"
                                                    @click.prevent="row._ownedOpen = !row._ownedOpen"
                                                    :aria-expanded="row._ownedOpen ? 'true' : 'false'"
                                                    title="Toggle calculator">
                                                <svg viewBox="0 0 24 24" class="h-4 w-4 text-gray-700" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                                                    <path d="M7 7h10M7 11h4M13 11h4M7 15h4M13 15h4"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="row.class!=='Owned' && row.class!=='Leased' && row.class!=='Group'">
                                        <input type="number" step="0.01" min="0" class="form-input w-full" x-model="row.cost_per_year" :name="'inputs[equipment][rows]['+idx+'][cost_per_year]'" placeholder="0.00">
                                    </template>
                                </div>
                                <!-- Owned details panel (full width under Cost/Yr/Ea) -->
                                <div class="col-span-12" x-show="row.class==='Owned' && row._ownedOpen">
                                    <div class="mt-2 bg-green-50 border border-green-200 rounded p-3 space-y-3">
                                        <div class="text-sm uppercase tracking-wide text-green-700 pb-2 mb-2 border-b-2 border-green-700 border-double">Owned – Cost/Year/Ea Breakdown</div>
                                        <div class="space-y-1.5">
                                            <div class="flex items-center justify-between py-1.5">
                                                <label class="text-sm font-medium text-gray-800 pr-3">Replacement value</label>
                                                <input type="number" step="0.01" min="0" class="form-input w-28 md:w-36 text-sm" x-model="row.owned.replacement_value" :name="'inputs[equipment][rows]['+idx+'][owned][replacement_value]'">
                                            </div>
                                            <div class="flex items-center justify-between py-1.5">
                                                <label class="text-sm font-medium text-gray-800 pr-3">Additional fees/taxes/admin</label>
                                                <input type="number" step="0.01" min="0" class="form-input w-28 md:w-36 text-sm" x-model="row.owned.fees" :name="'inputs[equipment][rows]['+idx+'][owned][fees]'">
                                            </div>
                                            <div class="flex items-center justify-between py-1.5">
                                                <label class="text-sm font-medium text-gray-800 pr-3">Useful life (years)</label>
                                                <input type="number" step="0.1" min="0.1" class="form-input w-28 md:w-36 text-sm" x-model="row.owned.years" :name="'inputs[equipment][rows]['+idx+'][owned][years]'">
                                            </div>
                                            <div class="flex items-center justify-between py-1.5">
                                                <label class="text-sm font-medium text-gray-800 pr-3">End-of-life value</label>
                                                <input type="number" step="0.01" min="0" class="form-input w-28 md:w-36 text-sm" x-model="row.owned.salvage_value" :name="'inputs[equipment][rows]['+idx+'][owned][salvage_value]'">
                                            </div>
                                            <div class="flex items-center justify-between py-1.5">
                                                <label class="text-sm font-medium text-gray-800 pr-3">Months used per year (1–12)</label>
                                                <select class="form-select w-28 md:w-36 text-sm" x-model="row.owned.months_per_year" x-init="$el.value = (row.owned.months_per_year || '')" :name="'inputs[equipment][rows]['+idx+'][owned][months_per_year]'">
                                                    <option value="" disabled x-bind:selected="!row.owned.months_per_year">Select…</option>
                                                    <template x-for="m in 12" :key="m">
                                                        <option :value="String(m)" :selected="String(row.owned.months_per_year) === String(m)" x-text="m"></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <div class="flex items-center justify-between py-1.5">
                                                <label class="text-sm font-medium text-gray-800 pr-3">Division months (1–12)</label>
                                                <select class="form-select w-28 md:w-36 text-sm" x-model="row.owned.division_months" x-init="$el.value = (row.owned.division_months || '')" :name="'inputs[equipment][rows]['+idx+'][owned][division_months]'">
                                                    <option value="" disabled x-bind:selected="!row.owned.division_months">Select…</option>
                
                                                    <template x-for="m in 12" :key="'d'+m">
                                                        <option :value="String(m)" :selected="String(row.owned.division_months) === String(m)" x-text="m"></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <div class="flex items-center justify-between py-1.5 mt-2 pt-2 border-t-2 border-green-700 border-double">
                                                <label class="text-sm font-medium text-gray-800 pr-3">Inflation/Interest rate (%)</label>
                                                <input type="number" step="0.01" min="0" max="100" class="form-input w-28 md:w-36 text-sm" x-model="row.owned.interest_rate_pct" :name="'inputs[equipment][rows]['+idx+'][owned][interest_rate_pct]'">
                                            </div>
                                        </div>
                                        <div class="grid md:grid-cols-5 gap-3 text-sm mt-2">
                                            <div>
                                                <div class="text-gray-600">Annual cost/equipment</div>
                                                <div class="text-base font-semibold" x-text="formatMoney(computeOwnedAnnual(row))"></div>
                                            </div>
                                            <div>
                                                <div class="text-gray-600">Monthly (calendar)</div>
                                                <div class="text-base font-semibold" x-text="formatMoney(computeOwnedMonthlyCalendar(row))"></div>
                                            </div>
                                            <div>
                                                <div class="text-gray-600">Monthly (active)</div>
                                                <div class="text-base font-semibold" x-text="formatMoney(computeOwnedMonthlyActive(row))"></div>
                                            </div>
                                            <div>
                                                <div class="text-gray-600">Division Annual</div>
                                                <div class="text-base font-semibold" x-text="formatMoney(computeDivisionAnnual(row))"></div>
                                            </div>
                                            <div>
                                                <div class="text-gray-600">Division Monthly (active)</div>
                                                <div class="text-base font-semibold" x-text="formatMoney(computeDivisionMonthlyActive(row))"></div>
                                            </div>
                                        </div>
                                        <div class="mt-2 pt-2 border-t flex items-center justify-between" x-show="computeOwnedInterestLifeCompounded(row) > 0">
                                            <div class="text-sm text-gray-700">Interest/Inflation value over the life of the equipment:</div>
                                            <div class="text-sm font-semibold" x-text="formatMoney(computeOwnedInterestLifeCompounded(row))"></div>
                                        </div>
                                        <div class="text-xs text-gray-600">Annual cost = (replacement value + fees + interest over life - end-of-life value) / useful life.</div>
                                    </div>
                                </div>
                                <!-- Group details panel (full width under Cost/Yr/Ea) -->
                                <div class="col-span-12" x-show="row.class==='Group' && row._ownedOpen">
                                    <div class="mt-2 bg-green-50 border border-green-200 rounded p-3 space-y-3">
                                        <div class="flex items-center justify-between">
                                            <div class="text-sm uppercase tracking-wide text-green-700 pb-2 mb-2 border-b-2 border-green-700 border-double">Group – Cost/Year (Total) Breakdown</div>
                                            <div class="space-x-2">
                                                <x-brand-button type="button" size="sm" @click="addGroupItem(row)">Add</x-brand-button>
                                                <x-secondary-button type="button" size="sm" @click="row._ownedOpen=false">Cancel</x-secondary-button>
                                            </div>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="hidden md:grid grid-cols-12 gap-2 text-xs font-medium text-gray-600 border-b pb-2">
                                                <div class="col-span-3">Name</div>
                                                <div class="col-span-1">Qty</div>
                                                <div class="col-span-2">Purch. Price</div>
                                                <div class="col-span-2">Resale Value</div>
                                                <div class="col-span-2">Yrs/Life</div>
                                                <div class="col-span-2 text-right">Cost/Yr (Ea)</div>
                                            </div>
                                            <template x-for="(it, gi) in (row.group?.items || [])" :key="'gi'+gi">
                                                <div class="grid grid-cols-12 gap-2 items-center py-2 border-b">
                                                    <div class="col-span-12 md:col-span-3">
                                                        <label class="md:hidden block text-xs text-gray-500">Name</label>
                                                        <input type="text" class="form-input w-full" x-model="it.name" :name="'inputs[equipment][rows]['+idx+'][group][items]['+gi+'][name]'" placeholder="Item name">
                                                    </div>
                                                    <div class="col-span-6 md:col-span-1">
                                                        <label class="md:hidden block text-xs text-gray-500">Qty</label>
                                                        <input type="number" step="1" min="0" class="form-input w-full" x-model="it.qty" :name="'inputs[equipment][rows]['+idx+'][group][items]['+gi+'][qty]'" placeholder="0">
                                                    </div>
                                                    <div class="col-span-6 md:col-span-2">
                                                        <label class="md:hidden block text-xs text-gray-500">Purch. Price</label>
                                                        <input type="number" step="0.01" min="0" class="form-input w-full" x-model="it.purchase_price" :name="'inputs[equipment][rows]['+idx+'][group][items]['+gi+'][purchase_price]'" placeholder="0.00">
                                                    </div>
                                                    <div class="col-span-6 md:col-span-2">
                                                        <label class="md:hidden block text-xs text-gray-500">Resale Value</label>
                                                        <input type="number" step="0.01" min="0" class="form-input w-full" x-model="it.resale_value" :name="'inputs[equipment][rows]['+idx+'][group][items]['+gi+'][resale_value]'" placeholder="0.00">
                                                    </div>
                                                    <div class="col-span-6 md:col-span-2">
                                                        <label class="md:hidden block text-xs text-gray-500">Yrs/Life</label>
                                                        <input type="number" step="0.1" min="0.1" class="form-input w-full" x-model="it.years" :name="'inputs[equipment][rows]['+idx+'][group][items]['+gi+'][years]'" placeholder="0.0">
                                                    </div>
                                                    <div class="col-span-6 md:col-span-2 text-right font-semibold">
                                                        <span x-text="formatMoney(computeGroupItemAnnual(it))"></span>
                                                    </div>
                                                    <div class="col-span-12 md:col-span-12 md:text-right">
                                                        <x-danger-button size="sm" type="button" @click="removeGroupItem(row, gi)">Delete</x-danger-button>
                                                    </div>
                                                </div>
                                            </template>
                                            <div class="grid grid-cols-12 gap-2 items-center pt-2">
                                                <div class="col-span-8 text-right font-semibold">Annual ROI (Total)</div>
                                                <div class="col-span-4 text-right font-bold" x-text="formatMoney(computeGroupAnnual(row))"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Leased details panel (full width under Cost/Yr/Ea) -->
                                <div class="col-span-12" x-show="row.class==='Leased' && row._ownedOpen">
                                    <div class="mt-2 bg-green-50 border border-green-200 rounded p-3 space-y-3">
                                        <div class="text-sm uppercase tracking-wide text-green-700 pb-2 mb-2 border-b-2 border-green-700 border-double">Leased – Cost/Year/Ea Breakdown</div>
                                        <div class="space-y-1.5">
                                            <div class="flex items-center justify-between py-1.5">
                                                <label class="text-sm font-medium text-gray-800 pr-3">Enter the monthly payment, including tax</label>
                                                <input type="number" step="0.01" min="0" class="form-input w-28 md:w-36 text-sm" x-model="row.leased.monthly_payment" :name="'inputs[equipment][rows]['+idx+'][leased][monthly_payment]'">
                                            </div>
                                            <div class="flex items-center justify-between py-1.5">
                                                <label class="text-sm font-medium text-gray-800 pr-3">How many payments do you make per year</label>
                                                <select class="form-select w-28 md:w-36 text-sm" x-model="row.leased.payments_per_year" x-init="$el.value = (row.leased.payments_per_year || '')" :name="'inputs[equipment][rows]['+idx+'][leased][payments_per_year]'">
                                                    <option value="" disabled x-bind:selected="!row.leased.payments_per_year">Select…</option>
                                                    <template x-for="m in 12" :key="'lp'+m">
                                                        <option :value="String(m)" :selected="String(row.leased.payments_per_year) === String(m)" x-text="m"></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <div class="flex items-center justify-between py-1.5">
                                                <label class="text-sm font-medium text-gray-800 pr-3">Enter the number of months per year you use it</label>
                                                <select class="form-select w-28 md:w-36 text-sm" x-model="row.leased.months_per_year" x-init="$el.value = (row.leased.months_per_year || '')" :name="'inputs[equipment][rows]['+idx+'][leased][months_per_year]'">
                                                    <option value="" disabled x-bind:selected="!row.leased.months_per_year">Select…</option>
                                                    <template x-for="m in 12" :key="'lm'+m">
                                                        <option :value="String(m)" :selected="String(row.leased.months_per_year) === String(m)" x-text="m"></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <div class="flex items-center justify-between py-1.5">
                                                <label class="text-sm font-medium text-gray-800 pr-3">If this is a divisional budget, months this works in this division</label>
                                                <select class="form-select w-28 md:w-36 text-sm" x-model="row.leased.division_months" x-init="$el.value = (row.leased.division_months || '')" :name="'inputs[equipment][rows]['+idx+'][leased][division_months]'">
                                                    <option value="" disabled x-bind:selected="!row.leased.division_months">Select…</option>
                                                    <template x-for="m in 12" :key="'ld'+m">
                                                        <option :value="String(m)" :selected="String(row.leased.division_months) === String(m)" x-text="m"></option>
                                                    </template>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="grid md:grid-cols-5 gap-3 text-sm mt-2">
                                            <div>
                                                <div class="text-gray-600">Annual ROI</div>
                                                <div class="text-base font-semibold" x-text="formatMoney(computeLeasedAnnual(row))"></div>
                                            </div>
                                            <div>
                                                <div class="text-gray-600">Monthly ROI</div>
                                                <div class="text-base font-semibold" x-text="formatMoney(computeLeasedMonthlyCalendar(row))"></div>
                                            </div>
                                            <div>
                                                <div class="text-gray-600">Monthly (active)</div>
                                                <div class="text-base font-semibold" x-text="formatMoney(computeLeasedMonthlyActive(row))"></div>
                                            </div>
                                            <div>
                                                <div class="text-gray-600">Division Annual</div>
                                                <div class="text-base font-semibold" x-text="formatMoney(computeLeasedDivisionAnnual(row))"></div>
                                            </div>
                                            <div>
                                                <div class="text-gray-600">Division Monthly (active)</div>
                                                <div class="text-base font-semibold" x-text="formatMoney(computeLeasedDivisionMonthlyActive(row))"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-span-4 md:col-span-1 text-right font-semibold relative">
                                    <span class="inline-block mr-2" x-text="formatMoney(perUnitCost(row))"></span>
                                    <div class="inline-block relative" @keydown.escape.stop="row._menuOpen=false">
                                        <button type="button" class="h-6 w-6 inline-flex items-center justify-center rounded border bg-white hover:bg-gray-50 text-gray-700"
                                                @click.stop="row._menuOpen = !row._menuOpen"
                                                :aria-expanded="row._menuOpen ? 'true' : 'false'"
                                                title="Row actions">
                                            <svg viewBox="0 0 20 20" class="h-4 w-4" fill="currentColor"><path d="M10 3a2 2 0 110 4 2 2 0 010-4zm0 5a2 2 0 110 4 2 2 0 010-4zm0 5a2 2 0 110 4 2 2 0 010-4z"/></svg>
                                        </button>
                                        <div class="absolute right-0 mt-1 w-44 bg-white border rounded shadow z-10" x-show="row._menuOpen" x-cloak @click.outside="row._menuOpen=false">
                                            <button type="button" class="block w-full text-left px-3 py-1.5 text-sm hover:bg-gray-50"
                                                    @click="moveEquipmentToOverhead(idx); row._menuOpen=false">Move to Overhead</button>
                                            <button type="button" class="block w-full text-left px-3 py-1.5 text-sm text-red-600 hover:bg-red-50"
                                                    @click="removeEquipmentRow(idx); row._menuOpen=false">Delete</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <div class="pt-3 flex items-center justify-between">
                            <x-brand-button type="button" size="sm" variant="ghost" @click="addEquipmentRow()">+ New</x-brand-button>
                            <div class="text-sm text-gray-700" x-show="equipmentTotal() > 0"><span class="font-semibold">Total Equipment:</span> <span x-text="formatMoney(equipmentTotal())"></span></div>
                        </div>
                    </div>
                </section>

                <!-- MATERIALS -->
                <section x-show="section==='Materials'" x-cloak>
                    <h2 class="text-lg font-semibold mb-3 flex items-center gap-2">Materials
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-brand-100 text-brand-800" x-text="formatMoney(materialsCurrentTotal())"></span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" :class="materialsPillClass()" x-text="materialsRatio().toFixed(1) + '%'"></span>
                    </h2>
                    <div class="rounded border p-4">
                        <!-- Graphics Row -->
                        <div class="grid md:grid-cols-3 gap-4 mb-4">
                            <!-- Key Factors -->
                            <div class="rounded border p-3 relative">
                                <div class="text-xs uppercase tracking-wide text-gray-500 mb-2">Key Factors</div>
                                <div class="absolute top-2 right-2 text-gray-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h18v4H3z"/><path d="M8 7v13"/><path d="M16 7v13"/></svg></div>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Materials Tax (%)</label>
                                        <input type="number" step="0.1" min="0" class="form-input w-full" x-model.number="materialsTaxPct" name="inputs[materials][tax_pct]" placeholder="0.0">
                                        <p class="text-xs text-gray-500 mt-1">Applied to material expenses totals.</p>
                                    </div>
                                </div>
                            </div>
                            <!-- Material Summary -->
                            <div class="rounded border p-3 relative">
                                <div class="text-xs uppercase tracking-wide text-gray-500 mb-2">Material Summary</div>
                                <div class="absolute top-2 right-2 text-gray-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M7 7h10M7 11h10M7 15h10"/></svg></div>
                                <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-sm">
                                    <div class="text-gray-600">Previous Total</div>
                                    <div class="text-right font-semibold" x-text="formatMoney(materialsPrevTotal())"></div>
                                    <div class="text-gray-600">Previous Ratio</div>
                                    <div class="text-right font-semibold">
                                        <span class="px-2 py-0.5 rounded-full" :class="materialsPillClassFor(materialsPrevRatio())" x-text="materialsPrevRatio().toFixed(1) + '%'"></span>
                                    </div>
                                    <div class="text-gray-600">Current Total</div>
                                    <div class="text-right font-semibold" x-text="formatMoney(materialsCurrentTotal())"></div>
                                    <div class="text-gray-600">Current Ratio</div>
                                    <div class="text-right font-semibold">
                                        <span class="px-2 py-0.5 rounded-full" :class="materialsPillClass()" x-text="materialsRatio().toFixed(1) + '%'"></span>
                                    </div>
                                </div>
                            </div>
                            <!-- Material Ratio -->
                            <div class="rounded border p-3 relative">
                                <div class="text-xs uppercase tracking-wide text-gray-500 mb-2">Material Ratio</div>
                                <div class="absolute top-2 right-2 text-gray-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg></div>
                                <div class="space-y-2">
                                    <div class="flex items-start justify-between gap-3 mb-2">
                                        <div class="flex-1">
                                            <div class="text-xs uppercase text-gray-500">Your Ratio</div>
                                            <div class="text-3xl font-bold"><span class="px-2 py-0.5 rounded-full" :class="materialsPillClass()" x-text="materialsRatio().toFixed(1) + '%'"></span></div>
                                        </div>
                                        <div class="flex-1 text-right">
                                            <div class="text-xs uppercase text-gray-500">Industry Avg</div>
                                            <div class="text-3xl font-bold"><span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-800" x-text="(materialsIndustryAvgRatio||0).toFixed(1) + '%'"></span></div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Industry Avg (%)</label>
                                        <input type="number" step="0.1" min="0" class="form-input w-full" x-model.number="materialsIndustryAvgRatio" name="inputs[materials][industry_avg_ratio]" placeholder="22.3">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Header Row -->
                        <div class="hidden md:grid grid-cols-12 gap-3 text-xs font-medium text-gray-600 border-b pb-2">
                            <div class="col-span-2">Acct. ID</div>
                            <div class="col-span-3">Material Expense</div>
                            <div class="col-span-2">Previous $</div>
                            <div class="col-span-2">Current $</div>
                            <div class="col-span-2">Comments</div>
                            <div class="col-span-1 text-right">Actions</div>
                        </div>
                        <template x-for="(row, idx) in materialsRows" :key="'m'+idx">
                            <div class="grid grid-cols-12 gap-3 items-center py-2 border-b">
                                <div class="col-span-12 md:col-span-2">
                                    <label class="md:hidden block text-xs text-gray-500">Acct. ID</label>
                                    <input type="text" class="form-input w-full" x-model="row.account_id" :name="'inputs[materials][rows]['+idx+'][account_id]'" placeholder="e.g., 5001">
                                </div>
                                <div class="col-span-12 md:col-span-3">
                                    <label class="md:hidden block text-xs text-gray-500">Material Expense</label>
                                    <input type="text" class="form-input w-full" x-model="row.expense" :name="'inputs[materials][rows]['+idx+'][expense]'" placeholder="e.g., Mulch">
                                </div>
                                <div class="col-span-6 md:col-span-2">
                                    <label class="md:hidden block text-xs text-gray-500">Previous $</label>
                                    <input type="number" step="0.01" min="0" inputmode="decimal" class="form-input w-full" x-model="row.previous" :name="'inputs[materials][rows]['+idx+'][previous]'" placeholder="0.00">
                                </div>
                                <div class="col-span-6 md:col-span-2">
                                    <label class="md:hidden block text-xs text-gray-500">Current $</label>
                                    <input type="number" step="0.01" min="0" inputmode="decimal" class="form-input w-full" x-model="row.current" :name="'inputs[materials][rows]['+idx+'][current]'" placeholder="0.00">
                                </div>
                                <div class="col-span-6 md:col-span-2">
                                    <label class="md:hidden block text-xs text-gray-500">Comments</label>
                                    <input type="text" class="form-input w-full" x-model="row.comments" :name="'inputs[materials][rows]['+idx+'][comments]'" placeholder="Notes">
                                </div>
                                <div class="col-span-12 md:col-span-1 flex md:justify-end">
                                    <x-danger-button size="sm" type="button" @click="removeMaterialsRow(idx)">Delete</x-danger-button>
                                </div>
                            </div>
                        </template>
                        <div class="pt-3">
                            <x-brand-button type="button" size="sm" variant="ghost" @click="addMaterialsRow()">+ New</x-brand-button>
                        </div>
                    </div>
                </section>

                <!-- SUBCONTRACTING -->
                <section x-show="section==='Subcontracting'" x-cloak>
                    <h2 class="text-lg font-semibold mb-3 flex items-center gap-2">Subcontracting
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-brand-100 text-brand-800" x-text="formatMoney(subcCurrentTotal())"></span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800" x-text="subcRatio().toFixed(1) + '%'"></span>
                    </h2>
                    <div class="rounded border p-4">
                        <!-- Graphics Row -->
                        <div class="grid md:grid-cols-3 gap-4 mb-4">
                            <!-- Subcontracting Summary -->
                            <div class="rounded border p-3 relative md:col-span-2">
                                <div class="text-xs uppercase tracking-wide text-gray-500 mb-2">Subcontracting Summary</div>
                                <div class="absolute top-2 right-2 text-gray-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M7 7h10M7 11h10M7 15h10"/></svg></div>
                                <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-sm">
                                    <div class="text-gray-600">Previous Total</div>
                                    <div class="text-right font-semibold" x-text="formatMoney(subcPrevTotal())"></div>
                                    <div class="text-gray-600">Previous Ratio</div>
                                    <div class="text-right font-semibold"><span class="px-2 py-0.5 rounded-full bg-green-100 text-green-800" x-text="subcPrevRatio().toFixed(1) + '%'"></span></div>
                                    <div class="text-gray-600">Current Total</div>
                                    <div class="text-right font-semibold" x-text="formatMoney(subcCurrentTotal())"></div>
                                    <div class="text-gray-600">Current Ratio</div>
                                    <div class="text-right font-semibold"><span class="px-2 py-0.5 rounded-full bg-green-100 text-green-800" x-text="subcRatio().toFixed(1) + '%'"></span></div>
                                </div>
                            </div>
                            <!-- Subcontracting Ratio -->
                            <div class="rounded border p-3 relative">
                                <div class="text-xs uppercase tracking-wide text-gray-500 mb-2">Subcontracting Ratio</div>
                                <div class="absolute top-2 right-2 text-gray-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg></div>
                                <div class="space-y-2">
                                    <div class="flex items-start justify-between gap-3 mb-2">
                                        <div class="flex-1">
                                            <div class="text-xs uppercase text-gray-500">Your Ratio</div>
                                            <div class="text-3xl font-bold"><span class="px-2 py-0.5 rounded-full bg-green-100 text-green-800" x-text="subcRatio().toFixed(1) + '%'"></span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Header Row -->
                        <div class="hidden md:grid grid-cols-12 gap-3 text-xs font-medium text-gray-600 border-b pb-2">
                            <div class="col-span-2">Acct. ID</div>
                            <div class="col-span-3">Subcontracting Expense</div>
                            <div class="col-span-2">Previous $</div>
                            <div class="col-span-2">Current $</div>
                            <div class="col-span-2">Comments</div>
                            <div class="col-span-1 text-right">Actions</div>
                        </div>
                        <template x-for="(row, idx) in subcontractingRows" :key="'sc'+idx">
                            <div class="grid grid-cols-12 gap-3 items-center py-2 border-b">
                                <div class="col-span-12 md:col-span-2">
                                    <label class="md:hidden block text-xs text-gray-500">Acct. ID</label>
                                    <input type="text" class="form-input w-full" x-model="row.account_id" :name="'inputs[subcontracting][rows]['+idx+'][account_id]'" placeholder="e.g., 6001">
                                </div>
                                <div class="col-span-12 md:col-span-3">
                                    <label class="md:hidden block text-xs text-gray-500">Subcontracting Expense</label>
                                    <input type="text" class="form-input w-full" x-model="row.expense" :name="'inputs[subcontracting][rows]['+idx+'][expense]'" placeholder="e.g., Tree work">
                                </div>
                                <div class="col-span-6 md:col-span-2">
                                    <label class="md:hidden block text-xs text-gray-500">Previous $</label>
                                    <input type="number" step="0.01" min="0" inputmode="decimal" class="form-input w-full" x-model="row.previous" :name="'inputs[subcontracting][rows]['+idx+'][previous]'" placeholder="0.00">
                                </div>
                                <div class="col-span-6 md:col-span-2">
                                    <label class="md:hidden block text-xs text-gray-500">Current $</label>
                                    <input type="number" step="0.01" min="0" inputmode="decimal" class="form-input w-full" x-model="row.current" :name="'inputs[subcontracting][rows]['+idx+'][current]'" placeholder="0.00">
                                </div>
                                <div class="col-span-6 md:col-span-2">
                                    <label class="md:hidden block text-xs text-gray-500">Comments</label>
                                    <input type="text" class="form-input w-full" x-model="row.comments" :name="'inputs[subcontracting][rows]['+idx+'][comments]'" placeholder="Notes">
                                </div>
                                <div class="col-span-12 md:col-span-1 flex md:justify-end">
                                    <x-danger-button size="sm" type="button" @click="removeSubcontractingRow(idx)">Delete</x-danger-button>
                                </div>
                            </div>
                        </template>
                        <div class="pt-3">
                            <x-brand-button type="button" size="sm" variant="ghost" @click="addSubcontractingRow()">+ New</x-brand-button>
                        </div>
                    </div>
                </section>

                <!-- OVERHEAD -->
                <section x-show="section==='Overhead'" x-cloak>
                    <h2 class="text-lg font-semibold mb-3 flex items-center gap-2">Overhead
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-brand-100 text-brand-800" x-text="formatMoney(overheadCurrentTotal())"></span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" :class="overheadPillClass()" x-text="overheadRatio().toFixed(1) + '%'"></span>
                    </h2>
                    <div class="rounded border p-4">
                        <!-- Graphics Row -->
                        <div class="grid md:grid-cols-3 gap-4 mb-4">
                            <!-- Key Factors -->
                            <div class="rounded border p-3 relative">
                                <div class="text-xs uppercase tracking-wide text-gray-500 mb-2">Key Factors</div>
                                <div class="absolute top-2 right-2 text-gray-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h18v4H3z"/><path d="M8 7v13"/><path d="M16 7v13"/></svg></div>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Labor Burden (%)</label>
                                        <input type="number" step="0.1" min="0" class="form-input w-full" x-model.number="overheadLaborBurdenPct" name="inputs[overhead][labor_burden_pct]" placeholder="0.0">
                                    </div>
                                </div>
                            </div>
                            <!-- Overhead Summary -->
                            <div class="rounded border p-3 relative">
                                <div class="text-xs uppercase tracking-wide text-gray-500 mb-2">Overhead Summary</div>
                                <div class="absolute top-2 right-2 text-gray-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M7 7h10M7 11h10M7 15h10"/></svg></div>
                                <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-sm">
                                    <div class="text-gray-600">Overhead Expenses</div>
                                    <div class="text-right font-semibold" x-text="formatMoney(overheadExpensesCurrentTotal())"></div>
                                    <div class="text-gray-600">Overhead Wages</div>
                                    <div class="text-right font-semibold" x-text="formatMoney(overheadWagesForecastTotal())"></div>
                                    <div class="text-gray-600">Overhead Equipment</div>
                                    <div class="text-right font-semibold" x-text="formatMoney(overheadEquipmentTotal())"></div>
                                </div>
                            </div>
                            <!-- Overhead Ratio -->
                            <div class="rounded border p-3 relative">
                                <div class="text-xs uppercase tracking-wide text-gray-500 mb-2">Overhead Ratio</div>
                                <div class="absolute top-2 right-2 text-gray-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg></div>
                                <div class="space-y-2">
                                    <div class="flex items-start justify-between gap-3 mb-2">
                                        <div class="flex-1">
                                            <div class="text-xs uppercase text-gray-500">Your Ratio</div>
                                            <div class="text-3xl font-bold"><span class="px-2 py-0.5 rounded-full" :class="overheadPillClass()" x-text="overheadRatio().toFixed(1) + '%'"></span></div>
                                        </div>
                                        <div class="flex-1 text-right">
                                            <div class="text-xs uppercase text-gray-500">Industry Avg</div>
                                            <div class="text-3xl font-bold"><span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-800" x-text="(overheadIndustryAvgRatio||0).toFixed(1) + '%'"></span></div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Industry Avg (%)</label>
                                        <input type="number" step="0.1" min="0" class="form-input w-full" x-model.number="overheadIndustryAvgRatio" name="inputs[overhead][industry_avg_ratio]" placeholder="24.8">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Overhead Tabs -->
                        <div class="inline-flex rounded-md border overflow-hidden mb-4">
                            <button type="button" class="px-3 py-1.5 text-sm" :class="{ 'bg-gray-200 text-gray-900' : overheadTab==='expenses' }" @click="overheadTab='expenses'">Overhead Expenses</button>
                            <button type="button" class="px-3 py-1.5 text-sm border-l" :class="{ 'bg-gray-200 text-gray-900' : overheadTab==='wages' }" @click="overheadTab='wages'">Overhead Wages</button>
                            <button type="button" class="px-3 py-1.5 text-sm border-l" :class="{ 'bg-gray-200 text-gray-900' : overheadTab==='equipment' }" @click="overheadTab='equipment'">Overhead Equipment</button>
                        </div>
                        <!-- Overhead Expenses Table -->
                        <div class="mb-6" x-show="overheadTab==='expenses'">
                            <div class="hidden md:grid grid-cols-12 gap-3 text-xs font-medium text-gray-600 border-b pb-2">
                                <div class="col-span-2">Acct. ID</div>
                                <div class="col-span-3">Overhead</div>
                                <div class="col-span-2">Previous $</div>
                                <div class="col-span-2">Current $</div>
                                <div class="col-span-2">Comments</div>
                                <div class="col-span-1 text-right">Actions</div>
                            </div>
                            <template x-for="(row, idx) in overheadExpensesRows" :key="'oe'+idx">
                                <div class="grid grid-cols-12 gap-3 items-center py-2 border-b">
                                    <div class="col-span-12 md:col-span-2">
                                        <label class="md:hidden block text-xs text-gray-500">Acct. ID</label>
                                        <input type="text" class="form-input w-full" x-model="row.account_id" :name="'inputs[overhead][expenses][rows]['+idx+'][account_id]'" placeholder="e.g., 7001">
                                    </div>
                                    <div class="col-span-12 md:col-span-3">
                                        <label class="md:hidden block text-xs text-gray-500">Overhead</label>
                                        <input type="text" class="form-input w-full" x-model="row.expense" :name="'inputs[overhead][expenses][rows]['+idx+'][expense]'" placeholder="e.g., Utilities">
                                    </div>
                                    <div class="col-span-6 md:col-span-2">
                                        <label class="md:hidden block text-xs text-gray-500">Previous $</label>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-input w-full" x-model="row.previous" :name="'inputs[overhead][expenses][rows]['+idx+'][previous]'" placeholder="0.00">
                                    </div>
                                    <div class="col-span-6 md:col-span-2">
                                        <label class="md:hidden block text-xs text-gray-500">Current $</label>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-input w-full" x-model="row.current" :name="'inputs[overhead][expenses][rows]['+idx+'][current]'" placeholder="0.00">
                                    </div>
                                    <div class="col-span-6 md:col-span-2">
                                        <label class="md:hidden block text-xs text-gray-500">Comments</label>
                                        <input type="text" class="form-input w-full" x-model="row.comments" :name="'inputs[overhead][expenses][rows]['+idx+'][comments]'" placeholder="Notes">
                                    </div>
                                    <div class="col-span-12 md:col-span-1 flex md:justify-end">
                                        <x-danger-button size="sm" type="button" @click="removeOverheadExpenseRow(idx)">Delete</x-danger-button>
                                    </div>
                                </div>
                            </template>
                            <div class="pt-3">
                                <x-brand-button type="button" size="sm" variant="ghost" @click="addOverheadExpenseRow()">+ New</x-brand-button>
                            </div>
                        </div>
                        <!-- Overhead Wages Table -->
                        <div class="mb-6" x-show="overheadTab==='wages'">
                            <div class="hidden md:grid grid-cols-12 gap-3 text-xs font-medium text-gray-600 border-b pb-2">
                                <div class="col-span-3">Salary</div>
                                <div class="col-span-2">Previous $</div>
                                <div class="col-span-2">Forecast $</div>
                                <div class="col-span-2">% Diff</div>
                                <div class="col-span-2">Comments</div>
                                <div class="col-span-1 text-right">Actions</div>
                            </div>
                            <template x-for="(row, idx) in overheadWagesRows" :key="'ow'+idx">
                                <div class="grid grid-cols-12 gap-3 items-center py-2 border-b">
                                    <div class="col-span-12 md:col-span-3">
                                        <label class="md:hidden block text-xs text-gray-500">Salary</label>
                                        <input type="text" class="form-input w-full" x-model="row.title" :name="'inputs[overhead][wages][rows]['+idx+'][title]'" placeholder="e.g., Office Admin">
                                    </div>
                                    <div class="col-span-6 md:col-span-2">
                                        <label class="md:hidden block text-xs text-gray-500">Previous $</label>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-input w-full" x-model="row.previous" :name="'inputs[overhead][wages][rows]['+idx+'][previous]'" placeholder="0.00">
                                    </div>
                                    <div class="col-span-6 md:col-span-2">
                                        <label class="md:hidden block text-xs text-gray-500">Forecast $</label>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-input w-full" x-model="row.forecast" :name="'inputs[overhead][wages][rows]['+idx+'][forecast]'" placeholder="0.00">
                                    </div>
                                    <div class="col-span-6 md:col-span-2">
                                        <label class="md:hidden block text-xs text-gray-500">% Diff</label>
                                        <input type="text" class="form-input w-full bg-gray-50" :value="overheadWageDiff(row)" readonly tabindex="-1">
                                    </div>
                                    <div class="col-span-6 md:col-span-2">
                                        <label class="md:hidden block text-xs text-gray-500">Comments</label>
                                        <input type="text" class="form-input w-full" x-model="row.comments" :name="'inputs[overhead][wages][rows]['+idx+'][comments]'" placeholder="Notes">
                                    </div>
                                    <div class="col-span-12 md:col-span-1 flex md:justify-end">
                                        <x-danger-button size="sm" type="button" @click="removeOverheadWageRow(idx)">Delete</x-danger-button>
                                    </div>
                                </div>
                            </template>
                            <div class="pt-3">
                                <x-brand-button type="button" size="sm" variant="ghost" @click="addOverheadWageRow()">+ New</x-brand-button>
                            </div>
                        </div>
                        <!-- Overhead Equipment (mirrors Equipment) -->
                        <div class="mb-2" x-show="overheadTab==='equipment'">
                            <div class="text-sm font-semibold mb-2">Overhead Equipment</div>
                            <div class="hidden md:grid grid-cols-12 gap-2 text-xs font-medium text-gray-600 border-b pb-2">
                                <div class="col-span-2">Equipment Type</div>
                                <div class="col-span-1">Qty</div>
                                <div class="col-span-2">Class</div>
                                <div class="col-span-4">Description</div>
                                <div class="col-span-2">Cost/Yr/Ea</div>
                                <div class="col-span-1 text-right">Cost/Yr/Ea</div>
                            </div>
                            <template x-for="(row, idx) in overheadEquipmentRows" :key="'oer'+idx">
                                <div class="grid grid-cols-12 gap-2 items-center py-2 border-b" @keydown.escape.stop="row._menuOpen=false">
                                    <div class="col-span-12 md:col-span-2">
                                        <label class="md:hidden block text-xs text-gray-500">Equipment Type</label>
                                        <input type="text" class="form-input w-full" x-model="row.type" :name="'inputs[overhead][equipment][rows]['+idx+'][type]'" placeholder="e.g., Copier">
                                    </div>
                                    <div class="col-span-6 md:col-span-1">
                                        <label class="md:hidden block text-xs text-gray-500">Qty</label>
                                        <input type="number" step="1" min="0" class="form-input w-full" x-model="row.qty" :name="'inputs[overhead][equipment][rows]['+idx+'][qty]'" placeholder="0">
                                    </div>
                                    <div class="col-span-6 md:col-span-2">
                                        <label class="md:hidden block text-xs text-gray-500">Class</label>
                                        <select class="form-select w-full" x-model="row.class" :name="'inputs[overhead][equipment][rows]['+idx+'][class]'" @change="if(row.class==='Owned' && !row.owned){row.owned={ replacement_value:'', fees:'', years:'', salvage_value:'', months_per_year:'', division_months:'', interest_rate_pct:'' }}; if(row.class==='Leased' && !row.leased){row.leased={ monthly_payment:'', payments_per_year:'', months_per_year:'', division_months:'' }}; if(row.class==='Group' && !row.group){row.group={ items: [] }}" >
                                            <option>Custom</option>
                                            <option>Owned</option>
                                            <option>Leased</option>
                                            <option>Group</option>
                                        </select>
                                        <!-- Always submit months fields so values persist regardless of panel state -->
                                        <template x-if="row.class==='Owned'">
                                            <div class="hidden">
                                                <input type="hidden" :name="'inputs[overhead][equipment][rows]['+idx+'][owned][months_per_year]'" :value="row.owned.months_per_year">
                                                <input type="hidden" :name="'inputs[overhead][equipment][rows]['+idx+'][owned][division_months]'" :value="row.owned.division_months">
                                            </div>
                                        </template>
                                        <template x-if="row.class==='Leased'">
                                            <div class="hidden">
                                                <input type="hidden" :name="'inputs[overhead][equipment][rows]['+idx+'][leased][payments_per_year]'" :value="row.leased.payments_per_year">
                                                <input type="hidden" :name="'inputs[overhead][equipment][rows]['+idx+'][leased][months_per_year]'" :value="row.leased.months_per_year">
                                                <input type="hidden" :name="'inputs[overhead][equipment][rows]['+idx+'][leased][division_months]'" :value="row.leased.division_months">
                                            </div>
                                        </template>
                                    </div>
                                    <div class="col-span-12 md:col-span-4 relative">
                                        <label class="md:hidden block text-xs text-gray-500">Description</label>
                                        <input type="text" class="form-input w-full" x-model="row.description" :name="'inputs[overhead][equipment][rows]['+idx+'][description]'" placeholder="Notes">
                                    </div>
                                    <div class="col-span-6 md:col-span-2">
                                        <label class="md:hidden block text-xs text-gray-500">Cost/Yr/Ea</label>
                                        <template x-if="row.class==='Owned'">
                                            <div class="relative">
                                                <input type="text" class="form-input w-full bg-green-50 pr-10" :value="(computeOwnedAnnual(row) || 0).toFixed(2)" readonly tabindex="-1" placeholder="0.00">
                                                <input type="hidden" :name="'inputs[overhead][equipment][rows]['+idx+'][cost_per_year]'" :value="computeOwnedAnnual(row)">
                                                <!-- Ensure owned fields persist even when collapsed -->
                                                <template x-if="!row._ownedOpen">
                                                    <div>
                                                        <input type="hidden" :name="'inputs[overhead][equipment][rows]['+idx+'][owned][replacement_value]'" :value="row.owned.replacement_value">
                                                        <input type="hidden" :name="'inputs[overhead][equipment][rows]['+idx+'][owned][fees]'" :value="row.owned.fees">
                                                        <input type="hidden" :name="'inputs[overhead][equipment][rows]['+idx+'][owned][years]'" :value="row.owned.years">
                                                        <input type="hidden" :name="'inputs[overhead][equipment][rows]['+idx+'][owned][salvage_value]'" :value="row.owned.salvage_value">
                                                        <input type="hidden" :name="'inputs[overhead][equipment][rows]['+idx+'][owned][months_per_year]'" :value="row.owned.months_per_year">
                                                        <input type="hidden" :name="'inputs[overhead][equipment][rows]['+idx+'][owned][division_months]'" :value="row.owned.division_months">
                                                        <input type="hidden" :name="'inputs[overhead][equipment][rows]['+idx+'][owned][interest_rate_pct]'" :value="row.owned.interest_rate_pct">
                                                    </div>
                                                </template>
                                                <button type="button" class="absolute inset-y-0 right-1 my-auto h-7 w-7 rounded border bg-white/80 hover:bg-white flex items-center justify-center shadow-sm"
                                                        @click.prevent="row._ownedOpen = !row._ownedOpen"
                                                        :aria-expanded="row._ownedOpen ? 'true' : 'false'"
                                                        title="Toggle calculator">
                                                    <svg viewBox="0 0 24 24" class="h-4 w-4 text-gray-700" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                                                        <path d="M7 7h10M7 11h4M13 11h4M7 15h4M13 15h4"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>
                                        <template x-if="row.class==='Leased'">
                                            <div class="relative">
                                                <input type="text" class="form-input w-full bg-green-50 pr-10" :value="(computeLeasedAnnual(row) || 0).toFixed(2)" readonly tabindex="-1" placeholder="0.00">
                                                <input type="hidden" :name="'inputs[overhead][equipment][rows]['+idx+'][cost_per_year]'" :value="computeLeasedAnnual(row)">
                                                <!-- Ensure leased fields persist even when collapsed -->
                                                <template x-if="!row._ownedOpen">
                                                    <div>
                                                        <input type="hidden" :name="'inputs[overhead][equipment][rows]['+idx+'][leased][monthly_payment]'" :value="row.leased.monthly_payment">
                                                        <input type="hidden" :name="'inputs[overhead][equipment][rows]['+idx+'][leased][payments_per_year]'" :value="row.leased.payments_per_year">
                                                        <input type="hidden" :name="'inputs[overhead][equipment][rows]['+idx+'][leased][months_per_year]'" :value="row.leased.months_per_year">
                                                        <input type="hidden" :name="'inputs[overhead][equipment][rows]['+idx+'][leased][division_months]'" :value="row.leased.division_months">
                                                    </div>
                                                </template>
                                                <button type="button" class="absolute inset-y-0 right-1 my-auto h-7 w-7 rounded border bg-white/80 hover:bg-white flex items-center justify-center shadow-sm"
                                                        @click.prevent="row._ownedOpen = !row._ownedOpen"
                                                        :aria-expanded="row._ownedOpen ? 'true' : 'false'"
                                                        title="Toggle calculator">
                                                    <svg viewBox="0 0 24 24" class="h-4 w-4 text-gray-700" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                                                        <path d="M7 7h10M7 11h4M13 11h4M7 15h4M13 15h4"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>
                                        <template x-if="row.class!=='Owned' && row.class!=='Leased' && row.class!=='Group'">
                                            <input type="number" step="0.01" min="0" class="form-input w-full" x-model="row.cost_per_year" :name="'inputs[overhead][equipment][rows]['+idx+'][cost_per_year]'" placeholder="0.00">
                                        </template>
                                    </div>
                                    <div class="col-span-4 md:col-span-1 text-right font-semibold relative">
                                        <span class="inline-block mr-2" x-text="formatMoney(perUnitCost(row))"></span>
                                        <div class="inline-block relative" @keydown.escape.stop="row._menuOpen=false">
                                            <button type="button" class="h-6 w-6 inline-flex items-center justify-center rounded border bg-white hover:bg-gray-50 text-gray-700"
                                                    @click.stop="row._menuOpen = !row._menuOpen"
                                                    :aria-expanded="row._menuOpen ? 'true' : 'false'"
                                                    title="Row actions">
                                                <svg viewBox="0 0 20 20" class="h-4 w-4" fill="currentColor"><path d="M10 3a2 2 0 110 4 2 2 0 010-4zm0 5a2 2 0 110 4 2 2 0 010-4zm0 5a2 2 0 110 4 2 2 0 010-4z"/></svg>
                                            </button>
                                            <div class="absolute right-0 mt-1 w-44 bg-white border rounded shadow z-10" x-show="row._menuOpen" x-cloak @click.outside="row._menuOpen=false">
                                                <button type="button" class="block w-full text-left px-3 py-1.5 text-sm hover:bg-gray-50"
                                                        @click="moveOverheadEquipmentToEquipment(idx); row._menuOpen=false">Move to Equipment</button>
                                                <button type="button" class="block w-full text-left px-3 py-1.5 text-sm text-red-600 hover:bg-red-50"
                                                        @click="removeOverheadEquipmentRow(idx); row._menuOpen=false">Delete</button>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Owned details panel (Overhead Equipment) -->
                                    <div class="col-span-12" x-show="row.class==='Owned' && row._ownedOpen">
                                        <div class="mt-2 bg-green-50 border border-green-200 rounded p-3 space-y-3">
                                            <div class="text-sm uppercase tracking-wide text-green-700 pb-2 mb-2 border-b-2 border-green-700 border-double">Owned – Cost/Year/Ea Breakdown</div>
                                            <div class="space-y-1.5">
                                                <div class="flex items-center justify-between py-1.5">
                                                    <label class="text-sm font-medium text-gray-800 pr-3">Replacement value</label>
                                                    <input type="number" step="0.01" min="0" class="form-input w-28 md:w-36 text-sm" x-model="row.owned.replacement_value" :name="'inputs[overhead][equipment][rows]['+idx+'][owned][replacement_value]'">
                                                </div>
                                                <div class="flex items-center justify-between py-1.5">
                                                    <label class="text-sm font-medium text-gray-800 pr-3">Additional fees/taxes/admin</label>
                                                    <input type="number" step="0.01" min="0" class="form-input w-28 md:w-36 text-sm" x-model="row.owned.fees" :name="'inputs[overhead][equipment][rows]['+idx+'][owned][fees]'">
                                                </div>
                                                <div class="flex items-center justify-between py-1.5">
                                                    <label class="text-sm font-medium text-gray-800 pr-3">Useful life (years)</label>
                                                    <input type="number" step="0.1" min="0.1" class="form-input w-28 md:w-36 text-sm" x-model="row.owned.years" :name="'inputs[overhead][equipment][rows]['+idx+'][owned][years]'">
                                                </div>
                                                <div class="flex items-center justify-between py-1.5">
                                                    <label class="text-sm font-medium text-gray-800 pr-3">End-of-life value</label>
                                                    <input type="number" step="0.01" min="0" class="form-input w-28 md:w-36 text-sm" x-model="row.owned.salvage_value" :name="'inputs[overhead][equipment][rows]['+idx+'][owned][salvage_value]'">
                                                </div>
                                                <div class="flex items-center justify-between py-1.5">
                                                    <label class="text-sm font-medium text-gray-800 pr-3">Months used per year (1–12)</label>
                                                    <select class="form-select w-28 md:w-36 text-sm" x-model="row.owned.months_per_year" x-init="$el.value = (row.owned.months_per_year || '')" :name="'inputs[overhead][equipment][rows]['+idx+'][owned][months_per_year]'">
                                                        <option value="" disabled x-bind:selected="!row.owned.months_per_year">Select…</option>
                                                        <template x-for="m in 12" :key="'oem'+m">
                                                            <option :value="String(m)" :selected="String(row.owned.months_per_year) === String(m)" x-text="m"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                                <div class="flex items-center justify-between py-1.5">
                                                    <label class="text-sm font-medium text-gray-800 pr-3">Division months (1–12)</label>
                                                    <select class="form-select w-28 md:w-36 text-sm" x-model="row.owned.division_months" x-init="$el.value = (row.owned.division_months || '')" :name="'inputs[overhead][equipment][rows]['+idx+'][owned][division_months]'">
                                                        <option value="" disabled x-bind:selected="!row.owned.division_months">Select…</option>
                                                        <template x-for="m in 12" :key="'oed'+m">
                                                            <option :value="String(m)" :selected="String(row.owned.division_months) === String(m)" x-text="m"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                                <div class="flex items-center justify-between py-1.5 mt-2 pt-2 border-t-2 border-green-700 border-double">
                                                    <label class="text-sm font-medium text-gray-800 pr-3">Inflation/Interest rate (%)</label>
                                                    <input type="number" step="0.01" min="0" max="100" class="form-input w-28 md:w-36 text-sm" x-model="row.owned.interest_rate_pct" :name="'inputs[overhead][equipment][rows]['+idx+'][owned][interest_rate_pct]'">
                                                </div>
                                            </div>
                                            <div class="grid md:grid-cols-5 gap-3 text-sm mt-2">
                                                <div>
                                                    <div class="text-gray-600">Annual cost/equipment</div>
                                                    <div class="text-base font-semibold" x-text="formatMoney(computeOwnedAnnual(row))"></div>
                                                </div>
                                                <div>
                                                    <div class="text-gray-600">Monthly (calendar)</div>
                                                    <div class="text-base font-semibold" x-text="formatMoney(computeOwnedMonthlyCalendar(row))"></div>
                                                </div>
                                                <div>
                                                    <div class="text-gray-600">Monthly (active)</div>
                                                    <div class="text-base font-semibold" x-text="formatMoney(computeOwnedMonthlyActive(row))"></div>
                                                </div>
                                                <div>
                                                    <div class="text-gray-600">Division Annual</div>
                                                    <div class="text-base font-semibold" x-text="formatMoney(computeDivisionAnnual(row))"></div>
                                                </div>
                                                <div>
                                                    <div class="text-gray-600">Division Monthly (active)</div>
                                                    <div class="text-base font-semibold" x-text="formatMoney(computeDivisionMonthlyActive(row))"></div>
                                                </div>
                                            </div>
                                            <div class="mt-2 pt-2 border-t flex items-center justify-between" x-show="computeOwnedInterestLifeCompounded(row) > 0">
                                                <div class="text-sm text-gray-700">Interest/Inflation value over the life of the equipment:</div>
                                                <div class="text-sm font-semibold" x-text="formatMoney(computeOwnedInterestLifeCompounded(row))"></div>
                                            </div>
                                            <div class="text-xs text-gray-600">Annual cost = (replacement value + fees + interest over life - end-of-life value) / useful life.</div>
                                        </div>
                                    </div>
                                    <!-- Leased details panel (Overhead Equipment) -->
                                    <div class="col-span-12" x-show="row.class==='Leased' && row._ownedOpen">
                                        <div class="mt-2 bg-green-50 border border-green-200 rounded p-3 space-y-3">
                                            <div class="text-sm uppercase tracking-wide text-green-700 pb-2 mb-2 border-b-2 border-green-700 border-double">Leased – Cost/Year/Ea Breakdown</div>
                                            <div class="space-y-1.5">
                                                <div class="flex items-center justify-between py-1.5">
                                                    <label class="text-sm font-medium text-gray-800 pr-3">Enter the monthly payment, including tax</label>
                                                    <input type="number" step="0.01" min="0" class="form-input w-28 md:w-36 text-sm" x-model="row.leased.monthly_payment" :name="'inputs[overhead][equipment][rows]['+idx+'][leased][monthly_payment]'">
                                                </div>
                                                <div class="flex items-center justify-between py-1.5">
                                                    <label class="text-sm font-medium text-gray-800 pr-3">How many payments do you make per year</label>
                                                    <select class="form-select w-28 md:w-36 text-sm" x-model="row.leased.payments_per_year" x-init="$el.value = (row.leased.payments_per_year || '')" :name="'inputs[overhead][equipment][rows]['+idx+'][leased][payments_per_year]'">
                                                        <option value="" disabled x-bind:selected="!row.leased.payments_per_year">Select…</option>
                                                        <template x-for="m in 12" :key="'oelp'+m">
                                                            <option :value="String(m)" :selected="String(row.leased.payments_per_year) === String(m)" x-text="m"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                                <div class="flex items-center justify-between py-1.5">
                                                    <label class="text-sm font-medium text-gray-800 pr-3">Enter the number of months per year you use it</label>
                                                    <select class="form-select w-28 md:w-36 text-sm" x-model="row.leased.months_per_year" x-init="$el.value = (row.leased.months_per_year || '')" :name="'inputs[overhead][equipment][rows]['+idx+'][leased][months_per_year]'">
                                                        <option value="" disabled x-bind:selected="!row.leased.months_per_year">Select…</option>
                                                        <template x-for="m in 12" :key="'oelm'+m">
                                                            <option :value="String(m)" :selected="String(row.leased.months_per_year) === String(m)" x-text="m"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                                <div class="flex items-center justify-between py-1.5">
                                                    <label class="text-sm font-medium text-gray-800 pr-3">If this is a divisional budget, months this works in this division</label>
                                                    <select class="form-select w-28 md:w-36 text-sm" x-model="row.leased.division_months" x-init="$el.value = (row.leased.division_months || '')" :name="'inputs[overhead][equipment][rows]['+idx+'][leased][division_months]'">
                                                        <option value="" disabled x-bind:selected="!row.leased.division_months">Select…</option>
                                                        <template x-for="m in 12" :key="'oeld'+m">
                                                            <option :value="String(m)" :selected="String(row.leased.division_months) === String(m)" x-text="m"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="grid md:grid-cols-5 gap-3 text-sm mt-2">
                                                <div>
                                                    <div class="text-gray-600">Annual ROI</div>
                                                    <div class="text-base font-semibold" x-text="formatMoney(computeLeasedAnnual(row))"></div>
                                                </div>
                                                <div>
                                                    <div class="text-gray-600">Monthly ROI</div>
                                                    <div class="text-base font-semibold" x-text="formatMoney(computeLeasedMonthlyCalendar(row))"></div>
                                                </div>
                                                <div>
                                                    <div class="text-gray-600">Monthly (active)</div>
                                                    <div class="text-base font-semibold" x-text="formatMoney(computeLeasedMonthlyActive(row))"></div>
                                                </div>
                                                <div>
                                                    <div class="text-gray-600">Division Annual</div>
                                                    <div class="text-base font-semibold" x-text="formatMoney(computeLeasedDivisionAnnual(row))"></div>
                                                </div>
                                                <div>
                                                    <div class="text-gray-600">Division Monthly (active)</div>
                                                    <div class="text-base font-semibold" x-text="formatMoney(computeLeasedDivisionMonthlyActive(row))"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <div class="pt-3 flex items-center justify-between">
                                <x-brand-button type="button" size="sm" variant="ghost" @click="addOverheadEquipmentRow()">+ New</x-brand-button>
                                <div class="text-sm text-gray-700" x-show="overheadEquipmentTotal() > 0"><span class="font-semibold">Total Equipment:</span> <span x-text="formatMoney(overheadEquipmentTotal())"></span></div>
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
    window.__initialEquipmentRows = @json($initialEquipmentRows);
    window.__initialEquipmentGeneral = @json($initialEquipmentGeneral);
    window.__initialEquipmentRentals = @json($initialEquipmentRentals);
    window.__initialEquipmentIndustryAvgRatio = @json($initialEquipmentIndustryAvg);
    window.__initialMaterialsRows = @json($initialMaterialsRows);
    window.__initialMaterialsTaxPct = @json($initialMaterialsTaxPct);
    window.__initialMaterialsIndustryAvg = @json($initialMaterialsIndustryAvg);
    window.__initialOverheadExpensesRows = @json($initialOverheadExpensesRows);
    window.__initialOverheadWagesRows = @json($initialOverheadWagesRows);
    window.__initialOverheadEquipmentRows = @json($initialOverheadEquipmentRows);
    window.__initialOverheadEquipmentGeneral = @json($initialOverheadEquipmentGeneral);
    window.__initialOverheadEquipmentRentals = @json($initialOverheadEquipmentRentals);
    window.__initialOverheadIndustryAvg = @json($initialOverheadIndustryAvg);
    window.__initialOverheadLaborBurden = @json($initialOverheadLaborBurden);
    window.__initialSubcontractingRows = @json($initialSubcontractingRows);

    // Alpine data for the budget editor
    window.budgetEditor = function(){
        return {
            section: (new URL(window.location.href)).searchParams.get('section') || 'Budget Info',
            salesRows: Array.isArray(window.__initialSalesRows) ? window.__initialSalesRows : [],
            laborTab: 'hourly',
            overheadTab: 'expenses',
            hourlyRows: Array.isArray(window.__initialHourlyRows) ? window.__initialHourlyRows : [],
            salaryRows: Array.isArray(window.__initialSalaryRows) ? window.__initialSalaryRows : [],
            equipmentRows: Array.isArray(window.__initialEquipmentRows) ? window.__initialEquipmentRows : [],
            materialsRows: Array.isArray(window.__initialMaterialsRows) ? window.__initialMaterialsRows : [],
            overheadExpensesRows: Array.isArray(window.__initialOverheadExpensesRows) ? window.__initialOverheadExpensesRows : [],
            overheadWagesRows: Array.isArray(window.__initialOverheadWagesRows) ? window.__initialOverheadWagesRows : [],
            overheadEquipmentRows: Array.isArray(window.__initialOverheadEquipmentRows) ? window.__initialOverheadEquipmentRows : [],
            subcontractingRows: Array.isArray(window.__initialSubcontractingRows) ? window.__initialSubcontractingRows : [],
            equipmentGeneral: (window.__initialEquipmentGeneral && typeof window.__initialEquipmentGeneral === 'object') ? {
                fuel: Number(window.__initialEquipmentGeneral.fuel ?? 0),
                repairs: Number(window.__initialEquipmentGeneral.repairs ?? 0),
                insurance_misc: Number(window.__initialEquipmentGeneral.insurance_misc ?? 0),
            } : { fuel: 0, repairs: 0, insurance_misc: 0 },
            equipmentRentals: (window.__initialEquipmentRentals !== null && window.__initialEquipmentRentals !== undefined && window.__initialEquipmentRentals !== '') ? Number(window.__initialEquipmentRentals) : 0,
            equipmentIndustryAvgRatio: (window.__initialEquipmentIndustryAvgRatio !== null && window.__initialEquipmentIndustryAvgRatio !== undefined && window.__initialEquipmentIndustryAvgRatio !== '') ? Number(window.__initialEquipmentIndustryAvgRatio) : 13.7,
            materialsTaxPct: (window.__initialMaterialsTaxPct !== null && window.__initialMaterialsTaxPct !== undefined && window.__initialMaterialsTaxPct !== '') ? Number(window.__initialMaterialsTaxPct) : 0,
            materialsIndustryAvgRatio: (window.__initialMaterialsIndustryAvg !== null && window.__initialMaterialsIndustryAvg !== undefined && window.__initialMaterialsIndustryAvg !== '') ? Number(window.__initialMaterialsIndustryAvg) : 22.3,
            overheadLaborBurdenPct: (window.__initialOverheadLaborBurden !== null && window.__initialOverheadLaborBurden !== undefined && window.__initialOverheadLaborBurden !== '') ? Number(window.__initialOverheadLaborBurden) : 0,
            overheadIndustryAvgRatio: (window.__initialOverheadIndustryAvg !== null && window.__initialOverheadIndustryAvg !== undefined && window.__initialOverheadIndustryAvg !== '') ? Number(window.__initialOverheadIndustryAvg) : 24.8,
            overheadEquipmentGeneral: (window.__initialOverheadEquipmentGeneral && typeof window.__initialOverheadEquipmentGeneral === 'object') ? {
                fuel: Number(window.__initialOverheadEquipmentGeneral.fuel ?? 0),
                repairs: Number(window.__initialOverheadEquipmentGeneral.repairs ?? 0),
                insurance_misc: Number(window.__initialOverheadEquipmentGeneral.insurance_misc ?? 0),
            } : { fuel: 0, repairs: 0, insurance_misc: 0 },
            overheadEquipmentRentals: (window.__initialOverheadEquipmentRentals !== null && window.__initialOverheadEquipmentRentals !== undefined && window.__initialOverheadEquipmentRentals !== '') ? Number(window.__initialOverheadEquipmentRentals) : 0,
            init(){
                // Ensure toggle flags and sub-objects exist and normalize select-bound values as numbers to preserve selection
                this.equipmentRows = (Array.isArray(this.equipmentRows) ? this.equipmentRows : []).map(r => {
                    if (r && typeof r === 'object') {
                        if (r._ownedOpen === undefined) r._ownedOpen = false;
                        if (r._menuOpen === undefined) r._menuOpen = false;
                        if (!r.owned) r.owned = { replacement_value:'', fees:'', years:'', salvage_value:'', months_per_year:'', division_months:'', interest_rate_pct:'' };
                        if (!r.leased) r.leased = { monthly_payment:'', payments_per_year:'', months_per_year:'', division_months:'' };
                        if (!r.group) r.group = { items: [] };

                        // Normalize to strings so <select> value binding matches option values
                        if (r.owned) {
                            if (r.owned.months_per_year !== undefined && r.owned.months_per_year !== null && r.owned.months_per_year !== '') {
                                r.owned.months_per_year = String(r.owned.months_per_year);
                            } else { r.owned.months_per_year = r.owned.months_per_year || ''; }
                            if (r.owned.division_months !== undefined && r.owned.division_months !== null && r.owned.division_months !== '') {
                                r.owned.division_months = String(r.owned.division_months);
                            } else { r.owned.division_months = r.owned.division_months || ''; }
                        }
                        if (r.leased) {
                            if (r.leased.payments_per_year !== undefined && r.leased.payments_per_year !== null && r.leased.payments_per_year !== '') {
                                r.leased.payments_per_year = String(r.leased.payments_per_year);
                            } else { r.leased.payments_per_year = r.leased.payments_per_year || ''; }
                            if (r.leased.months_per_year !== undefined && r.leased.months_per_year !== null && r.leased.months_per_year !== '') {
                                r.leased.months_per_year = String(r.leased.months_per_year);
                            } else { r.leased.months_per_year = r.leased.months_per_year || ''; }
                            if (r.leased.division_months !== undefined && r.leased.division_months !== null && r.leased.division_months !== '') {
                                r.leased.division_months = String(r.leased.division_months);
                            } else { r.leased.division_months = r.leased.division_months || ''; }
                        }
                    }
                    return r;
                });
                // Normalize overhead equipment rows to ensure calculator panels work
                this.overheadEquipmentRows = (Array.isArray(this.overheadEquipmentRows) ? this.overheadEquipmentRows : []).map(r => {
                    if (r && typeof r === 'object') {
                        if (r._ownedOpen === undefined) r._ownedOpen = false;
                        if (r._menuOpen === undefined) r._menuOpen = false;
                        if (!r.owned) r.owned = { replacement_value:'', fees:'', years:'', salvage_value:'', months_per_year:'', division_months:'', interest_rate_pct:'' };
                        if (!r.leased) r.leased = { monthly_payment:'', payments_per_year:'', months_per_year:'', division_months:'' };
                        if (!r.group) r.group = { items: [] };

                        // Normalize to strings so <select> value binding matches option values
                        if (r.owned) {
                            if (r.owned.months_per_year !== undefined && r.owned.months_per_year !== null && r.owned.months_per_year !== '') {
                                r.owned.months_per_year = String(r.owned.months_per_year);
                            } else { r.owned.months_per_year = r.owned.months_per_year || ''; }
                            if (r.owned.division_months !== undefined && r.owned.division_months !== null && r.owned.division_months !== '') {
                                r.owned.division_months = String(r.owned.division_months);
                            } else { r.owned.division_months = r.owned.division_months || ''; }
                        }
                        if (r.leased) {
                            if (r.leased.payments_per_year !== undefined && r.leased.payments_per_year !== null && r.leased.payments_per_year !== '') {
                                r.leased.payments_per_year = String(r.leased.payments_per_year);
                            } else { r.leased.payments_per_year = r.leased.payments_per_year || ''; }
                            if (r.leased.months_per_year !== undefined && r.leased.months_per_year !== null && r.leased.months_per_year !== '') {
                                r.leased.months_per_year = String(r.leased.months_per_year);
                            } else { r.leased.months_per_year = r.leased.months_per_year || ''; }
                            if (r.leased.division_months !== undefined && r.leased.division_months !== null && r.leased.division_months !== '') {
                                r.leased.division_months = String(r.leased.division_months);
                            } else { r.leased.division_months = r.leased.division_months || ''; }
                        }
                    }
                    return r;
                });
            },
            burdenPct: (window.__initialLaborBurdenPct !== null && window.__initialLaborBurdenPct !== undefined && window.__initialLaborBurdenPct !== '') ? Number(window.__initialLaborBurdenPct) : 0,
            otMultiplier: (window.__initialOtMultiplier !== null && window.__initialOtMultiplier !== undefined && window.__initialOtMultiplier !== '') ? Number(window.__initialOtMultiplier) : 1.5,
            industryAvgRatio: (window.__initialIndustryAvgRatio !== null && window.__initialIndustryAvgRatio !== undefined && window.__initialIndustryAvgRatio !== '') ? Number(window.__initialIndustryAvgRatio) : 26.6,
            addSalesRow() { this.salesRows.push({ account_id: '', division: '', previous: '', forecast: '', comments: '' }); },
            removeSalesRow(i) { this.salesRows.splice(i, 1); },
            addHourlyRow(){ this.hourlyRows.push({ type:'', staff:'', hrs:'', ot_hrs:'', avg_wage:'', bonus:'' }); },
            removeHourlyRow(i){ this.hourlyRows.splice(i,1); },
            addSalaryRow(){ this.salaryRows.push({ type:'', staff:'', ann_hrs:'', ann_salary:'', bonus:'' }); },
            removeSalaryRow(i){ this.salaryRows.splice(i,1); },
            addMaterialsRow(){ this.materialsRows.push({ account_id:'', expense:'', previous:'', current:'', comments:'' }); },
            removeMaterialsRow(i){ this.materialsRows.splice(i,1); },
            addOverheadExpenseRow(){ this.overheadExpensesRows.push({ account_id:'', expense:'', previous:'', current:'', comments:'' }); },
            removeOverheadExpenseRow(i){ this.overheadExpensesRows.splice(i,1); },
              addOverheadWageRow(){ this.overheadWagesRows.push({ title:'', previous:'', forecast:'', comments:'' }); },
  removeOverheadWageRow(i){ this.overheadWagesRows.splice(i,1); },
  addOverheadEquipmentRow(){ this.overheadEquipmentRows.push({ type:'', qty:'', class:'Custom', description:'', cost_per_year:'', _ownedOpen:false, _menuOpen:false, owned: { replacement_value:'', fees:'', years:'', salvage_value:'', months_per_year:'', division_months:'', interest_rate_pct:'' }, leased: { monthly_payment:'', payments_per_year:'', months_per_year:'', division_months:'' } }); },
  removeOverheadEquipmentRow(i){ this.overheadEquipmentRows.splice(i,1); },
  moveOverheadEquipmentToEquipment(i){
      const row = this.overheadEquipmentRows[i];
      if (!row) return;
      // Push a shallow copy into Equipment list with compatible structure
      const clone = JSON.parse(JSON.stringify(row));
      // Ensure keys exist as in equipment rows
      if (!clone.owned) clone.owned = { replacement_value:'', fees:'', years:'', salvage_value:'', months_per_year:'', division_months:'', interest_rate_pct:'' };
      if (!clone.leased) clone.leased = { monthly_payment:'', payments_per_year:'', months_per_year:'', division_months:'' };
      if (!clone.group) clone.group = { items: [] };
      clone._ownedOpen = false; clone._menuOpen = false;
      this.equipmentRows.push(clone);
      // Remove from overhead equipment
      this.overheadEquipmentRows.splice(i,1);
  },
  addSubcontractingRow(){ this.subcontractingRows.push({ account_id:'', expense:'', previous:'', current:'', comments:'' }); },
            removeSubcontractingRow(i){ this.subcontractingRows.splice(i,1); },
            addEquipmentRow(){ this.equipmentRows.push({ type:'', qty:'', class:'Custom', description:'', cost_per_year:'', _ownedOpen:false, _menuOpen:false, owned: { replacement_value:'', fees:'', years:'', salvage_value:'', months_per_year:'', division_months:'', interest_rate_pct:'' }, leased: { monthly_payment:'', payments_per_year:'', months_per_year:'', division_months:'' }, group: { items: [] } }); },
            removeEquipmentRow(i){ this.equipmentRows.splice(i,1); },
            // Owned calculations (Annual = ((replacement + fees + interest over life) - end-of-life value) / useful life)
            computeOwnedAnnual(row){
                const cap = (parseFloat(row?.owned?.replacement_value) || 0) + (parseFloat(row?.owned?.fees) || 0);
                const years = Math.max(0.1, parseFloat(row?.owned?.years) || 0);
                const rate = Math.max(0, Math.min(100, parseFloat(row?.owned?.interest_rate_pct) || 0)) / 100;
                const salvage = Math.max(0, parseFloat(row?.owned?.salvage_value) || 0);
                const totalInflationLife = cap * (Math.pow(1 + rate, years) - 1);
                const numerator = (cap + totalInflationLife) - salvage;
                const annual = numerator / years;
                return Math.max(0, annual);
            },
            computeOwnedMonthlyCalendar(row){
                const annual = this.computeOwnedAnnual(row);
                return annual / 12;
            },
            computeOwnedMonthlyActive(row){
                const annual = this.computeOwnedAnnual(row);
                const months = Math.max(1, parseInt(row?.owned?.months_per_year) || 12);
                return annual / months;
            },
            computeDivisionAnnual(row){
                const annual = this.computeOwnedAnnual(row);
                const divMonths = Math.max(0, parseInt(row?.owned?.division_months) || 0);
                return annual * (divMonths / 12);
            },
            computeDivisionMonthlyActive(row){
                const divAnnual = this.computeDivisionAnnual(row);
                const divMonths = Math.max(1, parseInt(row?.owned?.division_months) || 1);
                return divAnnual / divMonths;
            },
            // Interest/Inflation value over life (compounded, per equipment unit)
            computeOwnedInterestLifeCompounded(row){
                if (row.class !== 'Owned') return 0;
                const cap = (parseFloat(row?.owned?.replacement_value) || 0) + (parseFloat(row?.owned?.fees) || 0);
                const years = Math.max(0.1, parseFloat(row?.owned?.years) || 0);
                const rate = Math.max(0, Math.min(100, parseFloat(row?.owned?.interest_rate_pct) || 0)) / 100;
                const compoundedInterest = cap * (Math.pow(1 + rate, years) - 1);
                return Math.max(0, compoundedInterest);
            },
            // Group computations
            computeGroupItemAnnual(it){
                const p = parseFloat(it?.purchase_price) || 0;
                const r = parseFloat(it?.resale_value) || 0;
                const y = Math.max(0.1, parseFloat(it?.years) || 0);
                return Math.max(0, (p - r) / y);
            },
            computeGroupAnnual(row){
                const items = (row?.group?.items || []);
                return items.reduce((s, it) => s + ((parseFloat(it?.qty)||0) * this.computeGroupItemAnnual(it)), 0);
            },
            addGroupItem(row){ if (!row.group) row.group = { items: [] }; row.group.items.push({ name:'', qty:'', purchase_price:'', resale_value:'', years:'' }); },
            removeGroupItem(row, i){ try { row.group.items.splice(i,1); } catch(_) {} },
            // Leased computations
            computeLeasedAnnual(row){
                const pmt = Math.max(0, parseFloat(row?.leased?.monthly_payment) || 0);
                const perYear = Math.max(1, parseInt(row?.leased?.payments_per_year) || 12);
                return pmt * perYear;
            },
            computeLeasedMonthlyCalendar(row){
                const annual = this.computeLeasedAnnual(row);
                return annual / 12;
            },
            computeLeasedMonthlyActive(row){
                const annual = this.computeLeasedAnnual(row);
                const months = Math.max(1, parseInt(row?.leased?.months_per_year) || 12);
                return annual / months;
            },
            computeLeasedDivisionAnnual(row){
                const annual = this.computeLeasedAnnual(row);
                const divMonths = Math.max(0, parseInt(row?.leased?.division_months) || 0);
                return annual * (divMonths / 12);
            },
            computeLeasedDivisionMonthlyActive(row){
                const divAnnual = this.computeLeasedDivisionAnnual(row);
                const divMonths = Math.max(1, parseInt(row?.leased?.division_months) || 1);
                return divAnnual / divMonths;
            },
            perUnitCost(row){ if (row.class==='Owned') return this.computeOwnedAnnual(row); if (row.class==='Leased') return this.computeLeasedAnnual(row); if (row.class==='Group') return this.computeGroupAnnual(row); return (parseFloat(row.cost_per_year)||0); },
            equipmentRowTotal(row){ const q = parseFloat(row.qty)||0; const c = this.perUnitCost(row); return q * c; },
            equipmentTotal(){ return this.equipmentRows.reduce((s,r)=> s + this.equipmentRowTotal(r), 0); },
            equipmentDisplayedListTotal(){ return this.equipmentRows.reduce((s,r)=> s + (this.perUnitCost(r)||0), 0); },
            generalExpensesTotal(){
                const g = this.equipmentGeneral || {};
                return (parseFloat(g.fuel)||0) + (parseFloat(g.repairs)||0) + (parseFloat(g.insurance_misc)||0);
            },
            equipmentExpensesTotal(){
                // Total Equipment Expense = Equipment Expenses (list total) + Other (General Expenses total)
                return (this.equipmentDisplayedListTotal() || 0) + (this.generalExpensesTotal() || 0);
            },
            equipmentGrandTotal(){
                return this.equipmentExpensesTotal() + (parseFloat(this.equipmentRentals)||0);
            },
            equipmentRatio(){
                const sales = this.forecastTotal();
                if (!sales) return 0;
                return (this.equipmentGrandTotal() / Math.abs(sales)) * 100;
            },
            moveEquipmentToOverhead(i){
                const row = this.equipmentRows[i];
                if (!row) return;
                const addVal = this.equipmentRowTotal(row);
                const ohInput = document.querySelector('input[name="inputs[overhead][total]"]');
                if (ohInput) {
                    const cur = parseFloat(ohInput.value || '0') || 0;
                    const next = cur + addVal;
                    ohInput.value = next.toFixed(2);
                    // Also reflect in any bound Alpine? Not bound, so direct is fine.
                }
                this.removeEquipmentRow(i);
            },
            computeDiff(row) {
                const p = parseFloat(row.previous);
                const f = parseFloat(row.forecast);
                if (!isFinite(p) || p === 0) return '0%';
                const pct = (( (isFinite(f) ? f : 0) - p) / Math.abs(p)) * 100;
                return pct.toFixed(1) + '%';
            },
            // Formatting helpers
            formatMoney(n){ const v = parseFloat(n) || 0; return '$' + v.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },
            // Ratio coloring helpers (within 4 percentage points of industry avg => green; else red)
            within4(cur, avg){ const a = Number(cur)||0; const b = Number(avg)||0; return Math.abs(a - b) <= 4; },
            laborPillClass(){ return this.within4(this.laborRatio(), this.industryAvgRatio) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; },
              equipmentPillClass(){ return this.within4(this.equipmentRatio(), this.equipmentIndustryAvgRatio) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; },
              materialsPillClass(){ return this.within4(this.materialsRatio(), this.materialsIndustryAvgRatio) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; },
              materialsPillClassFor(val){ return this.within4(val, this.materialsIndustryAvgRatio) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; },
              overheadPillClass(){ return this.within4(this.overheadRatio(), this.overheadIndustryAvgRatio) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; },
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
                const mult = parseFloat(this.otMultiplier) || 1.5;
                const h = this.hourlyRows.reduce((sum, r) => {
                    const staff = parseFloat(r.staff) || 0;
                    const hrs = parseFloat(r.hrs) || 0;
                    const ot = parseFloat(r.ot_hrs) || 0;
                    return sum + (staff * (hrs + (ot * mult)));
                }, 0);
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
            // Materials totals and ratios
            materialsPrevTotal(){ const sum = this.materialsRows.reduce((s,r)=> s + (parseFloat(r.previous)||0), 0); const t = (parseFloat(this.materialsTaxPct)||0)/100; return sum * (1 + Math.max(0,t)); },
            materialsCurrentTotal(){ const sum = this.materialsRows.reduce((s,r)=> s + (parseFloat(r.current)||0), 0); const t = (parseFloat(this.materialsTaxPct)||0)/100; return sum * (1 + Math.max(0,t)); },
            materialsPrevRatio(){ const sales = this.forecastTotal(); if (!sales) return 0; return (this.materialsPrevTotal() / Math.abs(sales)) * 100; },
            materialsRatio(){ const sales = this.forecastTotal(); if (!sales) return 0; return (this.materialsCurrentTotal() / Math.abs(sales)) * 100; },
            // Subcontracting totals and ratios
            subcPrevTotal(){ return this.subcontractingRows.reduce((s,r)=> s + (parseFloat(r.previous)||0), 0); },
            subcCurrentTotal(){ return this.subcontractingRows.reduce((s,r)=> s + (parseFloat(r.current)||0), 0); },
            subcPrevRatio(){ const sales = this.forecastTotal(); if (!sales) return 0; return (this.subcPrevTotal() / Math.abs(sales)) * 100; },
            subcRatio(){ const sales = this.forecastTotal(); if (!sales) return 0; return (this.subcCurrentTotal() / Math.abs(sales)) * 100; },
            changeRing(){
                const c = this.changePercent();
                const pct = Math.max(0, Math.min(100, Math.abs(c)));
                const color = c >= 0 ? '#16a34a' : '#dc2626';
                return `conic-gradient(${color} 0 ${pct}%, #e5e7eb ${pct}%)`;
            },
            // Overhead totals/ratios
            overheadExpensesPrevTotal(){ return this.overheadExpensesRows.reduce((s,r)=> s + (parseFloat(r.previous)||0), 0); },
            overheadExpensesCurrentTotal(){ return this.overheadExpensesRows.reduce((s,r)=> s + (parseFloat(r.current)||0), 0); },
            overheadWagesPrevTotal(){ return this.overheadWagesRows.reduce((s,r)=> s + (parseFloat(r.previous)||0), 0); },
            overheadWagesForecastTotal(){ return this.overheadWagesRows.reduce((s,r)=> s + (parseFloat(r.forecast)||0), 0); },
            overheadEquipmentDisplayedListTotal(){ return this.overheadEquipmentRows.reduce((s,r)=> s + (this.perUnitCost(r)||0), 0); },
            overheadEquipmentRowTotal(row){ const qRaw = row?.qty; const q = (qRaw === '' || qRaw === null || qRaw === undefined) ? 1 : (parseFloat(qRaw)||0); const c = this.perUnitCost(row); return q * c; },
            overheadEquipmentTotal(){ return this.overheadEquipmentRows.reduce((s,r)=> s + this.overheadEquipmentRowTotal(r), 0); },
            overheadEquipmentExpensesTotal(){ return (this.overheadEquipmentDisplayedListTotal() || 0) + (parseFloat(this.overheadEquipmentGeneral.fuel)||0) + (parseFloat(this.overheadEquipmentGeneral.repairs)||0) + (parseFloat(this.overheadEquipmentGeneral.insurance_misc)||0); },
            overheadCurrentTotal(){ return this.overheadExpensesCurrentTotal() + this.overheadWagesForecastTotal() + this.overheadEquipmentTotal(); },
            overheadPrevTotal(){ return this.overheadExpensesPrevTotal() + this.overheadWagesPrevTotal(); },
            overheadRatio(){ const sales = this.forecastTotal(); if (!sales) return 0; return (this.overheadCurrentTotal() / Math.abs(sales)) * 100; }
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
        // Append close=1 to action so server can redirect away after save
        const form = document.getElementById('companyBudgetForm');
        if (form) {
            form.addEventListener('submit', () => {
                try {
                    const url = new URL(form.action, window.location.origin);
                    url.searchParams.set('close', '1');
                    form.action = url.pathname + url.search;
                } catch (e) {}
            });
        }
    });
</script>
@endpush
