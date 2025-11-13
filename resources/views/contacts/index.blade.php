@extends('layouts.sidebar')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="flex flex-col gap-4 mb-6 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-semibold text-gray-800">Contacts</h1>
            <p class="text-gray-500 text-sm">Search by contact or company name.</p>
        </div>
        <div class="flex flex-col gap-3 md:flex-row md:items-center">
            <form method="GET" action="{{ route('contacts.index') }}" class="flex flex-1 items-center gap-2">
                @php $types = ['lead','client','vendor','owner']; @endphp
                <select name="type" class="form-select border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                    @foreach ($types as $t)
                        <option value="{{ $t }}" {{ ($type ?? 'client') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
                <input type="text"
                       name="search"
                       value="{{ $search ?? '' }}"
                       placeholder="Search contacts..."
                       class="flex-1 form-input border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                @if(!empty($search))
                    <a href="{{ route('contacts.index') }}"
                       class="px-3 py-2 border-t border-b border-gray-300 text-gray-600 bg-gray-100 hover:bg-gray-200">
                        ✕
                    </a>
                @endif
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    🔍
                </button>
            </form>
            <a href="{{ route('contacts.create') }}"
               class="inline-flex items-center justify-center px-4 py-2 text-lg font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow">
                ➕ Add Contact
            </a>
        </div>
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
                        <tr>
                            <td class="px-6 py-4"><input type="checkbox" value="{{ $contact->id }}" data-role="row-check"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $contact->first_name }} {{ $contact->last_name }}
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
