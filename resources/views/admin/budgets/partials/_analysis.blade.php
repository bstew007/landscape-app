                <!-- ANALYSIS -->
                <section x-show="section==='Analysis'" x-cloak>
                    <h2 class="text-lg font-semibold mb-3">Analysis</h2>
                    <div class="grid md:grid-cols-4 gap-4 text-sm">
                        <div class="rounded border p-3">
                            <p class="text-gray-600">Direct Labor Cost</p>
                            <p class="font-semibold">${{ number_format(data_get($budget->outputs ?? [], 'labor.dlc', 0), 2) }}/hr</p>
                        </div>
                        <div class="rounded border p-3">
                            <p class="text-gray-600">Overhead / Prod. Hour</p>
                            <p class="font-semibold">${{ number_format(data_get($budget->outputs ?? [], 'labor.ohr', 0), 2) }}/hr</p>
                        </div>
                        <div class="rounded border p-3">
                            <p class="text-gray-600">Burdened Labor Cost</p>
                            <p class="font-semibold">${{ number_format(data_get($budget->outputs ?? [], 'labor.blc', 0), 2) }}/hr</p>
                        </div>
                        <div class="rounded border p-3">
                            <p class="text-gray-600">Productive Hours (annual)</p>
                            <p class="font-semibold">{{ number_format(data_get($budget->outputs ?? [], 'labor.plh', 0), 0) }}</p>
                        </div>
                    </div>
                </section>
