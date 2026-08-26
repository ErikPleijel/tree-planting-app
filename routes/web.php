<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PictureController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\StatsController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Vite;
use App\Http\Controllers\PublicPlantingLocationController;
use App\Http\Controllers\TreeTypeController;
use App\Http\Controllers\DivisionController;

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

// Tree Planting Measurements — same roles as Inspections/TreePlantings can
// record/edit; verifying is restricted to a smaller set below.
Route::middleware(['auth', 'role:Admin|SuperAdmin|Monitor|Grower'])->group(function () {
    Route::get('/tree-plantings/{treePlanting}/measurements', [\App\Http\Controllers\TreePlantingMeasurementController::class, 'index'])
        ->name('tree-planting-measurements.index');
    Route::get('/tree-plantings/{treePlanting}/measurements/create', [\App\Http\Controllers\TreePlantingMeasurementController::class, 'create'])
        ->name('tree-planting-measurements.create');
    Route::post('/tree-plantings/{treePlanting}/measurements', [\App\Http\Controllers\TreePlantingMeasurementController::class, 'store'])
        ->name('tree-planting-measurements.store');
    Route::get('/tree-planting-measurements/{measurement}/edit', [\App\Http\Controllers\TreePlantingMeasurementController::class, 'edit'])
        ->name('tree-planting-measurements.edit');
    Route::put('/tree-planting-measurements/{measurement}', [\App\Http\Controllers\TreePlantingMeasurementController::class, 'update'])
        ->name('tree-planting-measurements.update');
});

Route::patch('/tree-planting-measurements/{measurement}/verify', [\App\Http\Controllers\TreePlantingMeasurementController::class, 'verify'])
    ->middleware(['auth', 'role:Admin|SuperAdmin|Monitor'])
    ->name('tree-planting-measurements.verify');

// LiDAR Scans — same roles as TreePlantingMeasurements' record/edit gate.
// No verify() route: verification stays entirely on the parent
// measurement, per the LiDAR Integration DECISIONS.md entry.
Route::middleware(['auth', 'role:Admin|SuperAdmin|Monitor|Grower'])->group(function () {
    Route::get('/lidar-scans/create', [\App\Http\Controllers\LidarScanController::class, 'create'])
        ->name('lidar-scans.create');
    Route::post('/lidar-scans', [\App\Http\Controllers\LidarScanController::class, 'store'])
        ->name('lidar-scans.store');
});

// Biochar Batches — same roles as Inspections/TreePlantings/Measurements.
Route::middleware(['auth', 'role:Admin|SuperAdmin|Monitor|Grower'])->group(function () {
    Route::get('/planting-locations/{plantingLocation}/biochar-batches', [\App\Http\Controllers\BiocharBatchController::class, 'index'])
        ->name('biochar-batches.index');
    Route::get('/biochar-batches/create', [\App\Http\Controllers\BiocharBatchController::class, 'create'])
        ->name('biochar-batches.create');
    Route::post('/biochar-batches', [\App\Http\Controllers\BiocharBatchController::class, 'store'])
        ->name('biochar-batches.store');
    Route::get('/biochar-batches/{biocharBatch}/edit', [\App\Http\Controllers\BiocharBatchController::class, 'edit'])
        ->name('biochar-batches.edit');
    Route::put('/biochar-batches/{biocharBatch}', [\App\Http\Controllers\BiocharBatchController::class, 'update'])
        ->name('biochar-batches.update');
    Route::delete('/biochar-batches/{biocharBatch}', [\App\Http\Controllers\BiocharBatchController::class, 'destroy'])
        ->name('biochar-batches.destroy');
});

// Planting Locations search JSON endpoint — must be before resource route to avoid {plantingLocation} binding conflict
Route::get('/planting-locations/search', [\App\Http\Controllers\PlantingLocationController::class, 'search'])
    ->middleware(['auth', 'role:Admin|SuperAdmin'])
    ->name('planting-locations.search');

// Move plantings between locations
Route::middleware(['auth', 'role:Admin|SuperAdmin'])->group(function () {
    Route::get('/planting-locations/{plantingLocation}/move', [\App\Http\Controllers\PlantingLocationController::class, 'moveForm'])
        ->name('planting-locations.move-form');
    Route::post('/planting-locations/{plantingLocation}/move', [\App\Http\Controllers\PlantingLocationController::class, 'executeMove'])
        ->name('planting-locations.move');
});

// Planting Locations - Admin, SuperAdmin and Monitor only
Route::resource('planting-locations', \App\Http\Controllers\PlantingLocationController::class)
    ->middleware(['auth', 'role:Admin|SuperAdmin|Monitor|Grower']);

