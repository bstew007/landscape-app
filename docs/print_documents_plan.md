# Print Documents Tab - Implementation Plan

## Overview
Add a comprehensive "Print Documents" tab to the estimates page that provides various printing, export, and synchronization options for estimate documents, purchase orders, and reports.

## Requirements Summary

### Print/Export Options
1. **Estimate Variations**
   - All items with quantities and prices (with work area totals)
   - Materials only with quantities and totals
   - Labor only with quantities and totals
   - Description only (no materials/labor details, just work area totals)
   - Custom combinations

2. **Purchase Orders**
   - Generate material POs by supplier/vendor
   - Print individual or batch POs
   - Sync POs to QuickBooks

3. **Reports**
   - Cost analysis report
   - Profit margin analysis
   - Labor hours summary
   - Material requirements list

4. **QuickBooks Integration**
   - Sync estimate to QB
   - Create QB purchase orders
   - Track sync status

---

## Database Schema Requirements

### New Tables

#### `estimate_print_templates`
```sql
- id
- name (e.g., "Full Detail", "Materials Only", "Summary")
- description
- config (JSON: which sections to include)
- is_default (boolean)
- created_at, updated_at
```

#### `estimate_purchase_orders`
```sql
- id
- estimate_id (FK to estimates)
- supplier_id (FK to suppliers - may need new table)
- po_number (auto-generated or manual)
- status (draft, sent, received, cancelled)
- total_amount
- notes
- qbo_id (QuickBooks ID)
- qbo_synced_at
- created_at, updated_at
```

#### `estimate_purchase_order_items`
```sql
- id
- purchase_order_id (FK)
- estimate_item_id (FK to estimate_items)
- material_id (FK to materials catalog)
- quantity
- unit_cost
- total_cost
- notes
- created_at, updated_at
```

#### `suppliers` (if not exists)
```sql
- id
- name
- company_name
- contact_person
- email
- phone
- address
- city, state, zip
- qbo_vendor_id
- is_active
- created_at, updated_at
```

---

## Implementation Phases

### Phase 1: Basic Print Documents Tab Setup
**Goal:** Create tab structure and basic estimate print variations

#### Step 1.1: Add Print Documents Tab
- [ ] Add "Print Documents" tab button in `estimates/show.blade.php`
- [ ] Create `estimates/partials/print-documents.blade.php` partial
- [ ] Style tab to match existing design

#### Step 1.2: Create Print Template Options UI
- [ ] Radio button/checkbox selection for print options:
  - [ ] Full detail (all items with prices)
  - [ ] Materials only
  - [ ] Labor only  
  - [ ] Description/Summary only
  - [ ] Custom selection
- [ ] Preview section showing what will be included
- [ ] Print/PDF download button

#### Step 1.3: Create Print Routes & Controller Methods
- [ ] Route: `GET /estimates/{estimate}/print` (already exists - enhance)
- [ ] Route: `GET /estimates/{estimate}/print-preview`
- [ ] Controller method: `EstimateController@printPreview`
- [ ] Controller method: `EstimateController@generatePDF`

#### Step 1.4: Enhanced Print Views
- [ ] `estimates/print-templates/full-detail.blade.php`
- [ ] `estimates/print-templates/materials-only.blade.php`
- [ ] `estimates/print-templates/labor-only.blade.php`
- [ ] `estimates/print-templates/summary.blade.php`
- [ ] Common header/footer partials

**Deliverable:** User can print/download estimate in different formats

---

### Phase 2: Purchase Order Generation
**Goal:** Generate and manage material purchase orders

**STATUS: 95% COMPLETE** ✅

#### Completed:
- ✅ Database tables created (suppliers, estimate_purchase_orders, estimate_purchase_order_items)
- ✅ Models with relationships and business logic
- ✅ PurchaseOrderService with PO generation
- ✅ PurchaseOrderController with CRUD operations
- ✅ PO management UI in Print Documents tab
- ✅ PO print template for professional output
- ✅ Auto-generated PO numbers (PO-YYYY-0001 format)
- ✅ Status tracking (draft, sent, received, cancelled)
- ✅ Bulk print functionality

