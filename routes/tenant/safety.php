<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Safety\SafetyDataController;

Route::controller(SafetyDataController::class)
     ->prefix('safety')
     ->name('safety.')
     ->group(function () {
    Route::get('/', 'index')
         ->name('index')
         ->middleware('permission:safety-data.view');
    Route::post('/', 'store')
         ->name('store')
         ->middleware('permission:safety-data.create');
    Route::put('{id}', 'update')
         ->name('update')
         ->middleware('permission:safety-data.update');
    Route::delete('{id}', 'destroy')
         ->name('destroy')
         ->middleware('permission:safety-data.delete');
    Route::delete('-bulk', 'destroyBulk')
         ->name('destroyBulk')
         ->middleware('permission:safety-data.delete');
    Route::post('/import', 'import')
         ->name('import')
         ->middleware('permission:safety-data.import');
    Route::get('/export', 'export')
         ->name('export')
         ->middleware('permission:safety-data.export');
});
