<x-page-header title="{{ $estimate->title }}" eyebrow="Estimate" subtitle="{{ $estimate->client->name }} · {{ $estimate->property->name ?? 'No property' }}" variant="compact">
    <x-slot:leading>
        <div class="h-12 w-12 rounded-full bg-brand-600 text-white flex items-center justify-center text-lg font-semibold shadow-sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6"><path d="M7 2h7l5 5v13a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/><path d="M14 2v5h5"/></svg>
        </div>
    </x-slot:leading>
    <x-slot:actions>
        <x-brand-button type="button" id="estimateRefreshBtn" variant="outline">Refresh</x-brand-button>
        <x-brand-button type="button" id="saveAllBtn" class="ml-2">Save All</x-brand-button>
        <x-brand-button href="{{ route('estimates.edit', $estimate) }}" variant="outline">Edit</x-brand-button>
        <form action="{{ route('estimates.destroy', $estimate) }}" method="POST" onsubmit="return confirm('Delete this estimate?');">
            @csrf
            @method('DELETE')
            <x-brand-button type="submit" variant="outline" class="border-red-300 text-red-700 hover:bg-red-50">Delete</x-brand-button>
        </form>
        @if($previewEmailRoute ?? false)
            <x-brand-button href="{{ $previewEmailRoute }}" variant="outline">Preview Email</x-brand-button>
        @endif
        <form action="{{ route('estimates.invoice', $estimate) }}" method="POST">
            @csrf
            <x-brand-button type="submit" variant="outline">Create Invoice</x-brand-button>
        </form>
        @if($printRoute ?? false)
            <x-brand-button href="{{ $printRoute }}" target="_blank" variant="outline">Print</x-brand-button>
        @endif
    </x-slot:actions>
</x-page-header>
