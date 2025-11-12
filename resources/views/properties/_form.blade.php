<form action="{{ $route }}" method="POST" class="space-y-6 bg-white p-6 rounded-lg shadow">
    @csrf
    @if ($method === 'PUT')
        @method('PUT')
    @endif

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label for="name" class="block text-lg font-medium text-gray-700">Property Name</label>
            <input type="text" name="name" id="name"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xl"
                   value="{{ old('name', $property->name) }}" required>
        </div>
        <div>
            <label for="type" class="block text-lg font-medium text-gray-700">Type</label>
            <select name="type" id="type"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xl">
                @foreach (['residential' => 'Residential', 'commercial' => 'Commercial', 'other' => 'Other'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('type', $property->type ?? 'residential') === $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div>
            <label for="contact_name" class="block text-lg font-medium text-gray-700">On-site Contact</label>
            <input type="text" name="contact_name" id="contact_name"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xl"
                   value="{{ old('contact_name', $property->contact_name) }}">
        </div>
        <div>
            <label for="contact_email" class="block text-lg font-medium text-gray-700">Contact Email</label>
            <input type="email" name="contact_email" id="contact_email"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xl"
                   value="{{ old('contact_email', $property->contact_email) }}">
        </div>
        <div>
            <label for="contact_phone" class="block text-lg font-medium text-gray-700">Contact Phone</label>
            <input type="text" name="contact_phone" id="contact_phone"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xl"
                   value="{{ old('contact_phone', $property->contact_phone) }}">
        </div>
    </div>

    <div>
        <label for="address_line1" class="block text-lg font-medium text-gray-700">Address Line 1</label>
        <input type="text" name="address_line1" id="address_line1"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xl"
               value="{{ old('address_line1', $property->address_line1) }}">
    </div>

    <div>
        <label for="address_line2" class="block text-lg font-medium text-gray-700">Address Line 2</label>
        <input type="text" name="address_line2" id="address_line2"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xl"
               value="{{ old('address_line2', $property->address_line2) }}">
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div>
            <label for="city" class="block text-lg font-medium text-gray-700">City</label>
            <input type="text" name="city" id="city"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xl"
                   value="{{ old('city', $property->city) }}">
        </div>
        <div>
            <label for="state" class="block text-lg font-medium text-gray-700">State / Province</label>
            <input type="text" name="state" id="state"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xl"
                   value="{{ old('state', $property->state) }}">
        </div>
        <div>
            <label for="postal_code" class="block text-lg font-medium text-gray-700">Postal Code</label>
            <input type="text" name="postal_code" id="postal_code"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xl"
                   value="{{ old('postal_code', $property->postal_code) }}">
        </div>
    </div>

    <div class="flex items-center gap-3">
        <input type="checkbox" name="is_primary" id="is_primary" value="1"
               class="h-5 w-5 text-blue-600 border-gray-300 rounded"
               @checked(old('is_primary', $property->is_primary))>
        <label for="is_primary" class="text-lg text-gray-700">Mark as primary property for this client</label>
    </div>

    <div>
        <label for="notes" class="block text-lg font-medium text-gray-700">Notes</label>
        <textarea name="notes" id="notes" rows="4"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xl">{{ old('notes', $property->notes) }}</textarea>
    </div>

    <div class="flex items-center justify-between">
        <button type="submit"
                class="inline-flex items-center px-6 py-3 bg-brand-700 hover:bg-brand-800 text-white text-lg font-medium rounded-md shadow">
            Save
        </button>

        <a href="{{ route('clients.show', $client) }}"
           class="text-gray-600 hover:text-gray-800 text-lg underline">
            Back to Client
        </a>
    </div>
</form>
