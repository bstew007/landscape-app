<div class="rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden">
    <div class="border-b border-gray-100 px-4 py-3">
        <h2 class="text-base font-semibold text-gray-900">Project Information</h2>
    </div>
    <div class="px-4 py-4">
        <form method="POST" action="{{ route('estimates.update', $estimate) }}" class="space-y-3">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700">Project Name</label>
                <input type="text" name="title" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="{{ old('title', $estimate->title) }}" required>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Estimate ID</label>
                    <input type="text" class="form-input w-full bg-gray-50" value="{{ $estimate->id }}" readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Estimate Date</label>
                    <input type="date" class="form-input w-full bg-gray-50" value="{{ $estimate->created_at->format('Y-m-d') }}" readonly>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Estimate Status</label>
                    <select name="status" class="form-select w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500">
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(old('status', $estimate->status ?? 'draft') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Expires On</label>
                    <input type="date" name="expires_at" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" value="{{ old('expires_at', optional($estimate->expires_at ?? null)->format('Y-m-d')) }}">
                </div>
            </div>
            <div class="flex justify-end">
                <x-brand-button type="submit">Save</x-brand-button>
            </div>
        </form>
    </div>
</div>
