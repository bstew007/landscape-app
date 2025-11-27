                <!-- EQUIPMENT -->
                <section x-show="section==='Equipment'" x-cloak>
                    <h2 class="text-2xl font-semibold text-brand-900 mb-5 flex items-center gap-2">Equipment</h2>
                    <div class="rounded-[32px] border border-brand-100/80 bg-white/95 shadow-sm p-5 space-y-5">
                        <!-- Graphics Row -->
                        <div class="grid md:grid-cols-3 gap-4 mb-4">
                            <!-- General Expenses -->
                            <x-panel-card title="General Expenses">
                                <x-slot:icon>
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2C8 6 6 9 6 12a6 6 0 0 0 12 0c0-3-2-6-6-10z"/></svg>
                                </x-slot:icon>
                                <div class="space-y-1.5">
                                    <x-compact-input-row label="Forecast Fuel">
                                        <input type="number" step="0.01" min="0" class="form-input w-24 md:w-28 text-sm text-right" x-model.number="equipmentGeneral.fuel" name="inputs[equipment][general][fuel]" placeholder="0.00">
                                    </x-compact-input-row>
                                    <x-compact-input-row label="Forecast Repairs">
                                        <input type="number" step="0.01" min="0" class="form-input w-24 md:w-28 text-sm text-right" x-model.number="equipmentGeneral.repairs" name="inputs[equipment][general][repairs]" placeholder="0.00">
                                    </x-compact-input-row>
                                    <x-compact-input-row label="Insurance + Misc">
                                        <input type="number" step="0.01" min="0" class="form-input w-24 md:w-28 text-sm text-right" x-model.number="equipmentGeneral.insurance_misc" name="inputs[equipment][general][insurance_misc]" placeholder="0.00">
                                    </x-compact-input-row>
                                </div>
                            </x-panel-card>
                            <!-- Equip Summary -->
                            <x-panel-card title="Equip Summary">
                                <x-slot:icon>
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M7 7h10M7 11h10M7 15h10"/></svg>
                                </x-slot:icon>
                                <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-sm">
                                    <div class="text-brand-500">Equipment Expenses (list total)</div>
                                    <div class="text-right font-semibold" x-text="formatMoney(equipmentDisplayedListTotal())"></div>
                                    <div class="text-brand-500">Other (General Expenses total)</div>
                                    <div class="text-right font-semibold" x-text="formatMoney(generalExpensesTotal())"></div>
                                    <div class="text-brand-500">Total Equip Expenses</div>
                                    <div class="text-right font-semibold" x-text="formatMoney(equipmentExpensesTotal())"></div>
                                    <div class="text-brand-500">Plus Equip Rentals</div>
                                    <div>
                                        <input type="number" step="0.01" min="0" class="form-input w-full text-right" x-model.number="equipmentRentals" name="inputs[equipment][rentals]" placeholder="0.00">
                                    </div>
                                </div>
                            </x-panel-card>
                              <!-- Equipment Ratio -->
  <x-panel-card title="Equipment Ratio">
      <x-slot:icon>
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>
      </x-slot:icon>
      <div class="space-y-2">
          <div class="flex items-start justify-between gap-3 mb-2">
              <div class="flex-1">
                  <div class="text-xs uppercase text-brand-400">Your Ratio</div>
                                    <div class="text-3xl font-bold" x-text="equipmentRatio().toFixed(1) + '%' "></div>

              </div>
              <div class="flex-1 text-right">
                  <div class="text-xs uppercase text-brand-400">Industry Avg</div>
                  <div class="text-3xl font-bold text-brand-900" x-text="(equipmentIndustryAvgRatio||0).toFixed(1) + '%'"></div>
              </div>
          </div>
          <div>
              <label class="block text-xs font-medium text-brand-600">Industry Avg (%)</label>
              <input type="number" step="0.1" min="0" class="form-input w-full" x-model.number="equipmentIndustryAvgRatio" name="inputs[equipment][industry_avg_ratio]" placeholder="13.7">
          </div>
      </div>
  </x-panel-card>
                        </div>
                        <div class="hidden md:grid grid-cols-12 gap-2 text-xs font-medium text-brand-500 border-b pb-2">
                            <div class="col-span-2">Equipment Type</div>
                            <div class="col-span-1">Qty</div>
                            <div class="col-span-2">Class</div>
                            <div class="col-span-4">Description</div>
                            <div class="col-span-2">Cost/Yr/Ea</div>
                            <div class="col-span-1 text-right">Cost/Yr/Ea</div>
                        </div>
                        <template x-for="(row, idx) in equipmentRows" :key="'eq'+idx">
                            <div class="grid grid-cols-12 gap-2 items-center py-2 border-b">
                                <div class="col-span-12 md:col-span-2">
                                    <label class="md:hidden block text-xs text-brand-400">Equipment Type</label>
                                    <input type="text" class="form-input w-full" x-model="row.type" :name="'inputs[equipment][rows]['+idx+'][type]'" placeholder="e.g., Truck">
                                </div>
                                <div class="col-span-6 md:col-span-1">
                                    <label class="md:hidden block text-xs text-brand-400">Qty</label>
                                    <input type="number" step="1" min="0" class="form-input w-full" x-model="row.qty" :name="'inputs[equipment][rows]['+idx+'][qty]'" placeholder="0">
                                </div>
                                <div class="col-span-6 md:col-span-2">
                                    <label class="md:hidden block text-xs text-brand-400">Class</label>
                                    <select class="form-select w-full" x-model="row.class" :name="'inputs[equipment][rows]['+idx+'][class]'" @change="if(row.class==='Owned' && !row.owned){row.owned={ replacement_value:'', fees:'', years:'', salvage_value:'', months_per_year:'', division_months:'', interest_rate_pct:'' }}; if(row.class==='Leased' && !row.leased){row.leased={ monthly_payment:'', payments_per_year:'', months_per_year:'', division_months:'' }}; if(row.class==='Group' && !row.group){row.group={ items: [] }}}" >
                                        <option>Custom</option>
                                        <option>Owned</option>
                                        <option>Leased</option>
                                        <option>Group</option>
                                    </select>
                                    <!-- Always submit months fields so values persist regardless of panel state -->
                                    <template x-if="row.class==='Owned'">
                                        <div class="hidden">
                                            <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][owned][months_per_year]'" :value="row.owned.months_per_year">
                                            <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][owned][division_months]'" :value="row.owned.division_months">
                                        </div>
                                    </template>
                                    <template x-if="row.class==='Leased'">
                                        <div class="hidden">
                                            <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][leased][payments_per_year]'" :value="row.leased.payments_per_year">
                                            <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][leased][months_per_year]'" :value="row.leased.months_per_year">
                                            <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][leased][division_months]'" :value="row.leased.division_months">
                                        </div>
                                    </template>
                                </div>
                                <div class="col-span-12 md:col-span-4">
                                    <label class="md:hidden block text-xs text-brand-400">Description</label>
                                    <input type="text" class="form-input w-full" x-model="row.description" :name="'inputs[equipment][rows]['+idx+'][description]'" placeholder="Notes">
                                </div>
                                <div class="col-span-6 md:col-span-2">
                                    <label class="md:hidden block text-xs text-brand-400">Cost/Yr/Ea</label>
                                    <template x-if="row.class==='Owned'">
                                        <div class="relative">
                                            <input type="text" class="form-input w-full bg-green-50 pr-10" :value="(computeOwnedAnnual(row) || 0).toFixed(2)" readonly tabindex="-1" placeholder="0.00">
                                            <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][cost_per_year]'" :value="computeOwnedAnnual(row)">
                                            <!-- Ensure owned fields persist even when collapsed -->
                                            <template x-if="!row._ownedOpen">
                                                <div>
                                                    <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][owned][replacement_value]'" :value="row.owned.replacement_value">
                                                    <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][owned][fees]'" :value="row.owned.fees">
                                                    <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][owned][years]'" :value="row.owned.years">
                                                    <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][owned][salvage_value]'
                                                           :value="row.owned.salvage_value">
                                                    <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][owned][months_per_year]'" :value="row.owned.months_per_year">
                                                    <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][owned][division_months]'" :value="row.owned.division_months">
                                                    <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][owned][interest_rate_pct]'" :value="row.owned.interest_rate_pct">
                                                </div>
                                            </template>
                                            <button type="button" class="absolute inset-y-0 right-1 my-auto h-7 w-7 rounded border bg-white/80 hover:bg-white flex items-center justify-center shadow-sm"
                                                    @click.prevent="row._ownedOpen = !row._ownedOpen"
                                                    :aria-expanded="row._ownedOpen ? 'true' : 'false'"
                                                    title="Toggle calculator">
                                                <svg viewBox="0 0 24 24" class="h-4 w-4 text-brand-600" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                                                    <path d="M7 7h10M7 11h4M13 11h4M7 15h4M13 15h4"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="row.class==='Leased'">
                                        <div class="relative">
                                            <input type="text" class="form-input w-full bg-green-50 pr-10" :value="(computeLeasedAnnual(row) || 0).toFixed(2)" readonly tabindex="-1" placeholder="0.00">
                                            <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][cost_per_year]'" :value="computeLeasedAnnual(row)">
                                            <!-- Ensure leased fields persist even when collapsed -->
                                            <template x-if="!row._ownedOpen">
                                                <div>
                                                    <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][leased][monthly_payment]'" :value="row.leased.monthly_payment">
                                                    <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][leased][payments_per_year]'" :value="row.leased.payments_per_year">
                                                    <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][leased][months_per_year]'" :value="row.leased.months_per_year">
                                                    <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][leased][division_months]'" :value="row.leased.division_months">
                                                </div>
                                            </template>
                                            <button type="button" class="absolute inset-y-0 right-1 my-auto h-7 w-7 rounded border bg-white/80 hover:bg-white flex items-center justify-center shadow-sm"
                                                    @click.prevent="row._ownedOpen = !row._ownedOpen"
                                                    :aria-expanded="row._ownedOpen ? 'true' : 'false'"
                                                    title="Toggle calculator">
                                                <svg viewBox="0 0 24 24" class="h-4 w-4 text-brand-600" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                                                    <path d="M7 7h10M7 11h4M13 11h4M7 15h4M13 15h4"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="row.class==='Group'">
                                        <div class="relative">
                                            <input type="text" class="form-input w-full bg-green-50 pr-10" :value="(computeGroupAnnual(row) || 0).toFixed(2)" readonly tabindex="-1" placeholder="0.00">
                                            <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][cost_per_year]'" :value="computeGroupAnnual(row)">
                                            <!-- Ensure group items persist even when collapsed -->
                                            <template x-if="!row._ownedOpen">
                                                <div>
                                                    <template x-for="(gi, gidx) in (row.group?.items || [])" :key="'g'+gidx">
                                                        <div>
                                                            <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][group][items]['+gidx+'][name]'" :value="row.group.items[gidx].name">
                                                            <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][group][items]['+gidx+'][qty]'" :value="row.group.items[gidx].qty">
                                                            <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][group][items]['+gidx+'][purchase_price]'" :value="row.group.items[gidx].purchase_price">
                                                            <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][group][items]['+gidx+'][resale_value]'" :value="row.group.items[gidx].resale_value">
                                                            <input type="hidden" :name="'inputs[equipment][rows]['+idx+'][group][items]['+gidx+'][years]'" :value="row.group.items[gidx].years">
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                            <button type="button" class="absolute inset-y-0 right-1 my-auto h-7 w-7 rounded border bg-white/80 hover:bg-white flex items-center justify-center shadow-sm"
                                                    @click.prevent="row._ownedOpen = !row._ownedOpen"
                                                    :aria-expanded="row._ownedOpen ? 'true' : 'false'"
                                                    title="Toggle calculator">
                                                <svg viewBox="0 0 24 24" class="h-4 w-4 text-brand-600" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                                                    <path d="M7 7h10M7 11h4M13 11h4M7 15h4M13 15h4"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="row.class!=='Owned' && row.class!=='Leased' && row.class!=='Group'">
                                        <input type="number" step="0.01" min="0" class="form-input w-full" x-model="row.cost_per_year" :name="'inputs[equipment][rows]['+idx+'][cost_per_year]'" placeholder="0.00">
                                    </template>
                                </div>
                                <!-- Owned details panel (full width under Cost/Yr/Ea) -->
                                <div class="col-span-12" x-show="row.class==='Owned' && row._ownedOpen">
                                    <div class="mt-2 bg-green-50 border border-green-200 rounded p-3 space-y-3">
                                        <div class="text-sm uppercase tracking-wide text-green-700 pb-2 mb-2 border-b-2 border-green-700 border-double">Owned – Cost/Year/Ea Breakdown</div>
                                        <div class="space-y-1.5">
                                            <div class="flex items-center justify-between py-1.5">
                                                <label class="text-sm font-medium text-brand-900 pr-3">Replacement value</label>
                                                <input type="number" step="0.01" min="0" class="form-input w-28 md:w-36 text-sm" x-model="row.owned.replacement_value" :name="'inputs[equipment][rows]['+idx+'][owned][replacement_value]'">
                                            </div>
                                            <div class="flex items-center justify-between py-1.5">
                                                <label class="text-sm font-medium text-brand-900 pr-3">Additional fees/taxes/admin</label>
                                                <input type="number" step="0.01" min="0" class="form-input w-28 md:w-36 text-sm" x-model="row.owned.fees" :name="'inputs[equipment][rows]['+idx+'][owned][fees]'">
                                            </div>
                                            <div class="flex items-center justify-between py-1.5">
                                                <label class="text-sm font-medium text-brand-900 pr-3">Useful life (years)</label>
                                                <input type="number" step="0.1" min="0.1" class="form-input w-28 md:w-36 text-sm" x-model="row.owned.years" :name="'inputs[equipment][rows]['+idx+'][owned][years]'">
                                            </div>
                                            <div class="flex items-center justify-between py-1.5">
                                                <label class="text-sm font-medium text-brand-900 pr-3">End-of-life value</label>
                                                <input type="number" step="0.01" min="0" class="form-input w-28 md:w-36 text-sm" x-model="row.owned.salvage_value" :name="'inputs[equipment][rows]['+idx+'][owned][salvage_value]'">
                                            </div>
                                            <div class="flex items-center justify-between py-1.5">
                                                <label class="text-sm font-medium text-brand-900 pr-3">Months used per year (1–12)</label>
                                                <select class="form-select w-28 md:w-36 text-sm" x-model="row.owned.months_per_year" x-init="$el.value = (row.owned.months_per_year || '')" :name="'inputs[equipment][rows]['+idx+'][owned][months_per_year]'">
                                                    <option value="" disabled x-bind:selected="!row.owned.months_per_year">Select…</option>
                                                    <template x-for="m in 12" :key="m">
                                                        <option :value="String(m)" :selected="String(row.owned.months_per_year) === String(m)" x-text="m"></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <div class="flex items-center justify-between py-1.5">
                                                <label class="text-sm font-medium text-brand-900 pr-3">Division months (1–12)</label>
                                                <select class="form-select w-28 md:w-36 text-sm" x-model="row.owned.division_months" x-init="$el.value = (row.owned.division_months || '')" :name="'inputs[equipment][rows]['+idx+'][owned][division_months]'">
                                                    <option value="" disabled x-bind:selected="!row.owned.division_months">Select…</option>
                                                    
                                                    <template x-for="m in 12" :key="'d'+m">
                                                        <option :value="String(m)" :selected="String(row.owned.division_months) === String(m)" x-text="m"></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <div class="flex items-center justify-between py-1.5 mt-2 pt-2 border-t-2 border-green-700 border-double">
                                                <label class="text-sm font-medium text-brand-900 pr-3">Inflation/Interest rate (%)</label>
                                                <input type="number" step="0.01" min="0" max="100" class="form-input w-28 md:w-36 text-sm" x-model="row.owned.interest_rate_pct" :name="'inputs[equipment][rows]['+idx+'][owned][interest_rate_pct]'">
                                            </div>
                                        </div>
                                        <div class="grid md:grid-cols-5 gap-3 text-sm mt-2">
                                            <div>
                                                <div class="text-brand-500">Annual cost/equipment</div>
                                                <div class="text-base font-semibold" x-text="formatMoney(computeOwnedAnnual(row))"></div>
                                            </div>
                                            <div>
                                                <div class="text-brand-500">Monthly (calendar)</div>
                                                <div class="text-base font-semibold" x-text="formatMoney(computeOwnedMonthlyCalendar(row))"></div>
                                            </div>
                                            <div>
                                                <div class="text-brand-500">Monthly (active)</div>
                                                <div class="text-base font-semibold" x-text="formatMoney(computeOwnedMonthlyActive(row))"></div>
                                            </div>
                                            <div>
                                                <div class="text-brand-500">Division Annual</div>
                                                <div class="text-base font-semibold" x-text="formatMoney(computeDivisionAnnual(row))"></div>
                                            </div>
                                            <div>
                                                <div class="text-brand-500">Division Monthly (active)</div>
                                                <div class="text-base font-semibold" x-text="formatMoney(computeDivisionMonthlyActive(row))"></div>
                                            </div>
                                        </div>
                                        <div class="mt-2 pt-2 border-t flex items-center justify-between" x-show="computeOwnedInterestLifeCompounded(row) > 0">
                                            <div class="text-sm text-brand-600">Interest/Inflation value over the life of the equipment:</div>
                                            <div class="text-sm font-semibold" x-text="formatMoney(computeOwnedInterestLifeCompounded(row))"></div>
                                        </div>
                                        <div class="text-xs text-brand-500">Annual cost = (replacement value + fees + interest over life - end-of-life value) / useful life.</div>
                                    </div>
                                </div>
                                <!-- Group details panel (full width under Cost/Yr/Ea) -->
                                <div class="col-span-12" x-show="row.class==='Group' && row._ownedOpen">
                                    <div class="mt-2 bg-green-50 border border-green-200 rounded p-3 space-y-3">
                                        <div class="flex items-center justify-between">
                                            <div class="text-sm uppercase tracking-wide text-green-700 pb-2 mb-2 border-b-2 border-green-700 border-double">Group – Cost/Year (Total) Breakdown</div>
                                            <div class="space-x-2">
                                                <x-brand-button type="button" size="sm" @click="addGroupItem(row)">Add</x-brand-button>
                                                <x-secondary-button type="button" size="sm" @click="row._ownedOpen=false">Cancel</x-secondary-button>
                                            </div>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="hidden md:grid grid-cols-12 gap-2 text-xs font-medium text-brand-500 border-b pb-2">
                                                <div class="col-span-3">Name</div>
                                                <div class="col-span-1">Qty</div>
                                                <div class="col-span-2">Purch. Price</div>
                                                <div class="col-span-2">Resale Value</div>
                                                <div class="col-span-2">Yrs/Life</div>
                                                <div class="col-span-2 text-right">Cost/Yr (Ea)</div>
                                            </div>
                                            <template x-for="(it, gi) in (row.group?.items || [])" :key="'gi'+gi">
                                                <div class="grid grid-cols-12 gap-2 items-center py-2 border-b">
                                                    <div class="col-span-12 md:col-span-3">
                                                        <label class="md:hidden block text-xs text-brand-400">Name</label>
                                                        <input type="text" class="form-input w-full" x-model="it.name" :name="'inputs[equipment][rows]['+idx+'][group][items]['+gi+'][name]'" placeholder="Item name">
                                                    </div>
                                                    <div class="col-span-6 md:col-span-1">
                                                        <label class="md:hidden block text-xs text-brand-400">Qty</label>
                                                        <input type="number" step="1" min="0" class="form-input w-full" x-model="it.qty" :name="'inputs[equipment][rows]['+idx+'][group][items]['+gi+'][qty]'" placeholder="0">
                                                    </div>
                                                    <div class="col-span-6 md:col-span-2">
                                                        <label class="md:hidden block text-xs text-brand-400">Purch. Price</label>
                                                        <input type="number" step="0.01" min="0" class="form-input w-full" x-model="it.purchase_price" :name="'inputs[equipment][rows]['+idx+'][group][items]['+gi+'][purchase_price]'" placeholder="0.00">
                                                    </div>
                                                    <div class="col-span-6 md:col-span-2">
                                                        <label class="md:hidden block text-xs text-brand-400">Resale Value</label>
                                                        <input type="number" step="0.01" min="0" class="form-input w-full" x-model="it.resale_value" :name="'inputs[equipment][rows]['+idx+'][group][items]['+gi+'][resale_value]'" placeholder="0.00">
                                                    </div>
                                                    <div class="col-span-6 md:col-span-2">
                                                        <label class="md:hidden block text-xs text-brand-400">Yrs/Life</label>
                                                        <input type="number" step="0.1" min="0.1" class="form-input w-full" x-model="it.years" :name="'inputs[equipment][rows]['+idx+'][group][items]['+gi+'][years]'" placeholder="0.0">
                                                    </div>
                                                    <div class="col-span-6 md:col-span-2 text-right font-semibold">
                                                        <span x-text="formatMoney(computeGroupItemAnnual(it))"></span>
                                                    </div>
                                                    <div class="col-span-12 md:col-span-12 md:text-right">
                                                        <button type="button"
                                                                class="inline-flex items-center justify-center h-8 w-8 rounded-full border border-brand-100 bg-brand-50 text-brand-500 hover:text-rose-600 hover:border-rose-200 hover:bg-rose-50 transition"
                                                                aria-label="Delete item"
                                                                @click="removeGroupItem(row, gi)">
                                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                <line x1="18" y1="6" x2="6" y2="18" />
                                                                <line x1="6" y1="6" x2="18" y2="18" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                            <div class="grid grid-cols-12 gap-2 items-center pt-2">
                                                <div class="col-span-8 text-right font-semibold">Annual ROI (Total)</div>
                                                <div class="col-span-4 text-right font-bold" x-text="formatMoney(computeGroupAnnual(row))"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Leased details panel (full width under Cost/Yr/Ea) -->
                                <div class="col-span-12" x-show="row.class==='Leased' && row._ownedOpen">
                                    <div class="mt-2 bg-green-50 border border-green-200 rounded p-3 space-y-3">
                                        <div class="text-sm uppercase tracking-wide text-green-700 pb-2 mb-2 border-b-2 border-green-700 border-double">Leased – Cost/Year/Ea Breakdown</div>
                                        <div class="space-y-1.5">
                                            <div class="flex items-center justify-between py-1.5">
                                                <label class="text-sm font-medium text-brand-900 pr-3">Enter the monthly payment, including tax</label>
                                                <input type="number" step="0.01" min="0" class="form-input w-28 md:w-36 text-sm" x-model="row.leased.monthly_payment" :name="'inputs[equipment][rows]['+idx+'][leased][monthly_payment]'">
                                            </div>
                                            <div class="flex items-center justify-between py-1.5">
                                                <label class="text-sm font-medium text-brand-900 pr-3">How many payments do you make per year</label>
                                                <select class="form-select w-28 md:w-36 text-sm" x-model="row.leased.payments_per_year" x-init="$el.value = (row.leased.payments_per_year || '')" :name="'inputs[equipment][rows]['+idx+'][leased][payments_per_year]'">
                                                    <option value="" disabled x-bind:selected="!row.leased.payments_per_year">Select…</option>
                                                    <template x-for="m in 12" :key="'lp'+m">
                                                        <option :value="String(m)" :selected="String(row.leased.payments_per_year) === String(m)" x-text="m"></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <div class="flex items-center justify-between py-1.5">
                                                <label class="text-sm font-medium text-brand-900 pr-3">Enter the number of months per year you use it</label>
                                                <select class="form-select w-28 md:w-36 text-sm" x-model="row.leased.months_per_year" x-init="$el.value = (row.leased.months_per_year || '')" :name="'inputs[equipment][rows]['+idx+'][leased][months_per_year]'">
                                                    <option value="" disabled x-bind:selected="!row.leased.months_per_year">Select…</option>
                                                    <template x-for="m in 12" :key="'lm'+m">
                                                        <option :value="String(m)" :selected="String(row.leased.months_per_year) === String(m)" x-text="m"></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <div class="flex items-center justify-between py-1.5">
                                                <label class="text-sm font-medium text-brand-900 pr-3">If this is a divisional budget, months this works in this division</label>
                                                <select class="form-select w-28 md:w-36 text-sm" x-model="row.leased.division_months" x-init="$el.value = (row.leased.division_months || '')" :name="'inputs[equipment][rows]['+idx+'][leased][division_months]'">
                                                    <option value="" disabled x-bind:selected="!row.leased.division_months">Select…</option>
                                                    <template x-for="m in 12" :key="'ld'+m">
                                                        <option :value="String(m)" :selected="String(row.leased.division_months) === String(m)" x-text="m"></option>
                                                    </template>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="grid md:grid-cols-5 gap-3 text-sm mt-2">
                                            <div>
                                                <div class="text-brand-500">Annual ROI</div>
                                                <div class="text-base font-semibold" x-text="formatMoney(computeLeasedAnnual(row))"></div>
                                            </div>
                                            <div>
                                                <div class="text-brand-500">Monthly ROI</div>
                                                <div class="text-base font-semibold" x-text="formatMoney(computeLeasedMonthlyCalendar(row))"></div>
                                            </div>
                                            <div>
                                                <div class="text-brand-500">Monthly (active)</div>
                                                <div class="text-base font-semibold" x-text="formatMoney(computeLeasedMonthlyActive(row))"></div>
                                            </div>
                                            <div>
                                                <div class="text-brand-500">Division Annual</div>
                                                <div class="text-base font-semibold" x-text="formatMoney(computeLeasedDivisionAnnual(row))"></div>
                                            </div>
                                            <div>
                                                <div class="text-brand-500">Division Monthly (active)</div>
                                                <div class="text-base font-semibold" x-text="formatMoney(computeLeasedDivisionMonthlyActive(row))"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-span-4 md:col-span-1 text-right font-semibold relative">
                                    <span class="inline-block mr-2" x-text="formatMoney(perUnitCost(row))"></span>
                                    <div class="inline-block relative" @keydown.escape.stop="row._menuOpen=false">
                                        <button type="button" class="h-6 w-6 inline-flex items-center justify-center rounded border bg-white hover:bg-brand-50/70 text-brand-600"
                                                @click.stop="row._menuOpen = !row._menuOpen"
                                                :aria-expanded="row._menuOpen ? 'true' : 'false'"
                                                title="Row actions">
                                            <svg viewBox="0 0 20 20" class="h-4 w-4" fill="currentColor"><path d="M10 3a2 2 0 110 4 2 2 0 010-4zm0 5a2 2 0 110 4 2 2 0 010-4zm0 5a2 2 0 110 4 2 2 0 010-4z"/></svg>
                                        </button>
                                        <div class="absolute right-0 mt-1 w-44 bg-white border rounded shadow z-10" x-show="row._menuOpen" x-cloak @click.outside="row._menuOpen=false">
                                            <button type="button" class="block w-full text-left px-3 py-1.5 text-sm hover:bg-brand-50/70"
                                                    @click="moveEquipmentToOverhead(idx); row._menuOpen=false">Move to Overhead</button>
                                            <button type="button" class="block w-full text-left px-3 py-1.5 text-sm text-red-600 hover:bg-red-50"
                                                    @click="removeEquipmentRow(idx); row._menuOpen=false">Delete</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <div class="pt-3 flex items-center justify-between">
                            <x-brand-button type="button" size="sm" variant="ghost" @click="addEquipmentRow()">+ New</x-brand-button>
                            <div class="text-sm text-brand-600" x-show="equipmentTotal() > 0"><span class="font-semibold">Total Equipment:</span> <span x-text="formatMoney(equipmentTotal())"></span></div>
                        </div>
                    </div>
                </section>
