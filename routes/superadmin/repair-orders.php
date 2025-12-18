<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\RepairOrder\RepairOrderController;

Route::controller(RepairOrderController::class)->group(function () {
    // Repair orders
    Route::get('/asset-maintenance', 'index')->name('repair_orders.index.admin');
    
    Route::prefix('repair-orders')->name('repair_orders.')->group(function () {
        Route::post('/', 'store')->name('store.admin');
        Route::put('{repair_order}', 'updateAdmin')->name('update.admin');
        Route::delete('{repair_order}', 'destroyAdmin')->name('destroy.admin');
        Route::delete('-bulk', 'destroyBulkAdmin')->name('destroyBulk.admin');
        
        // Import routes
        Route::post('/validate-import', 'validateImport')->name('validateImport.admin');
        Route::post('/confirm-import', 'confirmImport')->name('confirmImport.admin');
        Route::get('/download-error-report', 'downloadErrorReport')->name('downloadErrorReport.admin');
        
        Route::get('/export', 'export')->name('export.admin');
    });
    
    // Areas of concern
    Route::prefix('areas-of-concern')->name('area_of_concerns.')->group(function () {
        Route::post('/', 'storeAreaOfConcern')->name('store.admin');
        Route::delete('{id}', 'destroyAreaOfConcern')->name('destroy.admin');
        Route::post('{id}/restore', 'restoreAreaOfConcern')->name('restore.admin');
        Route::delete('{id}/force', 'forceDeleteAreaOfConcern')->name('forceDelete.admin');
    });
    
    // Vendors
    Route::prefix('vendors')->name('vendors.')->group(function () {
        Route::post('/', 'storeVendor')->name('store.admin');
        Route::delete('{id}', 'destroyVendor')->name('destroy.admin');
        Route::post('{id}/restore', 'restoreVendor')->name('restore.admin');
        Route::delete('{id}/force', 'forceDeleteVendor')->name('forceDelete.admin');
    });
    
    // WO statuses
    Route::prefix('wo-statuses')->name('wo_statuses.')->group(function () {
        Route::post('/', 'storeWoStatus')->name('store.admin');
        Route::delete('{id}', 'destroyWoStatus')->name('destroy.admin');
        Route::post('{id}/restore', 'restoreWoStatus')->name('restore.admin');
        Route::delete('{id}/force', 'forceDeleteWoStatus')->name('forceDelete.admin');
    });
});
