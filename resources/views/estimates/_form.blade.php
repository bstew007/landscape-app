<form id="estimateForm" action="{{ $route }}" method="POST" class="space-y-6 bg-white p-6 rounded shadow">
    @csrf
    @if ($method === 'PUT')
        @method('PUT')
    @endif

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Title</label>
            <input type="text" name="title" class="form-input w-full mt-1"
                   value="{{ old('title', $estimate->title ?? 'New Landscape Estimate') }}" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <select name="status" class="form-select w-full mt-1">
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(old('status', $estimate->status ?? 'draft') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Client</label>
            <select name="client_id" class="form-select w-full mt-1" required>
                <option value="">Select client</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}" @selected(old('client_id', $estimate->client_id ?? '') == $client->id)>
                        {{ $client->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Property</label>
            <select name="property_id" class="form-select w-full mt-1">
                <option value="">Select property</option>
                @foreach ($clients as $client)
                    @foreach ($client->properties as $property)
                        <option value="{{ $property->id }}" data-client-id="{{ $client->id }}" @selected(old('property_id', $estimate->property_id ?? '') == $property->id)>
                            {{ $client->name }} – {{ $property->name }}
                        </option>
                    @endforeach
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Site Visit (optional)</label>
            <select
                name="site_visit_id"
                class="form-select w-full mt-1"
                data-line-items-url="{{ route('site-visits.estimate-line-items', ['site_visit' => '__SITE_VISIT__']) }}"
            >
                <option value="">Link visit</option>
                @foreach ($siteVisits as $visit)
                    <option value="{{ $visit->id }}" data-client-id="{{ $visit->client_id }}" @selected(old('site_visit_id', $estimate->site_visit_id ?? '') == $visit->id)>
                        {{ optional($visit->client)->name }} – {{ optional($visit->visit_date)->format('M j, Y') }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Expires On</label>
            <input type="date" name="expires_at" class="form-input w-full mt-1"
                   value="{{ old('expires_at', optional($estimate->expires_at ?? null)->format('Y-m-d')) }}">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Total</label>
            <input type="number" step="0.01" name="total" class="form-input w-full mt-1"
                   value="{{ old('total', $estimate->total ?? '') }}" readonly>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Line Items</label>
        <div class="overflow-x-auto border rounded">
            <table class="w-full text-sm" id="line-items-table">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wide">
                <tr>
                    <th class="px-2 py-2 text-left">Description</th>
                    <th class="px-2 py-2 text-center">Qty</th>
                    <th class="px-2 py-2 text-center">Cost</th>
                    <th class="px-2 py-2 text-center">Margin (%)</th>
                    <th class="px-2 py-2 text-center">Price</th>
                    <th class="px-2 py-2 text-center">Total</th>
                    <th class="px-2 py-2"></th>
                </tr>
                </thead>
                <tbody>
                @php
                    $lineItems = old('line_items') ? json_decode(old('line_items'), true) : ($estimate->line_items ?? []);
                    if (! is_array($lineItems) || empty($lineItems)) {
                        $lineItems = [[
                            'label' => '',
                            'qty' => 1,
                            'cost' => 0,
                            'margin' => 15,
                        ]];
                    }
                @endphp
                @foreach ($lineItems as $index => $item)
                    @php
                        $qty = $item['qty'] ?? 1;
                        $cost = $item['cost'] ?? ($item['total'] ?? 0);
                        $margin = $item['margin'] ?? 15;
                        $price = $item['price'] ?? ($cost && $margin < 100 ? $cost / max(0.01, 1 - $margin / 100) : $cost);
                        $total = $item['total'] ?? ($price * $qty);
                    @endphp
                    <tr class="line-item-row">
                        <td class="px-2 py-1">
                            <input type="text" class="line-label form-input w-full" value="{{ $item['label'] ?? '' }}" />
                        </td>
                        <td class="px-2 py-1 text-center">
                            <input type="number" min="1" class="line-qty form-input w-full" value="{{ $qty }}" />
                        </td>
                        <td class="px-2 py-1 text-center">
                            <input type="number" step="0.01" class="line-cost form-input w-full" value="{{ $cost }}" />
                        </td>
                        <td class="px-2 py-1 text-center">
                            <input type="number" step="0.1" class="line-margin form-input w-full" value="{{ $margin }}" />
                        </td>
                        <td class="px-2 py-1 text-center">
                            <input type="number" step="0.01" class="line-price form-input w-full" value="{{ number_format($price, 2, '.', '') }}" readonly />
                        </td>
                        <td class="px-2 py-1 text-center">
                            <span class="line-total text-gray-700 font-semibold">{{ number_format($total, 2) }}</span>
                        </td>
                        <td class="px-2 py-1 text-center">
                            <button type="button" class="remove-line text-red-600 hover:text-red-900">×</button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <button type="button" id="add-line-item" class="text-sm text-blue-600 hover:text-blue-900 mt-2">+ Add line item</button>
        <input type="hidden" name="line_items" id="line_items_input">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Notes / Scope</label>
        <textarea name="notes" rows="4" class="form-textarea w-full mt-1">{{ old('notes', $estimate->notes ?? '') }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Terms & Conditions</label>
        <textarea name="terms" rows="4" class="form-textarea w-full mt-1">{{ old('terms', $estimate->terms ?? 'Prices valid for 30 days. 50% deposit due upon acceptance.') }}</textarea>
    </div>

    <div class="flex justify-end gap-2">
        <a href="{{ route('estimates.index') }}" class="px-4 py-2 rounded border border-gray-300 hover:bg-gray-50">Cancel</a>
        <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Save Estimate</button>
    </div>
</form>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tableBody = document.querySelector('#line-items-table tbody');
            const lineItemsInput = document.getElementById('line_items_input');
            const form = document.getElementById('estimateForm') || document.querySelector('form');
            const totalField = document.querySelector('input[name="total"]');
            const clientSelect = document.querySelector('select[name="client_id"]');
            const propertySelect = document.querySelector('select[name="property_id"]');
            const siteVisitSelect = document.querySelector('select[name="site_visit_id"]');
            const lineItemsEndpointTemplate = siteVisitSelect && siteVisitSelect.dataset
                ? siteVisitSelect.dataset.lineItemsUrl || null
                : null;

            if (!tableBody || !lineItemsInput || !form) {
                return;
            }

            const propertyOptions = propertySelect
                ? Array.from(propertySelect.options).map(option => ({
                    value: option.value,
                    text: option.textContent,
                    clientId: option.dataset.clientId || '',
                }))
                : [];

            const siteVisitOptions = siteVisitSelect
                ? Array.from(siteVisitSelect.options).map(option => ({
                    value: option.value,
                    text: option.textContent,
                    clientId: option.dataset.clientId || '',
                }))
                : [];

            function rebuildSelect(select, options, clientId, selectedValue) {
                const fragment = document.createDocumentFragment();
                options.forEach(option => {
                    const matches = !option.value || !clientId || option.clientId === clientId || option.value === selectedValue;
                    if (!matches) {
                        return;
                    }
                    const opt = document.createElement('option');
                    opt.value = option.value;
                    opt.textContent = option.text;
                    if (option.clientId) {
                        opt.dataset.clientId = option.clientId;
                    }
                    if (selectedValue && option.value === selectedValue) {
                        opt.selected = true;
                    }
                    fragment.appendChild(opt);
                });
                select.innerHTML = '';
                select.appendChild(fragment);
            }

            function filterClientRelatedSelects() {
                if (!clientSelect) {
                    return;
                }
                const clientId = clientSelect.value;

                if (propertySelect) {
                    const currentProperty = propertySelect.value;
                    const propertyValid = propertyOptions.some(opt => opt.value === currentProperty && opt.clientId === clientId);
                    rebuildSelect(propertySelect, propertyOptions, clientId, propertyValid ? currentProperty : '');
                }

                if (siteVisitSelect) {
                    const currentVisit = siteVisitSelect.value;
                    const visitValid = siteVisitOptions.some(opt => opt.value === currentVisit && opt.clientId === clientId);
                    rebuildSelect(siteVisitSelect, siteVisitOptions, clientId, visitValid ? currentVisit : '');
                }
            }

            function recalcRow(row) {
                const qty = parseFloat(row.querySelector('.line-qty').value) || 0;
                const cost = parseFloat(row.querySelector('.line-cost').value) || 0;
                const margin = parseFloat(row.querySelector('.line-margin').value) || 0;
                const priceField = row.querySelector('.line-price');
                const marginFraction = Math.min(Math.max(margin / 100, 0), 0.99);
                const price = marginFraction >= 0.99 ? cost : (cost / (1 - marginFraction)) || cost;
                const total = price * qty || 0;
                priceField.value = price.toFixed(2);
                row.querySelector('.line-total').textContent = total.toFixed(2);
                updateEstimateTotal();
            }

            function updateEstimateTotal() {
                if (!totalField) {
                    return;
                }
                const sum = Array.from(tableBody.querySelectorAll('.line-item-row')).reduce((carry, row) => {
                    const qty = parseFloat(row.querySelector('.line-qty').value) || 0;
                    const price = parseFloat(row.querySelector('.line-price').value) || 0;
                    return carry + (qty * price);
                }, 0);

                totalField.value = sum > 0 ? sum.toFixed(2) : '';
            }

            function serializeLineItems() {
                const payload = [];
                tableBody.querySelectorAll('.line-item-row').forEach(row => {
                    const label = row.querySelector('.line-label').value.trim();
                    const qty = parseFloat(row.querySelector('.line-qty').value) || 0;
                    const cost = parseFloat(row.querySelector('.line-cost').value) || 0;
                    const margin = parseFloat(row.querySelector('.line-margin').value) || 0;
                    const price = parseFloat(row.querySelector('.line-price').value) || 0;
                    const total = qty * price;
                    if (!label && qty === 0) {
                        return;
                    }
                    payload.push({ label, qty, cost, margin, price, total });
                });
                lineItemsInput.value = JSON.stringify(payload);
            }

            function addRow(prefill = null) {
                const row = document.createElement('tr');
                row.classList.add('line-item-row');
                row.innerHTML = 
                    <td class="px-2 py-1">
                        <input type="text" class="line-label form-input w-full" value="">
                    </td>
                    <td class="px-2 py-1 text-center">
                        <input type="number" min="1" class="line-qty form-input w-full" value="1">
                    </td>
                    <td class="px-2 py-1 text-center">
                        <input type="number" step="0.01" class="line-cost form-input w-full" value="0">
                    </td>
                    <td class="px-2 py-1 text-center">
                        <input type="number" step="0.1" class="line-margin form-input w-full" value="15">
                    </td>
                    <td class="px-2 py-1 text-center">
                        <input type="number" step="0.01" class="line-price form-input w-full" value="0" readonly>
                    </td>
                    <td class="px-2 py-1 text-center">
                        <span class="line-total text-gray-700 font-semibold">0.00</span>
                    </td>
                    <td class="px-2 py-1 text-center">
                        <button type="button" class="remove-line text-red-600 hover:text-red-900">A-</button>
                    </td>
                ;
                tableBody.appendChild(row);
                attachRowListeners(row);

                if (prefill) {
                    row.querySelector('.line-label').value = prefill.label ?? '';
                    const qtyValue = Number(prefill.qty ?? 1) || 1;
                    const baseCost = Number(prefill.cost ?? prefill.rate ?? prefill.price ?? prefill.total ?? 0) || 0;
                    row.querySelector('.line-qty').value = qtyValue;
                    row.querySelector('.line-cost').value = baseCost.toFixed(2);
                    row.querySelector('.line-margin').value = prefill.margin ?? 0;
                }

                recalcRow(row);
            }

            function replaceLineItems(items) {
                tableBody.innerHTML = '';
                if (!Array.isArray(items) || items.length === 0) {
                    addRow();
                    updateEstimateTotal();
                    return;
                }
                items.forEach(item => addRow(item));
                updateEstimateTotal();
            }

            function attachRowListeners(row) {
                ['line-qty', 'line-cost', 'line-margin'].forEach(selector => {
                    row.querySelector(.).addEventListener('input', () => recalcRow(row));
                });
                row.querySelector('.remove-line').addEventListener('click', () => {
                    row.remove();
                    updateEstimateTotal();
                });
            }

            async function hydrateFromSiteVisit(siteVisitId) {
                if (!lineItemsEndpointTemplate || !siteVisitId) {
                    return;
                }

                const endpoint = lineItemsEndpointTemplate.replace('__SITE_VISIT__', siteVisitId);

                try {
                    const response = await fetch(endpoint, { headers: { 'Accept': 'application/json' } });
                    if (!response.ok) {
                        return;
                    }

                    const payload = await response.json();

                    if (clientSelect) {
                        if (payload.client_id) {
                            const newClientId = String(payload.client_id);
                            if (clientSelect.value !== newClientId) {
                                clientSelect.value = newClientId;
                                clientSelect.dispatchEvent(new Event('change'));
                            } else {
                                filterClientRelatedSelects();
                            }
                        } else {
                            filterClientRelatedSelects();
                        }
                    }

                    if (propertySelect && payload.property_id) {
                        if (!propertySelect.querySelector(option[value=""])) {
                            filterClientRelatedSelects();
                        }
                        propertySelect.value = String(payload.property_id);
                    }

                    if (Array.isArray(payload.line_items)) {
                        replaceLineItems(payload.line_items);
                    }
                } catch (error) {
                    console.error('Unable to load site visit data', error);
                }
            }

            tableBody.querySelectorAll('.line-item-row').forEach(row => attachRowListeners(row));

            const addLineItemButton = document.getElementById('add-line-item');
            if (addLineItemButton) {
                addLineItemButton.addEventListener('click', () => addRow());
            }

            form.addEventListener('submit', () => {
                tableBody.querySelectorAll('.line-item-row').forEach(row => recalcRow(row));
                serializeLineItems();
            });

            if (clientSelect) {
                clientSelect.addEventListener('change', () => {
                    filterClientRelatedSelects();
                });
                filterClientRelatedSelects();
            }

            if (siteVisitSelect && lineItemsEndpointTemplate) {
                siteVisitSelect.addEventListener('change', () => {
                    const siteVisitId = siteVisitSelect.value;
                    if (siteVisitId) {
                        hydrateFromSiteVisit(siteVisitId);
                    }
                });
            }

            updateEstimateTotal();
        });
    </script>
@endpush

