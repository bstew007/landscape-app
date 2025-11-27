                <!-- SUBCONTRACTING -->
                <section x-show="section==='Subcontracting'" x-cloak>
                    <h2 class="text-2xl font-semibold text-brand-900 mb-5 flex items-center gap-2">Subcontracting</h2>
                    <div class="rounded-[32px] border border-brand-100/80 bg-white/95 shadow-sm p-5 space-y-5">
                        <!-- Graphics Row -->
                        <div class="grid md:grid-cols-3 gap-4 mb-4">
                            <!-- Subcontracting Summary -->
                            <div class="rounded-2xl border border-brand-100/70 bg-white shadow-sm p-4 relative md:col-span-2">
                                <div class="text-xs uppercase tracking-wide text-brand-500 mb-2">Subcontracting Summary</div>
                                <div class="absolute top-2 right-2 text-brand-500"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M7 7h10M7 11h10M7 15h10"/></svg></div>
                                <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-sm">
                                    <div class="text-brand-500">Previous Total</div>
                                    <div class="text-right font-semibold" x-text="formatMoney(subcPrevTotal())"></div>
                                                                        <div class="text-brand-500">Previous Ratio</div>
                                    <div class="text-right font-semibold" x-text="subcPrevRatio().toFixed(1) + '%'"></div>

                                    <div class="text-brand-500">Current Total</div>
                                    <div class="text-right font-semibold" x-text="formatMoney(subcCurrentTotal())"></div>
                                                                        <div class="text-brand-500">Current Ratio</div>
                                    <div class="text-right font-semibold" x-text="subcRatio().toFixed(1) + '%'"></div>

                                </div>
                            </div>
                            <!-- Subcontracting Ratio -->
                            <div class="rounded-2xl border border-brand-100/70 bg-white shadow-sm p-4 relative">
                                <div class="text-xs uppercase tracking-wide text-brand-500 mb-2">Subcontracting Ratio</div>
                                <div class="absolute top-2 right-2 text-brand-500"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg></div>
                                <div class="space-y-2">
                                    <div class="flex items-start justify-between gap-3 mb-2">
                                        <div class="flex-1">
                                            <div class="text-xs uppercase text-brand-400">Your Ratio</div>
                                                                                        <div class="text-3xl font-bold" x-text="subcRatio().toFixed(1) + '%'"></div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Header Row -->
                        <div class="hidden md:grid grid-cols-12 gap-3 text-xs font-medium text-brand-500 border-b pb-2">
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
                                    <label class="md:hidden block text-xs text-brand-400">Acct. ID</label>
                                    <input type="text" class="form-input w-full" x-model="row.account_id" :name="'inputs[subcontracting][rows]['+idx+'][account_id]'" placeholder="e.g., 6001">
                                </div>
                                <div class="col-span-12 md:col-span-3">
                                    <label class="md:hidden block text-xs text-brand-400">Subcontracting Expense</label>
                                    <input type="text" class="form-input w-full" x-model="row.expense" :name="'inputs[subcontracting][rows]['+idx+'][expense]'" placeholder="e.g., Tree work">
                                </div>
                                <div class="col-span-6 md:col-span-2">
                                    <label class="md:hidden block text-xs text-brand-400">Previous $</label>
                                    <input type="number" step="0.01" min="0" inputmode="decimal" class="form-input w-full" x-model="row.previous" :name="'inputs[subcontracting][rows]['+idx+'][previous]'" placeholder="0.00">
                                </div>
                                <div class="col-span-6 md:col-span-2">
                                    <label class="md:hidden block text-xs text-brand-400">Current $</label>
                                    <input type="number" step="0.01" min="0" inputmode="decimal" class="form-input w-full" x-model="row.current" :name="'inputs[subcontracting][rows]['+idx+'][current]'" placeholder="0.00">
                                </div>
                                <div class="col-span-6 md:col-span-2">
                                    <label class="md:hidden block text-xs text-brand-400">Comments</label>
                                    <input type="text" class="form-input w-full" x-model="row.comments" :name="'inputs[subcontracting][rows]['+idx+'][comments]'" placeholder="Notes">
                                </div>
                                <div class="col-span-12 md:col-span-1 flex md:justify-end">
                                    <button type="button"
                                            class="inline-flex items-center justify-center h-8 w-8 rounded-full border border-brand-100 bg-brand-50 text-brand-500 hover:text-rose-600 hover:border-rose-200 hover:bg-rose-50 transition"
                                            aria-label="Delete row"
                                            @click="removeSubcontractingRow(idx)">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="18" y1="6" x2="6" y2="18" />
                                            <line x1="6" y1="6" x2="18" y2="18" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                        <div class="pt-3">
                            <x-brand-button type="button" size="sm" variant="ghost" @click="addSubcontractingRow()">+ New</x-brand-button>
                        </div>
                    </div>
                </section>
