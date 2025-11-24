                <!-- FIELD LABOR -->
                <section x-show="section==='Field Labor'" x-cloak>
                    <h2 class="text-2xl font-semibold text-brand-900 mb-5 flex items-center gap-2">Field Labor</h2>
                    <div class="rounded-[32px] border border-brand-100/80 bg-white/95 shadow-sm p-5 space-y-5">
                        <!-- Boxes Row -->
                        <div class="grid md:grid-cols-3 gap-4 mb-4">
                            <!-- Key Factors -->
                            <div class="rounded-2xl border border-brand-100/70 bg-white shadow-sm p-4">
                                <div class="text-xs uppercase tracking-wide text-brand-500 mb-2">Key Factors</div>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-medium text-brand-600">Labor Burden (%)</label>
                                        <input type="number" step="0.1" min="0" class="form-input w-full" x-model.number="burdenPct" name="inputs[labor][burden_pct]" placeholder="0.0">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-brand-600">Overtime Multiplier</label>
                                        <select class="form-select w-full" x-model.number="otMultiplier" name="inputs[labor][ot_multiplier]">
                                            <template x-for="opt in overtimeOptions()" :key="opt">
                                                <option :value="opt" x-text="opt.toFixed(2) + 'x'" :selected="Number(opt) === Number(otMultiplier)"></option>
                                            </template>
                                        </select>
                                        <input type="hidden" name="section" :value="section">
                                    </div>
                                </div>
                            </div>
                            <!-- Field Labor Summary -->
                            <div class="rounded-2xl border border-brand-100/70 bg-white shadow-sm p-4 relative">
                                <div class="text-xs uppercase tracking-wide text-brand-500 mb-2">Field Labor Summary</div>
                                <div class="absolute top-2 right-2 text-brand-500"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h18v4H3z"/><path d="M8 7v13"/><path d="M16 7v13"/></svg></div>
                                <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-sm">
                                    <div class="text-brand-500">Total Hrs</div>
                                    <div class="text-right font-semibold" x-text="(totalHours()).toLocaleString()"></div>
                                    <div class="text-brand-500">Total Wages</div>
                                    <div class="text-right font-semibold" x-text="formatMoney(totalWages())"></div>
                                    <div class="text-brand-500">Total Burden</div>
                                    <div class="text-right font-semibold" x-text="formatMoney(totalBurden())"></div>
                                    <div class="text-brand-500">Field Payroll</div>
                                    <div class="text-right font-semibold" x-text="formatMoney(fieldPayroll())"></div>
                                </div>
                            </div>
                            <!-- Field Labor Ratio -->
                            <div class="rounded-2xl border border-brand-100/70 bg-white shadow-sm p-4 relative">
                                <div class="text-xs uppercase tracking-wide text-brand-500 mb-2">Field Labor Ratio</div>
                                <div class="absolute top-2 right-2 text-brand-500"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19h16"/><path d="M8 15h8"/><path d="M10 11h4"/><path d="M12 7h0"/></svg></div>
                                <div class="space-y-2">
                                    <div class="flex items-start justify-between gap-3 mb-2">
                                        <div class="flex-1">
                                            <div class="text-xs uppercase text-brand-400">Your Ratio</div>
                                                                                        <div class="text-3xl font-bold" x-text="laborRatio().toFixed(1) + '%' "></div>
                                        </div>
                                        <div class="flex-1 text-right">
                                            <div class="text-xs uppercase text-brand-400">Industry Avg</div>
                                            <div class="text-3xl font-bold text-brand-900" x-text="(industryAvgRatio||0).toFixed(1) + '%'"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-brand-600">Industry Avg (%)</label>
                                        <input type="number" step="0.1" min="0" class="form-input w-full" x-model.number="industryAvgRatio" name="inputs[labor][industry_avg_ratio]" placeholder="26.6">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Tabs -->
                        <div class="inline-flex rounded-md border overflow-hidden mb-4">
                            <button type="button" class="px-3 py-1.5 text-sm" :class="{ 'bg-brand-100 text-brand-900' : laborTab==='hourly' }" @click="laborTab='hourly'">Hourly Field Staff</button>
                            <button type="button" class="px-3 py-1.5 text-sm border-l" :class="{ 'bg-brand-100 text-brand-900' : laborTab==='salary' }" @click="laborTab='salary'">Salary Field Staff</button>
                        </div>

                        <!-- Hourly Table -->
                        <div x-show="laborTab==='hourly'" class="space-y-2">
                            <div class="hidden md:grid grid-cols-12 gap-2 text-xs font-medium text-brand-500 border-b pb-2">
                                <div class="col-span-2">Employee Type</div>
                                <div class="col-span-1"># Staff</div>
                                <div class="col-span-2">Hrs/Yr (Ea)</div>
                                <div class="col-span-2">OT Hrs (Ea)</div>
                                <div class="col-span-2">Avg Wage</div>
                                <div class="col-span-2">Bonus</div>
                                <div class="col-span-1 text-right">Wages/Yr</div>
                            </div>
                            <template x-for="(row, idx) in hourlyRows" :key="'h'+idx">
                                <div class="grid grid-cols-12 gap-2 items-center py-2 border-b">
                                    <div class="col-span-12 md:col-span-2">
                                        <label class="md:hidden block text-xs text-brand-400">Employee Type</label>
                                        <input type="text" class="form-input w-full" x-model="row.type" :name="'inputs[labor][hourly][rows]['+idx+'][type]'" placeholder="e.g., Crew Lead">
                                    </div>
                                    <div class="col-span-6 md:col-span-1">
                                        <label class="md:hidden block text-xs text-brand-400"># Staff</label>
                                        <input type="number" step="1" min="0" class="form-input w-full" x-model="row.staff" :name="'inputs[labor][hourly][rows]['+idx+'][staff]'" placeholder="0">
                                    </div>
                                    <div class="col-span-6 md:col-span-2">
                                        <label class="md:hidden block text-xs text-brand-400">Hrs/Yr (Ea)</label>
                                        <input type="number" step="1" min="0" class="form-input w-full" x-model="row.hrs" :name="'inputs[labor][hourly][rows]['+idx+'][hrs]'" placeholder="2080">
                                    </div>
                                    <div class="col-span-6 md:col-span-2">
                                        <label class="md:hidden block text-xs text-brand-400">OT Hrs (Ea)</label>
                                        <input type="number" step="1" min="0" class="form-input w-full" x-model="row.ot_hrs" :name="'inputs[labor][hourly][rows]['+idx+'][ot_hrs]'" placeholder="0">
                                    </div>
                                    <div class="col-span-6 md:col-span-2">
                                        <label class="md:hidden block text-xs text-brand-400">Avg Wage</label>
                                        <input type="number" step="0.01" min="0" class="form-input w-full" x-model="row.avg_wage" :name="'inputs[labor][hourly][rows]['+idx+'][avg_wage]'" placeholder="0.00">
                                    </div>
                                    <div class="col-span-6 md:col-span-2">
                                        <label class="md:hidden block text-xs text-brand-400">Bonus</label>
                                        <input type="number" step="0.01" min="0" class="form-input w-full" x-model="row.bonus" :name="'inputs[labor][hourly][rows]['+idx+'][bonus]'" placeholder="0.00">
                                    </div>
                                    <div class="col-span-10 md:col-span-1 text-right font-semibold">
                                        <span x-text="formatMoney(wagesHourlyRow(row))"></span>
                                    </div>
                                    <div class="col-span-2 md:col-span-12 md:text-right">
                                        <x-danger-button size="sm" type="button" @click="removeHourlyRow(idx)">Delete</x-danger-button>
                                    </div>
                                </div>
                            </template>
                            <div class="pt-3">
                                <x-brand-button type="button" size="sm" variant="ghost" @click="addHourlyRow()">+ New</x-brand-button>
                            </div>
                        </div>

                        <!-- Salary Table -->
                        <div x-show="laborTab==='salary'" class="space-y-2">
                            <div class="hidden md:grid grid-cols-12 gap-2 text-xs font-medium text-brand-500 border-b pb-2">
                                <div class="col-span-3">Employee Type</div>
                                <div class="col-span-1"># Staff</div>
                                <div class="col-span-2">Ann Hrs (Ea)</div>
                                <div class="col-span-2">Ann Salary (Ea)</div>
                                <div class="col-span-2">Bonus</div>
                                <div class="col-span-2 text-right">Ann. Wages</div>
                            </div>
                            <template x-for="(row, idx) in salaryRows" :key="'s'+idx">
                                <div class="grid grid-cols-12 gap-2 items-center py-2 border-b">
                                    <div class="col-span-12 md:col-span-3">
                                        <label class="md:hidden block text-xs text-brand-400">Employee Type</label>
                                        <input type="text" class="form-input w-full" x-model="row.type" :name="'inputs[labor][salary][rows]['+idx+'][type]" placeholder="e.g., Supervisor">
                                    </div>
                                    <div class="col-span-6 md:col-span-1">
                                        <label class="md:hidden block text-xs text-brand-400"># Staff</label>
                                        <input type="number" step="1" min="0" class="form-input w-full" x-model="row.staff" :name="'inputs[labor][salary][rows]['+idx+'][staff]'" placeholder="0">
                                    </div>
                                    <div class="col-span-6 md:col-span-2">
                                        <label class="md:hidden block text-xs text-brand-400">Ann Hrs (Ea)</label>
                                        <input type="number" step="1" min="0" class="form-input w-full" x-model="row.ann_hrs" :name="'inputs[labor][salary][rows]['+idx+'][ann_hrs]'" placeholder="2080">
                                    </div>
                                    <div class="col-span-6 md:col-span-2">
                                        <label class="md:hidden block text-xs text-brand-400">Ann Salary (Ea)</label>
                                        <input type="number" step="0.01" min="0" class="form-input w-full" x-model="row.ann_salary" :name="'inputs[labor][salary][rows]['+idx+'][ann_salary]'" placeholder="0.00">
                                    </div>
                                    <div class="col-span-6 md:col-span-2">
                                        <label class="md:hidden block text-xs text-brand-400">Bonus</label>
                                        <input type="number" step="0.01" min="0" class="form-input w-full" x-model="row.bonus" :name="'inputs[labor][salary][rows]['+idx+'][bonus]'" placeholder="0.00">
                                    </div>
                                    <div class="col-span-10 md:col-span-2 text-right font-semibold">
                                        <span x-text="formatMoney(wagesSalaryRow(row))"></span>
                                    </div>
                                    <div class="col-span-2 md:col-span-12 md:text-right">
                                        <x-danger-button size="sm" type="button" @click="removeSalaryRow(idx)">Delete</x-danger-button>
                                    </div>
                                </div>
                            </template>
                            <div class="pt-3">
                                <x-brand-button type="button" size="sm" variant="ghost" @click="addSalaryRow()">+ New</x-brand-button>
                            </div>
                        </div>
                    </div>
                </section>
