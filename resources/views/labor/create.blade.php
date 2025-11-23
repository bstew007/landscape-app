@extends('layouts.sidebar')

@section('content')
@php
  $activeBudget = app(\App\Services\BudgetService::class)->active(false);
  $budgetMargin = (float) (($activeBudget->desired_profit_margin ?? 0.20)); // 0-1
  // Compute OH Recovery: Field Labor Hour Markup = OverheadCurrentTotal / Total Labor Hours
  $inputs = $activeBudget?->inputs ?? [];
  $expensesRows = (array) data_get($inputs, 'overhead.expenses.rows', []);
  $wagesRows = (array) data_get($inputs, 'overhead.wages.rows', []);
  $ohEquipRows = (array) data_get($inputs, 'overhead.equipment.rows', []);
  $ohExpenses = 0.0; foreach ($expensesRows as $r) { $ohExpenses += (float) ($r['current'] ?? 0); }
  $ohWages = 0.0; foreach ($wagesRows as $r) { $ohWages += (float) ($r['forecast'] ?? 0); }
  $ohEquip = 0.0; foreach ($ohEquipRows as $r) { $qty = (float) ($r['qty'] ?? 1); $per = (float) ($r['cost_per_year'] ?? 0); $ohEquip += ($qty * $per); }
  $ohTotal = $ohExpenses + $ohWages + $ohEquip;
  $hourlyRows = (array) data_get($inputs, 'labor.hourly.rows', []);
  $salaryRows = (array) data_get($inputs, 'labor.salary.rows', []);
  $totalHours = 0.0;
  foreach ($hourlyRows as $r) { $staff = (float) ($r['staff'] ?? 0); $hrs = (float) ($r['hrs'] ?? 0); $ot = (float) ($r['ot_hrs'] ?? 0); $totalHours += $staff * ($hrs + $ot); }
  foreach ($salaryRows as $r) { $staff = (float) ($r['staff'] ?? 0); $hrs = (float) ($r['ann_hrs'] ?? 0); $totalHours += $staff * $hrs; }
  $ohr = $totalHours > 0 ? ($ohTotal / $totalHours) : 0.0; // Field Labor Hour Markup ($/hr)
  $otMultiplier = (float) (data_get($inputs, 'labor.ot_multiplier', 1.5));
  $employeeRows = [];
  foreach ($hourlyRows as $r) {
      $employeeRows[] = [
          'label' => $r['type'] ?? 'Hourly',
          'wage' => isset($r['avg_wage']) ? (float) $r['avg_wage'] : 0.0,
          'reg_hrs' => isset($r['hrs']) ? (float) $r['hrs'] : 0.0,
          'ot_hrs' => isset($r['ot_hrs']) ? (float) $r['ot_hrs'] : 0.0,
          'count' => 0,
          'source' => 'hourly',
      ];
  }
  foreach ($salaryRows as $r) {
      $annHrs = (float) ($r['ann_hrs'] ?? 0);
      $annSal = (float) ($r['ann_salary'] ?? 0);
      $wage = $annHrs > 0 ? ($annSal / $annHrs) : 0.0;
      $employeeRows[] = [
          'label' => $r['type'] ?? 'Salary',
          'wage' => $wage,
          'reg_hrs' => isset($r['ann_hrs']) ? (float) $r['ann_hrs'] : 0.0,
          'ot_hrs' => 0.0,
          'count' => 0,
          'source' => 'salary',
      ];
  }
@endphp

