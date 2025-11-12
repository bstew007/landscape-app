@php
    $propertyOptions = ($properties ?? collect());
    $selectedProperty = old(
        'property_id',
        $siteVisit->property_id ?? ($preferredPropertyId ?? optional($client->primaryProperty)->id)
    );
    $dateValue = old(
        'visit_date',
        optional($siteVisit->visit_date)->format('Y-m-d') ?? now()->format('Y-m-d')
    );
@endphp

<form action="{{ $route }}" method="POST" class="space-y-6 bg-white p-6 rounded-lg shadow">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    <div>
        <label for="property_id" class="block text-lg font-medium text-gray-700">Property</label>

        @if ($propertyOptions->isEmpty())
            <div class="mt-2 p-4 bg-yellow-50 border border-yellow-200 rounded text-yellow-900 text-lg">
                No properties found for this client. Please
                <a href="{{ route('clients.properties.create', $client) }}" class="underline font-semibold">add a property</a>
                before scheduling a site visit.
            </div>
        @else
            <select name="property_id" id="property_id" required
                    class="mt-1 block w-full text-xl rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="">Select a property</option>
                @foreach ($propertyOptions as $property)
                    <option value="{{ $property->id }}" @selected((string) $selectedProperty === (string) $property->id)>
                        {{ $property->name }} @if($property->is_primary) (Primary) @endif
                        @if($property->city) - {{ $property->city }}, {{ $property->state }} @endif
                    </option>
                @endforeach
            </select>
        @endif
    </div>

    <div>
        <label for="visit_date" class="block text-lg font-medium text-gray-700">Visit Date</label>
        <input type="date" name="visit_date" id="visit_date"
               class="mt-1 block w-full text-xl rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
               value="{{ $dateValue }}"
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
                class="px-6 py-3 bg-brand-700 text-white text-lg rounded-md shadow hover:bg-brand-800"
                @disabled($propertyOptions->isEmpty())>
            Save
        </button>

        <a href="{{ route('clients.site-visits.index', $siteVisit->client_id ?? $client->id) }}"
           class="text-gray-600 hover:text-gray-800 text-lg underline">
            Back to Visits
        </a>
    </div>
</form>
