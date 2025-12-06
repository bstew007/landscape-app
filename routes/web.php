<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SiteVisitController;
use App\Http\Controllers\RetainingWallCalculatorController;
use App\Http\Controllers\PaverPatioCalculatorController;
use App\Http\Controllers\ProductionRateController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\FenceCalculatorController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\CalculationController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ClientHubController;
use App\Http\Controllers\EstimateController;
use App\Http\Controllers\PruningCalculatorController;
use App\Http\Controllers\WeedingCalculatorController;
use App\Http\Controllers\MulchingCalculatorController;
use App\Http\Controllers\PineNeedleCalculatorController;
use App\Http\Controllers\SynTurfCalculatorController;
use App\Http\Controllers\SiteVisitReportController;
use App\Http\Controllers\TurfMowingCalculatorController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\PlantingCalculatorController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\LaborController;
use App\Http\Controllers\EstimateItemController;
use App\Http\Controllers\Admin\CompanyBudgetController;
use App\Http\Controllers\Api\MaterialController as ApiMaterialController;
use App\Http\Controllers\Api\EquipmentController as ApiEquipmentController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\CalculatorImportController;


Route::get('/', fn () => redirect()->route('client-hub'));

// API Routes for Material & Equipment Catalog
Route::prefix('api')->middleware('auth')->group(function () {
    Route::get('/materials/active', [ApiMaterialController::class, 'active'])->name('api.materials.active');
    Route::get('/materials/search', [ApiMaterialController::class, 'search'])->name('api.materials.search');
    Route::get('/materials/{material}', [ApiMaterialController::class, 'show'])->name('api.materials.show');
    
    Route::get('/equipment/active', [ApiEquipmentController::class, 'active'])->name('api.equipment.active');
    Route::get('/equipment/search', [ApiEquipmentController::class, 'search'])->name('api.equipment.search');
    Route::get('/equipment/{equipment}', [ApiEquipmentController::class, 'show'])->name('api.equipment.show');
    
    // Mobile Timesheet API
    Route::get('/mobile/my-jobs', [\App\Http\Controllers\Api\TimesheetApiController::class, 'myJobs'])->name('api.mobile.my-jobs');
    Route::get('/mobile/my-timesheets', [\App\Http\Controllers\Api\TimesheetApiController::class, 'myTimesheets'])->name('api.mobile.my-timesheets');
    Route::post('/mobile/clock-in', [\App\Http\Controllers\Api\TimesheetApiController::class, 'clockIn'])->name('api.mobile.clock-in');
    Route::post('/mobile/clock-out', [\App\Http\Controllers\Api\TimesheetApiController::class, 'clockOut'])->name('api.mobile.clock-out');
    Route::post('/mobile/submit-timesheet', [\App\Http\Controllers\Api\TimesheetApiController::class, 'submitTimesheet'])->name('api.mobile.submit-timesheet');
});

