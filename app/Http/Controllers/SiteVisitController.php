<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use App\Models\Client;
use App\Models\SiteVisit;
use App\Models\SiteVisitPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
    $validated = $request->validate([
        'calculation_type' => 'required|string',
        'data' => 'required|string',
        'site_visit_id' => 'required|exists:site_visits,id',
        'calculation_id' => 'nullable|exists:calculations,id',
    ]);

    $calculationData = json_decode($validated['data'], true);// Already a JSON string, validated as string


    $siteVisit = SiteVisit::findOrFail($validated['site_visit_id']);

    if (!empty($validated['calculation_id'])) {
        // ✅ Update existing calculation
        $calc = Calculation::findOrFail($validated['calculation_id']);
        $calc->update(['data' => $calculationData]);

        return redirect()
            ->route('clients.show', $siteVisit->client_id)
            ->with('success', 'Calculation updated successfully.');
    }

    // ✅ Check if a new calculation of this type already exists
    $existing = $siteVisit->calculations()
        ->where('calculation_type', $validated['calculation_type'])
        ->first();

    if ($existing) {
        return redirect()
            ->route('clients.show', $siteVisit->client_id)
            ->with('warning', 'A calculation of this type already exists for this site visit.');
    }

    $siteVisit->calculations()->create([
    'calculation_type' => $validated['calculation_type'],
    'data' => json_encode($calculationData), // <-- This is essential
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
        $siteVisit->load('photos');
        $calculations = $siteVisit->calculations()->latest()->get();

        return view('site-visits.show', compact('client', 'siteVisit', 'calculations'));
    }

    public function storePhoto(Request $request, Client $client, SiteVisit $siteVisit)
    {
        $validated = $request->validate([
            'photo' => 'required|image|max:5120',
            'caption' => 'nullable|string|max:255',
        ]);

        $path = $request->file('photo')->store("site-visits/{$siteVisit->id}", 'public');

        $siteVisit->photos()->create([
            'path' => $path,
            'caption' => $validated['caption'] ?? null,
            'uploaded_by' => optional($request->user())->id,
        ]);

        return back()->with('success', 'Photo uploaded successfully.');
    }

    public function destroyPhoto(Client $client, SiteVisit $siteVisit, SiteVisitPhoto $photo)
    {
        abort_unless($photo->site_visit_id === $siteVisit->id, 404);

        Storage::disk('public')->delete($photo->path);
        $photo->delete();

        return back()->with('success', 'Photo deleted.');
    }
}
