<form action="{{ $route }}" method="POST" class="space-y-6 bg-white p-6 rounded-lg shadow">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    <div>
        <label for="visit_date" class="block text-lg font-medium text-gray-700">Visit Date</label>
        <input type="date" name="visit_date" id="visit_date"
               class="mt-1 block w-full text-xl rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
               value="{{ old('visit_date', optional($siteVisit->visit_date)->format('Y-m-d')) }}"
               required>
    </div>

    <div>
        <label for="notes" class="block text-lg font-medium text-gray-700">Notes</label>
        <textarea name="notes" id="notes" rows="4"
                  class="mt-1 block w-full text-xl rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
        >{{ old('notes', $siteVisit->notes) }}</textarea>
    </div>

    <div class="flex items-center justify-between">
        <button type="submit"
                class="px-6 py-3 bg-green-600 text-white text-lg rounded-md shadow hover:bg-green-700">
            💾 Save
        </button>

        <a href="{{ route('clients.site-visits.index', $siteVisit->client_id ?? $client->id) }}"
           class="text-gray-600 hover:text-gray-800 text-lg underline">
            ← Back to Visits
        </a>
    </div>
</form>
