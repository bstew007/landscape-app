@extends('layouts.sidebar')

@section('content')
<div class="max-w-5xl mx-auto py-10 space-y-6">
    <x-page-header title="Labor Catalog" eyebrow="Catalogs" subtitle="Crew, subcontractor, and equipment rates for estimates.">
        <x-slot:actions>
            <x-brand-button href="{{ route('labor.importForm') }}">⬆ Import (JSON/CSV)</x-brand-button>
            <x-brand-button href="{{ route('labor.export') }}" variant="outline">⬇ Export CSV</x-brand-button>
            <x-brand-button href="{{ route('labor.create') }}">+ Add Labor Entry</x-brand-button>
        </x-slot:actions>
    </x-page-header>

    @if (session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>
    @endif

    <form method="GET" class="flex flex-col sm:flex-row gap-3 bg-white p-4 rounded shadow mt-6">
        <input type="text" name="search" value="{{ $search }}" placeholder="Search by name or type"
               class="form-input flex-1">
        <x-brand-button type="submit">Search</x-brand-button>
    </form>

    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
            <tr>
                <th class="text-left px-4 py-3">Name</th>
                <th class="text-left px-4 py-3">Type</th>
                <th class="text-left px-4 py-3">Unit</th>
                <th class="text-right px-4 py-3">Base Rate</th>
                <th class="text-center px-4 py-3">Billable</th>
                <th class="px-4 py-3"></th>
            </tr>
            </thead>
            <tbody>
            @forelse ($labor as $entry)
                <tr class="border-t">
                    <td class="px-4 py-3">
                        <div class="font-semibold text-gray-900">{{ $entry->name }}</div>
                        <div class="text-xs text-gray-500">{{ $entry->notes ? \Illuminate\Support\Str::limit($entry->notes, 40) : '—' }}</div>
                    </td>
                    <td class="px-4 py-3 text-gray-700">{{ ucfirst($entry->type) }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $entry->unit }}</td>
                    <td class="px-4 py-3 text-right text-gray-900">${{ number_format($entry->base_rate, 2) }}</td>
                    <td class="px-4 py-3 text-center">
                        @if ($entry->is_billable)
                            <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">Billable</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded bg-gray-200 text-gray-700">Internal</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('labor.edit', $entry) }}" class="text-blue-600 hover:underline mr-3">Edit</a>
                        <form action="{{ route('labor.destroy', $entry) }}" method="POST" class="inline"
                              onsubmit="return confirm('Delete this labor entry?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">No labor entries yet.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{ $labor->links() }}
</div>
@endsection
