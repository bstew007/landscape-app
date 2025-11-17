<form action="{{ $route }}" method="POST" class="space-y-6 bg-white p-6 rounded shadow">
    @csrf
    @if (($method ?? 'POST') === 'PUT')
        @method('PUT')
    @endif

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Estimate Type</label>
            <select name="estimate_type" class="form-select w-full mt-1 border-brand-300 focus:ring-brand-500 focus:border-brand-500" required>
                <option value="design_build" selected>Design/Build</option>
                <option value="maintenance">Maintenance</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Title</label>
            <input type="text" name="title" class="form-input w-full mt-1 border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="{{ old('title', $estimate->title ?? '') }}" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <select name="status" class="form-select w-full mt-1 border-brand-300 focus:ring-brand-500 focus:border-brand-500" required>
                <option value="draft" selected>Draft</option>
                <option value="pending">Pending</option>
                <option value="sent">Sent</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Division</label>
            <select name="division_id" class="form-select w-full mt-1 border-brand-300 focus:ring-brand-500 focus:border-brand-500">
                <option value="">—</option>
                @foreach (($divisions ?? []) as $d)
                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Cost Code</label>
            <select name="cost_code_id" class="form-select w-full mt-1 border-brand-300 focus:ring-brand-500 focus:border-brand-500" required>
                <option value="">—</option>
                @foreach (($costCodes ?? []) as $cc)
                    <option value="{{ $cc->id }}">{{ $cc->code }} - {{ $cc->name }}</option>
                @endforeach
            </select>
            @if (empty($costCodes) || (is_iterable($costCodes) && count($costCodes) === 0))
                <p class="text-xs text-amber-700 mt-1">No mapped Cost Codes available. Add one under Settings → Estimates → Cost Codes.</p>
            @endif
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Client</label>
            <select name="client_id" class="form-select w-full mt-1 border-brand-300 focus:ring-brand-500 focus:border-brand-500" required>
                <option value="">Select client</option>
                @foreach (($clients ?? []) as $client)
                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Property</label>
            <select name="property_id" class="form-select w-full mt-1 border-brand-300 focus:ring-brand-500 focus:border-brand-500">
                <option value="">Select property</option>
                @foreach (($clients ?? []) as $client)
                    @foreach (($client->properties ?? []) as $property)
                        <option value="{{ $property->id }}">{{ $client->name }} – {{ $property->name }}</option>
                    @endforeach
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Site Visit (optional)</label>
            <select name="site_visit_id" class="form-select w-full mt-1 border-brand-300 focus:ring-brand-500 focus:border-brand-500">
                <option value="">Link visit</option>
                @foreach (($siteVisits ?? []) as $visit)
                    <option value="{{ $visit->id }}">{{ optional($visit->client)->name }} – {{ optional($visit->visit_date)->format('M j, Y') }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Expires On</label>
            <input type="date" name="expires_at" class="form-input w-full mt-1 border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="{{ old('expires_at', optional($estimate->expires_at ?? null)->format('Y-m-d')) }}">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Notes / Scope</label>
        <textarea name="notes" rows="4" class="form-textarea w-full mt-1 border-brand-300 focus:ring-brand-500 focus:border-brand-500">{{ old('notes', $estimate->notes ?? '') }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Terms & Conditions</label>
        <textarea name="terms" rows="4" class="form-textarea w-full mt-1 border-brand-300 focus:ring-brand-500 focus:border-brand-500">{{ old('terms', $estimate->terms ?? '') }}</textarea>
    </div>

    <div class="flex justify-end gap-2">
        <x-secondary-button as="a" href="{{ route('estimates.index') }}">Cancel</x-secondary-button>
        <x-brand-button type="submit">Save Estimate</x-brand-button>
    </div>
</form>
