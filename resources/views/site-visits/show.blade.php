@extends('layouts.sidebar')

@section('content')
<div class="max-w-4xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">🗓️ Site Visit Details</h1>

    {{-- Site Visit Info --}}
    <div class="bg-white p-6 rounded-lg shadow text-gray-800 space-y-2 mb-6">
        <p><strong>Client:</strong> {{ $client->first_name }} {{ $client->last_name }}</p>
        <p><strong>Visit Date:</strong> {{ $siteVisit->visit_date->format('F j, Y') }}</p>
        <p><strong>Notes:</strong> {{ $siteVisit->notes ?? '—' }}</p>
    </div>

    {{-- ✏️ Edit and ❌ Delete Site Visit --}}
    <div class="flex gap-4 mb-8">
        <a href="{{ route('clients.site-visits.edit', [$client, $siteVisit]) }}"
           class="px-5 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-semibold">
            ✏️ Edit Site Visit
        </a>

        <form method="POST" action="{{ route('clients.site-visits.destroy', [$client, $siteVisit]) }}"
              onsubmit="return confirm('Are you sure you want to delete this site visit?');">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="px-5 py-3 bg-red-500 hover:bg-red-600 text-white rounded-lg font-semibold">
                🗑️ Delete Site Visit
            </button>
        </form>
    </div>

    {{-- 🧮 Calculations Section --}}
    <h2 class="text-2xl font-semibold mb-4">🧮 Calculations</h2>

    @if ($calculations->count())
        <div class="space-y-4">
            @foreach ($calculations as $calc)
                <div class="bg-white p-4 rounded shadow flex justify-between items-center">
                    <div>
                        <p><strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $calc->calculation_type)) }}</p>
                        <p class="text-sm text-gray-500">Created: {{ $calc->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div class="flex gap-2">
                        {{-- 🔍 View/Edit --}}
                        @if ($calc->calculation_type === 'retaining_wall')
                            <a href="{{ route('calculators.wall.edit', $calc->id) }}"
                               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                                Edit
                            </a>
                        @endif

                        {{-- ❌ Delete --}}
                        <form method="POST" action="{{ route('site-visits.deleteCalculation', $calc->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-gray-600 mb-6">No calculations saved for this visit.</p>
    @endif

    {{-- 🔙 Back to Client --}}
    <div class="mt-8">
        <a href="{{ route('clients.show', $client) }}"
           class="inline-block px-5 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-md text-lg">
            🔙 Back to Client
        </a>
    </div>
</div>
@endsection
