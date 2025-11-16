---
description: Apply to any code that performs QBO Customer updates or syncs. This
  reduces validation faults and unintended overwrites in production.
---

When updating QuickBooks Customer records, send a minimal sparse payload that includes only changed, allowed fields (PrimaryEmailAddr, PrimaryPhone, BillAddr). Do not include DisplayName, CompanyName, GivenName, FamilyName, or Mobile on update to avoid 2010 ValidationFaults. Always fetch the latest SyncToken first and set sparse=true with operation=update.