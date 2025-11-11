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


Route::get('/', fn () => redirect()->route('client-hub'));

Route::get('/dashboard', fn () => view('dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

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

    // ✅ Production Rates (Admin)
    Route::resource('production-rates', ProductionRateController::class)->except(['show']);

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
    // ✅ Clients & Site Visits
    // ================================
    Route::resource('clients', ClientController::class);
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
    Route::resource('estimates', EstimateController::class);
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