#### 🔜 Next Enhancement - Automatic Vendor Assignment:

**Current Behavior:**
- PO generation groups materials by `material.supplier_id`
- Materials without suppliers get grouped into "no_supplier" PO
- Manual items (non-catalog) don't have vendor association

**Desired Behavior:**
- When generating POs, match estimate items with database materials by name/SKU
- Automatically assign the vendor from the matched catalog material
- One PO per vendor with all their materials
- Better handling of manual vs. catalog items

**Implementation Steps:**

#### Step 2.1: Database Setup
- [ ] ✅ Already complete - supplier_id exists on materials table

#### Step 2.2: Enhanced Material Matching Logic
- [ ] Update `PurchaseOrderService::groupItemsBySupplier()` to:
  - First check if item has catalog_id (direct catalog link)
  - If no catalog_id, attempt fuzzy match by name/SKU
  - Match against Material model using similarity search
  - Assign matched material's supplier_id
  - Track matching confidence for review

#### Step 2.3: Matching Service
- [ ] Create `MaterialMatchingService` with methods:
  - `findBestMatch($itemName, $itemDescription)` - returns Material or null
  - `calculateMatchScore($item, $material)` - returns 0-100 confidence
  - `suggestMatches($item, $limit = 5)` - returns array of possible matches
  - Consider: Levenshtein distance, fuzzy search, keyword matching

#### Step 2.4: PO Generation Improvements
- [ ] Enhance `PurchaseOrderService::generatePOsFromEstimate()`:
  - Use MaterialMatchingService for items without catalog_id
  - Store matched material_id in purchase_order_items
  - Log unmatched items for manual review
  - Group by matched supplier_id
  - Create "Unassigned Vendor" PO for unmatched items

#### Step 2.5: UI Enhancements
- [ ] Add "Review Matches" step before PO generation (optional)
- [ ] Show confidence scores for auto-matches
- [ ] Allow manual vendor assignment before generation
- [ ] Display warning for items without vendors
- [ ] Option to assign vendor to multiple items at once

#### Step 2.6: Supplier Management (Optional)
- [ ] Routes for supplier CRUD
- [ ] `SuppliersController` with index, create, store, edit, update, destroy
- [ ] Views: suppliers/index, create, edit
- [ ] Quick-add supplier from PO interface

**Deliverable:** Generate and print material purchase orders

---

### Phase 3: QuickBooks Integration
**Goal:** Sync estimates and POs to QuickBooks Online

#### Step 3.1: QB Setup & Configuration
- [ ] Install/configure QB SDK (if not already)
- [ ] Create `config/quickbooks.php` if needed
- [ ] QB OAuth authentication setup
- [ ] Store QB tokens securely

#### Step 3.2: QB Sync Service
- [ ] Service: `QuickBooksEstimateService`
  - Method: `syncEstimateToQB(Estimate $estimate)`
  - Method: `syncPurchaseOrderToQB(PurchaseOrder $po)`
  - Method: `getQBStatus(Estimate $estimate)`
- [ ] Map estimate fields to QB estimate fields
- [ ] Map PO fields to QB purchase order fields
- [ ] Handle QB vendor matching/creation

#### Step 3.3: Sync UI
- [ ] "Sync to QuickBooks" button for estimate
- [ ] "Sync to QuickBooks" button for each PO
- [ ] Sync status indicators
- [ ] Last synced timestamp
- [ ] Error handling and display

#### Step 3.4: QB Sync Tracking
- [ ] Add `qbo_estimate_id` to estimates table
- [ ] Add `qbo_synced_at` to estimates table
- [ ] Track sync history (optional audit log)

**Deliverable:** Sync estimates and POs to QuickBooks

---

### Phase 4: Reports & Cost Analysis
**Goal:** Generate analytical reports

#### Step 4.1: Cost Analysis Report
- [ ] Report view: `reports/cost-analysis.blade.php`
- [ ] Breakdown by:
  - Material costs vs. prices
  - Labor costs vs. prices
  - Overhead allocation
  - Profit margins
  - Break-even analysis
- [ ] Charts/visualizations (optional)

