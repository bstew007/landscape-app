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
            <div class="flex gap-2 mt-1">
                <select
                    name="site_visit_id"
                    class="form-select w-full flex-1"
                    data-line-items-url="{{ route('site-visits.estimate-line-items', ['site_visit' => '__SITE_VISIT__']) }}"
                >
                    <option value="">Link visit</option>
                    @foreach ($siteVisits as $visit)
                        <option value="{{ $visit->id }}"
                                data-client-id="{{ $visit->client_id }}"
                                data-scope-template="{{ base64_encode($visit->scope_note_template ?? '') }}"
                                @selected(old('site_visit_id', $estimate->site_visit_id ?? '') == $visit->id)>
                            {{ optional($visit->client)->name }} – {{ optional($visit->visit_date)->format('M j, Y') }}
                        </option>
                    @endforeach
                </select>
                <button
                    type="button"
                    id="import-line-items"
                    class="px-3 py-2 text-sm border rounded bg-gray-50 hover:bg-gray-100 opacity-50 cursor-not-allowed"
                    disabled
                >
                    Import
                </button>
            </div>
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
                            <input type="text" inputmode="decimal" class="line-qty form-input w-full" value="{{ $qty }}" />
                        </td>
                        <td class="px-2 py-1 text-center">
                            <input type="text" inputmode="decimal" class="line-cost form-input w-full" value="{{ $cost }}" />
                        </td>
                        <td class="px-2 py-1 text-center">
                            <input type="text" inputmode="decimal" class="line-margin form-input w-full" value="{{ $margin }}" />
                        </td>
                        <td class="px-2 py-1 text-center">
                        <input type="text" inputmode="decimal" class="line-price form-input w-full" value="{{ number_format($price, 2, '.', '') }}" />
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

    @php
        $scopeNoteTemplate = $scopeNoteTemplate ?? '';
        $noteDefault = old('notes', $estimate->notes ?? $scopeNoteTemplate);
    @endphp

    <div>
        <label class="block text-sm font-medium text-gray-700">Notes / Scope</label>
        <textarea name="notes"
                  rows="4"
                  class="form-textarea w-full mt-1"
                  data-initial-template="{{ base64_encode($scopeNoteTemplate ?? '') }}">{{ $noteDefault }}</textarea>
    </div>

    @php
        $scopeSummaries = $scopeSummaries ?? [];
    @endphp

    @if (!empty($scopeSummaries))
        <div class="space-y-4">
            <h3 class="text-sm font-semibold text-gray-700">Calculator Measurements</h3>
            @foreach ($scopeSummaries as $summary)
                <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-4 py-3">
                        <p class="font-semibold text-gray-900">{{ $summary['title'] }}</p>
                    </div>
                    <div class="px-4 py-4 grid gap-4 md:grid-cols-2">
                        @if (!empty($summary['measurements']))
                            <table class="w-full text-sm border border-gray-100 rounded-lg overflow-hidden">
                                <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wide">
                                    <tr>
                                        <th class="px-3 py-2 text-left">Measurement</th>
                                        <th class="px-3 py-2 text-left">Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($summary['measurements'] as $measurement)
                                        <tr class="border-t border-gray-100">
                                            <td class="px-3 py-2 text-gray-600">{{ $measurement['label'] }}</td>
                                            <td class="px-3 py-2 font-semibold text-gray-900">{{ $measurement['value'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif

                        @if (!empty($summary['materials']))
                            <table class="w-full text-sm border border-gray-100 rounded-lg overflow-hidden">
                                <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wide">
                                    <tr>
                                        <th class="px-3 py-2 text-left">Material</th>
                                        <th class="px-3 py-2 text-left">Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($summary['materials'] as $material)
                                        <tr class="border-t border-gray-100">
                                            <td class="px-3 py-2 text-gray-600">
                                                {{ $material['label'] }}
                                                @if (!empty($material['meta']))
                                                    <span class="block text-xs text-gray-400">{{ $material['meta'] }}</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 font-semibold text-gray-900">{{ $material['value'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

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
            const notesField = document.querySelector('textarea[name="notes"]');
            const lineItemsEndpointTemplate = siteVisitSelect && siteVisitSelect.dataset
                ? siteVisitSelect.dataset.lineItemsUrl || null
                : null;
            const importButton = document.getElementById('import-line-items');

            if (!tableBody || !lineItemsInput || !form) {
                return;
            }

            const DEFAULT_MARGIN = 15;
            const MAX_MARGIN = 95;
            const MIN_MARGIN = -90;

            function decodeTemplate(raw) {
                if (!raw) {
                    return '';
                }

                try {
                    return decodeURIComponent(escape(window.atob(raw)));
                } catch (error) {
                    return raw.replace(/&#10;/g, '\n').replace(/\\r/g, '');
                }
            }

            const initialTemplateRaw = notesField ? decodeTemplate(notesField.dataset.initialTemplate || '') : '';
            const initialTemplateTrimmed = initialTemplateRaw.trim();

            if (notesField && !notesField.value && initialTemplateRaw) {
                notesField.value = initialTemplateRaw;
            }

            let lastAutoNotes = notesField && initialTemplateTrimmed && notesField.value.trim() === initialTemplateTrimmed
                ? notesField.value
                : '';
            let notesDirty = notesField
                ? (lastAutoNotes
                    ? notesField.value !== lastAutoNotes
                    : notesField.value.trim().length > 0)
                : false;

            function maybeApplyNotesTemplate(template) {
                if (!notesField) {
                    return;
                }

                const normalizedTemplate = (template || '').trim();

                if (!normalizedTemplate) {
                    return;
                }

                if (!notesDirty || notesField.value === lastAutoNotes || notesField.value.trim() === '') {
                    notesField.value = template;
                    lastAutoNotes = template;
                    notesDirty = false;
                }
            }

            if (notesField) {
                notesField.addEventListener('input', () => {
                    notesDirty = notesField.value !== lastAutoNotes;
                });
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
                    scopeTemplate: option.dataset.scopeTemplate || '',
                }))
                : [];

            function updateImportButtonState() {
                if (!importButton) {
                    return;
                }

                const hasVisit = Boolean(siteVisitSelect && siteVisitSelect.value);
                importButton.disabled = !hasVisit;
                importButton.classList.toggle('opacity-50', !hasVisit);
                importButton.classList.toggle('cursor-not-allowed', !hasVisit);
            }

            function rebuildSelect(select, options, clientId, selectedValue) {
                if (!select || !options.length) {
                    return;
                }

                const fragment = document.createDocumentFragment();

                options.forEach(option => {
                    if (option.value !== '' && clientId && option.clientId !== clientId) {
                        return;
                    }

                    const opt = document.createElement('option');
                    opt.value = option.value;
                    opt.textContent = option.text;
                    if (option.clientId) {
                        opt.dataset.clientId = option.clientId;
                    }
                    if (option.scopeTemplate) {
                        opt.dataset.scopeTemplate = option.scopeTemplate;
                    }
                    fragment.appendChild(opt);
                });

                select.innerHTML = '';
                select.appendChild(fragment);

                const hasSelected = selectedValue && Array.from(select.options).some(opt => opt.value === selectedValue);
                select.value = hasSelected ? selectedValue : '';
            }

            function filterClientRelatedSelects() {
                if (!clientSelect) {
                    return;
                }

                const clientId = clientSelect.value;
                const currentProperty = propertySelect ? propertySelect.value : '';
                const currentVisit = siteVisitSelect ? siteVisitSelect.value : '';

                if (propertySelect) {
                    rebuildSelect(propertySelect, propertyOptions, clientId, currentProperty);
                }

                if (siteVisitSelect) {
                    rebuildSelect(siteVisitSelect, siteVisitOptions, clientId, currentVisit);
                }

                updateImportButtonState();
            }

            function clampMargin(value) {
                if (!Number.isFinite(value)) {
                    return DEFAULT_MARGIN;
                }
                if (value > MAX_MARGIN) {
                    return MAX_MARGIN;
                }
                if (value < MIN_MARGIN) {
                    return MIN_MARGIN;
                }
                return value;
            }

            function computePrice(cost, margin) {
                if (!Number.isFinite(cost)) {
                    cost = 0;
                }

                const safeMargin = clampMargin(margin);
                const fraction = Math.min(safeMargin / 100, 0.95);
                const denominator = 1 - fraction;

                if (denominator <= 0) {
                    return cost;
                }

                return cost / denominator;
            }

            function computeMargin(cost, price) {
                if (!Number.isFinite(cost) || cost <= 0 || !Number.isFinite(price) || price <= 0) {
                    return 0;
                }

                const ratio = 1 - (cost / price);
                return clampMargin(ratio * 100);
            }

            function recalcRow(row, changedField = null, options = {}) {
                const { formatChangedField = true } = options;
                const qtyInput = row.querySelector('.line-qty');
                const costInput = row.querySelector('.line-cost');
                const marginInput = row.querySelector('.line-margin');
                const priceInput = row.querySelector('.line-price');
                const totalDisplay = row.querySelector('.line-total');
                const shouldFormat = (field) => formatChangedField || changedField !== field;

                const qty = parseFloat(qtyInput.value) || 0;
                const cost = parseFloat(costInput.value) || 0;
                let margin = parseFloat(marginInput.value);
                let price = parseFloat(priceInput.value);

                if (!Number.isFinite(margin)) {
                    margin = DEFAULT_MARGIN;
                }
                if (!Number.isFinite(price)) {
                    price = 0;
                }

                if (changedField === 'price') {
                    price = Math.max(0, price);
                    margin = cost > 0 ? computeMargin(cost, price) : 0;
                    if (shouldFormat('price')) {
                        priceInput.value = price.toFixed(2);
                    }
                    marginInput.value = clampMargin(margin).toFixed(1);
                } else if (changedField === 'margin') {
                    margin = clampMargin(margin);
                    price = computePrice(cost, margin);
                    if (shouldFormat('margin')) {
                        marginInput.value = margin.toFixed(1);
                    }
                    priceInput.value = price.toFixed(2);
                } else if (changedField === 'cost') {
                    margin = clampMargin(margin);
                    price = computePrice(cost, margin);
                    marginInput.value = margin.toFixed(1);
                    priceInput.value = price.toFixed(2);
                } else {
                    if (shouldFormat('price')) {
                        priceInput.value = price.toFixed(2);
                    }
                    if (shouldFormat('margin')) {
                        marginInput.value = clampMargin(margin).toFixed(1);
                    }
                }

                const lineTotal = qty * (parseFloat(priceInput.value) || 0);
                totalDisplay.textContent = Number.isFinite(lineTotal) ? lineTotal.toFixed(2) : '0.00';
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

                totalField.value = sum > 0 ? sum.toFixed(2) : '0.00';
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

                    payload.push({
                        label,
                        qty,
                        cost,
                        margin,
                        price,
                        total,
                    });
                });

                lineItemsInput.value = JSON.stringify(payload);
            }

            function addRow(prefill = null) {
                const row = document.createElement('tr');
                row.classList.add('line-item-row');
                row.innerHTML = `
                    <td class="px-2 py-1">
                        <input type="text" class="line-label form-input w-full" value="">
                    </td>
                    <td class="px-2 py-1 text-center">
                        <input type="text" inputmode="decimal" class="line-qty form-input w-full" value="1">
                    </td>
                    <td class="px-2 py-1 text-center">
                        <input type="text" inputmode="decimal" class="line-cost form-input w-full" value="0">
                    </td>
                    <td class="px-2 py-1 text-center">
                        <input type="text" inputmode="decimal" class="line-margin form-input w-full" value="${DEFAULT_MARGIN}">
                    </td>
                    <td class="px-2 py-1 text-center">
                        <input type="text" inputmode="decimal" class="line-price form-input w-full" value="0">
                    </td>
                    <td class="px-2 py-1 text-center">
                        <span class="line-total text-gray-700 font-semibold">0.00</span>
                    </td>
                    <td class="px-2 py-1 text-center">
                        <button type="button" class="remove-line text-red-600 hover:text-red-900">A-</button>
                    </td>
                `;

                tableBody.appendChild(row);

                const labelInput = row.querySelector('.line-label');
                const qtyInput = row.querySelector('.line-qty');
                const costInput = row.querySelector('.line-cost');
                const marginInput = row.querySelector('.line-margin');
                const priceInput = row.querySelector('.line-price');
                const totalDisplay = row.querySelector('.line-total');

                if (prefill) {
                    labelInput.value = prefill.label ?? '';
                    qtyInput.value = Number(prefill.qty ?? 1) || 1;

                    const costValue = Number(prefill.cost ?? prefill.rate ?? prefill.price ?? prefill.total ?? 0) || 0;
                    costInput.value = costValue.toFixed(2);

                    const rawPrice = Number(prefill.price ?? prefill.rate ?? prefill.total ?? costValue) || 0;
                    priceInput.value = rawPrice.toFixed(2);

                    if (prefill.margin !== undefined) {
                        marginInput.value = Number(prefill.margin).toFixed(1);
                    } else if (costValue > 0 && rawPrice > 0) {
                        const derivedMargin = computeMargin(costValue, rawPrice);
                        marginInput.value = derivedMargin.toFixed(1);
                    }

                    const initialTotal = (Number(qtyInput.value) || 0) * rawPrice;
                    totalDisplay.textContent = initialTotal.toFixed(2);
                }

                attachRowListeners(row);
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
                const wireField = (selector, field) => {
                    const input = row.querySelector(selector);
                    if (!input) {
                        return;
                    }
                    input.addEventListener('input', () => recalcRow(row, field, { formatChangedField: false }));
                    input.addEventListener('blur', () => recalcRow(row, field));
                };

                wireField('.line-qty', 'qty');
                wireField('.line-cost', 'cost');
                wireField('.line-margin', 'margin');
                wireField('.line-price', 'price');
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

                    if (clientSelect && payload.client_id) {
                        const newClientId = String(payload.client_id);
                        if (clientSelect.value !== newClientId) {
                            clientSelect.value = newClientId;
                            clientSelect.dispatchEvent(new Event('change'));
                        } else {
                            filterClientRelatedSelects();
                        }
                    }

                    if (propertySelect && payload.property_id) {
                        const propertyValue = String(payload.property_id);
                        const exists = Array.from(propertySelect.options).some(opt => opt.value === propertyValue);
                        if (!exists) {
                            rebuildSelect(propertySelect, propertyOptions, clientSelect ? clientSelect.value : '', propertyValue);
                        }
                        propertySelect.value = propertyValue;
                    }

                    if (Array.isArray(payload.line_items)) {
                        replaceLineItems(payload.line_items);
                    }
                } catch (error) {
                    console.error('Unable to load site visit data', error);
                }
            }

            tableBody.querySelectorAll('.line-item-row').forEach(row => {
                attachRowListeners(row);
                recalcRow(row);
            });

            const addLineItemButton = document.getElementById('add-line-item');
            if (addLineItemButton) {
                addLineItemButton.addEventListener('click', () => addRow());
            }

            form.addEventListener('submit', () => {
                tableBody.querySelectorAll('.line-item-row').forEach(row => recalcRow(row));
                serializeLineItems();
            });

            if (clientSelect) {
                clientSelect.addEventListener('change', filterClientRelatedSelects);
                filterClientRelatedSelects();
            }

            if (siteVisitSelect) {
                siteVisitSelect.addEventListener('change', () => {
                    const selectedOption = siteVisitSelect.options[siteVisitSelect.selectedIndex];
                    const scopeTemplate = selectedOption ? decodeTemplate(selectedOption.dataset.scopeTemplate || '') : '';
                    updateImportButtonState();
                    if (scopeTemplate) {
                        maybeApplyNotesTemplate(scopeTemplate);
                    }
                    const siteVisitId = siteVisitSelect.value;
                    if (lineItemsEndpointTemplate && siteVisitId) {
                        hydrateFromSiteVisit(siteVisitId);
                    }
                });

                // Apply template for preselected visit on load
                const initialOption = siteVisitSelect.options[siteVisitSelect.selectedIndex];
                if (initialOption && initialOption.dataset.scopeTemplate && notesField && notesField.value.trim() === '') {
                    maybeApplyNotesTemplate(decodeTemplate(initialOption.dataset.scopeTemplate));
                }
            }

            if (importButton && lineItemsEndpointTemplate) {
                importButton.addEventListener('click', () => {
                    const siteVisitId = siteVisitSelect ? siteVisitSelect.value : null;
                    if (siteVisitId) {
                        hydrateFromSiteVisit(siteVisitId);
                    }
                });
            }

            updateEstimateTotal();
            updateImportButtonState();
        });
    </script>
@endpush
