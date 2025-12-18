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

                    // ✅ MATCH PERFORMANCE IMPORT FLOW
        Route::post('/validate-import', 'validateImport')
            ->name('validateImport')
            ->middleware('permission:drivers.import');

        Route::post('/confirm-import', 'confirmImport')
            ->name('confirmImport')
            ->middleware('permission:drivers.import');

        Route::get('/download-error-report', 'downloadErrorReport')
            ->name('downloadErrorReport')
            ->middleware('permission:drivers.import');
            
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



        Route::get('/export', 'export')
            ->name('export')
            ->middleware('permission:drivers.export');

        Route::get('{driver}', 'show')
            ->name('show')
            ->middleware('permission:drivers.profile.view');
    });
