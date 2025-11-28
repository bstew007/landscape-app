<div class="space-y-6" x-data="{ 
    printTemplate: 'full-detail',
    showPreview: false,
    selectedPOs: []
}">
    
    <!-- Estimate Print Options -->
    <section class="bg-white rounded-lg shadow overflow-hidden">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <svg class="h-5 w-5 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Estimate Print Options
            </h2>
            <p class="text-sm text-gray-600 mt-1">Choose how to print or export this estimate</p>
        </div>
        
        <div class="px-6 py-6 space-y-4">
            <!-- Print Template Selection -->
            <div class="space-y-3">
                <label class="flex items-start gap-3 p-4 border-2 rounded-lg cursor-pointer transition hover:border-brand-300" 
                       :class="{ 'border-brand-500 bg-brand-50': printTemplate === 'full-detail', 'border-gray-200': printTemplate !== 'full-detail' }">
                    <input type="radio" name="print_template" value="full-detail" class="mt-1" x-model="printTemplate">
                    <div class="flex-1">
                        <div class="font-medium text-gray-900">Full Detail</div>
                        <div class="text-sm text-gray-600">All items with quantities, unit prices, and totals. Includes work area subtotals.</div>
                    </div>
                </label>
                
                <label class="flex items-start gap-3 p-4 border-2 rounded-lg cursor-pointer transition hover:border-brand-300"
                       :class="{ 'border-brand-500 bg-brand-50': printTemplate === 'proposal', 'border-gray-200': printTemplate !== 'proposal' }">
                    <input type="radio" name="print_template" value="proposal" class="mt-1" x-model="printTemplate">
                    <div class="flex-1">
                        <div class="font-medium text-gray-900">Proposal (Materials Only, No Pricing)</div>
                        <div class="text-sm text-gray-600">Material items with quantities only. Pricing shown at work area level. Labor excluded.</div>
                    </div>
                </label>
                
                <label class="flex items-start gap-3 p-4 border-2 rounded-lg cursor-pointer transition hover:border-brand-300"
                       :class="{ 'border-brand-500 bg-brand-50': printTemplate === 'materials-only', 'border-gray-200': printTemplate !== 'materials-only' }">
                    <input type="radio" name="print_template" value="materials-only" class="mt-1" x-model="printTemplate">
                    <div class="flex-1">
                        <div class="font-medium text-gray-900">Materials Only</div>
                        <div class="text-sm text-gray-600">Material items with quantities and totals. Labor items excluded.</div>
                    </div>
                </label>
                
                <label class="flex items-start gap-3 p-4 border-2 rounded-lg cursor-pointer transition hover:border-brand-300"
                       :class="{ 'border-brand-500 bg-brand-50': printTemplate === 'labor-only', 'border-gray-200': printTemplate !== 'labor-only' }">
                    <input type="radio" name="print_template" value="labor-only" class="mt-1" x-model="printTemplate">
                    <div class="flex-1">
                        <div class="font-medium text-gray-900">Labor Only</div>
                        <div class="text-sm text-gray-600">Labor items with hours and totals. Material items excluded.</div>
                    </div>
                </label>
                
                <label class="flex items-start gap-3 p-4 border-2 rounded-lg cursor-pointer transition hover:border-brand-300"
                       :class="{ 'border-brand-500 bg-brand-50': printTemplate === 'summary', 'border-gray-200': printTemplate !== 'summary' }">
                    <input type="radio" name="print_template" value="summary" class="mt-1" x-model="printTemplate">
                    <div class="flex-1">
                        <div class="font-medium text-gray-900">Summary Only</div>
                        <div class="text-sm text-gray-600">Work area descriptions with totals. No detailed line items.</div>
                    </div>
                </label>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center gap-3 pt-4 border-t">
                <a :href="`{{ route('estimates.print', $estimate) }}?template=${printTemplate}`" 
                   target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700 transition font-medium">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print / Preview
                </a>
                
                <a :href="`{{ route('estimates.print', $estimate) }}?template=${printTemplate}&download=1`"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition font-medium">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download PDF
                </a>
            </div>
        </div>
    </section>

    <!-- Purchase Orders Section -->
    <section class="bg-white rounded-lg shadow overflow-hidden">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <svg class="h-5 w-5 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Purchase Orders
            </h2>
            <p class="text-sm text-gray-600 mt-1">Generate material purchase orders for suppliers</p>
        </div>
        
        <div class="px-6 py-6 space-y-4">
            <!-- Generate PO Button -->
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-700">Generate purchase orders from estimate materials, grouped by supplier</p>
                <form method="POST" action="{{ route('estimates.generate-purchase-orders', $estimate) }}">
                    @csrf
                    <input type="hidden" name="replace_existing" value="1">
                    <button type="submit" 
                            class="inline-flex items-center gap-2 px-4 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700 transition font-medium">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Generate Purchase Orders
                    </button>
                </form>
            </div>

            @php
                $purchaseOrders = $estimate->purchaseOrders()->with(['supplier', 'items'])->orderBy('po_number')->get();
            @endphp

            @if($purchaseOrders->isEmpty())
                <!-- No POs Message -->
                <div class="text-center py-8 border-2 border-dashed border-gray-200 rounded-lg bg-gray-50">
                    <svg class="mx-auto h-12 w-12 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="mt-3 text-sm font-medium text-gray-900">No Purchase Orders</h3>
                    <p class="mt-1 text-sm text-gray-500">Click "Generate Purchase Orders" to create POs from materials</p>
                </div>
            @else
                <!-- POs List -->
                <div class="space-y-3">
                    @foreach($purchaseOrders as $po)
                        <div class="border border-gray-200 rounded-lg p-4 hover:border-brand-300 transition">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3">
                                        <h3 class="font-semibold text-gray-900">{{ $po->po_number }}</h3>
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                            {{ $po->status === 'draft' ? 'bg-gray-100 text-gray-700' : '' }}
                                            {{ $po->status === 'sent' ? 'bg-blue-100 text-blue-700' : '' }}
                                            {{ $po->status === 'received' ? 'bg-green-100 text-green-700' : '' }}
                                            {{ $po->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}">
                                            {{ ucfirst($po->status) }}
                                        </span>
                                    </div>
                                    <div class="mt-1 text-sm text-gray-600">
                                        <span class="font-medium">Supplier:</span> 
                                        {{ $po->supplier ? $po->supplier->display_name : 'No Supplier Assigned' }}
                                    </div>
                                    <div class="mt-1 text-sm text-gray-600">
                                        <span class="font-medium">Items:</span> {{ $po->items->count() }} 
                                        <span class="mx-2">•</span>
                                        <span class="font-medium">Total:</span> ${{ number_format($po->total_amount, 2) }}
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <label class="flex items-center">
                                        <input type="checkbox" 
                                               class="form-checkbox text-brand-600 rounded"
                                               x-model="selectedPOs"
                                               :value="{{ $po->id }}">
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mt-3 flex flex-wrap items-center gap-2 pt-3 border-t border-gray-100">
                                <a href="{{ route('purchase-orders.print', $po) }}" 
                                   target="_blank"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                    </svg>
                                    Print
                                </a>
                                
                                <a href="{{ route('purchase-orders.print', ['purchaseOrder' => $po, 'download' => 1]) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    PDF
                                </a>
                                
                                <form method="POST" action="{{ route('purchase-orders.destroy', $po) }}" class="inline" 
                                      onsubmit="return confirm('Delete this purchase order?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-red-700 bg-white border border-red-300 rounded-lg hover:bg-red-50">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Bulk Actions -->
                <div class="flex items-center gap-3 pt-4 border-t" x-show="selectedPOs.length > 0">
                    <span class="text-sm text-gray-600" x-text="`${selectedPOs.length} selected`"></span>
                    <form method="POST" action="{{ route('purchase-orders.print-batch') }}" class="inline">
                        @csrf
                        <template x-for="id in selectedPOs" :key="id">
                            <input type="hidden" name="po_ids[]" :value="id">
                        </template>
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Print Selected
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </section>

    <!-- Reports Section -->
    <section class="bg-white rounded-lg shadow overflow-hidden">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <svg class="h-5 w-5 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Reports & Analysis
            </h2>
            <p class="text-sm text-gray-600 mt-1">View detailed cost and profit analysis reports</p>
        </div>
        
        <div class="px-6 py-6">
            <div class="grid md:grid-cols-2 gap-4">
                <!-- Cost Analysis Report -->
                <div class="border border-gray-200 rounded-lg p-4 hover:border-brand-300 transition">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1">
                            <h3 class="font-medium text-gray-900">Cost Analysis Report</h3>
                            <p class="text-sm text-gray-600 mt-1">Detailed breakdown of costs, pricing, and profit margins</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium bg-amber-100 text-amber-800 rounded">Soon</span>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <button disabled class="text-sm text-gray-400 cursor-not-allowed">View</button>
                        <button disabled class="text-sm text-gray-400 cursor-not-allowed">Print</button>
                    </div>
                </div>
                
                <!-- Labor Hours Summary -->
                <div class="border border-gray-200 rounded-lg p-4 hover:border-brand-300 transition">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1">
                            <h3 class="font-medium text-gray-900">Labor Hours Summary</h3>
                            <p class="text-sm text-gray-600 mt-1">Total labor hours by work area and category</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium bg-amber-100 text-amber-800 rounded">Soon</span>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <button disabled class="text-sm text-gray-400 cursor-not-allowed">View</button>
                        <button disabled class="text-sm text-gray-400 cursor-not-allowed">Print</button>
                    </div>
                </div>
                
                <!-- Material Requirements -->
                <div class="border border-gray-200 rounded-lg p-4 hover:border-brand-300 transition">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1">
                            <h3 class="font-medium text-gray-900">Material Requirements</h3>
                            <p class="text-sm text-gray-600 mt-1">Complete materials list with quantities and costs</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium bg-amber-100 text-amber-800 rounded">Soon</span>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <button disabled class="text-sm text-gray-400 cursor-not-allowed">View</button>
                        <button disabled class="text-sm text-gray-400 cursor-not-allowed">Print</button>
                    </div>
                </div>
                
                <!-- Profit Margin Analysis -->
                <div class="border border-gray-200 rounded-lg p-4 hover:border-brand-300 transition">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1">
                            <h3 class="font-medium text-gray-900">Profit Margin Analysis</h3>
                            <p class="text-sm text-gray-600 mt-1">Gross and net profit analysis by work area</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium bg-amber-100 text-amber-800 rounded">Soon</span>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <button disabled class="text-sm text-gray-400 cursor-not-allowed">View</button>
                        <button disabled class="text-sm text-gray-400 cursor-not-allowed">Print</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- QuickBooks Integration Section -->
    <section class="bg-white rounded-lg shadow overflow-hidden">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <svg class="h-5 w-5 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                QuickBooks Integration
            </h2>
            <p class="text-sm text-gray-600 mt-1">Sync estimates and purchase orders to QuickBooks Online</p>
        </div>
        
        <div class="px-6 py-6">
            <!-- Coming Soon Message -->
            <div class="text-center py-8 border-2 border-dashed border-gray-200 rounded-lg bg-gray-50">
                <svg class="mx-auto h-12 w-12 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                <h3 class="mt-3 text-sm font-medium text-gray-900">QuickBooks Sync</h3>
                <p class="mt-1 text-sm text-gray-500">Coming in Phase 3</p>
                <p class="mt-1 text-xs text-gray-400">Sync estimates and POs directly to QuickBooks Online</p>
            </div>
        </div>
    </section>

</div>
