<section class="bg-white rounded-lg shadow p-6 space-y-4">
    <div class="flex items-center justify-between gap-3 flex-wrap">
        <h2 class="text-lg font-semibold text-gray-900">Invoices</h2>
        <div class="flex items-center gap-2">
            @if(!empty($estimates) && $estimates->isNotEmpty())
                <form id="createInvoiceForm" method="POST" action="#" class="flex items-center gap-2">
                    @csrf
                    <select id="createInvoiceEstimate" class="form-select text-sm border-brand-300 focus:ring-brand-500 focus:border-brand-500 min-w-[220px]">
                        <option value="">Select estimate…</option>
                        @foreach($estimates->take(25) as $est)
                            <option value="{{ $est->id }}">#{{ $est->id }} · {{ $est->title }}</option>
                        @endforeach
                    </select>
                    <x-brand-button type="button" id="createInvoiceBtn" size="sm">Create Invoice</x-brand-button>
                </form>
            @endif
            @if(isset($invoices) && $invoices->isNotEmpty())
                <form method="POST" action="{{ route('invoices.qbo.refresh', $invoices->first()) }}">
                    @csrf
                    <x-brand-button type="submit" variant="outline" size="sm">Refresh QBO</x-brand-button>
                </form>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('createInvoiceBtn');
            const sel = document.getElementById('createInvoiceEstimate');
            const form = document.getElementById('createInvoiceForm');
            if (btn && sel && form) {
                btn.addEventListener('click', () => {
                    const id = sel.value;
                    if (!id) { alert('Please select an estimate first.'); return; }
                    form.action = `{{ url('estimates') }}/${id}/invoice`;
                    form.submit();
                });
            }
        });
    </script>
    @endpush

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
                        <th class="text-right px-3 py-2">QBO Balance</th>
                        <th class="text-left px-3 py-2">Due</th>
                        <th class="text-left px-3 py-2">Created</th>
                        <th class="text-right px-3 py-2">QBO</th>
                        <th class="text-right px-3 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($invoices as $inv)
                    <tr class="border-t">
                        <td class="px-3 py-2">#{{ $inv->id }}</td>
                        <td class="px-3 py-2 text-gray-700">{{ ucfirst($inv->status ?? 'draft') }}</td>
                        <td class="px-3 py-2 text-right text-gray-900">${{ number_format($inv->amount ?? 0, 2) }}</td>
                        <td class="px-3 py-2 text-right text-gray-900">{{ isset($inv->qbo_balance) ? ('$'.number_format($inv->qbo_balance,2)) : '—' }}</td>
                        <td class="px-3 py-2 text-gray-700">{{ optional($inv->due_date)->format('M j, Y') ?? '—' }}</td>
                        <td class="px-3 py-2 text-gray-700">{{ optional($inv->created_at)->format('M j, Y') }}</td>
                        <td class="px-3 py-2 text-right">
                            @if($inv->qbo_invoice_id)
                                <span class="inline-flex items-center px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs">Linked</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-50 text-gray-700 border border-gray-200 text-xs">Not Linked</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right space-x-2">
                            @if(!$inv->qbo_invoice_id)
                                <form method="POST" action="{{ route('invoices.qbo.create', $inv) }}" class="inline">
                                    @csrf
                                    <x-brand-button type="submit" size="xs">Create in QBO</x-brand-button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('invoices.qbo.refresh', $inv) }}" class="inline">
                                    @csrf
                                    <x-brand-button type="submit" variant="outline" size="xs">Refresh</x-brand-button>
                                </form>
                            @endif
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
