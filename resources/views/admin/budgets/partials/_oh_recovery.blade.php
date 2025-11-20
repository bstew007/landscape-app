                <!-- OH RECOVERY -->
                <section x-show="section==='OH Recovery'" x-cloak>
                    <h2 class="text-lg font-semibold mb-3">Overhead Recovery</h2>
                    <div class="grid md:grid-cols-3 gap-4">
                        <!-- Method description -->
                        <x-panel-card title="Field Labor Hour Overhead Recovery">
                            <x-slot:icon>
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h18v4H3z"/><path d="M8 7v13"/><path d="M16 7v13"/></svg>
                            </x-slot:icon>
                            <div class="text-sm text-gray-700">
                                The field labor hour method recovers all your overhead in your labor rates. It divides your forecast overhead by your forecast field labor hours to get a markup per hour.
                            </div>
                        </x-panel-card>

                        <!-- Inputs and totals -->
                        <x-panel-card title="Forecasts">
                            <x-slot:icon>
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M7 7h10M7 11h10M7 15h10"/></svg>
                            </x-slot:icon>
                            <div class="grid grid-cols-2 gap-x-3 gap-y-2 text-sm">
                                <div class="text-gray-600">Forecast Overhead Costs</div>
                                <div class="text-right font-semibold" x-text="formatMoney(overheadCurrentTotal())"></div>
                                <div class="text-gray-600">Forecast Labor Hours</div>
                                <div class="text-right font-semibold" x-text="totalHours().toLocaleString()"></div>
                                <div class="col-span-2 pt-2 mt-1 border-t flex items-center justify-between">
                                    <div class="font-semibold text-gray-900">Field Labor Hour Markup</div>
                                    <div class="text-right font-bold text-lg tabular-nums" x-text="(function(){ const hrs = totalHours(); const v = hrs ? (overheadCurrentTotal()/hrs) : 0; return formatMoney(v) + '/hr'; })()"></div>
                                </div>
                            </div>
                        </x-panel-card>

                        <!-- Activation -->
                        <x-panel-card title="Activation">
                            <x-slot:icon>
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </x-slot:icon>
                            <div x-data="{ active: {{ old('inputs.oh_recovery.labor_hour.activated', data_get($budget->inputs ?? [], 'oh_recovery.labor_hour.activated', false)) ? 'true' : 'false' }} }" class="flex items-center justify-between">
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="hidden" name="inputs[oh_recovery][labor_hour][activated]" value="0">
                                    <input type="checkbox" class="form-checkbox" x-model="active" name="inputs[oh_recovery][labor_hour][activated]" value="1">
                                    <span x-text="active ? 'Activated' : 'Activate'"></span>
                                </label>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold" :class="active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700'" x-text="active ? 'Active' : 'Inactive'"></span>
                            </div>
                        </x-panel-card>
                    </div>

                    <!-- Revenue-based Overhead Recovery -->
                    <div class="grid md:grid-cols-3 gap-4 mt-4">
                        <!-- Method description -->
                        <x-panel-card title="Revenue-based Overhead Recovery">
                            <x-slot:icon>
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 13h3v5H7zM12 9h3v9h-3zM17 5h3v13h-3z"/></svg>
                            </x-slot:icon>
                            <div class="text-sm text-gray-700">
                                The revenue method recovers all your overhead as a percent of sales. It divides your forecast overhead by your forecast revenue to get a markup percentage.
                            </div>
                        </x-panel-card>

                        <!-- Inputs and totals -->
                        <x-panel-card title="Forecasts">
                            <x-slot:icon>
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M7 7h10M7 11h10M7 15h10"/></svg>
                            </x-slot:icon>
                            <div class="grid grid-cols-2 gap-x-3 gap-y-2 text-sm">
                                <div class="text-gray-600">Forecast Overhead Costs</div>
                                <div class="text-right font-semibold" x-text="formatMoney(overheadCurrentTotal())"></div>
                                <div class="text-gray-600">Forecast Revenue</div>
                                <div class="text-right font-semibold" x-text="formatMoney(forecastTotal())"></div>
                                <div class="col-span-2 pt-2 mt-1 border-t flex items-center justify-between">
                                    <div class="font-semibold text-gray-900">Revenue Markup</div>
                                    <div class="text-right font-bold text-lg tabular-nums" x-text="(function(){ const rev = forecastTotal(); const v = rev ? ((overheadCurrentTotal()/Math.abs(rev))*100) : 0; return v.toFixed(2) + '%'; })()"></div>
                                </div>
                            </div>
                        </x-panel-card>

                        <!-- Activation -->
                        <x-panel-card title="Activation">
                            <x-slot:icon>
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </x-slot:icon>
                            <div x-data="{ active: {{ old('inputs.oh_recovery.revenue.activated', data_get($budget->inputs ?? [], 'oh_recovery.revenue.activated', false)) ? 'true' : 'false' }} }" class="flex items-center justify-between">
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="hidden" name="inputs[oh_recovery][revenue][activated]" value="0">
                                    <input type="checkbox" class="form-checkbox" x-model="active" name="inputs[oh_recovery][revenue][activated]" value="1">
                                    <span x-text="active ? 'Activated' : 'Activate'"></span>
                                </label>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold" :class="active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700'" x-text="active ? 'Active' : 'Inactive'"></span>
                            </div>
                        </x-panel-card>
                    </div>

                    <!-- Dual-base Overhead Recovery -->
                    <div class="grid md:grid-cols-3 gap-4 mt-4">
                        <!-- Method description -->
                        <x-panel-card title="Dual-base Overhead Recovery">
                            <x-slot:icon>
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 13h3v5H7zM12 9h3v9h-3zM17 5h3v13h-3z"/></svg>
                            </x-slot:icon>
                            <div class="text-sm text-gray-700">
                                Dual-base recovery splits overhead between labor-hours and revenue. Set the labor-based share (%). We’ll calculate a per-hour markup for the labor share and a revenue % markup for the remainder.
                            </div>
                        </x-panel-card>

                        <!-- Inputs and totals with split -->
                        <x-panel-card title="Forecasts & Split">
                            <x-slot:icon>
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M7 7h10M7 11h10M7 15h10"/></svg>
                            </x-slot:icon>
                            <div x-data="{ pct: {{ old('inputs.oh_recovery.dual.labor_share_pct', data_get($budget->inputs ?? [], 'oh_recovery.dual.labor_share_pct', 50)) }} }" class="space-y-2 text-sm">
                                <div class="grid grid-cols-2 gap-x-3 gap-y-2">
                                    <div class="text-gray-600">Forecast Overhead Costs</div>
                                    <div class="text-right font-semibold" x-text="formatMoney($root.overheadCurrentTotal())"></div>
                                    <div class="text-gray-600">Forecast Labor Hours</div>
                                    <div class="text-right font-semibold" x-text="$root.totalHours().toLocaleString()"></div>
                                    <div class="text-gray-600">Forecast Revenue</div>
                                    <div class="text-right font-semibold" x-text="formatMoney($root.forecastTotal())"></div>
                                </div>
                                <div class="grid grid-cols-2 gap-x-3 gap-y-2">
                                    <label class="text-gray-700 font-medium">Labor-based share (%)</label>
                                    <div class="text-right">
                                        <input type="number" min="0" max="100" step="1" class="form-input w-28 text-right" x-model.number="pct" name="inputs[oh_recovery][dual][labor_share_pct]">
                                    </div>
                                </div>
                                <div class="pt-2 mt-1 border-t">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <div class="font-semibold text-gray-900">Labor Markup (per hour)</div>
                                            <div class="text-xs text-gray-600">(Labor share / Labor hours)</div>
                                        </div>
                                        <div class="text-right font-bold text-lg tabular-nums" x-text="(function(){ const oh=$root.overheadCurrentTotal(); const hrs=$root.totalHours(); const laborShare = oh * (Math.max(0, Math.min(100, pct))/100); const v = hrs ? (laborShare/hrs) : 0; return formatMoney(v) + '/hr'; })()"></div>
                                    </div>
                                    <div class="flex items-start justify-between mt-2">
                                        <div>
                                            <div class="font-semibold text-gray-900">Revenue Markup (%)</div>
                                            <div class="text-xs text-gray-600">(Revenue share / Revenue)</div>
                                        </div>
                                        <div class="text-right font-bold text-lg tabular-nums" x-text="(function(){ const oh=$root.overheadCurrentTotal(); const rev=$root.forecastTotal(); const laborShare = oh * (Math.max(0, Math.min(100, pct))/100); const revShare = oh - laborShare; const v = rev ? ((revShare/Math.abs(rev))*100) : 0; return v.toFixed(2) + '%'; })()"></div>
                                    </div>
                                </div>
                            </div>
                        </x-panel-card>

                        <!-- Activation -->
                        <x-panel-card title="Activation">
                            <x-slot:icon>
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </x-slot:icon>
                            <div x-data="{ active: {{ old('inputs.oh_recovery.dual.activated', data_get($budget->inputs ?? [], 'oh_recovery.dual.activated', false)) ? 'true' : 'false' }} }" class="flex items-center justify-between">
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="hidden" name="inputs[oh_recovery][dual][activated]" value="0">
                                    <input type="checkbox" class="form-checkbox" x-model="active" name="inputs[oh_recovery][dual][activated]" value="1">
                                    <span x-text="active ? 'Activated' : 'Activate'"></span>
                                </label>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold" :class="active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700'" x-text="active ? 'Active' : 'Inactive'"></span>
                            </div>
                        </x-panel-card>
                    </div>
                </section>
