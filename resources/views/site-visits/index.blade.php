@extends('layouts.sidebar')

@section('content')
<div class="max-w-5xl mx-auto">
    <h1 class="text-3xl font-semibold mb-6">📝 Site Visits for {{ $client->first_name }} {{ $client->last_name }}</h1>

    <a href="{{ route('clients.site-visits.create', $client) }}"
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
                        <th class="px-6 py-3 text-left">Date</th>
                        <th class="px-6 py-3 text-left">Notes</th>
                        <th class="px-6 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($siteVisits as $visit)
                        <tr class="border-t">
                            <td class="px-6 py-3">{{ $visit->visit_date->format('M d, Y') }}</td>
                            <td class="px-6 py-3">{{ $visit->notes ?? '-' }}</td>
                            <td class="px-6 py-3 flex gap-2">
                                <a href="{{ route('clients.site-visits.edit', [$client, $visit]) }}"
                                   class="text-yellow-600 hover:underline">✏️ Edit</a>

                                <form action="{{ route('clients.site-visits.destroy', [$client, $visit]) }}" method="POST"
                                      onsubmit="return confirm('Delete this site visit?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">🗑️ Delete</button>
                                </form>
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
