<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SiteVisitController;
use App\Http\Controllers\RetainingWallCalculatorController;
use App\Http\Controllers\PaverPatioCalculatorController;

Route::get('/', function () {
    return redirect()->route('clients.index');
});

Route::get('/calculations/{calculation}/pdf', [RetainingWallCalculatorController::class, 'downloadPdf'])->name('calculations.downloadPdf');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ✅ View calculation result after saving
    Route::get('/calculations/{calculation}/result', [RetainingWallCalculatorController::class, 'showResult'])
        ->name('calculations.showResult');

    // ✅ Calculators index (optional)
    Route::get('/calculators', function () {
        return view('calculators.index');
    })->name('calculators.index');

    // ✅ Retaining Wall Calculator
    Route::get('/calculators/retaining-wall', [RetainingWallCalculatorController::class, 'showForm'])
        ->name('calculators.wall.form');

    Route::post('/calculators/retaining-wall', [RetainingWallCalculatorController::class, 'calculate'])
        ->name('calculators.wall.calculate');

    Route::get('/calculators/retaining-wall/{calculation}/edit', [RetainingWallCalculatorController::class, 'edit'])
    ->name('calculators.wall.edit');

    // Paver Patio Calculator
Route::get('/calculators/paver-patio', [PaverPatioCalculatorController::class, 'showForm'])
    ->name('calculators.patio.form');

Route::post('/calculators/paver-patio', [PaverPatioCalculatorController::class, 'calculate'])
    ->name('calculators.patio.calculate');

Route::get('/calculators/paver-patio/{calculation}/edit', [PaverPatioCalculatorController::class, 'edit'])
    ->name('calculators.patio.edit');

Route::get('/calculations/{calculation}/paver-patio/result', [PaverPatioCalculatorController::class, 'showResult'])
    ->name('calculations.patio.showResult');

Route::get('/calculations/{calculation}/paver-patio/pdf', [PaverPatioCalculatorController::class, 'downloadPdf'])
    ->name('calculations.patio.downloadPdf');


    // ✅ Save calculation to Site Visit
    Route::post('/site-visits/calculation', [SiteVisitController::class, 'storeCalculation'])
        ->name('site-visits.storeCalculation');

    // ✅ Clients & Site Visits
    Route::resource('clients', ClientController::class);
    Route::resource('clients.site-visits', SiteVisitController::class);

    Route::delete('/calculations/{calculation}', [\App\Http\Controllers\CalculationController::class, 'destroy'])
    ->name('site-visits.deleteCalculation');


});

require __DIR__.'/auth.php';