#### Step 4.2: Labor Hours Summary
- [ ] Total labor hours by work area
- [ ] Labor hours by type/category
- [ ] Crew scheduling recommendations

#### Step 4.3: Material Requirements Report
- [ ] Complete materials list with quantities
- [ ] Grouped by supplier
- [ ] Total material costs
- [ ] Comparison with POs

#### Step 4.4: Profit Margin Analysis
- [ ] Gross profit by work area
- [ ] Net profit calculations
- [ ] Margin percentages
- [ ] Comparison to budget (if applicable)

**Deliverable:** Comprehensive analytical reports

---

## UI/UX Design

### Print Documents Tab Layout

```
┌─────────────────────────────────────────────────────────────┐
│  Print Documents Tab                                         │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌─ Estimate Print Options ─────────────────────────────┐  │
│  │  ○ Full Detail (All items with prices)               │  │
│  │  ○ Materials Only (with quantities and totals)       │  │
│  │  ○ Labor Only (with quantities and totals)           │  │
│  │  ○ Summary (Description with work area totals only)  │  │
│  │  ○ Custom...                                          │  │
│  │                                                        │  │
│  │  [Preview] [Print] [Download PDF]                     │  │
│  └────────────────────────────────────────────────────────┘  │
│                                                              │
│  ┌─ Purchase Orders ─────────────────────────────────────┐  │
│  │  Materials grouped by supplier                         │  │
│  │                                                         │  │
│  │  [Generate Purchase Orders]                            │  │
│  │                                                         │  │
│  │  PO List:                                              │  │
│  │  □ PO-2025-001 | ABC Supply    | $2,450 | [Print]    │  │
│  │  □ PO-2025-002 | Home Depot    | $1,320 | [Print]    │  │
│  │                                                         │  │
│  │  [Print Selected] [Sync to QuickBooks]                 │  │
│  └────────────────────────────────────────────────────────┘  │
│                                                              │
│  ┌─ Reports ──────────────────────────────────────────────┐  │
│  │  • Cost Analysis Report        [View] [Print]          │  │
│  │  • Labor Hours Summary          [View] [Print]          │  │
│  │  • Material Requirements        [View] [Print]          │  │
│  │  • Profit Margin Analysis       [View] [Print]          │  │
│  └────────────────────────────────────────────────────────┘  │
│                                                              │
│  ┌─ QuickBooks Integration ──────────────────────────────┐  │
│  │  Status: ● Connected                                   │  │
│  │  Last Sync: Nov 27, 2025 2:30 PM                       │  │
│  │                                                         │  │
│  │  [Sync Estimate to QB] [View QB Details]               │  │
│  └────────────────────────────────────────────────────────┘  │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

---

## Technical Considerations

### PDF Generation
- **Option 1:** Use existing DomPDF (already in project)
- **Option 2:** Use Snappy (wkhtmltopdf wrapper)
- **Recommendation:** Enhance existing print route to support PDF download

### QuickBooks Integration
- **SDK:** Use QuickBooks PHP SDK or REST API
- **Authentication:** OAuth 2.0 (store tokens in database or cache)
- **Sync Strategy:** 
  - On-demand sync (manual button)
  - Optional: Auto-sync on estimate approval
- **Error Handling:** Log QB API errors, show user-friendly messages

### Performance
- PO generation should be backgroundable for large estimates
- Consider queuing QB sync operations
- Cache report data for large estimates

### Security
- Verify user permissions before printing/syncing
- Sanitize data before QB sync
- Audit log for QB operations (optional)

---

## File Structure

```
app/
├── Http/Controllers/
│   ├── EstimateController.php (enhance)
│   ├── PurchaseOrderController.php (new)
│   └── Reports/
│       └── EstimateReportController.php (new)
├── Models/
│   ├── Supplier.php (new)
│   ├── EstimatePurchaseOrder.php (new)
│   └── EstimatePurchaseOrderItem.php (new)
├── Services/
│   ├── PurchaseOrderService.php (new)
│   ├── QuickBooksEstimateService.php (new)
│   └── EstimateReportService.php (new)
└── View/Components/
    └── PurchaseOrderCard.php (new - optional)

