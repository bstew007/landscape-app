---
description: Prevents reintroducing loader conflicts that previously broke
  autocomplete/map fill
---

Do not add or modify classic Google Maps JS script tags; rely on the Extended Component Library (UI Kit) loader already included in layouts/sidebar.blade.php and app.js listeners.