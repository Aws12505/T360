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
});
