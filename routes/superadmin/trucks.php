<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Truck\TruckController;

Route::controller(TruckController::class)
    ->prefix('trucks')
    ->group(function () {

        Route::get('/', 'index')->name('truck.index.admin');
        Route::post('/', 'store')->name('truck.store.admin');
        Route::put('{truck}', 'updateAdmin')->name('truck.update.admin');
        Route::delete('{truck}', 'destroyAdmin')->name('truck.destroy.admin');
        Route::delete('-bulk', 'destroyBulkAdmin')->name('truck.destroyBulk.admin');

        // ✅ NEW: validate + confirm + error report
        Route::post('/validate-import', 'validateImport')->name('truck.validateImport.admin');
        Route::post('/confirm-import', 'confirmImport')->name('truck.confirmImport.admin');
        Route::get('/download-error-report', 'downloadErrorReport')->name('truck.downloadErrorReport.admin');

        Route::get('/export', 'export')->name('truck.export.admin');
    });
