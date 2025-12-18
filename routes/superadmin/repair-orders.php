<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\RepairOrder\RepairOrderController;

Route::controller(RepairOrderController::class)->group(function () {
    // Repair orders
    Route::get('/asset-maintenance', 'index')->name('repair_orders.index.admin');
    
    Route::prefix('repair-orders')->group(function () {
        Route::post('/', 'store')->name('repair_orders.store.admin');
        Route::put('{repair_order}', 'updateAdmin')->name('repair_orders.update.admin');
        Route::delete('{repair_order}', 'destroyAdmin')->name('repair_orders.destroy.admin');
        Route::delete('-bulk', 'destroyBulkAdmin')->name('repair_orders.destroyBulk.admin');
        Route::post('/import', 'import')->name('repair_orders.import.admin');
        Route::get('/export', 'export')->name('repair_orders.export.admin');
    });
    
    // Areas of concern
    Route::prefix('areas-of-concern')->group(function () {
        Route::post('/', 'storeAreaOfConcern')->name('area_of_concerns.store.admin');
        Route::delete('{id}', 'destroyAreaOfConcern')->name('area_of_concerns.destroy.admin');
        Route::post('{id}/restore', 'restoreAreaOfConcern')->name('area_of_concerns.restore.admin');
        Route::delete('{id}/force', 'forceDeleteAreaOfConcern')->name('area_of_concerns.forceDelete.admin');
    });
    
    // Vendors
    Route::prefix('vendors')->group(function () {
        Route::post('/', 'storeVendor')->name('vendors.store.admin');
        Route::delete('{id}', 'destroyVendor')->name('vendors.destroy.admin');
        Route::post('{id}/restore', 'restoreVendor')->name('vendors.restore.admin');
        Route::delete('{id}/force', 'forceDeleteVendor')->name('vendors.forceDelete.admin');
    });
    
    // WO statuses
    Route::prefix('wo-statuses')->group(function () {
        Route::post('/', 'storeWoStatus')->name('wo_statuses.store.admin');
        Route::delete('{id}', 'destroyWoStatus')->name('wo_statuses.destroy.admin');
        Route::post('{id}/restore', 'restoreWoStatus')->name('wo_statuses.restore.admin');
        Route::delete('{id}/force', 'forceDeleteWoStatus')->name('wo_statuses.forceDelete.admin');
    });
});
