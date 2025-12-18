<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Performance\PerformanceMetricRuleController;
use App\Http\Controllers\Web\Performance\PerformanceController;

// Performance metric rules
Route::controller(PerformanceMetricRuleController::class)
     ->prefix('metrics')
     ->name('performance-metrics.')
     ->group(function () {
    Route::get('/', 'editGlobal')->name('edit');
    Route::post('/', 'updateGlobal')->name('update');
});

// Performance data
Route::controller(PerformanceController::class)
     ->prefix('performance')
     ->group(function () {
    Route::get('/', 'index')->name('performance.index.admin');
    Route::post('/', 'store')->name('performance.store.admin');
    Route::put('{performance}', 'adminUpdate')->name('performance.update.admin');
    Route::delete('{performance}', 'adminDestroy')->name('performance.destroy.admin');
    Route::delete('-bulk', 'destroyBulkAdmin')->name('performance.destroyBulk.admin');
    Route::post('/import', 'import')->name('performance.import.admin');
    Route::get('/export', 'export')->name('performance.export.admin');
});
