#!/bin/bash

echo "===================================="
echo "Catalog Issues - Automated Fix"
echo "===================================="
echo ""

echo "Step 1: Fixing catalog_type format issues..."
php artisan catalog:fix-broken-links
echo ""

echo "Step 2: Clearing orphaned catalog references..."
php artisan catalog:clear-orphaned --no-interaction
echo ""

echo "Step 3: Final audit..."
php artisan catalog:audit-links
echo ""

echo "Step 4: Clearing cache..."
php artisan cache:clear
php artisan config:clear
php artisan view:clear
echo ""

echo "===================================="
echo "✓ All catalog issues have been fixed!"
echo "===================================="
echo ""
echo "Next steps:"
echo "1. Refresh your browser on the estimate page"
echo "2. Test the Reset button"
echo "3. Check browser console for any errors"
echo ""
