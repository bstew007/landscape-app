@extends('layouts.sidebar')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <x-page-header title="Contacts" eyebrow="CRM" subtitle="Search by contact or company name.">
        <x-slot:actions>
            <x-brand-button href="{{ route('clients.create') }}">➕ Add Contact</x-brand-button>
        </x-slot:actions>
    </x-page-header>

    <div class="mt-6 flex flex-col gap-3 md:flex-row md:items-center">
        <form method="GET" action="{{ route('clients.index') }}" class="flex flex-1 items-center gap-2">
            <select name="type" class="form-select border-gray-300 rounded focus:ring-brand-500 focus:border-brand-500">
                @php $types = ['lead','client','vendor','owner']; @endphp
                @foreach ($types as $t)
                    <option value="{{ $t }}" {{ ($type ?? 'client') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                @endforeach
            </select>
            <input type="text"
                   name="search"
                   value="{{ $search ?? '' }}"
                   placeholder="Search contacts..."
                   class="flex-1 form-input border-gray-300 rounded focus:ring-brand-500 focus:border-brand-500">
            @if(!empty($search))
                <a href="{{ route('clients.index') }}"
                   class="px-3 py-2 border-t border-b border-gray-300 text-gray-600 bg-gray-100 hover:bg-gray-200">
                    ✕
                </a>
            @endif
            <x-brand-button type="submit">🔍</x-brand-button>
        </form>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg border border-green-300">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md mb-3 p-3 flex items-center gap-2">
        <button class="px-3 py-2 bg-blue-600 text-white rounded disabled:opacity-50" data-action="bulk-view" disabled>View</button>
        <button class="px-3 py-2 bg-green-600 text-white rounded disabled:opacity-50" data-action="bulk-edit" disabled>Edit</button>
        <button class="px-3 py-2 bg-red-600 text-white rounded disabled:opacity-50" data-action="bulk-delete" disabled>Delete</button>
    </div>

    @if ($clients->count())
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
                    @foreach ($clients as $client)
                        <tr>
                            <td class="px-6 py-4"><input type="checkbox" value="{{ $client->id }}" data-role="row-check"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $client->first_name }} {{ $client->last_name }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $client->company_name ?? '—' }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $client->email }}
                                @if($client->email2)
                                    <div class="text-xs text-gray-500">{{ $client->email2 }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                {{ $client->phone }}
                                @if($client->phone2)
                                    <div class="text-xs text-gray-500">{{ $client->phone2 }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 capitalize">{{ $client->contact_type ?? 'client' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-gray-600 text-lg mt-4">No clients yet. Click “Add Client” to get started.</p>
    @endif

</div>

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
        }

        if (toggleAll) {
            toggleAll.addEventListener('change', () => {
                checks().forEach(c => c.checked = toggleAll.checked);
                updateToolbar();
            });
        }
        checks().forEach(c => c.addEventListener('change', updateToolbar));

        function selectedIds() { return checks().filter(c => c.checked).map(c => c.value); }

        if (toolbarBtns.view) {
            toolbarBtns.view.addEventListener('click', () => {
                const ids = selectedIds();
                if (!ids.length) return;
                // Open the first one (multi-view not implemented)
                window.location.href = `{{ url('clients') }}/${ids[0]}`;
            });
        }
        if (toolbarBtns.edit) {
            toolbarBtns.edit.addEventListener('click', () => {
                const ids = selectedIds();
                if (!ids.length) return;
                window.location.href = `{{ url('clients') }}/${ids[0]}/edit`;
            });
        }
        if (toolbarBtns.del) {
            toolbarBtns.del.addEventListener('click', () => {
                const ids = selectedIds();
                if (!ids.length) return;
                if (!confirm(`Delete ${ids.length} contact(s)?`)) return;
                // Submit delete for each id sequentially (could be optimized to bulk endpoint)
                (async () => {
                    for (const id of ids) {
                        await fetch(`{{ url('clients') }}/${id}`, {
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
@endsection
