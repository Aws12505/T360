<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\On_Time\DelaysController;

Route::controller(DelaysController::class)->group(function () {
    // On-time delays
    Route::prefix('ontime')->group(function () {
        Route::get('/', 'index')->name('ontime.index.admin');
        Route::post('/', 'store')->name('ontime.store.admin');
        Route::put('{delay}', 'updateAdmin')->name('ontime.update.admin');
        Route::delete('{delay}', 'destroyAdmin')->name('ontime.destroy.admin');
        Route::delete('-bulk', 'destroyBulkAdmin')->name('ontime.destroyBulk.admin');
        Route::post('/import', 'importAdmin')->name('ontime.import.admin');
        Route::get('/export', 'exportAdmin')->name('ontime.export.admin');
    });
    
    // Delay codes
    Route::prefix('delay-codes')->group(function () {
        Route::post('/', 'storeCode')->name('delay_codes.store.admin');
        Route::delete('{id}', 'destroyCode')->name('delay_codes.destroy.admin');
        Route::post('{id}/restore', 'restoreCode')->name('delay_codes.restore.admin');
        Route::delete('{id}/force', 'forceDeleteCode')->name('delay_codes.forceDelete.admin');
    });
});
