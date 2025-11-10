<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $clients = Client::query()
            ->when($search, function ($query, $term) {
                $query->where(function ($q) use ($term) {
                    $q->where('first_name', 'like', "%{$term}%")
                      ->orWhere('last_name', 'like', "%{$term}%")
                      ->orWhere('company_name', 'like', "%{$term}%");
                });
            })
            ->orderBy('company_name')
            ->orderBy('last_name')
            ->get();

        return view('clients.index', compact('clients', 'search'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email'      => 'nullable|email',
            'phone'      => 'nullable|string|max:20',
            'address'    => 'nullable|string',
        ]);

        Client::create($validated);

        return redirect()->route('clients.index')->with('success', 'Client added successfully.');
    }

    /**
     * Show a single client with related actions like site visits.
     */
    public function show(Client $client)
    {
        $properties = $client->properties()
            ->withCount('siteVisits')
            ->orderByDesc('is_primary')
            ->orderBy('name')
            ->get();

        $latestVisit = $client->siteVisits()->with('property')->latest()->first();
        $recentVisits = $client->siteVisits()->with('property')->latest()->limit(5)->get();

        return view('clients.show', [
            'client' => $client,
            'siteVisit' => $latestVisit, // This line is key
            'properties' => $properties,
            'recentVisits' => $recentVisits,
        ]);
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email'      => 'nullable|email',
            'phone'      => 'nullable|string|max:20',
            'address'    => 'nullable|string',
        ]);

        $client->update($validated);

        return redirect()->route('clients.index')->with('success', 'Client updated successfully.');
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client deleted successfully.');
    }
}
