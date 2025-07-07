<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::resource('tree-plantings', \App\Http\Controllers\TreePlantingController::class)
    ->middleware(['auth', 'role:Admin,Inspector']);

Route::resource('inspections', \App\Http\Controllers\InspectionController::class)
    ->middleware(['auth', 'role:Admin,Inspector,Verifier']);

Route::resource('planting-locations', \App\Http\Controllers\PlantingLocationController::class)
    ->middleware(['auth', 'role:Admin,Inspector']);

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

/*



Route::resource('inspections', \App\Http\Controllers\InspectionController::class)->middleware(['auth']);


Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'role:Admin,Inspector,Verifier,Viewer']);

Route::resource('tree-plantings', \App\Http\Controllers\TreePlantingController::class)->middleware(['auth']);

Route::resource('inspections', \App\Http\Controllers\InspectionController::class)
    ->middleware(['auth', 'role:Admin,Inspector,Verifier']);

Route::resource('planting-locations', \App\Http\Controllers\PlantingLocationController::class)->middleware(['auth']);
*/




Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
