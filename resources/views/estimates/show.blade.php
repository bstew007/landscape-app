@extends('layouts.sidebar')

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
@endphp

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm text-gray-500 uppercase tracking-wide">Estimate</p>
            <h1 class="text-3xl font-bold">{{ $estimate->title }}</h1>
            <p class="text-gray-600">{{ $estimate->client->name }} · {{ $estimate->property->name ?? 'No property' }}</p>
        </div>
            <div class="flex flex-wrap gap-2">
            <a href="{{ route('estimates.edit', $estimate) }}" class="rounded border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">Edit</a>
            <form action="{{ route('estimates.destroy', $estimate) }}" method="POST" onsubmit="return confirm('Delete this estimate?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded border border-red-300 px-4 py-2 text-sm text-red-600 hover:bg-red-50">Delete</button>
            </form>
            <a href="{{ route('estimates.preview-email', $estimate) }}" class="rounded border border-blue-300 px-4 py-2 text-sm text-blue-700 hover:bg-blue-50">Preview Email</a>
            <form action="{{ route('estimates.invoice', $estimate) }}" method="POST">
                @csrf
                <button type="submit" class="rounded border border-green-300 px-4 py-2 text-sm text-green-700 hover:bg-green-50">Create Invoice</button>
            </form>
            <a href="{{ route('estimates.print', $estimate) }}" target="_blank" class="rounded border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Print</a>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-xs font-semibold uppercase text-gray-500">Status</h2>
            <p class="text-xl font-bold text-gray-900">{{ ucfirst($estimate->status) }}</p>
            <p class="text-sm text-gray-600 mt-2">Created {{ $estimate->created_at->format('M j, Y') }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-xs font-semibold uppercase text-gray-500">Total</h2>
            <p class="text-xl font-bold text-gray-900">{{ $estimate->total ? '$' . number_format($estimate->total, 2) : 'Pending' }}</p>
            <p class="text-sm text-gray-600 mt-2">Expires {{ optional($estimate->expires_at)->format('M j, Y') ?? 'N/A' }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-xs font-semibold uppercase text-gray-500">Linked Site Visit</h2>
            @if ($estimate->siteVisit)
                <a
                    href="{{ route('clients.site-visits.show', [$estimate->client, $estimate->siteVisit]) }}"
                    class="text-sm text-blue-600 hover:text-blue-800"
                >
                    {{ $estimate->siteVisit->visit_date?->format('M j, Y') ?? 'View site visit' }}
                </a>
            @else
                <p class="text-sm text-gray-800">Not linked</p>
            @endif
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-xs font-semibold uppercase text-gray-500">Email Status</h2>
            @if ($estimate->email_send_count)
                <p class="text-sm font-semibold text-gray-900">
                    Last sent {{ $estimate->email_last_sent_at?->timezone(config('app.timezone'))->format('M j, Y g:i A') }}
                </p>
                <p class="text-xs text-gray-600 mt-1">
                    Sent {{ $estimate->email_send_count }} {{ Str::plural('time', $estimate->email_send_count) }}
                    @if ($estimate->emailSender)
                        by {{ $estimate->emailSender->name ?? $estimate->emailSender->email }}
                    @endif
                </p>
            @else
                <p class="text-sm text-gray-700 mt-2">Not emailed yet.</p>
            @endif
        </div>
    </div>

    <section class="bg-white rounded-lg shadow p-6 space-y-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Scope & Notes</h2>
            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $estimate->notes ?: 'No additional notes yet.' }}</p>
        </div>
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Terms & Conditions</h2>
            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $estimate->terms ?: 'Add project terms to finalize this estimate.' }}</p>
        </div>
    </section>

    <section class="bg-white rounded-lg shadow p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Line Items</h2>
                <p class="text-sm text-gray-500">Includes calculator imports + manual catalog additions.</p>
            </div>
            <div class="text-sm text-gray-600">
                <p>Materials: <strong id="summary-material">${{ number_format($estimate->material_subtotal, 2) }}</strong></p>
                <p>Labor: <strong id="summary-labor">${{ number_format($estimate->labor_subtotal, 2) }}</strong></p>
                <p>Fees/Discounts: <strong id="summary-fees">${{ number_format($estimate->fee_total - $estimate->discount_total, 2) }}</strong></p>
                <p>Tax: <strong id="summary-tax">${{ number_format($estimate->tax_total, 2) }}</strong></p>
                <p class="text-base text-gray-900 mt-1">Grand Total: <strong id="summary-grand">${{ number_format($estimate->grand_total, 2) }}</strong></p>
            </div>
        </div>

        @if ($estimate->items->isNotEmpty())
            <div class="overflow-x-auto border rounded">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="text-left px-3 py-2">Type</th>
                        <th class="text-left px-3 py-2">Description</th>
                        <th class="text-center px-3 py-2">Qty</th>
                        <th class="text-center px-3 py-2">Unit Cost</th>
                        <th class="text-center px-3 py-2">Tax</th>
                        <th class="text-right px-3 py-2">Total</th>
                        <th class="px-3 py-2"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @php
                        $grouped = $estimate->items->groupBy('calculation_id');
                    @endphp
                    @foreach ($grouped as $calcId => $items)
                        @php
                            $calc = $calcId ? $items->first()->calculation : null;
                            $groupLabel = $calc ? (\Illuminate\Support\Str::headline($calc->calculation_type) . ' Calculation') : 'Manual Items';
                            $groupSubtotal = $items->sum('line_total');
                        @endphp
                        <tr class="bg-gray-50" @if ($calc) data-calculation-id="{{ $calc->id }}" @endif>
                            <td colspan="5" class="px-3 py-2 text-gray-700 font-semibold">{{ $groupLabel }}</td>
                            <td class="px-3 py-2 text-right font-semibold text-gray-900">${{ number_format($groupSubtotal, 2) }}</td>
                            <td class="px-3 py-2 text-right space-x-2">
                                @if ($calc)
                                    <button type="button" class="text-red-600 hover:underline text-sm" data-action="remove-group" data-calculation-id="{{ $calc->id }}">Remove Items</button>
                                @endif
                            </td>
                        </tr>
                        @foreach ($items as $item)
                            <tr class="border-t" data-item-id="{{ $item->id }}" @if ($calcId) data-calculation-id="{{ $calcId }}" @endif draggable="true" data-name="{{ e($item->name) }}" data-quantity="{{ $item->quantity }}" data-unit="{{ $item->unit }}" data-unit-cost="{{ $item->unit_cost }}" data-tax-rate="{{ $item->tax_rate }}">
                                <td class="px-3 py-2 text-gray-600 capitalize">{{ $item->item_type }}</td>
                                <td class="px-3 py-2">
                                    <div class="font-semibold text-gray-900">{{ $item->name }}</div>
                                    @if ($item->description)
                                        <p class="text-xs text-gray-500">{{ $item->description }}</p>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-center text-gray-700" data-col="quantity">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }} {{ $item->unit }}</td>
                                <td class="px-3 py-2 text-center text-gray-700" data-col="unit_cost">${{ number_format($item->unit_cost, 2) }}</td>
                                <td class="px-3 py-2 text-center text-gray-700" data-col="tax_rate">
                                    {{ $item->tax_rate > 0 ? ($item->tax_rate * 100) . '%' : '—' }}
                                </td>
                                <td class="px-3 py-2 text-right font-semibold text-gray-900" data-col="line_total">${{ number_format($item->line_total, 2) }}</td>
                                <td class="px-3 py-2 text-right space-x-3" data-col="actions">
                                    <button type="button" class="text-blue-600 hover:underline text-sm" data-action="edit-item" data-item-id="{{ $item->id }}">Edit</button>
                                    <form action="{{ route('estimates.items.destroy', [$estimate, $item]) }}" method="POST"
                                          onsubmit="return confirm('Remove this line item?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:underline text-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-gray-500">No structured line items yet. Import from calculators or add a catalog item below.</p>
        @endif
    </section>

    <section class="grid lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6 space-y-4">
            <h3 class="text-lg font-semibold">Add Material from Catalog</h3>
            <form method="POST" action="{{ route('estimates.items.store', $estimate) }}" class="space-y-3" id="materialCatalogForm" data-form-type="material">
                @csrf
                <input type="hidden" name="item_type" value="material">
                <input type="hidden" name="catalog_type" value="material">
                <div>
                    <label class="block text-sm font-semibold mb-1">Material</label>
                    <input type="text" class="form-input w-full mb-2 text-sm" placeholder="Search materials..." data-role="filter">
                    <select name="catalog_id" class="form-select w-full" data-role="material-select">
                        <option value="">Select material</option>
                        @foreach ($materials as $material)
                            <option value="{{ $material->id }}"
                                    data-unit="{{ $material->unit }}"
                                    data-cost="{{ $material->unit_cost }}"
                                    data-tax="{{ $material->is_taxable ? $material->tax_rate : 0 }}">
                                {{ $material->name }} ({{ $material->unit }} @ ${{ number_format($material->unit_cost, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Quantity</label>
                        <input type="number" step="0.01" min="0" name="quantity" class="form-input w-full" value="1" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Unit Cost ($)</label>
                        <input type="number" step="0.01" min="0" name="unit_cost" class="form-input w-full" value="0" required data-role="material-cost">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Unit Label</label>
                        <input type="text" name="unit" class="form-input w-full" value="" data-role="material-unit">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Tax Rate</label>
                        <input type="number" step="0.001" min="0" name="tax_rate" class="form-input w-full" value="0" data-role="material-tax">
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <button class="inline-flex justify-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed" type="submit" disabled>
                        Add Material
                    </button>
                    <span class="text-xs text-gray-500" data-role="preview-total">Line total: $0.00</span>
                </div>
                @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                @error('unit_cost')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
            </form>
        </div>

        <div class="bg-white rounded-lg shadow p-6 space-y-4">
            <h3 class="text-lg font-semibold">Add Labor from Catalog</h3>
            <form method="POST" action="{{ route('estimates.items.store', $estimate) }}" class="space-y-3" id="laborCatalogForm" data-form-type="labor">
                @csrf
                <input type="hidden" name="item_type" value="labor">
                <input type="hidden" name="catalog_type" value="labor">
                <div>
                    <label class="block text-sm font-semibold mb-1">Labor</label>
                    <input type="text" class="form-input w-full mb-2 text-sm" placeholder="Search labor..." data-role="filter">
                    <select name="catalog_id" class="form-select w-full" data-role="labor-select">
                        <option value="">Select labor</option>
                        @foreach ($laborCatalog as $labor)
                            <option value="{{ $labor->id }}"
                                    data-unit="{{ $labor->unit }}"
                                    data-cost="{{ $labor->base_rate }}">
                                {{ $labor->name }} ({{ ucfirst($labor->type) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Quantity</label>
                        <input type="number" step="0.01" min="0" name="quantity" class="form-input w-full" value="1" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Unit Cost ($)</label>
                        <input type="number" step="0.01" min="0" name="unit_cost" class="form-input w-full" value="0" required data-role="labor-cost">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Unit Label</label>
                        <input type="text" name="unit" class="form-input w-full" value="" data-role="labor-unit">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Tax Rate</label>
                        <input type="number" step="0.001" min="0" name="tax_rate" class="form-input w-full" value="0">
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <button class="inline-flex justify-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed" type="submit" disabled>
                        Add Labor
                    </button>
                    <span class="text-xs text-gray-500" data-role="preview-total">Line total: $0.00</span>
                </div>
                @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                @error('unit_cost')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
            </form>
        </div>

        <div class="bg-white rounded-lg shadow p-6 space-y-4">
            <h3 class="text-lg font-semibold">Add Custom Line Item</h3>
            <form method="POST" action="{{ route('estimates.items.store', $estimate) }}" class="space-y-3" id="customItemForm" data-form-type="custom">
                @csrf
                <div>
                    <label class="block text-sm font-semibold mb-1">Type</label>
                    <select name="item_type" class="form-select w-full">
                        <option value="material">Material</option>
                        <option value="labor">Labor</option>
                        <option value="fee">Fee</option>
                        <option value="discount">Discount</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Name</label>
                    <input type="text" name="name" class="form-input w-full" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Description</label>
                    <textarea name="description" rows="2" class="form-textarea w-full"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Quantity</label>
                        <input type="number" step="0.01" min="0" name="quantity" class="form-input w-full" value="1" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Unit Cost ($)</label>
                        <input type="number" step="0.01" min="0" name="unit_cost" class="form-input w-full" value="0" required>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Unit Label</label>
                        <input type="text" name="unit" class="form-input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Tax Rate</label>
                        <input type="number" step="0.001" min="0" name="tax_rate" class="form-input w-full" value="0">
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <button class="inline-flex justify-center px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-900 disabled:opacity-50 disabled:cursor-not-allowed" type="submit" disabled>
                        Add Custom Item
                    </button>
                    <span class="text-xs text-gray-500" data-role="preview-total">Line total: $0.00</span>
                </div>
                @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                @error('unit_cost')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
            </form>
        </div>
    </section>

    <section class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Invoice</h2>
            <p class="text-sm text-gray-500">Auto-generated from estimate</p>
        </div>
        @if ($estimate->invoice)
            <p class="text-sm text-gray-700"><strong>Status:</strong> {{ ucfirst($estimate->invoice->status) }}</p>
            <p class="text-sm text-gray-700"><strong>Amount:</strong> ${{ number_format($estimate->invoice->amount ?? 0, 2) }}</p>
            <p class="text-sm text-gray-700"><strong>Due:</strong> {{ optional($estimate->invoice->due_date)->format('M j, Y') ?? 'N/A' }}</p>
            @if ($estimate->invoice->pdf_path)
                <a href="{{ Storage::disk('public')->url($estimate->invoice->pdf_path) }}" class="text-blue-600 hover:text-blue-800 text-sm">Download Invoice</a>
            @endif
        @else
            <p class="text-sm text-gray-500">No invoice generated yet. Use the button above to create one.</p>
        @endif
    </section>

    @if ($availableCalculations->isNotEmpty())
        <section class="bg-white rounded-lg shadow p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold">Calculator Outputs</h3>
                    <p class="text-sm text-gray-500">Import detailed materials/labor rows directly from saved calculations.</p>
                </div>
                <span class="text-sm text-gray-500">{{ $availableCalculations->count() }} available</span>
            </div>

            <div class="divide-y">
                @foreach ($availableCalculations as $calc)
                    @php
                        $data = $calc->data ?? [];
                        $materialsTotal = $data['material_total'] ?? 0;
                        $laborTotal = $data['labor_cost'] ?? 0;
                        $finalPrice = $data['final_price'] ?? ($materialsTotal + $laborTotal);
                    @endphp
                    <div class="py-3 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div>
                            <p class="font-semibold text-gray-900">
                                {{ \Illuminate\Support\Str::headline($calc->calculation_type) }}
                                <span class="text-xs text-gray-500">Saved {{ optional($calc->created_at)->format('M j, Y') }}</span>
                            </p>
                            <p class="text-sm text-gray-600">
                                Materials: ${{ number_format($materialsTotal, 2) }} · Labor: ${{ number_format($laborTotal, 2) }} · Final: ${{ number_format($finalPrice, 2) }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <form method="POST" action="{{ route('estimates.import-calculation', [$estimate, $calc]) }}">
                                @csrf
                                <button class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">
                                    Import Line Items
                                </button>
                            </form>
                            <form method="POST" action="{{ route('estimates.import-calculation', [$estimate, $calc]) }}">
                                @csrf
                                <input type="hidden" name="replace" value="1">
                                <button class="px-4 py-2 bg-purple-50 text-purple-700 rounded hover:bg-purple-100">
                                    Replace Previous Import
                                </button>
                            </form>
                            <form method="POST" action="{{ route('estimates.remove-calculation', [$estimate, $calc]) }}" onsubmit="return confirm('Remove imported items for this calculation?')">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="px-4 py-2 bg-red-50 text-red-700 rounded hover:bg-red-100" data-action="remove-group" data-calculation-id="{{ $calc->id }}">
                                    Remove Imported Items
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const reorderUrl = "{{ url('estimates/'.$estimate->id.'/items/reorder') }}";
        const updateBaseUrl = "{{ url('estimates/'.$estimate->id.'/items') }}/"; // + id
        const removeCalcBaseUrl = "{{ url('estimates/'.$estimate->id.'/remove-calculation') }}/"; // + calculation id

        function formatMoney(n) {
            const val = Number(n || 0);
            return `$${val.toFixed(2)}`;
        }
        function updateSummary(totals) {
            const m = document.getElementById('summary-material');
            const l = document.getElementById('summary-labor');
            const f = document.getElementById('summary-fees');
            const t = document.getElementById('summary-tax');
            const g = document.getElementById('summary-grand');
            if (m) m.textContent = formatMoney(Number(totals.material_subtotal || 0));
            if (l) l.textContent = formatMoney(Number(totals.labor_subtotal || 0));
            const fees = Number(totals.fee_total || 0) - Number(totals.discount_total || 0);
            if (f) f.textContent = formatMoney(fees);
            if (t) t.textContent = formatMoney(Number(totals.tax_total || 0));
            if (g) g.textContent = formatMoney(Number(totals.grand_total || 0));
        }

        function wireCatalogForm(formSelector, selectSelector, unitSelector, costSelector, taxSelector) {
            const form = document.querySelector(formSelector);
            if (!form) return;
            const select = form.querySelector(selectSelector);
            const unitInput = form.querySelector(unitSelector);
            const costInput = form.querySelector(costSelector);
            const taxInput = taxSelector ? form.querySelector(taxSelector) : null;
            if (!select) return;
            select.addEventListener('change', () => {
                const option = select.options[select.selectedIndex];
                if (!option) return;
                const unit = option.dataset.unit || '';
                const cost = option.dataset.cost || 0;
                const tax = option.dataset.tax || 0;
                if (unitInput) unitInput.value = unit;
                if (costInput) costInput.value = cost;
                if (taxInput) taxInput.value = tax;
            });
            // simple typeahead filter
            const filterInput = form.querySelector('[data-role="filter"]');
            if (filterInput && select) {
                filterInput.addEventListener('input', () => {
                    const q = (filterInput.value || '').toLowerCase().trim();
                    Array.from(select.options).forEach((opt, i) => {
                        if (i === 0) return; // keep placeholder visible
                        const text = (opt.textContent || '').toLowerCase();
                        opt.hidden = q && !text.includes(q);
                    });
                });
            }
        }

        wireCatalogForm('#materialCatalogForm', '[data-role="material-select"]', '[data-role="material-unit"]', '[data-role="material-cost"]', '[data-role="material-tax"]');
        wireCatalogForm('#laborCatalogForm', '[data-role="labor-select"]', '[data-role="labor-unit"]', '[data-role="labor-cost"]');

        // Guardrails + line total preview + AJAX submit for add-item forms
        const forms = ['#materialCatalogForm', '#laborCatalogForm', '#customItemForm'].map(sel => document.querySelector(sel)).filter(Boolean);
        const getNum = (v) => { const n = parseFloat(v); return Number.isFinite(n) ? n : 0; };

        function updateFormState(form) {
            const type = form.dataset.formType;
            const qty = form.querySelector('input[name="quantity"]');
            const cost = form.querySelector('input[name="unit_cost"]');
            const name = form.querySelector('input[name="name"]');
            const select = form.querySelector('select[name="catalog_id"]');
            const submitBtn = form.querySelector('button[type="submit"]');
            const preview = form.querySelector('[data-role="preview-total"]');

            const q = getNum(qty?.value);
            const c = getNum(cost?.value);
            if (preview) preview.textContent = `Line total: $${(q * c).toFixed(2)}`;

            let canSubmit = false;
            if (type === 'material' || type === 'labor') {
                canSubmit = !!(select && select.value);
            } else {
                const hasName = Boolean(name && name.value.trim().length);
                const hasCost = Number.isFinite(c) && c >= 0;
                canSubmit = hasName && hasCost;
            }

            if (submitBtn) submitBtn.disabled = !canSubmit;
        }

        function clearFormErrors(form) {
            form.querySelectorAll('[data-error]').forEach(el => el.remove());
            form.querySelectorAll('.border-red-300').forEach(el => el.classList.remove('border-red-300'));
        }
        function renderFormErrors(form, errors) {
            Object.entries(errors || {}).forEach(([field, messages]) => {
                const input = form.querySelector(`[name="${field}"]`);
                const msg = Array.isArray(messages) ? messages[0] : String(messages);
                if (input) {
                    input.classList.add('border-red-300');
                    const p = document.createElement('p');
                    p.className = 'text-red-600 text-xs mt-1';
                    p.setAttribute('data-error', field);
                    p.textContent = msg;
                    input.insertAdjacentElement('afterend', p);
                }
            });
        }

        function bindForm(form) {
            const inputs = form.querySelectorAll('input, select');
            inputs.forEach(el => el.addEventListener('input', () => updateFormState(form)));
            inputs.forEach(el => el.addEventListener('change', () => updateFormState(form)));
            updateFormState(form);

            form.addEventListener('submit', (e) => {
                e.preventDefault();
                clearFormErrors(form);
                const action = form.getAttribute('action');
                const fd = new FormData(form);
                fetch(action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: fd,
                }).then(async (r) => {
                    if (!r.ok) throw await r.json().catch(() => ({}));
                    const json = await r.json();
                    if (json.totals) updateSummary(json.totals);

                    // Insert the new row into the Manual Items group without reloading
                    const table = document.querySelector('table');
                    const tbody = table ? table.querySelector('tbody') : null;
                    if (!tbody) {
                        // If table hasn't been rendered yet, fallback to reload
                        return location.reload();
                    }

                    const item = json.item || {};
                    const calcId = item.calculation_id || null; // manual additions should be null
                    if (calcId) {
                        // For defensive handling; current add forms only create manual items
                        return location.reload();
                    }

                    // Ensure a Manual Items group header exists
                    let manualHeader = tbody.querySelector('tr.bg-gray-50:not([data-calculation-id])');
                    if (!manualHeader) {
                        const header = document.createElement('tr');
                        header.className = 'bg-gray-50';
                        header.innerHTML = `
                            <td colspan="5" class="px-3 py-2 text-gray-700 font-semibold">Manual Items</td>
                            <td class="px-3 py-2 text-right font-semibold text-gray-900" data-role="group-subtotal">$0.00</td>
                            <td class="px-3 py-2 text-right space-x-2"></td>
                        `;
                        tbody.appendChild(header);
                        manualHeader = header;
                    }

                    // Find insertion point (after last row in this group)
                    let insertBefore = manualHeader.nextElementSibling;
                    let after = manualHeader;
                    while (insertBefore && !insertBefore.classList.contains('bg-gray-50')) {
                        after = insertBefore;
                        insertBefore = insertBefore.nextElementSibling;
                    }

                    // Render and insert the new item row
                    const newRow = document.createElement('tr');
                    newRow.className = 'border-t bg-green-50';
                    newRow.setAttribute('data-item-id', item.id);
                    newRow.setAttribute('draggable', 'true');
                    newRow.setAttribute('data-name', item.name || 'Line Item');
                    newRow.setAttribute('data-quantity', item.quantity || 0);
                    newRow.setAttribute('data-unit', item.unit || '');
                    newRow.setAttribute('data-unit-cost', item.unit_cost || 0);
                    newRow.setAttribute('data-tax-rate', item.tax_rate || 0);

                    const qtyText = Number(item.quantity || 0).toFixed(2).replace(/\.00$/, '');
                    const taxText = Number(item.tax_rate || 0) > 0 ? `${(Number(item.tax_rate) * 100).toFixed(3).replace(/0+$/,'').replace(/\.$/,'')}%` : '—';

                    newRow.innerHTML = `
                        <td class="px-3 py-2 text-gray-600 capitalize">${(item.item_type || 'material')}</td>
                        <td class="px-3 py-2">
                            <div class="font-semibold text-gray-900">${(item.name || 'Line Item')}</div>
                            ${item.description ? `<p class="text-xs text-gray-500">${item.description}</p>` : ''}
                        </td>
                        <td class="px-3 py-2 text-center text-gray-700" data-col="quantity">${qtyText} ${item.unit || ''}</td>
                        <td class="px-3 py-2 text-center text-gray-700" data-col="unit_cost">${formatMoney(Number(item.unit_cost || 0))}</td>
                        <td class="px-3 py-2 text-center text-gray-700" data-col="tax_rate">${taxText}</td>
                        <td class="px-3 py-2 text-right font-semibold text-gray-900" data-col="line_total">${formatMoney(Number(item.line_total || 0))}</td>
                        <td class="px-3 py-2 text-right space-x-3" data-col="actions">
                            <button type="button" class="text-blue-600 hover:underline text-sm" data-action="edit-item" data-item-id="${item.id}">Edit</button>
                            <form action="{{ url('estimates/'.$estimate->id.'/items') }}/${item.id}" method="POST" class="inline" onsubmit="return confirm('Remove this line item?')">
                                <input type="hidden" name="_token" value="${csrfToken}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button class="text-red-600 hover:underline text-sm">Delete</button>
                            </form>
                        </td>
                    `;

                    if (after && after !== manualHeader) {
                        after.insertAdjacentElement('afterend', newRow);
                    } else {
                        manualHeader.insertAdjacentElement('afterend', newRow);
                    }
                    // remove highlight after a moment
                    setTimeout(() => newRow.classList.remove('bg-green-50'), 1200);

                    // Update manual group subtotal by summing line_total cells until next header
                    let subtotal = 0;
                    let cursor = manualHeader.nextElementSibling;
                    while (cursor && !cursor.classList.contains('bg-gray-50')) {
                        const cell = cursor.querySelector('[data-col="line_total"]');
                        if (cell && cell.textContent) {
                            const val = parseFloat((cell.textContent || '').replace(/[^0-9.\-]/g, ''));
                            if (Number.isFinite(val)) subtotal += val;
                        }
                        cursor = cursor.nextElementSibling;
                    }
                    const subtotalCell = manualHeader.querySelector('[data-role="group-subtotal"]') || manualHeader.children[5];
                    if (subtotalCell) subtotalCell.textContent = formatMoney(subtotal);

                    showToast('Line item added', 'success');

                    // Reset and disable the form appropriately
                    const formType = form.dataset.formType;
                    if (formType === 'material' || formType === 'labor') {
                        const select = form.querySelector('select[name="catalog_id"]');
                        if (select) select.value = '';
                    } else {
                        const nameInput = form.querySelector('input[name="name"]');
                        if (nameInput) nameInput.value = '';
                    }
                    const qtyInput = form.querySelector('input[name="quantity"]');
                    if (qtyInput) qtyInput.value = '1';
                    const costInput = form.querySelector('input[name="unit_cost"]');
                    if (costInput) costInput.value = '';
                    const unitInput = form.querySelector('input[name="unit"]');
                    if (unitInput) unitInput.value = '';
                    const taxInput = form.querySelector('input[name="tax_rate"]');
                    if (taxInput) taxInput.value = formType === 'material' ? (form.querySelector('[data-role="material-tax"]')?.value || '0') : '0';

                    updateFormState(form);
                }).catch(async (err) => {
                    const errors = err && err.errors ? err.errors : null;
                    if (errors) renderFormErrors(form, errors);
                    showToast('Could not add item', 'error');
                });
            });
        }

        forms.forEach(bindForm);

        // Drag & drop reordering
        const tbody = document.querySelector('table tbody');
        if (tbody) {
            let dragSrcEl = null;
            tbody.addEventListener('dragstart', (e) => {
                const row = e.target.closest('tr[data-item-id]');
                if (!row) return;
                dragSrcEl = row;
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', row.dataset.itemId);
                row.classList.add('opacity-50');
            });
            tbody.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
            });
            tbody.addEventListener('drop', (e) => {
                e.preventDefault();
                const targetRow = e.target.closest('tr[data-item-id]');
                if (!dragSrcEl || !targetRow || dragSrcEl === targetRow) return;
                const rect = targetRow.getBoundingClientRect();
                const before = (e.clientY - rect.top) < rect.height / 2;
                if (before) {
                    targetRow.parentNode.insertBefore(dragSrcEl, targetRow);
                } else {
                    targetRow.parentNode.insertBefore(dragSrcEl, targetRow.nextSibling);
                }
                dragSrcEl.classList.remove('opacity-50');
                dragSrcEl = null;

                const ids = Array.from(tbody.querySelectorAll('tr[data-item-id]')).map(tr => parseInt(tr.dataset.itemId, 10));
                fetch(reorderUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ order: ids }),
                }).then(r => { if (!r.ok) throw new Error('bad'); showToast('Item order updated', 'success'); }).catch(() => location.reload());
            });
            tbody.addEventListener('dragend', (e) => {
                const row = e.target.closest('tr[data-item-id]');
                if (row) row.classList.remove('opacity-50');
            });
        }

        // Event delegation for edit/save/cancel and group removal
        document.addEventListener('click', (e) => {
            const editBtn = e.target.closest('[data-action="edit-item"]');
            const saveBtn = e.target.closest('[data-action="save-item"]');
            const cancelBtn = e.target.closest('[data-action="cancel-edit"]');
            const removeGroupBtn = e.target.closest('[data-action="remove-group"]');

            if (editBtn) {
                const id = editBtn.dataset.itemId;
                const row = document.querySelector(`tr[data-item-id="${id}"]`);
                if (!row || row.dataset.editing === '1') return;
                row.dataset.editing = '1';

                const name = row.dataset.name || '';
                const qty = parseFloat(row.dataset.quantity || '0');
                const unit = row.dataset.unit || '';
                const unitCost = parseFloat(row.dataset.unitCost || '0');
                const tax = parseFloat(row.dataset.taxRate || '0');

                const nameCell = row.children[1];
                const qtyCell = row.querySelector('[data-col="quantity"]');
                const unitCostCell = row.querySelector('[data-col="unit_cost"]');
                const taxCell = row.querySelector('[data-col="tax_rate"]');
                const actionsCell = row.querySelector('[data-col="actions"]');

                row.dataset.originalHtml = row.innerHTML;

                nameCell.innerHTML = `
                    <input type="text" class="form-input w-full text-sm" data-edit-name value="${name.replace(/&/g,'&amp;').replace(/"/g,'&quot;')}">
                    <p class="text-xs text-gray-500">Edit name and quantities</p>
                `;
                qtyCell.innerHTML = `
                    <input type="number" step="0.01" min="0" class="form-input w-24 text-sm text-center" data-edit-qty value="${qty}"> 
                    <input type="text" class="form-input w-16 text-sm text-center ml-2" data-edit-unit value="${unit.replace(/&/g,'&amp;').replace(/"/g,'&quot;')}">`;
                unitCostCell.innerHTML = `<input type="number" step="0.01" min="0" class="form-input w-24 text-sm text-center" data-edit-unit-cost value="${unitCost}">`;
                taxCell.innerHTML = `<input type="number" step="0.001" min="0" class="form-input w-20 text-sm text-center" data-edit-tax value="${tax}">`;
                actionsCell.innerHTML = `
                    <button class="text-green-700 hover:underline text-sm" data-action="save-item" data-item-id="${id}">Save</button>
                    <button class="text-gray-600 hover:underline text-sm ml-2" data-action="cancel-edit" data-item-id="${id}">Cancel</button>
                `;
                return;
            }

            if (cancelBtn) {
                const id = cancelBtn.dataset.itemId;
                const row = document.querySelector(`tr[data-item-id="${id}"]`);
                if (row && row.dataset.originalHtml) {
                    row.innerHTML = row.dataset.originalHtml;
                    row.dataset.editing = '0';
                }
                return;
            }

            if (saveBtn) {
                const id = saveBtn.dataset.itemId;
                const row = document.querySelector(`tr[data-item-id="${id}"]`);
                if (!row) return;
                const name = row.querySelector('[data-edit-name]')?.value ?? '';
                const quantity = parseFloat(row.querySelector('[data-edit-qty]')?.value ?? '0');
                const unit = row.querySelector('[data-edit-unit]')?.value ?? '';
                const unit_cost = parseFloat(row.querySelector('[data-edit-unit-cost]')?.value ?? '0');
                const tax_rate = parseFloat(row.querySelector('[data-edit-tax]')?.value ?? '0');

                if (!Number.isFinite(quantity) || quantity < 0) return alert('Invalid quantity');
                if (!Number.isFinite(unit_cost) || unit_cost < 0) return alert('Invalid unit cost');
                if (!Number.isFinite(tax_rate) || tax_rate < 0) return alert('Invalid tax rate');

                fetch(updateBaseUrl + id, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ name, quantity, unit, unit_cost, tax_rate }),
                }).then(async (r) => {
                    if (!r.ok) throw new Error('bad');
                    const json = await r.json();
                    // Update row display
                    row.dataset.name = json.item.name;
                    row.dataset.quantity = json.item.quantity;
                    row.dataset.unit = json.item.unit || '';
                    row.dataset.unitCost = json.item.unit_cost;
                    row.dataset.taxRate = json.item.tax_rate || 0;

                    const cells = row.children;
                    // name/description cell
                    cells[1].innerHTML = `<div class="font-semibold text-gray-900">${json.item.name}</div>`;
                    // qty
                    const qtyText = Number(json.item.quantity).toFixed(2).replace(/\.00$/, '');
                    row.querySelector('[data-col="quantity"]').textContent = `${qtyText} ${json.item.unit || ''}`;
                    // unit cost
                    row.querySelector('[data-col="unit_cost"]').textContent = formatMoney(Number(json.item.unit_cost));
                    // tax
                    row.querySelector('[data-col="tax_rate"]').textContent = Number(json.item.tax_rate) > 0 ? `${(Number(json.item.tax_rate) * 100).toFixed(3).replace(/0+$/,'').replace(/\.$/,'')}%` : '—';
                    // line total
                    row.querySelector('[data-col="line_total"]').textContent = formatMoney(Number(json.item.line_total));
                    // actions
                    row.querySelector('[data-col="actions"]').innerHTML = `
                        <button type="button" class="text-blue-600 hover:underline text-sm" data-action="edit-item" data-item-id="${json.item.id}">Edit</button>
                        <form action="{{ url('estimates/'.$estimate->id.'/items') }}/${json.item.id}" method="POST" class="inline" onsubmit="return confirm('Remove this line item?')">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button class="text-red-600 hover:underline text-sm">Delete</button>
                        </form>`;
                    row.dataset.editing = '0';

                    if (json.totals) updateSummary(json.totals);
                    showToast('Item updated', 'success');
                }).catch(() => alert('Failed to update item'));
                return;
            }

            if (removeGroupBtn) {
                const calcId = removeGroupBtn.dataset.calculationId;
                if (!calcId) return;
                fetch(removeCalcBaseUrl + calcId, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ _method: 'DELETE' }),
                }).then(async (r) => {
                    if (!r.ok) throw new Error('bad');
                    const json = await r.json();
                    if (json.totals) updateSummary(json.totals);
                    // Remove the header and all rows in this calculation group without a full reload
                    document.querySelectorAll(`tr[data-calculation-id="${calcId}"]`).forEach(el => el.remove());
                    showToast('Removed items for calculation', 'success');
                }).catch(() => location.reload());
                return;
            }
        });
        function showToast(message, type = 'info') {
            const colors = { success: 'bg-green-600', error: 'bg-red-600', info: 'bg-gray-800' };
            const el = document.createElement('div');
            el.className = `${colors[type] || colors.info} text-white px-4 py-2 rounded shadow fixed top-4 right-4 z-50 opacity-0 transition-opacity duration-300`;
            el.textContent = message;
            document.body.appendChild(el);
            requestAnimationFrame(() => el.classList.remove('opacity-0'));
            setTimeout(() => { el.classList.add('opacity-0'); setTimeout(() => el.remove(), 300); }, 2500);
        }
    });
</script>
@endpush
