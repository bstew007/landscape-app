# Equipment Catalog Implementation

## Overview
Complete equipment catalog system implemented following the same patterns as Materials and Labor catalogs.

## Database

### Migration
- **File:** `database/migrations/2025_12_05_220000_create_equipment_catalog_table.php`
- **Table:** `equipment_catalog`
- **Key Fields:**
  - `id`, `name`, `sku`, `category`, `model`
  - `ownership_type` (enum: 'company', 'rental')
  - `unit` ('hr' or 'day')
  - `hourly_cost`, `daily_cost`, `hourly_rate`, `daily_rate`
  - `breakeven`, `profit_percent`
  - `vendor_name` (for rentals)
  - `asset_id` (links to Assets table for company-owned)
  - `is_active`, `description`, `notes`
  - `created_at`, `updated_at`

### Seeder
- **File:** `database/seeders/EquipmentCatalogSeeder.php`
- **Purpose:** Seeds catalog with existing company-owned assets
- **Status:** ✅ Successfully seeded 15 items from Assets table

## Models

### EquipmentItem
- **File:** `app/Models/EquipmentItem.php`
- **Relationships:**
  - `belongsTo(Asset::class)` - Optional link to asset management
- **Scopes:**
  - `active()` - Get only active equipment
  - `companyOwned()` - Filter by company-owned
  - `rental()` - Filter by rentals
- **Methods:**
  - `isCompanyOwned()` - Check ownership type
  - `isRental()` - Check if rental
  - `getPrimaryRate()` - Get rate based on unit type
  - `getPrimaryCost()` - Get cost based on unit type

## Controllers

### EquipmentController
- **File:** `app/Http/Controllers/EquipmentController.php`
- **Routes:**
  - `GET /equipment` - Index with search/filters
  - `GET /equipment/create` - Create form
  - `POST /equipment` - Store new equipment
  - `GET /equipment/{id}/edit` - Edit form
  - `PATCH /equipment/{id}` - Update equipment
  - `DELETE /equipment/{id}` - Delete equipment
  - `GET /equipment/import` - Import form
  - `POST /equipment/import` - Process import (JSON/CSV)
  - `GET /equipment/export` - Export to CSV
  - `POST /equipment/bulk` - Bulk actions

### API EquipmentController
- **File:** `app/Http/Controllers/Api/EquipmentController.php`
- **API Routes:**
  - `GET /api/equipment/active` - Get all active equipment
  - `GET /api/equipment/search` - Search equipment
  - `GET /api/equipment/{id}` - Get single equipment

## Views

### Index View
- **File:** `resources/views/equipment/index.blade.php`
- **Features:**
  - Search by name, SKU, model, category
  - Filter by category and ownership type (Company/Rental)
  - Bulk actions (delete, activate, deactivate, change category)
  - Stats cards showing totals
  - Pagination

### Create/Edit Views
- **Files:**
  - `resources/views/equipment/create.blade.php`
  - `resources/views/equipment/edit.blade.php`
- **Features:**
  - Prominent ownership type toggle (Company 🏢 vs Rental 🔑)
  - Conditional fields based on ownership:
    - **Company:** Asset selector dropdown
    - **Rental:** Vendor name field
  - Pricing calculator (auto-calculates rate from cost + margin)
  - Unit selector (Hourly or Daily)
  - Category, model, description, notes
  - Active status toggle

### Import View
- **File:** `resources/views/equipment/import.blade.php`
- Supports JSON and CSV imports

### Equipment Catalog Picker Component
- **File:** `resources/views/components/equipment-catalog-picker.blade.php`
- **Features:**
  - Modal interface for equipment selection
  - Search by name, SKU, model
  - Filter by category and ownership type
  - Real-time filtering
  - Displays rates and ownership badges
  - JavaScript API for integration

## Routes

### Web Routes (Admin/Manager only)
```php
Route::middleware(['role:admin,manager'])->group(function () {
    Route::get('equipment/import', [EquipmentController::class, 'importForm']);
    Route::post('equipment/import', [EquipmentController::class, 'import']);
    Route::get('equipment/export', [EquipmentController::class, 'export']);
    Route::post('equipment/bulk', [EquipmentController::class, 'bulk']);
    Route::resource('equipment', EquipmentController::class)->except(['show']);
});
```

