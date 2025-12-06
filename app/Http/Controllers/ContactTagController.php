<?php

namespace App\Http\Controllers;

use App\Models\ContactTag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContactTagController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        
        $tags = ContactTag::withCount('contacts')
            ->when($search, function($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(20);

        return view('admin.contact-tags.index', compact('tags', 'search'));
    }

    public function create()
    {
        return view('admin.contact-tags.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:contact_tags,name',
            'color' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        ContactTag::create($validated);

        return redirect()
            ->route('admin.contact-tags.index')
            ->with('success', 'Tag created successfully.');
    }

    public function edit(ContactTag $contactTag)
    {
        return view('admin.contact-tags.edit', ['tag' => $contactTag]);
    }

    public function update(Request $request, ContactTag $contactTag)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:contact_tags,name,' . $contactTag->id,
            'color' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $contactTag->update($validated);

        return redirect()
            ->route('admin.contact-tags.index')
            ->with('success', 'Tag updated successfully.');
    }

    public function destroy(ContactTag $contactTag)
    {
        $contactCount = $contactTag->contacts()->count();
        
        if ($contactCount > 0) {
            return back()->with('error', "Cannot delete tag. It is currently used by {$contactCount} contact(s).");
        }

        $contactTag->delete();

        return redirect()
            ->route('admin.contact-tags.index')
            ->with('success', 'Tag deleted successfully.');
    }
}
