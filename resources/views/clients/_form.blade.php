<form action="{{ $route }}" method="POST" class="space-y-6 bg-white p-6 rounded-lg shadow-md">
    @csrf
    @if ($method === 'PUT')
        @method('PUT')
    @endif

    <div>
        <label for="first_name" class="block text-lg font-medium text-gray-700">First Name</label>
        <input type="text" name="first_name" id="first_name" autocomplete="given-name"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xl"
               value="{{ old('first_name', $client->first_name ?? '') }}" required>
    </div>

    <div>
        <label for="last_name" class="block text-lg font-medium text-gray-700">Last Name</label>
        <input type="text" name="last_name" id="last_name" autocomplete="family-name"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xl"
               value="{{ old('last_name', $client->last_name ?? '') }}" required>
    </div>

    <div>
        <label for="company_name" class="block text-lg font-medium text-gray-700">Company Name (optional)</label>
        <input type="text" name="company_name" id="company_name" autocomplete="organization"
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
            <input type="email" name="email" id="email" autocomplete="email"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xl"
                   value="{{ old('email', $client->email ?? '') }}">
        </div>
        <div>
            <label for="email2" class="block text-lg font-medium text-gray-700">Secondary Email</label>
            <input type="email" name="email2" id="email2" autocomplete="email"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xl"
                   value="{{ old('email2', $client->email2 ?? '') }}">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="phone" class="block text-lg font-medium text-gray-700">Primary Phone</label>
            <input type="text" name="phone" id="phone" autocomplete="tel"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xl"
                   value="{{ old('phone', $client->phone ?? '') }}" placeholder="(555) 555-1234">
        </div>
        <div>
            <label for="phone2" class="block text-lg font-medium text-gray-700">Secondary Phone</label>
            <input type="text" name="phone2" id="phone2" autocomplete="tel"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xl"
                   value="{{ old('phone2', $client->phone2 ?? '') }}" placeholder="(555) 555-1234">
        </div>
    </div>

    <div>
        <label for="address" class="block text-lg font-medium text-gray-700">Address</label>
        <div class="mt-1">
            <gmpx-place-picker id="contact_place_picker" style="display:block;width:100%;min-height:56px;font-size:1.25rem;"></gmpx-place-picker>
        </div>
        <input type="text" name="address" id="address" autocomplete="address-line1" data-city-id="city" data-state-id="state" data-zip-id="postal_code"
               class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-xl"
               value="{{ old('address', $client->address ?? '') }}" placeholder="Street address" />
        @if(!empty($client->address))
            <p class="text-xs text-gray-500 mt-1">Current: {{ $client->address }}</p>
        @endif
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-3">
            <div>
                <label class="block text-lg font-medium text-gray-700">City</label>
                <input type="text" name="city" id="city" autocomplete="address-level2" value="{{ old('city', $client->city ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-xl">
            </div>
            <div>
                <label class="block text-lg font-medium text-gray-700">State</label>
                <input type="text" name="state" id="state" autocomplete="address-level1" value="{{ old('state', $client->state ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-xl">
            </div>
            <div>
                <label class="block text-lg font-medium text-gray-700">Postal Code</label>
                <input type="text" name="postal_code" id="postal_code" autocomplete="postal-code" value="{{ old('postal_code', $client->postal_code ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-xl">
            </div>
        </div>
        <p class="text-xs text-gray-500 mt-1">Autocomplete powered by Google Places (optional). Start typing, then pick a result.</p>
    </div>

    <div class="flex items-center justify-between">
        <button type="submit"
                class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white text-lg font-medium rounded-md shadow">
            💾 Save
        </button>

        <!-- Back link removed — header provides navigation -->
    </div>
<script>
    // Lightweight input mask: formats as (XXX) XXX-XXXX while typing
    document.addEventListener('DOMContentLoaded', function(){
        function maskPhone(e){
            const input = e.target;
            let digits = (input.value || '').replace(/\D/g,'');
            if (digits.length > 11) digits = digits.slice(0,11);
            if (digits.length === 11 && digits.startsWith('1')) digits = digits.slice(1);
            if (digits.length > 6){
                input.value = `(${digits.slice(0,3)}) ${digits.slice(3,6)}-${digits.slice(6,10)}`;
            } else if (digits.length > 3){
                input.value = `(${digits.slice(0,3)}) ${digits.slice(3,6)}`;
            } else if (digits.length > 0){
                input.value = `(${digits.slice(0,3)}`;
            } else {
                input.value = '';
            }
        }
        const p1 = document.getElementById('phone');
        const p2 = document.getElementById('phone2');
        if (p1) p1.addEventListener('input', maskPhone);
        if (p2) p2.addEventListener('input', maskPhone);
    });
    // Address Autocomplete handled globally from app.js (initPlacesAutocomplete)
    // This page only keeps phone masks locally.
</script>
</form>
