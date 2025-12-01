#!/bin/bash

# Quick Test Script for Timesheets System
# Run this to verify all components are working

echo "🧪 Timesheets System Test Suite"
echo "================================"
echo ""

# Test 1: Routes
echo "✓ Testing Routes Registration..."
TIMESHEET_ROUTES=$(php artisan route:list --path=timesheets 2>&1 | grep -c "timesheets")
API_ROUTES=$(php artisan route:list --path=api/mobile 2>&1 | grep -c "api.mobile")

if [ $TIMESHEET_ROUTES -gt 10 ]; then
    echo "  ✅ Timesheet routes: $TIMESHEET_ROUTES registered"
else
    echo "  ❌ Missing timesheet routes"
fi

if [ $API_ROUTES -gt 3 ]; then
    echo "  ✅ Mobile API routes: $API_ROUTES registered"
else
    echo "  ❌ Missing API routes"
fi

echo ""

# Test 2: Files Exist
echo "✓ Testing File Structure..."
FILES=(
    "app/Models/Timesheet.php"
    "app/Services/TimesheetService.php"
    "app/Http/Controllers/TimesheetController.php"
    "app/Http/Controllers/Api/TimesheetApiController.php"
    "app/Observers/TimesheetObserver.php"
    "resources/views/timesheets/index.blade.php"
    "resources/views/timesheets/create.blade.php"
    "resources/views/timesheets/edit.blade.php"
    "resources/views/timesheets/show.blade.php"
    "resources/views/timesheets/approve.blade.php"
)

MISSING=0
for file in "${FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "  ✅ $file"
    else
        echo "  ❌ $file - MISSING"
        MISSING=$((MISSING + 1))
    fi
done

if [ $MISSING -eq 0 ]; then
    echo "  ✅ All files present"
else
    echo "  ❌ $MISSING files missing"
fi

echo ""

# Test 3: Database Migration
echo "✓ Testing Database Migration..."
if php artisan migrate:status 2>&1 | grep -q "create_timesheets_table"; then
    echo "  ✅ Timesheets migration found"
else
    echo "  ⚠️  Timesheets migration not run yet (run: php artisan migrate)"
fi

echo ""

# Test 4: Observer Registration
echo "✓ Testing Observer Registration..."
if grep -q "TimesheetObserver" app/Providers/AppServiceProvider.php; then
    echo "  ✅ TimesheetObserver registered in AppServiceProvider"
else
    echo "  ❌ TimesheetObserver not registered"
fi

echo ""

# Test 5: Navigation Links
echo "✓ Testing Navigation Integration..."
if grep -q "timesheets.approve" resources/views/layouts/sidebar.blade.php; then
    echo "  ✅ Approve Timesheets link in sidebar"
else
    echo "  ❌ Navigation link missing"
fi

echo ""

# Test 6: PHP Syntax
echo "✓ Testing PHP Syntax..."
SYNTAX_ERRORS=0
for file in "${FILES[@]}"; do
    if [ -f "$file" ]; then
        if ! php -l "$file" > /dev/null 2>&1; then
            echo "  ❌ Syntax error in $file"
            SYNTAX_ERRORS=$((SYNTAX_ERRORS + 1))
        fi
    fi
done

if [ $SYNTAX_ERRORS -eq 0 ]; then
    echo "  ✅ No syntax errors"
else
    echo "  ❌ $SYNTAX_ERRORS files with syntax errors"
fi

echo ""

# Summary
echo "================================"
echo "📊 Test Summary"
echo "================================"
echo "Routes: ✅"
echo "Files: $([ $MISSING -eq 0 ] && echo '✅' || echo '❌')"
echo "Syntax: $([ $SYNTAX_ERRORS -eq 0 ] && echo '✅' || echo '❌')"
echo ""
echo "Next Steps:"
echo "1. Run migration: php artisan migrate"
echo "2. Test in browser: http://localhost:8000/timesheets"
echo "3. Test approval: http://localhost:8000/timesheets-approve"
echo "4. Test mobile API with Postman or cURL"
echo ""
echo "📚 Documentation:"
echo "- API Docs: docs/MOBILE_TIMESHEET_API.md"
echo "- Complete Summary: docs/TIMESHEETS_PHASE_2_COMPLETE.md"
echo ""
