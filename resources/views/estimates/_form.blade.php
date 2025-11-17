<form id="estimateForm" action="{{ $route }}" method="POST" class="space-y-6 bg-white p-6 rounded shadow">
    @csrf
    @if ($method === 'PUT')
        @method('PUT')
    @endif

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Estimate Type</label>
            <select name="estimate_type" class="form-select w-full mt-1 border-brand-300 focus:ring-brand-500 focus:border-brand-500" required>
                @php($typeVal = old('estimate_type', $estimate->estimate_type ?? 'design_build'))
                <option value="design_build" {{ $typeVal==='design_build' ? 'selected' : '' }}>Design/Build</option>
                <option value="maintenance" {{ $typeVal==='maintenance' ? 'selected' : '' }}>Maintenance</option>
            </select>
            <p class="text-xs text-gray-500 mt-1">Design/Build uses Work Areas; Maintenance uses Services.</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Title</label>
            <input type="text" name="title" class="form-input w-full mt-1 border-brand-300 focus:ring-brand-500 focus:border-brand-500"
                   value="{{ old('title', $estimate->title ?? 'New Landscape Estimate') }}" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <select name="status" class="form-select w-full mt-1 border-brand-300 focus:ring-brand-500 focus:border-brand-500">
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" {{ old('status', $estimate->status ?? 'draft') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Division</label>
            <select name="division_id" class="form-select w-full mt-1 border-brand-300 focus:ring-brand-500 focus:border-brand-500">
                <option value="">—</option>
                @foreach (($divisions ?? []) as $d)
                    <option value="{{ $d->id }}" {{ old('division_id', $estimate->division_id ?? null) == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Cost Code</label>
            <select name="cost_code_id" class="form-select w-full mt-1 border-brand-300 focus:ring-brand-500 focus:border-brand-500" required>
                <option value="">—</option>
                @forelse (($costCodes ?? []) as $cc)
                    <option value="{{ $cc->id }}">{{ $cc->code }} - {{ $cc->name }}</option>
                @empty
                    <option value="" disabled>(No mapped Cost Codes found)</option>
                @endforelse
            </select>
            <input type="hidden" id="preselected_cost_code_id" value="{{ old('cost_code_id', $estimate->cost_code_id ?? '') }}">
            @if(empty($costCodes) || (is_iterable($costCodes) && count($costCodes) === 0))
                <p class="text-xs text-amber-700 mt-1">No mapped Cost Codes available. Add one under Settings → Estimates → Cost Codes.</p>
            @endif
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Client</label>
            <select name="client_id" class="form-select w-full mt-1 border-brand-300 focus:ring-brand-500 focus:border-brand-500" required>
                <option value="">Select client</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}" {{ old('client_id', $estimate->client_id ?? '') == $client->id ? 'selected' : '' }}>
                        {{ $client->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Property</label>
            <select name="property_id" class="form-select w-full mt-1 border-brand-300 focus:ring-brand-500 focus:border-brand-500">
                <option value="">Select property</option>
                @foreach ($clients as $client)
                    @foreach ($client->properties as $property)
                        <option value="{{ $property->id }}" data-client-id="{{ $client->id }}" {{ old('property_id', $estimate->property_id ?? '') == $property->id ? 'selected' : '' }}>
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
                class="form-select w-full mt-1 border-brand-300 focus:ring-brand-500 focus:border-brand-500"
            >
                <option value="">Link visit</option>
                @foreach ($siteVisits as $visit)
                    <option value="{{ $visit->id }}"
                            data-client-id="{{ $visit->client_id }}"
                            data-scope-template="{{ base64_encode($visit->scope_note_template ?? '') }}"
                            {{ old('site_visit_id', $estimate->site_visit_id ?? '') == $visit->id ? 'selected' : '' }}>
                        {{ optional($visit->client)->name }} – {{ optional($visit->visit_date)->format('M j, Y') }}
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-1">Link a visit to pull scope templates + calculator data after saving.</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Expires On</label>
            <input type="date" name="expires_at" class="form-input w-full mt-1 border-brand-300 focus:ring-brand-500 focus:border-brand-500"
                   value="{{ old('expires_at', optional($estimate->expires_at ?? null)->format('Y-m-d')) }}">
        </div>
        @if (($method ?? 'POST') === 'PUT')
        <div>
            <label class="block text-sm font-medium text-gray-700">Total</label>
            <input type="number" step="0.01" name="total" class="form-input w-full mt-1 border-brand-300 focus:ring-brand-500 focus:border-brand-500"
                   value="{{ old('total', $estimate->total ?? 0) }}" readonly>
        </div>
        @endif
    </div>

    <div class="bg-brand-50 border border-brand-100 rounded p-4 text-sm text-brand-900">
        <p class="font-semibold">Line Items Managed After Save</p>
        <p class="mt-1">Use this form to capture client + scope info. After saving, the estimate builder lets you import calculator outputs or add catalog materials/labor with rich controls.</p>
    </div>

    @php
        $scopeNoteTemplate = $scopeNoteTemplate ?? '';
    @endphp

    <div>
        <label class="block text-sm font-medium text-gray-700">Notes / Scope</label>
        <textarea name="notes"
                  rows="4"
                  class="form-textarea w-full mt-1 border-brand-300 focus:ring-brand-500 focus:border-brand-500"
                  data-initial-template="{{ base64_encode($scopeNoteTemplate ?? '') }}">{{ old('notes', $estimate->notes ?? '') }}</textarea>
    </div>

    @php
        $scopeSummaries = $scopeSummaries ?? [];
    @endphp

    @if (!empty($scopeSummaries))
        <div class="space-y-4">
            <h3 class="text-sm font-semibold text-brand-800">Calculator Measurements</h3>
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
        <textarea name="terms" rows="4" class="form-textarea w-full mt-1 border-brand-300 focus:ring-brand-500 focus:border-brand-500">{{ old('terms', $estimate->terms ?? 'Prices valid for 30 days. 50% deposit due upon acceptance.') }}</textarea>
    </div>

    <div class="flex justify-end gap-2">
        <x-secondary-button as="a" href="{{ route('estimates.index') }}">Cancel</x-secondary-button>
        <x-brand-button type="submit">Save Estimate</x-brand-button>
    </div>
</form>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const clientSelect = document.querySelector('select[name="client_id"]');
        const propertySelect = document.querySelector('select[name="property_id"]');
        const visitSelect = document.querySelector('select[name="site_visit_id"]');

        function getSelectedClientId() {
            return (clientSelect && clientSelect.value) ? String(clientSelect.value) : '';
        }

        function filterSelectByClient(selectEl, clientId) {
            if (!selectEl) return;
            let hasVisible = false;
            Array.from(selectEl.options).forEach((opt, idx) => {
                if (idx === 0) {
                    opt.hidden = false; // keep placeholder visible
                    return;
                }
                const optClientId = opt.getAttribute('data-client-id') || '';
                const matches = clientId ? (optClientId === clientId) : true;
                opt.hidden = !matches;
                if (matches) hasVisible = true;
            });
            // Clear selection if current option is hidden or not matching
            const selOpt = selectEl.selectedOptions[0];
            if (selOpt && (selOpt.hidden || (clientId && selOpt.getAttribute('data-client-id') !== clientId))) {
                selectEl.value = '';
            }
            // Disable if no client selected; enable otherwise
            selectEl.disabled = !clientId;
        }

        function applyFilters() {
            const cid = getSelectedClientId();
            filterSelectByClient(propertySelect, cid);
            filterSelectByClient(visitSelect, cid);
            // If a site visit carries a scope template for this client and notes field is empty, we could auto-fill after save
        }

        if (clientSelect) {
            clientSelect.addEventListener('change', applyFilters);
        }
        // Initial run on load (handles edit form pre-selection or query-param prefill)
        applyFilters();

        // Apply pre-selected Cost Code without inline Blade conditionals
        const ccSelect = document.querySelector('select[name="cost_code_id"]');
        const ccHidden = document.getElementById('preselected_cost_code_id');
        if (ccSelect && ccHidden) {
            const pre = (ccHidden.value || '').toString();
            if (pre) ccSelect.value = pre;
        }
    });
</script>
@endpush

