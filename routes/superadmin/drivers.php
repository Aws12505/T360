<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Driver\DriverController;

Route::controller(DriverController::class)
    ->prefix('drivers')
    ->group(function () {
        Route::get('/', 'index')->name('driver.index.admin');
                // ✅ MATCH PERFORMANCE IMPORT FLOW
        Route::post('/validate-import', 'validateImport')
            ->name('driver.validateImport.admin');

        Route::post('/confirm-import', 'confirmImport')
            ->name('driver.confirmImport.admin');

        Route::get('/download-error-report', 'downloadErrorReport')
            ->name('driver.downloadErrorReport.admin');
        Route::post('/', 'store')->name('driver.store.admin');
        Route::post('{driver}', 'updateAdmin')->name('driver.update.admin');
        Route::delete('{driver}', 'destroyAdmin')->name('driver.destroy.admin');
        Route::delete('-bulk', 'destroyBulkAdmin')->name('driver.destroyBulk.admin');



        Route::get('/export', 'export')->name('driver.export.admin');
        Route::get('{driver}', 'showAdmin')->name('driver.show.admin');
    });
