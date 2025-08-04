<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PictureController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\StatsController;

// Homepage
Route::get('/', [HomeController::class, 'index'])->name('home');

// Tree Plantings - Admin, SuperAdmin and Monitor only
Route::get('/tree-plantings/report', [\App\Http\Controllers\TreePlantingController::class, 'report'])
    ->middleware(['auth', 'role:Admin|SuperAdmin|Monitor|Grower'])
    ->name('tree-plantings.report');
Route::resource('tree-plantings', \App\Http\Controllers\TreePlantingController::class)
    ->middleware(['auth', 'role:Admin|SuperAdmin|Monitor|Grower']);

// Inspections - Admin, SuperAdmin, Monitor, Grower
Route::resource('inspections', \App\Http\Controllers\InspectionController::class)
    ->middleware(['auth', 'role:Admin|SuperAdmin|Monitor|Grower|Grower']);

// Planting Locations - Admin, SuperAdmin and Monitor only
Route::resource('planting-locations', \App\Http\Controllers\PlantingLocationController::class)
    ->middleware(['auth', 'role:Admin|SuperAdmin|Monitor|Grower']);

// Dashboard (any logged-in and verified user)
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Picture upload
Route::get('planting-locations/{plantingLocation}/pictures/create', [PictureController::class, 'create'])
    ->name('pictures.create');
Route::post('pictures', [PictureController::class, 'store'])->name('pictures.store');

// User management routes
Route::middleware(['auth'])->group(function () {
    Route::get('/users/report', [UserController::class, 'index'])
        ->middleware('role:Admin|SuperAdmin')
        ->name('users.report');

    Route::get('/users/{user}/edit', [UserController::class, 'edit'])
        ->middleware('role:Admin|SuperAdmin')
        ->name('users.edit');

    Route::put('/users/{user}', [UserController::class, 'update'])
        ->middleware('role:Admin|SuperAdmin')
        ->name('users.update');
});

Route::get('/stats/map', [MapController::class, 'index'])->name('stats.map');

Route::get('/stats/stats1', [StatsController::class, 'stats1'])->name('stats.stats1');

require __DIR__.'/auth.php';
