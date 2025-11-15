<form action="{{ $route }}" method="POST" class="space-y-6 bg-white p-6 rounded shadow">
    @csrf
    @if ($method === 'PUT')
        @method('PUT')
    @endif

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Asset Name</label>
            <input id="name" name="name" type="text" class="form-input w-full mt-1"
                   value="{{ old('name', $asset->name ?? '') }}" required>
        </div>
        <div>
            <label for="type" class="block text-sm font-medium text-gray-700">Category</label>
            <select id="type" name="type" class="form-select w-full mt-1" required>
                @foreach (\App\Models\Asset::TYPES as $type)
                    <option value="{{ $type }}" @selected(old('type', $asset->type ?? 'vehicle') === $type)>{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="identifier" class="block text-sm font-medium text-gray-700">Serial / VIN</label>
            <input id="identifier" name="identifier" type="text" class="form-input w-full mt-1"
                   value="{{ old('identifier', $asset->identifier ?? '') }}">
        </div>
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
            <select id="status" name="status" class="form-select w-full mt-1" required>
                @foreach (\App\Models\Asset::STATUSES as $status)
                    <option value="{{ $status }}" @selected(old('status', $asset->status ?? 'active') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="assigned_to" class="block text-sm font-medium text-gray-700">Assigned To</label>
            <input id="assigned_to" name="assigned_to" type="text" class="form-input w-full mt-1"
                   value="{{ old('assigned_to', $asset->assigned_to ?? '') }}">
        </div>
        <div>
            <label for="mileage_hours" class="block text-sm font-medium text-gray-700">Mileage / Hours</label>
            <input id="mileage_hours" name="mileage_hours" type="number" class="form-input w-full mt-1"
                   value="{{ old('mileage_hours', $asset->mileage_hours ?? '') }}">
        </div>
        <div>
            <label for="purchase_date" class="block text-sm font-medium text-gray-700">Purchase Date</label>
            <input id="purchase_date" name="purchase_date" type="date" class="form-input w-full mt-1"
                   value="{{ old('purchase_date', optional($asset->purchase_date ?? null)->format('Y-m-d')) }}">
        </div>
        <div>
            <label for="purchase_price" class="block text-sm font-medium text-gray-700">Purchase Price</label>
            <input id="purchase_price" name="purchase_price" type="number" step="0.01" class="form-input w-full mt-1"
                   value="{{ old('purchase_price', $asset->purchase_price ?? '') }}">
        </div>
        <div>
            <label for="next_service_date" class="block text-sm font-medium text-gray-700">Next Service Date</label>
            <input id="next_service_date" name="next_service_date" type="date" class="form-input w-full mt-1"
                   value="{{ old('next_service_date', optional($asset->next_service_date ?? null)->format('Y-m-d')) }}">
        </div>
    </div>

    <div>
        <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
        <textarea id="notes" name="notes" rows="4" class="form-textarea w-full mt-1"
                  placeholder="Maintenance intervals, inspection reminders, etc.">{{ old('notes', $asset->notes ?? '') }}</textarea>
    </div>

    <div class="flex items-center justify-between">
        <x-brand-button type="submit">Save Asset</x-brand-button>
        <x-brand-button href="{{ route('assets.index') }}" variant="outline">Cancel</x-brand-button>
    </div>
</form>
