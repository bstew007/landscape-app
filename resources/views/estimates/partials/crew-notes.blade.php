<section class="bg-white rounded-lg shadow p-6 space-y-4">
    <form method="POST" action="{{ route('estimates.update', $estimate) }}" class="space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm font-medium text-gray-700">Crew Notes</label>
            <p class="text-xs text-gray-500 mb-2">Internal notes for the crew. These are not visible to the client.</p>
            <textarea name="crew_notes" rows="10" class="form-textarea w-full border-brand-300 focus:ring-brand-500 focus:border-brand-500">{{ old('crew_notes', $estimate->crew_notes) }}</textarea>
            @error('crew_notes')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="flex justify-end">
            <x-brand-button type="submit">Save</x-brand-button>
        </div>
    </form>
</section>
