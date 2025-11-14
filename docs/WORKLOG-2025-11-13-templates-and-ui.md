# Worklog: Calculator Templates + UI Standardization (2025-11-13)

This document captures what we implemented today and the next steps to continue tomorrow. It’s safe to commit to the repo and serves as a quick-start for the office.

## Summary
We added a “Template Mode” for the Mulching calculator so line items can be created directly in an Estimate without a Site Visit. We also standardized calculator UI (buttons, headings, client info) and added an in-estimate panel for templates.

Additionally, we extended Template Mode and the shared result actions to Paver Patio, Retaining Wall, Weeding, Turf Mowing, Planting, Fence, Pruning, and Pine Needles. On the estimate page, we added a Refresh button and wired auto-refresh with a spinner for common actions.

## Changes Implemented

0) Estimate page quality-of-life
- Refresh button added; auto-refresh with a spinner after common actions (add/edit/remove/reorder items).
- Work area headers now use the area names instead of “Area {id}”.

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

5) Template Mode across calculators + Mulching (MVP pattern)
- Extended to: Syn-Turf, Paver Patio, Retaining Wall, Weeding, Turf Mowing, Planting, Fence, Pruning, Pine Needles
- Pages accept `?mode=template&estimate_id={id}` (site_visit optional), save template rows, and import from estimate drawer

5a) Mulching Calculator: Template Mode (MVP)
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

6) Syn-Turf Enhancements
- Production rates:
  - Faster infill (0.0025 hr/sqft)
  - Excavation by cubic yards for equipment methods (skid/mini)
  - Added base_install (0.20 hr/cy)
- Form:
  - Excavation Method toggle (Generic/Skid/Mini), Excavation Depth (in)
  - ABC Depth (in), Rock Dust Depth (in) → material lines (cy) without defaults
  - Tamper rental as a fee $125/day with Rental Days field
- Controller/service:
  - Convert sqft+depth to CY for excavation/base; add materials, labor, fee as appropriate

## How to Test (Quick)
1) Install/Update
- Run migrations:
  - `php artisan migrate`
- Clear caches:
  - `php artisan optimize:clear`
  - `php artisan view:clear`
- Rebuild assets (for new Tailwind components):
  - `npm run dev` (or `npm run build`)

2) Templates Flow (Mulching + Gallery)
- Open any Estimate (e.g. /estimates/{id})
- Use “Add via Calculator” > Templates tab
  - Refresh templates, Import (Append/Replace), or Go to Gallery (pre-filtered by type)
- Gallery (/calculator/templates)
  - Filter by type/scope/date, search by name
  - Rename, Delete, Duplicate templates
  - Import modal: search estimates (by # or title), select Work Area, Replace toggle, Import and redirect to estimate
  - Sidebar includes a “Calculator Templates” link

3) UI Standardization + Syn-Turf
- Open several calculator forms and results pages to confirm:
  - Syn-Turf now shows the Excavation Method toggle and new ABC/Rock Dust/Tamper fields on the form
  - Results include ABC/Rock Dust material lines (when depths provided) and tamper fee when selected
  - Consistent header, section headings, and client info
  - Standardized buttons on forms and results (Save/Append/Replace/PDF/Back)

## Known Issues / Notes
- If you saved a template before the `Calculation` model update (missing is_template in $fillable), it won’t appear in the drawer. Either re-save a new template or manually flag the row in DB:
  ```sql
  UPDATE calculations
  SET is_template = 1, template_name = 'My Template'
  WHERE id = X;
  ```
- Ensure `site_visit_id` is nullable in the calculations table; otherwise, template mode will fail.
- Seed updated production rates after pulling:
  - `php artisan db:seed --class=ProductionRateSeeder`

## Next Steps
1) Import modal enhancements
- Add validation message if typed estimate id is invalid
- Show recent estimates on focus (empty search)
- Save last used and remember Replace toggle per user

2) Gallery polish
- Admin-only “Make Global” toggle with badge on cards
- AJAX rename/delete/duplicate + toasts
- Details drawer with material/labor preview

3) Estimates integration
- “Go to Gallery” links implemented; add “Back to Estimate” crumb after import (optional)
- Pre-fill scope/search from estimate context

4) Syn-Turf mode polish
- Tons preview (read-only) with configurable conversion factors
- Small UI refinements across sections

5) Docs / GIFs
- Add a short GIF for the Import modal and Gallery filters
- README: update Template Mode + Gallery section

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
