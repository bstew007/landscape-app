<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SiteVisitController;
use App\Http\Controllers\RetainingWallCalculatorController;
use App\Http\Controllers\PaverPatioCalculatorController;
use App\Http\Controllers\LandscapeEnhancementController;
use App\Http\Controllers\ProductionRateController;
use App\Http\Controllers\FenceCalculatorController;
use App\Http\Controllers\CalculationController;

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

    Route::get('/{calculation}/edit', [FenceCalculatorController::class, 'edit'])
        ->name('calculators.fence.edit');

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
    // ✅ Landscape Enhancements
    // ================================
    Route::get('/calculators/landscape-enhancements', [LandscapeEnhancementController::class, 'create'])->name('calculators.enhancements.form');
    Route::post('/calculators/landscape-enhancements', [LandscapeEnhancementController::class, 'calculate'])->name('calculators.enhancements.calculate');
    Route::get('/calculators/landscape-enhancements/pdf/{id}', [LandscapeEnhancementController::class, 'downloadPdf'])->name('calculators.enhancements.downloadPdf');
    Route::get('/calculators/landscape-enhancements/{id}/edit', [LandscapeEnhancementController::class, 'edit'])->name('calculators.enhancements.edit');


    // ================================
    // ✅ Clients & Site Visits
    // ================================
    Route::resource('clients', ClientController::class);
    Route::resource('clients.site-visits', SiteVisitController::class);
});
require __DIR__.'/auth.php';
