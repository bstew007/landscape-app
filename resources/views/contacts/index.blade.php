@extends('layouts.sidebar')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <x-page-header title="Contacts" eyebrow="Client Hub" subtitle="Search by contact or company name.">
        <x-slot:actions>
            <x-brand-button href="{{ route('contacts.create') }}">➕ Add Contact</x-brand-button>
            <a href="{{ route('contacts.qbo.search') }}" target="_self" class="inline-flex items-center rounded font-medium whitespace-nowrap transition-colors gap-2 h-10 px-4 text-sm border border-brand-600 text-brand-700 hover:bg-brand-50 focus:outline-none focus:ring-2 focus:ring-brand-500">Import from QBO</a>
        </x-slot:actions>
    </x-page-header>

    <div class="mt-6 flex flex-col gap-3 md:flex-row md:items-center">
        <form method="GET" action="{{ route('contacts.index') }}" class="flex flex-1 items-center gap-2">
            @php $types = ['lead','client','vendor','owner']; @endphp
                            <select name="type" class="form-select border-brand-300 rounded focus:ring-brand-500 focus:border-brand-500">
                @foreach ($types as $t)
                    <option value="{{ $t }}" {{ ($type ?? 'client') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                @endforeach
            </select>
            <input type="text"
                   name="search"
                   value="{{ $search ?? '' }}"
                   placeholder="Search contacts..."
                   class="flex-1 form-input border-brand-300 rounded focus:ring-brand-500 focus:border-brand-500">
            @if(!empty($search))
                <x-brand-button href="{{ route('contacts.index') }}" variant="ghost" aria-label="Clear search">✕</x-brand-button>
            @endif
            <x-brand-button type="submit">🔍</x-brand-button>
        </form>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg border border-green-300">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md mb-3 p-3 flex items-center gap-2" data-role="bulk-toolbar">
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
    </div>

    @if ($contacts->count())
        <div class="overflow-x-auto bg-white rounded-lg shadow-md">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 text-gray-700 text-left text-sm uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4"><input type="checkbox" data-action="toggle-all"></th>
                        <th class="px-6 py-4">Name</th>
                        <th class="px-6 py-4">Company</th>
                        <th class="px-6 py-4">Email</th>
                        <th class="px-6 py-4">Phone</th>
                        <th class="px-6 py-4">Type</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-gray-800 text-lg">
                    @foreach ($contacts as $contact)
                        <tr class="hover:bg-brand-50/50 data-[selected=true]:bg-brand-50">
                            <td class="px-6 py-4"><input type="checkbox" value="{{ $contact->id }}" data-role="row-check"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('contacts.show', $contact) }}" class="text-brand-700 hover:text-brand-900 hover:underline">
                                    {{ $contact->first_name }} {{ $contact->last_name }}
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                {{ $contact->company_name ?? '—' }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $contact->email }}
                                @if($contact->email2)
                                    <div class="text-xs text-gray-500">{{ $contact->email2 }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                {{ $contact->phone }}
                                @if($contact->phone2)
                                    <div class="text-xs text-gray-500">{{ $contact->phone2 }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 capitalize">{{ $contact->contact_type ?? 'client' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-gray-600 text-lg mt-4">No contacts yet. Click “Add Contact” to get started.</p>
    @endif

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