resources/views/
├── estimates/
│   ├── partials/
│   │   └── print-documents.blade.php (new)
│   └── print-templates/
│       ├── full-detail.blade.php (new)
│       ├── materials-only.blade.php (new)
│       ├── labor-only.blade.php (new)
│       └── summary.blade.php (new)
├── purchase-orders/
│   ├── index.blade.php (new)
│   ├── show.blade.php (new)
│   └── print.blade.php (new)
└── reports/
    ├── cost-analysis.blade.php (new)
    ├── labor-summary.blade.php (new)
    ├── material-requirements.blade.php (new)
    └── profit-analysis.blade.php (new)

database/migrations/
├── xxxx_create_suppliers_table.php (new)
├── xxxx_create_estimate_purchase_orders_table.php (new)
└── xxxx_create_estimate_purchase_order_items_table.php (new)

routes/web.php
└── (Add new routes for POs, reports, QB sync)
```

---

## Next Steps - Implementation Order

### ✅ Immediate (Phase 1 - Week 1) - COMPLETED
1. ✅ Create this plan document
2. ✅ Add Print Documents tab to estimates page
3. ✅ Create print-documents partial
4. ✅ Implement basic print template selection UI
5. ✅ Create enhanced print views (full, materials, labor, summary)
6. ✅ Test print/PDF functionality

### 🔄 Short Term (Phase 2 - Week 2) - IN PROGRESS
7. ✅ Create database migrations for suppliers and POs
8. ⏳ Build supplier management (Optional - deferred)
9. ✅ Implement PO generation service
10. ✅ Create PO management UI
11. ✅ Create PO print template
12. 🔜 **NEXT: Match materials with database catalog items and assign vendors automatically**

### Medium Term (Phase 3 - Week 3)
12. Set up QuickBooks integration
13. Implement estimate sync to QB
14. Implement PO sync to QB
15. Add sync status tracking

### Long Term (Phase 4 - Week 4+)
16. Create cost analysis report
17. Create labor hours summary
18. Create material requirements report
19. Create profit margin analysis
20. Polish and optimize

---

## Questions/Decisions Needed

1. **Supplier Management:** Do we need full supplier CRUD, or just basic linking?
2. **QB Integration:** Is QB already set up in the project? Do we have API credentials?
3. **PO Workflow:** Should POs have approval workflow, or just draft/sent/received?
4. **Auto-sync:** Should we auto-sync to QB on estimate approval, or manual only?
5. **Email:** Should we be able to email POs to suppliers from this interface?
6. **Print Headers:** Company logo, address, etc. - where should this be configured?

---

## Success Criteria

✅ User can print estimate in multiple formats  
✅ User can generate material purchase orders  
✅ User can print/download POs  
✅ User can sync estimate to QuickBooks  
✅ User can sync POs to QuickBooks  
✅ User can view cost analysis and reports  
✅ All features work correctly with existing estimate data  
✅ Print templates are professional and print-ready  
✅ QB sync handles errors gracefully  

---

**Document Version:** 2.0  
**Created:** November 27, 2025  
**Last Updated:** November 27, 2025  
**Status:** Phase 2 - 95% Complete, Next: Automatic Vendor Assignment  
**Next Review:** After vendor matching implementation

---

## Current Progress Summary

### ✅ Phase 1: COMPLETE (100%)
- Print Documents tab with 5 template options
- Full-detail, materials-only, labor-only, summary, proposal views
- Print and PDF download functionality working

### 🔄 Phase 2: IN PROGRESS (95%)
**Completed:**
- Database schema for suppliers and POs
- Models with relationships and auto-calculations
- PO generation service (basic grouping by supplier_id)
- Full CRUD operations for POs
- Professional print template
- UI with generate, view, print, delete, bulk operations

**Next Task:**
- Implement automatic material matching and vendor assignment
- Match estimate items with catalog materials by name/SKU
- Ensure one PO per vendor with all their materials
- Handle unmatched items gracefully

### ⏳ Phase 3: PENDING
- QuickBooks Integration (not started)

### ⏳ Phase 4: PENDING
- Reports & Analytics (not started)
