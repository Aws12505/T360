<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Safety\SafetyDataController;

Route::controller(SafetyDataController::class)
     ->prefix('safety')
     ->group(function () {
    Route::get('/', 'index')->name('safety.index.admin');
    Route::post('/', 'store')->name('safety.store.admin');
    Route::put('{id}', 'updateAdmin')->name('safety.update.admin');
    Route::delete('{id}', 'destroyAdmin')->name('safety.destroy.admin');
    Route::delete('-bulk', 'destroyBulkAdmin')->name('safety.destroyBulk.admin');
    Route::post('/import', 'import')->name('safety.import.admin');
    Route::get('/export', 'export')->name('safety.export.admin');
});