<div class="p-4 space-y-4" data-theme="compact" x-data="{ isModal: {{ request()->has('modal') ? 'true' : 'false' }} }">
    @if ($errors->any())
        <div class="max-w-2xl mx-auto mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
            <ul class="list-disc pl-5 text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <x-modal name="labor-create" :show="true" maxWidth="3xl">
        <div x-data="laborCreateForm()" x-init="mode = 'budget'" class="p-4">
            <x-page-header title="Add Labor" eyebrow="Catalogs" variant="compact" class="mb-4 shadow-sm" x-show="!isModal">
                <x-slot:actions>
                    <a href="{{ route('labor.index') }}" class="inline-flex items-center h-9 px-3 rounded border text-sm hover:bg-gray-50">Cancel</a>
                    <button form="laborCreateForm" type="submit" class="inline-flex items-center h-9 px-4 rounded bg-green-600 text-white text-sm hover:bg-green-700">Save</button>
                </x-slot:actions>
            </x-page-header>
            
            <!-- Modal mode header/buttons -->
            <div x-show="isModal" class="flex items-center justify-end gap-2 mb-4">
                <button form="laborCreateForm" type="submit" class="inline-flex items-center h-9 px-4 rounded bg-green-600 text-white text-sm hover:bg-green-700">Save Labor</button>
            </div>

            <form id="laborCreateForm" method="POST" action="{{ route('labor.store') }}" class="space-y-6 mt-4" @submit="if (isModal) { $event.preventDefault(); submitInModalMode($event.target); }">
                @csrf
                <input type="hidden" name="type" value="{{ old('type','crew') }}">
                <!-- Base rate follows the Price Calculator -->
                <input type="hidden" name="base_rate" :value="price().toFixed(2)">
                <input type="hidden" name="pricing_mode" x-model="mode" value="budget">

                <!-- Two-column layout: left = Item Information, right = Calculators -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left: Item Information -->
                    <div class="space-y-6">
                        <x-panel-card title="Item Information" titleClass="text-lg font-semibold text-gray-900 mb-4" class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-3 items-center gap-4">
                                <label for="name" class="text-sm font-medium text-gray-800 sm:text-right whitespace-nowrap">Name</label>
                                <div class="sm:col-span-2">
                                    <input id="name" type="text" name="name" class="form-input w-full" value="{{ old('name') }}" required>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 items-center gap-4">
                                <label for="unit" class="text-sm font-medium text-gray-800 sm:text-right whitespace-nowrap">Units</label>
                                <div class="sm:col-span-2">
                                    <input id="unit" type="text" name="unit" class="form-input w-full" value="{{ old('unit','hr') }}" required>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 items-start gap-4">
                                <label for="description" class="pt-2 text-sm font-medium text-gray-800 sm:text-right whitespace-nowrap">Description</label>
                                <div class="sm:col-span-2">
                                    <textarea id="description" name="description" rows="2" class="form-textarea w-full" placeholder="Client-facing description">{{ old('description') }}</textarea>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 items-start gap-4">
                                <label for="internal_notes" class="pt-2 text-sm font-medium text-gray-800 sm:text-right whitespace-nowrap">Internal Notes</label>
                                <div class="sm:col-span-2">
                                    <textarea id="internal_notes" name="internal_notes" rows="2" class="form-textarea w-full" placeholder="Internal only">{{ old('internal_notes') }}</textarea>
                                </div>
                            </div>
                        </x-panel-card>
                    </div>

                    <!-- Right: Cost/Breakeven + Price Calculator -->
                    <div class="space-y-6">
                        <x-panel-card title="Cost + Breakeven" titleClass="text-lg font-semibold text-gray-900 mb-4" class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-3 items-center gap-4">
                                <label for="average_wage" class="text-sm font-medium text-gray-800 sm:text-right whitespace-nowrap">Average Wage</label>
                                <div class="sm:col-span-2">
                                    <div class="relative">
                                        <input id="average_wage" type="number" step="0.01" min="0" name="average_wage" class="form-input w-full pr-11" x-model.number="wage" value="{{ old('average_wage') }}">
                                        <button type="button" class="absolute inset-y-0 right-1 my-auto h-8 w-8 rounded border bg-white/90 hover:bg-white flex items-center justify-center shadow-sm" @click="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'wage-calc' }))" title="Open wage calculator" aria-label="Open wage calculator">
                                            <svg viewBox="0 0 24 24" class="h-4 w-4 text-gray-700" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                                <path d="M7 7h10M7 11h4M13 11h4M7 15h4M13 15h4"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 items-center gap-4">
                                <label for="overtime_factor" class="text-sm font-medium text-gray-800 sm:text-right whitespace-nowrap">Overtime Factor</label>
                                <div class="sm:col-span-2">
                                    <input id="overtime_factor" type="number" step="0.01" min="1" name="overtime_factor" class="form-input w-full" x-model.number="otFactor" value="{{ old('overtime_factor', 1.00) }}" placeholder="e.g., 1.5">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 items-center gap-4">
                                <label for="unbillable_percentage" class="text-sm font-medium text-gray-800 sm:text-right whitespace-nowrap">Unbillable %</label>
                                <div class="sm:col-span-2">
                                    <input id="unbillable_percentage" type="number" step="0.1" min="0" max="99.9" name="unbillable_percentage" class="form-input w-full" x-model.number="unbillable" value="{{ old('unbillable_percentage', 0) }}">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 items-center gap-4">
                                <label for="labor_burden_percentage" class="text-sm font-medium text-gray-800 sm:text-right whitespace-nowrap">Labor Burden %</label>
                                <div class="sm:col-span-2">
                                    <input id="labor_burden_percentage" type="number" step="0.1" min="0" name="labor_burden_percentage" class="form-input w-full" x-model.number="burden" value="{{ old('labor_burden_percentage', 0) }}">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 items-center gap-4">
                                <div class="text-sm font-medium text-gray-800 sm:text-right whitespace-nowrap">OH Markup</div>
                                <div class="sm:col-span-2 text-gray-900">$<span x-text="Number(overhead).toFixed(2)"></span><span class="text-gray-500">/hr</span></div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 items-center gap-4">
                                <label class="text-sm font-medium text-gray-800 sm:text-right whitespace-nowrap">Breakeven</label>
                                <div class="sm:col-span-2 text-gray-900">
                                    <span class="inline-block text-right sm:text-left w-full sm:w-32">$<span x-text="breakeven().toFixed(2)"></span></span>
                                </div>
                            </div>

                        </x-panel-card>

                        <x-panel-card title="Price Calculator" titleClass="text-lg font-semibold text-gray-900 mb-4" class="space-y-3">
                            <div class="space-y-2">
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="radio" name="pricing_mode_choice" value="budget" x-model="mode" checked>
                                    <span>Use Profit Margin from Budget</span>
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" name="pricing_mode_choice" value="custom-margin" x-model="mode">
                                    <span>Set a Custom Profit Margin</span>
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" name="pricing_mode_choice" value="custom-price" x-model="mode" @change="ensureCustomPriceSeed()">
                                    <span>Set a Custom Price</span>
                                </label>
                            </div>
                            <div class="space-y-3 mt-1">
                                <div class="grid grid-cols-1 sm:grid-cols-3 items-center gap-3">
                                    <div class="text-gray-600 text-sm whitespace-nowrap">Profit Margin</div>
                                    <div class="sm:col-span-2 flex items-center gap-3">
                                        <div class="relative inline-flex items-center" x-show="mode !== 'custom-margin'">
                                            <input
                                                type="text"
                                                class="form-input w-32 pr-7 bg-gray-50 text-right"
                                                x-bind:value="Number(budgetMargin).toFixed(1)"
                                                readonly
                                                aria-label="Budget profit margin">
                                            <span class="absolute right-2 text-gray-500 pointer-events-none">%</span>
                                        </div>
                                        <div class="relative inline-flex items-center" x-show="mode === 'custom-margin'">
                                            <input
                                                type="number"
                                                step="0.1"
                                                min="0"
                                                max="99.9"
                                                class="form-input w-32 pr-7 text-right"
                                                x-model.number="customMargin"
                                                x-ref="customMarginInput"
                                                aria-label="Custom profit margin percent">
                                            <span class="absolute right-2 text-gray-500 pointer-events-none">%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 items-center gap-3">
                                    <div class="text-gray-600 text-sm whitespace-nowrap">Price</div>
                                    <div class="sm:col-span-2 flex items-center gap-2">
                                        <div class="relative w-32">
                                            <span class="absolute inset-y-0 left-2 flex items-center text-sm text-gray-500">$</span>
                                            <input
                                                id="price_display"
                                                type="number"
                                                step="0.01"
                                                class="form-input w-full text-right pl-6"
                                                :readonly="mode !== 'custom-price'"
                                                :class="mode !== 'custom-price' ? 'bg-gray-50' : ''"
                                                :value="mode !== 'custom-price' ? price().toFixed(2) : (Number(customPrice)||0).toFixed(2)"
                                                @input="if (mode === 'custom-price') { customPrice = parseFloat($event.target.value) || 0 }"
                                                title="Breakeven ÷ (1 - profit margin)">
                                        </div>
                                        <span class="text-sm text-gray-600">/ man hr.</span>
                                    </div>
                                </div>
                            </div>
                        </x-panel-card>
                    </div>
                </div>

                <!-- Flags -->
                <div class="flex items-center gap-6 pt-2">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_billable" value="1" class="form-checkbox" {{ old('is_billable', true) ? 'checked' : '' }}>
                        <span class="ml-2 text-sm">Billable</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_active" value="1" class="form-checkbox" {{ old('is_active', true) ? 'checked' : '' }}>
                        <span class="ml-2 text-sm">Active</span>
                    </label>
                </div>

                <!-- Hidden/Advanced (optional) -->
                <input type="hidden" name="overtime_rate" value="{{ old('overtime_rate') }}">
                <input type="hidden" name="cost_code_id" value="{{ old('cost_code_id') }}">
            </form>


        </div>
        <script>
            function laborCreateForm(){
            return {
                // Inputs
                wage: Number({{ json_encode(old('average_wage', '')) }}) || 0,
                otFactor: Number({{ json_encode(old('overtime_factor', 1.00)) }}) || 1,
                unbillable: Number({{ json_encode(old('unbillable_percentage', 0)) }}) || 0,
                burden: Number({{ json_encode(old('labor_burden_percentage', 0)) }}) || 0,
                overhead: Number({{ json_encode(number_format($ohr, 2, '.', '')) }}) || 0,
                    // Pricing
                    mode: 'budget',
                    budgetMargin: (parseFloat('{{ number_format($budgetMargin * 100, 1, '.', '') }}') || 0), // %
                    customMargin: (() => {
                        const oldVal = parseFloat('{{ old('custom_margin', number_format($budgetMargin * 100, 1, '.', '')) }}');
                        const seed = parseFloat('{{ number_format($budgetMargin * 100, 1, '.', '') }}') || 0;
                        return Number.isFinite(oldVal) ? oldVal : seed;
                    })(), // %
                customPrice: Number({{ json_encode(old('base_rate', '')) }}) || 0,
                    // Wage modal data (no longer used inside nested modal)
                    // employees: {!! json_encode($employeeRows) !!},
                    // Derived helpers
                    init(){
                        // Ensure default mode is budget on load
                        if (!this.mode) this.mode = 'budget';
                        // Seed custom margin to budget margin on load if empty/invalid
                        const cm = Number(this.customMargin);
                        this.customMargin = Number.isFinite(cm) ? cm : (Number(this.budgetMargin) || 0);
                        // When switching to custom-margin, seed from budget if customMargin isn't valid yet
                        this.$watch('mode', (val) => {
                            try { document.querySelector('input[name="pricing_mode"]').value = val; } catch(_) {}
                            if (val === 'custom-margin') {
                                const cv = Number(this.customMargin);
                                if (!Number.isFinite(cv)) {
                                    this.customMargin = Number(this.budgetMargin) || 0;
                                }
                                // Focus the input when entering custom-margin
                                this.$nextTick(() => { try { this.$refs.customMarginInput && this.$refs.customMarginInput.focus(); this.$refs.customMarginInput.select?.(); } catch(_) {} });
                            }
                        });
                    },
                effectiveWage(){
                    const f = this.otFactor && this.otFactor > 0 ? this.otFactor : 1;
                    return this.wage * f;
                },
                    loadedWage(){ return (this.effectiveWage()) * (1 + ((Number(this.burden)||0)/100)); },
                    billableFraction(){ const frac = 1 - ((Number(this.unbillable)||0) / 100); return Math.max(0.01, frac); },
                    breakeven(){ return (this.loadedWage() / this.billableFraction()) + (Number(this.overhead)||0); },
                    selectedMargin(){
                        if (this.mode === 'custom-price') {
                            const p = Number(this.customPrice)||0; const c = this.breakeven();
                            if (p <= 0) return 0;
                            const m = (p - c) / p;
                            return Math.max(-100, Math.min(100, m * 100));
                        }
                        if (this.mode === 'custom-margin') return Number(this.customMargin)||0;
                        return Number(this.budgetMargin)||0;
                    },
                    price(){
                        if (this.mode === 'custom-price') return Number(this.customPrice)||0;
                        const marginPct = this.mode === 'custom-margin' ? (Number(this.customMargin)||0) : (Number(this.budgetMargin)||0);
                        const m = Math.min(99.9, Math.max(0, marginPct)) / 100; // 0-0.999
                        const c = this.breakeven();
                        return m >= 0.999 ? c : (c / (1 - m));
                    },
                    ensureCustomPriceSeed(){
                        if (!this.customPrice) this.customPrice = this.price();
                    },
                    submitInModalMode(form){
                        const fd = new FormData(form);
                        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                        fetch(form.action, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                            body: fd
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (window.parent && window.parent !== window) {
                                window.parent.postMessage({ type: 'labor:saved', labor: data }, '*');
                            }
                        })
                        .catch(err => {
                            console.error('Save failed', err);
                            alert('Failed to save labor item');
                        });
                    },
                    fmtMoney(v){ const n = Number(v)||0; return '$' + n.toFixed(2); },
                    // Wage modal helpers
                    openWageCalc(){ try { window.dispatchEvent(new CustomEvent('open-modal', { detail: 'wage-calc' })); } catch(_) {} },
                }
            }

        </script>
    </x-modal>

