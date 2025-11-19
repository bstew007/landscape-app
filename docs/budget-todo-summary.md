# Budget Editor: Implementation Summary and To‑Dos

Date: 2025-11-19 (updated)

## What’s Implemented

- Navigation and Save
  - Left sidebar shows section pills with live metrics for Sales (Forecast), Field Labor (Field Payroll), Equipment (Total Equip Expenses), Materials (Current Total), Subcontracting (Current Total), and Overhead (Current Total)
  - Ratio bubbles added in sidebar and section headers for Field Labor, Equipment, Materials, Subcontracting (always green), and Overhead (vs industry avg). Colors: green when within 4 percentage points of industry average; red otherwise (where applicable).
  - Single Save button in the top header; bottom Save removed
  - After save, the form appends close=1 enabling controller to redirect away (server handling optional)

- Equipment Section
  - Graphics Row (top of section)
    - General Expenses card with compact rows
      - Forecast Fuel, Forecast Repairs, Insurance + Misc (persisted)
    - Equip Summary card
      - Equipment Expenses (list total: sum of per-unit Cost/Yr/Ea across rows)
      - Other (sum of General Expenses inputs)
      - Total Equip Expenses = Equipment Expenses + Other
      - Plus Equip Rentals (persisted)
    - Equipment Ratio card
      - Your Ratio = (Total Equip Expenses + Rentals) / Sales Forecast × 100
      - Industry Avg (%) editable, default 13.7 (persisted)
      - Side-by-side large number comparison
      - Subtle top-right icons across cards; icons darkened for visibility
  - Equipment list/table
    - Columns: Type, Qty, Class, Description, Cost/Yr/Ea, Cost/Yr/Ea (per-unit display)
    - Per-row actions menu (kebab): Move to Overhead (adds row total to Overhead and removes row), Delete
    - “Total Equipment” footer displays only when > 0
    - Calc toggle button in Cost/Yr/Ea for computed rows
  - Owned Class
    - Cost/Yr/Ea becomes read-only with computed Annual
    - Green breakdown panel (collapsed by default; toggled by calculator)
    - Inputs: Replacement value, Fees/Taxes/Admin, Useful life (years), End-of-life value, Months used per year, Division months, Interest/Inflation rate (%)
    - Calculations
      - Annual cost = (replacement+fees)/years + compounded_interest_over_life/years
      - Compounded interest over life = (replacement+fees) × ((1 + rate)^years − 1)
      - Monthly (calendar) = Annual/12
      - Monthly (active) = Annual / months_used_per_year
      - Division Annual = Annual × (division_months/12)
      - Division Monthly (active) = Division Annual / division_months
    - Lifetime interest value displayed inside panel
    - Persistence under inputs.equipment.rows[i].owned
  - Leased Class
    - Cost/Yr/Ea becomes read-only with computed Annual
    - Green breakdown panel (mirrors Owned styling; collapsed by default; toggled by calculator)
    - Inputs: Monthly payment, Payments per year (1–12), Months used per year (1–12), Division months (1–12)
    - Calculations
      - Annual = monthly_payment × payments_per_year
      - Monthly (calendar) = Annual / 12
      - Monthly (active) = Annual / months_used_per_year
      - Division Annual = Annual × (division_months/12)
      - Division Monthly (active) = Division Annual / division_months
    - Persistence under inputs.equipment.rows[i].leased
  - Totals and integration
    - Per-unit Cost/Yr/Ea column shows computed Annual for Owned/Leased, or manual for Custom/Group
    - equipmentDisplayedListTotal = sum of per-unit Cost/Yr/Ea across rows
    - equipmentRowTotal = Qty × perUnitCost
    - equipmentTotal = sum of equipmentRowTotal (used for overhead move)
    - General Expenses persist and coerce to numeric for totals
    - Total Equip Expenses pill used in header and left sidebar for Equipment

- Field Labor Section
  - Summary and Ratio cards updated with top-right icons and big-number ratio comparison
  - Existing hourly/salary tables unchanged; persistence retained

- Sales Budget Section
  - Graphics cards enhanced with top-right icons
  - Pie and bars unchanged; persistence retained

- Materials Section (new)
  - Key Factors: Materials Tax (%) used to compute tax-inclusive totals
  - Material Summary: Previous Total/Ratio, Current Total/Ratio with colored pills
  - Material Ratio: Your Ratio vs Industry Avg (default 22.3) with colored pill
  - Table mirrors Sales: Acct. ID, Material Expense, Previous $, Current $, Comments
  - Persists under inputs.materials.*

