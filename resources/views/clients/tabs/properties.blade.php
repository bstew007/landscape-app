<section class="bg-white rounded-lg shadow p-6 space-y-4">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900">Properties</h2>
        <div class="flex items-center gap-2">
            <a href="{{ route('contacts.properties.create', $contact) }}" class="rounded bg-blue-600 text-white px-4 py-2 text-sm hover:bg-blue-700">+ Add Property</a>
            <button type="button" id="openPropSlideOver" class="rounded border px-4 py-2 text-sm">+ Quick Add</button>
        </div>
    </div>

    <!-- Slide-over for Quick Add -->
    <div id="propSlideOver" class="fixed inset-0 z-40 hidden">
        <div id="propSlideOverOverlay" class="absolute inset-0 bg-black/30"></div>
        <div class="absolute right-0 top-0 h-full w-full sm:max-w-xl bg-white shadow-xl flex flex-col">
            <div class="flex items-center justify-between px-4 py-3 border-b">
                <h3 class="text-lg font-semibold">Add Property</h3>
                <button id="closePropSlideOver" class="text-gray-500 hover:text-gray-700">Close</button>
            </div>
            <div class="p-4 overflow-y-auto">
                <form method="POST" action="{{ route('contacts.properties.store', $contact) }}" class="space-y-3" id="quickPropForm">
                    @csrf
                    <input type="hidden" name="is_primary" value="0">
                    <div class="grid sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500">Name</label>
                            <input type="text" name="name" class="form-input w-full" required>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500">Type</label>
                            <input type="text" name="type" class="form-input w-full" value="residential" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500">Address line 1</label>
                        <input type="text" name="address_line1" class="form-input w-full">
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500">City</label>
                            <input type="text" name="city" class="form-input w-full">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500">State</label>
                            <input type="text" name="state" class="form-input w-full">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500">ZIP</label>
                            <input type="text" name="postal_code" class="form-input w-full">
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" id="cancelPropSlideOver" class="px-3 py-2 rounded border">Cancel</button>
                        <button class="px-3 py-2 rounded bg-blue-600 text-white">Save Property</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($properties->isEmpty())
        <p class="text-sm text-gray-500">No properties yet.</p>
    @else
        <div class="overflow-x-auto border rounded">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="text-left px-3 py-2">Name</th>
                        <th class="text-left px-3 py-2">Address</th>
                        <th class="text-left px-3 py-2">Type</th>
                        <th class="text-left px-3 py-2">Primary</th>
                        <th class="text-right px-3 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($properties as $property)
                    <tr class="border-t">
                        <td class="px-3 py-2 font-semibold text-gray-900">{{ $property->name }}</td>
                        <td class="px-3 py-2 text-gray-700">{{ $property->display_address ?? '—' }}</td>
                        <td class="px-3 py-2 text-gray-700">{{ ucfirst($property->type) }}</td>
                        <td class="px-3 py-2 text-gray-700">{{ $property->is_primary ? 'Yes' : 'No' }}</td>
                        <td class="px-3 py-2 text-right space-x-2">
                            @if(!$property->is_primary)
                                <form method="POST" action="{{ route('contacts.properties.update', [$contact, $property]) }}" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="name" value="{{ $property->name }}">
                                    <input type="hidden" name="type" value="{{ $property->type }}">
                                    <input type="hidden" name="is_primary" value="1">
                                    <button class="text-emerald-700 hover:underline" title="Set Primary">Set Primary</button>
                                </form>
                            @endif
                            <a href="{{ route('contacts.properties.edit', [$contact, $property]) }}" class="text-blue-600 hover:underline">Edit</a>
                            <form action="{{ route('contacts.properties.destroy', [$contact, $property]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this property?');">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
