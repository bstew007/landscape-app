@php
    $designBuild = old('estimate_type', $estimate->estimate_type ?? 'design_build') === 'design_build';
@endphp

<form action="{{ $route }}" method="POST" class="space-y-8">
    @csrf
    @if (($method ?? 'POST') === 'PUT')
        @method('PUT')
    @endif

    <section class="rounded-[28px] border border-brand-100/70 bg-white shadow-sm">
        <div class="px-5 py-4 border-b border-brand-100/70 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold text-brand-500 uppercase tracking-wide">Project Setup</h2>
                <p class="text-xs text-brand-400">Choose the type, client, and property before building work areas.</p>
            </div>
            @if($designBuild)
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200">Design/Build Flow</span>
            @endif
        </div>
        <div class="p-5 space-y-4">
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs uppercase tracking-wide text-brand-400">Estimate Type</label>
                    <select name="estimate_type" class="form-select w-full mt-1 border-brand-200 rounded-xl focus:ring-brand-500 focus:border-brand-500" required>
                        <option value="design_build" @selected(old('estimate_type', $estimate->estimate_type ?? 'design_build') === 'design_build')>Design/Build</option>
                        <option value="maintenance" @selected(old('estimate_type', $estimate->estimate_type ?? '') === 'maintenance')>Service / Maintenance</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-wide text-brand-400">Title</label>
                    <input type="text" name="title" class="form-input w-full mt-1 border-brand-200 rounded-xl focus:ring-brand-500 focus:border-brand-500" value="{{ old('title', $estimate->title ?? '') }}" required>
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-wide text-brand-400">Status</label>
                    <select name="status" class="form-select w-full mt-1 border-brand-200 rounded-xl focus:ring-brand-500 focus:border-brand-500" required>
                        @foreach (['draft','pending','sent','approved','rejected'] as $status)
                            <option value="{{ $status }}" @selected(old('status', $estimate->status ?? 'draft') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-wide text-brand-400">Division</label>
                    <select name="division_id" class="form-select w-full mt-1 border-brand-200 rounded-xl focus:ring-brand-500 focus:border-brand-500">
                        <option value="">Select division</option>
                        @foreach (($divisions ?? []) as $d)
                            <option value="{{ $d->id }}" @selected(old('division_id', $estimate->division_id ?? null) == $d->id)>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs uppercase tracking-wide text-brand-400">Client</label>
                    <select name="client_id" class="form-select w-full mt-1 border-brand-200 rounded-xl focus:ring-brand-500 focus:border-brand-500" required>
                        <option value="">Select client</option>
                        @foreach (($clients ?? []) as $client)
                            <option value="{{ $client->id }}" @selected(old('client_id', $estimate->client_id ?? null) == $client->id)>{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-wide text-brand-400">Expires On</label>
                    <input type="date" name="expires_at" class="form-input w-full mt-1 border-brand-200 rounded-xl focus:ring-brand-500 focus:border-brand-500" value="{{ old('expires_at', optional($estimate->expires_at ?? null)->format('Y-m-d')) }}">
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs uppercase tracking-wide text-brand-400">Property</label>
                    <select name="property_id" class="form-select w-full mt-1 border-brand-200 rounded-xl focus:ring-brand-500 focus:border-brand-500">
                        <option value="">Select property</option>
                        @foreach (($clients ?? []) as $client)
                            @foreach (($client->properties ?? []) as $property)
                                <option value="{{ $property->id }}" @selected(old('property_id', $estimate->property_id ?? null) == $property->id)>{{ $client->name }} — {{ $property->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-wide text-brand-400">Site Visit (optional)</label>
                    <select name="site_visit_id" class="form-select w-full mt-1 border-brand-200 rounded-xl focus:ring-brand-500 focus:border-brand-500">
                        <option value="">Link visit</option>
                        @foreach (($siteVisits ?? []) as $visit)
                            <option value="{{ $visit->id }}" @selected(old('site_visit_id', $estimate->site_visit_id ?? null) == $visit->id)>{{ optional($visit->client)->name }} — {{ optional($visit->visit_date)->format('M j, Y') }}</option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-brand-400 mt-1">Linked visits unlock calculator imports and site photos on the next screen.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-[28px] border border-brand-100/70 bg-white shadow-sm">
        <div class="px-5 py-4 border-b border-brand-100/70 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold text-brand-500 uppercase tracking-wide">Billing Defaults</h2>
                <p class="text-xs text-brand-400">Cost codes must map to QBO items so invoices sync without editing.</p>
            </div>
        </div>
        <div class="p-5 space-y-4">
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs uppercase tracking-wide text-brand-400">Cost Code</label>
                    <select name="cost_code_id" class="form-select w-full mt-1 border-brand-200 rounded-xl focus:ring-brand-500 focus:border-brand-500" required>
                        <option value="">Select cost code</option>
                        @foreach (($costCodes ?? []) as $cc)
                            @php $hasQboItem = !empty($cc->qbo_item_id); @endphp
                            <option value="{{ $cc->id }}" @selected(old('cost_code_id', $estimate->cost_code_id ?? null) == $cc->id)">
                                {{ $cc->code }} — {{ $cc->name }} {{ $hasQboItem ? '(QBO ready)' : '(needs QBO item)' }}
                            </option>
                        @endforeach
                    </select>
                    @if (empty($costCodes) || (is_iterable($costCodes) && count($costCodes) === 0))
                        <p class="text-xs text-amber-700 mt-1">No mapped cost codes available. Add one under Settings → Estimates → Cost Codes.</p>
                    @else
                        <p class="text-[11px] mt-1 text-brand-400">Each work area can override this default, but invoices require cost codes linked to QBO items.</p>
                    @endif
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-wide text-brand-400">Division</label>
                    <select name="division_id" class="form-select w-full mt-1 border-brand-200 rounded-xl focus:ring-brand-500 focus:border-brand-500">
                        <option value="">Select division</option>
                        @foreach (($divisions ?? []) as $d)
                            <option value="{{ $d->id }}" @selected(old('division_id', $estimate->division_id ?? null) == $d->id)>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-[28px] border border-brand-100/70 bg-white shadow-sm">
        <div class="px-5 py-4 border-b border-brand-100/70">
            <h2 class="text-sm font-semibold text-brand-500 uppercase tracking-wide">Narrative & Terms</h2>
        </div>
        <div class="p-5 space-y-4">
            <div>
                <label class="block text-xs uppercase tracking-wide text-brand-400">Notes / Scope</label>
                <textarea name="notes" rows="4" class="form-textarea w-full mt-1 border-brand-200 rounded-xl focus:ring-brand-500 focus:border-brand-500">{{ old('notes', $estimate->notes ?? '') }}</textarea>
            </div>
            <div>
                <label class="block text-xs uppercase tracking-wide text-brand-400">Terms & Conditions</label>
                <textarea name="terms" rows="4" class="form-textarea w-full mt-1 border-brand-200 rounded-xl focus:ring-brand-500 focus:border-brand-500">{{ old('terms', $estimate->terms ?? '') }}</textarea>
            </div>
        </div>
    </section>

    <section class="rounded-[28px] border border-dashed border-brand-200 bg-brand-50/40 p-5 text-sm text-brand-700">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <p class="font-semibold">Next Step: Build Work Areas</p>
                <p class="text-sm text-brand-500">After saving, use “Add Items” on the estimate to load labor & material catalogs, templates, and site-visit data into each work area. Work areas drive job costing and QBO invoices.</p>
            </div>
            <div class="flex gap-2">
                <x-secondary-button as="a" href="{{ route('calculator.templates.gallery') }}" size="sm">Open Templates</x-secondary-button>
                <x-secondary-button as="a" href="{{ route('site-visit.select') }}" size="sm">Link Site Visit</x-secondary-button>
            </div>
        </div>
    </section>

    <div class="flex justify-end gap-2">
        <x-secondary-button as="a" href="{{ route('estimates.index') }}">Cancel</x-secondary-button>
        <x-brand-button type="submit">Save Estimate</x-brand-button>
    </div>
</form>
