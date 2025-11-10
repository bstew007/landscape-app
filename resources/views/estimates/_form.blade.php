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
                        <option value="{{ $property->id }}" @selected(old('property_id', $estimate->property_id ?? '') == $property->id)>
                            {{ $client->name }} – {{ $property->name }}
                        </option>
                    @endforeach
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Site Visit (optional)</label>
            <select name="site_visit_id" class="form-select w-full mt-1">
                <option value="">Link visit</option>
                @foreach ($siteVisits as $visit)
                    <option value="{{ $visit->id }}" @selected(old('site_visit_id', $estimate->site_visit_id ?? '') == $visit->id)>
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
                   value="{{ old('total', $estimate->total ?? '') }}">
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

            function addRow() {
                const row = document.createElement('tr');
                row.classList.add('line-item-row');
                row.innerHTML = `
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
                        <button type="button" class="remove-line text-red-600 hover:text-red-900">×</button>
                    </td>
                `;
                tableBody.appendChild(row);
                attachRowListeners(row);
                recalcRow(row);
            }

            function attachRowListeners(row) {
                ['line-qty', 'line-cost', 'line-margin'].forEach(selector => {
                    row.querySelector(`.${selector}`).addEventListener('input', () => recalcRow(row));
                });
                row.querySelector('.remove-line').addEventListener('click', () => {
                    row.remove();
                });
            }

            tableBody.querySelectorAll('.line-item-row').forEach(row => attachRowListeners(row));

            document.getElementById('add-line-item').addEventListener('click', () => addRow());

            form.addEventListener('submit', () => {
                tableBody.querySelectorAll('.line-item-row').forEach(row => recalcRow(row));
                serializeLineItems();
            });
        });
    </script>
@endpush
