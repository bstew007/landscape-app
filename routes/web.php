<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SiteVisitController;
use App\Http\Controllers\RetainingWallCalculatorController;
use App\Http\Controllers\PaverPatioCalculatorController;
use App\Http\Controllers\ProductionRateController;
use App\Http\Controllers\FenceCalculatorController;
use App\Http\Controllers\CalculationController;
use App\Http\Controllers\PruningCalculatorController;
use App\Http\Controllers\WeedingCalculatorController;
use App\Http\Controllers\MulchingCalculatorController;
use App\Http\Controllers\PineNeedleCalculatorController;
use App\Http\Controllers\SynTurfCalculatorController;


Route::get('/', fn () => redirect()->route('clients.index'));

Route::get('/dashboard', fn () => view('dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
     // ✅ Calculator Index Route
    Route::get('/calculators', function () {
        return view('calculators.index');
    })->name('calculators.index');

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
});

Route::get('/calculators/syn-turf/pdf/{calculation}', [SynTurfCalculatorController::class, 'downloadPdf'])
    ->name('calculators.syn_turf.downloadPdf');



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
    Route::resource('clients.site-visits', SiteVisitController::class);
});
require __DIR__.'/auth.php';