### API Routes
```php
Route::prefix('api')->middleware('auth')->group(function () {
    Route::get('/equipment/active', [ApiEquipmentController::class, 'active']);
    Route::get('/equipment/search', [ApiEquipmentController::class, 'search']);
    Route::get('/equipment/{equipment}', [ApiEquipmentController::class, 'show']);
});
```

## Navigation

### Sidebar Integration
- **File:** `resources/views/layouts/sidebar.blade.php`
- **Location:** Under "PRICE LIST" section
- Added "Equipment Catalog" link in both sidebars (desktop and mobile)

## Key Features

### 1. Ownership Type System
- **Company-Owned:** Equipment your company owns
  - Can link to existing Assets for tracking
  - Use for owned skid steers, trucks, mowers, etc.
  
- **Rental:** Equipment rented from vendors
  - Track rental vendor name
  - Manage rental rates

### 2. Flexible Pricing
- **Hourly or Daily rates**
- Auto-calculation: `Rate = Cost × (1 + Profit %)`
- Separate fields for cost and billable rate
- Breakeven tracking

### 3. Integration with Assets
- Optional linking to Asset Management system
- Equipment catalog is separate (for estimates/pricing)
- Assets are for operational tracking
- Can seed catalog from existing assets

### 4. Search & Filtering
- Search by: name, SKU, model, category
- Filter by: category, ownership type
- Active/inactive status filtering

### 5. Import/Export
- CSV export of entire catalog
- JSON/CSV import for bulk updates
- Same format as Material/Labor catalogs

## Usage Workflow

### Adding Company Equipment
1. Navigate to Equipment Catalog
2. Click "Add Equipment"
3. Select "Company-Owned" type
4. Optionally link to existing Asset
5. Set hourly or daily cost
6. Set profit margin % (auto-calculates rate)
7. Save

### Adding Rental Equipment
1. Navigate to Equipment Catalog
2. Click "Add Equipment"
3. Select "Rental" type
4. Enter vendor name
5. Set rental cost and rate
6. Save

### Seeding from Assets
```bash
php artisan db:seed --class=EquipmentCatalogSeeder
```
This imports all active equipment-type assets as company-owned catalog items.

## Future Enhancements (Not Implemented)

### For Estimates Integration
The following would complete the integration with estimates (similar to Materials/Labor):

1. **EstimateController Updates**
   - Pass `$equipmentCatalog` to estimate views
   - Add equipment to `show()` method

2. **Add Items Panel**
   - Add "Equipment" tab alongside Materials and Labor
   - Include equipment catalog picker

3. **JavaScript Integration**
   - Wire equipment catalog form in `estimate-show.js`
   - Handle equipment selection and pricing
   - Update estimate totals

4. **EstimateItem Updates**
   - Add support for `item_type = 'equipment'`
   - Store equipment pricing by hour/day

## Files Created

### Migrations
- `database/migrations/2025_12_05_220000_create_equipment_catalog_table.php` ✅

### Models
- `app/Models/EquipmentItem.php` ✅

### Controllers
- `app/Http/Controllers/EquipmentController.php` ✅
- `app/Http/Controllers/Api/EquipmentController.php` ✅

### Views
- `resources/views/equipment/index.blade.php` ✅
- `resources/views/equipment/create.blade.php` ✅
- `resources/views/equipment/edit.blade.php` ✅
- `resources/views/equipment/import.blade.php` ✅
- `resources/views/components/equipment-catalog-picker.blade.php` ✅

### Seeders
- `database/seeders/EquipmentCatalogSeeder.php` ✅

### Routes
- Updated `routes/web.php` with equipment routes ✅

### Navigation
- Updated `resources/views/layouts/sidebar.blade.php` ✅

## Database Status

- ✅ Migration run successfully
- ✅ Table created: `equipment_catalog`
- ✅ Seeded with 15 company-owned items from existing assets
- ✅ Frontend assets built

## Testing the Catalog

1. **View catalog:** Navigate to Equipment → Equipment Catalog
2. **Add equipment:** Click "Add Equipment" button
3. **Toggle ownership:** Use the Company/Rental toggle
4. **Search/Filter:** Test search and category filters
5. **Bulk actions:** Select multiple items and test bulk operations
6. **Import/Export:** Test CSV export and import functionality

## Notes

- The catalog is completely independent of Asset Management
- Use for pricing/estimating purposes
- Equipment rates can be updated from Company Budget in the future
- Follows exact same patterns as Materials and Labor catalogs
- All views use the same brand styling and components

---

**Status:** ✅ Complete and ready for use
**Date Implemented:** December 5, 2025
