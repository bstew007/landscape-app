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
        <label for="email" class="block text-lg font-medium text-gray-700">Email</label>
        <input type="email" name="email" id="email"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xl"
               value="{{ old('email', $client->email ?? '') }}">
    </div>

    <div>
        <label for="phone" class="block text-lg font-medium text-gray-700">Phone</label>
        <input type="text" name="phone" id="phone"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xl"
               value="{{ old('phone', $client->phone ?? '') }}">
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

