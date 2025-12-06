@extends('layouts.sidebar')

@section('content')
<div x-data="{
    ownershipType: '{{ old('ownership_type', $equipment->ownership_type) }}',
    unit: '{{ old('unit', $equipment->unit) }}',
    hourlyCost: {{ old('hourly_cost', $equipment->hourly_cost) ?: 0 }},
    dailyCost: {{ old('daily_cost', $equipment->daily_cost) ?: 0 }},
    hourlyRate: {{ old('hourly_rate', $equipment->hourly_rate) ?: 0 }},
    dailyRate: {{ old('daily_rate', $equipment->daily_rate) ?: 0 }},
    profitPercent: {{ old('profit_percent', $equipment->profit_percent ?? 20) }},
    
    calculateRate() {
        if (this.unit === 'hr' && this.hourlyCost > 0) {
            this.hourlyRate = this.hourlyCost * (1 + (this.profitPercent / 100));
        } else if (this.unit === 'day' && this.dailyCost > 0) {
            this.dailyRate = this.dailyCost * (1 + (this.profitPercent / 100));
        }
    }
}" class="space-y-8">
    
    <section class="rounded-[20px] sm:rounded-[28px] lg:rounded-[32px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-4 sm:p-6 lg:p-8 shadow-2xl border border-brand-800/40">
        <div class="flex flex-wrap items-start gap-4 sm:gap-6">
            <div class="space-y-2 sm:space-y-3 max-w-3xl">
                <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Equipment Catalog</p>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-semibold">Edit Equipment</h1>
                <p class="text-xs sm:text-sm text-brand-100/85">Update equipment details and pricing.</p>
            </div>
            <div class="ml-auto flex gap-2">
                <a href="{{ route('equipment.index') }}" class="inline-flex items-center h-9 px-4 rounded-lg border text-sm bg-white/10 text-white border-white/40 hover:bg-white/20">Cancel</a>
                <button form="equipmentEditForm" type="submit" class="inline-flex items-center h-9 px-4 rounded-lg bg-white text-brand-900 text-sm font-semibold hover:bg-brand-50">Update Equipment</button>
            </div>
        </div>
    </section>

    @if ($errors->any())
        <div class="rounded-lg bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3">
            <p class="font-semibold mb-2">Please fix the following errors:</p>
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden">
        <form id="equipmentEditForm" method="POST" action="{{ route('equipment.update', $equipment) }}" class="p-6 sm:p-8 space-y-8">
            @csrf
            @method('PATCH')

            {{-- Ownership Type Selector --}}
            <div class="space-y-3">
                <label class="block text-sm font-semibold text-brand-900">Equipment Ownership Type</label>
                <div class="grid grid-cols-2 gap-4">
                    <button type="button" 
                            class="flex items-center justify-center gap-3 px-6 py-4 rounded-xl border-2 transition-all duration-200"
                            :class="ownershipType === 'company' ? 'bg-green-50 border-green-500 shadow-md' : 'bg-white border-brand-200 hover:border-brand-300'"
                            @click="ownershipType = 'company'">
                        <span class="text-2xl">🏢</span>
                        <div class="text-left">
                            <div class="font-semibold" :class="ownershipType === 'company' ? 'text-green-900' : 'text-brand-900'">Company-Owned</div>
                            <div class="text-xs" :class="ownershipType === 'company' ? 'text-green-700' : 'text-brand-500'">Equipment we own</div>
                        </div>
                    </button>
                    <button type="button"
                            class="flex items-center justify-center gap-3 px-6 py-4 rounded-xl border-2 transition-all duration-200"
                            :class="ownershipType === 'rental' ? 'bg-blue-50 border-blue-500 shadow-md' : 'bg-white border-brand-200 hover:border-brand-300'"
                            @click="ownershipType = 'rental'">
                        <span class="text-2xl">🔑</span>
                        <div class="text-left">
                            <div class="font-semibold" :class="ownershipType === 'rental' ? 'text-blue-900' : 'text-brand-900'">Rental</div>
                            <div class="text-xs" :class="ownershipType === 'rental' ? 'text-blue-700' : 'text-brand-500'">Rented from vendor</div>
                        </div>
                    </button>
                </div>
                <input type="hidden" name="ownership_type" x-model="ownershipType">
            </div>

            {{-- Basic Information --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-semibold text-brand-900 mb-2">Equipment Name <span class="text-rose-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $equipment->name) }}" required
                           class="w-full rounded-lg border-brand-200 focus:border-brand-500 focus:ring focus:ring-brand-500/20"
                           placeholder="e.g., Bobcat Skid Steer">
                </div>

                <div>
                    <label for="sku" class="block text-sm font-semibold text-brand-900 mb-2">SKU / Equipment ID</label>
                    <input type="text" id="sku" name="sku" value="{{ old('sku', $equipment->sku) }}"
                           class="w-full rounded-lg border-brand-200 focus:border-brand-500 focus:ring focus:ring-brand-500/20"
                           placeholder="Optional identifier">
                </div>

                <div>
                    <label for="category" class="block text-sm font-semibold text-brand-900 mb-2">Category</label>
                    <input type="text" id="category" name="category" value="{{ old('category', $equipment->category) }}"
                           class="w-full rounded-lg border-brand-200 focus:border-brand-500 focus:ring focus:ring-brand-500/20"
                           placeholder="e.g., Skid Steer, Excavator, Trailer">
                </div>

                <div>
                    <label for="model" class="block text-sm font-semibold text-brand-900 mb-2">Model</label>
                    <input type="text" id="model" name="model" value="{{ old('model', $equipment->model) }}"
                           class="w-full rounded-lg border-brand-200 focus:border-brand-500 focus:ring focus:ring-brand-500/20"
                           placeholder="e.g., MT-100">
                </div>
            </div>

            {{-- Company-Owned Fields --}}
            <div x-show="ownershipType === 'company'" class="space-y-6">
                <div class="rounded-xl bg-green-50 border border-green-200 p-6">
                    <h3 class="text-sm font-semibold text-green-900 mb-4">Company Equipment Settings</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="asset_id" class="block text-sm font-semibold text-brand-900 mb-2">Link to Asset (Optional)</label>
                            <select id="asset_id" name="asset_id" class="w-full rounded-lg border-brand-200 focus:border-brand-500 focus:ring focus:ring-brand-500/20">
                                <option value="">-- Not linked --</option>
                                @foreach($assets as $asset)
                                    <option value="{{ $asset->id }}" {{ old('asset_id', $equipment->asset_id) == $asset->id ? 'selected' : '' }}>
                                        {{ $asset->name }} @if($asset->model)({{ $asset->model }})@endif
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-brand-500 mt-1">Link to an existing asset for tracking</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Rental Fields --}}
            <div x-show="ownershipType === 'rental'" class="space-y-6">
                <div class="rounded-xl bg-blue-50 border border-blue-200 p-6">
                    <h3 class="text-sm font-semibold text-blue-900 mb-4">Rental Information</h3>
                    
                    <div>
                        <label for="vendor_name" class="block text-sm font-semibold text-brand-900 mb-2">Rental Vendor</label>
                        <input type="text" id="vendor_name" name="vendor_name" value="{{ old('vendor_name', $equipment->vendor_name) }}"
                               class="w-full rounded-lg border-brand-200 focus:border-brand-500 focus:ring focus:ring-brand-500/20"
                               placeholder="e.g., United Rentals, Sunbelt">
                    </div>
                </div>
            </div>

            {{-- Pricing --}}
            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-brand-900 border-b border-brand-200 pb-2">Pricing</h3>
                
                <div>
                    <label class="block text-sm font-semibold text-brand-900 mb-2">Pricing Unit</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="unit" value="hr" x-model="unit" class="text-brand-600 focus:ring-brand-500">
                            <span class="text-sm">Hourly</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="unit" value="day" x-model="unit" class="text-brand-600 focus:ring-brand-500">
                            <span class="text-sm">Daily</span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div x-show="unit === 'hr'">
                        <label for="hourly_cost" class="block text-sm font-semibold text-brand-900 mb-2">Hourly Cost</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-brand-500">$</span>
                            <input type="number" id="hourly_cost" name="hourly_cost" x-model.number="hourlyCost" @input="calculateRate()" step="0.01" min="0"
                                   class="w-full pl-8 rounded-lg border-brand-200 focus:border-brand-500 focus:ring focus:ring-brand-500/20"
                                   placeholder="0.00">
                        </div>
                    </div>

                    <div x-show="unit === 'day'">
                        <label for="daily_cost" class="block text-sm font-semibold text-brand-900 mb-2">Daily Cost</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-brand-500">$</span>
                            <input type="number" id="daily_cost" name="daily_cost" x-model.number="dailyCost" @input="calculateRate()" step="0.01" min="0"
                                   class="w-full pl-8 rounded-lg border-brand-200 focus:border-brand-500 focus:ring focus:ring-brand-500/20"
                                   placeholder="0.00">
                        </div>
                    </div>

                    <div>
                        <label for="profit_percent" class="block text-sm font-semibold text-brand-900 mb-2">Profit Margin %</label>
                        <div class="relative">
                            <input type="number" id="profit_percent" name="profit_percent" x-model.number="profitPercent" @input="calculateRate()" step="0.1" min="0"
                                   class="w-full pr-8 rounded-lg border-brand-200 focus:border-brand-500 focus:ring focus:ring-brand-500/20"
                                   placeholder="20">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-brand-500">%</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div x-show="unit === 'hr'">
                        <label for="hourly_rate" class="block text-sm font-semibold text-brand-900 mb-2">Hourly Rate (Billable)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-brand-500">$</span>
                            <input type="number" id="hourly_rate" name="hourly_rate" x-model.number="hourlyRate" step="0.01" min="0"
                                   class="w-full pl-8 rounded-lg border-brand-200 focus:border-brand-500 focus:ring focus:ring-brand-500/20 bg-brand-50"
                                   placeholder="0.00">
                        </div>
                        <p class="text-xs text-brand-500 mt-1">Auto-calculated from cost + margin</p>
                    </div>

                    <div x-show="unit === 'day'">
                        <label for="daily_rate" class="block text-sm font-semibold text-brand-900 mb-2">Daily Rate (Billable)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-brand-500">$</span>
                            <input type="number" id="daily_rate" name="daily_rate" x-model.number="dailyRate" step="0.01" min="0"
                                   class="w-full pl-8 rounded-lg border-brand-200 focus:border-brand-500 focus:ring focus:ring-brand-500/20 bg-brand-50"
                                   placeholder="0.00">
                        </div>
                        <p class="text-xs text-brand-500 mt-1">Auto-calculated from cost + margin</p>
                    </div>
                </div>
            </div>

            {{-- Additional Details --}}
            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-brand-900 border-b border-brand-200 pb-2">Additional Details</h3>
                
                <div>
                    <label for="description" class="block text-sm font-semibold text-brand-900 mb-2">Description</label>
                    <textarea id="description" name="description" rows="3"
                              class="w-full rounded-lg border-brand-200 focus:border-brand-500 focus:ring focus:ring-brand-500/20"
                              placeholder="Equipment description for estimates">{{ old('description', $equipment->description) }}</textarea>
                </div>

                <div>
                    <label for="notes" class="block text-sm font-semibold text-brand-900 mb-2">Internal Notes</label>
                    <textarea id="notes" name="notes" rows="2"
                              class="w-full rounded-lg border-brand-200 focus:border-brand-500 focus:ring focus:ring-brand-500/20"
                              placeholder="Internal notes (not shown on estimates)">{{ old('notes', $equipment->notes) }}</textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $equipment->is_active) ? 'checked' : '' }}
                           class="rounded text-brand-600 focus:ring-brand-500">
                    <label for="is_active" class="text-sm font-medium text-brand-900">Active (available for estimates)</label>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-brand-200">
                <a href="{{ route('equipment.index') }}" class="px-6 py-2.5 rounded-lg border border-brand-300 text-brand-700 font-semibold hover:bg-brand-50">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-lg bg-brand-600 text-white font-semibold hover:bg-brand-700 shadow-sm">
                    Update Equipment
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
