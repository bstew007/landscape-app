@extends('layouts.sidebar')

@section('content')
<div class="max-w-5xl mx-auto">
    <h1 class="text-3xl font-semibold mb-6">📝 Site Visits for {{ $client->first_name }} {{ $client->last_name }}</h1>

    <a href="{{ route('contacts.site-visits.create', $client) }}"
       class="inline-block mb-4 bg-blue-600 hover:bg-blue-700 text-white text-lg px-4 py-2 rounded-md shadow">
        ➕ Add Site Visit
    </a>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 border border-green-300 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if($siteVisits->count())
        <div class="bg-white shadow-md rounded-lg overflow-x-auto">
            <table class="min-w-full text-lg">
                <thead class="bg-gray-100 text-gray-700 uppercase">
                    <tr>
                        <th class="px-6 py-3 text-left">Property</th>
                        <th class="px-6 py-3 text-left">Date</th>
                        <th class="px-6 py-3 text-left">Notes</th>
                        <th class="px-6 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-800">
                    @foreach ($siteVisits as $visit)
                        <tr class="border-t">
                            <td class="px-6 py-4">
                                <p class="font-semibold">
                                    {{ optional($visit->property)->name ?? 'Unassigned' }}
                                    @if(optional($visit->property)->is_primary)
                                        <span class="ml-1 inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800">Primary</span>
                                    @endif
                                </p>
                                <p class="text-sm text-gray-500">
                                    {{ optional($visit->property)->display_address ?? 'No address on file' }}
                                </p>
                            </td>
                            <td class="px-6 py-4">{{ $visit->visit_date->format('F j, Y') }}</td>
                            <td class="px-6 py-4">{{ $visit->notes ?? 'No notes' }}</td>
                            <td class="px-6 py-4">
                                <div class="flex gap-2">
                                    {{-- View --}}
                                    <a href="{{ route('contacts.site-visits.show', [$client, $visit]) }}"
                                       class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-sm">
                                        View
                                    </a>

                                    {{-- Edit --}}
                                    <a href="{{ route('contacts.site-visits.edit', [$client, $visit]) }}"
                                       class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                        Edit
                                    </a>

                                    {{-- Delete --}}
                                    <form action="{{ route('contacts.site-visits.destroy', [$client, $visit]) }}"
                                          method="POST"
                                          onsubmit="return confirm('Are you sure you want to delete this site visit?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-gray-600 text-lg mt-4">No site visits yet. Click "Add Site Visit" to get started.</p>
    @endif
</div>
@endsection