// Dashboard (any logged-in and verified user)
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Team directory (all logged-in users)
Route::get('/team', [UserController::class, 'team'])
    ->middleware('auth')
    ->name('team.index');

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get(
    '/planting-locations/{plantingLocation}/pictures/upload',
    [PictureController::class, 'uploadForm']
)->name('pictures.upload.form');

Route::post(
    '/planting-locations/{plantingLocation}/pictures/upload',
    [PictureController::class, 'uploadStore']
)->name('pictures.upload.store');

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

Route::get('/diagnostic', function () {
    // Use Vite facade to generate asset URLs
    $cssUrl = Vite::asset('resources/css/app.css');

    Log::info('Diagnostic route hit', [
        'ip'    => request()->ip(),
        'agent' => request()->userAgent(),
        'css'   => $cssUrl,
    ]);


});

Route::get('/p/{public_code}', [PublicPlantingLocationController::class, 'show'])
    ->name('public.planting-locations.show');

Route::get('/planting-locations/{plantingLocation}/qr-label', [\App\Http\Controllers\PlantingLocationController::class, 'qrLabel'])
    ->middleware(['auth', 'role:Admin|SuperAdmin|Monitor|Grower'])
    ->name('planting-locations.qr-label');


Route::delete('/pictures/{picture}', [PictureController::class, 'destroy'])
    ->name('pictures.destroy');

Route::patch('/pictures/{picture}/toggle-welcome', [PictureController::class, 'toggleWelcome'])
    ->name('pictures.toggle-welcome');


// Tree types — index/show are public; create/edit/delete require Admin or SuperAdmin
Route::get('/tree-types', [TreeTypeController::class, 'index'])->name('tree-types.index');
Route::middleware(['auth', 'role:Admin|SuperAdmin'])->group(function () {
    Route::get('/tree-types/create', [TreeTypeController::class, 'create'])->name('tree-types.create');
    Route::post('/tree-types', [TreeTypeController::class, 'store'])->name('tree-types.store');
    Route::get('/tree-types/{treeType}/edit', [TreeTypeController::class, 'edit'])->name('tree-types.edit');
    Route::put('/tree-types/{treeType}', [TreeTypeController::class, 'update'])->name('tree-types.update');
    Route::delete('/tree-types/{treeType}', [TreeTypeController::class, 'destroy'])->name('tree-types.destroy');
});
Route::get('/tree-types/{treeType}', [TreeTypeController::class, 'show'])->name('tree-types.show');

// Contributors registry — managing the organization registry itself is
// more sensitive than attaching one to a specific event, so it gets a
// narrower gate (Admin|SuperAdmin only) than the attach/detach routes
// below. Search must come before the resource route to avoid a
// {contributor} binding conflict, same pattern as planting-locations.search.
Route::get('/contributors/search', [\App\Http\Controllers\ContributorController::class, 'search'])
    ->middleware(['auth', 'role:Admin|SuperAdmin|Monitor|Grower'])
    ->name('contributors.search');
Route::resource('contributors', \App\Http\Controllers\ContributorController::class)
    ->except(['show'])
    ->middleware(['auth', 'role:Admin|SuperAdmin']);

// Divisions — SuperAdmin only, including index/viewing (unlike tree-types,
// where index/show are public). Adding/editing/removing a region is a
// bigger action than a tree type, so the whole feature is gated, not just
// mutations. No show() — LGA_name/latitude/longitude are simple enough
// that index + edit cover everything a detail view would show.
Route::resource('divisions', DivisionController::class)
    ->except(['show'])
    ->middleware(['auth', 'role:SuperAdmin']);

// Attach/detach contributors on a specific TreePlanting — broader gate,
// matches the existing pattern for measurements/biochar batches.
Route::middleware(['auth', 'role:Admin|SuperAdmin|Monitor|Grower'])->group(function () {
    Route::get('/tree-plantings/{treePlanting}/contributors', [\App\Http\Controllers\TreePlantingContributorController::class, 'index'])
        ->name('tree-planting-contributors.index');
    Route::post('/tree-plantings/{treePlanting}/contributors', [\App\Http\Controllers\TreePlantingContributorController::class, 'attach'])
        ->name('tree-planting-contributors.attach');
    Route::delete('/tree-plantings/{treePlanting}/contributors/{contributor}', [\App\Http\Controllers\TreePlantingContributorController::class, 'detach'])
        ->name('tree-planting-contributors.detach');
});
