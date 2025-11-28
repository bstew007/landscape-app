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

#### ~~`suppliers`~~ **DEPRECATED - NOW USING CONTACTS**
**Note:** Suppliers are now managed as Contacts with `contact_type = 'vendor'`. This unifies vendor management with customer management and aligns with QuickBooks model (Customers & Vendors).

The `clients` table now includes:
- `qbo_customer_id` - for QuickBooks Customer sync
- `qbo_vendor_id` - for QuickBooks Vendor sync
- `contact_type` - 'lead', 'client', 'vendor', 'owner'

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

**STATUS: 100% COMPLETE** ✅

#### Completed:
- ✅ Database tables created (estimate_purchase_orders, estimate_purchase_order_items)
- ✅ **Unified suppliers with Contacts** - vendors are now `contact_type = 'vendor'`
- ✅ Added `qbo_vendor_id` to clients table for QuickBooks integration
- ✅ Migrated all suppliers → contacts (32 vendors total)
- ✅ Models with relationships and business logic
- ✅ PurchaseOrderService with PO generation
- ✅ MaterialMatchingService with intelligent SKU/vendor SKU/name matching
- ✅ PurchaseOrderController with CRUD operations
- ✅ PO management UI in Print Documents tab
- ✅ PO print template for professional output
- ✅ Auto-generated PO numbers (PO-YYYY-0001 format)
- ✅ Status tracking (draft, sent, received, cancelled)
- ✅ Bulk print functionality
- ✅ Automatic vendor assignment from materials catalog
- ✅ Fuzzy matching with 70% threshold for materials without direct catalog links

#### ⚙️ Current System Behavior:

**How It Works:**
1. **PO Generation** groups materials by `material.supplier_id` (linked to contacts)
2. **Automatic Matching** uses MaterialMatchingService with priority:
   - Priority 1: Exact SKU match (100% confidence)
   - Priority 2: Exact vendor SKU match (100% confidence)
   - Priority 3: Exact name match (100% confidence)
   - Fallback: Fuzzy matching (SKU: 35%, vendor SKU: 25%, name: 40%, description: 10%)
3. **One PO per vendor** - materials automatically grouped by supplier
4. **Materials without vendors** - grouped into "Unassigned Vendor" PO for manual review

**Vendor Management:**
- ✅ Vendors managed at `/contacts` with filter `Type: vendor`
- ✅ Full CRUD operations via existing Contacts UI
- ✅ 32 vendors currently in system (migrated from old suppliers table)
- ⚠️ **TO DO: Merge duplicate vendors** (e.g., "SiteOne" vs "Site One")

**Next Maintenance Tasks:**
- [ ] Merge duplicate vendor contacts (same company, different spellings)
- [ ] Add missing vendor contact information (addresses, phone, email)
- [ ] Review and update material → vendor assignments as needed
- [ ] Consider adding vendor tags for better categorization

**Deliverable:** ✅ Generate and print material purchase orders - COMPLETE

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

### 🔄 Short Term (Phase 2 - Week 2) - COMPLETE ✅
7. ✅ Create database migrations for suppliers and POs
8. ✅ **Unified suppliers with Contacts** - vendors now managed in `/contacts`
9. ✅ Implement PO generation service
10. ✅ Create PO management UI
11. ✅ Create PO print template
12. ✅ Implement MaterialMatchingService with SKU/vendor SKU/fuzzy matching
13. ✅ Migrate all suppliers to contacts table
14. ✅ Add qbo_vendor_id to clients table

### 🔜 Immediate Next Steps
15. **Merge duplicate vendors** - consolidate vendors with different spellings
16. Clean up vendor contact information (addresses, phone, email)

### 🔄 Medium Term (Phase 3 - Week 3) - IN PROGRESS
12. ✅ Set up QuickBooks Vendor integration (QboVendorService created)
13. ✅ Implement vendor sync to QB (tested successfully with Martin Marietta, Hoffman Eco Works, SiteOne)
14. 🔜 Implement estimate sync to QB
15. 🔜 Implement PO sync to QB
16. ✅ Add vendor sync routes and controller methods

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

**Document Version:** 3.0  
**Created:** November 27, 2025  
**Last Updated:** November 27, 2025 (Phase 2 Complete)  
**Status:** Phase 2 - 100% Complete | Phase 3 - Ready to Start  
**Next Review:** After vendor consolidation and Phase 3 planning

---

## Current Progress Summary

### ✅ Phase 1: COMPLETE (100%)
- Print Documents tab with 5 template options
- Full-detail, materials-only, labor-only, summary, proposal views
- Print and PDF download functionality working

### 🔄 Phase 2: COMPLETE (100%)
**Completed:**
- Database schema for POs and vendor management via Contacts
- Unified suppliers with Contacts table (`contact_type = 'vendor'`)
- Added `qbo_vendor_id` to clients for QuickBooks integration
- Models with relationships and auto-calculations
- PO generation service with automatic vendor assignment
- MaterialMatchingService with intelligent SKU/vendor SKU/name matching
- Full CRUD operations for POs
- Professional print template with batch printing
- UI with generate, view, print, delete, bulk operations

**Current Task:**
- Merge duplicate vendors in contacts (different spellings of same company)

### 🔄 Phase 3: IN PROGRESS (30%)
**Completed:**
- ✅ Created QboVendorService for vendor sync
- ✅ Added vendor sync routes (sync, refresh, push-names, push-mobile)
- ✅ Extended ContactQboSyncController with vendor methods
- ✅ Tested vendor sync successfully (3 vendors synced to QBO)
- ✅ Created test command `php artisan qbo:test-vendor-sync`

**Next Tasks:**
- 🔜 Create QboEstimateService for estimate sync
- 🔜 Create QboPurchaseOrderService for PO sync
- 🔜 Add sync UI buttons to estimates page
- 🔜 Add sync UI buttons to PO management page

### ⏳ Phase 4: PENDING
- Reports & Analytics (not started)
