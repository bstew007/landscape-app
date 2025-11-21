# Labor Catalog Reference

## Overview
- **Purpose:** Central hub for reusable labor rates leveraged across estimates and calculators.
- **Key Screens:** `/labor` (catalog index), `/labor/create`, `/labor/{id}/edit`, `/labor/{id}` destroy action.
- **Core Model:** `App\Models\LaborItem` persisted in `labor_catalog` table with normalized numeric columns (wage, burden, unbillable %, etc.).

## Catalog Index (`resources/views/labor/index.blade.php`)
- **Header:** Uses the compact `x-page-header` with a 🛠️ icon for quick context; only a `+ New` CTA (no import/export buttons).
- **Summary Cards:**
  - **Budget:** Shows the active budget name via `BudgetService::active()`.
  - **Overhead:** Displays the current OH markup $/hr derived from budget overhead totals.
  - **Profit:** Displays the desired profit margin (%) from the active budget.
- **Search Row:** Compact search input on the left; `Search` and `+ New` buttons sit together on the right for quick access.
- **Table Columns:** Name, Wage/Hr, Cost/Hr, Breakeven, Rate/Hr, Actions (inline Edit/Delete). Rows recompute the derived costs using each labor item’s stored averages plus the current OH markup.

## Create / Edit Form (`resources/views/labor/create.blade.php`)
- **Layout:** Modal-style UI rendered inside a compact-themed container and `x-page-header`.
- **Pricing Panel:** Three modes (budget margin, custom margin, custom price) backed by Alpine helpers:
  - Wage + overhead drives the base cost.
  - Breakeven calculation applies overtime %, labor burden %, unbillable %, and global overhead.
  - Price field mirrors `breakeven ÷ (1 - margin)` and becomes editable when `custom-price` is selected.
- **Hidden Inputs:** `base_rate` stays in sync with the displayed price using the DOM script, ensuring the stored rate matches what the user sees.
- **Validation Guards:** Controller normalizes nullable numeric fields to `0` to avoid `NOT NULL` DB errors (e.g., `labor_burden_percentage`).

## Controller / Service Flow
- `LaborController@index` paginates `LaborItem` models and augments the view with budget stats (name, OH markup, total labor hours, desired profit).
- `computeBudgetStats()` replicates the budget-derived overhead math (totals expenses + wages + equipment, divides by total labor hours) so the catalog and detail pages use the same OH reference.
- `store` / `update` use `normalizeNumericFields()` after validation to persist clean numeric values (prevents MySQL constraint errors).

## Known Issues / Constraints
- **Edit Blade Parity:** The edit view still uses earlier layouts and lacks the newer pricing calculator UX from the create blade (radio modes, inline price editor).
- **Budget Dependency:** Overhead and profit displays require an active budget; when absent, the index falls back to zeros/placeholder text.
- **Custom Price Mode:** Currently binds through a hidden input and DOM listeners; if the DOM script fails to load, custom prices may not persist. A progressive enhancement (server-side fallback) could help.
- **Missing Bulk Actions:** Import/export buttons were intentionally removed per request; consider reintroducing them elsewhere if CSV workflows are needed.

## Enhancement Ideas
1. **Edit Blade Refresh:** Mirror the create form’s calculator UX (radio controls, editable price box, breakeven label) for consistency.
2. **Server-Side Price Recalc:** Move the breakeven/price formulas into a shared PHP helper or model accessor to reduce duplication between Blade + JS.
3. **Row Badges:** Reintroduce billable/active status indicators in the table (e.g., pill badges) to aid catalog scanning.
4. **Filtering / Sorting:** Add dropdown filters (billable/active) and sortable headers for rate columns.
5. **Audit Trail:** Track who last edited each labor item (timestamps + user) so teams can trace changes after updates.
6. **Validation Messages:** Surface inline validation hints for null numeric fields (currently coerced to 0 silently).
7. **Accessibility:** Ensure the price radio group and editable input announce state changes (ARIA live regions or `aria-describedby`).
