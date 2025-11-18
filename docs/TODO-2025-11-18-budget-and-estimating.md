# TODO – Budget and Estimating (2025-11-18)

This document summarizes what’s been completed, what’s in progress, and what’s next around the Budget and Estimating work. It also captures the current behavior of the Add Items menu.

## Completed (mark these as Completed on the To‑Do board)

1) Estimating – Add Items Drawer wired
- Added “Add via Calculator” drawer with tabs (Labor, Materials, Equipment, Subs, Other, Templates)
- Catalog pickers auto-populate units/costs (materials: unit, cost, tax; labor: unit, base/avg wage)
- Return-to flow for New Material / New Labor modals returns to the estimate after save

2) Catalog – Labor fields + DB migration
- Labor catalog fields added: Name, Units, Description, Internal Notes, Average Wage, Overtime Factor, Unbillable %, Labor Burden %
- Model, controller, export/import updated; estimate UI pulls Average Wage (fallback Base Rate)

3) Budget – Two-column workspace + compact theme + header
- Budget editor rebuilt as two-column layout (left menu, right panel)
- Applied compact spacing and consistent x-page-header (with icon)
- Sections: Budget Info, Sales Budget, Field Labor, Equipment, Materials, Subcontracting, Overhead, Profit/Loss, OH Recovery, Analysis

4) Sales Budget – Graphics + grid
- Divisional Sales pie (legend shows % only per division)
- Previous vs Forecast bars with totals
- Change over Previous ring with +/- percent
- Sales table rows (Acct. ID, Division, Previous $, Forecast $, % Diff, Comments) with + New and Delete

5) Buttons – Theme standardization
- brand-button: green (Save/primary), ghost for “+ New” (clear)
- secondary-button: amber/yellow (Cancel)
- danger-button: brownish/red (Delete)

6) Field Labor – Tabs, tables, and summary
- Tabs: Hourly Field Staff, Salary Field Staff
- Hourly: Employee Type, # Staff, Hrs/Yr (Ea), OT Hrs (Ea), Avg Wage, Bonus, Wages/Yr (computed)
- Salary: Employee Type, # Staff, Ann Hrs (Ea), Ann Salary (Ea), Bonus, Ann. Wages (computed)
- Key Factors box: Labor Burden %, Overtime Multiplier (1.25x to 3.00x)
- Field Labor Summary: Total Hrs, Total Wages, Total Burden, Field Payroll
- Field Labor Ratio: Your Ratio (Field Payroll vs Sales), Industry Avg (editable, default 26.6%)

## In Progress (mark as In Progress)

1) Budget – Wire labor summary to budget outputs
- Persist hourly/salary rows, burden %, OT multiplier, and industry avg ratio into inputs
- Compute consolidated outputs and expose via BudgetService for use across the app

2) Budget – Define inputs for Equipment/Materials/Subcontracting/OH Recovery
- Equipment: owned/leased rates, utilization, maintenance
- Materials: category markups, waste factors
- Subcontracting: vendor markups
- Overhead Recovery: method (labor-based/revenue-based) and base

## Next Up (mark as Pending/Future)

1) Pricing – Derive catalog pricing from active Budget
- Use BudgetService outputs for labor charge-out rates, equipment day rates, material category markups
- Ensure calculators and estimate items consume budget-based pricing

2) Sales Budget – Totals and ratios in Analysis
- Surface sales totals and ratios (including Your Ratio and Industry Avg) on Analysis tab

3) Global – Normalize buttons to components across all pages
- Replace legacy inline buttons with x-brand-button / x-secondary-button / x-danger-button

4) Estimates – Enhance Add Items for Equipment/Sub/Other
- Vendor lookups, equipment presets, area assignment shortcuts, stronger validation

## Add Items Menu – Current Behavior

- Drawer tabs: Labor, Materials, Equipment, Subs, Other, Templates
- Catalog selection auto-fills cost/unit (materials: unit, cost, tax; labor: unit, cost)
- “New” modal for Materials/Labor supports return_to; returns to estimate after save
- Templates: list templates by type and import into estimate (append/replace)
- Forms show a live preview of line total and default margin handling

## Notes
- The Budget editor now drives future pricing logic (all pricing items will be derived from budget outputs)
- Button theme standardized for a consistent UI: Save=green, Cancel=amber/yellow, Delete=brownish/red, New=clear/ghost
