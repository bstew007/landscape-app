# Budget Editor Architecture and Guide

Date: 2025-11-20
Owner: Dev Team

## Overview
The Budget Editor has been restructured for maintainability and clarity. The UI is split into Blade partials (one per section), with per-section Alpine modules that encapsulate behavior and calculations. Common UI patterns are implemented as Blade components to reduce duplication and improve consistency.

This document explains the current structure, how the page is assembled, where the logic lives, and how to extend it. It also outlines ideas for future improvements.

## High-Level Structure

- Route / Controller
  - Standard resource controller methods serve the Budget edit form with a `$budget` model containing inputs/outputs.

- View (root scaffold)
  - resources/views/admin/budgets/edit.blade.php
    - Provides the page frame (header, sidebar, main form).
    - Includes section partials with `@include('admin.budgets.partials._<section>')`.
    - Seeds initial values into `window.__initial*` variables for Alpine hydration.
    - Defines a small, root Alpine store (`window.budgetEditor`) to keep shared state accessible (e.g., sidebar totals).

- Section Partials
  - resources/views/admin/budgets/partials/
    - _sales.blade.php
    - _field_labor.blade.php
    - _equipment.blade.php
    - _materials.blade.php
    - _subcontracting.blade.php
    - _overhead.blade.php
    - _profit_loss.blade.php (stub)
    - _oh_recovery.blade.php (stub)
    - _analysis.blade.php (stub)

- Alpine Section Modules
  - resources/js/budget/
    - sales.js → `salesEditor()`
    - fieldLabor.js → `fieldLaborEditor(root)`
    - equipment.js → `equipmentEditor(root)`
    - materials.js → `materialsEditor(root)`
    - subcontracting.js → `subcontractingEditor(root)`
    - overhead.js → `overheadEditor(root)`
  - Registered in resources/js/app.js as globals (e.g., `window.salesEditor = salesEditor`).

- Shared UI Components
  - resources/views/components/
    - panel-card.blade.php → Card shell with `title` and `icon` slot
    - compact-input-row.blade.php → Compact label/value row used in dense panels
    - ratio-pill.blade.php → Reusable ratio pill (currently used in the sidebar only; graphics panels now show plain numbers)

## Rendering Flow

1) edit.blade.php renders the page shell, sidebar, and form.
2) Server-side data is JSON-encoded into `window.__initial*` variables.
3) `x-data="budgetEditor()"` initializes the root store, then each section sets `x-data="<section>Editor($root)"` to access its own logic and the shared root state.
4) User interactions update Alpine state; inputs are bound via `x-model` to persist on submit.

## Data Model and Binding

- Each partial binds its inputs under the `inputs[...]` namespace (e.g., `inputs[equipment][rows][i][type]`).
- Root-level derived values (e.g., sidebar metrics) read from section totals via the root store.
- Deletions: Equipment and Overhead Equipment rows are persisted by overwriting the full list on update (missing list = empty → deletion).

## Current UI/UX Decisions

- Graphics panels show ratios as large numeric values (no pills) to avoid crowding.
- Sidebar shows compact pills for quick-glance metrics.
- Dense panels (e.g., Equipment/Overhead Key Factors) use `<x-compact-input-row>` to keep layouts compact and consistent.

## Files to Know

- Root view: `resources/views/admin/budgets/edit.blade.php`
- Partials: `resources/views/admin/budgets/partials/*.blade.php`
- Section scripts: `resources/js/budget/*.js`
- App bootstrap: `resources/js/app.js`
- Components: `resources/views/components/*.blade.php`

## Adding or Extending a Section

Use this checklist when adding a new budget section (e.g., Profit/Loss, OH Recovery, Analysis):

1) Create a Blade partial in `resources/views/admin/budgets/partials/_my_section.blade.php`.
   - Use `<x-panel-card>` for graphics/info panels.
   - Use `<x-compact-input-row>` for dense input rows.
   - Bind inputs under a logical namespace: `inputs[my_section][...]`.

2) Create an Alpine module in `resources/js/budget/mySection.js`.
   - Export a function, e.g., `export function mySectionEditor(root) { return { /* state, methods */ } }`.
   - Use `root` to read cross-section values (e.g., sales forecast) when needed.

3) Register the module in `resources/js/app.js`.
   - Import your editor and attach to `window` (e.g., `window.mySectionEditor = mySectionEditor`).

4) Wire the partial to the editor.
   - Add `x-data="mySectionEditor($root)"` to the section root.

5) Include partial in edit.blade.php.
   - Add `@include('admin.budgets.partials._my_section')` at the appropriate place.

6) Persist and seed initial state.
   - Update the controller to seed initial values into `window.__initial*` if necessary.

## Current Status

- Modularized Sections: Sales, Field Labor, Equipment, Materials, Subcontracting, Overhead.
- UI Components: Panel cards and compact input rows applied to the main panels.
- Pills: Removed from graphics panels; kept (or removable) in the sidebar only.
- Duplicates: Legacy inline sections were removed after partials were verified.

## Upcoming Sections (to expand)

We have 3 sections to complete/expand:

1) Profit/Loss
   - Partial: `_profit_loss.blade.php` (stub UI is present)
   - Add an Alpine module (e.g., `profitLossEditor(root)`), and flesh out the P&L metrics/cards.

2) OH Recovery
   - Partial: `_oh_recovery.blade.php` (stub UI is present)
   - Add an Alpine module to configure and display recovery method/allocations.

3) Analysis
   - Partial: `_analysis.blade.php` (stub UI is present)
   - Add charts or KPIs as `x-panel-card` items and a small editor if interactive.

## Conventions and Tips

- Keep section logic inside its module (`resources/js/budget/<section>.js`).
- Share derived state via the root store only when needed (e.g., sales forecast used for ratios in other sections).
- Prefer Blade components for repeated UI patterns (e.g., cards, rows) to keep partials readable.
- When adding select fields whose values come from saved JSON, normalize values so `<select>` correctly reflects them (strings vs numbers).

## Future Enhancements

- Shared JS utilities
  - Extract helpers like `formatMoney`, `within4`, and common math to `resources/js/budget/utils.js` and import into section modules.

- Table header component
  - Abstract repeated table header rows into a Blade component for consistency and fewer edits.

- Root Alpine store
  - Convert `window.budgetEditor` (inline) into a module imported by app.js for consistency and testability.

- Visualization components
  - Reusable components for progress bars, pie charts, and ratio displays to simplify the graphics row markup.

- Validation and hints
  - Add inline validations or helper text for sensitive inputs (e.g., months used per year, division months) to reduce user error.

- Tests
  - Add feature tests for critical behaviors (row add/remove, totals/ratios) and form submissions to prevent regressions.

## Quick Troubleshooting

- Graphics not updating: Ensure section editor is bound with `x-data` and its module is registered in app.js.
- Values not persisting: Confirm input names under `inputs[...]` and that the controller maps them into the `$budget->inputs` JSON.
- Select fields not reflecting saved values: Normalize saved numbers to strings for `<select>` bindings.

## Summary
The Budget Editor is now split into clear, manageable pieces:
- One partial per section
- One Alpine editor module per section
- Reusable UI components for cards and compact rows

This setup makes it straightforward to enhance existing sections and add new ones (Profit/Loss, OH Recovery, Analysis) while keeping complexity under control.
