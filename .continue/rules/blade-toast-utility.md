---
globs: resources/views/layouts/**/*.blade.php
description: Use when adding UI actions that reference showToast in Blade or JS
  so toasts work across pages.
---

Always ensure a global window.showToast helper exists on layouts used by interactive pages. Include a toast root container and JS to render success/error toasts.