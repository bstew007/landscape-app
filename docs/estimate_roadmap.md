# Estimate & Job Workflow Roadmap

## Objectives
- Ship a leaner estimate experience that keeps the current brand language while reducing cognitive load.
- Support two estimate archetypes (`standard`, `maintenance`) so crews can price construction work by area and recurring work by service.
- Make the new Add Items workspace the single place to pull catalog entries, site-visit captures, calculators, and templates into an estimate.
- Provide a reliable Estimate → Job pipeline that feeds scheduling, time tracking, material usage, and QBO invoicing without re-entry.

## Pillars
1. **Leaner Estimate Shell** – Break `estimates.show` into header/overview/areas/services/add-items partials with scoped Alpine controllers so design changes stay modular.
2. **Add Items Workspace v2** – Sticky work-area/service selector, tabbed catalog list, live pricing preview, inline validation, and optimistic row insertion that reuses the JSON payload returned by `EstimateItemController@store`.
3. **Site Visit Migration** – A dedicated tab inside the workspace that lists captured labor/material/equipment from site visits with filters per visit and “Add selected to estimate” batching.
4. **Estimate Modes** – `standard` mode tracks Labor/Equipment/Materials within work areas; `maintenance` mode tracks Services (name, frequency, crew size, hours) with per-service summaries.
5. **Estimate → Job Conversion** – Approved estimates can spawn jobs that inherit areas/services, cost codes, and budgets. Jobs manage scheduling metadata (start, duration from planned hours) and hold references back to the estimate.
6. **Job Costing & Actuals** – Jobs accept time entries, equipment logs, and material usage, exposing variance widgets (Estimate vs Actual) per area/service and for the whole job.
7. **QBO Alignment** – Every line carries a cost code tied to the correct QBO product/service. Estimate approval → job → invoice keeps that mapping intact so syncing stays consistent.

## Phase Plan
| Phase | Scope | Key Outputs |
| --- | --- | --- |
| 0 | Align & wireframe | This roadmap, low-fi mockups for Add Items + maintenance services |
| 1 | Blade/JS refactor | Partialized `estimates/show`, extracted controllers (`estimate-shell.js`, `add-items.js`) |
| 2 | Add Items workspace | New UI, AJAX submissions, inline errors, template & site-visit tabs |
| 3 | Estimate types | Migration adding `estimate_type`, services tables, UI toggles |
| 4 | Job conversion | Jobs schema, “Create Job” flow, schedule metadata, status updates |
| 5 | Job costing | Time entry + material usage tables, variance widgets, reporting hooks |
| 6 | QBO sync polish | Ensure cost codes flow through estimates → jobs → invoices and document sync behavior |

## Schema Touchpoints
- **estimates**: add `estimate_type` enum (`standard`, `maintenance`), default `standard`.
- **estimate_work_areas** (existing): ensure `sort_order`, `cost_code_id`, `planned_hours`.
- **estimate_services** (new): `estimate_id`, `name`, `frequency`, `default_hours`, `crew_size`, `cost_code_id`, `sort_order`.
- **estimate_site_visit_items** (new or reuse existing capture tables) to store staged labor/material data awaiting migration.
- **jobs** (new): `estimate_id`, `job_number`, `status`, `start_date`, `duration_hours`, `calendar_block_id`, `type`.
- **job_work_areas / job_services**: cloned snapshots of estimate structures plus actual metrics.
- **job_time_entries**: `job_id`, `work_area_id`/`service_id`, `user_id`, `hours`, `source`.
- **job_material_usages / job_equipment_logs**: actual consumption with unit, cost, variance metadata.

## UX Deliverables
- **Add Items Workspace v2**
  - Left rail: area/service selector, quick stats (current subtotal, planned hours, margin).
  - Tabs: Labor, Materials, Equipment, Subs, Misc, Templates, Site Visits.
  - Shared form pane: quantity, unit cost, margin %, auto-calculated price, tax toggle, notes.
  - Submission: async post, optimistic row insert, highlight target table, update totals via `updateSummary`.
  - Validation: inline error badges + toast fallback.
- **Maintenance Services View**
  - Cards/table per service with columns (Service, Frequency, Crew, Est. Hours, Price).
  - “Add Service” modal with catalog shortcuts.
  - Add Items workspace filters by service instead of area.
- **Site Visit Import**
  - Table grouped by visit date with labor/material checkboxes.
  - “Send selected to Area/Service” dropdown; respects active estimate type.
- **Job Conversion Dialog**
  - Summary of totals, mapping confirmation for cost codes, scheduling options (dates, crew).
  - Confirmation triggers job creation and surfaces link to calendar.

## Dependencies & Risks
- Need up-to-date catalogs (labor/material/equipment) for the workspace to feel cohesive.
- Estimate → job conversion must be idempotent; re-running should update existing jobs rather than duplicate.
- QBO mapping changes should be audited before jobs go live to avoid sync errors.
- Calendar integration assumptions (existing scheduling system?) must be clarified before Phase 4.

## Immediate Next Steps
1. Produce low-fidelity wireframes for the Add Items workspace (standard + maintenance variants) and the Site Visit import flow.
2. Identify any existing site-visit data structures to reuse for migration tab; document expected payload shape.
3. Start Phase 1 refactor: extract Blade partials and split `estimate-show.js` into shell + workspace modules without changing behavior.

