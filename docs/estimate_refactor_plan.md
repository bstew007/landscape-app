# Estimate View Refactor Plan (Phase 1)

## Goals
- Split `resources/views/estimates/show.blade.php` (~1.2k lines) into logical partials.
- Introduce scoped Alpine/JS controllers so each section handles its own behavior.
- Prepare the template for the new Add Items workspace without changing current UX yet.

## Current Structure Snapshot

| Section | Lines (approx) | Key Responsibilities |
| --- | --- | --- |
| Header + globals | 1–120 | Page header, action buttons, inline `<script>` that seeds globals (`window.__calcRoutes`, `__estimateSetup`, etc.). |
| Calculator drawer (`#calcDrawer`) | 120–360 | Separate slide-over with calculator tabs, template list, and add buttons. |
| Estimate tabs | 360–520 | Alpine component controlling Overview / Work & Pricing / Notes / Crew / Analysis / Files. |
| Overview panels | 520–720 | Summary cards, invoice snapshot. |
| Work areas accordion | 720–1010 | Area cards with inline forms, Add Items CTA, tables per area (includes `estimates.partials.item-row`). |
| Add Items slide-over v1 | 1010–1260 | Current multi-tab form stack (labor/materials/equipment/etc.). |
| Modals (new labor/material) | 1260+ | Blade component includes. |

Every region shares the same `estimateShowComponent` data object, so toggles (tabs, add-items drawer, calculator drawer) are entangled.

## Proposed Partial Breakdown

| New File | Content | Notes |
| --- | --- | --- |
| `resources/views/estimates/show/header.blade.php` | Page header, action buttons, global config script. | Replaces top block; easier to tweak actions later. |
| `resources/views/estimates/show/tabs.blade.php` | Tabs bar + wrapper for `overview`, `work`, `notes`, etc. | Each tab `@include` into slots. |
| `resources/views/estimates/show/overview.blade.php` | Client/property summary, KPI cards, invoice card. | Pure markup partial. |
| `resources/views/estimates/show/work-areas.blade.php` | Work area accordion + tables (standard estimates). | Contains `@each('estimates.partials.item-row', ...)`. |
| `resources/views/estimates/show/services.blade.php` | Placeholder for maintenance mode (Phase 3). | Hook up later but stub now. |
| `resources/views/estimates/show/add-items.blade.php` | Slide-over markup (to be replaced by workspace v2). | Phase 1 just relocates existing blocks. |
| `resources/views/estimates/show/calculator-drawer.blade.php` | “Add via Calculator” slide-over. | Keeps template manageable. |
| `resources/views/estimates/show/modals.blade.php` | Labor/material modals. | Shared include. |

Parent view (`show.blade.php`) becomes a short orchestrator that sets data attributes + includes partials.

### Data Dependencies per Partial
| Partial | Inputs Needed |
| --- | --- |
| `header` | `$estimate`, `$calcRoutes`, `$templatesRoute`, `$importRoute`, `$galleryRoute`, `$previewEmailRoute`, `$printRoute`, booleans for re-open states, `session('recent_item_id')`. |
| `tabs` | Alpine initial state (`$initialState`), tab labels. |
| `overview` | `$estimate`, `$siteVisit`, `$budgetStats`, `$invoice`, `$client`, `$property`. |
| `work-areas` | `$estimate`, `$estimate->areas`, grouped items per area (reuse `$estimate->items->groupBy('area_id')`), default margin values, `$laborCatalog`, `$materials`. |
| `services` (future) | `$estimate->services`, service metrics. |
| `add-items` | `$estimate`, `$laborCatalog`, `$materials`, `$defaultMarginPercent`, `$defaultMarginRate`, area list, session flags. |
| `calculator-drawer` | `$calcRoutes`, `$templatesRoute`, `$importRoute`, `$galleryRoute`, `$estimate`. |
| `modals` | Form partials for labor/material creation, `$estimate` for redirect/back links. |

Ensure each include receives only what it needs (pass via `@include(..., compact('estimate', ...))`) to keep files testable.

## JS Module Split

| Module | Responsibility |
| --- | --- |
| `resources/js/estimate-shell.js` | Bootstraps page-level Alpine state (tab switching, overlay spinner, refresh/save all) and exposes config. |
| `resources/js/add-items-panel.js` | Handles slide-over state, catalog forms, site-visit tab (future). Lives next to new workspace markup. |
| `resources/js/calculator-drawer.js` | Existing `initEstimateCalculatorDrawer` wrapper; current module already exists, just import here. |

`estimate-show.js` will become a thin orchestrator that imports the three modules and calls their initializers; legacy code lives there until each responsibility migrates.

## Migration Steps
1. **Scaffold partials** – move markup without changing behavior; ensure includes receive the same data the monolithic view had.
2. **Refactor JS bootstrapping** – expose config via `window.estimateConfig` and update legacy code references (while keeping old names for now to avoid regressions).
3. **Regression pass** – manually test tabs, Add Items drawer, calculator drawer, area editing, modals. Add smoke tests if practical (e.g., Pest unit Dusk test stub) to validate partialization didn’t break data attributes.

This layout provides clean seams for the Add Items workspace work coming next.
