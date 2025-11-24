<section class="bg-white rounded-lg shadow p-6 space-y-4">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900">Estimates</h2>
        <a href="{{ route('estimates.create', ['client_id' => $contact->id, 'property_id' => optional($contact->primaryProperty)->id]) }}" class="rounded bg-brand-700 text-white px-4 py-2 text-sm hover:bg-brand-800">+ New Estimate</a>
    </div>

    @if($estimates->isEmpty())
        <p class="text-sm text-gray-500">No estimates yet.</p>
    @else
        <div class="overflow-x-auto border border-brand-200 rounded">
            <table class="w-full text-sm border-collapse">
                <thead class="bg-brand-50 text-xs uppercase text-brand-600">
                    <tr>
                        <th class="text-left px-3 py-2 border border-brand-200 bg-brand-100">ID</th>
                        <th class="text-left px-3 py-2 border border-brand-200 bg-brand-100">Title</th>
                        <th class="text-left px-3 py-2 border border-brand-200 bg-brand-100">Property</th>
                        <th class="text-left px-3 py-2 border border-brand-200 bg-brand-100">Status</th>
                        <th class="text-right px-3 py-2 border border-brand-200 bg-brand-100">Amount</th>
                        <th class="text-left px-3 py-2 border border-brand-200 bg-brand-100">Created</th>
                        <th class="text-right px-3 py-2 border border-brand-200 bg-brand-100">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($estimates as $e)
                    @php
                        $rowShade = $loop->even ? 'bg-brand-50/70' : 'bg-white';
                        $status = strtolower($e->status ?? 'draft');
                        $statusColors = [
                            'draft' => 'bg-brand-50 text-brand-700 border border-brand-200',
                            'pending' => 'bg-amber-50 text-amber-800 border border-amber-200',
                            'sent' => 'bg-blue-50 text-blue-800 border border-blue-200',
                            'approved' => 'bg-accent-50 text-accent-800 border border-accent-200',
                            'rejected' => 'bg-red-50 text-red-800 border border-red-200',
                        ];
                        $statusClass = $statusColors[$status] ?? 'bg-brand-50 text-brand-700 border border-brand-200';
                    @endphp
                    <tr class="{{ $rowShade }}">
                        <td class="px-3 py-2 border border-brand-200">#{{ $e->id }}</td>
                        <td class="px-3 py-2 border border-brand-200 font-semibold text-brand-900">{{ $e->title ?? 'Untitled' }}</td>
                        <td class="px-3 py-2 border border-brand-200 text-brand-700">{{ $e->property->name ?? '-' }}</td>
                        <td class="px-3 py-2 border border-brand-200">
                            <span class="badge text-xs {{ $statusClass }}">{{ ucfirst($status) }}</span>
                        </td>
                        <td class="px-3 py-2 border border-brand-200 text-right text-brand-900">${{ number_format($e->grand_total > 0 ? $e->grand_total : $e->total, 2) }}</td>
                        <td class="px-3 py-2 border border-brand-200 text-brand-700">{{ optional($e->created_at)->format('M j, Y') }}</td>
                        <td class="px-3 py-2 border border-brand-200 text-right space-x-2">
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