<!-- Wage Calculator Modal (top-level) -->
<x-modal name="wage-calc" :show="false" maxWidth="xl">
    <div class="p-4" x-data="wageCalcTopModal()">
        <div class="flex items-start justify-between pb-3 border-b">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Average Wage Calculator</h3>
                <p class="text-xs text-gray-600">Choose employees and counts to compute a weighted average.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="inline-flex items-center h-9 px-3 rounded border text-sm hover:bg-gray-50" @click="close()">Cancel</button>
                <button type="button" class="inline-flex items-center h-9 px-4 rounded bg-green-600 text-white text-sm hover:bg-green-700" @click="apply()">Apply Average</button>
            </div>
        </div>
        <div class="mt-3">
            <div class="hidden md:grid grid-cols-12 gap-2 text-xs font-medium text-gray-600 border-b pb-2">
                <div class="col-span-6">Employee</div>
                <div class="col-span-3">Hourly Wage</div>
                <div class="col-span-3">Count</div>
            </div>
            <template x-for="(row, idx) in employees" :key="'emp2'+idx">
                <div class="grid grid-cols-12 gap-2 items-center py-2 border-b">
                    <div class="col-span-12 md:col-span-6">
                        <label class="md:hidden block text-xs text-gray-500">Employee</label>
                        <div class="text-sm" x-text="row.label || (row.source==='salary' ? 'Salary' : 'Hourly')"></div>
                    </div>
                    <div class="col-span-6 md:col-span-3">
                        <label class="md:hidden block text-xs text-gray-500">Hourly Wage</label>
                        <input type="number" step="0.01" min="0" class="form-input w-full" x-model.number="row.wage">
                    </div>
                    <div class="col-span-6 md:col-span-3">
                        <label class="md:hidden block text-xs text-gray-500">Count</label>
                        <select class="form-select w-full" x-model.number="row.count">
                            <option :value="0">0</option>
                            <template x-for="n in 9" :key="'ct'+n">
                                <option :value="n" x-text="n"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </template>
                                    <div class="pt-2 text-sm text-gray-700 flex items-center justify-between">
                            <div><span class="font-medium">Preview Average:</span> <span x-text="fmtMoney(avg())"></span></div>
                            <button type="button" class="text-xs underline hover:text-brand-700" @click="reset(0)">Reset counts to 0</button>
                        </div>
                        <div class="text-sm text-gray-700 mt-1">
                            <span class="font-medium">O/T Factor (%):</span>
                            <span x-text="otFactorPct().toFixed(2) + '%' "></span>
                        </div>
        </div>
    </div>
