# Estimates Page Status – November 2025

## What’s in place

- **Blade markup cleanup**: `resources/views/estimates/show.blade.php` now emits only markup + data attributes. All behavior‐heavy inline `<script>` blocks were removed, keeping the template readable and easier to diff.
- **Dedicated JS module**: `resources/js/estimate-show.js` owns the page logic (tabs, add‑items drawer, summary cards, catalog forms, row highlight). It exposes an Alpine component factory plus helper functions, so new interactions live in one file and can be unit‑tested.
- **Global bootstrapping**: Per‑page data (routes, area IDs, defaults) is serialized once near the top of the Blade file (`window.__estimateSetup`, `__estimateItemsBaseUrl`, etc.). The JS module consumes those globals instead of interpolating long PHP strings into script blocks.
- **Vite integration**: `resources/js/app.js` imports `initEstimateShow()`, so the behavior ships in the compiled bundle. A fallback inline component factory keeps Alpine from throwing while assets load.
- **UI wiring**: Tabs, drawer toggles, catalog search filters, and area reorder logic all run through the module. The loader overlay + “Save All” button are still supported, and calculator drawer helpers are ready for deeper integration.

## Current pain points

- **Line‑item POSTs are still full‑page reloads** (`window.location.reload()` after the fetch). If the request fails (validation/auth), the user only sees a toast and the DOM stays untouched.
- **Backend response handling**: we don’t render the returned JSON row; even on success the user must wait for a reload before seeing the new labor entry.
- **Implicit globals**: we still rely on `window.__estimateSetup`/`__calcRoutes`. They’re centralized now, but a typed interface or `data-*` JSON could reduce hard‑coded property names.
- **Testing gap**: no automated coverage (Dusk/Playwright) around add‑item flows, area reorder, or drawer state. Regressions go unnoticed until manual QA.

## Next steps

1. **Live item insertion** – Instead of reloading, use the JSON payload from `POST /estimates/{estimate}/items` to inject the new row (or at least append with template literals) and refresh totals via `updateSummary`.
2. **Error surfacing** – Display server validation errors inline (e.g., near the catalog form) rather than only via a toast.
3. **Network diagnostics** – Log failed request payloads to the console/dev tools to speed up troubleshooting, and consider using the Fetch API’s `response.clone().text()` for better error bodies.
4. **Modular globals** – Replace the scattered `window.__*` with a single `window.estimateConfig` object (or even embed JSON via `data-config` on `body`) so the module consumes a well‑typed config.
5. **Regression tests** – Add at least one browser test that loads an estimate, adds a catalog labor line, and asserts that the row + totals update. This catches future JS/refactor breaks automatically.

Once those are done we can focus on richer UX (inline editing, optimistic updates, background auto‑save) with much lower risk. Let me know if you want me to tackle the live insertion step next.***
