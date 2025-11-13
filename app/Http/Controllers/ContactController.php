<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $type = $request->query('type', 'client');

        $contacts = Contact::query()
            ->when($type, fn($q) => $q->where('contact_type', $type))
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

        return view('contacts.index', ['contacts' => $contacts, 'search' => $search, 'type' => $type]);
    }

    public function create()
    {
        return view('clients.create', ['types' => Contact::types()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'contact_type' => ['required', 'in:'.implode(',', Contact::types())],
            'email'      => 'nullable|email',
            'email2'     => 'nullable|email',
            'phone'      => ['nullable','regex:/^(\\+1\\s?)?\\(?\\d{3}\\)?[\\s.-]?\\d{3}[\\s.-]?\\d{4}$/'],
            'phone2'     => ['nullable','regex:/^(\\+1\\s?)?\\(?\\d{3}\\)?[\\s.-]?\\d{3}[\\s.-]?\\d{4}$/'],
            'address'    => 'nullable|string',
        ]);

        Contact::create($validated);

        return redirect()->route('contacts.index')->with('success', 'Contact added successfully.');
    }

    public function show(Contact $contact)
    {
        $properties = $contact->properties()
            ->withCount('siteVisits')
            ->orderByDesc('is_primary')
            ->orderBy('name')
            ->get();

        $siteVisitOptions = $contact->siteVisits()
            ->with('property')
            ->orderByDesc('visit_date')
            ->orderByDesc('id')
            ->get();

        $siteVisitSummaries = $siteVisitOptions->map(function ($visit) {
            return [
                'id' => $visit->id,
                'date' => optional($visit->visit_date)->format('F j, Y'),
                'property' => optional($visit->property)->name,
            ];
        });

        $selectedSiteVisit = $siteVisitOptions->firstWhere('id', (int) request('site_visit_id'))
            ?? $siteVisitOptions->first();

        $recentVisits = $siteVisitOptions->take(5);

        return view('clients.show', [
            'client' => $contact,
            'contact' => $contact,
            'siteVisit' => $selectedSiteVisit,
            'siteVisitOptions' => $siteVisitOptions,
            'siteVisitSummaries' => $siteVisitSummaries,
            'properties' => $properties,
            'recentVisits' => $recentVisits,
        ]);
    }

    public function edit(Contact $contact)
    {
        return view('clients.edit', ['client' => $contact, 'types' => Contact::types()]);
    }

    public function update(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'contact_type' => ['required', 'in:'.implode(',', Contact::types())],
            'email'      => 'nullable|email',
            'email2'     => 'nullable|email',
            'phone'      => ['nullable','regex:/^(\\+1\\s?)?\\(?\\d{3}\\)?[\\s.-]?\\d{3}[\\s.-]?\\d{4}$/'],
            'phone2'     => ['nullable','regex:/^(\\+1\\s?)?\\(?\\d{3}\\)?[\\s.-]?\\d{3}[\\s.-]?\\d{4}$/'],
            'address'    => 'nullable|string',
        ]);

        $contact->update($validated);

        return redirect()->route('contacts.index')->with('success', 'Contact updated successfully.');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect()->route('contacts.index')->with('success', 'Contact deleted successfully.');
    }
}
