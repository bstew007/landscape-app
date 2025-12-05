<form action="{{ $route }}" method="POST" class="space-y-6">
    @csrf
    @if ($method === 'PUT')
        @method('PUT')
    @endif

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label for="name" class="block text-sm font-medium text-brand-800 mb-1">Asset Name</label>
            <input id="name" name="name" type="text" class="form-input w-full"
                   value="{{ old('name', $asset->name ?? '') }}" required>
        </div>
        <div>
            <label for="model" class="block text-sm font-medium text-brand-800 mb-1">Model</label>
            <input id="model" name="model" type="text" class="form-input w-full"
                   value="{{ old('model', $asset->model ?? '') }}" placeholder="e.g., F-150, Bobcat S650">
        </div>
        <div>
            <label for="type" class="block text-sm font-medium text-brand-800 mb-1">Category</label>
            <select id="type" name="type" class="form-select w-full" required>
                @foreach (\App\Models\Asset::TYPES as $type)
                    <option value="{{ $type }}" @selected(old('type', $asset->type ?? 'vehicle') === $type)>{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="identifier" class="block text-sm font-medium text-brand-800 mb-1">Serial / VIN</label>
            <input id="identifier" name="identifier" type="text" class="form-input w-full"
                   value="{{ old('identifier', $asset->identifier ?? '') }}">
        </div>
        <div>
            <label for="status" class="block text-sm font-medium text-brand-800 mb-1">Status</label>
            <select id="status" name="status" class="form-select w-full" required>
                @foreach (\App\Models\Asset::STATUSES as $status)
                    <option value="{{ $status }}" @selected(old('status', $asset->status ?? 'active') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="assigned_to" class="block text-sm font-medium text-brand-800 mb-1">Assigned To</label>
            <select id="assigned_to" name="assigned_to" class="form-select w-full">
                <option value="">-- Select Driver --</option>
                @foreach($drivers as $driver)
                    <option value="{{ $driver->name }}" @selected(old('assigned_to', $asset->assigned_to ?? '') === $driver->name)>{{ $driver->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="mileage_hours" class="block text-sm font-medium text-brand-800 mb-1">Mileage / Hours</label>
            <input id="mileage_hours" name="mileage_hours" type="number" class="form-input w-full"
                   value="{{ old('mileage_hours', $asset->mileage_hours ?? '') }}">
        </div>
        <div>
            <label for="purchase_date" class="block text-sm font-medium text-brand-800 mb-1">Purchase Date</label>
            <input id="purchase_date" name="purchase_date" type="date" class="form-input w-full"
                   value="{{ old('purchase_date', optional($asset->purchase_date ?? null)->format('Y-m-d')) }}">
        </div>
        <div>
            <label for="purchase_price" class="block text-sm font-medium text-brand-800 mb-1">Purchase Price</label>
            <input id="purchase_price" name="purchase_price" type="number" step="0.01" class="form-input w-full"
                   value="{{ old('purchase_price', $asset->purchase_price ?? '') }}">
        </div>
        <div>
            <label for="next_service_date" class="block text-sm font-medium text-brand-800 mb-1">Next Service Date</label>
            <input id="next_service_date" name="next_service_date" type="date" class="form-input w-full"
                   value="{{ old('next_service_date', optional($asset->next_service_date ?? null)->format('Y-m-d')) }}">
        </div>
    </div>

    <div>
        <label for="notes" class="block text-sm font-medium text-brand-800 mb-1">Notes</label>
        <textarea id="notes" name="notes" rows="4" class="form-textarea w-full"
                  placeholder="Maintenance intervals, inspection reminders, etc.">{{ old('notes', $asset->notes ?? '') }}</textarea>
    </div>

    <div class="flex items-center justify-between pt-4 border-t border-brand-100">
        <x-brand-button type="submit">
            <svg class="h-4 w-4 mr-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Save Asset
        </x-brand-button>
        <x-brand-button href="{{ route('assets.index') }}" variant="outline">
            <svg class="h-4 w-4 mr-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Cancel
        </x-brand-button>
    </div>
</form>
