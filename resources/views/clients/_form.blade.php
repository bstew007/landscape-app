<form action="{{ $route }}" method="POST" class="space-y-6 bg-white p-6 rounded-lg shadow-md">
    @csrf
    @if ($method === 'PUT')
        @method('PUT')
    @endif

    <div>
        <label for="first_name" class="block text-lg font-medium text-gray-700">First Name</label>
        <input type="text" name="first_name" id="first_name"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xl"
               value="{{ old('first_name', $client->first_name ?? '') }}" required>
    </div>

    <div>
        <label for="last_name" class="block text-lg font-medium text-gray-700">Last Name</label>
        <input type="text" name="last_name" id="last_name"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xl"
               value="{{ old('last_name', $client->last_name ?? '') }}" required>
    </div>

    <div>
        <label for="company_name" class="block text-lg font-medium text-gray-700">Company Name (optional)</label>
        <input type="text" name="company_name" id="company_name"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xl"
               value="{{ old('company_name', $client->company_name ?? '') }}">
    </div>

    <div>
        <label for="contact_type" class="block text-lg font-medium text-gray-700">Contact Type</label>
        <select name="contact_type" id="contact_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xl" required>
            @foreach (($types ?? ['lead','client','vendor','owner']) as $t)
                <option value="{{ $t }}" {{ old('contact_type', $client->contact_type ?? 'client') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="email" class="block text-lg font-medium text-gray-700">Primary Email</label>
            <input type="email" name="email" id="email"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xl"
                   value="{{ old('email', $client->email ?? '') }}">
        </div>
        <div>
            <label for="email2" class="block text-lg font-medium text-gray-700">Secondary Email</label>
            <input type="email" name="email2" id="email2"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xl"
                   value="{{ old('email2', $client->email2 ?? '') }}">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="phone" class="block text-lg font-medium text-gray-700">Primary Phone</label>
            <input type="text" name="phone" id="phone"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xl"
                   value="{{ old('phone', $client->phone ?? '') }}">
        </div>
        <div>
            <label for="phone2" class="block text-lg font-medium text-gray-700">Secondary Phone</label>
            <input type="text" name="phone2" id="phone2"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xl"
                   value="{{ old('phone2', $client->phone2 ?? '') }}">
        </div>
    </div>

    <div>
        <label for="address" class="block text-lg font-medium text-gray-700">Address</label>
        <textarea name="address" id="address" rows="3"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xl">{{ old('address', $client->address ?? '') }}</textarea>
    </div>

    <div class="flex items-center justify-between">
        <button type="submit"
                class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white text-lg font-medium rounded-md shadow">
            💾 Save
        </button>

        <a href="{{ route('clients.index') }}"
           class="text-gray-600 hover:text-gray-800 text-lg underline">
            ← Back to Clients
        </a>
    </div>
</form>
