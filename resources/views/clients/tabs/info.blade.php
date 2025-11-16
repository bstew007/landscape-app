<section class="bg-white rounded-lg shadow p-6 space-y-4">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900">Contact Information</h2>
        <div class="flex items-center gap-2">
            @if ($contact->qbo_customer_id)
                <span class="inline-flex items-center px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs">QBO Linked</span>
                @if ($contact->qbo_last_synced_at && $contact->updated_at && $contact->updated_at->gt($contact->qbo_last_synced_at))
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 text-xs">Needs Sync</span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs">Synced</span>
                @endif
                <form method="POST" action="{{ route('contacts.qbo.sync', $contact) }}">
                    @csrf
                    <x-brand-button type="submit" variant="outline" size="sm">Sync to QBO</x-brand-button>
                </form>
                <form method="POST" action="{{ route('contacts.qbo.refresh', $contact) }}">
                    @csrf
                    <x-brand-button type="submit" variant="outline" size="sm">Refresh from QBO</x-brand-button>
                </form>
            @else
                <details class="relative">
                    <summary class="list-none inline-flex items-center h-8 px-3 rounded font-medium text-xs whitespace-nowrap border border-brand-600 text-brand-700 cursor-pointer select-none hover:bg-brand-50">Link to QBO</summary>
                    <div class="absolute right-0 mt-2 w-[420px] max-w-[90vw] bg-white border rounded shadow z-10 p-3">
                        <form method="GET" action="{{ route('contacts.qbo.search') }}" class="flex gap-2 items-center mb-2">
                            <input type="hidden" name="list" value="1" />
                            <input type="text" name="q" placeholder="Search QBO customers by name" class="form-input flex-1">
                            <x-brand-button type="submit" variant="outline" size="sm">Open Import</x-brand-button>
                        </form>
                        <form method="POST" action="{{ route('contacts.qbo.link', $contact) }}" class="flex gap-2 items-center">
                            @csrf
                            <input type="text" name="qbo_customer_id" placeholder="QBO Customer ID (e.g., 123)" class="form-input flex-1">
                            <x-brand-button type="submit" size="sm">Link</x-brand-button>
                        </form>
                    </div>
                </details>
            @endif
        </div>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 text-sm">
        <div>
            <p class="text-xs uppercase text-gray-500">First Name</p>
            <p class="font-semibold text-gray-900">{{ $contact->first_name ?? '—' }}</p>
        </div>
        <div>
            <p class="text-xs uppercase text-gray-500">Last Name</p>
            <p class="font-semibold text-gray-900">{{ $contact->last_name ?? '—' }}</p>
        </div>
        <div>
            <p class="text-xs uppercase text-gray-500">Company</p>
            <p class="font-semibold text-gray-900">{{ $contact->company_name ?? '—' }}</p>
        </div>
        <div>
            <p class="text-xs uppercase text-gray-500">Email</p>
            <p class="font-semibold text-gray-900">{{ $contact->email ?? '—' }}</p>
        </div>
        <div>
            <p class="text-xs uppercase text-gray-500">Alt Email</p>
            <p class="font-semibold text-gray-900">{{ $contact->email2 ?? '—' }}</p>
        </div>
        <div>
            <p class="text-xs uppercase text-gray-500">Phone</p>
            <p class="font-semibold text-gray-900">{{ $contact->phone ?? '—' }}</p>
        </div>
        <div>
            <p class="text-xs uppercase text-gray-500">Mobile</p>
            <p class="font-semibold text-gray-900">{{ $contact->mobile ?? '—' }}</p>
        </div>
        <div>
            <p class="text-xs uppercase text-gray-500">Alt Phone</p>
            <p class="font-semibold text-gray-900">{{ $contact->phone2 ?? '—' }}</p>
        </div>
        <div class="sm:col-span-2 lg:col-span-3">
            <p class="text-xs uppercase text-gray-500">Billing Address</p>
            <p class="font-semibold text-gray-900">{{ $contact->address ?? '—' }}</p>
        </div>
    </div>
</section>