Route::get('/dashboard', fn () => view('dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Route::get('/public/labor', [LaborController::class, 'publicIndex'])->name('public.labor.index');

// Public legal pages (for integrations like QBO)
Route::view('/legal/eula', 'legal.eula')->name('legal.eula');
Route::view('/legal/privacy', 'legal.privacy')->name('legal.privacy');
Route::view('/legal/terms', 'legal.terms')->name('legal.terms');

// QBO Webhook (must be public, no auth, and exempt from CSRF)
Route::post('integrations/qbo/webhook', [\App\Http\Controllers\Integrations\QboWebhookController::class, 'handle'])
    ->name('integrations.qbo.webhook')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

Route::middleware('auth')->group(function () {
     // ✅ Calculator Index Route
    Route::get('/calculators', function () {
        return view('calculators.index');
    })->name('calculators.index');

    // ✅ Calculator Import Route (for importing calculations to estimates)
    Route::post('/calculators/import-to-estimate', [CalculatorImportController::class, 'import'])
        ->name('calculators.import-to-estimate');

    Route::get('/client-hub', ClientHubController::class)->name('client-hub');

    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');

    // ✅ Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ✅ Production Rates (moved into Admin-only group above)

    // ✅ Catalogs (Admin & Manager Only)
    Route::middleware(['role:admin,manager'])->group(function () {
        Route::get('materials/import', [MaterialController::class, 'importForm'])->name('materials.importForm');
        Route::post('materials/import', [MaterialController::class, 'import'])->name('materials.import');
        Route::get('materials/export', [MaterialController::class, 'export'])->name('materials.export');
        // Temporary ping route to help diagnose 404s on POST /materials/bulk
        Route::get('materials/bulk', function() { return response('bulk ping', 200); })->name('materials.bulk.ping');
        Route::match(['post','delete'], 'materials/bulk', [MaterialController::class, 'bulk'])->name('materials.bulk');
        // Alternate path to avoid any web server collisions with /materials/*
        Route::get('catalog/materials/bulk', function() { return response('bulk alt ping', 200); })->name('materials.bulk.alt.ping');
        Route::match(['post','delete'], 'catalog/materials/bulk', [MaterialController::class, 'bulk'])->name('materials.bulk.alt');
        Route::resource('materials', MaterialController::class)->except(['show']);
        Route::get('labor/import', [LaborController::class, 'importForm'])->name('labor.importForm');
        Route::post('labor/import', [LaborController::class, 'import'])->name('labor.import');
        Route::get('labor/export', [LaborController::class, 'export'])->name('labor.export');
        Route::resource('labor', LaborController::class)->except(['show']);
        
        Route::get('equipment/import', [EquipmentController::class, 'importForm'])->name('equipment.importForm');
        Route::post('equipment/import', [EquipmentController::class, 'import'])->name('equipment.import');
        Route::get('equipment/export', [EquipmentController::class, 'export'])->name('equipment.export');
        Route::post('equipment/bulk', [EquipmentController::class, 'bulk'])->name('equipment.bulk');
        Route::resource('equipment', EquipmentController::class)->except(['show']);
        
        // Bulk update for production rates
        Route::patch('production-rates/bulk', [ProductionRateController::class, 'bulkUpdate'])->name('production-rates.bulkUpdate');
        Route::resource('production-rates', ProductionRateController::class)->except(['show']);
    });

    // Read-only catalogs for non-admin users could be added here if desired
    
    // API endpoint for catalog defaults (used by estimate line items)
    Route::get('/api/catalog/{type}/{id}', function ($type, $id) {
        // Normalize type (handle both 'labor' and 'App\Models\LaborItem')
        $type = strtolower($type);
        if (strpos($type, 'laboritem') !== false || $type === 'labor') {
            $type = 'labor';
        } elseif (strpos($type, 'material') !== false) {
            $type = 'material';
        } elseif (strpos($type, 'equipment') !== false) {
            $type = 'equipment';
        }
        
        if ($type === 'labor') {
            // Look for the labor item, including inactive ones
            $item = \App\Models\LaborItem::find($id);
            
            // Debug logging
            \Log::info('Catalog API lookup', [
                'type' => 'labor',
                'id' => $id,
                'found' => $item ? 'yes' : 'no',
                'is_active' => $item ? $item->is_active : null,
                'name' => $item ? $item->name : null,
            ]);
            
            if (!$item) {
                return response()->json([
                    'error' => 'Labor item not found',
                    'message' => "Labor catalog item with ID {$id} does not exist in the database. It may have been deleted.",
                    'debug' => [
                        'requested_id' => $id,
                        'type' => 'labor',
                        'table' => 'labor_catalog',
                    ]
                ], 404);
            }
            
            // Warn if inactive but still return data
            if (!$item->is_active) {
                \Log::warning('Catalog item is inactive', ['type' => 'labor', 'id' => $id, 'name' => $item->name]);
            }
            
            $wage = (float) ($item->average_wage ?? 0);
            $otMult = max(1, (float) ($item->overtime_factor ?? 1));
            $burdenPct = max(0, (float) ($item->labor_burden_percentage ?? 0));
            $unbillPct = min(99.9, max(0, (float) ($item->unbillable_percentage ?? 0)));
            $effectiveWage = $wage * $otMult;
            $unitCost = $effectiveWage * (1 + ($burdenPct / 100));
            $billableFraction = max(0.01, 1 - ($unbillPct / 100));
            $unitCostPerBillableHour = $unitCost / $billableFraction;
            
            // Get overhead rate from active budget
            $budget = app(\App\Services\BudgetService::class)->active();
            $overheadRate = 0.0;
            if ($budget) {
                $overheadRate = (float) data_get($budget->inputs, 'oh_recovery.labor_hour.markup_per_hour', 0);
                if ($overheadRate == 0 && $budget->outputs) {
                    $overheadRate = (float) data_get($budget->outputs, 'labor.ohr', 0);
                }
            }
            
            $breakeven = $unitCostPerBillableHour + $overheadRate;
            $defaultMarginRate = (float) ($budget->desired_profit_margin ?? 0.2);
            $unitPrice = $breakeven / (1 - $defaultMarginRate);
            
            return response()->json([
                'unit_cost' => round($unitCostPerBillableHour, 2),
                'unit_price' => round($unitPrice, 2),
                'overhead_rate' => $overheadRate,
                'name' => $item->name,
                'unit' => $item->unit,
            ]);
        } elseif ($type === 'equipment') {
            // Look for the equipment item, including inactive ones
            $item = \App\Models\EquipmentItem::find($id);
            
            // Debug logging
            \Log::info('Catalog API lookup', [
                'type' => 'equipment',
                'id' => $id,
                'found' => $item ? 'yes' : 'no',
                'is_active' => $item ? $item->is_active : null,
                'name' => $item ? $item->name : null,
            ]);
            
            if (!$item) {
                return response()->json([
                    'error' => 'Equipment not found',
                    'message' => "Equipment catalog item with ID {$id} does not exist in the database. It may have been deleted.",
                    'debug' => [
                        'requested_id' => $id,
                        'type' => 'equipment',
                        'table' => 'equipment_catalog',
                    ]
                ], 404);
            }
            
            // Warn if inactive but still return data
            if (!$item->is_active) {
                \Log::warning('Catalog item is inactive', ['type' => 'equipment', 'id' => $id, 'name' => $item->name]);
            }
            
            // Use hourly or daily cost/rate based on unit type
            $unitCost = $item->unit === 'hr' ? (float) $item->hourly_cost : (float) $item->daily_cost;
            $unitPrice = $item->unit === 'hr' ? (float) $item->hourly_rate : (float) $item->daily_rate;
            
            return response()->json([
                'unit_cost' => round($unitCost, 2),
                'unit_price' => round($unitPrice, 2),
                'name' => $item->name,
                'unit' => $item->unit,
                'ownership_type' => $item->ownership_type,
            ]);
        } elseif ($type === 'material') {
            // Look for the material, including inactive ones
            $item = \App\Models\Material::find($id);
            
            // Debug logging
            \Log::info('Catalog API lookup', [
                'type' => 'material',
                'id' => $id,
                'found' => $item ? 'yes' : 'no',
                'is_active' => $item ? $item->is_active : null,
                'name' => $item ? $item->name : null,
            ]);
            
            if (!$item) {
                return response()->json([
                    'error' => 'Material not found',
                    'message' => "Material catalog item with ID {$id} does not exist in the database. It may have been deleted.",
                    'debug' => [
                        'requested_id' => $id,
                        'type' => 'material',
                        'table' => 'materials',
                    ]
                ], 404);
            }
            
            // Warn if inactive but still return data
            if (!$item->is_active) {
                \Log::warning('Catalog item is inactive', ['type' => 'material', 'id' => $id, 'name' => $item->name]);
            }
            
            $budget = app(\App\Services\BudgetService::class)->active();
            $defaultMarginRate = (float) ($budget->desired_profit_margin ?? 0.2);
            $unitCost = (float) $item->unit_cost;
            $taxRate = (float) ($item->tax_rate ?? 0);
            $breakeven = $unitCost * (1 + $taxRate);
            $unitPrice = $breakeven / (1 - $defaultMarginRate);
            
            return response()->json([
                'unit_cost' => round($unitCost, 2),
                'unit_price' => round($unitPrice, 2),
                'tax_rate' => $taxRate,
                'name' => $item->name,
                'unit' => $item->unit,
            ]);
        }
        
        return response()->json(['error' => 'Invalid type'], 400);
    })->name('api.catalog.defaults');

    // ✅ Save calculation to site visit
    Route::post('/site-visits/calculation', [SiteVisitController::class, 'storeCalculation'])
        ->name('site-visits.storeCalculation');

    // ✅ Site Visit Selector (before calculator)
    Route::get('/select-site-visit', [SiteVisitController::class, 'select'])->name('site-visit.select');
    Route::get('/calculators/select-site-visit', [SiteVisitController::class, 'select'])->name('calculators.selectSiteVisit');

    // ✅ Calculations - Shared Routes
    Route::delete('/calculations/{calculation}', [CalculationController::class, 'destroy'])
        ->name('site-visits.deleteCalculation');

    // ================================
// ✅ Fence Calculator Routes
// ================================
Route::prefix('calculators/fence')->group(function () {
    Route::get('/form', [FenceCalculatorController::class, 'showForm'])->name('calculators.fence.form');
    Route::post('/calculate', [FenceCalculatorController::class, 'calculate'])->name('calculators.fence.calculate');

    Route::get('/results/{calculation}', [FenceCalculatorController::class, 'showResult'])
        ->name('calculators.fence.showResult');

    Route::get('/{calculation}/edit', [FenceCalculatorController::class, 'edit'])->name('calculators.fence.edit');

    Route::get('/{calculation}/pdf', [FenceCalculatorController::class, 'downloadPdf'])
        ->name('calculators.fence.downloadPdf');
});


    // ================================
    // ✅ Paver Patio Calculator Routes
    // ================================
    Route::prefix('calculators/paver-patio')->group(function () {
        Route::get('/', [PaverPatioCalculatorController::class, 'showForm'])->name('calculators.patio.form');
        Route::post('/', [PaverPatioCalculatorController::class, 'calculate'])->name('calculators.patio.calculate');
        Route::get('/results/{calculation}', [PaverPatioCalculatorController::class, 'showResult'])->name('calculations.patio.showResult');
        Route::get('/{calculation}/edit', [PaverPatioCalculatorController::class, 'edit'])->name('calculators.patio.edit');
        Route::get('/{calculation}/pdf', [PaverPatioCalculatorController::class, 'downloadPdf'])->name('calculations.patio.downloadPdf');
    });

    // ================================
    // ✅ Retaining Wall Calculator Routes
    // ================================
        Route::prefix('calculators/retaining-wall')->group(function () {
        Route::get('/', [RetainingWallCalculatorController::class, 'showForm'])->name('calculators.wall.form');
        Route::post('/', [RetainingWallCalculatorController::class, 'calculate'])->name('calculators.wall.calculate');
        Route::get('/results/{calculation}', [RetainingWallCalculatorController::class, 'showResult'])->name('calculations.wall.showResult');
        Route::get('/{calculation}/edit', [RetainingWallCalculatorController::class, 'edit'])->name('calculators.wall.edit');
        Route::get('/{calculation}/pdf', [RetainingWallCalculatorController::class, 'downloadPdf'])->name('calculations.wall.downloadPdf');
    });

    // ================================
    // ✅ Weeding
    // ================================
    Route::prefix('calculators/weeding')->name('calculators.weeding.')->group(function () {
    Route::get('/', [WeedingCalculatorController::class, 'showForm'])->name('form');
    Route::post('/calculate', [WeedingCalculatorController::class, 'calculate'])->name('calculate');
    Route::get('/{calculation}/edit', [WeedingCalculatorController::class, 'edit'])->name('edit');
    Route::get('/results/{calculation}', [WeedingCalculatorController::class, 'showResult'])->name('showResult');
});

    Route::get('/calculators/weeding/pdf/{calculation}', [WeedingCalculatorController::class, 'downloadPdf'])
    ->name('calculators.weeding.downloadPdf');

 // ================================
    // ✅ Mulching
    // ================================
    Route::prefix('calculators/mulching')->name('calculators.mulching.')->group(function () {
    Route::get('/', [MulchingCalculatorController::class, 'showForm'])->name('form');
    Route::post('/calculate', [MulchingCalculatorController::class, 'calculate'])->name('calculate');
    Route::get('/{calculation}/edit', [MulchingCalculatorController::class, 'edit'])->name('edit');
    Route::get('/results/{calculation}', [MulchingCalculatorController::class, 'showResult'])->name('showResult');
});

    Route::get('/calculators/mulching/pdf/{calculation}', [MulchingCalculatorController::class, 'downloadPdf'])
    ->name('calculators.mulching.downloadPdf');


// ================================
    // ✅ Pine Needles
    // ================================
    Route::prefix('calculators/pine_needles')->name('calculators.pine_needles.')->group(function () {
    Route::get('/', [PineNeedleCalculatorController::class, 'showForm'])->name('form');
    Route::post('/calculate', [PineNeedleCalculatorController::class, 'calculate'])->name('calculate');
    Route::get('/{calculation}/edit', [PineNeedleCalculatorController::class, 'edit'])->name('edit');
    Route::get('/results/{calculation}', [PineNeedleCalculatorController::class, 'showResult'])->name('showResult');
});

    Route::get('/calculators/pine_needles/pdf/{calculation}', [PineNeedleCalculatorController::class, 'downloadPdf'])
    ->name('calculators.pine_needles.downloadPdf');



// ================================
// ✅ Synthetic Turf
// ================================
Route::prefix('calculators/syn-turf')->name('calculators.syn_turf.')->group(function () {
    Route::get('/', [SynTurfCalculatorController::class, 'showForm'])->name('form');
    Route::post('/calculate', [SynTurfCalculatorController::class, 'calculate'])->name('calculate');
    Route::get('/{calculation}/edit', [SynTurfCalculatorController::class, 'edit'])->name('edit');
    Route::get('/results/{calculation}', [SynTurfCalculatorController::class, 'showResult'])->name('showResult');
    Route::post('/{calculation}/email', [SynTurfCalculatorController::class, 'emailEstimate'])->name('email');
});

Route::get('/calculators/syn-turf/pdf/{calculation}', [SynTurfCalculatorController::class, 'downloadPdf'])
    ->name('calculators.syn_turf.downloadPdf');

// ================================
// Planting
// ================================
Route::prefix('calculators/planting')->name('calculators.planting.')->group(function () {
    Route::get('/', [PlantingCalculatorController::class, 'showForm'])->name('form');
    Route::post('/calculate', [PlantingCalculatorController::class, 'calculate'])->name('calculate');
    Route::get('/{calculation}/edit', [PlantingCalculatorController::class, 'edit'])->name('edit');
    Route::get('/results/{calculation}', [PlantingCalculatorController::class, 'showResult'])->name('showResult');
});

Route::get('/calculators/planting/pdf/{calculation}', [PlantingCalculatorController::class, 'downloadPdf'])
    ->name('calculators.planting.downloadPdf');


// ================================
// ✅ Turf Mowing
// ================================
Route::prefix('calculators/turf-mowing')->name('calculators.turf_mowing.')->group(function () {
    Route::get('/', [TurfMowingCalculatorController::class, 'showForm'])->name('form');
    Route::post('/calculate', [TurfMowingCalculatorController::class, 'calculate'])->name('calculate');
    Route::get('/{calculation}/edit', [TurfMowingCalculatorController::class, 'edit'])->name('edit');
    Route::get('/results/{calculation}', [TurfMowingCalculatorController::class, 'showResult'])->name('showResult');
});

Route::get('/calculators/turf-mowing/pdf/{calculation}', [TurfMowingCalculatorController::class, 'downloadPdf'])
    ->name('calculators.turf_mowing.downloadPdf');


// ================================
// ✅ Pruning
// ================================

    Route::prefix('calculators/pruning')->name('calculators.pruning.')->group(function () {
    Route::get('/', [PruningCalculatorController::class, 'showForm'])->name('form');
    Route::post('/calculate', [PruningCalculatorController::class, 'calculate'])->name('calculate');
    Route::get('/{calculation}/edit', [PruningCalculatorController::class, 'edit'])->name('edit');
    Route::get('/results/{calculation}', [PruningCalculatorController::class, 'showResult'])->name('showResult');
});

// ✅ This must be OUTSIDE the group
Route::get('/calculators/pruning/pdf/{calculation}', [PruningCalculatorController::class, 'downloadPdf'])
    ->name('calculators.pruning.downloadPdf');


    // ================================
    // ✅ Contacts & Site Visits (with legacy redirects)
    // ================================
    // QBO: sync a single contact and import (place before resource to avoid route conflicts)
    // Customer sync routes
    Route::post('contacts/{client}/qbo-sync', [\App\Http\Controllers\ContactQboSyncController::class, 'sync'])->name('contacts.qbo.sync');
    Route::post('contacts/{client}/qbo-refresh', [\App\Http\Controllers\ContactQboSyncController::class, 'refresh'])->name('contacts.qbo.refresh');
    Route::post('contacts/{client}/qbo-push-names', [\App\Http\Controllers\ContactQboSyncController::class, 'pushNames'])->name('contacts.qbo.push-names');
    Route::post('contacts/{client}/qbo-push-mobile', [\App\Http\Controllers\ContactQboSyncController::class, 'pushMobile'])->name('contacts.qbo.push-mobile');
    // Vendor sync routes
    Route::post('contacts/{client}/qbo-vendor-sync', [\App\Http\Controllers\ContactQboSyncController::class, 'syncVendor'])->name('contacts.qbo.vendor.sync');
    Route::post('contacts/{client}/qbo-vendor-refresh', [\App\Http\Controllers\ContactQboSyncController::class, 'refreshVendor'])->name('contacts.qbo.vendor.refresh');
    Route::post('contacts/{client}/qbo-vendor-push-names', [\App\Http\Controllers\ContactQboSyncController::class, 'pushVendorNames'])->name('contacts.qbo.vendor.push-names');
    Route::post('contacts/{client}/qbo-vendor-push-mobile', [\App\Http\Controllers\ContactQboSyncController::class, 'pushVendorMobile'])->name('contacts.qbo.vendor.push-mobile');
    // Customer import routes
    Route::get('contacts/qbo/customers/link', [\App\Http\Controllers\ContactQboImportController::class, 'customerLinkPage'])->name('contacts.qbo.customer.link-page');
    Route::post('contacts/qbo/customers/sync-all', [\App\Http\Controllers\ContactQboImportController::class, 'syncAllCustomers'])->name('contacts.qbo.customer.sync-all');
    Route::get('contacts/qbo/import', [\App\Http\Controllers\ContactQboImportController::class, 'search'])->name('contacts.qbo.search');
    Route::post('contacts/qbo/import', [\App\Http\Controllers\ContactQboImportController::class, 'import'])->name('contacts.qbo.import');
    Route::post('contacts/qbo/import/selected', [\App\Http\Controllers\ContactQboImportController::class, 'importSelected'])->name('contacts.qbo.import.selected');
    Route::post('contacts/qbo/import/bulk', [\App\Http\Controllers\ContactQboImportController::class, 'importBulk'])->name('contacts.qbo.import.bulk');
    Route::post('contacts/{client}/qbo-link', [\App\Http\Controllers\ContactQboImportController::class, 'link'])->name('contacts.qbo.link');
    // Vendor import routes
    Route::get('contacts/qbo/vendors/link', [\App\Http\Controllers\ContactQboVendorImportController::class, 'linkPage'])->name('contacts.qbo.vendor.link-page');
    Route::get('contacts/qbo/vendors/import', [\App\Http\Controllers\ContactQboVendorImportController::class, 'search'])->name('contacts.qbo.vendor.search');
    Route::post('contacts/qbo/vendors/import', [\App\Http\Controllers\ContactQboVendorImportController::class, 'import'])->name('contacts.qbo.vendor.import');
    Route::post('contacts/{client}/qbo-vendor-link', [\App\Http\Controllers\ContactQboVendorImportController::class, 'link'])->name('contacts.qbo.vendor.link');
    Route::post('contacts/qbo/vendors/sync-all', [\App\Http\Controllers\ContactQboVendorImportController::class, 'syncAll'])->name('contacts.qbo.vendor.sync-all');

    // Bulk contact actions (must be before resource routes)
    Route::post('contacts/bulk/tags', [\App\Http\Controllers\ContactController::class, 'bulkTags'])->name('contacts.bulk.tags');
    Route::post('contacts/bulk/archive', [\App\Http\Controllers\ContactController::class, 'bulkArchive'])->name('contacts.bulk.archive');
    Route::delete('contacts/bulk/delete', [\App\Http\Controllers\ContactController::class, 'bulkDelete'])->name('contacts.bulk.delete');

    Route::resource('contacts', \App\Http\Controllers\ContactController::class);

    // Legacy clients -> contacts redirects
    Route::get('clients', function () { return redirect()->route('contacts.index'); })->name('clients.index');
    Route::get('clients/create', function () { return redirect()->route('contacts.create'); })->name('clients.create');
    Route::get('clients/{client}', function ($id) { return redirect('/contacts/'.$id); })->name('clients.show');
    Route::get('clients/{client}/edit', function ($id) { return redirect('/contacts/'.$id.'/edit'); })->name('clients.edit');

    // Legacy write routes to support old clients.* names
    Route::post('clients', [\App\Http\Controllers\ContactController::class, 'store'])->name('clients.store');
    Route::match(['put','patch'], 'clients/{client}', [\App\Http\Controllers\ContactController::class, 'update'])->name('clients.update');
    Route::delete('clients/{client}', [\App\Http\Controllers\ContactController::class, 'destroy'])->name('clients.destroy');

    // Contacts nested resources
    Route::resource('contacts.properties', PropertyController::class)
        ->except(['show'])
        ->parameters(['contacts' => 'client']);
    Route::resource('contacts.site-visits', SiteVisitController::class)
        ->parameters(['contacts' => 'client']);

    Route::post('contacts/{client}/site-visits/{site_visit}/photos', [SiteVisitController::class, 'storePhoto'])->name('contacts.site-visits.photos.store');
    Route::delete('contacts/{client}/site-visits/{site_visit}/photos/{photo}', [SiteVisitController::class, 'destroyPhoto'])->name('contacts.site-visits.photos.destroy');


    // Legacy nested client routes kept for backward comp (can be removed later)
    Route::resource('clients.properties', PropertyController::class)->except(['show']);
    Route::resource('clients.site-visits', SiteVisitController::class);
    Route::post('clients/{client}/site-visits/{site_visit}/photos', [SiteVisitController::class, 'storePhoto'])->name('clients.site-visits.photos.store');
    Route::delete('clients/{client}/site-visits/{site_visit}/photos/{photo}', [SiteVisitController::class, 'destroyPhoto'])->name('clients.site-visits.photos.destroy');

    Route::get('site-visits/{site_visit}/report', [SiteVisitReportController::class, 'show'])->name('site-visits.report');
    Route::get('site-visits/{site_visit}/report/pdf', [SiteVisitReportController::class, 'downloadPdf'])->name('site-visits.report.pdf');

    Route::resource('todos', TodoController::class)->except(['show']);
    Route::patch('todos/{todo}/status', [TodoController::class, 'updateStatus'])->name('todos.updateStatus');

    Route::get('site-visits/{site_visit}/estimate-line-items', [EstimateController::class, 'siteVisitLineItems'])->name('site-visits.estimate-line-items');
    Route::get('estimates/{estimate}/preview-email', [EstimateController::class, 'previewEmail'])->name('estimates.preview-email');
    Route::get('estimates/{estimate}/email-preview', [EstimateController::class, 'emailPreview'])->name('estimates.email-preview');
    Route::post('estimates/{estimate}/send-email', [EstimateController::class, 'sendEmailWithTemplate'])->name('estimates.send-email');
    Route::get('estimates/{estimate}/print', [EstimateController::class, 'print'])->name('estimates.print');
    Route::match(['get', 'post'], 'estimates/{estimate}/recalculate', [EstimateController::class, 'recalculate'])->name('estimates.recalculate');
    Route::get('estimates/{estimate}/reports/cost-analysis', [EstimateController::class, 'costAnalysisReport'])->name('estimates.reports.cost-analysis');
    Route::get('estimates/{estimate}/reports/labor-hours', [EstimateController::class, 'laborHoursReport'])->name('estimates.reports.labor-hours');
    Route::get('estimates/{estimate}/reports/material-requirements', [EstimateController::class, 'materialRequirementsReport'])->name('estimates.reports.material-requirements');
    Route::get('estimates/{estimate}/reports/profit-margin', [EstimateController::class, 'profitMarginReport'])->name('estimates.reports.profit-margin');
    Route::post('estimates/bulk-update-status', [EstimateController::class, 'bulkUpdateStatus'])->name('estimates.bulk-update-status');
    Route::resource('estimates', EstimateController::class);
    Route::post('estimates/bulk-status', [EstimateController::class, 'bulkUpdateStatus'])->name('estimates.bulk-status');
    Route::post('estimates/bulk-update-status', [EstimateController::class, 'bulkUpdateStatus'])->name('estimates.bulk-update-status');

    // Jobs (created from approved estimates)
    Route::get('jobs', [\App\Http\Controllers\JobController::class, 'index'])->name('jobs.index');
    Route::get('jobs/{job}', [\App\Http\Controllers\JobController::class, 'show'])->name('jobs.show');
    Route::patch('jobs/{job}', [\App\Http\Controllers\JobController::class, 'update'])->name('jobs.update');
    Route::post('estimates/{estimate}/create-job', [\App\Http\Controllers\JobController::class, 'createFromEstimate'])->name('estimates.create-job');

    // Timesheets
    Route::resource('timesheets', \App\Http\Controllers\TimesheetController::class);
    Route::post('timesheets/{timesheet}/submit', [\App\Http\Controllers\TimesheetController::class, 'submit'])->name('timesheets.submit');
    Route::post('timesheets/clock-in', [\App\Http\Controllers\TimesheetController::class, 'clockIn'])->name('timesheets.clock-in');
    Route::post('timesheets/{timesheet}/clock-out', [\App\Http\Controllers\TimesheetController::class, 'clockOut'])->name('timesheets.clock-out');
    
    // Timesheet Approval (Foreman/Manager/Admin)
    Route::middleware(['role:admin,manager,foreman'])->group(function () {
        Route::get('timesheets-approve', [\App\Http\Controllers\TimesheetController::class, 'approvalPage'])->name('timesheets.approve');
        Route::post('timesheets/{timesheet}/approve', [\App\Http\Controllers\TimesheetController::class, 'approve'])->name('timesheets.approve.submit');
        Route::post('timesheets/{timesheet}/reject', [\App\Http\Controllers\TimesheetController::class, 'reject'])->name('timesheets.reject');
        Route::post('timesheets/{timesheet}/unapprove', [\App\Http\Controllers\TimesheetController::class, 'unapprove'])->name('timesheets.unapprove');
        Route::post('timesheets-bulk-approve', [\App\Http\Controllers\TimesheetController::class, 'bulkApprove'])->name('timesheets.bulk-approve');
    });

    // Purchase Orders
    Route::get('purchase-orders', [\App\Http\Controllers\PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
    Route::get('purchase-orders/{purchaseOrder}', [\App\Http\Controllers\PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
    Route::delete('purchase-orders/{purchaseOrder}', [\App\Http\Controllers\PurchaseOrderController::class, 'destroy'])->name('purchase-orders.destroy');
    Route::get('purchase-orders/{purchaseOrder}/print', [\App\Http\Controllers\PurchaseOrderController::class, 'print'])->name('purchase-orders.print');
    Route::get('purchase-orders/{purchaseOrder}/email-preview', [\App\Http\Controllers\PurchaseOrderController::class, 'emailPreview'])->name('purchase-orders.email-preview');
    Route::post('purchase-orders/{purchaseOrder}/send-email', [\App\Http\Controllers\PurchaseOrderController::class, 'sendEmail'])->name('purchase-orders.send-email');
    Route::post('purchase-orders/print-batch', [\App\Http\Controllers\PurchaseOrderController::class, 'printBatch'])->name('purchase-orders.print-batch');
    Route::post('estimates/{estimate}/generate-purchase-orders', [\App\Http\Controllers\PurchaseOrderController::class, 'generateFromEstimate'])->name('estimates.generate-purchase-orders');
    Route::patch('purchase-orders/{purchaseOrder}/status', [\App\Http\Controllers\PurchaseOrderController::class, 'updateStatus'])->name('purchase-orders.update-status');
    Route::post('purchase-orders/{purchaseOrder}/qbo/sync', [\App\Http\Controllers\PurchaseOrderController::class, 'syncToQuickBooks'])->name('purchase-orders.qbo.sync');
    Route::post('purchase-orders/qbo/sync-batch', [\App\Http\Controllers\PurchaseOrderController::class, 'syncBatchToQuickBooks'])->name('purchase-orders.qbo.sync-batch');
    Route::delete('purchase-orders/{purchaseOrder}/qbo/delete', [\App\Http\Controllers\PurchaseOrderController::class, 'deleteFromQuickBooks'])->name('purchase-orders.qbo.delete');

    // Invoices -> QBO actions
    Route::post('invoices/{invoice}/qbo/create', [\App\Http\Controllers\InvoiceQboController::class, 'create'])->name('invoices.qbo.create');
    Route::post('invoices/{invoice}/qbo/refresh', [\App\Http\Controllers\InvoiceQboController::class, 'refresh'])->name('invoices.qbo.refresh');
    // Admin budgets (Admin only)
    Route::prefix('admin')->name('admin.')->middleware(['role:admin'])->group(function () {
        Route::resource('budgets', CompanyBudgetController::class)->except(['destroy', 'show']);
        // Users management
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->except(['show']);
        // Settings & Configurations: Divisions, Cost Codes, Material Categories, and Contact Tags
        Route::resource('divisions', \App\Http\Controllers\Admin\DivisionController::class)->except(['show']);
        Route::resource('cost-codes', \App\Http\Controllers\Admin\CostCodeController::class)->except(['show']);
        Route::resource('material-categories', \App\Http\Controllers\Admin\MaterialCategoryController::class)->except(['show']);
        Route::resource('contact-tags', \App\Http\Controllers\ContactTagController::class)->except(['show']);
        Route::resource('expense-accounts', \App\Http\Controllers\Admin\ExpenseAccountMappingController::class)->except(['show']);
        Route::post('expense-accounts/sync-all', [\App\Http\Controllers\Admin\ExpenseAccountMappingController::class, 'syncAll'])->name('expense-accounts.sync-all');
        Route::get('qbo/items/search', [\App\Http\Controllers\Admin\QboItemLookupController::class, 'search'])->name('qbo.items.search');
        
        // Expense Approvals & QBO Sync
        Route::get('expense-approvals', [\App\Http\Controllers\Admin\ExpenseApprovalController::class, 'index'])->name('expense-approvals.index');
        // Bulk routes must come BEFORE parameterized routes to avoid conflicts
        Route::post('expense-approvals/bulk-approve', [\App\Http\Controllers\Admin\ExpenseApprovalController::class, 'bulkApprove'])->name('expense-approvals.bulk-approve');
        Route::post('expense-approvals/bulk-sync', [\App\Http\Controllers\Admin\ExpenseApprovalController::class, 'bulkSync'])->name('expense-approvals.bulk-sync');
        Route::post('expense-approvals/{expense}/approve', [\App\Http\Controllers\Admin\ExpenseApprovalController::class, 'approve'])->name('expense-approvals.approve');
        Route::post('expense-approvals/{expense}/sync', [\App\Http\Controllers\Admin\ExpenseApprovalController::class, 'sync'])->name('expense-approvals.sync');
        Route::post('expense-approvals/{expense}/approve-and-sync', [\App\Http\Controllers\Admin\ExpenseApprovalController::class, 'approveAndSync'])->name('expense-approvals.approve-and-sync');
        
        // Company Settings
        Route::get('company-settings', [\App\Http\Controllers\Admin\CompanySettingsController::class, 'edit'])->name('company-settings.edit');
        Route::put('company-settings', [\App\Http\Controllers\Admin\CompanySettingsController::class, 'update'])->name('company-settings.update');
    });

    // Integrations: QuickBooks Online
    Route::get('integrations/qbo', [\App\Http\Controllers\Integrations\QboController::class, 'settings'])->name('integrations.qbo.settings');
    Route::get('integrations/qbo/launch', [\App\Http\Controllers\Integrations\QboController::class, 'launch'])->name('integrations.qbo.launch');
    Route::get('integrations/qbo/connect', [\App\Http\Controllers\Integrations\QboController::class, 'connect'])->name('integrations.qbo.connect');
    Route::get('integrations/qbo/callback', [\App\Http\Controllers\Integrations\QboController::class, 'callback'])->name('integrations.qbo.callback');
    Route::post('integrations/qbo/disconnect', [\App\Http\Controllers\Integrations\QboController::class, 'disconnect'])->name('integrations.qbo.disconnect');

    Route::prefix('estimates/{estimate}')->name('estimates.')->group(function () {
        Route::post('items', [EstimateItemController::class, 'store'])->name('items.store');
        Route::patch('items/{item}', [EstimateItemController::class, 'update'])->name('items.update');
        Route::delete('items/{item}', [EstimateItemController::class, 'destroy'])->name('items.destroy');
        Route::post('items/reorder', [EstimateItemController::class, 'reorder'])->name('items.reorder');

        // Work Areas
        Route::post('areas', [\App\Http\Controllers\EstimateAreaController::class, 'store'])->name('areas.store');
        Route::patch('areas/{area}', [\App\Http\Controllers\EstimateAreaController::class, 'update'])->name('areas.update');
        Route::delete('areas/{area}', [\App\Http\Controllers\EstimateAreaController::class, 'destroy'])->name('areas.destroy');
        Route::post('areas/{area}/duplicate', [\App\Http\Controllers\EstimateAreaController::class, 'duplicate'])->name('areas.duplicate');
        Route::post('areas/reorder', [\App\Http\Controllers\EstimateAreaController::class, 'reorder'])->name('areas.reorder');
        
        // Custom Pricing
        Route::post('areas/{area}/custom-price', [\App\Http\Controllers\EstimateAreaController::class, 'customPrice'])->name('areas.customPrice');
        Route::post('areas/{area}/custom-profit', [\App\Http\Controllers\EstimateAreaController::class, 'customProfit'])->name('areas.customProfit');
        Route::post('areas/{area}/clear-custom-pricing', [\App\Http\Controllers\EstimateAreaController::class, 'clearCustomPricing'])->name('areas.clearCustomPricing');

        Route::delete('remove-calculation/{calculation}', [EstimateController::class, 'removeCalculation'])->name('remove-calculation');

        // Files
        Route::post('files', [\App\Http\Controllers\EstimateFileController::class, 'store'])->name('files.store');
        Route::delete('files/{file}', [\App\Http\Controllers\EstimateFileController::class, 'destroy'])->name('files.destroy');

        // Calculator Template APIs
        Route::get('calculator/templates', [\App\Http\Controllers\EstimateCalculatorController::class, 'templates'])->name('calculator.templates');
        Route::post('calculator/import', [\App\Http\Controllers\EstimateCalculatorController::class, 'import'])->name('calculator.import');
    });

    // Calculator Templates - Global endpoints
    Route::get('calculator/templates', [\App\Http\Controllers\CalculatorTemplateController::class, 'index'])->name('calculator.templates.gallery');
    Route::get('calculator/templates/estimates/search', [\App\Http\Controllers\CalculatorTemplateController::class, 'estimateSearch'])->name('calculator.templates.estimates.search');
    Route::get('calculator/templates/estimates/{estimate}/areas', [\App\Http\Controllers\CalculatorTemplateController::class, 'estimateAreas'])->name('calculator.templates.estimates.areas');
    Route::patch('calculator/templates/{calculation}', [\App\Http\Controllers\CalculatorTemplateController::class, 'update'])->name('calculator.templates.update');
    Route::delete('calculator/templates/{calculation}', [\App\Http\Controllers\CalculatorTemplateController::class, 'destroy'])->name('calculator.templates.destroy');
    Route::post('calculator/templates/{calculation}/duplicate', [\App\Http\Controllers\CalculatorTemplateController::class, 'duplicate'])->name('calculator.templates.duplicate');
    Route::post('calculator/templates/{calculation}/import', [\App\Http\Controllers\CalculatorTemplateController::class, 'import'])->name('calculator.templates.import');

    Route::post('calculator/templates', [\App\Http\Controllers\EstimateCalculatorController::class, 'saveTemplate'])->name('calculator.templates.save');

    Route::post('estimates/{estimate}/email', [EstimateController::class, 'sendEmail'])->name('estimates.email');
    Route::post('estimates/{estimate}/invoice', [EstimateController::class, 'createInvoice'])->name('estimates.invoice');

    Route::get('asset-issues/create', [AssetController::class, 'createIssue'])->name('assets.issues.create');
    Route::post('asset-issues', [AssetController::class, 'storeIssueQuick'])->name('assets.issues.quickStore');
    Route::get('asset-reminders/create', [AssetController::class, 'createReminder'])->name('assets.reminders.create');
    Route::post('asset-reminders', [AssetController::class, 'storeReminder'])->name('assets.reminders.store');

    Route::resource('assets', AssetController::class);
    Route::post('assets/{asset}/maintenance', [AssetController::class, 'storeMaintenance'])->name('assets.maintenance.store');
    Route::post('assets/{asset}/issues', [AssetController::class, 'storeIssue'])->name('assets.issues.store');
    Route::post('assets/{asset}/attachments', [AssetController::class, 'storeAttachment'])->name('assets.attachments.store');
    Route::delete('assets/{asset}/attachments/{attachment}', [AssetController::class, 'destroyAttachment'])->name('assets.attachments.destroy');
    Route::post('assets/{asset}/link', [AssetController::class, 'linkAsset'])->name('assets.link');
    Route::delete('assets/{asset}/unlink/{linkedAsset}', [AssetController::class, 'unlinkAsset'])->name('assets.unlink');
    Route::get('assets/{asset}/checkout', [AssetController::class, 'showCheckout'])->name('assets.checkout');
    Route::post('assets/{asset}/checkout', [AssetController::class, 'storeCheckout'])->name('assets.checkout.store');
    Route::get('assets/{asset}/checkin', [AssetController::class, 'showCheckin'])->name('assets.checkin');
    Route::post('assets/{asset}/checkin', [AssetController::class, 'storeCheckin'])->name('assets.checkin.store');
    Route::get('assets/{asset}/usage-logs/{usageLog}/edit', [AssetController::class, 'editUsageLog'])->name('assets.usage-logs.edit');
    Route::put('assets/{asset}/usage-logs/{usageLog}', [AssetController::class, 'updateUsageLog'])->name('assets.usage-logs.update');
    Route::delete('assets/{asset}/usage-logs/{usageLog}', [AssetController::class, 'destroyUsageLog'])->name('assets.usage-logs.destroy');

    // Asset Expenses
    Route::get('assets-expenses/select-asset', [App\Http\Controllers\AssetExpenseController::class, 'selectAsset'])->name('assets.expenses.select-asset');
    Route::get('assets/{asset}/expenses/create', [App\Http\Controllers\AssetExpenseController::class, 'create'])->name('assets.expenses.create');
    Route::post('assets/{asset}/expenses', [App\Http\Controllers\AssetExpenseController::class, 'store'])->name('assets.expenses.store');
    Route::get('assets/{asset}/expenses/{expense}/edit', [App\Http\Controllers\AssetExpenseController::class, 'edit'])->name('assets.expenses.edit');
    Route::put('assets/{asset}/expenses/{expense}', [App\Http\Controllers\AssetExpenseController::class, 'update'])->name('assets.expenses.update');
    Route::delete('assets/{asset}/expenses/{expense}', [App\Http\Controllers\AssetExpenseController::class, 'destroy'])->name('assets.expenses.destroy');
    Route::post('assets/{asset}/expenses/{expense}/approve', [App\Http\Controllers\AssetExpenseController::class, 'approve'])->name('assets.expenses.approve');
    Route::post('assets/{asset}/expenses/{expense}/sync-qbo', [App\Http\Controllers\AssetExpenseController::class, 'syncToQbo'])->name('assets.expenses.sync-qbo');
    Route::delete('assets/{asset}/expenses/{expense}/attachments/{attachment}', [App\Http\Controllers\AssetExpenseController::class, 'deleteAttachment'])->name('assets.expenses.attachments.delete');
    Route::get('assets/{asset}/expenses/{expense}/attachments/{attachment}/download', [App\Http\Controllers\AssetExpenseController::class, 'downloadAttachment'])->name('assets.expenses.attachments.download');

    // Asset Reports
    Route::get('asset-reports', [App\Http\Controllers\AssetReportController::class, 'index'])->name('asset-reports.index');
    Route::get('asset-reports/usage', [App\Http\Controllers\AssetReportController::class, 'usageReport'])->name('asset-reports.usage');
    Route::get('asset-reports/maintenance', [App\Http\Controllers\AssetReportController::class, 'maintenanceReport'])->name('asset-reports.maintenance');
    Route::get('asset-reports/issues', [App\Http\Controllers\AssetReportController::class, 'issuesReport'])->name('asset-reports.issues');
    Route::get('asset-reports/utilization', [App\Http\Controllers\AssetReportController::class, 'utilizationReport'])->name('asset-reports.utilization');
    Route::get('asset-reports/costs', [App\Http\Controllers\AssetReportController::class, 'costsReport'])->name('asset-reports.costs');
});
require __DIR__.'/auth.php';
