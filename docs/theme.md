# CFL App Theme Guide

This document captures the current UI theme choices so we can keep everything consistent as the app grows.

## 🎨 Primary Theme: Charcoal (Brand Colors)

**As of November 29, 2025:** The app is transitioning to a fully charcoal-based theme. The forest green accent colors are being phased out in favor of the charcoal palette for all new designs and pages.

## Palette

- **Brand (charcoal family) - PRIMARY THEME:**
  - `brand-50` `#fbfbfc` – light backgrounds, highlights
  - `brand-100` `#f1f2f5` – subtle backgrounds
  - `brand-200` `#e1e3ea` – borders, dividers
  - `brand-300` `#c8cbd7` – muted elements
  - `brand-400` `#a4a8ba` – secondary text
  - `brand-500` `#81889d` – placeholder text
  - `brand-600` `#6b7285` – secondary actions
  - `brand-700` `#555a6a` – hover states
  - `brand-800` `#3f4350` – **PRIMARY BUTTONS & HEADERS**
  - `brand-900` `#2d2f38` – sidebar/menu/background

- **Accent (forest green) - DEPRECATED:**
  - ⚠️ **Note:** Accent colors are being phased out. Use `brand-*` colors for new components.
  - Legacy pages may still use these colors during transition.
  - `accent-50` through `accent-900` – Use only for backwards compatibility

## Layout & Containers

- Global background matches the sidebar (`bg-brand-900`) for a seamless canvas.
- Page content lives inside a full-width rounded frame:
  - Outer padding: `p-2 sm:p-4 lg:p-6`
  - Frame block: `rounded-[28px] bg-brand-50 shadow-2xl`
- Individual sections (header card, content card, filters) use white backgrounds with `border-brand-100/200` and subtle shadows.

## Sidebar / Navigation

- Sidebar and mobile drawer are `bg-brand-900`.
- Links use `text-brand-50/90` with hover `bg-brand-800/60`.
- No borders in the sidebar; spacing + rounded states provide separation.

## Buttons

**NEW STANDARD (November 2025):**
- **Primary buttons:** `bg-brand-800 hover:bg-brand-700` with `text-white`
- **Secondary buttons:** `bg-white border-2 border-gray-300 hover:border-gray-400`
- **Danger buttons:** `bg-red-600 hover:bg-red-700` with `focus:ring-red-400`

**DEPRECATED:**
- ~~`bg-accent-600`~~ - Use `bg-brand-800` instead
- ~~`bg-gradient-to-r from-green-*`~~ - Use solid `bg-brand-800` instead
- ~~`bg-gradient-to-r from-blue-*`~~ - Use solid `bg-brand-800` instead

## Tables & Lists

- Tables use `border-collapse` with both horizontal and vertical lines (`border-brand-200`).
- Header cells get `bg-brand-100`.
- Body rows zebra stripe: odd rows white, even rows `bg-brand-100`.
- Selection/hover state lightens to `bg-brand-100`.

## Cards & Page Header

- `x-page-header` renders as `rounded-2xl border border-brand-100 bg-white shadow-sm`.
- Content cards follow the same pattern (white background, thin brand border, small shadow).
- **Section headers:** Use `bg-gradient-to-r from-gray-800 to-gray-700` with `text-white` for modern charcoal gradient headers

## Highlights & Special Elements

- **Price/total highlights:** Use charcoal gradient `bg-gradient-to-r from-gray-800 to-gray-700`
- **Recommended badges:** `bg-brand-100 text-brand-800`
- **Hover states on selections:** `hover:border-brand-400 hover:bg-brand-50`

## Flash / Status Indicators

- Success flash: `bg-green-50` / `border-green-200` (use standard Tailwind green, not accent).
- QBO status chips:
  - Needs Sync → amber badge.
  - Synced → green badge.
  - Not Linked → brand badge.
- **Badge shape:** use the shared `.badge` class for all chip/label styles – inline-flex, `px-3 py-1`, `rounded-lg`, uppercase tracking. Apply color utilities on top for each status/type so every label keeps the same geometry.

## Migration Notes

**Updating Legacy Pages:**
1. Replace `bg-accent-*` with `bg-brand-*` (typically `brand-800` for primary actions)
2. Replace `from-green-*` and `from-blue-*` gradients with `bg-brand-800`
3. Replace section headers: `from-accent-600 to-accent-700` → `from-gray-800 to-gray-700`
4. Replace recommended badges: `bg-accent-100 text-accent-800` → `bg-brand-100 text-brand-800`
5. Replace hover states: `hover:border-accent-300` → `hover:border-brand-400`

## Future Additions

Add notes here as we introduce new components, typography rules, or variations on cards/buttons. This file should remain the single source of truth for theme decisions.
