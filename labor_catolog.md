# Labor Catalog and New Labor Item Modal – Working Notes

This document captures the current state of the Labor Catalog and the work we did to implement a new, modal-based Labor Item create flow. It includes the data sources used, the UI and calculation logic we wired up, issues we hit during implementation, and concrete recommendations to finish and polish the feature.

---

## Scope

- Catalog: Labor items (crew, equipment, subcontracted labor) used in estimates
- Create Flow: A new modal-based interface to create a Labor Item with: 
  - Item Information (Name, Units, Description, Internal Notes)
  - Cost + Breakeven Calculator (Average Wage, OT Factor, Unbillable %, Labor Burden %, Overhead Markup $/hr)
  - Price Calculator (Budget margin vs custom margin vs custom price)
- Budget Integration: Pull needed values from the active Company Budget (inputs + outputs) and, critically, from OH Recovery (Field Labor Hour Markup)

---

## Data sources and references

- Active Budget: `app(\App\Services\BudgetService::class)->active(false)`
  - Using `active(false)` to bypass cache and pick up latest budget
- Budget inputs used:
  - `inputs.overhead.expenses.rows[].current`
  - `inputs.overhead.wages.rows[].forecast`
  - `inputs.overhead.equipment.rows[].qty` and `cost_per_year`
  - `inputs.labor.hourly.rows[]` with `staff`, `hrs`, `ot_hrs`, `avg_wage`, `type`
  - `inputs.labor.salary.rows[]` with `staff`, `ann_hrs`, `ann_salary`, `type`
  - `inputs.labor.ot_multiplier` (for wage OT calculations)
- OH Recovery – Field Labor Hour Markup (labor-hour method):
  - Overhead Current Total = overhead expenses (current) + wages (forecast) + equipment (qty * cost_per_year)
  - Total Labor Hours = hourly staff × (hrs + ot_hrs) + salary staff × ann_hrs
  - Field Labor Hour Markup ($/hr) = Overhead / Total Labor Hours

---

## New Labor Item Modal – Intended UX

- Modal: Opens on top of the page with Cancel/Save in the header.
- Layout (final intent):
  - Split columns: 
    - Left: Item Information
    - Right: Cost + Breakeven (and below that, Price Calculator)
  - Boxed sections (cards) for readability:
    - Item Information
    - Cost + Breakeven
    - Price Calculator
- Item Information fields:
  - Name (text)
  - Units (text; default "hr")
  - Description (textarea)
  - Internal Notes (textarea)

### Cost + Breakeven

- Inputs:
  - Average Wage ($) – with a calculator icon to open a wage calculator modal
  - Overtime Factor (e.g., 1.5)
  - Unbillable % (e.g., non-productive time/off-bench)
  - Labor Burden % (payroll taxes/benefits/etc.)
  - Overhead Markup ($/hr) – read-only label; comes from OH Recovery Field Labor Hour Markup

- Derived metrics:
  - Effective Wage = wage × OT factor
  - Loaded Wage = Effective Wage × (1 + labor burden)
  - Billable Fraction = 1 − (unbillable % / 100)
  - Breakeven ($/hr) = (Loaded Wage / Billable Fraction) + Overhead Markup

### Price Calculator

- Mode options:
  - Use Profit Margin from Budget (budget desired margin)
  - Set a Custom Profit Margin (editable %)
  - Set a Custom Price (editable $)
- Display:
  - Profit Margin (editable only in custom-margin mode)
  - OH Hour Recovery Rate (showing breakeven $/hr)
  - Price (editable only in custom-price mode)

> Save behavior: base_rate is set to the computed/selected Price.

---

## Wage Calculator Modal

- Source rows:
  - Hourly rows from budget: each becomes an entry (label=type, wage=avg_wage)
  - Salary rows from budget: wage computed as (ann_salary / ann_hrs)
- Columns: Employee, Hourly Wage (editable), Count (0–9 dropdown)
- Preview Average: Weighted average of hourly wage by Count
- O/T factor calculation guidance and status:
  - Correct calculation requested:
    - Combined average wage: (reg_wage*reg_hrs + ot_wage*ot_hrs) / (reg_hrs + ot_hrs)
    - O/T Factor Ratio = Combined Average / Base Wage
    - O/T Factor (%) = (Ratio − 1) × 100
  - We added support using budget `ot_multiplier` to compute `ot_wage = wage × ot_multiplier` and weighted by provided reg_hrs/ot_hrs across the selected rows.
  - Counts default to 0; Reset sets counts to 0; dropdown includes 0.

