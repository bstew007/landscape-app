                <!-- PROFIT / LOSS -->
                <section x-show="section==='Profit/Loss'" x-cloak>
                    <h2 class="text-2xl font-semibold text-brand-900 mb-5 flex items-center gap-2">
                        <span>Profit / Loss</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-brand-100 text-brand-800" x-text="formatMoney(netIncome())"></span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" :class="netIncome() >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" x-text="netIncomePct().toFixed(1) + '%'"></span>
                    </h2>
                    <div class="space-y-4">
                        <!-- Top Row: Sales (left) and COGS (right) -->
                        <div class="grid md:grid-cols-2 gap-4">
                            <!-- Block 1: Sales Revenue -->
                            <x-panel-card title="Sales Revenue">
                                <x-slot:icon>
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><rect x="7" y="13" width="3" height="5"/><rect x="12" y="9" width="3" height="9"/><rect x="17" y="5" width="3" height="13"/></svg>
                                </x-slot:icon>
                                <div class="space-y-2 text-sm">
                                    <template x-for="(row, idx) in salesRows" :key="'sr'+idx">
                                        <div class="flex items-center justify-between border-b pb-1 last:border-b-0 last:pb-0">
                                            <div class="text-brand-600" x-text="(row.division || row.account_id || ('Line ' + (idx+1)))"></div>
                                            <div class="font-semibold tabular-nums" x-text="formatMoney(parseFloat(row.forecast)||0)"></div>
                                        </div>
                                    </template>
                                    <div x-show="salesRows.length===0" class="text-brand-400">No projected income items added.</div>
                                    <div class="flex items-center justify-between pt-2 mt-1 border-t">
                                        <div class="font-semibold">Total Income</div>
                                        <div class="font-bold" x-text="formatMoney(forecastTotal())"></div>
                                    </div>
                                </div>
                        </x-panel-card>

                            <!-- Block 2: Cost of Goods Sold -->
                            <x-panel-card title="Cost of Goods Sold">
                                <x-slot:icon>
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M8 13h8M8 17h6"/></svg>
                                </x-slot:icon>
                                <div class="space-y-2 text-sm">
                                    <!-- Field Labor Wages -->
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <div class="font-medium text-brand-900">Field Labor Wages</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-semibold tabular-nums" x-text="formatMoney(fieldPayroll())"></div>
                                            <div class="text-xs text-brand-500" x-text="((forecastTotal() ? (fieldPayroll()/Math.abs(forecastTotal())*100) : 0).toFixed(1)) + '% of Income'"></div>
                                        </div>
                                    </div>
                                    <!-- Materials Cost -->
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <div class="font-medium text-brand-900">Materials Cost</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-semibold tabular-nums" x-text="formatMoney(materialsCurrentTotal())"></div>
                                            <div class="text-xs text-brand-500" x-text="((forecastTotal() ? (materialsCurrentTotal()/Math.abs(forecastTotal())*100) : 0).toFixed(1)) + '% of Income'"></div>
                                        </div>
                                    </div>
                                    <!-- Total COGS (Job Expenses) -->
                                    <div class="flex items-start justify-between pt-2 mt-1 border-t">
                                        <div>
                                            <div class="font-semibold text-brand-900">Total COGS (Job Expenses)</div>
                                        </div>
                                        <div class="text-right">
                                            <template x-init="$store=null"></template>
                                            <div class="font-bold tabular-nums" x-text="formatMoney((fieldPayroll()||0) + (materialsCurrentTotal()||0))"></div>
                                            <div class="text-xs text-brand-500" x-text="((forecastTotal() ? (((fieldPayroll()||0)+(materialsCurrentTotal()||0))/Math.abs(forecastTotal())*100) : 0).toFixed(1)) + '% of Income'"></div>
                                        </div>
                                    </div>
                                </div>
                            </x-panel-card>
                        </div>

                        <!-- Bottom Row: Profit (left) and Overhead (right) -->
                        <div class="grid md:grid-cols-2 gap-4">
                        <!-- Profit -->
                        <x-panel-card title="Profit">
                            <x-slot:icon>
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 17l4-4 3 3 5-5"/></svg>
                            </x-slot:icon>
                            <div class="space-y-3 text-sm">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="font-medium text-brand-900">Gross Profit</div>
                                        <div class="text-xs text-brand-500">Sales - Job Costs</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold tabular-nums" x-text="formatMoney(grossProfit())"></div>
                                        <div class="text-xs text-brand-500" x-text="grossProfitPct().toFixed(1) + '% of Sales'"></div>
                                    </div>
                                </div>
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="font-medium text-brand-900">Net Income (Profit)</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold tabular-nums" x-text="formatMoney(netIncome())"></div>
                                        <div class="text-xs text-brand-500" x-text="netIncomePct().toFixed(1) + '% of Sales'"></div>
                                    </div>
                                </div>
                            </div>
                        </x-panel-card>

                        <!-- Overhead -->
                        <x-panel-card title="Overhead">
                            <x-slot:icon>
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M7 7h10M7 11h10M7 15h10"/></svg>
                            </x-slot:icon>
                            <div class="space-y-2 text-sm">
                                <!-- Overhead Expenses -->
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="font-medium text-brand-900">Overhead Expenses</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold tabular-nums" x-text="formatMoney(overheadExpensesCurrentTotal())"></div>
                                        <div class="text-xs text-brand-500" x-text="((forecastTotal() ? (overheadExpensesCurrentTotal()/Math.abs(forecastTotal())*100) : 0).toFixed(1)) + '% of Income'"></div>
                                    </div>
                                </div>
                                <!-- Overhead Equipment -->
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="font-medium text-brand-900">Overhead Equipment</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold tabular-nums" x-text="formatMoney(overheadEquipmentTotal())"></div>
                                        <div class="text-xs text-brand-500" x-text="((forecastTotal() ? (overheadEquipmentTotal()/Math.abs(forecastTotal())*100) : 0).toFixed(1)) + '% of Income'"></div>
                                    </div>
                                </div>
                                <!-- Overhead Payroll -->
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="font-medium text-brand-900">Overhead Payroll</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold tabular-nums" x-text="formatMoney(overheadWagesForecastTotal())"></div>
                                        <div class="text-xs text-brand-500" x-text="((forecastTotal() ? (overheadWagesForecastTotal()/Math.abs(forecastTotal())*100) : 0).toFixed(1)) + '% of Income'"></div>
                                    </div>
                                </div>
                                <!-- Total of Overhead Expenses -->
                                <div class="flex items-start justify-between pt-2 mt-1 border-t">
                                    <div>
                                        <div class="font-semibold text-brand-900">Total of Overhead Expenses</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-bold tabular-nums" x-text="formatMoney(overheadCurrentTotal())"></div>
                                        <div class="text-xs text-brand-500" x-text="((forecastTotal() ? (overheadCurrentTotal()/Math.abs(forecastTotal())*100) : 0).toFixed(1)) + '% of Income'"></div>
                                    </div>
                                </div>
                            </div>
                        </x-panel-card>
                        </div>
                    </div>
                </section>
