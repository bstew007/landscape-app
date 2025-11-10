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
        $siteVisits = $client->siteVisits()->with('property')->latest()->get();

        return view('site-visits.index', compact('client', 'siteVisits'));
    }

    public function create(Client $client)
    {
        $properties = $client->properties()->orderBy('name')->get();
        $preferredPropertyId = request()->input('property_id');

        if (! $preferredPropertyId && $client->primaryProperty) {
            $preferredPropertyId = $client->primaryProperty->id;
        }

        return view('site-visits.create', [
            'client' => $client,
            'properties' => $properties,
            'preferredPropertyId' => $preferredPropertyId,
        ]);
    }

    public function store(Request $request, Client $client)
    {
        $validated = $request->validate([
            'visit_date' => 'required|date',
            'notes' => 'nullable|string',
            'property_id' => 'required|exists:properties,id',
        ]);

        $property = $client->properties()->findOrFail($validated['property_id']);

        $client->siteVisits()->create([
            'visit_date' => $validated['visit_date'],
            'notes' => $validated['notes'] ?? null,
            'property_id' => $property->id,
        ]);

        return redirect()
            ->route('clients.show', $client->id)
            ->with('success', 'Site visit created successfully.');
    }

    public function edit(Client $client, SiteVisit $siteVisit)
    {
        $properties = $client->properties()->orderBy('name')->get();

        return view('site-visits.edit', [
            'client' => $client,
            'siteVisit' => $siteVisit,
            'properties' => $properties,
            'preferredPropertyId' => $siteVisit->property_id,
        ]);
    }

    /**
     * Show the site visit selector before launching a calculator.
     */
    public function select(Request $request)
    {
        $redirectTo = $request->get('redirect_to', ''); // �o. fixed variable
        $siteVisits = SiteVisit::with(['client', 'property'])
            ->orderBy('visit_date', 'desc')
            ->get();

        return view('calculators.select-site-visit', compact('siteVisits', 'redirectTo'));
    }

    public function update(Request $request, Client $client, SiteVisit $siteVisit)
    {
        $validated = $request->validate([
            'visit_date' => 'required|date',
            'notes' => 'nullable|string',
            'property_id' => 'required|exists:properties,id',
        ]);

        $property = $client->properties()->findOrFail($validated['property_id']);

        $siteVisit->update([
            'visit_date' => $validated['visit_date'],
            'notes' => $validated['notes'] ?? null,
            'property_id' => $property->id,
        ]);

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
            // �o. Update existing calculation
            $calc = Calculation::findOrFail($validated['calculation_id']);
            $calc->update(['data' => $calculationData]);

            return redirect()
                ->route('clients.show', $siteVisit->client_id)
                ->with('success', 'Calculation updated successfully.');
        }

        // �o. Check if a new calculation of this type already exists
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
        $siteVisit->load(['photos', 'property']);
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
