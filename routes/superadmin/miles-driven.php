<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Miles\MilesDrivenController;

Route::controller(MilesDrivenController::class)
     ->prefix('miles-driven')
     ->group(function () {
    Route::get('/', 'index')->name('miles_driven.index.admin');
    Route::post('/', 'store')->name('miles_driven.store.admin');
    Route::put('{milesDriven}', 'updateAdmin')->name('miles_driven.update.admin');
    Route::delete('{milesDriven}', 'destroyAdmin')->name('miles_driven.destroy.admin');
});
