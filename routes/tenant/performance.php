<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Performance\PerformanceController;

Route::controller(PerformanceController::class)
     ->prefix('performance')
     ->name('performance.')
     ->group(function () {
    Route::get('/', 'index')
         ->name('index')
         ->middleware('permission:performance.view');
    Route::post('/', 'store')
         ->name('store')
         ->middleware('permission:performance.create');
    Route::put('{performance}', 'update')
         ->name('update')
         ->middleware('permission:performance.update');
    Route::delete('{performance}', 'destroy')
         ->name('destroy')
         ->middleware('permission:performance.delete');
    Route::delete('-bulk', 'destroyBulk')
         ->name('destroyBulk')
         ->middleware('permission:performance.delete');
    Route::post('/import', 'import')
         ->name('import')
         ->middleware('permission:performance.import');
    Route::get('/export', 'export')
         ->name('export')
         ->middleware('permission:performance.export');
});
