                <!-- PROFIT / LOSS -->
                <section x-show="section==='Profit/Loss'" x-cloak>
                    <h2 class="text-lg font-semibold mb-3">Profit / Loss</h2>
                    <div class="space-y-4">
                        <!-- Block 1: Sales Revenue -->
                        <x-panel-card title="Sales Revenue">
                            <x-slot:icon>
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><rect x="7" y="13" width="3" height="5"/><rect x="12" y="9" width="3" height="9"/><rect x="17" y="5" width="3" height="13"/></svg>
                            </x-slot:icon>
                            <div class="space-y-2 text-sm">
                                <template x-for="(row, idx) in salesRows" :key="'sr'+idx">
                                    <div class="flex items-center justify-between border-b pb-1 last:border-b-0 last:pb-0">
                                        <div class="text-gray-700" x-text="(row.division || row.account_id || ('Line ' + (idx+1)))"></div>
                                        <div class="font-semibold tabular-nums" x-text="formatMoney(parseFloat(row.forecast)||0)"></div>
                                    </div>
                                </template>
                                <div x-show="salesRows.length===0" class="text-gray-500">No projected income items added.</div>
                                <div class="flex items-center justify-between pt-2 mt-1 border-t">
                                    <div class="font-semibold">Total Income</div>
                                    <div class="font-bold" x-text="formatMoney(forecastTotal())"></div>
                                </div>
                            </div>
                        </x-panel-card>

                        <!-- Block 2: Profit -->
                        <x-panel-card title="Profit">
                            <x-slot:icon>
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 17l4-4 3 3 5-5"/></svg>
                            </x-slot:icon>
                            <div class="space-y-3 text-sm">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="font-medium text-gray-800">Gross Profit</div>
                                        <div class="text-xs text-gray-600">Sales - Job Costs</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold tabular-nums" x-text="formatMoney(grossProfit())"></div>
                                        <div class="text-xs text-gray-600" x-text="grossProfitPct().toFixed(1) + '% of Sales'"></div>
                                    </div>
                                </div>
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="font-medium text-gray-800">Net Income (Profit)</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold tabular-nums" x-text="formatMoney(netIncome())"></div>
                                        <div class="text-xs text-gray-600" x-text="netIncomePct().toFixed(1) + '% of Sales'"></div>
                                    </div>
                                </div>
                            </div>
                        </x-panel-card>
                    </div>
                </section>