</x-modal>

<script>
function wageCalcTopModal(){
    return {
        employees: {!! json_encode($employeeRows) !!},
        ot: Number({{ json_encode($otMultiplier) }}) || 1.0,
        fmtMoney(v){ const n = Number(v)||0; return '$' + n.toFixed(2); },
        avg(){ let sum=0, cnt=0; (this.employees||[]).forEach(r=>{ const c=Number(r.count)||0; const w=Number(r.wage)||0; sum += c*w; cnt += c; }); return cnt>0 ? (sum/cnt) : 0; },
        otFactor(){
            let sumW=0, sumH=0, sumBase=0; const m = Number(this.ot)||1;
            (this.employees||[]).forEach(r=>{
                const c=Number(r.count)||0; const w=Number(r.wage)||0; const rh=Number(r.reg_hrs)||0; const oh=Number(r.ot_hrs)||0;
                sumW += c * ( (rh*w) + (oh*w*m) );
                const th = (rh + oh);
                sumH += c * th;
                sumBase += c * ( th * w );
            });
            if (!sumH) return 0;
            const avgCombined = sumW / sumH;
            const avgBase = sumBase / sumH;
            return avgBase > 0 ? (avgCombined / avgBase) : 0;
                    },
                    otFactorPct(){ const f = this.otFactor(); return f > 0 ? (f - 1) * 100 : 0; },
                    reset(val=0){ (this.employees||[]).forEach(r=> { r.count = Number(val); }); },
        close(){ try { window.dispatchEvent(new CustomEvent('close-modal', { detail: 'wage-calc' })); } catch(_) {} },
        apply(){
            const avg = this.avg();
            const elW = document.querySelector('input[name="average_wage"]');
            if (elW) {
                elW.value = (Number(avg)||0).toFixed(2);
                elW.dispatchEvent(new Event('input', { bubbles: true }));
                elW.dispatchEvent(new Event('change', { bubbles: true }));
            }
            const ratio = this.otFactor();
            const elOT = document.querySelector('input[name="overtime_factor"]');
            if (elOT && ratio > 0) {
                elOT.value = Number(ratio).toFixed(4);
                elOT.dispatchEvent(new Event('input', { bubbles: true }));
                elOT.dispatchEvent(new Event('change', { bubbles: true }));
            }
            this.close();
        },
    }
}
</script>


</div>
@endsection
