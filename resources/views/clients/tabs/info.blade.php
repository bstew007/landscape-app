<section class="bg-white rounded-lg shadow p-6 space-y-4">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900">Contact Information</h2>
        <div class="w-full md:w-auto">
            <div class="border rounded-md bg-gray-50 p-2 md:p-3 flex flex-col md:flex-row items-start md:items-center gap-2 md:gap-3">
                @if ($contact->qbo_customer_id)
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs">QBO Linked</span>
                        @if ($contact->qbo_last_synced_at && $contact->updated_at && $contact->updated_at->gt($contact->qbo_last_synced_at))
                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 text-xs">Needs Sync</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs">Synced</span>
                        @endif
                    </div>
                    <div class="md:ml-auto flex items-center gap-2 flex-wrap" id="qboActions">
                        <form method="POST" action="{{ route('contacts.qbo.sync', $contact) }}" data-action="qbo-sync">
                            @csrf
                            <x-brand-button type="submit" variant="outline" size="sm">Sync to QBO</x-brand-button>
                        </form>
                        <form method="POST" action="{{ route('contacts.qbo.refresh', $contact) }}" data-action="qbo-refresh">
                            @csrf
                            <x-brand-button type="submit" variant="outline" size="sm">Refresh from QBO</x-brand-button>
                        </form>
                        <details class="relative" data-role="qbo-more">
                            <summary class="list-none inline-flex items-center h-8 px-3 rounded font-medium text-xs whitespace-nowrap border border-brand-600 text-brand-700 cursor-pointer select-none hover:bg-brand-50">More</summary>
                            <div class="absolute right-0 mt-2 w-[280px] bg-white border rounded shadow z-10 p-2 space-y-2">
                                <form method="POST" action="{{ route('contacts.qbo.push-names', $contact) }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-3 py-2 rounded hover:bg-gray-50 text-sm">Update Names in QBO</button>
                                </form>
                                <form method="POST" action="{{ route('contacts.qbo.push-mobile', $contact) }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-3 py-2 rounded hover:bg-gray-50 text-sm">Update Mobile in QBO</button>
                                </form>
                                <div class="border-t my-1"></div>
                                <div class="px-2 py-1">
                                    <p class="text-xs text-gray-500 mb-1">Button management</p>
                                    <label class="flex items-center gap-2 text-sm py-1">
                                        <input type="checkbox" data-pref="show-sync" class="rounded">
                                        <span>Show "Sync to QBO"</span>
                                    </label>
                                    <label class="flex items-center gap-2 text-sm py-1">
                                        <input type="checkbox" data-pref="show-refresh" class="rounded">
                                        <span>Show "Refresh from QBO"</span>
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500 px-2">Use with care. QBO may reject some updates (e.g., duplicate DisplayName).</p>
                            </div>
                        </details>
                    </div>
                @else
                    <div class="md:ml-auto">
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
                    </div>
            @endif
            </div>
        </div>
        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const prefs = {
                    'show-sync': localStorage.getItem('qbo_pref_show_sync') !== '0',
                    'show-refresh': localStorage.getItem('qbo_pref_show_refresh') !== '0',
                };
                const syncForm = document.querySelector('#qboActions [data-action="qbo-sync"]');
                const refreshForm = document.querySelector('#qboActions [data-action="qbo-refresh"]');
                if (syncForm) syncForm.classList.toggle('hidden', !prefs['show-sync']);
                if (refreshForm) refreshForm.classList.toggle('hidden', !prefs['show-refresh']);
                document.querySelectorAll('[data-pref]').forEach(cb => {
                    const key = cb.getAttribute('data-pref');
                    cb.checked = !!prefs[key];
                    cb.addEventListener('change', () => {
                        const val = cb.checked ? '1' : '0';
                        if (key === 'show-sync' && syncForm) syncForm.classList.toggle('hidden', val==='0');
                        if (key === 'show-refresh' && refreshForm) refreshForm.classList.toggle('hidden', val==='0');
                        localStorage.setItem('qbo_pref_'+key.replace(/\//g,'_'), val);
                    });
                });
            });
        </script>
        @endpush
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
        <div>
            <p class="text-xs uppercase text-gray-500">QBO Balance</p>
            <p class="font-semibold text-gray-900">{{ isset($contact->qbo_balance) ? ('$'.number_format($contact->qbo_balance,2)) : '—' }}</p>
        </div>
        <div class="sm:col-span-2 lg:col-span-3">
            <p class="text-xs uppercase text-gray-500">Billing Address</p>
            <p class="font-semibold text-gray-900">{{ $contact->address ?? '—' }}</p>
        </div>
    </div>
</section>
