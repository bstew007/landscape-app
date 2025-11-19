# Budget Editor Split Plan (Phase 1 → Phase 3)

Date: 2025-11-19
Owner: Dev Assistant

## Overview
The admin budget editor (resources/views/admin/budgets/edit.blade.php) has grown to ~2,000 lines. To improve maintainability and reduce the risk of changes, we are splitting the UI into Blade partials (Phase 1), then modularizing Alpine/JS logic (Phase 2), and finally extracting repeated UI patterns into components (Phase 3).

## Current Status

Done (Phase 1: extracted into partials and included)
- Profit / Loss → resources/views/admin/budgets/partials/_profit_loss.blade.php (included)
- OH Recovery → resources/views/admin/budgets/partials/_oh_recovery.blade.php (included)
- Analysis → resources/views/admin/budgets/partials/_analysis.blade.php (included)
- Materials → resources/views/admin/budgets/partials/_materials.blade.php (included)
- Subcontracting → resources/views/admin/budgets/partials/_subcontracting.blade.php (included)
- Equipment → resources/views/admin/budgets/partials/_equipment.blade.php (included)
- Overhead → resources/views/admin/budgets/partials/_overhead.blade.php (included)

Not Done Yet (Phase 1 extractions remaining)
- Field Labor → resources/views/admin/budgets/partials/_field_labor.blade.php (included)
- Sales → resources/views/admin/budgets/partials/_sales.blade.php (included)

Duplicates Issue (Action Needed)
- Some sections show twice in the UI because we added @include partials without fully removing the original inline markup blocks.
- Affected areas: Materials, Subcontracting, Equipment, Overhead (depending on cache and current branch state).
- Plan to resolve: remove the original inline sections from edit.blade.php now that partials are included. Confirm by a hard refresh (cache) that only one copy of each section remains.

## Phase 1 Details (Blade-only split)
- Extract each major section into a partial under resources/views/admin/budgets/partials.
- Replace the corresponding inline section in edit.blade.php with an @include.
- No changes to the Alpine data controller or server logic in Phase 1.

## Phase 2 Plan (Modularize Alpine/JS)
- Create dedicated Alpine components per section, each with its own x-data:
  - salesEditor, fieldLaborEditor, equipmentEditor, materialsEditor, subcontractingEditor, overheadEditor.
- Move logic into ES modules under resources/js/budget/* and import them via app.js.
- Extract shared helpers (formatMoney, within4, perUnitCost, etc.) into a shared module to avoid duplication.
- Feed each section its initial data via @json in the corresponding partial to limit hydration scope.

## Phase 3 Plan (UI Components)
- Extract repeated UI patterns into Blade components:
  - Card shells with title + top-right icon
  - Ratio/metric badges (pills)
  - Row actions menu (kebab) with consistent aria/keyboard support
  - Compact labeled input rows
- Apply these components across Equipment, Overhead, Field Labor, Sales, etc., to reduce markup duplication and improve consistency.

## Recent Functional Fixes Kept During Split
- Overhead Equipment breakdown panels (Owned/Leased) now working and persisted.
- Overhead Summary includes Overhead Equipment, and overheadCurrentTotal/overheadRatio include it.
- Overhead Equipment totals fixed to sum all rows and compute per-unit correctly for each class; blank Qty treated as 1.
- Server persistence of deletions:
  - Equipment rows: overwrite list on update (missing list = empty) to persist deletions.
  - Overhead Equipment rows: overwrite list on update (missing list = empty) to persist deletions.

## Risks & Mitigations
- Risk: Rendering duplicates if includes and inline sections both remain.
  - Mitigation: Systematically remove inline blocks after includes are verified; hard refresh to clear cache.
- Risk: Cross-section dependencies (ratios depend on Sales Forecast) when splitting Alpine.
  - Mitigation: Keep a shared root store or shared helpers; pass derived values as needed.

## Next Actions
1) Remove remaining inline sections for Materials, Subcontracting, Equipment, Overhead in edit.blade.php to eliminate duplicates (leave only @include calls).
2) Extract Field Labor and Sales into partials and include them.
3) Verify no visual/functional regressions; check that all Alpine bindings continue to operate.
4) Start Phase 2: modularize Alpine/JS per section.

## Notes
- No database changes required for Phase 1.
- Controller adjustments made earlier to persist deletions in Equipment and Overhead Equipment.
## Status Update (2025-11-19)

Done
- Field Labor and Sales extracted into partials and included in edit.blade.php.
- Left sidebar metrics and ratio pills wired for Sales, Field Labor, Equipment, Materials, Subcontracting, and Overhead.

Not Done
- Inline duplicates cleanup: hidden inline Equipment and Overhead blocks still present in edit.blade.php; remove in next pass, leaving only @include partials.
- Phase 2 (Alpine modularization) and Phase 3 (UI components) as outlined below.

Recommendations
- Before starting Phase 2, extract Field Labor and Sales into partials to complete Phase 1 and lock the template structure.
- For Equipment list last-column display, choose one of the following and implement:
  - Per-unit Cost/Yr/Ea only (current behavior)
  - Extended total (Qty × Cost) only
  - Dual display: "$X.xx ea | $Y.yy total" with a small-muted second value
- Clarify Division Monthly (active) semantics (Owned/Leased): keep as Division Annual / division_months, or divide by min(months_used, division_months) if preferred.
- Consider removing the Overhead Equipment tab long-term if those costs will be tracked in Overhead Expenses instead; otherwise, keep the tab and ensure totals are clearly presented.
- Add controller handling for close=1 to return to index with a toast on save.
- Add duplicate-row action and tooltip/info icons for Owned/Leased math to improve UX.

