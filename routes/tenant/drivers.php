<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Driver\DriverController;

Route::controller(DriverController::class)
     ->prefix('drivers')
     ->name('driver.')
     ->group(function () {
    Route::get('/', 'index')
         ->name('index')
         ->middleware('permission:drivers.view');
    Route::post('/', 'store')
         ->name('store')
         ->middleware('permission:drivers.create');
    Route::post('{driver}', 'update')
         ->name('update')
         ->middleware('permission:drivers.update');
    Route::delete('{driver}', 'destroy')
         ->name('destroy')
         ->middleware('permission:drivers.delete');
    Route::delete('-bulk', 'destroyBulk')
         ->name('destroyBulk')
         ->middleware('permission:drivers.delete');
    Route::post('/import', 'import')
         ->name('import')
         ->middleware('permission:drivers.import');
    Route::get('/export', 'export')
         ->name('export')
         ->middleware('permission:drivers.export');
    Route::get('{driver}', 'show')
         ->name('show')
         ->middleware('permission:drivers.profile.view');
});
