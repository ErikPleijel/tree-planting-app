<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PictureController;
use App\Http\Controllers\HomeController;

/*Route::get('/', function () {
    return view('home');
})->name('home');*/
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::resource('tree-plantings', \App\Http\Controllers\TreePlantingController::class)
    ->middleware(['auth', 'role:Admin,Inspector']);



Route::resource('inspections', \App\Http\Controllers\InspectionController::class)
    ->middleware(['auth', 'role:Admin,Inspector,Verifier']);

Route::resource('planting-locations', \App\Http\Controllers\PlantingLocationController::class)
    ->middleware(['auth', 'role:Admin,Inspector']);



/*Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');*/

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::get('planting-locations/{plantingLocation}/pictures/create', [PictureController::class, 'create'])->name('pictures.create');
Route::post('pictures', [PictureController::class, 'store'])->name('pictures.store');

/*




Route::resource('inspections', \App\Http\Controllers\InspectionController::class)->middleware(['auth']);


Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'role:Admin,Inspector,Verifier,Viewer']);

Route::resource('tree-plantings', \App\Http\Controllers\TreePlantingController::class)->middleware(['auth']);

Route::resource('inspections', \App\Http\Controllers\InspectionController::class)
    ->middleware(['auth', 'role:Admin,Inspector,Verifier']);

Route::resource('planting-locations', \App\Http\Controllers\PlantingLocationController::class)->middleware(['auth']);
*/





require __DIR__.'/auth.php';
