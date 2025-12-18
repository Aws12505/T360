<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Driver\DriverController;

Route::controller(DriverController::class)
     ->prefix('drivers')
     ->group(function () {
    Route::get('/', 'index')->name('driver.index.admin');
    Route::post('/', 'store')->name('driver.store.admin');
    Route::post('{driver}', 'updateAdmin')->name('driver.update.admin');
    Route::delete('{driver}', 'destroyAdmin')->name('driver.destroy.admin');
    Route::delete('-bulk', 'destroyBulkAdmin')->name('driver.destroyBulk.admin');
    Route::post('/import', 'import')->name('driver.import.admin');
    Route::get('/export', 'export')->name('driver.export.admin');
    Route::get('{driver}', 'showAdmin')->name('driver.show.admin');
});
