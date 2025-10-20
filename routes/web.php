<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SiteVisitController;
use App\Http\Controllers\RetainingWallCalculatorController;

Route::get('/', function () {
    return redirect()->route('clients.index');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ✅ Calculators index (optional)
    Route::get('/calculators', function () {
        return view('calculators.index');
    })->name('calculators.index');

    // ✅ Retaining Wall Calculator
    Route::get('/calculators/retaining-wall', [RetainingWallCalculatorController::class, 'showForm'])
        ->name('calculators.wall.form');

    Route::post('/calculators/retaining-wall', [RetainingWallCalculatorController::class, 'calculate'])
        ->name('calculators.wall.calculate');

    // ✅ Save calculation to Site Visit
    Route::post('/site-visits/calculation', [SiteVisitController::class, 'storeCalculation'])
        ->name('site-visits.storeCalculation');

    // ✅ Clients & Site Visits
    Route::resource('clients', ClientController::class);
    Route::resource('clients.site-visits', SiteVisitController::class);

    Route::delete('/calculations/{calculation}', [App\Http\Controllers\CalculationController::class, 'destroy'])
    ->name('calculations.destroy');

});

require __DIR__.'/auth.php';
