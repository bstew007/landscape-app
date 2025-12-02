# Site Visit Data Inventory

## Existing Tables

| Table | Key Columns | Notes |
| --- | --- | --- |
| `site_visits` | `id`, `client_id`, `property_id`, `visit_date`, `notes`, timestamps | Built in 2025-10-18 migration; property linkage added 2025-11-10 to keep visits tied to addresses. |
| `site_visit_photos` | `id`, `site_visit_id`, `path`, `caption` | Currently only used for media uploads on the visit detail page. |
| `calculations` | `id`, `site_visit_id`, `estimate_id`, `calculation_type`, `data (json)`, `is_template`, `client_id`, `property_id`, `created_by` | Stores calculator payloads (labor/material breakdowns live inside `data`). Drives the estimate import step today. |

## Current Import Path

- `EstimateController::buildLineItemsFromSiteVisit()` (`app/Http/Controllers/EstimateController.php:420`) pulls every calculation for a visit, extracts `final_price`, `labor_cost`, `material_total`, and returns a coarse array of line items (label, qty=1, rate). Used when pre-populating estimate forms or hitting `GET /site-visits/{id}/estimate-line-items`.
- `CalculationImportService` (`app/Services/CalculationImportService.php`) performs the real work after an estimate is saved:
  - `importMaterials()` walks `data['materials']` for each calculation, creating individual estimate items (name, qty, unit cost, margin rate).
  - `importLabor()` converts `labor_cost` + hours into a labor line.
  - `importFeeOrMarkup()` adds a fee when budgets don’t provide margin.
  - `importSiteVisitCalculations()` loops every calculation tied to the visit and pipes it through the helpers, optionally replacing prior calculator-derived lines.

### Key Takeaways
- We already capture structured labor/material detail inside each calculator’s JSON payload; no extra `site_visit_items` table yet.
- The importer currently assigns everything to whichever work area the estimate has active (defaults to “General”) because no area/service mapping is provided; Add Items v2 needs a UI hook to select the target area/service per import.
- Validation/margins rely on the active budget (`BudgetService::active()`), so migrations from site visits should keep using that service.

## Available Endpoints / Hooks

| Endpoint | Method | Purpose |
| --- | --- | --- |
| `GET /site-visits/{visit}/estimate-line-items` (`EstimateController@siteVisitLineItems`) | JSON preview of summarized line items. Good for the Site Visit tab’s “preview import” drawer. |
| `POST /estimates/{estimate}/items` (`EstimateItemController@store`) | Accepts catalog/manual submissions. Already returns `item`, `row_html`, and `totals`; the Site Visit tab can reuse this endpoint once it packages the captured material/labor info. |
| `CalculationImportService::importCalculation()` | Server-side hook to re-import calculator payloads into an estimate (used after saving the estimate). We’ll reuse this when migrating selected site-visit entries into specific areas/services. |

## Data Requirements for Add Items Workspace

To power the new Site Visit tab + general workspace we need:
1. **Work Area / Service list** – already available via `window.__estimateSetup.areas`; we’ll extend it with `services` once the maintenance type ships.
2. **Catalog datasets** – `materials`, `laborCatalog`, (future) equipment/sub catalog. Currently injected from `EstimateController@show`.
3. **Site Visit payload** – either:
   - Lazy-fetch via `GET /site-visits/{visit}/estimate-line-items` for summary rows, then request a specific calculation record when “Add to Area” is clicked, or
   - Preload `estimate->siteVisit->calculations` (already eager-loaded) and expose a minified array via `window.__estimateSetup.siteVisitCalculations`.
4. **Config blob** – consolidate `window.__estimateItemsBaseUrl`, `__estimateItemsUpdateBaseUrl`, etc., into `window.estimateConfig` so the workspace module can read: estimate id, CSRF, routes, default margins, and optional site-visit data.

## Gaps / To Decide

- Whether we keep storing calculator data in `calculations.data` only or introduce a dedicated `site_visit_items` table for faster querying/filtering. The new Site Visit tab could work with the existing JSON but may need denormalized helpers for performance.
- Area/service mapping for imports: we likely need to extend `CalculationImportService::importSiteVisitCalculations()` to accept an `area_id`/`service_id` parameter so UI selections carry through.
- Multi-select behavior: importing several calculator outputs at once currently means re-running the importer on every estimate save. The workspace should either:
  - Trigger a new endpoint (e.g., `POST /estimates/{estimate}/import-calculation`) that wraps the existing service and returns new rows, or
  - Materialize site-visit entries as pending “staged” records (`estimate_site_visit_items`) before landing them as real estimate items.

Documenting these pieces now lets us wire the workspace without guessing where data lives.
