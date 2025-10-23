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
        $siteVisit = $client->siteVisits()->create([
            'visit_date' => now(), // Optional
            'notes' => $request->input('notes'),
        ]);

        return redirect()
            ->route('clients.show', $client->id)
            ->with('success', 'Site visit created successfully.');
    }

    public function edit(Client $client, SiteVisit $siteVisit)
    {
        return view('site-visits.edit', compact('client', 'siteVisit'));
    }

    /**
     * Show the site visit selector before launching a calculator.
     */
    public function select(Request $request)
    {
        $redirectTo = $request->get('redirect_to', ''); // ✅ fixed variable
        $siteVisits = SiteVisit::with('client')->orderBy('visit_date', 'desc')->get();

        return view('calculators.select-site-visit', compact('siteVisits', 'redirectTo'));
    }

    public function update(Request $request, Client $client, SiteVisit $siteVisit)
    {
        $validated = $request->validate([
            'visit_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $siteVisit->update($validated);

        return redirect()
            ->route('clients.site-visits.index', $client)
            ->with('success', 'Site visit updated.');
    }

   public function storeCalculation(Request $request)
{
    // ✅ Validate and capture input
    $validated = $request->validate([
        'calculation_type' => 'required|string',
        'data' => 'required|string',
        'site_visit_id' => 'required|exists:site_visits,id',
    ]);

    // ✅ Decode JSON data once
    $calculationData = json_decode($validated['data'], true);

    // ✅ Find associated site visit
    $siteVisit = SiteVisit::findOrFail($validated['site_visit_id']);

    // ✅ Check if a calculation of this type already exists for this site visit
    $existing = $siteVisit->calculations()
        ->where('calculation_type', $validated['calculation_type'])
        ->first();

    if ($existing) {
        return redirect()
            ->route('clients.show', $siteVisit->client_id)
            ->with('warning', 'A calculation of this type already exists for this site visit.');
    }

    // ✅ Create new calculation if not already exists
    $siteVisit->calculations()->create([
        'calculation_type' => $validated['calculation_type'],
        'data' => $calculationData,
    ]);

    return redirect()
        ->route('clients.show', $siteVisit->client_id)
        ->with('success', 'Calculation saved to site visit.');
}


    public function destroy(Client $client, SiteVisit $siteVisit)
    {
        $siteVisit->delete();

        return redirect()
            ->route('clients.site-visits.index', $client)
            ->with('success', 'Site visit deleted.');
    }

    public function show(Client $client, SiteVisit $siteVisit)
    {
        $calculations = $siteVisit->calculations()->latest()->get();

        return view('site-visits.show', compact('client', 'siteVisit', 'calculations'));
    }
}

