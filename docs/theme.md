# CFL App Theme Guide

This document captures the current UI theme choices so we can keep everything consistent as the app grows.

## Palette

- **Brand (charcoal family):**
  - `brand-50` `#fbfbfc` – light backgrounds
  - `brand-100` `#f1f2f5`
  - `brand-200` `#e1e3ea`
  - `brand-300` `#c8cbd7`
  - `brand-400` `#a4a8ba`
  - `brand-500` `#81889d`
  - `brand-600` `#6b7285`
  - `brand-700` `#555a6a`
  - `brand-800` `#3f4350`
  - `brand-900` `#2d2f38` – sidebar/menu/background

- **Accent (forest green for primary actions):**
  - `accent-50` `#edf7f1`
  - `accent-100` `#cfe9db`
  - `accent-200` `#add7c2`
  - `accent-300` `#84c2a3`
  - `accent-400` `#55a97e`
  - `accent-500` `#2f8f5f`
  - `accent-600` `#236f47` – default button background
  - `accent-700` `#1c5638`
  - `accent-800` `#153c28`
  - `accent-900` `#0d271a`

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

- Primary `x-brand-button` (solid): `bg-accent-600` with `focus:ring-accent-400`.
- Outline variant uses an accent border/text with light hover fill.
- Secondary buttons use `bg-brand-50` / `border-brand-200`.
- Danger buttons use a red palette (`bg-red-600`, `focus:ring-red-400`).

## Tables & Lists

- Tables use `border-collapse` with both horizontal and vertical lines (`border-brand-200`).
- Header cells get `bg-brand-100`.
- Body rows zebra stripe: odd rows white, even rows `bg-brand-100`.
- Selection/hover state lightens to `bg-brand-100`.

## Cards & Page Header

- `x-page-header` renders as `rounded-2xl border border-brand-100 bg-white shadow-sm`.
- Content cards follow the same pattern (white background, thin brand border, small shadow).

## Flash / Status Indicators

- Success flash: `bg-accent-50` / `border-accent-200`.
- QBO status chips:
  - Needs Sync → amber badge.
  - Synced → accent badge.
  - Not Linked → brand badge.

## Future Additions

Add notes here as we introduce new components, typography rules, or variations on cards/buttons. This file should remain the single source of truth for theme decisions.
