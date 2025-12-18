<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Truck\TruckController;

Route::controller(TruckController::class)
     ->prefix('trucks')
     ->name('truck.')
     ->group(function () {
    Route::get('/', 'index')
         ->name('index')
         ->middleware('permission:trucks.view');
    Route::post('/', 'store')
         ->name('store')
         ->middleware('permission:trucks.create');
    Route::put('{truck}', 'update')
         ->name('update')
         ->middleware('permission:trucks.update');
    Route::delete('{truck}', 'destroy')
         ->name('destroy')
         ->middleware('permission:trucks.delete');
    Route::delete('-bulk', 'destroyBulk')
         ->name('destroyBulk')
         ->middleware('permission:trucks.delete');
    Route::post('/import', 'import')
         ->name('import')
         ->middleware('permission:trucks.import');
    Route::get('/export', 'export')
         ->name('export')
         ->middleware('permission:trucks.export');
});
