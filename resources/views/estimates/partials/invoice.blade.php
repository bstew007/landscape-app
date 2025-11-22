<section class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-900">Invoice</h2>
        <p class="text-sm text-gray-500">Auto-generated from estimate</p>
    </div>
    @if ($estimate->invoice)
        <p class="text-sm text-gray-700"><strong>Status:</strong> {{ ucfirst($estimate->invoice->status) }}</p>
        <p class="text-sm text-gray-700"><strong>Amount:</strong> ${{ number_format($estimate->invoice->amount ?? 0, 2) }}</p>
        <p class="text-sm text-gray-700"><strong>Due:</strong> {{ optional($estimate->invoice->due_date)->format('M j, Y') ?? 'N/A' }}</p>
        @if ($estimate->invoice->pdf_path)
            <a href="{{ Storage::disk('public')->url($estimate->invoice->pdf_path) }}" class="text-brand-700 hover:text-brand-900 text-sm">Download Invoice</a>
        @endif
    @else
        <p class="text-sm text-gray-500">No invoice generated yet. Use the button above to create one.</p>
    @endif
</section>
