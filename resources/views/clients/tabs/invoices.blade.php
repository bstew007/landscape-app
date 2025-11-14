<section class="bg-white rounded-lg shadow p-6 space-y-4">
    <h2 class="text-lg font-semibold text-gray-900">Invoices</h2>

    @if($invoices->isEmpty())
        <p class="text-sm text-gray-500">No invoices yet.</p>
    @else
        <div class="overflow-x-auto border rounded">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="text-left px-3 py-2">ID</th>
                        <th class="text-left px-3 py-2">Status</th>
                        <th class="text-right px-3 py-2">Amount</th>
                        <th class="text-left px-3 py-2">Due</th>
                        <th class="text-left px-3 py-2">Created</th>
                        <th class="text-right px-3 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($invoices as $inv)
                    <tr class="border-t">
                        <td class="px-3 py-2">#{{ $inv->id }}</td>
                        <td class="px-3 py-2 text-gray-700">{{ ucfirst($inv->status ?? 'draft') }}</td>
                        <td class="px-3 py-2 text-right text-gray-900">${{ number_format($inv->amount ?? 0, 2) }}</td>
                        <td class="px-3 py-2 text-gray-700">{{ optional($inv->due_date)->format('M j, Y') ?? '—' }}</td>
                        <td class="px-3 py-2 text-gray-700">{{ optional($inv->created_at)->format('M j, Y') }}</td>
                        <td class="px-3 py-2 text-right space-x-2">
                            @if($inv->pdf_path)
                                <a href="{{ Storage::disk('public')->url($inv->pdf_path) }}" class="text-blue-600 hover:underline">PDF</a>
                            @endif
                            @if($inv->estimate)
                                <a href="{{ route('estimates.show', $inv->estimate) }}" class="text-blue-600 hover:underline">Estimate</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
