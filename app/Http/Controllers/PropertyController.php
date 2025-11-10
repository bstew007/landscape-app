<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Property;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function create(Client $client)
    {
        return view('properties.create', [
            'client' => $client,
            'property' => new Property(),
        ]);
    }

    public function store(Request $request, Client $client)
    {
        $data = $this->validateProperty($request);

        $property = $client->properties()->create($data);

        $this->syncPrimaryFlag($client, $property);

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Property created successfully.');
    }

    public function edit(Client $client, Property $property)
    {
        $this->ensureOwnership($client, $property);

        return view('properties.edit', compact('client', 'property'));
    }

    public function update(Request $request, Client $client, Property $property)
    {
        $this->ensureOwnership($client, $property);

        $property->update($this->validateProperty($request));

        $this->syncPrimaryFlag($client, $property);

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Property updated successfully.');
    }

    public function destroy(Client $client, Property $property)
    {
        $this->ensureOwnership($client, $property);

        $property->delete();

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Property deleted.');
    }

    protected function validateProperty(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:50',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:120',
            'state' => 'nullable|string|max:120',
            'postal_code' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'is_primary' => 'nullable|boolean',
        ]);

        $data['is_primary'] = $request->boolean('is_primary');

        return $data;
    }

    protected function ensureOwnership(Client $client, Property $property): void
    {
        abort_unless($property->client_id === $client->id, 404);
    }

    protected function syncPrimaryFlag(Client $client, Property $property): void
    {
        if (! $property->is_primary) {
            return;
        }

        $client->properties()
            ->where('id', '!=', $property->id)
            ->update(['is_primary' => false]);
    }
}
