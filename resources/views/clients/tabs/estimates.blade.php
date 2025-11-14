<section class="bg-white rounded-lg shadow p-6 space-y-4">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900">Estimates</h2>
        <a href="{{ route('estimates.create', ['client_id' => $contact->id, 'property_id' => optional($contact->primaryProperty)->id]) }}" class="rounded bg-brand-700 text-white px-4 py-2 text-sm hover:bg-brand-800">+ New Estimate</a>
    </div>

    @if($estimates->isEmpty())
        <p class="text-sm text-gray-500">No estimates yet.</p>
    @else
        <div class="overflow-x-auto border rounded">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="text-left px-3 py-2">ID</th>
                        <th class="text-left px-3 py-2">Title</th>
                        <th class="text-left px-3 py-2">Property</th>
                        <th class="text-left px-3 py-2">Status</th>
                        <th class="text-right px-3 py-2">Amount</th>
                        <th class="text-left px-3 py-2">Created</th>
                        <th class="text-right px-3 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($estimates as $e)
                    <tr class="border-t">
                        <td class="px-3 py-2">#{{ $e->id }}</td>
                        <td class="px-3 py-2 font-semibold text-gray-900">{{ $e->title ?? 'Untitled' }}</td>
                        <td class="px-3 py-2 text-gray-700">{{ $e->property->name ?? '—' }}</td>
                        <td class="px-3 py-2 text-gray-700">{{ ucfirst($e->status ?? 'draft') }}</td>
                        <td class="px-3 py-2 text-right text-gray-900">${{ number_format($e->grand_total ?? $e->total ?? 0, 2) }}</td>
                        <td class="px-3 py-2 text-gray-700">{{ optional($e->created_at)->format('M j, Y') }}</td>
                        <td class="px-3 py-2 text-right space-x-2">
                            <a href="{{ route('estimates.show', $e) }}" class="text-blue-600 hover:underline">Open</a>
                            <a href="{{ route('estimates.print', $e) }}" target="_blank" class="text-gray-700 hover:underline">Print</a>
                            <a href="{{ route('estimates.preview-email', $e) }}" class="text-emerald-700 hover:underline">Email</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
