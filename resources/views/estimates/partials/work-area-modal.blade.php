<x-modal name="add-work-area" maxWidth="lg">
    <div class="border-b px-4 py-3">
        <h3 class="text-lg font-semibold">Add Work Area</h3>
    </div>
    <div class="p-4">
        <form method="POST" action="{{ route('estimates.areas.store', $estimate) }}" class="space-y-4" id="addWorkAreaForm" data-allow-async="1">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">Work Area Name</label>
                <input type="text" name="name" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Work Area Identifier (optional)</label>
                <input type="text" name="identifier" class="form-input w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" placeholder="e.g., A1, Zone 3">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Cost Code</label>
                <select name="cost_code_id" class="form-select w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">—</option>
                    @foreach (($costCodes ?? []) as $cc)
                        <option value="{{ $cc->id }}">{{ $cc->code }} - {{ $cc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Notes</label>
                <textarea name="description" rows="3" class="form-textarea w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500" placeholder="Details or special instructions for this area"></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'add-work-area')">Cancel</x-secondary-button>
                <x-brand-button type="submit">Save Area</x-brand-button>
            </div>
        </form>
    </div>
</x-modal>
