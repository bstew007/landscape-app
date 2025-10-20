@extends('layouts.sidebar')

@section('content')
<div class="max-w-3xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">✏️ Edit Site Visit</h1>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 text-red-800 border border-red-300 rounded">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('clients.site-visits.update', [$client, $siteVisit]) }}" method="POST" class="bg-white p-6 rounded shadow space-y-4">
        @csrf
        @method('PUT')

        {{-- Visit Date --}}
        <div>
            <label for="visit_date" class="block text-lg font-semibold mb-1">Visit Date</label>
            <input type="date" name="visit_date" id="visit_date"
                   value="{{ old('visit_date', $siteVisit->visit_date->format('Y-m-d')) }}"
                   class="w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-blue-200">
        </div>

        {{-- Notes --}}
        <div>
            <label for="notes" class="block text-lg font-semibold mb-1">Notes</label>
            <textarea name="notes" id="notes" rows="5"
                      class="w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-blue-200">{{ old('notes', $siteVisit->notes) }}</textarea>
        </div>

        {{-- Submit --}}
        <div class="flex gap-4 mt-6">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold">
                💾 Update Site Visit
            </button>

            <a href="{{ route('clients.site-visits.index', $client) }}"
               class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-semibold">
                🔙 Cancel
            </a>
        </div>
    </form>
</div>
@endsection
