# Worklog: Calculator Templates + UI Standardization (2025-11-13)

This document captures what we implemented today and the next steps to continue tomorrow. It’s safe to commit to the repo and serves as a quick-start for the office.

## Summary
We added a “Template Mode” for the Mulching calculator so line items can be created directly in an Estimate without a Site Visit. We also standardized calculator UI (buttons, headings, client info) and added an in-estimate panel for templates.

## Changes Implemented

1) Contacts
- Added secondary email/phone
  - DB migration: `2025_11_13_000003_add_secondary_contact_fields_to_clients_table.php`
  - Models: `Client`, `Contact` include email2/phone2 in `$fillable`
  - Validation: `ContactController@store/update` include email2/phone2
  - Views: show/index/forms updated
- Phone format validation (US): regex for phone and phone2
- “Client Details” -> “Contact Details” label updated on contact show page

2) Legacy clients.* routes maintained
- `routes/web.php`: Added legacy write routes (clients.store/update/destroy) to point to `ContactController`

3) UI standardization across calculators
- Shared button styles via Tailwind component classes in `resources/css/app.css` (.btn, .btn-primary, .btn-secondary, etc.)
- Shared partials
  - `resources/views/calculators/partials/actions.blade.php` (results actions)
  - `resources/views/calculators/partials/client_info.blade.php`
  - `resources/views/calculators/partials/form_header.blade.php`
  - `resources/views/calculators/partials/section_heading.blade.php`
- Results pages now include the shared actions with estimate picker and standardized buttons
- Form pages:
  - Show shared client info at the top
  - Use shared form/section headings
  - Use standardized buttons for submit/back

4) Retained/Fixed route structure
- Fixed unmatched brace in `routes/web.php` by consolidating estimate subroutes inside the `Route::prefix('estimates/{estimate}')` group

5) Mulching Calculator: Template Mode (MVP)
- DB migrations:
  - `2025_11_13_010000_add_template_fields_to_calculations_table.php` adds `is_template`, `template_name`, `estimate_id`
  - `2025_11_13_011000_make_site_visit_id_nullable_in_calculations_table.php` makes `site_visit_id` nullable
- Model: `App\Models\Calculation`
  - `$fillable` includes: site_visit_id, estimate_id, calculation_type, data, is_template, template_name
  - `$casts` includes: data=>array, is_template=>boolean
- Controller: `MulchingCalculatorController`
  - `showForm`: supports `?mode=template&estimate_id={id}`
  - `calculate`: when `mode=template`, allows saving as template and optionally importing into the target estimate
- Estimate-side API endpoints: `EstimateCalculatorController`
  - GET `estimates/{estimate}/calculator/templates?type=mulching`
  - POST `estimates/{estimate}/calculator/import`
  - POST `calculator/templates` (save template)
- Routes added in `routes/web.php` under the estimates group and globally for saving templates
- Estimate UI: `resources/views/estimates/show.blade.php`
  - New panel in “Work & Pricing” to:
    - Launch Mulching calculator in Template Mode
    - Load & import a saved mulching template (append/replace)

6) Fixes
- Resolved Blade parsing issue by wrapping inline HTML in HtmlString in a couple of form views (retaining-wall, paver-patio)
- Fixed parse error in routes/web.php by rebalancing group braces

## How to Test (Quick)
1) Install/Update
- Run migrations:
  - `php artisan migrate`
- Clear caches:
  - `php artisan optimize:clear`
- Rebuild assets (for new Tailwind components):
  - `npm run dev` (or `npm run build`)

2) Templates Flow (Mulching)
- Open any Estimate (e.g. /estimates/{id})
- In “Work & Pricing”, use the new “Add via Calculator” panel:
  - Click “➕ New Mulching via Calculator” (opens Template Mode)
  - Fill in inputs, set a Template Name
  - Save Template (keeps it) or Save & Import to Estimate (appends or replaces)
- Back on the estimate page, use the “Select mulching template…” dropdown to import a saved template

3) UI Standardization
- Open several calculator forms and results pages to confirm:
  - Consistent header, section headings, and client info
  - Standardized buttons on forms and results (Save/Append/Replace/PDF/Back)

## Known Issues / Notes
- If you saved a mulching template before the `Calculation` model update (missing is_template in $fillable), it won’t appear in the dropdown. Either re-save a new template or manually flag the row in DB:
  ```sql
  UPDATE calculations
  SET is_template = 1, template_name = 'My Mulch Template'
  WHERE id = X;
  ```
- Ensure `site_visit_id` is nullable in the calculations table; otherwise, template mode will fail.

## Next Steps (Proposed for Tomorrow)
1) UX: Add modal workflow to estimates.show
- “Add via Calculator” opens a modal (or side drawer) with:
  - Tabs: “Create with Calculator” | “Templates”
  - Create with Calculator: Inline Mulching mini-form (Template Mode) or deep link to full page
  - Templates: List mulching templates with preview and Import (Append/Replace)
  - Small “Refresh templates” icon next to dropdown

2) Extend Template Mode to other calculators
- Candidate next: Planting, Retaining Wall
- Repeat the Mulching pattern: `mode=template`, saving structure, import flow

3) Template Management UI
- Templates Gallery page (list by type, filter by tags/date)
- Rename/Delete templates (owner/global visibility)

4) Defaults & smarts
- Auto-suggest template_name from inputs, e.g., “Front Beds – 2 yd”
- Remember last-used labor rate and overhead inputs per user/calculator

5) Estimate-side polish
- “Add via Calculator” button in the top action bar
- Inline preview of template totals (Materials/Labor/Final) before import
- Optionally select a Work Area for imported rows

6) Tech Debt / Maintenance
- Convert any remaining inline Blade HTML strings in includes to HtmlString or dedicated partials
- Confirm all controllers pass `$siteVisit` to views (done for known cases)

## Rollback / Reference
- New and edited files are summarized above. Key ones:
  - Routes: `routes/web.php`
  - Controllers: `MulchingCalculatorController`, `EstimateCalculatorController`
  - Models: `Calculation`
  - Migrations: two for calculations table (template fields; site_visit_id nullable)
  - Views: `estimates/show.blade.php`, calculator forms/results, shared partials

## Command Cheatsheet
- Clear & rebuild
  - `php artisan optimize:clear`
  - `npm run dev`
- Migrate
  - `php artisan migrate`
- Routes
  - `php artisan route:list | Select-String "calculator"`

---
This file is intended for quick onboarding tomorrow. Feel free to add checkboxes to the Next Steps and assign owners.
