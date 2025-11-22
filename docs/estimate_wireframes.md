# Estimate Experience Wireframes (Text Spec)

> Low-fidelity descriptions documenting layout goals before building new templates. All sections assume existing typography, spacing, and brand button styles.

## 1. Add Items Workspace v2

### Layout
- **Container:** Slide-over (desktop max-width 480px) with three vertical regions.
  1. **Header Bar:** Title + close button, subtext showing active estimate + type.
  2. **Selector Rail (top 96px):** Sticky block containing:
     - Work Area dropdown (`standard`) or Service dropdown (`maintenance`).
     - Pills that show subtotal, planned hours, margin; updates when selector changes.
  3. **Tabbed Canvas:** Fills the rest, split into:
     - **Catalog List Pane (top half):** Scrollable list with search/filter controls.
     - **Detail Form Pane (bottom half):** Shared form fields, live preview, submit button anchored at bottom.

### Tabs
1. **Labor** – list of catalog labor items (name, type, wage). Selecting prefills unit cost/unit label.
2. **Materials** – catalog materials list with stock indicator.
3. **Equipment** – asset list (type, daily cost) with optional utilization slider.
4. **Subs** – placeholder for subcontractor fee catalog.
5. **Misc** – custom line builder (fee/discount).
6. **Templates** – opens calculator template gallery inline (grid of cards with “Insert” button).
7. **Site Visits** – table grouped by visit date, each row showing captured labor/material with checkbox.

### Detail Form Pane
- Fields: Quantity, Unit Cost, Margin %, Unit Price (auto), Unit label, Tax %, Notes.
- Dynamic extras per tab:
  - Labor: Rate basis toggle (catalog wage vs override).
  - Equipment: Duration units (hours/days), cost recovery checkbox.
  - Site Visit: read-only captured values plus editable qty multiplier.
- **Live Preview Card:** Shows `Line Total`, `Cost`, `Profit`, `Margin%`.
- **Submit Row:** Primary button (“Add to Area” / “Add to Service”) + inline validation errors; button disabled until required fields valid.

### Site Visit Tab Flow
1. Filter by visit date dropdown.
2. Items grouped (Labor, Materials, Equipment) with checkboxes.
3. “Assign to” dropdown (area/service) + “Add Selected” button. Selected rows summarize at bottom before submit.

## 2. Maintenance Services Panel (Estimate View)

### Overview
- Replaces Work Areas accordion when `estimate_type === 'maintenance'`.
- **Header:** “Services” title with `+ Add Service` button.
- **List:** Each service card contains:
  - Name + frequency badge (Weekly, Monthly, Seasonal).
  - Crew size, estimated hours, default labor mix.
  - Price summary + margin chip.
  - Action buttons: Edit, Duplicate, Delete, “Add Items” (opens workspace with service preselected).
- **Totals Bar:** Aggregates recurring revenue, total hours per cycle, average margin.

### Add/Edit Service Modal
- Fields: Name, Description, Frequency select, Crew size, Default hours, Cost code (QBO service), Color tag.
- Optional sections: Included tasks checklist, default materials.

## 3. Site Visit Migration Drawer

- Accessed from estimate header (“Import Site Visit Data”).
- **Left sidebar:** list of site visits with date, property, crew.
- **Main pane:** Tabs for Labor / Materials / Notes.
- Each tab renders a data table with captured entries, checkboxes, and “Add to [Area/Service]” CTA.
- On submit, confirmation toast + highlight newly inserted rows.

## 4. Estimate → Job Conversion Dialog

- Trigger: “Create Job” button visible when estimate status = Approved.
- Modal sections:
  1. **Summary:** Estimate totals, type, # work areas/services.
  2. **Schedule:** Start date, duration (auto-filled from planned hours / crew size), calendar toggle.
  3. **Mapping Review:** Table listing each area/service and associated cost code/QBO item.
  4. **Confirmation:** Checkbox “Notify team” + Create Job button.
- Success state: Modal closes, toast “Job created”, deep-link to job detail + calendar entry.

## 5. Job Costing Widgets (Future State)

- On job view, include cards for:
  - Labor: Planned hours vs actual, variance bar.
  - Materials: Estimated qty vs actual usage, variance value.
  - Equipment: Planned cost vs actual run cost.
  - Gross Margin: Estimate vs actual net.
- These widgets pull from new `job_time_entries`, `job_material_usages`, and `job_equipment_logs`.

---

Use this spec as the blueprint for Phase 1 refactor and Phase 2 workspace build. Update sections if wireframes evolve.
