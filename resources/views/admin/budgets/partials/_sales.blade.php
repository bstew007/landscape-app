                <!-- SALES BUDGET -->
                <section x-data="salesEditor()" x-show="section==='Sales Budget'" x-cloak>
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
