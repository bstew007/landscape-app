# Scripts Directory

This directory contains utility scripts for development, testing, and maintenance tasks. These scripts are not part of the core application but are useful for development and administrative purposes.

## 📁 Directory Structure

### `/testing/`
Development and testing scripts for verifying functionality.

- **test-api-direct.php** - Direct API endpoint testing
- **test-calculator-import.php** - Tests calculator → estimate import functionality
  - Usage: `php scripts/testing/test-calculator-import.php [calculation_id] [estimate_id] [area_name]`
- **test-calculator-output.php** - Verifies enhanced calculator output format
  - Usage: `php scripts/testing/test-calculator-output.php [calculation_id]`
- **test-catalog-api.php** - Tests material/labor catalog API
- **test-full-flow.php** - End-to-end test: calculator → import → catalog linking
  - Usage: `php scripts/testing/test-full-flow.php [calculation_id] [estimate_id]`
- **test-material-catalog.php** - Comprehensive material catalog integration test
- **test-vendor-matching.php** - Tests vendor assignment when generating POs

### `/maintenance/`
Database maintenance and diagnostic scripts.

**Shell Scripts:**
- **fix-all-catalog-issues.sh** - Automated catalog issue resolution (runs multiple artisan commands)
- **fix-estimate-totals.sh** - Recalculates all estimate totals

**PHP Scripts:**
- **check-estimates.php** - Lists all estimates with basic info
- **check-budget-data.php** - Displays company budget information
- **check-labor-items.php** - Lists all labor items in catalog
- **check-newest-items.php** - Shows latest estimate items (for debugging)
- **check-orphaned-items.php** - Finds estimate items with broken catalog references
- **fix-orphaned-items.php** - Cleans up orphaned estimate items
- **list-labor-items.php** - Simple labor catalog listing

### `/data-migration/`
One-time data migration and seeding scripts (legacy - may not be needed for new installs).

- **assign-suppliers-to-materials.php** - Bulk supplier assignment to materials
- **merge-duplicate-vendors.php** - Merges duplicate vendor contacts
- **seed-mulch-materials.php** - Seeds sample mulch materials for testing
- **sync-material-suppliers.php** - Syncs supplier_id from vendor_name
- **sync-vendors-to-suppliers.php** - Creates Supplier records from vendor_name values
- **materials_export.json** - Material catalog backup/export
- **materials_import.sql** - Material catalog SQL import

## 🚀 Usage Guidelines

### Running Scripts

All scripts should be run from the **application root directory**:

```bash
# From project root
cd /path/to/landscape-app

# Run testing scripts
php scripts/testing/test-calculator-import.php 1 5 "Front Yard"

# Run maintenance scripts
php scripts/maintenance/check-estimates.php
bash scripts/maintenance/fix-all-catalog-issues.sh

# Run data migration (use with caution)
php scripts/data-migration/sync-material-suppliers.php
```

### Safety Notes

⚠️ **Data Migration Scripts**: These scripts modify database records. Always:
- Review the script code before running
- Backup your database first
- Test on staging environment first
- Many of these were one-time migrations and may not be needed anymore

✅ **Testing Scripts**: Safe to run repeatedly - designed for development

✅ **Maintenance Scripts**: Generally safe, but `fix-*` scripts should be reviewed first

## 📝 Best Practices

1. **New Scripts**: Add new utility scripts to the appropriate subdirectory
2. **Documentation**: Include usage instructions in script header comments
3. **Testing**: Test scripts on staging before production
4. **Cleanup**: Archive or delete scripts that are no longer needed
5. **Version Control**: These scripts are tracked in git for team collaboration

## 🔄 Migration to Artisan Commands

Consider converting frequently-used scripts to Laravel Artisan commands:

```bash
# Instead of: php scripts/maintenance/check-estimates.php
# Create: php artisan estimates:list

# Benefits:
# - Better error handling
# - Laravel service container access
# - Consistent command interface
# - Built-in help documentation
```

---

**Last Updated**: November 29, 2024
**Maintained By**: Development Team
