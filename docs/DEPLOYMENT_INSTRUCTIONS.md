# Deployment Steps for Material Vendor Dropdown Fix

## Problem
On production, materials are not showing the selected vendor in the dropdown when editing, causing PO generation issues. This is because the production database is missing the `supplier_id` column and relationships.

## Solution
Deploy the migration and sync existing data.

---

## Steps to Deploy on Laravel Forge

### 1. Push Code to Repository
Make sure all changes are committed and pushed to your Git repository:
```bash
git add .
git commit -m "Add supplier_id to materials and sync vendor relationships"
git push origin main
```

### 2. Deploy via Forge Dashboard
- Go to your Laravel Forge dashboard
- Navigate to your site
- Click "Deploy Now" button
- Wait for deployment to complete

### 3. SSH into Server
Click "SSH" button in Forge or connect via terminal:
```bash
ssh forge@your-server-ip
cd /home/forge/your-site-domain
```

### 4. Run Migration
```bash
php artisan migrate
```

This will add the `supplier_id` column to the `materials` table.

### 5. Sync Existing Material Suppliers
Run the sync script to populate `supplier_id` for existing materials:
```bash
php sync-material-suppliers.php
```

This script will:
- Find all materials with a `vendor_name` but no `supplier_id`
- Match them against existing Contact records (where contact_type='vendor')
- Update the `supplier_id` field automatically
- Report any vendors that don't have matching contacts

### 6. Create Missing Vendors (if needed)
If the sync script reports vendors that weren't found:
- Log into your production site
- Go to `/contacts/create?type=vendor`
- Create Contact records for each missing vendor
- Re-run the sync script: `php sync-material-suppliers.php`

### 7. Verify
- Edit a material on production
- Verify the vendor dropdown shows the correct vendor selected
- Create a purchase order and verify it includes the correct vendor information

---

## Files Changed

### Database Migration
- `database/migrations/2025_11_28_000633_add_supplier_id_to_materials_table.php`

### Controllers
- `app/Http/Controllers/MaterialController.php`
  - `create()` - passes $vendors to view
  - `edit()` - passes $vendors to view  
  - `store()` - syncs vendor_name from supplier_id
  - `update()` - syncs vendor_name from supplier_id

### Views
- `resources/views/materials/create.blade.php` - vendor dropdown
- `resources/views/materials/edit.blade.php` - vendor dropdown

### Models
- `app/Models/Material.php` - supplier() relationship

### Scripts
- `sync-material-suppliers.php` - one-time data sync script

---

## Rollback (if needed)

If something goes wrong:

```bash
php artisan migrate:rollback --step=1
```

This will remove the `supplier_id` column and restore the previous state.
