                <!-- ANALYSIS -->
                <section x-show="section==='Analysis'" x-cloak>
                    <h2 class="text-2xl font-semibold text-brand-900 mb-5">Analysis</h2>
                    <div class="grid md:grid-cols-4 gap-4 text-sm">
                        <div class="rounded-2xl border border-brand-100/70 bg-white shadow-sm p-4">
                            <p class="text-brand-500">Direct Labor Cost</p>
                            <p class="font-semibold">${{ number_format(data_get($budget->outputs ?? [], 'labor.dlc', 0), 2) }}/hr</p>
                        </div>
                        <div class="rounded-2xl border border-brand-100/70 bg-white shadow-sm p-4">
                            <p class="text-brand-500">Overhead / Prod. Hour</p>
                            <p class="font-semibold">${{ number_format(data_get($budget->outputs ?? [], 'labor.ohr', 0), 2) }}/hr</p>
                        </div>
                        <div class="rounded-2xl border border-brand-100/70 bg-white shadow-sm p-4">
                            <p class="text-brand-500">Burdened Labor Cost</p>
                            <p class="font-semibold">${{ number_format(data_get($budget->outputs ?? [], 'labor.blc', 0), 2) }}/hr</p>
                        </div>
                        <div class="rounded-2xl border border-brand-100/70 bg-white shadow-sm p-4">
                            <p class="text-brand-500">Productive Hours (annual)</p>
                            <p class="font-semibold">{{ number_format(data_get($budget->outputs ?? [], 'labor.plh', 0), 0) }}</p>
                        </div>
                    </div>
                </section>
