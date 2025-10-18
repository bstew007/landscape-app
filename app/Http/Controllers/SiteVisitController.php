<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\SiteVisit;
use Illuminate\Http\Request;

class SiteVisitController extends Controller
{
    public function index(Client $client)
    {
        $siteVisits = $client->siteVisits()->latest()->get();
        return view('site-visits.index', compact('client', 'siteVisits'));
    }

    public function create(Client $client)
    {
        return view('site-visits.create', compact('client'));
    }

    public function store(Request $request, Client $client)
    {
        $validated = $request->validate([
            'visit_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $client->siteVisits()->create($validated);

        return redirect()->route('clients.site-visits.index', $client)
                         ->with('success', 'Site visit added.');
    }

    public function edit(Client $client, SiteVisit $siteVisit)
    {
        return view('site-visits.edit', compact('client', 'siteVisit'));
    }

    public function update(Request $request, Client $client, SiteVisit $siteVisit)
    {
        $validated = $request->validate([
            'visit_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $siteVisit->update($validated);

        return redirect()->route('clients.site-visits.index', $client)
                         ->with('success', 'Site visit updated.');
    }

    public function destroy(Client $client, SiteVisit $siteVisit)
    {
        $siteVisit->delete();

        return redirect()->route('clients.site-visits.index', $client)
                         ->with('success', 'Site visit deleted.');
    }
}
