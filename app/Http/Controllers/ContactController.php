<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactTag;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $type = $request->query('type', 'all');

        $contacts = Contact::query()
            ->whereNull('archived_at') // Exclude archived contacts
            ->when($type && $type !== 'all', fn($q) => $q->where('contact_type', $type))
            ->when($search, function ($query, $term) {
                $query->where(function ($q) use ($term) {
                    $q->where('first_name', 'like', "%{$term}%")
                      ->orWhere('last_name', 'like', "%{$term}%")
                      ->orWhere('company_name', 'like', "%{$term}%");
                });
            })
            ->orderByRaw("CASE WHEN company_name IS NULL OR company_name = '' THEN 1 ELSE 0 END")
            ->orderBy('company_name')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(50)
            ->withQueryString();

        return view('contacts.index', ['contacts' => $contacts, 'search' => $search, 'type' => $type]);
    }

    public function create()
    {
        $allTags = ContactTag::orderBy('name')->get();
        return view('clients.create', ['types' => Contact::types(), 'allTags' => $allTags]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name'  => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'contact_type' => ['required', 'in:'.implode(',', Contact::types())],
            'email'      => 'nullable|email',
            'email2'     => 'nullable|email',
            'phone'      => ['nullable','regex:/^(\\+1\\s?)?\\(?\\d{3}\\)?[\\s.-]?\\d{3}[\\s.-]?\\d{4}$/'],
            'mobile'     => ['nullable','regex:/^(\\+1\\s?)?\\(?\\d{3}\\)?[\\s.-]?\\d{3}[\\s.-]?\\d{4}$/'],
            'phone2'     => ['nullable','regex:/^(\\+1\\s?)?\\(?\\d{3}\\)?[\\s.-]?\\d{3}[\\s.-]?\\d{4}$/'],
            'address'    => 'nullable|string|max:255',
            'city'       => 'nullable|string|max:120',
            'state'      => 'nullable|string|max:80',
            'postal_code'=> 'nullable|string|max:20',
        ]);

        // Require at least one name identifier
        if (empty($validated['first_name']) && empty($validated['last_name']) && empty($validated['company_name'])) {
            return back()->withErrors(['first_name' => 'At least one of First Name, Last Name, or Company Name is required.'])->withInput();
        }

        // Normalize phone mask to (XXX) XXX-XXXX
        foreach (['phone','mobile','phone2'] as $p) {
            if (!empty($validated[$p])) {
                $digits = preg_replace('/[^0-9]/', '', (string) $validated[$p]);
                if (strlen($digits) === 11 && str_starts_with($digits, '1')) { $digits = substr($digits, 1); }
                if (strlen($digits) === 10) {
                    $validated[$p] = sprintf('(%s) %s-%s', substr($digits,0,3), substr($digits,3,3), substr($digits,6));
                }
            }
        }

        $contact = Contact::create($validated);

        // Sync tags if provided
        if (isset($validated['tags'])) {
            $contact->tags()->sync($validated['tags']);
        }

        return redirect(url('contacts/'.$contact->getKey()))->with('success', 'Contact added successfully.');
    }

    public function show(Contact $contact)
    {
        $tab = request('tab', 'info');

        $properties = $contact->properties()
            ->withCount('siteVisits')
            ->orderByDesc('is_primary')
            ->orderBy('name')
            ->get();

        // Estimates & Invoices
        $estimates = $contact->hasMany(\App\Models\Estimate::class, 'client_id')
            ->with('property')
            ->latest('created_at')
            ->take(50)
            ->get();

        $invoices = \App\Models\Invoice::with('estimate')
            ->whereHas('estimate', function($q) use ($contact) {
                $q->where('client_id', $contact->id);
            })
            ->latest('created_at')
            ->take(50)
            ->get();

        // Communications data
        $todos = \App\Models\Todo::with('property')
            ->where('client_id', $contact->id)
            ->orderByDesc('updated_at')
            ->take(200)
            ->get();

        $emailEvents = \App\Models\Estimate::with(['property','emailSender'])
            ->where('client_id', $contact->id)
            ->whereNotNull('email_last_sent_at')
            ->orderByDesc('email_last_sent_at')
            ->take(100)
            ->get();

        $siteVisits = $contact->siteVisits()->with('property')
            ->latest('visit_date')
            ->take(100)
            ->get();

        return view('clients.show', [
            'contact' => $contact,
            'tab' => $tab,
            'properties' => $properties,
            'estimates' => $estimates,
            'invoices' => $invoices,
            'todos' => $todos,
            'emailEvents' => $emailEvents,
            'siteVisits' => $siteVisits,
        ]);
    }

    public function edit(Contact $contact)
    {
        $allTags = ContactTag::orderBy('name')->get();
        return view('clients.edit', ['client' => $contact, 'types' => Contact::types(), 'allTags' => $allTags]);
    }

    public function update(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name'  => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'contact_type' => ['required', 'in:'.implode(',', Contact::types())],
            'email'      => 'nullable|email',
            'email2'     => 'nullable|email',
            'phone'      => ['nullable','regex:/^(\\+1\\s?)?\\(?\\d{3}\\)?[\\s.-]?\\d{3}[\\s.-]?\\d{4}$/'],
            'mobile'     => ['nullable','regex:/^(\\+1\\s?)?\\(?\\d{3}\\)?[\\s.-]?\\d{3}[\\s.-]?\\d{4}$/'],
            'phone2'     => ['nullable','regex:/^(\\+1\\s?)?\\(?\\d{3}\\)?[\\s.-]?\\d{3}[\\s.-]?\\d{4}$/'],
            'address'    => 'nullable|string|max:255',
            'city'       => 'nullable|string|max:120',
            'state'      => 'nullable|string|max:80',
            'postal_code'=> 'nullable|string|max:20',
            'tags'       => 'nullable|array',
            'tags.*'     => 'exists:contact_tags,id',
        ]);

        // Require at least one name identifier
        if (empty($validated['first_name']) && empty($validated['last_name']) && empty($validated['company_name'])) {
            return back()->withErrors(['first_name' => 'At least one of First Name, Last Name, or Company Name is required.'])->withInput();
        }

        // Normalize phone mask to (XXX) XXX-XXXX
        foreach (['phone','mobile','phone2'] as $p) {
            if (!empty($validated[$p])) {
                $digits = preg_replace('/[^0-9]/', '', (string) $validated[$p]);
                if (strlen($digits) === 11 && str_starts_with($digits, '1')) { $digits = substr($digits, 1); }
                if (strlen($digits) === 10) {
                    $validated[$p] = sprintf('(%s) %s-%s', substr($digits,0,3), substr($digits,3,3), substr($digits,6));
                }
            }
        }

        $contact->update($validated);

        // Sync tags if provided
        if (isset($validated['tags'])) {
            $contact->tags()->sync($validated['tags']);
        }

        return redirect(url('contacts/'.$contact->getKey()))->with('success', 'Contact updated successfully.');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect()->route('contacts.index')->with('success', 'Contact deleted successfully.');
    }

    public function bulkTags(Request $request)
    {
        $validated = $request->validate([
            'contact_ids' => 'required|string',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:contact_tags,id',
        ]);

        $contactIds = array_filter(explode(',', $validated['contact_ids']));
        $tagIds = $validated['tags'] ?? [];

        if (empty($contactIds)) {
            return back()->with('error', 'No contacts selected.');
        }

        $contacts = Contact::whereIn('id', $contactIds)->get();
        
        foreach ($contacts as $contact) {
            $contact->tags()->sync($tagIds);
        }

        return back()->with('success', 'Tags updated for ' . count($contacts) . ' contact(s).');
    }

    public function bulkArchive(Request $request)
    {
        $validated = $request->validate([
            'contact_ids' => 'required|string',
        ]);

        $contactIds = array_filter(explode(',', $validated['contact_ids']));

        if (empty($contactIds)) {
            return back()->with('error', 'No contacts selected.');
        }

        $count = Contact::whereIn('id', $contactIds)->update(['archived_at' => now()]);

        return back()->with('success', $count . ' contact(s) archived successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'contact_ids' => 'required|string',
        ]);

        $contactIds = array_filter(explode(',', $validated['contact_ids']));

        if (empty($contactIds)) {
            return back()->with('error', 'No contacts selected.');
        }

        $count = Contact::whereIn('id', $contactIds)->delete();

        return back()->with('success', $count . ' contact(s) deleted successfully.');
    }
}
