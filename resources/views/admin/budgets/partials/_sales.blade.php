                <!-- SALES BUDGET -->
                <section x-data="salesEditor($root)" x-show="section==='Sales Budget'" x-cloak>
                    <h2 class="text-2xl font-semibold text-brand-900 mb-5 flex items-center gap-2">
                        <svg class="h-5 w-5 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><rect x="7" y="13" width="3" height="5"/><rect x="12" y="9" width="3" height="9"/><rect x="17" y="5" width="3" height="13"/></svg>
                        <span>SALES BUDGET</span>

                    </h2>
                    <div class="rounded-[32px] border border-brand-100/80 bg-white/95 shadow-sm p-5 space-y-5">
                        <!-- Graphics Row -->
                        <div class="grid md:grid-cols-3 gap-4 mb-4">
                            <!-- Pie: Divisional Sales -->
                            <x-panel-card title="Divisional Sales">
                                <x-slot:icon>
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10a8.1 8.1 0 0 1-.9 3.8L12 12V3a9 9 0 1 1 9 9z"/></svg>
                                </x-slot:icon>
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
                                        <div x-show="divisionSegments().length === 0" class="text-brand-400">No data</div>
                                    </div>
                                </div>
                            </x-panel-card>
                            <!-- Prev vs Forecast -->
                            <x-panel-card title="Prev vs Forecast">
                                <x-slot:icon>
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 13h3v5H7zM12 9h3v9h-3zM17 5h3v13h-3z"/></svg>
                                </x-slot:icon>
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between text-xs">
                                        <span>Previous</span>
                                        <span x-text="formatMoney(prevTotal())"></span>
                                    </div>
                                    <div class="h-2 rounded bg-brand-100 overflow-hidden">
                                        <div class="h-2 bg-brand-500" :style="{ width: barWidth(prevTotal()) }"></div>
                                    </div>
                                    <div class="flex items-center justify-between text-xs">
                                        <span>Forecast</span>
                                        <span x-text="formatMoney(forecastTotal())"></span>
                                    </div>
                                    <div class="h-2 rounded bg-brand-200 overflow-hidden">
                                        <div class="h-2 bg-brand-600" :style="{ width: barWidth(forecastTotal()) }"></div>
                                    </div>
                                </div>
                            </x-panel-card>
                            <!-- Change over Previous -->
                            <x-panel-card title="Change over Previous">
                                <x-slot:icon>
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 17l4-4 3 3 5-5"/></svg>
                                </x-slot:icon>
                                <div class="flex items-center gap-4">
                                    <div class="relative h-28 w-28 rounded-full" :style="{ backgroundImage: changeRing() }">
                                        <div class="absolute inset-4 bg-white rounded-full flex items-center justify-center text-lg font-semibold">
                                            <span x-text="(changePercent() >= 0 ? '+' : '') + changePercent().toFixed(1) + '%' "></span>
                                        </div>
                                    </div>
                                    <div class="text-xs text-brand-600">
                                        <div class="font-semibold" x-text="(changePercent() >= 0 ? 'Increase' : 'Decrease')"></div>
                                        <div>Total Prev: <span class="font-semibold" x-text="formatMoney(prevTotal())"></span></div>
                                        <div>Total Forecast: <span class="font-semibold" x-text="formatMoney(forecastTotal())"></span></div>
                                    </div>
                                </div>
                            </x-panel-card>
                        </div>
                        <!-- Header Row -->
                        <div class="hidden md:grid grid-cols-12 gap-3 text-xs font-medium text-brand-500 border-b pb-2">
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
                                    <label class="md:hidden block text-xs text-brand-400">Acct. ID</label>
                                    <input type="text" class="form-input w-full" x-model="row.account_id" :name="'inputs[sales][rows]['+idx+'][account_id]'" placeholder="e.g., 4001">
                                </div>
                                <!-- Division -->
                                <div class="col-span-12 md:col-span-2">
                                    <label class="md:hidden block text-xs text-brand-400">Division</label>
                                    <input type="text" class="form-input w-full" x-model="row.division" :name="'inputs[sales][rows]['+idx+'][division]'" placeholder="e.g., Maintenance">
                                </div>
                                <!-- Previous $ -->
                                <div class="col-span-6 md:col-span-2">
                                    <label class="md:hidden block text-xs text-brand-400">Previous $</label>
                                    <input type="number" step="0.01" min="0" inputmode="decimal" class="form-input w-full" x-model="row.previous" :name="'inputs[sales][rows]['+idx+'][previous]'" placeholder="0.00">
                                </div>
                                <!-- Forecast $ -->
                                <div class="col-span-6 md:col-span-2">
                                    <label class="md:hidden block text-xs text-brand-400">Forecast $</label>
                                    <input type="number" step="0.01" min="0" inputmode="decimal" class="form-input w-full" x-model="row.forecast" :name="'inputs[sales][rows]['+idx+'][forecast]'" placeholder="0.00">
                                </div>
                                <!-- % Diff -->
                                <div class="col-span-6 md:col-span-1">
                                    <label class="md:hidden block text-xs text-brand-400">% Diff</label>
                                    <input type="text" class="form-input w-full bg-brand-50/70" :value="computeDiff(row)" readonly tabindex="-1">
                                </div>
                                <!-- Comments -->
                                <div class="col-span-6 md:col-span-2">
                                    <label class="md:hidden block text-xs text-brand-400">Comments</label>
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
