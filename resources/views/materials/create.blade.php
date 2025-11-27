@extends('layouts.sidebar')

@section('content')
@php
    $categories = \App\Models\MaterialCategory::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
    $activeBudget = app(\App\Services\BudgetService::class)->active(false);
    $budgetMargin = (float) (($activeBudget->desired_profit_margin ?? 0.20)); // 0-1
    $overheadMarkup = 0.0; // TODO: Get from config or budget
@endphp

<script>
function materialCreateForm(){
return {
    // Inputs
    unitCost: {{ old('unit_cost', 0) ?: 0 }},
    taxRate: {{ old('tax_rate', 0) ?: 0 }},
    overheadMarkup: {{ number_format($overheadMarkup, 2, '.', '') }},
    // Pricing
    mode: 'budget',
    budgetMargin: {{ number_format($budgetMargin * 100, 1, '.', '') }},
    customMargin: {{ old('custom_margin', number_format($budgetMargin * 100, 1, '.', '')) }},
    customPrice: {{ old('unit_price', 0) ?: 0 }},
    
    init(){
        if (!this.mode) this.mode = 'budget';
        const cm = Number(this.customMargin);
        this.customMargin = Number.isFinite(cm) ? cm : (Number(this.budgetMargin) || 0);
    },
    
    // Cost with purchase tax
    costWithTax(){
        const cost = Number(this.unitCost) || 0;
        const taxPct = Number(this.taxRate) || 0;
        return cost * (1 + taxPct);
    },
    
    // Breakeven = cost with tax + overhead markup
    breakeven(){
        return this.costWithTax() + this.overheadMarkup;
    },
    
    // Price based on selected mode
    price(){
        if (this.mode === 'custom-price') return this.customPrice;
        const marginPct = this.mode === 'custom-margin' ? this.customMargin : this.budgetMargin;
        const m = Math.min(99.9, Math.max(0, marginPct)) / 100;
        const c = this.breakeven();
        return m >= 0.999 ? c : (c / (1 - m));
    },
    
    ensureCustomPriceSeed(){
        if (!this.customPrice) this.customPrice = this.price();
    }
}
}
</script>

