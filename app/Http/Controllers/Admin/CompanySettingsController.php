<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use Illuminate\Http\Request;

class CompanySettingsController extends Controller
{
    public function edit()
    {
        $settings = CompanySetting::getSettings();
        
        return view('admin.company-settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
        ]);

        $settings = CompanySetting::getSettings();
        $settings->update($validated);

        return redirect()
            ->route('admin.company-settings.edit')
            ->with('success', 'Company settings updated successfully.');
    }
}
