<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\RepairOrder\RepairOrderController;

Route::controller(RepairOrderController::class)->group(function () {
    Route::get('asset-maintenance', 'index')
         ->name('repair_orders.index')
         ->middleware('permission:repair-orders.view|trucks.view|miles-driven.view');
    
    Route::prefix('repair-orders')->name('repair_orders.')->group(function () {
        Route::post('/', 'store')
             ->name('store')
             ->middleware('permission:repair-orders.create');
        Route::put('{repair_order}', 'update')
             ->name('update')
             ->middleware('permission:repair-orders.update');
        Route::delete('{repair_order}', 'destroy')
             ->name('destroy')
             ->middleware('permission:repair-orders.delete');
        Route::delete('-bulk', 'destroyBulk')
             ->name('destroyBulk')
             ->middleware('permission:repair-orders.delete');
        
        // Import routes
        Route::post('/validate-import', 'validateImport')
             ->name('validateImport')
             ->middleware('permission:repair-orders.import');
        Route::post('/confirm-import', 'confirmImport')
             ->name('confirmImport')
             ->middleware('permission:repair-orders.import');
        Route::get('/download-error-report', 'downloadErrorReport')
             ->name('downloadErrorReport')
             ->middleware('permission:repair-orders.import');
        
        Route::get('/export', 'export')
             ->name('export')
             ->middleware('permission:repair-orders.export');
    });
});
