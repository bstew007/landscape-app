@extends('layouts.sidebar')

@section('content')
<div class="space-y-8 max-w-7xl mx-auto">
    <section class="rounded-[32px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-6 sm:p-8 shadow-2xl border border-brand-800/40 relative overflow-hidden">
        <div class="flex flex-wrap items-start gap-6">
            <div class="space-y-3 max-w-3xl">
                <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Estimates</p>
                <h1 class="text-3xl sm:text-4xl font-semibold">Update Estimate</h1>
                <p class="text-sm text-brand-100/85">Adjust the metadata before returning to the builder to tweak work areas, labor, and templates.</p>
            </div>
            <div class="ml-auto flex gap-2">
                <a href="{{ route('estimates.show', $estimate) }}" 
                   class="inline-flex items-center h-9 px-4 rounded-lg border text-sm bg-white/10 text-white border-white/40 hover:bg-white/20 transition">
                    Cancel
                </a>
                <button form="estimateEditForm" type="submit" 
                        class="inline-flex items-center h-9 px-4 rounded-lg bg-white text-brand-900 text-sm font-semibold hover:bg-brand-50 transition shadow-sm">
                    Save Changes
                </button>
            </div>
        </div>
        <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8 text-sm text-brand-100">
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Status</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ ucfirst($estimate->status ?? 'draft') }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Work Areas</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ $estimate->areas->count() }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Total</dt>
                <dd class="text-2xl font-semibold text-white mt-2">${{ number_format($estimate->grand_total > 0 ? $estimate->grand_total : $estimate->total, 2) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Last Updated</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ optional($estimate->updated_at)->diffForHumans() ?? 'Just now' }}</dd>
            </div>
        </dl>
    </section>

    <section class="rounded-[32px] bg-white shadow-2xl border border-brand-100/60 p-6 sm:p-8 space-y-6">
        @php $errorBag = session('errors'); @endphp
        @if ($errorBag?->any())
            <div class="p-4 bg-red-50 text-red-900 rounded-2xl border border-red-200 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errorBag->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="estimateEditForm" method="POST" action="{{ route('estimates.update', $estimate) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid md:grid-cols-2 gap-6">
                <!-- Left Column -->
                <div class="space-y-6">
                    <x-panel-card title="Basic Information" titleClass="text-lg font-semibold text-gray-900 mb-4" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                            <input type="text" name="title" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500"
                                   value="{{ old('title', $estimate->title) }}" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" class="form-select w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500">
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}" {{ old('status', $estimate->status) === $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Expires On</label>
                            <input type="date" name="expires_at" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500"
                                   value="{{ old('expires_at', optional($estimate->expires_at)->format('Y-m-d')) }}">
                        </div>
                    </x-panel-card>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <x-panel-card title="Client & Property" titleClass="text-lg font-semibold text-gray-900 mb-4" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Client</label>
                            <select name="client_id" class="form-select w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" required>
                                <option value="">Select client</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}" {{ old('client_id', $estimate->client_id) == $client->id ? 'selected' : '' }}>
                                        {{ $client->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Property</label>
                            <select name="property_id" class="form-select w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500">
                                <option value="">Select property</option>
                                @foreach ($clients as $client)
                                    @foreach ($client->properties as $property)
                                        <option value="{{ $property->id }}" data-client-id="{{ $client->id }}" 
                                                {{ old('property_id', $estimate->property_id) == $property->id ? 'selected' : '' }}>
                                            {{ $client->name }} – {{ $property->name }}
                                        </option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Site Visit (optional)</label>
                            <select name="site_visit_id" class="form-select w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500">
                                <option value="">Link visit</option>
                                @foreach ($siteVisits as $visit)
                                    <option value="{{ $visit->id }}" data-client-id="{{ $visit->client_id }}"
                                            {{ old('site_visit_id', $estimate->site_visit_id) == $visit->id ? 'selected' : '' }}>
                                        {{ optional($visit->client)->name }} – {{ optional($visit->visit_date)->format('M j, Y') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </x-panel-card>
                </div>
            </div>

            <x-panel-card title="Notes & Terms" titleClass="text-lg font-semibold text-gray-900 mb-4" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes / Scope</label>
                    <textarea name="notes" rows="4" class="form-textarea w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500">{{ old('notes', $estimate->notes) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Terms & Conditions</label>
                    <textarea name="terms" rows="4" class="form-textarea w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500">{{ old('terms', $estimate->terms ?? 'Prices valid for 30 days. 50% deposit due upon acceptance.') }}</textarea>
                </div>
            </x-panel-card>

            <div class="flex flex-wrap items-center justify-between gap-4 pt-4">
                <div class="text-sm text-gray-600">
                    <p class="font-medium">Line items are managed in the estimate builder.</p>
                    <p class="text-xs mt-1">After saving, return to the <a href="{{ route('estimates.show', $estimate) }}" class="text-brand-700 hover:text-brand-900 underline">estimate builder</a> to add or modify work areas and line items.</p>
                </div>
                <div class="flex items-center gap-2">
                    <x-secondary-button as="a" href="{{ route('estimates.show', $estimate) }}">
                        Cancel
                    </x-secondary-button>
                    <x-brand-button type="submit">
                        Save Changes
                    </x-brand-button>
                </div>
            </div>
        </form>
    </section>
</div>

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
        Array.from(selectEl.options).forEach((opt, idx) => {
            if (idx === 0) {
                opt.hidden = false;
                return;
            }
            const optClientId = opt.getAttribute('data-client-id') || '';
            const matches = clientId ? (optClientId === clientId) : true;
            opt.hidden = !matches;
        });
        const selOpt = selectEl.selectedOptions[0];
        if (selOpt && (selOpt.hidden || (clientId && selOpt.getAttribute('data-client-id') !== clientId))) {
            selectEl.value = '';
        }
        selectEl.disabled = !clientId;
    }

    function applyFilters() {
        const cid = getSelectedClientId();
        filterSelectByClient(propertySelect, cid);
        filterSelectByClient(visitSelect, cid);
    }

    if (clientSelect) {
        clientSelect.addEventListener('change', applyFilters);
    }
    applyFilters();
});
</script>
@endpush
@endsection
