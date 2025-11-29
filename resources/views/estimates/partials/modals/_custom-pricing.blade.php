{{-- 
    CUSTOM PRICING MODAL
    
    This modal allows users to override work area pricing with custom total price or profit percentage.
    Uses Alpine.js for state management and interactivity.
--}}

<div x-data="customPricingModal()" 
     x-show="show"
     x-cloak
     @open-custom-pricing.window="openModal($event.detail)"
     @keydown.escape.window="closeModal()"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-black/50 transition-opacity" 
         x-show="show"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="closeModal()"></div>

    {{-- Modal Container --}}
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white shadow-2xl transition-all"
             x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.stop>

            {{-- Header --}}
            <div class="border-b border-gray-200 bg-gradient-to-r from-brand-50 to-blue-50 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-100">
                            <svg class="h-5 w-5 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <template x-if="mode === 'price'">
                                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                                </template>
                                <template x-if="mode === 'profit'">
                                    <path d="M3 3v18h18M18 17V9M13 17V5M8 17v-3"/>
                                </template>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900" x-text="mode === 'price' ? 'Custom Total Price' : 'Custom Profit %'"></h3>
                            <p class="text-xs text-gray-600" x-text="areaName"></p>
                        </div>
                    </div>
                    <button type="button" 
                            @click="closeModal()"
                            class="rounded-lg p-1 text-gray-400 hover:bg-white/50 hover:text-gray-600 transition">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div class="px-6 py-5">
                {{-- Current Values --}}
                <div class="mb-5 rounded-lg bg-gray-50 p-4">
                    <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Current Values</h4>
                    <div class="space-y-1.5">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Total Price:</span>
                            <span class="font-semibold text-gray-900" x-text="'$' + Number(currentTotal).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Total Cost:</span>
                            <span class="font-semibold text-gray-900" x-text="'$' + Number(currentCost).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Profit Margin:</span>
                            <span class="font-semibold" :class="currentProfit >= 20 ? 'text-green-600' : currentProfit >= 10 ? 'text-yellow-600' : 'text-red-600'" x-text="currentProfit.toFixed(1) + '%'"></span>
                        </div>
                    </div>
                </div>

                {{-- Input Form --}}
                <form @submit.prevent="submitOverride()">
                    <div class="mb-5">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            <span x-show="mode === 'price'">Target Total Price</span>
                            <span x-show="mode === 'profit'">Target Profit Percentage</span>
                        </label>
                        <div class="relative">
                            <template x-if="mode === 'price'">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <span class="text-gray-500 text-sm">$</span>
                                </div>
                            </template>
                            <input type="number" 
                                   step="0.01"
                                   x-model.number="targetValue"
                                   :placeholder="mode === 'price' ? '0.00' : '0.0'"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                   :class="mode === 'price' ? 'pl-7' : ''"
                                   required
                                   autofocus>
                            <template x-if="mode === 'profit'">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <span class="text-gray-500 text-sm">%</span>
                                </div>
                            </template>
                        </div>
                        <p class="mt-1.5 text-xs text-gray-500">
                            <span x-show="mode === 'price'">Enter the desired total price for this work area</span>
                            <span x-show="mode === 'profit'">Enter the desired profit percentage (e.g., 25 for 25%)</span>
                        </p>
                    </div>

                    {{-- Distribution Method --}}
                    <div class="mb-5">
                        <label class="mb-2 block text-sm font-medium text-gray-700">Distribution Method</label>
                        <div class="space-y-2">
                            <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-3 cursor-pointer hover:bg-gray-50 transition"
                                   :class="distributionMethod === 'proportional' ? 'border-brand-500 bg-brand-50' : ''">
                                <input type="radio" 
                                       name="distribution_method" 
                                       value="proportional" 
                                       x-model="distributionMethod"
                                       class="mt-0.5 text-brand-600 focus:ring-brand-500">
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900">Proportional Distribution</div>
                                    <div class="text-xs text-gray-600">Spread the change equally across all items</div>
                                </div>
                            </label>
                            <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-3 cursor-pointer hover:bg-gray-50 transition"
                                   :class="distributionMethod === 'line_item' ? 'border-brand-500 bg-brand-50' : ''">
                                <input type="radio" 
                                       name="distribution_method" 
                                       value="line_item" 
                                       x-model="distributionMethod"
                                       class="mt-0.5 text-brand-600 focus:ring-brand-500">
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900">Single Adjustment Line</div>
                                    <div class="text-xs text-gray-600">Add as separate line item adjustment</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Calculated Preview --}}
                    <div class="mb-5 rounded-lg border border-blue-200 bg-blue-50 p-4" x-show="targetValue > 0">
                        <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-blue-700">Preview</h4>
                        <div class="space-y-1.5">
                            <template x-if="mode === 'price'">
                                <div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-blue-700">New Total:</span>
                                        <span class="font-semibold text-blue-900" x-text="'$' + Number(targetValue).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-blue-700">New Profit %:</span>
                                        <span class="font-semibold text-blue-900" x-text="calculateNewProfit().toFixed(1) + '%'"></span>
                                    </div>
                                </div>
                            </template>
                            <template x-if="mode === 'profit'">
                                <div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-blue-700">New Profit %:</span>
                                        <span class="font-semibold text-blue-900" x-text="Number(targetValue).toFixed(1) + '%'"></span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-blue-700">New Total:</span>
                                        <span class="font-semibold text-blue-900" x-text="'$' + calculateNewPrice().toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                                    </div>
                                </div>
                            </template>
                            <div class="flex justify-between text-sm pt-1.5 border-t border-blue-200">
                                <span class="text-blue-700">Adjustment:</span>
                                <span class="font-semibold" :class="getAdjustment() >= 0 ? 'text-green-700' : 'text-red-700'" x-text="(getAdjustment() >= 0 ? '+' : '') + '$' + Math.abs(getAdjustment()).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Warning --}}
                    <div class="mb-5 rounded-lg border-l-4 border-yellow-400 bg-yellow-50 p-3">
                        <div class="flex gap-2">
                            <svg class="h-5 w-5 flex-shrink-0 text-yellow-600" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <div class="text-xs text-yellow-800">
                                <p class="font-semibold mb-1">This will override catalog pricing</p>
                                <p>Item prices will be adjusted. You can clear this override later to return to catalog pricing.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-end gap-3">
                        <button type="button" 
                                @click="closeModal()"
                                class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 transition">
                            Cancel
                        </button>
                        <button type="submit"
                                :disabled="loading || !targetValue || targetValue <= 0"
                                class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition">
                            <span x-show="!loading">Apply Custom Pricing</span>
                            <span x-show="loading" class="flex items-center gap-2">
                                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Applying...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function customPricingModal() {
    return {
        show: false,
        mode: 'price', // 'price' or 'profit'
        areaId: null,
        areaName: '',
        estimateId: null,
        currentTotal: 0,
        currentCost: 0,
        currentProfit: 0,
        targetValue: '',
        distributionMethod: 'proportional',
        loading: false,

        openModal(data) {
            this.mode = data.mode || 'price';
            this.areaId = data.areaId;
            this.areaName = data.areaName || 'Work Area';
            this.estimateId = data.estimateId;
            this.currentTotal = parseFloat(data.currentTotal) || 0;
            this.currentCost = parseFloat(data.currentCost) || 0;
            this.currentProfit = this.currentTotal > 0 
                ? ((this.currentTotal - this.currentCost) / this.currentTotal) * 100 
                : 0;
            this.targetValue = '';
            this.distributionMethod = 'proportional';
            this.loading = false;
            this.show = true;
        },

        closeModal() {
            this.show = false;
            setTimeout(() => {
                this.mode = 'price';
                this.areaId = null;
                this.areaName = '';
                this.estimateId = null;
                this.targetValue = '';
            }, 300);
        },

        calculateNewProfit() {
            if (!this.targetValue || this.targetValue <= 0 || this.currentCost === 0) return 0;
            return ((this.targetValue - this.currentCost) / this.targetValue) * 100;
        },

        calculateNewPrice() {
            if (!this.targetValue || this.targetValue >= 100 || this.currentCost === 0) return 0;
            const profitDecimal = this.targetValue / 100;
            return this.currentCost / (1 - profitDecimal);
        },

        getAdjustment() {
            if (this.mode === 'price') {
                return this.targetValue - this.currentTotal;
            } else {
                return this.calculateNewPrice() - this.currentTotal;
            }
        },

        async submitOverride() {
            if (!this.targetValue || this.targetValue <= 0) return;
            
            this.loading = true;
            
            try {
                const endpoint = this.mode === 'price'
                    ? `/estimates/${this.estimateId}/areas/${this.areaId}/custom-price`
                    : `/estimates/${this.estimateId}/areas/${this.areaId}/custom-profit`;
                
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        value: this.targetValue,
                        method: this.distributionMethod,
                    }),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Failed to apply custom pricing');
                }

                // Success - reload the page to show updated pricing
                window.location.reload();
            } catch (error) {
                console.error('Error applying custom pricing:', error);
                alert(error.message || 'An error occurred. Please try again.');
                this.loading = false;
            }
        },
    };
}
</script>
@endpush
