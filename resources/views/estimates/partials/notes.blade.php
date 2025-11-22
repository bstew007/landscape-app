<section class="bg-white rounded-lg shadow p-6 space-y-4">
    <form method="POST" action="{{ route('estimates.update', $estimate) }}" class="space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm font-medium text-gray-700">Client Notes</label>
            <textarea name="notes" rows="6" class="form-textarea w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500">{{ old('notes', $estimate->notes) }}</textarea>
            @error('notes')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Terms & Conditions</label>
            <textarea name="terms" rows="6" class="form-textarea w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500">{{ old('terms', $estimate->terms) }}</textarea>
            @error('terms')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="flex justify-end">
            <x-brand-button type="submit">Save</x-brand-button>
        </div>
    </form>
</section>