<div class="space-y-8 max-w-7xl mx-auto p-4">
    <div x-data="materialCreateForm()">
        <section class="rounded-[32px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-6 sm:p-8 shadow-2xl border border-brand-800/40 relative overflow-hidden">
            <div class="flex flex-wrap items-start gap-6">
                <div class="space-y-3 max-w-3xl">
                    <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Material Catalog</p>
                    <h1 class="text-3xl sm:text-4xl font-semibold">Add Material</h1>
                    <p class="text-sm text-brand-100/85">Create a new material catalog item with cost, tax, overhead, and profit margins.</p>
                </div>
                <div class="ml-auto flex gap-2">
                    <a href="{{ route('materials.index') }}" class="inline-flex items-center h-9 px-4 rounded-lg border text-sm bg-white/10 text-white border-white/40 hover:bg-white/20">Cancel</a>
                    <button form="materialCreateForm" type="submit" class="inline-flex items-center h-9 px-4 rounded-lg bg-white text-brand-900 text-sm font-semibold hover:bg-brand-50">Save Material</button>
                </div>
            </div>
            <dl class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-8 text-sm text-brand-100">
                <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                    <dt class="text-xs uppercase tracking-wide text-brand-200">OH Markup</dt>
                    <dd class="text-2xl font-semibold text-white mt-2">$<span x-text="Number(overheadMarkup).toFixed(2)"></span></dd>
                </div>
                <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                    <dt class="text-xs uppercase tracking-wide text-brand-200">Breakeven</dt>
                    <dd class="text-2xl font-semibold text-white mt-2">$<span x-text="breakeven().toFixed(2)"></span></dd>
                </div>
                <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                    <dt class="text-xs uppercase tracking-wide text-brand-200">Price</dt>
                    <dd class="text-2xl font-semibold text-white mt-2">$<span x-text="price().toFixed(2)"></span></dd>
                </div>
            </dl>
        </section>

    @if ($errors->any())
        <div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3">
            <ul class="list-disc pl-5 text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden p-6 sm:p-8">
        @if ($errors->any())
            <div class="p-4 bg-red-50 text-red-900 rounded-2xl border border-red-200 text-sm mb-6">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="materialCreateForm" method="POST" action="{{ route('materials.store') }}" class="space-y-6">
            @csrf
            
            <!-- Hidden fields for pricing -->
            <input type="hidden" name="unit_price" :value="price().toFixed(2)">
            <input type="hidden" name="breakeven" :value="breakeven().toFixed(2)">
            <input type="hidden" name="profit_percent" :value="((breakeven() > 0 ? ((price() - breakeven()) / price()) * 100 : 0)).toFixed(2)">
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
                            <label for="sku" class="text-sm font-medium text-gray-800 sm:text-right whitespace-nowrap">SKU</label>
                            <div class="sm:col-span-2">
                                <input id="sku" type="text" name="sku" class="form-input w-full" value="{{ old('sku') }}">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 items-center gap-4">
                            <label for="unit" class="text-sm font-medium text-gray-800 sm:text-right whitespace-nowrap">Unit</label>
                            <div class="sm:col-span-2">
                                <input id="unit" type="text" name="unit" class="form-input w-full" value="{{ old('unit', 'ea') }}" required>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 items-center gap-4">
                            <label for="vendor_name" class="text-sm font-medium text-gray-800 sm:text-right whitespace-nowrap">Vendor</label>
                            <div class="sm:col-span-2">
                                <input id="vendor_name" type="text" name="vendor_name" class="form-input w-full" value="{{ old('vendor_name') }}">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 items-center gap-4">
                            <label for="vendor_sku" class="text-sm font-medium text-gray-800 sm:text-right whitespace-nowrap">Vendor SKU</label>
                            <div class="sm:col-span-2">
                                <input id="vendor_sku" type="text" name="vendor_sku" class="form-input w-full" value="{{ old('vendor_sku') }}">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 items-start gap-4">
                            <label for="description" class="pt-2 text-sm font-medium text-gray-800 sm:text-right whitespace-nowrap">Description</label>
                            <div class="sm:col-span-2">
                                <textarea id="description" name="description" rows="2" class="form-textarea w-full" placeholder="Displayed when selecting materials">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </x-panel-card>
                </div>

                <!-- Right: Cost/Breakeven + Price Calculator -->
                <div class="space-y-6">
                    <x-panel-card title="Cost + Breakeven" titleClass="text-lg font-semibold text-gray-900 mb-4" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 items-center gap-4">
                            <label for="unit_cost" class="text-sm font-medium text-gray-800 sm:text-right whitespace-nowrap">Unit Cost</label>
                            <div class="sm:col-span-2">
                                <input id="unit_cost" type="number" step="0.01" min="0" name="unit_cost" class="form-input w-full" x-model.number="unitCost" value="{{ old('unit_cost', 0) }}" required>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 items-center gap-4">
                            <label for="tax_rate" class="text-sm font-medium text-gray-800 sm:text-right whitespace-nowrap">Purchase Tax %</label>
                            <div class="sm:col-span-2">
                                <input id="tax_rate" type="number" step="0.001" min="0" name="tax_rate" class="form-input w-full" x-model.number="taxRate" value="{{ old('tax_rate', 0) }}">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 items-center gap-4">
                            <div class="text-sm font-medium text-gray-800 sm:text-right whitespace-nowrap">Cost w/ Tax</div>
                            <div class="sm:col-span-2 text-gray-900">$<span x-text="costWithTax().toFixed(2)"></span></div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 items-center gap-4">
                            <div class="text-sm font-medium text-gray-800 sm:text-right whitespace-nowrap">OH Markup</div>
                            <div class="sm:col-span-2 text-gray-900">$<span x-text="Number(overheadMarkup).toFixed(2)"></span></div>
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
                            <label class="flex items-center gap-2 text-sm">
                                <input type="radio" name="pricing_mode_choice" value="custom-margin" x-model="mode">
                                <span>Set a Custom Profit Margin</span>
                            </label>
                            <label class="flex items-center gap-2 text-sm">
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
                                <div class="text-gray-600 text-sm whitespace-nowrap">Unit Price</div>
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
                                </div>
                            </div>
                        </div>
                    </x-panel-card>
                </div>
            </div>

            <!-- Flags -->
            <div class="flex items-center gap-6 pt-2">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_taxable" value="1" class="form-checkbox" {{ old('is_taxable', true) ? 'checked' : '' }}>
                    <span class="ml-2 text-sm">Taxable (to customer)</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_active" value="1" class="form-checkbox" {{ old('is_active', true) ? 'checked' : '' }}>
                    <span class="ml-2 text-sm">Active</span>
                </label>
            </div>

            <!-- Categories Section -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Categories</h3>
                @if($categories->isEmpty())
                    <div class="text-center py-8 text-gray-500">
                        <p>No categories available.</p>
                        <a href="{{ route('admin.material-categories.create') }}" class="text-brand-600 hover:underline">Create a category first</a>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($categories as $category)
                            <label class="flex items-start gap-3 p-3 rounded-lg border border-brand-200 hover:bg-brand-50 cursor-pointer transition">
                                <input type="checkbox" name="categories[]" value="{{ $category->id }}" 
                                       class="mt-0.5 form-checkbox rounded border-brand-300 text-brand-600 focus:ring-brand-500">
                                <div class="flex-1">
                                    <div class="font-semibold text-gray-900">{{ $category->name }}</div>
                                    @if($category->description)
                                        <div class="text-xs text-gray-500 mt-1">{{ $category->description }}</div>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>
        </form>
    </section>
    </div>
</div>
@endsection
