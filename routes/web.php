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


Route::get('/', fn () => redirect()->route('client-hub'));

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
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::middleware('auth')->group(function () {
     // ✅ Calculator Index Route
    Route::get('/calculators', function () {
        return view('calculators.index');
    })->name('calculators.index');

    Route::get('/client-hub', ClientHubController::class)->name('client-hub');

    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');

    // ✅ Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ✅ Production Rates (moved into Admin-only group above)

    // ✅ Catalogs (Admin Only)
    Route::middleware(['can:manage-catalogs'])->group(function () {
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
    Route::post('contacts/{client}/qbo-sync', [\App\Http\Controllers\ContactQboSyncController::class, 'sync'])->name('contacts.qbo.sync');
    Route::post('contacts/{client}/qbo-refresh', [\App\Http\Controllers\ContactQboSyncController::class, 'refresh'])->name('contacts.qbo.refresh');
    Route::post('contacts/{client}/qbo-push-names', [\App\Http\Controllers\ContactQboSyncController::class, 'pushNames'])->name('contacts.qbo.push-names');
    Route::post('contacts/{client}/qbo-push-mobile', [\App\Http\Controllers\ContactQboSyncController::class, 'pushMobile'])->name('contacts.qbo.push-mobile');
    Route::get('contacts/qbo/import', [\App\Http\Controllers\ContactQboImportController::class, 'search'])->name('contacts.qbo.search');
    Route::post('contacts/qbo/import', [\App\Http\Controllers\ContactQboImportController::class, 'import'])->name('contacts.qbo.import');
    Route::post('contacts/qbo/import/selected', [\App\Http\Controllers\ContactQboImportController::class, 'importSelected'])->name('contacts.qbo.import.selected');
    Route::post('contacts/qbo/import/bulk', [\App\Http\Controllers\ContactQboImportController::class, 'importBulk'])->name('contacts.qbo.import.bulk');
    Route::post('contacts/{client}/qbo-link', [\App\Http\Controllers\ContactQboImportController::class, 'link'])->name('contacts.qbo.link');

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
    Route::get('estimates/{estimate}/print', [EstimateController::class, 'print'])->name('estimates.print');
    Route::post('estimates/bulk-update-status', [EstimateController::class, 'bulkUpdateStatus'])->name('estimates.bulk-update-status');
    Route::resource('estimates', EstimateController::class);
    Route::post('estimates/bulk-status', [EstimateController::class, 'bulkUpdateStatus'])->name('estimates.bulk-status');
    Route::post('estimates/bulk-update-status', [EstimateController::class, 'bulkUpdateStatus'])->name('estimates.bulk-update-status');

    // Purchase Orders
    Route::get('purchase-orders', [\App\Http\Controllers\PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
    Route::get('purchase-orders/{purchaseOrder}', [\App\Http\Controllers\PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
    Route::delete('purchase-orders/{purchaseOrder}', [\App\Http\Controllers\PurchaseOrderController::class, 'destroy'])->name('purchase-orders.destroy');
    Route::get('purchase-orders/{purchaseOrder}/print', [\App\Http\Controllers\PurchaseOrderController::class, 'print'])->name('purchase-orders.print');
    Route::post('purchase-orders/print-batch', [\App\Http\Controllers\PurchaseOrderController::class, 'printBatch'])->name('purchase-orders.print-batch');
    Route::post('estimates/{estimate}/generate-purchase-orders', [\App\Http\Controllers\PurchaseOrderController::class, 'generateFromEstimate'])->name('estimates.generate-purchase-orders');
    Route::patch('purchase-orders/{purchaseOrder}/status', [\App\Http\Controllers\PurchaseOrderController::class, 'updateStatus'])->name('purchase-orders.update-status');

    // Invoices -> QBO actions
    Route::post('invoices/{invoice}/qbo/create', [\App\Http\Controllers\InvoiceQboController::class, 'create'])->name('invoices.qbo.create');
    Route::post('invoices/{invoice}/qbo/refresh', [\App\Http\Controllers\InvoiceQboController::class, 'refresh'])->name('invoices.qbo.refresh');
    // Admin budgets (protect with auth/ability middleware in your app)
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('budgets', CompanyBudgetController::class)->except(['destroy', 'show']);
        // Users management
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->except(['show']);
        // Settings & Configurations: Divisions, Cost Codes, and Material Categories
        Route::resource('divisions', \App\Http\Controllers\Admin\DivisionController::class)->except(['show']);
        Route::resource('cost-codes', \App\Http\Controllers\Admin\CostCodeController::class)->except(['show']);
        Route::resource('material-categories', \App\Http\Controllers\Admin\MaterialCategoryController::class)->except(['show']);
        Route::get('qbo/items/search', [\App\Http\Controllers\Admin\QboItemLookupController::class, 'search'])->name('qbo.items.search');
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
        Route::post('areas/reorder', [\App\Http\Controllers\EstimateAreaController::class, 'reorder'])->name('areas.reorder');

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
});
require __DIR__.'/auth.php';