- Subcontracting Section (new)
  - Summary: Previous Total/Ratio, Current Total/Ratio (ratio pills always green; no industry avg)
  - Ratio: Your Ratio only (always green pill)
  - Table mirrors Materials: Acct. ID, Subcontracting Expense, Previous $, Current $, Comments
  - Persists under inputs.subcontracting.*

- Overhead Section (expanded)
  - Graphics Row:
    - Key Factors: Labor Burden (%)
    - Overhead Summary: Overhead Expenses (Current), Overhead Wages (Forecast)
    - Overhead Ratio: Your Ratio vs Industry Avg (default 24.8) with colored pill
  - Three tabs inside Overhead section:
    1) Overhead Expenses: Acct. ID, Overhead, Previous $, Current $, Comments (Add/Delete)
    2) Overhead Wages: Salary, Previous, Forecast, % Diff, Comments (Add/Delete)
    3) Overhead Equipment: mirrors Equipment row UI; Owned/Leased breakdown panels enabled; removed the equipment general/rentals graphics from Overhead per requirement
  - Overhead Current Total = Overhead Expenses (Current) + Overhead Wages (Forecast)
  - Persists under inputs.overhead.*

- Styling
  - Owned panel styled with dark-green double underline for title, thin dividers between rows
  - General Expenses uses compact, single-row label/input layout
  - Icons darkened to text-gray-600

## Known Gaps / Follow-ups

- Equipment: clarify which value the list’s last column should display
  - Currently shows per-unit Cost/Yr/Ea. Some users expect the extended total (Qty × Cost). Consider a dual-display: "$510.05 ea | $1,530.15 total".
- Division Monthly (active) semantics
  - For Owned/Leased, Division Monthly (active) = Division Annual / division_months. If desired, change to divide by min(months_used, division_months) to reflect active months inside division.
- Interest basis for Owned
  - Lifetime interest is compounding on principal (replacement+fees). This matches request and example. If desired, provide an option to include/exclude salvage or use average investment for annual interest.
- Default open state
  - Owned/Leased panels default to collapsed; confirm if users prefer open by default on first entry.
- Overhead move behavior
  - Move to Overhead increases inputs.overhead.total via DOM. Consider server-side recompute or displaying an inline confirmation.
- Rentals in Equipment Ratio
  - Ratio currently includes Total Equip Expenses + Rentals. Confirm intended scope for KPI.
- Overhead Equipment contribution
  - Per requirements, Overhead Equipment graphics/inputs were removed from Overhead totals; equipment costs should be entered via Overhead Expenses. Confirm long-term plan and whether to delete the Overhead Equipment tab entirely.
- Save-and-close behavior
  - Front-end appends close=1. Implement redirect logic in controller if desired.

## Ideas to Improve

- UX polish
  - Show both per-unit and extended totals per row; provide a toggle to choose which the list column shows.
  - Inline tooltip info for Owned/Leased formulas; add info icon next to panel headers.
  - Add copy/duplicate row action to speed data entry.
- Data validation and guardrails
  - Warn when salvage > replacement+fees; warn when months used < division months.
  - Block negative numbers; normalize percent input accepting "6" or "0.06" via a helper.
  - Materials/Subcontracting: optionally prevent saving when Sales Forecast is zero and ratios would divide by zero (show friendly message).
  - Normalize percent input accepting "6" or "0.06" via a helper.
- Performance and state
  - Debounce numeric inputs; centralize numeric coercion.
  - Add a computed grand equipment cost including Rentals in the sidebar pill if helpful.
- Reporting
  - Add compact summaries at the bottom of Materials/Subcontracting/Overhead like Equipment, including ratios and diffs.
  - Add a compact summary at the bottom of Equipment showing: Equipment list total (extended), Other, Rentals, Total, and Ratio.
  - Export to CSV/PDF for Equipment assumptions.
- Accessibility
  - Ensure all icon buttons have aria-labels and focus states.

## To‑Dos

1) Decide last-column display for equipment list (per-unit vs extended or both); implement chosen option
2) Confirm Division Monthly (active) definition and update Owned/Leased formulas if needed
3) Add controller logic to honor close=1 and redirect to index with toast
4) Add duplicate row action in Equipment
5) Add tooltips/info icons explaining Owned/Leased calculations
6) Optional: expose toggle to include Rentals in sidebar pill and section header pill
7) Add tests for persistence and validation of Materials, Subcontracting, Overhead (expenses, wages, equipment) and Equipment (General, Rentals, Industry Avg, Owned/Leased fields)
8) Overhead: consider fully removing Equipment tab if those costs are exclusively tracked under Expenses
9) Accessibility: ensure all icon buttons (calc toggles, delete) have aria-labels and keyboard focus states
10) Performance: debounce inputs and memoize totals/ratios for large tables
