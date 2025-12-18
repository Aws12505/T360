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
    Route::post('/import', 'import')->name('truck.import.admin');
    Route::get('/export', 'export')->name('truck.export.admin');
});