---

## What’s working

- Modal structure with three logical sections
- Budget integration for:
  - Desired Profit Margin (default)
  - OH Recovery per-hour markup (computed from budget inputs; shows as label)
- Cost + Breakeven calculations update derived metrics live
- Price Calculator flows update the bottom summary live
- Wage calculator modal:
  - Lists hourly/salary employees from budget
  - Editable wages and counts
  - Preview average wage updates live
  - O/T factor (%) computed per requested formula

---

## Issues we’ve run into

1) Radio button toggling inconsistencies
- Earlier attempts used different Alpine patterns (x-model vs explicit @click) which conflicted in the modal.
- Some pages didn’t update the summary when a mode was selected.

2) Layout churn (rows vs columns; widths)
- You asked for labels and inputs on the same row, which we tried with a compact row component.
- Visual alignment across the split columns is sensitive to fixed widths; switching to w-full inside boxed sections is more robust.

3) Overhead Markup value source
- You clarified it must come from OH Recovery – Field Labor Hour Markup. We updated to compute it from budget inputs (overhead totals / total labor hours). Previously we tried outputs.labor.ohr which did not reflect the expected value.

4) Wage calculator O/T factor visibility
- There were moments the ratio/% line didn’t appear due to scope/DOM binding. This is resolved by keeping a single top-level wage-calc modal and ensuring its Alpine scope exposes otFactor() and otFactorPct().

5) File became hard to incrementally edit
- Due to several iterative tweaks, some search/replace edits missed because of string drift.

---

## Recommendations to get this “right” and stable

- Simplify Alpine state for pricing mode
  - Use a single x-model="mode" on radios that share the same name to ensure mutual exclusivity. Avoid mixing @click assignments unless necessary.
  - Keep a hidden input bound to mode so the server knows which pricing mode was used.

- Lock down the layout
  - Keep the two primary sections as cards (Item Information; Cost + Breakeven).
  - Inside each card, use stacked labels/inputs (w-full). This is robust, responsive, and quick to read.
  - If horizontal rows are still preferred, use a single component (like compact row) and a single max width utility to keep alignment consistent, but expect more tuning.

- Confirm the OH Recovery source once
  - We’re computing Overhead / Total Labor Hours live from budget inputs. If the budget exposes a canonical precomputed field (e.g., a single value for Field Labor Hour Markup), prefer referencing that to avoid divergence.

- Wage calculator improvements
  - Consider showing both O/T Factor ratio (e.g., 1.0067x) and percent (0.67%), and add a small help tooltip describing the formula.
  - Allow seeding counts from budget staff counts (optional) via a toggle.

- Testing checklist
  - With a budget that you know has OH/hrs = 41.62, confirm the label renders “$41.62/hr”.
  - Toggle the three pricing modes and confirm the margin/price inputs enable/disable correctly.
  - Use the wage calculator with the provided example: wage=18, hrs=1800, ot_hrs=25, ot=1.5 → combined avg ≈ 18.12; O/T factor ≈ 0.67%.

---

## Open questions

- Do we want labor types beyond “crew” surfaced (e.g., equipment or subs) in the create form, or will those remain separate catalog entries elsewhere?
- Should we persist the chosen pricing mode on save (we currently submit `pricing_mode` as a hidden field) and reflect that mode on edit?
- For overhead recovery: do you want to display a source line referencing the active budget name/effective date for clarity?

---

## Next steps (implementation)

- Replace any remaining horizontal compact rows in the cards with stacked, w-full inputs for consistency.
- Ensure only one wage calculator modal (top-level) exists, and remove any nested duplicates to avoid event collisions.
- Finalize the price calculator section as a card, and confirm the radio-driven enable/disable states with a simple Alpine watcher.
- Add a tiny debug span (mode: <span x-text="mode"></span>) behind a feature flag while testing to verify the state toggles in all browsers.

---

If you want, I can apply these specific changes in the blade file in one clean pass so everything matches this document exactly. Let me know and I’ll do it in a single update.