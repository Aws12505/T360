<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Acceptance\RejectionsController;

Route::controller(RejectionsController::class)->group(function () {
    // Acceptance / Rejections
    Route::prefix('acceptance')->group(function () {
        Route::get('/', 'index')->name('acceptance.index.admin');
        Route::post('/', 'store')->name('acceptance.store.admin');
        Route::put('{rejection}', 'updateAdmin')->name('acceptance.update.admin');
        Route::delete('{rejection}', 'destroyAdmin')->name('acceptance.destroy.admin');
        Route::delete('-bulk', 'destroyBulkAdmin')->name('acceptance.destroyBulk.admin');
        Route::post('/import', 'importAdmin')->name('acceptance.import.admin');
        Route::get('/export', 'exportAdmin')->name('acceptance.export.admin');
    });
    
    // Rejection reason codes
    Route::prefix('rejection-reason-codes')->group(function () {
        Route::post('/', 'storeCode')->name('rejection_reason_codes.store.admin');
        Route::delete('{id}', 'destroyCode')->name('rejection_reason_codes.destroy.admin');
        Route::post('{id}/restore', 'restoreCode')->name('rejection_reason_codes.restore.admin');
        Route::delete('{id}/force', 'forceDeleteCode')->name('rejection_reason_codes.forceDelete.admin');
    });
});
