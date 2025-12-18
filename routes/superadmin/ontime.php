<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\On_Time\DelaysController;

Route::controller(DelaysController::class)->group(function () {

    Route::prefix('ontime')->group(function () {
        Route::get('/', 'index')->name('ontime.index.admin');
        Route::post('/', 'store')->name('ontime.store.admin');
        Route::put('{delay}', 'updateAdmin')->name('ontime.update.admin');
        Route::delete('{delay}', 'destroyAdmin')->name('ontime.destroy.admin');
        Route::delete('-bulk', 'destroyBulkAdmin')->name('ontime.destroyBulk.admin');

        // ✅ NEW: validation + confirm import + error report (admin)
        Route::post('/validate-import', 'validateImport')->name('ontime.validateImport.admin');
        Route::post('/confirm-import', 'confirmImport')->name('ontime.confirmImport.admin');
        Route::get('/download-error-report', 'downloadErrorReport')->name('ontime.downloadErrorReport.admin');

        Route::get('/export', 'exportAdmin')->name('ontime.export.admin');
    });

    // delay codes stay as-is
    Route::prefix('delay-codes')->group(function () {
        Route::post('/', 'storeCode')->name('delay_codes.store.admin');
        Route::delete('{id}', 'destroyCode')->name('delay_codes.destroy.admin');
        Route::post('{id}/restore', 'restoreCode')->name('delay_codes.restore.admin');
        Route::delete('{id}/force', 'forceDeleteCode')->name('delay_codes.forceDelete.admin');
    });
});
