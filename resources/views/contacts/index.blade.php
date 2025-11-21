@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">

    <div class="rounded-lg bg-white shadow-sm border border-brand-100 p-6 flex items-center gap-3">
        <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-brand-600/10 text-brand-700">
            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
            </svg>
        </span>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Contacts</h1>
        </div>
    </div>

    <div class="rounded-lg bg-white shadow-sm border border-brand-100 p-6 space-y-6">
        <form method="GET" action="{{ route('contacts.index') }}" class="flex w-full flex-col gap-3 sm:flex-row sm:items-center">
            @php $types = ['lead','client','vendor','owner']; @endphp
            <select name="type" class="form-select border-brand-300 rounded focus:ring-brand-500 focus:border-brand-500 w-full sm:w-48">
                @foreach ($types as $t)
                    <option value="{{ $t }}" {{ ($type ?? 'client') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                @endforeach
            </select>
            <div class="flex flex-1 items-center gap-3">
                <input type="text"
                       name="search"
                       value="{{ $search ?? '' }}"
                       placeholder="Search contacts..."
                       class="flex-1 form-input border-brand-300 rounded focus:ring-brand-500 focus:border-brand-500 text-lg py-3">
                @if(!empty($search))
                    <x-brand-button href="{{ route('contacts.index') }}" variant="ghost" aria-label="Clear search">?</x-brand-button>
                @endif
                <x-brand-button type="submit">??</x-brand-button>
            </div>
        </form>

        @if (session('success'))
            <div class="p-4 bg-accent-50 text-accent-900 rounded-lg border border-accent-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="rounded-lg border border-brand-100/80 bg-brand-50/40 p-3 flex items-center gap-2" data-role="bulk-toolbar">
            <x-secondary-button data-action="bulk-view" size="sm" disabled>
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                View
            </x-secondary-button>
            <x-brand-button variant="outline" data-action="bulk-edit" size="sm" disabled>
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4Z"/></svg>
                Edit
            </x-brand-button>
            <x-danger-button data-action="bulk-delete" size="sm" disabled>
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                Delete
            </x-danger-button>
            <div class="ml-auto flex items-center gap-2">
                <x-brand-button href="{{ route('contacts.create') }}" size="sm">New</x-brand-button>
                <a href="{{ route('contacts.qbo.search') }}" target="_self" class="inline-flex items-center rounded font-medium whitespace-nowrap transition-colors gap-2 h-9 px-4 text-sm border border-brand-600 text-brand-700 hover:bg-brand-50 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    Import from QBO
                </a>
            </div>
        </div>

        @if ($contacts->count())
            <div class="overflow-x-auto rounded-lg border border-brand-200">
                <table class="min-w-full border-collapse">
                    <thead class="bg-brand-50 text-brand-700 text-left text-sm uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4 border border-brand-200 bg-brand-100"><input type="checkbox" data-action="toggle-all"></th>
                            <th class="px-6 py-4 border border-brand-200 bg-brand-100">Name</th>
                            <th class="px-6 py-4 border border-brand-200 bg-brand-100">Company</th>
                            <th class="px-6 py-4 border border-brand-200 bg-brand-100">Address</th>
                            <th class="px-6 py-4 border border-brand-200 bg-brand-100">Type</th>
                            <th class="px-6 py-4 border border-brand-200 bg-brand-100">Contact</th>
                            <th class="px-6 py-4 border border-brand-200 bg-brand-100">Phone</th>
                            <th class="px-6 py-4 border border-brand-200 bg-brand-100">Email</th>
                            <th class="px-6 py-4 border border-brand-200 bg-brand-100">QBO</th>
                        </tr>
                    </thead>
                    <tbody class="text-brand-900 text-lg">
                        @foreach ($contacts as $contact)
                            @php
                                $cityState = collect([$contact->city, $contact->state])->filter()->join(', ');
                                $rowShade = $loop->even ? 'bg-brand-100' : 'bg-white';
                            @endphp
                            <tr class="{{ $rowShade }} hover:bg-brand-100 data-[selected=true]:bg-brand-100">
                                <td class="px-6 py-4 border border-brand-200"><input type="checkbox" value="{{ $contact->id }}" data-role="row-check"></td>
                                <td class="px-6 py-4 border border-brand-200 whitespace-nowrap">
                                    <a href="{{ route('contacts.show', $contact) }}" class="text-brand-700 hover:text-brand-900 hover:underline">
                                        {{ collect([$contact->last_name, $contact->first_name])->filter()->join(', ') }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 border border-brand-200">
                                    {{ $contact->company_name ?: 'N/A' }}
                                </td>
                                <td class="px-6 py-4 border border-brand-200">
                                    <div>{{ $contact->address ?: 'N/A' }}</div>
                                    @if($cityState)
                                        <div class="text-sm text-gray-500">{{ $cityState }}</div>
                                    @elseif($contact->postal_code)
                                        <div class="text-sm text-gray-500">{{ $contact->postal_code }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 border border-brand-200 capitalize">{{ $contact->contact_type ?? 'client' }}</td>
                                <td class="px-6 py-4 border border-brand-200">
                                    {{ $contact->mobile ?? 'N/A' }}
                                    @if($contact->phone2)
                                        <div class="text-xs text-gray-500">Alt: {{ $contact->phone2 }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 border border-brand-200">
                                    {{ $contact->phone ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 border border-brand-200">
                                    @if($contact->email)
                                        <a href="mailto:{{ $contact->email }}" class="text-brand-700 hover:text-brand-900 hover:underline">{{ $contact->email }}</a>
                                    @else
                                        N/A
                                    @endif
                                    @if($contact->email2)
                                        <div class="text-xs">
                                            <a href="mailto:{{ $contact->email2 }}" class="text-brand-600 hover:text-brand-800 hover:underline">{{ $contact->email2 }}</a>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 border border-brand-200">
                                    @if($contact->qbo_customer_id)
                                        @php $needsSync = $contact->qbo_last_synced_at && $contact->updated_at && $contact->updated_at->gt($contact->qbo_last_synced_at); @endphp
                                        @if($needsSync)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 text-xs">Needs Sync</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-accent-50 text-accent-700 border border-accent-200 text-xs">Synced</span>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-brand-50 text-brand-700 border border-brand-200 text-xs">Not Linked</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-brand-600 text-lg">No contacts yet. Click "New" to get started.</p>
        @endif
    </div>


</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggleAll = document.querySelector('[data-action="toggle-all"]');
        const checks = () => Array.from(document.querySelectorAll('[data-role="row-check"]'));
        const toolbarBtns = {
            view: document.querySelector('[data-action="bulk-view"]'),
            edit: document.querySelector('[data-action="bulk-edit"]'),
            del: document.querySelector('[data-action="bulk-delete"]'),
        };

        function updateToolbar() {
            const any = checks().some(c => c.checked);
            Object.values(toolbarBtns).forEach(btn => { if (btn) btn.disabled = !any; });
            document.querySelector('[data-role="selected-count"]')?.remove();
            if (any) {
                const count = checks().filter(c => c.checked).length;
                const badge = document.createElement('span');
                badge.setAttribute('data-role','selected-count');
                badge.className = 'ml-auto inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-brand-100 text-brand-800';
                badge.textContent = `${count} selected`;
                document.querySelector('[data-role="bulk-toolbar"]').appendChild(badge);
            }
        }

        if (toggleAll) {
            toggleAll.addEventListener('change', () => {
                checks().forEach(c => c.checked = toggleAll.checked);
                checks().forEach(c => c.closest('tr')?.setAttribute('data-selected', toggleAll.checked ? 'true' : 'false'));
                updateToolbar();
            });
        }
        checks().forEach(c => c.addEventListener('change', () => {
            c.closest('tr')?.setAttribute('data-selected', c.checked ? 'true' : 'false');
            updateToolbar();
        }));

        function selectedIds() { return checks().filter(c => c.checked).map(c => c.value); }

        if (toolbarBtns.view) {
            toolbarBtns.view.addEventListener('click', () => {
                const ids = selectedIds();
                if (!ids.length) return;
                window.location.href = `{{ url('contacts') }}/${ids[0]}`;
            });
        }
        if (toolbarBtns.edit) {
            toolbarBtns.edit.addEventListener('click', () => {
                const ids = selectedIds();
                if (!ids.length) return;
                window.location.href = `{{ url('contacts') }}/${ids[0]}/edit`;
            });
        }
        if (toolbarBtns.del) {
            toolbarBtns.del.addEventListener('click', () => {
                const ids = selectedIds();
                if (!ids.length) return;
                if (!confirm(`Delete ${ids.length} contact(s)?`)) return;
                (async () => {
                    for (const id of ids) {
                        await fetch(`{{ url('contacts') }}/${id}`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: new URLSearchParams({ '_method': 'DELETE' })
                        });
                    }
                    window.location.reload();
                })();
            });
        }
    });
</script>
@endpush
