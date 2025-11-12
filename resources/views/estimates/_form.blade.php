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
            <p class="text-xs text-gray-500 mt-1">Link a visit to pull scope templates + calculator data after saving.</p>
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

    <div class="bg-blue-50 border border-blue-100 rounded p-4 text-sm text-blue-900">
        <p class="font-semibold">Line Items Managed After Save</p>
        <p class="mt-1">Use this form to capture client + scope info. After saving, the estimate builder lets you import calculator outputs or add catalog materials/labor with rich controls.</p>
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
@endpush

